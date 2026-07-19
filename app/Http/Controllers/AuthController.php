<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('username', 'password')))
            return $this->errorResponse('login_failed', __('api.login_failed'), 401);

        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->dataResponse([
            'token' => $token,
            'user' => $user
        ], __('api.login_success'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(__('api.logout_success'));
    }

    public function profile(Request $request)
    {
        return $this->dataResponse($request->user());
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password))
            throw ValidationException::withMessages([
                'current_password' => [__('api.incorrect_password')],
            ]);

        $user->update([
            'password' => $request->new_password,
        ]);

        return $this->successResponse(__('api.password_updated'));
    }
}
