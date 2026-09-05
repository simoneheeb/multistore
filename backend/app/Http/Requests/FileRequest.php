<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware (auth:sanctum + admin) already gates this endpoint.
        return true;
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The uploaded file is not valid.',
                'errors' => $validator->errors(),
                'status' => false,
            ], 422)
        );
    }

    /**
     * Strip anything that could escape the upload root before validation
     * runs, so "../../" style paths can never reach the filesystem.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('path')) {
            $this->merge([
                'path' => trim(preg_replace('#[^A-Za-z0-9/_-]#', '', str_replace('..', '', (string) $this->input('path'))), '/'),
            ]);
        }

        if ($this->has('name')) {
            $this->merge([
                'name' => preg_replace('#[^A-Za-z0-9_-]#', '', (string) $this->input('name')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg,mp4', 'max:20480'],
            'name' => ['nullable', 'string', 'max:255'],
            'path' => ['nullable', 'string', 'max:255', 'regex:#^[A-Za-z0-9/_-]*$#'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please choose a file.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'Allowed file types: jpg, jpeg, png, webp, svg or mp4.',
            'file.max' => 'The file may not be larger than 20 MB.',
            'path.regex' => 'The storage path is not valid.',
        ];
    }
}
