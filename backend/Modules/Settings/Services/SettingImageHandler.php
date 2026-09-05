<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns inline base64 images inside a settings payload into stored files.
 *
 * The settings editor sends images as data URLs (it has no separate upload
 * step), so any string that looks like one is written to disk and replaced by
 * its public URL. The previous value is walked in parallel so a replaced or
 * removed image is deleted instead of leaking onto the disk forever.
 */
class SettingImageHandler
{
    protected const MAX_BYTES = 5 * 1024 * 1024;

    protected const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];

    protected function disk(): string
    {
        return (string) config('settings.media_disk', 'outside');
    }

    protected function directory(): string
    {
        return trim((string) config('settings.media_directory', 'settings'), '/');
    }

    public function process(mixed $value, mixed $oldValue = null): mixed
    {
        if (is_array($value)) {
            $oldArray = is_array($oldValue) ? $oldValue : [];

            $processed = [];
            foreach ($value as $key => $item) {
                $processed[$key] = $this->process($item, $oldArray[$key] ?? null);
            }

            // Anything present in the old value but gone from the new one is
            // an image the admin removed: clean it off the disk.
            foreach ($oldArray as $key => $item) {
                if (! array_key_exists($key, $value)) {
                    $this->purge($item);
                }
            }

            return $processed;
        }

        if (is_string($value) && $this->isBase64Image($value)) {
            if (is_string($oldValue) && $this->isStoredFile($oldValue)) {
                $this->delete($oldValue);
            }

            return $this->store($value);
        }

        // The value changed from one stored file to a different string
        // (another URL, or empty): the old file is now unreferenced.
        if (is_string($oldValue) && $oldValue !== $value && $this->isStoredFile($oldValue)) {
            $this->delete($oldValue);
        }

        return $value;
    }

    /**
     * Recursively delete any stored file inside a discarded branch.
     */
    protected function purge(mixed $value): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->purge($item);
            }

            return;
        }

        if (is_string($value) && $this->isStoredFile($value)) {
            $this->delete($value);
        }
    }

    protected function isBase64Image(string $value): bool
    {
        return (bool) preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $value);
    }

    protected function isStoredFile(string $url): bool
    {
        $base = Storage::disk($this->disk())->url($this->directory().'/');

        return $url !== '' && str_starts_with($url, $base);
    }

    protected function store(string $base64): string
    {
        preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64, $matches);

        $extension = strtolower($matches[1] ?? 'png');
        $extension = $extension === 'svg+xml' ? 'svg' : $extension;

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException('That image format is not supported.');
        }

        $payload = substr($base64, strpos($base64, ',') + 1);
        $binary = base64_decode($payload, true);

        if ($binary === false) {
            throw new \InvalidArgumentException('The submitted image is not valid.');
        }

        if (strlen($binary) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('The image may not be larger than 5 MB.');
        }

        // SVG can carry scripts; it is stored but never inlined by the
        // frontend, which always renders settings images through <img>.
        $filename = $this->directory().'/'.Str::uuid().'.'.$extension;

        Storage::disk($this->disk())->put($filename, $binary);

        return Storage::disk($this->disk())->url($filename);
    }

    protected function delete(string $url): void
    {
        $base = Storage::disk($this->disk())->url('');
        $path = ltrim(str_replace($base, '', $url), '/');

        if ($path !== '' && str_starts_with($path, $this->directory().'/')) {
            Storage::disk($this->disk())->delete($path);
        }
    }
}
