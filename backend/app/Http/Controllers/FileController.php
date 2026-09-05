<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;

/**
 * Thin HTTP layer over FileService for the admin media endpoints.
 */
class FileController extends Controller
{
    public function __construct(protected FileService $files) {}

    public function upload(FileRequest $request): JsonResponse
    {
        return response()->json(
            $this->files->upload($request) + ['status' => true],
            201
        );
    }

    public function update(FileRequest $request): JsonResponse
    {
        return response()->json(
            $this->files->update($request) + ['status' => true]
        );
    }
}
