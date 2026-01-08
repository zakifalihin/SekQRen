<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\URL;

use Closure;

use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {   
        if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
        URL::forceScheme('https');
    }
    }
}

