<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\Club;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Validar los datos entrantes
        $validator = Validator::make($request->all(), Club::$rules);
        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'code' => 400], 400);

        // Iniciar una transacción
        DB::beginTransaction();

        try {
            // Obtener el ID del usuario autenticado
            $userId = 1;

            // Manejar la subida de la imagen
            $rutaImagen = null;
            // En el controlador
            if ($request->hasFile('foto')) {
                $imagen = $request->file('foto');
                $nombreImagen = time() . '_' . $request->usuario_admin_id . '.' . $imagen->getClientOriginalExtension();
                // Guarda directamente en el disco público
                $rutaImagen = $imagen->storeAs('clubs', $nombreImagen, 'public');
            }

            // Crear el club con todos los campos
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
                // Carga la relación 'role' en la instancia recién creada
                $userRole->load('role.permissions'); // Carga las relaciones anidadas

                // Ahora puedes acceder a la relación role y sus permisos
                $role = $userRole->role; // Obtiene la relación role
                $permissions = $role->permissions; // Obtiene los permisos del rol
            }

            // Confirmar la transacción
            DB::commit();
            return response()->json([
                'club' => $club,
                'rol' => $role,
                'permisos' => $permissions,
                'status' => 'success'
            ], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            // LogHelper::LogRegister('error_store', 'Club', 0, $th->getMessage() . ' - line: ' . $th->getLine());
            // Retornar un mensaje de error
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
    public function update(Request $request, $id)
    {
        // Validar los datos entrantes
        $validator = Validator::make($request->all(), array_merge(Club::updateRules($id), Club::$rulesImagen), [
            'correo.unique' => 'El correo ya está en uso por otro club.'
        ]);

        if ($validator->fails())  return response()->json(['message' => $validator->errors()->first()], 400);

        DB::beginTransaction(); // Iniciar la transacción

        try {
            $club = Club::findOrFail($id); // Obtener el club por ID

            // Actualizar los datos del club
            $club->update([
                'nombre' => $request->nombre,
                'ciudad' => $request->ciudad,
                'direccion' => $request->direccion,
                'correo' => $request->correo,
                // Solo actualizar la foto si se envió una nueva
                'foto' => $request->file('foto') ? $request->file('foto')->store('imagenes_clubes', 'public') : $club->foto,
            ]);

            DB::commit(); // Confirmar la transacción

            return response()->json($club, 200);
        } catch (\Throwable $th) {
            DB::rollBack(); // Revertir la transacción en caso de error
            LogHelper::LogRegister('error', 'update_club', $id, $th->getMessage() . ' - line: ' . $th->getLine());
            return response()->json(['error' => 'Ocurrió un error al actualizar el club.'], 500);
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
