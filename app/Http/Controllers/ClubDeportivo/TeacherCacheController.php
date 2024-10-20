<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\TeacherCache;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\type;

class TeacherCacheController extends Controller
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
    public function store(Request $request)
    {

        // $validator = Validator::make($request->all(), array_merge(TeacherCache::$rules, User::$rules));
        // if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'code' => 400], 400);
        $request->validate(array_merge(TeacherCache::$rules, User::$rules)); //validar campos

        DB::beginTransaction();

        try {

            $rutaImagen = $this->procesarImagen($request->file('foto'));
            $request_user = $request->only(['nombre', 'apellido', 'tipo_documento', 'numero_documento', 'email']);

            $request_user['foto'] = $rutaImagen;
            $request_user['password'] = Hash::make($request_user['numero_documento']);

            // return response()->json($request_user);
            $user = User::create($request_user);


            $roles_user = UserRole::create([
                'usuario_id' => $user->id,
                'rol_id' => 2, //rol 2 para crear profesor
                'club_id' => $request->club_id
            ]);

            $request_Teacher = $request->only(['filosofia', 'especializaciones']);
            $request_Teacher["rol_usuario_id"] = $roles_user->id;
            $profesor_cache = TeacherCache::create($request_Teacher);

            DB::commit();
            return response()->json([
                'user' => $user,
                'roles_user' => $roles_user,
                'profesor_cache' => $profesor_cache,
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
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function procesarImagen($nuevaImagen, $imagenAnterior = null)
    {
        if ($nuevaImagen) {
            // Eliminar la imagen anterior si existe
            if ($imagenAnterior) {
                Storage::disk('public')->delete($imagenAnterior);
            }

            $nombreImagen = time() . '_' . $nuevaImagen->getClientOriginalName();
            return $nuevaImagen->storeAs('teachers', $nombreImagen, 'public');
        }

        return $imagenAnterior;
    }
}
