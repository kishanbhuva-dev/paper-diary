<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class isOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Auth::check() && Auth::user()->role == 'owner') {
                return $next($request);
            }else {
                return response()->json(['status'=>false ,'message'=>'Unauthorized']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status'=>false ,'message'=>'Unauthorized']);
        }
    }
}
