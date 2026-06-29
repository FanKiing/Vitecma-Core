<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTechnicianRole
{
    /**
     */
    public function handle(Request $request, Closure $next): Response
    {
        // السماح فقط لمن يملك دور admin بالمرور للعمليات الحساسة
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        if ($request->ajax()) {
            return response()->json(['error' => 'Action non autorisée.'], 403);
        }

        abort(403, 'Vous n\'avez pas les permissions nécessaires.');
    }
}