<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\StudentCache;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentCacheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $club_id = $request->club_id;
        $studen = StudentCache::paginate();
        return response()->json($studen);
    }

    public function showByClub(Request $request, int $clubId)
    {
        $limit = $request->input("per_page") ?? 12;
        $sortType = $request->has('ascending') ? ($request->input('ascending') == 1 ? 'asc' : 'desc') : 'asc';
        $search = $request->input("query");
        $is_active = $request->input("is_active");
        $date = $request->input("date");

        $query = UserRole::with([
            'user',
            'studentCache.parent.user'
        ])
            ->whereHas('role', function ($query) {
                $query->where('nombre', 'alumno');
            })
            ->where('club_id', $clubId);

        if (!empty($search)) {
            $query->whereHas('user', function ($queryBuilder) use ($search) {
                $queryBuilder->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if (!empty($is_active) && ($is_active == 'true' || $is_active == 'false')) {
            $query->whereHas('user', function ($queryBuilder) use ($is_active) {
                $queryBuilder->where('estado', $is_active == 'true' ? 1 : 0);
            });
        }

        if (!empty($date)) {
            $query->whereHas('user', function ($queryBuilder) use ($date) {
                $queryBuilder->where('created_at', '>=', $date);
            });
        }

        // Ordenamiento
        if ($request->has("orderBy")) {
            $orderBy = $request->orderBy;
            if (in_array($orderBy, ['nombre', 'apellido', 'cedula'])) {
                $query->orderBy(User::select($orderBy)
                    ->whereColumn('usuarios.id', 'roles_usuarios.usuario_id')
                    ->limit(1), $sortType);
            } else {
                $query->orderBy($orderBy, $sortType);
            }
        } else {
            $query->orderBy('created_at', $sortType);
        }

        $parents = $query->paginate($limit);

        return response()->json($parents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), array_merge(StudentCache::$rules, User::$rules));
        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'code' => 400], 400);
        // $request->validate(array_merge(/*StudentCache::$rules,*/User::$rules)); //validar campos


        DB::beginTransaction();
        try {

            // $rutaImagen = $this->procesarImagen($request->file('foto'));
            $request_user = $request->only(['nombre', 'apellido', 'tipo_documento', 'numero_documento', 'email']);

            // $request_user['foto'] = $rutaImagen;
            $request_user['password'] = Hash::make($request_user['numero_documento']);

            $user = User::create($request_user);


            $roles_user = UserRole::create([
                'usuario_id' => $user->id,
                'rol_id' => 3, //rol 2 para crear profesor
                'club_id' => $request->club_id
            ]);

            $request_Teacher = $request->only(['padre_rol_usuario_id', 'rol_usuario_id']);
            $request_Teacher["padre_rol_usuario_id"] = 62;
            $request_Teacher["rol_usuario_id"] = $roles_user->id;
            $profesor_cache = StudentCache::create($request_Teacher);

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
    public function show(StudentCache $studentCache)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            // Validate required parameters first
            $validated = $request->validate([
                'club_id' => 'required|integer',
                'id' => 'required|integer'
            ]);

            // Find the role user with eager loaded relationships
            $roleUser = UserRole::with(['user', 'studentCache'])
                ->where('club_id', $validated['club_id'])
                ->where('id', $validated['id'])
                ->firstOrFail();

            // Validate all input data at once
            $request->validate(
                array_merge(
                    // StudentCache::$rules,
                    User::updateRules($roleUser->usuario_id)
                )
            );

            return DB::transaction(function () use ($request, $roleUser) {
                // Datos para el usuario
                $userData = $request->except([
                    'id'
                ]);

                if ($request->file('foto')) {
                    $rutaImagen = $this->procesarImagen($request->file('foto'));
                    $userData['foto'] = $rutaImagen;
                }

                // Actualizar datos del usuario
                $roleUser->user->update($userData);

                // Datos para studentCache
                // $teacherData = $request->only([
                //     'especializaciones',
                //     'filosofia',
                // ]);

                // Actualizar datos de studentCache
                // $roleUser->studentCache->update($teacherData);

                // Recargar las relaciones para obtener los datos actualizados
                $roleUser->load(['user', 'studentCache']);

                return response()->json([
                    'message' => 'Estudiante actualizado correctamente',
                    'data' => [
                        'teacher' => $roleUser->studentCache,
                    ]
                ], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Club o usuario no encontrado',
                'code' => 404
            ], 404);
        } catch (\Exception $e) {
            // Log::error('Teacher update failed:', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            //     'request_data' => $request->all()
            // ]);

            return response()->json([
                'message' => 'Error al actualizar los datos',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            // Validar los parámetros recibidos
            $request->validate([
                'club_id' => 'required|integer',
                'id' => 'required|integer',
                'usuario_id' => 'required|integer',
            ]);


            // Usar transacción para manejar la eliminación de datos
            return DB::transaction(function () use ($request) {

                // Encontrar el registro con las relaciones cargadas (user y studentCache)
                $roleUser = UserRole::with(['user', 'studentCache'])
                    ->where('club_id', $request->club_id)
                    ->where('id', $request->id)
                    ->where('usuario_id', $request->usuario_id)
                    ->firstOrFail();

                return response()->json($roleUser);
                // Eliminar el registro relacionado (studentCache y user)
                if ($roleUser->studentCache) {
                    $roleUser->studentCache->delete();
                }

                if ($roleUser->user) {
                    $roleUser->user->delete();
                }

                // Eliminar el roleUser después de borrar las relaciones
                $roleUser->delete();

                // Commit implícito de la transacción si todo va bien
                return response()->json(['message' => 'Registro eliminado correctamente', 'code' => 200], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Registro no encontrado", "code" => 404]);
        } catch (\Exception $th) {
            // Capturar cualquier otro error
            return response()->json([
                'message' => "No se pudo procesar la solicitud, intente de nuevo",
                'error' => $th->getMessage(),
                'code' => 500
            ], 500);
        }
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
