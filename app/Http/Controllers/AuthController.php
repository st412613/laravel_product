<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ✅ REGISTER
    public function register(UserStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Token create with expiry
        $tokenResult = $user->createToken('api-token');
        $token = $tokenResult->accessToken;
        $token->expires_at = now()->addDays(config('sanctum.token_expiry_days', 7)); // configurable
        $token->save();

        return (new UserResource($user))
            ->additional([
                'token' => $tokenResult->plainTextToken,
                'expires_at' => $token->expires_at,
            ])
            ->response()
            ->setStatusCode(201);
    }

   
    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Token create with expiry
        $tokenResult = $user->createToken('api-token');
        $token = $tokenResult->accessToken;
        $token->expires_at = now()->addDays(config('sanctum.token_expiry_days', 7));
        $token->save();

        return (new UserResource($user))
            ->additional([
                'token' => $tokenResult->plainTextToken,
                'expires_at' => $token->expires_at,
            ])
            ->response()
            ->setStatusCode(200);
    }

    
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
