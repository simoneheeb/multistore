<?php

namespace Modules\Category\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Category\Models\Category;

class UpdateCategoryRequest extends FormRequest
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
    }

    public function rules(): array
    {
        $id = (string) $this->route('id');

        return [
            'brand_id' => ['required', 'uuid', 'exists:brands,id'],

            'name' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($id)],

            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('categories')->ignore($id)
                    ->where(fn ($q) => $q->where('brand_id', $this->input('brand_id'))),
            ],

            'parent_id' => [
                'nullable', 'uuid',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('brand_id', $this->input('brand_id'))),
                // A node can be neither its own parent nor a child of one of
                // its own descendants - either would cut the branch loose
                // from the tree.
                function (string $attribute, mixed $value, \Closure $fail) use ($id) {
                    if (! $value) {
                        return;
                    }

                    if ($value === $id) {
                        $fail('A category cannot be its own parent.');

                        return;
                    }

                    $category = Category::query()->find($id);

                    if ($category && in_array($value, $category->descendantIds(false), true)) {
                        $fail('A category cannot be moved beneath one of its own descendants.');
                    }
                },
            ],

            'description' => ['nullable', 'string', 'max:5000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'logo' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            'is_active' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required' => 'Please choose a brand.',
            'brand_id.exists' => 'The selected brand is not valid.',
            'name.unique' => 'A category with this name already exists.',
            'name.max' => 'The category name may not exceed 255 characters.',
            'slug.unique' => 'This slug is already used by another category of this brand.',
            'parent_id.exists' => 'The parent category does not belong to this brand.',
            'logo.image' => 'The selected file must be an image.',
            'logo.mimes' => 'The image must be a jpg, jpeg, png, webp or svg file.',
            'logo.max' => 'The image may not be larger than 2 MB.',
            'order.integer' => 'The display order must be a whole number.',
        ];
    }
}
