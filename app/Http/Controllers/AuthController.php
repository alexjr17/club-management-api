<?php

namespace App\Http\Controllers;

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
use Exception;

class AuthController extends Controller
{
    //
    public function register (Request $request) {
        // return response()->json(['alex'], 200);

        $rules = [
            "rol_id" => 'required',
            "nombre" => 'required|min:3|max:20',
            "apellido" => 'required',
            // "foto" => 'required',
            "tipo_documento" => 'required',
            "numero_documento" => 'required',
            "usuario" => [
                'required',
                'min:3',
                'max:20',
                'unique:users,usuario',
                'regex:/^[a-zA-Z0-9-_\.]+$/',
            ],
            "email" => 'required|email|unique:users,email',
            "password" => 'required|min:8',
            // "telefono" => 'require',
            // "direccion" => 'require',
            // "imagen" => 'require',
        ];
        // return response()->json($request->all());

        $messages = [
            'username.regex' => 'El campo usuario solo puede contener letras, números, guiones bajos y puntos.',
            'username.min' => 'El campo usuario debe tener al menos 3 caracteres.',
            'username.max' => 'El campo usuario no puede tener más de 20 caracteres.',
            'username.unique' => 'El usuario ya está en uso.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'status' => 400], 200);
        }


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

        auth()->login($user);
        $user_logged = auth()->user();

        $token = JWTAuth::claims(["user_id" => $user->id, "rol_user" => $user->rol_id])->fromUser($user_logged);

        return response()->json(['token' => $token, 'user' => $user_logged], 201);

    }

    public function login(Request $request) {

        $rules = [ 'password' => 'required|min:8'];
        $request->has('usuario') ? $rules['usuario'] = 'required|string' : $rules['email'] = 'required|email';

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json(["error" => $validator->errors(), 400]);
        }

        //credenciales usaurio y password en el request
        if(!auth()->attempt($request->all())){
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $user_logged = auth()->user();

        $token = JWTAuth::claims(["user_id" => $user_logged->id, "rol_user" => $user_logged->rol_id])->fromUser($user_logged);

        // Retornamos el token si es exitoso
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => auth()->user(),
        ]);
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

    public function generateRandomPassword($length = 12) {
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
