<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[QueryParameter('search', description: 'Search in name, username or role', type: 'string')]
    #[QueryParameter('role', description: 'Filter by user role (admin, engineer, farmer)', type: 'string')]
    #[QueryParameter('per_page', description: 'Number of items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $users = User::query()
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('username', 'like', "%{$request->search}%");
                    
                $roleEnum = UserRole::fromSearchTerm($request->search);
                if ($roleEnum)
                    $q->orWhere('role', $roleEnum->value);
            })
            ->paginate($perPage);

        return $this->paginatedResponse($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
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
        if ($request->user()->role === UserRole::Engineer && $user->role !== UserRole::Farmer)
            return $this->errorResponse('error', __('api.engineer_edit_farmer_only'), 403);

        if ($user->role === UserRole::Admin && $request->has('role'))
            return $this->errorResponse('error', __('api.cannot_change_admin_role'), 403);

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

        if (Auth::user()->role === UserRole::Engineer && $user->role !== UserRole::Farmer)
            return $this->errorResponse('error', __('api.engineer_delete_farmer_only'), 403);

        $user->delete();
        $user->tokens()->delete();

        return $this->successResponse(__('api.deleted'));
    }
}
