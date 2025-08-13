<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class KepsekMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->check() && $request->user()->role === 'kepala_sekolah') {
            return $next($request);
        }
        
        return response()->json(['message' => 'Akses ditolak'], 403);
    }
}