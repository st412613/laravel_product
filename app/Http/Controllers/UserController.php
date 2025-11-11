<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;



class UserController extends Controller
{
    // Show single user
     public function show(Request $request): UserResource
    {
    
        return new UserResource($request->user());
    
    }

    // Update user (only if it’s the logged-in user)
      public function update(UserUpdateRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return new UserResource($user);
    }

    // Delete the logged-in user
    public function destroy(Request $request): Response
    {
        $user = $request->user();
        $user->delete();

        return response()->noContent();
    }
}

