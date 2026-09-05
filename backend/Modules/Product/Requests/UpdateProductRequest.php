<?php

namespace Modules\Product\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        if (is_string($this->input('attributes'))) {
            $decoded = json_decode((string) $this->input('attributes'), true);

            if (is_array($decoded)) {
                $this->merge(['attributes' => $decoded]);
            }
        }

        // Only cast flags that were actually sent, so a partial update does
        // not silently switch a product off.
        foreach (['is_active', 'is_new'] as $flag) {
            if ($this->has($flag)) {
                $this->merge([$flag => filter_var($this->input($flag), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }

    public function rules(): array
    {
        // The admin route is /admin/products/{product}; the parameter carries
        // the raw id because the resource route is not model-bound here.
        $productId = $this->route('product');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'name')->ignore($productId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],

            'description' => ['sometimes', 'string', 'max:20000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'category_id' => ['sometimes', 'uuid', 'exists:categories,id'],
            'brand_id' => ['sometimes', 'uuid', 'exists:brands,id'],

            'is_active' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],

            'featured_img' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['sometimes', 'array', 'max:12'],
            'gallery.*' => ['file', 'mimes:jpg,jpeg,png,webp,svg,mp4', 'max:20480'],

            'attributes' => ['nullable', 'array', 'max:40'],
            'attributes.*.key' => ['required_with:attributes', 'string', 'max:255', 'distinct'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A product with this name already exists.',
            'category_id.exists' => 'The selected category is not valid.',
            'brand_id.exists' => 'The selected brand is not valid.',
            'featured_img.max' => 'The featured image may not be larger than 5 MB.',
            'attributes.*.key.distinct' => 'Attribute names must be unique.',
        ];
    }
}
