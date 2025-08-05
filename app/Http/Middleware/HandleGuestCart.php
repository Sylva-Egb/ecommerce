<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleGuestCart
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() && !$request->hasCookie('guest_cart_id')) {
            $response = $next($request);
            return $response->cookie('guest_cart_id', uniqid(), 60*24*30);
        }
        
        return $next($request);
    }
}