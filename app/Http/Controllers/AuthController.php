<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail; // Importar la fachada de Mail
use App\Mail\ResetPasswordMail; // Asegúrate de tener este Mail creado
use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\ClubPayment;
use App\Models\ClubDeportivo\Inventory;
use App\Models\ClubDeportivo\Membership;
use App\Models\Role;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $messages = [
            'username.regex' => 'El campo usuario solo puede contener letras, números, guiones bajos y puntos.',
            'username.min' => 'El campo usuario debe tener al menos 3 caracteres.',
            'username.max' => 'El campo usuario no puede tener más de 20 caracteres.',
            'username.unique' => 'El usuario ya está en uso.',
        ];


        $rules = ($request->has('step') && $request->step == 1) ? User::$rulesStep1 : User::$rules; //definir las validades para el step1 y step2

        // $request->validate($rules); //validar campos
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors(), 'status' => 400]);

        if ($request->has('step') && $request->step === 1) {
            return response()->json(['status' => 200, "message" => 'Validation success']);
        };

        // Inicia la transacción
        DB::beginTransaction();

        try {
            $user = User::create([
                'rol_id' => $request->rol_id,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'pais' => $request->pais,
                'ciudad' => $request->ciudad,
                'telefono' => $request->telefono,
                "tipo_documento" => $request->tipo_documento,
                "fecha_nacimiento" => $request->fecha_nacimiento,
                "numero_documento" => $request->numero_documento,
                'usuario' => $request->usuario,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            // Iniciar sesión automáticamente
            auth()->login($user);
            $user_logged = auth()->user();

            $token = JWTAuth::claims(["user_id" => $user->id, "rol_id" => null, "club_id" => null])->fromUser($user_logged);

            // Confirma la transacción
            DB::commit();
            return response()->json(['token' => $token, 'user' => $user_logged], 200);
        } catch (\Throwable $th) {
            // Revierte la transacción si algo falla
            DB::rollBack();
            return response()->json(['errors' => $th->getMessage(), 'status' => 500]);
        }
    }

    public function login(Request $request)
    {
        try {
            DB::beginTransaction();

            $rules = [
                'password' => 'required|string|min:8',
            ];

            if ($request->has('usuario')) {
                $rules['usuario'] = 'required|string';
            } else {
                $rules['email'] = 'required|email';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(["error" => $validator->errors()], 400);
            }

            $credentials = $request->only($request->has('usuario') ? 'usuario' : 'email', 'password');

            if (!auth()->attempt($credentials)) {
                return response()->json(['error' => 'Credenciales incorrectas'], 401);
            }

            $user = auth()->user();

            sleep(1);

            // Cargar las relaciones necesarias en el usuario autenticado
            $userData = $user->load([
                'roles.permissions',
                'clubs',
                'club.inventories',
                'club.memberships.clubPayments'
            ]);


            /*$userData = User::with([ //codigo importante no borrar
                'roles.permissions',
                'clubs',
                'club.inventories',
                'club.memberships.clubPayments'
            ])->findOrFail($user->id);*/

            // Obtener el club principal si existe
            $club = $userData->club->first() ?? null;
            $membresiasConEstadisticas = null;
            $estadisticasGenerales = null;

            if ($club) {
                /*$membresiasConEstadisticas = $club->memberships->map(function ($membresia) { //codigo importante no borrar
                    $pagos = $membresia->clubPayments;
                    return [
                        'id' => $membresia->id,
                        'nombre' => $membresia->nombre,
                        'descripcion' => $membresia->descripcion,
                        'precio' => $membresia->precio,
                        'duracion_dias' => $membresia->duracion_dias,
                        'estadisticas_pagos' => [
                            'total_monto' => $pagos->sum('monto'),
                            'cantidad_pagos' => $pagos->count(),
                            'desglose_por_estado' => $pagos->groupBy('estado')->map(function ($grupo) {
                                return [
                                    'cantidad' => $grupo->count(),
                                    'suma_monto' => $grupo->sum('monto')
                                ];
                            })
                        ],
                    ];
                });*/

                // Función para sumar pagos por estado para todas las membresías
                $sumarPagosPorEstado = function ($estado) use ($club) {
                    return $club->memberships->flatMap->clubPayments
                        ->where('estado', $estado)
                        ->sum('monto');
                };

                $estadisticasGenerales = [
                    'total_pagos_completados' => $sumarPagosPorEstado('completado'),
                    /*'total_pagos_pendientes' => $sumarPagosPorEstado('pendiente'), //codigo importatnte no borrar
                    'total_pagos_fallidos' => $sumarPagosPorEstado('fallido'),*/
                ];
            }

            $club_inventario = $club ? $club->inventories->count() : null;
            // $clubs = $userData->clubs ?? null; //codigo importante no borrar

            $roles = $userData->roles ?? null;
            $rol = $roles->first() ?? null;
            $rol_permiso = $rol ? $rol->permissions->unique() : null;
            // $roles_permisos = $roles ? $roles->flatMap->permissions->unique() : null; //codigo importatnte no borrar

            // return response()->json($rol_permiso);

            $integrantes_club = $club ? $this->integrantes($club->id, $rol->id) : null;
            if ($integrantes_club) {
                $integrantes_club['inventario'] = $club_inventario;
            }

            $token = JWTAuth::claims([
                "user_id" => $user->id,
                "rol_user" => $user->rol_id,
                "club_id" => $club->id ?? null
            ])->fromUser($user);

            DB::commit();

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'token' => $token,
                'user' => auth()->user(),
                'club' => $club,
                // 'clubs' => $clubs,
                // 'roles' => $roles,
                'rol' => $rol,
                'permisos' => $rol_permiso,
                // 'roles_permisos' => $roles_permisos, //codigo importatnte no borrar
                'integrantes_club' => $integrantes_club ?? [],
                // 'membresias_estadisticas' => $membresiasConEstadisticas,
                'estadisticas_generales' => $estadisticasGenerales
            ], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => 'Ocurrió un error durante el inicio de sesión', 'message' => $th->getMessage()], 500);
        }
    }

    public function integrantes($clud_id, $rol_id)
    {
        if ($clud_id && $rol_id) {
            // Obtener la cantidad de integrantes del club específico agrupados por rol
            $integrantes_club = DB::table('roles_usuarios') // Usamos DB::table para acceder directamente a la tabla sin crear modelos
                ->where('club_id', $clud_id) // Filtrar por el ID del club
                ->where('rol_id', '!=', $rol_id)
                ->join('roles', 'roles_usuarios.rol_id', '=', 'roles.id') // Unir con la tabla de roles
                ->groupBy('roles.id', 'roles.nombre') // Agrupar por ID del rol y nombre del rol
                ->selectRaw('roles.nombre as rol, COUNT(*) as count') // Seleccionar el nombre del rol y el conteo de usuarios
                ->get()
                ->pluck('count', 'rol')
                ->toArray();
        }
        $roles_existentes = DB::table('roles')
            ->where('id', '!=', $rol_id ?? '')
            ->select('nombre')
            ->union(
                DB::table('roles_personalizados')
                    ->where('club_id', '=', $clud_id)
                    ->select('nombre')
            )
            ->get()
            ->pluck('nombre')
            ->mapWithKeys(function ($item) {
                return [$item => 0];
            })
            ->toArray();
        // Combinar ambos arrays, dando prioridad a las cantidades de integrantes del club
        return array_merge($roles_existentes, $integrantes_club) ?? [];
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Validar el correo electrónico
        $request->validate(['email' => 'required|email']);

        // Obtener solo la contraseña del usuario
        $password = User::where('email', $request->email)->pluck('password')->first();

        if (!$password) {
            return response()->json(['error' => 'No se encontró ningún usuario con este correo'], 404);
        }

        // Generar una nueva contraseña aleatoria
        // $newPassword = $this->generateRandomPassword();
        $newPassword = "Alexjr17*";

        // Actualizar la contraseña del usuario (asegúrate de encriptarla)
        User::where('email', $request->email)->update(['password' => bcrypt($newPassword)]);

        // Enviar la nueva contraseña por correo electrónico
        Mail::to($request->email)->send(new ResetPasswordMail($newPassword));

        return response()->json(['message' => 'La nueva contraseña ha sido enviada al correo electrónico.'], 200);
    }

    public function generateRandomPassword($length = 12)
    {
        if ($length < 8) {
            throw new Exception("La longitud mínima de la contraseña debe ser de 8 caracteres.");
        }

        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*()-_=+[]{};:,.<>?';

        // Asegurar que la contraseña tenga al menos una mayúscula, un número y un carácter especial
        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)]; // 1 letra mayúscula
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)]; // 1 letra minúscula
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)]; // 1 letra minúscula
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)]; // 1 letra minúscula
        $password .= $numbers[rand(0, strlen($numbers) - 1)]; // 1 número
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)]; // 1 carácter especial

        // Rellenar el resto de la contraseña
        for ($i = 6; $i < $length; $i++) {
            $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        }

        // Barajar la contraseña para mezclar los caracteres
        $password = str_shuffle($password);

        return $password;
    }

    // Método para restablecer la contraseña
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Intentar restablecer la contraseña
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        // Responder según el resultado
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['error' => __($status)], 400);
    }
}
