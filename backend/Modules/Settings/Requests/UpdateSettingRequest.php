<?php

namespace Modules\Settings\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Settings\Services\SettingsSchema;

/**
 * Validates a single-group save: { key: "<group>", value: {...} }.
 *
 * The group name is constrained to the schema so an arbitrary key can never
 * be written into the settings table, and the value is then validated with
 * that group's own rule set.
 */
class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route is already behind auth:sanctum + admin.
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

    /**
     * When the group name comes from the URL (PUT /admin/settings/{key}) it
     * is folded into the payload so both routes validate identically.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('key') && $this->route('key')) {
            $this->merge(['key' => $this->route('key')]);
        }
    }

    public function rules(): array
    {
        $key = (string) $this->input('key');

        return array_merge([
            'key' => ['required', 'string', Rule::in(SettingsSchema::groups())],
            'value' => ['required', 'array'],
        ], SettingsSchema::rulesFor($key));
    }

    public function messages(): array
    {
        return [
            'key.required' => 'No settings key was supplied.',
            'key.in' => 'The settings key is not valid.',
            'value.required' => 'The settings value cannot be empty.',
            'value.array' => 'The settings value has an invalid structure.',
        ];
    }
}
