<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user)
            return $this->errorResponse(
                message: __('api.unauthenticated'),
                code: 401,
            );

        if (!in_array($user->role->value, $roles))
            return $this->errorResponse(
                message: __('api.unauthorized'),
                code: 403,
            );

        return $next($request);
    }
}
