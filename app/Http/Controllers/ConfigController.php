<?php

namespace App\Http\Controllers;

use App\Models\ClubDeportivo\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = Config::query();

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        return response()->json($query->paginate(10));
    }

    public function show(Config $config)
    {
        return response()->json($config);
    }

    public function store(Request $request)
    {
        // Validación básica
        $validator = Validator::make($request->all(), Config::$rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validación específica por tipo y módulo
        $specificRules = Config::customValidationRules($request->tipo, $request->modulo);
        if (!empty($specificRules)) {
            $validator = Validator::make($request->all(), $specificRules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        $config = Config::create($request->all());
        return response()->json($config, 201);
    }

    public function update(Request $request, Config $config)
    {
        // Validación básica
        $validator = Validator::make($request->all(), Config::$rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validación específica
        $specificRules = Config::customValidationRules($request->tipo, $request->modulo);
        if (!empty($specificRules)) {
            $validator = Validator::make($request->all(), $specificRules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }

        $config->update($request->all());
        return response()->json($config);
    }

    public function destroy(Config $config)
    {
        $config->delete();
        return response()->json(null, 204);
    }

    public function getUserConfig(Request $request)
    {
        $config = Config::where('usuario_id', $request->user()->id)
            ->where('tipo', 'usuario')
            ->first();

        if (!$config) {
            $config = new Config([
                'tipo' => 'usuario',
                'modulo' => 'sistema',
                'config' => Config::getDefaultConfig('usuario', 'sistema')
            ]);
        }

        return response()->json($config);
    }

    public function getClubConfig(Request $request, $clubId)
    {
        // Verificar si el usuario tiene permisos para ver configes del club
        $config = Config::where('club_id', $clubId)
            ->where('tipo', 'admin')
            ->get();

        return response()->json($config);
    }
}
