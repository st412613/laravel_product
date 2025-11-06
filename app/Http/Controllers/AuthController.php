<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    // REGISTER
    public function register(UserStoreRequest $request)
    {
        $data = $request->validated();

        // Hash password
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Create token for API auth
        $token = $user->createToken('api-token')->plainTextToken;

          return (new UserResource($user))->additional(['token' => $token]);
    
    }

    // LOGIN
    public function login(UserLoginRequest $request)
    {
        $request->validated();

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('api-token')->plainTextToken;

         return (new UserResource($user))->additional(['token' => $token]);
    
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
