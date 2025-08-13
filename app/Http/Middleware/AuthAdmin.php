<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthAdmin
{
    public function handle($request, Closure $next)
    {
        // Cek token valid di guard admin
        if (!Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Akses ditolak, hanya admin yang bisa akses'], 403);
        }

        return $next($request);
    }
}
