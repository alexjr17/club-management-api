<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\TeacherCache;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Psy\Output\Theme;

use function PHPSTORM_META\type;

class TeacherCacheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    public function showByClub(Request $request, int $clubId)
    {
        $limit = $request->input("per_page") ?? 12;
        $sortType = $request->has('ascending') ? ($request->input('ascending') == 1 ? 'asc' : 'desc') : 'asc';
        $search = $request->input("query");
        $is_active = $request->input("is_active");
        $date = $request->input("date");
        $especialidad = $request->input("especialidad");
        $calificacionMinima = $request->input("calificacion_minima");

        $query = UserRole::with(['user', 'teacherCache'])
            ->whereHas('role', function ($query) {
                $query->where('nombre', 'profesor');
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
                $queryBuilder->where('created_at', '>=' ,$date);
            });
        }

        // Filtro por especialidad
        // if (!empty($especialidad)) {
        //     $query->whereHas('teacherCache', function ($q) use ($especialidad) {
        //         $q->whereJsonContains('especializaciones', $especialidad);
        //     });
        // }

        // Filtro por calificación mínima
        if (!is_null($calificacionMinima)) {
            $query->whereHas('teacherCache', function ($q) use ($calificacionMinima) {
                $q->where('calificacion', '>=', $calificacionMinima);
            });
        }

        // Ordenamiento
        if ($request->has("orderBy")) {
            $orderBy = $request->orderBy;
            if (in_array($orderBy, ['nombre', 'apellido', 'cedula'])) {
                $query->orderBy(User::select($orderBy)
                    ->whereColumn('usuarios.id', 'roles_usuarios.usuario_id')
                    ->limit(1), $sortType);
            } elseif ($orderBy === 'calificacion') {
                $query->orderBy(TeacherCache::select('calificacion')
                    ->whereColumn('profesor_cache.rol_usuario_id', 'roles_usuarios.id')
                    ->limit(1), $sortType);
            } else {
                $query->orderBy($orderBy, $sortType);
            }
        } else {
            $query->orderBy('created_at', $sortType);
        }

        $profesores = $query->paginate($limit);

        return $profesores;
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
    /**
     * Update teacher and related user information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {

        // return response()->json($request->all());
        try {
            // Validate required parameters first
            $validated = $request->validate([
                'club_id' => 'required|integer',
                'id' => 'required|integer'
            ]);

            // Find the role user with eager loaded relationships
            $roleUser = UserRole::with(['user', 'teacherCache'])
                ->where('club_id', $validated['club_id'])
                ->where('id', $validated['id'])
                ->firstOrFail();

            // Validate all input data at once
            $request->validate(
                array_merge(
                    TeacherCache::$rules,
                    User::updateRules($roleUser->usuario_id)
                )
            );

            return DB::transaction(function () use ($request, $roleUser) {
                // Datos para el usuario
                $userData = $request->except([
                    'especializaciones',
                    'filosofia',
                    'club_id',
                    'id'
                ]);

                if ($request->file('foto')) {
                    $rutaImagen = $this->procesarImagen($request->file('foto'));
                    $userData['foto'] = $rutaImagen;
                }

                // Actualizar datos del usuario
                $roleUser->user->update($userData);

                // Datos para teacherCache
                $teacherData = $request->only([
                    'especializaciones',
                    'filosofia',
                ]);

                // Actualizar datos de teacherCache
                $roleUser->teacherCache->update($teacherData);

                // Recargar las relaciones para obtener los datos actualizados
                $roleUser->load(['user', 'teacherCache']);

                return response()->json([
                    'message' => 'Profedor actualizado correctamente',
                    'data' => [
                        'teacher' => $roleUser->teacherCache,
                    ]
                ], 200);
            });
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Club o usuario no encontrado',
                'code' => 404
            ], 404);
        } catch (\Exception $e) {
            Log::error('Teacher update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

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

                // Encontrar el registro con las relaciones cargadas (user y teacherCache)
                $roleUser = UserRole::with(['user', 'teacherCache'])
                    ->where('club_id', $request->club_id)
                    ->where('id', $request->id)
                    ->where('usuario_id', $request->usuario_id)
                    ->firstOrFail();

                // return response()->json($roleUser);
                // Eliminar el registro relacionado (teacherCache y user)
                if ($roleUser->teacherCache) {
                    $roleUser->teacherCache->delete();
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
