<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return $this->dataResponse($users);
    }

    public function store(CreateUserRequest $request)
    {
        $user = User::create($request->all());
        return $this->dataResponse($user, __('api.created'), 201);
    }

    public function show(User $user)
    {
        return $this->dataResponse($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        return $this->dataResponse($user, __('api.updated'));
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id())
            return $this->errorResponse('error', __('api.cannot_delete_self'), 403);

        $user->delete();
        $user->tokens()->delete();

        return $this->successResponse(__('api.deleted'));
    }
}
