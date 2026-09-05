<?php

namespace Modules\User\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Repositories\LoginRepository;
use Modules\User\Requests\AuthLoginRequest;
use Modules\User\Resources\LoginResource;

class UserController extends Controller
{
    public function __construct(protected LoginRepository $repository) {}

    /**
     * Issue a Sanctum token. Rate limited by the "auth" limiter on the route.
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $result = $this->repository->login($request->validated());

        // The repository returns the HTTP status alongside the payload so the
        // failure reason stays out of the response body itself.
        return response()->json($result, $result['status']);
    }

    /**
     * Revoke only the token used for the current request, so signing out on
     * one device does not sign the user out everywhere.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'You have been signed out.',
            'status' => true,
        ]);
    }

    /**
     * The authenticated account, used by the frontend to restore a session.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new LoginResource($request->user()),
            'status' => true,
        ]);
    }
}
