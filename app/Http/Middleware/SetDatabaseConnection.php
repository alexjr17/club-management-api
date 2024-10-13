<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\Models\Club;

class SetDatabaseConnection
{
    public function handle($request, Closure $next)
    {
        $clubId = $request->route('club') ?? $request->input('club_id');
        $user = auth()->user();

        // tengo una relacion con club para tomar la base de datos
        $clud_relacion_id = $user->clubs()->id

        if ($clubId && $clud_relacion_id) {
            $club = Club::findOrFail($clud_relacion_id ?? $clubId);
            $connection = $club->getDatabaseConnection();

            config(['database.default' => $connection]);
            DB::purge($connection);
            DB::reconnect($connection);
        }

        return $next($request);
    }
}
