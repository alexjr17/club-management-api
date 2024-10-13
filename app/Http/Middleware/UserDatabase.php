<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el nombre de la base de datos del usuario logeado
        $user = auth()->user();

        if ($user && $user->db) {
            // Establece el nombre de la base de datos en la conexión "user_connection"
            Config::set('database.connections.user_connection.database', env('DB_DATABASE', 'forge') ?? $user->db);

            // Establece la conexión activa a la base de datos del usuario
            DB::connection('user_connection')->reconnect();
        }

        // Continúa con la solicitud
        return $next($request);
    }
}
