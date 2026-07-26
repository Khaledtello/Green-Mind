<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return $this->dataResponse($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        return $this->dataResponse($user, __('api.created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->dataResponse($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return $this->dataResponse($user, __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id())
            return $this->errorResponse('error', __('api.cannot_delete_self'), 403);

        $user->delete();
        $user->tokens()->delete();

        return $this->successResponse(__('api.deleted'));
    }
}
