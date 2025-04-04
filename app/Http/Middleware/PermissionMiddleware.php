<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route_name = Route::currentRouteName();
        if (auth()->check()) {
            if (auth()->user()->role_id == 1) {
                if (auth()->user()?->can($route_name)) {
                    return $next($request);
                }
            } else {
                return $next($request);
            }
        }
        return abort(401);
    }
}
