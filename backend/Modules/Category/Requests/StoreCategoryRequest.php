<?php

namespace Modules\Category\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
     * Multipart form data sends booleans as the strings "true"/"false"/"1",
     * which the boolean rule rejects; normalise them before validating.
     */
    protected function prepareForValidation(): void
    {
        foreach (['is_active', 'is_new'] as $flag) {
            if ($this->has($flag)) {
                $this->merge([$flag => filter_var($this->input($flag), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'uuid', 'exists:brands,id'],

            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],

            // Slugs only have to be unique within a brand, matching the
            // composite unique index on (brand_id, slug).
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('categories')->where(fn ($q) => $q->where('brand_id', $this->input('brand_id'))),
            ],

            // A parent must belong to the same brand, otherwise the tree
            // would span two catalogues.
            'parent_id' => [
                'nullable', 'uuid',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('brand_id', $this->input('brand_id'))),
            ],

            'description' => ['nullable', 'string', 'max:5000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'logo' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            'is_active' => ['boolean'],
            'is_new' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required' => 'Please choose a brand.',
            'brand_id.exists' => 'The selected brand is not valid.',
            'name.required' => 'The category name is required.',
            'name.unique' => 'A category with this name already exists.',
            'parent_id.exists' => 'The parent category does not belong to this brand.',
            'slug.unique' => 'This slug is already used by another category of this brand.',
            'logo.image' => 'The selected file must be an image.',
            'logo.mimes' => 'The image must be a jpg, jpeg, png, webp or svg file.',
            'logo.max' => 'The image may not be larger than 2 MB.',
        ];
    }
}
