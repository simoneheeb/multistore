<?php

namespace App\Services;

use App\Http\Requests\FileRequest;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class FileService
{
    protected FilesystemAdapter $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('outside');
    }

    // store
    public function upload(FileRequest $fileRequest): array
    {
        $path = trim((string) $fileRequest->input('path', ''), '/');
        $name = $fileRequest->input('name');
        $file = $fileRequest->file('file');

        $filename = $name
            ? $name.'.'.$file->getClientOriginalExtension()
            : $file->hashName();

        $storedPath = $this->disk->putFileAs($path, $file, $filename);

        return [
            'path' => $storedPath,
            'url' => $this->disk->url($storedPath),
            'message' => 'File uploaded successfully.',
        ];
    }

    // update
    public function update(FileRequest $fileRequest): array
    {
        $path = trim((string) $fileRequest->input('path', ''), '/');
        $file = $fileRequest->file('file');

        if ($path === '') {
            throw new \InvalidArgumentException('path is required.');
        }

        $storedPath = $this->disk->putFileAs(dirname($path), $file, basename($path));

        return [
            'path' => $storedPath,
            'url' => $this->disk->url($storedPath),
            'message' => 'File updated successfully.',
        ];
    }

    // delete
    public function delete(string $path): bool
    {
        return $this->disk->delete($path);
    }

    // find exists
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    // find file path
    public function findPath(string $fileName): ?string
    {
        foreach ($this->disk->allFiles() as $file) {
            if (basename($file) === $fileName) {
                return $file;
            }
        }

        return null;
    }
}
