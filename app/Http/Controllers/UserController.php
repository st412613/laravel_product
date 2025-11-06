<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
     
    // Store new user (hash password)
    public function store(UserStoreRequest $request): UserResource
    {
        $data = $request->validated();

        // Hash the password before saving
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = User::create($data);
        return new UserResource($user);
    }

    // Show single user
    public function show(Request $request, User $user): UserResource
    {
        return new UserResource($user);
    }

    // Update user (hash password if present)
    public function update(UserUpdateRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        // Hash the password if it exists in the request
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return new UserResource($user);
    }

    // Delete user
    public function destroy(Request $request, User $user): Response
    {
        $user->delete();
        return response()->noContent();
    }
}
