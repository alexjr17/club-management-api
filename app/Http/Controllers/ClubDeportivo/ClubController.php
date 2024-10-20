<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\Club;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Crear un nuevo club
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Club::$rules);
        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'code' => 400], 400);

        DB::beginTransaction();

        try {
            $userId = 1; // Asumiendo que obtienes el ID del usuario autenticado

            $rutaImagen = $this->procesarImagen($request->file('foto'));

            $club = Club::create([
                'usuario_admin_id' => $request->usuario_admin_id,
                'foto' => $rutaImagen,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'direccion' => $request->direccion,
                'barrio' => $request->barrio,
                'nombreUbicacion' => $request->nombreUbicacion,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'fecha_fundacion' => $request->fecha_fundacion,
                'sede_id' => $request->sede_id,
                'ciudad' => $request->ciudad,
                'database_connection' => $request->database_connection,
                'referencia' => $request->referencia,
                // 'latitude' => $request->latitude,
                // 'longitude' => $request->longitude,
            ]);

            if ($club) {
                $userRole = UserRole::create([
                    'usuario_id' => $userId,
                    'rol_id' => 1,
                    'club_id' => $club->id
                ]);
                $userRole->load('role.permissions');

                $role = $userRole->role;
                $permissions = $role->permissions;
            }

            DB::commit();
            return response()->json([
                'club' => $club,
                'rol' => $role,
                'permisos' => $permissions,
                'status' => 'success'
            ], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => 'Ocurrió un error al crear el club.', "message" => $th->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Buscar el club por ID
            $club = Club::findOrFail($id); // Esto lanzará un error si el club no se encuentra

            return response()->json($club, 200);
        } catch (\Throwable $th) {
            LogHelper::LogRegister('error', 'show_club', $id, $th->getMessage() . ' - line: ' . $th->getLine());
            return response()->json(['error' => 'No se pudo encontrar el club.'], 404);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), Club::updateRules($request->club_id));

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 400], 400);
        }

        DB::beginTransaction();

        try {
            $club = Club::findOrFail($request->club_id);

            // Procesar la imagen usando el método existente
            $rutaImagen = $this->procesarImagen(
                $request->hasFile('foto') ? $request->file('foto') : null,
                $club->foto
            );

            // Actualizar los campos del club
            $club->update([
                'foto' => $rutaImagen,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'direccion' => $request->direccion,
                'barrio' => $request->barrio,
                'nombreUbicacion' => $request->nombreUbicacion,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'ciudad' => $request->ciudad,
                'database_connection' => $request->database_connection,
                'referencia' => $request->referencia
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Club actualizado exitosamente',
                'club' => $club,
                'status' => 'success'
            ], 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Club no encontrado", "code" => 404]);
        } catch (\Exception $th) {
            DB::rollBack();
            Log::error('Error al actualizar club: ' . $th->getMessage());
            return response()->json([
                'error' => 'Ocurrió un error al actualizar el club.',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function processFormData($content)
    {
        $data = [];
        $parts = explode("\r\n", $content);
        $key = null;

        foreach ($parts as $part) {
            if (strpos($part, 'name=') !== false) {
                preg_match('/name="([^"]+)"/', $part, $matches);
                $key = $matches[1];
            } elseif ($part !== '' && $key !== null && !strpos($part, '--')) {
                $data[$key] = trim($part);
                $key = null;
            }
        }

        // Procesar la fecha de fundación
        if (isset($data['fecha_fundacion'])) {
            $data['fecha_fundacion'] = date('Y-m-d', strtotime($data['fecha_fundacion']));
        }

        return $data;
    }

    private function procesarImagen($nuevaImagen, $imagenAnterior = null)
    {
        if ($nuevaImagen) {
            // Eliminar la imagen anterior si existe
            if ($imagenAnterior) {
                Storage::disk('public')->delete($imagenAnterior);
            }

            $nombreImagen = time() . '_' . $nuevaImagen->getClientOriginalName();
            return $nuevaImagen->storeAs('clubs', $nombreImagen, 'public');
        }

        return $imagenAnterior;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
