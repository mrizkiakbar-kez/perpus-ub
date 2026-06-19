<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('member_id') || (Auth::check() && Auth::user()->role === 'member')) {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Anda harus login sebagai anggota untuk mengakses halaman ini.');
    }
}
