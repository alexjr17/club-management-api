<?php

namespace App\Http\Controllers;

use App\Models\ClubDeportivo\Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConfigController extends Controller
{
    /**
     * Obtener todas las configuraciones de un usuario
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $usuario_id = $request->user()->id ?? $request->user_id;
            $club_id = $request->club_id;

            if (!$usuario_id || !$club_id) {
                return response()->json([
                    'error' => 'Parámetros requeridos',
                    'message' => 'usuario_id y club_id son obligatorios'
                ], 400);
            }

            $configuraciones = Config::where('usuario_id', $usuario_id)
                ->where('club_id', $club_id)
                ->activas()
                ->get();

            if ($configuraciones->isEmpty()) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'No se encontraron configuraciones activas'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $configuraciones->toArray()
            ]);
        } catch (\Throwable $th) {
            Log::error('Error en ConfiguracionController@index: ' . $th->getMessage(), [
                'usuario_id' => $usuario_id ?? null,
                'club_id' => $club_id ?? null,
                'request' => $request->all()
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'error' => 'Internal Server Error',
                    'message' => $th->getMessage()
                ], 500);
            }

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }
    /**
     * Obtener configuración específica por módulo
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $configuracion = Config::findOrFail($id);

            // Verificar que el usuario tiene acceso a esta configuración
            if (
                $configuracion->usuario_id != $request->usuario_id ||
                $configuracion->club_id != $request->club_id
            ) {
                return response()->json([
                    'message' => 'No autorizado para ver esta configuración',
                    'status' => 'error'
                ], 403);
            }

            return response()->json([
                'message' => 'Configuración obtenida exitosamente',
                'status' => 'success',
                'data' => $configuracion
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Error al obtener la configuración',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Guardar múltiples configuraciones
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), Config::$rules, Config::$message);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $configuraciones = collect($request->configuraciones)->map(function ($config) use ($request) {
            return Config::updateOrCreate(
                [
                    'usuario_id' => $request->user()->id,
                    'modulo' => $config['modulo']
                ],
                [
                    'configuracion' => $config['configuracion'],
                    'activo' => true
                ]
            );
        });

        return response()->json([
            'message' => 'Configuraciones guardadas exitosamente',
            'data' => $configuraciones
        ], 201);
    }

    /**
     * Actualizar una configuración específica
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), Config::$rules, Config::$message);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                $configuracionesGuardadas = [];

                foreach ($request->configuraciones as $configuracion) {
                    $modulo = $configuracion['modulo'];
                    $tipo = $configuracion['tipo'];
                    $data = $configuracion['data'];
                    $usuarioId = $request->usuario_id;
                    $clubId = $request->club_id;

                    // Buscar configuración existente
                    $configModel = Config::where('usuario_id', $usuarioId)
                        ->where('modulo', $modulo)
                        ->where('club_id', $clubId)
                        ->first();

                    $dataToSave = [
                        'usuario_id' => $usuarioId,
                        'club_id' => $clubId,
                        'tipo' => $tipo,
                        'modulo' => $modulo,
                        'configuraciones' => $data,
                        'activo' => true
                    ];

                    if ($configModel) {
                        $configModel->fill($dataToSave);
                        $configModel->save();
                        $configuracionesGuardadas[] = $configModel->fresh();
                    } else {
                        $newConfig = new Config($dataToSave);
                        $newConfig->save();
                        $configuracionesGuardadas[] = $newConfig;
                    }

                    // Log para verificar
                    Log::info("Configuración guardada para módulo {$modulo}", [
                        'tipo_enviado' => $tipo,
                        'tipo_guardado' => $configModel ? $configModel->tipo : $newConfig->tipo,
                        'club_id_enviado' => $clubId,
                        'club_id_guardado' => $configModel ? $configModel->club_id : $newConfig->club_id
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Configuraciones actualizadas exitosamente',
                    'status' => 'success',
                    'data' => $configuracionesGuardadas
                ], 200);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if ($e->errorInfo[1] === 1062) {
                    return response()->json([
                        'message' => 'Ya existe una configuración para este usuario y módulo',
                        'error' => 'Registro duplicado',
                        'status' => 'error'
                    ], 409);
                }

                Log::error('Error en base de datos: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            if (isset($e->errorInfo)) {
                Log::error('Database Error: ', $e->errorInfo);
            }
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Error en el proceso de actualización',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }



    /**
     * Eliminar una configuración
     */
    public function destroy(Request $request, string $modulo): JsonResponse
    {
        $configuracion = Config::where('usuario_id', $request->user()->id)
            ->modulo($modulo)
            ->firstOrFail();

        $configuracion->update(['activo' => false]);

        return response()->json([
            'message' => 'Configuración desactivada exitosamente'
        ]);
    }
}
