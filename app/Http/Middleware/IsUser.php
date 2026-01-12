<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Auth::check() && Auth::user()->role == 'user') {
                return $next($request);
            }

            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }
    }
}
