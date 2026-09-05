<?php

namespace Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Settings\Requests\BulkUpdateSettingsRequest;
use Modules\Settings\Requests\UpdateSettingRequest;
use Modules\Settings\Services\SettingService;
use Modules\Settings\Services\SettingsSchema;

/**
 * Admin CRUD for site settings. Every route is behind auth:sanctum + admin.
 */
class SettingsController extends Controller
{
    public function __construct(protected SettingService $service) {}

    /**
     * All groups, plus the schema so the editor can render controls for
     * fields it does not hard-code.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->all(),
            'groups' => SettingsSchema::groups(),
            'status' => true,
        ]);
    }

    public function show(string $key): JsonResponse
    {
        if (! SettingsSchema::isGroup($key)) {
            return response()->json([
                'message' => 'The settings key is not valid.',
                'status' => false,
            ], 404);
        }

        return response()->json([
            'data' => $this->service->find($key),
            'status' => true,
        ]);
    }

    /**
     * Save one group. POST and PUT share this handler; the request folds the
     * URL segment into the payload when present.
     */
    public function store(UpdateSettingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $saved = $this->service->set($validated['key'], $validated['value']);

        return response()->json([
            'message' => 'Settings saved successfully.',
            'data' => $saved,
            'status' => true,
        ]);
    }

    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        return $this->store($request);
    }

    /**
     * Save an entire tab (several groups) in one request.
     */
    public function bulk(BulkUpdateSettingsRequest $request): JsonResponse
    {
        $saved = $this->service->setMany($request->validated()['settings']);

        return response()->json([
            'message' => 'Settings saved successfully.',
            'data' => $saved,
            'status' => true,
        ]);
    }

    /**
     * Restore a group to the values shipped in the module config.
     */
    public function reset(string $key): JsonResponse
    {
        if (! SettingsSchema::isGroup($key)) {
            return response()->json([
                'message' => 'The settings key is not valid.',
                'status' => false,
            ], 404);
        }

        return response()->json([
            'message' => 'Settings restored to their defaults.',
            'data' => $this->service->reset($key),
            'status' => true,
        ]);
    }

    public function destroy(string $key): JsonResponse
    {
        $this->service->delete($key);

        return response()->json([
            'message' => 'Settings deleted.',
            'status' => true,
        ]);
    }
}
