<?php

namespace Modules\User\Repositories;

use Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\User\Resources\LoginResource;

class LoginRepository
{
    protected User $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function login(array $validUser)
    {
        try {
            $findUser = $this->model->where('email', $validUser['email'])->first();

            // Use a single generic message for both "no such user" and "wrong password"
            // so we don't leak which one was incorrect.
            if (! $findUser || ! Hash::check($validUser['password'], (string) $findUser->password)) {
                return [
                    'message' => 'Invalid credentials',
                    'status' => 401,
                ];
            }

            if (! $findUser->is_active) {
                return [
                    'message' => 'This account is inactive',
                    'status' => 403,
                ];
            }

            $token = $findUser->createToken('user-token')->plainTextToken;

            return [
                'token' => $token,
                'user' => new LoginResource($findUser),
                'message' => 'Welcome back',
                'status' => 200
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'status' => 500,
            ];
        }
    }

    public function register(array $validUser)
    {
        return $this->model->create([
            'mobile' => $validUser['mobile'],
            'email' => $validUser['email'],
            'password' => Hash::make($validUser['password']),
        ]);
    }
}