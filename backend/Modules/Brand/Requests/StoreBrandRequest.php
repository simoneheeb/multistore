<?php

namespace Modules\Brand\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (auth:sanctum + admin) already gates this.
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
     * Multipart form data carries booleans and integers as strings.
     */
    protected function prepareForValidation(): void
    {
        foreach (['is_active', 'is_new'] as $flag) {
            if ($this->has($flag)) {
                $this->merge([$flag => filter_var($this->input($flag), FILTER_VALIDATE_BOOLEAN)]);
            }
        }

        if ($this->has('order')) {
            $this->merge(['order' => (int) $this->input('order')]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug'],

            'description' => ['required', 'string', 'max:10000'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            'is_active' => ['boolean'],
            'is_new' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The brand name is required.',
            'name.unique' => 'A brand with this name already exists.',
            'description.required' => 'The brand description is required.',
            'logo.required' => 'A logo image is required.',
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a jpg, jpeg, png, webp or svg file.',
            'logo.max' => 'The logo may not be larger than 2 MB.',
        ];
    }
}
