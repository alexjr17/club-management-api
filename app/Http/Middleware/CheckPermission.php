<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permiso
     * @return mixed
     */
    public function handle($request, Closure $next, $permiso)
    {
        $user = Auth::user();
        // Verificar si el usuario tiene el permiso
        if (!$user || !$user->hasPermission($permiso)) {
            return response()->json(['message' => 'No tienes permiso para realizar esta acción'], 403);
        }

        return $next($request);
    }
}
