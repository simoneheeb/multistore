<?php

namespace Modules\Brand\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
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
        // The admin resource route parameter is {brand} and carries the id.
        $brandId = $this->route('brand');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brandId)],

            'description' => ['sometimes', 'string', 'max:10000'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            'is_active' => ['sometimes', 'boolean'],
            // The column is is_new; the old rule referenced a non-existent
            // is_featured field, so the value was silently dropped.
            'is_new' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A brand with this name already exists.',
            'slug.unique' => 'This slug is already in use.',
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a jpg, jpeg, png, webp or svg file.',
            'logo.max' => 'The logo may not be larger than 2 MB.',
            'order.integer' => 'The display order must be a whole number.',
        ];
    }
}
