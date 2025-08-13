<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $user = $request->user();
        if (!$user || $user->role !== $role) {
            return response()->json(['message' => 'Akses ditolak, hanya role '.$role.' yang bisa akses'], 403);
        }
        return $next($request);
    }
}
