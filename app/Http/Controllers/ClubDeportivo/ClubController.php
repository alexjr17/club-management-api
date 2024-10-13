<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\Club;
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
        $validator = Validator::make($request->all(), array_merge(Club::$rules, Club::$rulesImagen));
        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'code' => 400]);

        // Iniciar una transacción
        DB::beginTransaction();

        try {
            // Obtener el ID del usuario autenticado
            $userId = auth()->id() ?? 1;

            // Crear el club
            $club = Club::createsd([
                'nombre' => $request->nombre,
                'ciudad' => $request->ciudad,
                'direccion' => $request->direccion,
                'correo' => $request->correo,
                'user_id' => $userId, // Asignar el usuario autenticado
                'telefono' => $request->telefono,
                // 'foto' => $request->file('foto')->store('imagenes_clubes', 'public'), // Guardar la imagen
            ]);

            // Confirmar la transacción
            DB::commit();
            return response()->json($club, 201);
        } catch (\Exception $th) {
            DB::rollBack();

            LogHelper::LogRegister('error_store', 'Club', 0, $th->getMessage() . ' - line: ' . $th->getLine());
            // Retornar un mensaje de error
            return response()->json(['error' => 'Ocurrió un error al crear el club.'], 500);
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
