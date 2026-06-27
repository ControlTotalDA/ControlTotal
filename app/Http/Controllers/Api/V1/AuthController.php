<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Authenticate user and return Sanctum token with user and tenant data.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)
            ->where('active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->load('tenant');
        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
            'tenant' => new TenantResource($user->tenant),
        ], 'Login successful');
    }

    /**
     * Revoke the current access token.
     *
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken && str_contains($bearerToken, '|')) {
            [$id] = explode('|', $bearerToken, 2);
            PersonalAccessToken::query()->whereKey($id)->delete();
        } elseif ($token = PersonalAccessToken::findToken($bearerToken ?? '')) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Return the authenticated user with tenant.
     *
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('tenant');

        return ApiResponse::success(new UserResource($user));
    }
}
