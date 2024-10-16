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
use App\Models\Role;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\DB;

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

        $request->validate($rules); //validar campos
        // $validator = Validator::make($request->all(), $rules);
        // if ($validator->fails()) return response()->json(['errors' => $validator->errors(), 'status' => 400]);

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
                "tipo_documento" => $request->tipo_documento,
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

            $userData = User::with([
                'roles.permissions', // Cargar los roles del usuario con sus permisos
                'clubs', // Todos los clubes asociados al usuario
                'club' // El club principal donde es administrador
            ])->findOrFail($user->id);

            // Obtener el club principal si existe
            $club = $userData->club->first() ?? null;
            $clubs = $userData->clubs ?? null;
            $roles = $userData->roles ?? null;
            $rol = $roles->first() ?? null;
            $rol_permiso = $rol ? $rol->permissions->unique() : null;
            $roles_permisos = $roles ? $roles->flatMap->permissions->unique() : null;

            // Obtener la cantidad de integrantes del club específico agrupados por rol
            $integrantes_club = DB::table('roles_usuarios') // Usamos DB::table para acceder directamente a la tabla sin crear modelos
                ->where('club_id', $club->id) // Filtrar por el ID del club
                ->join('roles', 'roles_usuarios.rol_id', '=', 'roles.id') // Unir con la tabla de roles
                ->groupBy('roles.id', 'roles.nombre') // Agrupar por ID del rol y nombre del rol
                ->selectRaw('roles.nombre as rol, COUNT(*) as count') // Seleccionar el nombre del rol y el conteo de usuarios
                ->get(); // Obtener los resultados

            return response()->json($integrantes_club);

            $token = JWTAuth::claims([
                "user_id" => $user->id,
                "rol_user" => $user->rol_id,
                "club_id" => $club->id
            ])->fromUser($user);

            DB::commit();

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'token' => $token,
                // 'user' => $userData,
                'user' => auth()->user(),
                'club' => $club,
                'clubs' => $clubs,
                'roles' => $roles,
                'rol' => $rol,
                'permisos' => $rol_permiso,
                'roles_permisos' => $roles_permisos,
                'integrantes_club' => $integrantes_club
            ]);
        } catch (\Exception $th) {
            DB::rollBack();
            // LogHelper::LogRegister('error_Login', 'Club', 0, $th->getMessage() . ' - line: ' . $th->getLine());
            return response()->json(['error' => 'Ocurrió un error durante el inicio de sesión', 'message' => $th->getMessage()], 500);
        }
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
