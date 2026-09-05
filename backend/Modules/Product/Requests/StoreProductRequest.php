<?php

namespace Modules\Product\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
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

    /**
     * The admin form posts multipart/form-data, so structured values arrive
     * as JSON strings and booleans as "true"/"1". Normalise both before the
     * rules run.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('attributes'))) {
            $decoded = json_decode((string) $this->input('attributes'), true);

            if (is_array($decoded)) {
                $this->merge(['attributes' => $decoded]);
            }
        }

        $this->merge([
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            'is_new' => filter_var($this->input('is_new', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],

            // Product copy is long-form; 255 was far too tight for a real
            // description and rejected legitimate content.
            'description' => ['required', 'string', 'max:20000'],

            // Upper bounds only: the recommended ~60/~160 SEO lengths are a
            // guideline shown in the admin UI, not a hard rule.
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'brand_id' => ['required', 'uuid', 'exists:brands,id'],

            'is_active' => ['boolean'],
            'is_new' => ['boolean'],

            'featured_img' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['file', 'mimes:jpg,jpeg,png,webp,svg,mp4', 'max:20480'],

            'attributes' => ['nullable', 'array', 'max:40'],
            'attributes.*.key' => ['required', 'string', 'max:255', 'distinct'],
            'attributes.*.value' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'name.unique' => 'A product with this name already exists.',
            'description.required' => 'The product description is required.',
            'category_id.required' => 'Please choose a category.',
            'category_id.exists' => 'The selected category is not valid.',
            'brand_id.required' => 'Please choose a brand.',
            'brand_id.exists' => 'The selected brand is not valid.',
            'featured_img.required' => 'A featured image is required.',
            'featured_img.max' => 'The featured image may not be larger than 5 MB.',
            'gallery.max' => 'You may upload at most 12 gallery files.',
            'gallery.*.max' => 'Each gallery file may not be larger than 20 MB.',
            'attributes.*.key.required' => 'Every attribute needs a name.',
            'attributes.*.key.distinct' => 'Attribute names must be unique.',
            'attributes.*.value.required' => 'Every attribute needs a value.',
        ];
    }
}
