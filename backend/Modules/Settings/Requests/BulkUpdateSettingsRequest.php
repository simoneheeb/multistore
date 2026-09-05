<?php

namespace Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Settings\Services\SettingsSchema;

/**
 * Validates a whole settings tab saved at once:
 * { settings: { header: {...}, footer: {...} } }.
 *
 * Each group is validated with its own rules by rewriting that group's
 * "value.*" paths onto "settings.<group>.*".
 */
class BulkUpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The submitted data is not valid.',
                'errors' => $validator->errors(),
                'status' => false,
            ], 422)
        );
    }

    public function rules(): array
    {
        $rules = [
            'settings' => ['required', 'array', 'min:1'],
        ];

        $groups = SettingsSchema::groups();

        foreach (array_keys((array) $this->input('settings', [])) as $group) {
            if (! in_array($group, $groups, true)) {
                // Marks the unknown group as invalid instead of silently
                // ignoring it, so the admin sees why nothing was saved.
                $rules['settings.'.$group] = ['prohibited'];

                continue;
            }

            $rules['settings.'.$group] = ['required', 'array'];

            foreach (SettingsSchema::rulesFor($group) as $path => $rule) {
                $rules['settings.'.$group.substr($path, strlen('value'))] = $rule;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'No settings were supplied to save.',
            'settings.*.prohibited' => 'One of the supplied keys is not a valid settings group.',
        ];
    }
}
