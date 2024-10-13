<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;


    use SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'foto', 'nombre', 'apellido', 'email', 'password', 'estado',
        'nombre_usuario', 'telefono', 'ciudad', 'tipo_documento',
        'numero_documento', 'tutorial'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'estado' => 'string',
        'tipo_documento' => 'string',
        'tutorial' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $rules = [
        'foto' => 'nullable|image|max:2048',
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:usuarios,email',
        'password' => 'required|string|min:8',
        'estado' => 'required|string',
        'nombre_usuario' => 'required|string|max:255|unique:usuarios,nombre_usuario',
        'telefono' => 'nullable|string|max:20',
        'ciudad' => 'nullable|string|max:255',
        'tipo_documento' => 'required|string|max:30',
        'numero_documento' => 'required|string|max:50|unique:usuarios,numero_documento',
        'tutorial' => 'boolean',
    ];

    public static function updateRules($id)
    {
        $rules = self::$rules;
        $rules['email'] = 'sometimes|required|email|max:255|unique:usuarios,email,' . $id;
        $rules['nombre_usuario'] = 'sometimes|required|string|max:255|unique:usuarios,nombre_usuario,' . $id;
        $rules['numero_documento'] = 'sometimes|required|string|max:50|unique:usuarios,numero_documento,' . $id;
        $rules['contrasena'] = 'sometimes|required|string|min:8';
        return $rules;
    }

    public function clubs()
    {
        return $this->hasMany(Club::class, 'usuario_admin_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_usuario', 'usuario_id', 'rol_id')
                    ->withPivot('club_id', 'rol_personalizado_id')
                    ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'usuario_id');
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            "usuario" => $this->usuario,
        ];
    }
}
