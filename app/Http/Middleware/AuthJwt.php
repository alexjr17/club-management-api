<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthJwt
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar el token y obtener el usuario
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            // Si no se puede obtener el token, responder con un error
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // Permitir la solicitud al siguiente middleware
        return $next($request);
    }
}
