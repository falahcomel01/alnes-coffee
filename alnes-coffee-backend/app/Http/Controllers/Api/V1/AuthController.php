<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                email: $request->email,
                password: $request->password,
                deviceName: $request->input('device_name', 'web')
            );

            return $this->createdResponse(
                data: new AuthResource($result['user'], $result['token']),
                message: 'Login berhasil!'
            );
        } catch (AuthenticationException $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->noContentResponse('Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            data: new AuthResource($request->user()),
            message: 'Data user berhasil diambil.'
        );
    }
}