<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    use SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'foto', 'nombre', 'apellido', 'email', 'pais', 'ciudad',  'password', 'estado',
        'nombre_usuario', 'telefono', 'ciudad', 'tipo_documento',
        'numero_documento', 'tutorial', 'fecha_nacimiento'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'estado' => 'string',
        'tipo_documento' => 'string',
        'tutorial' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public static $rulesStep1 = [
        // 'foto' => 'nullable|image|max:2048',
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'telefono' => 'required|string|max:20',
        'pais' => 'required|string|max:255',
        'ciudad' => 'required|string|max:255',
        'fecha_nacimiento' => 'required|string|max:30',
        'tipo_documento' => 'required|string|max:30',
        'numero_documento' => 'required|string|max:50|unique:usuarios,numero_documento',
    ];

    public static $rulesStep2 = [
        'email' => 'required|email|max:255|unique:usuarios,email',
        'password' => 'required|string|min:8|confirmed',
    ];

    public static $rules = [
        // 'foto' => 'nullable|image|max:2048',
        'nombre' => 'required|string|max:255',
        'apellido' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:usuarios,email',
        // 'password' => 'required|string|min:8|confirmed',
        'estado' => 'nullable|string',
        // 'nombre_usuario' => 'required|string|max:255|unique:usuarios,nombre_usuario',
        'telefono' => 'nullable|string|max:20',
        'pais' => 'nullable|string|max:255',
        'ciudad' => 'nullable|string|max:255',
        'tipo_documento' => 'required|string|max:30',
        'fecha_nacimiento' => 'required|string|max:30',
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

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        $valida_caracter = strpos($this->foto, '_photo') !== false;
        if ($this->foto && $valida_caracter) {
            return asset(Storage::url($this->foto));
        }
        return $this->foto;
    }

    public function club()
    {
        return $this->hasMany(Club::class, 'usuario_admin_id');
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'roles_usuarios', 'usuario_id', 'club_id')
                    ->withPivot('rol_id', 'rol_personalizado_id')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_usuarios', 'usuario_id', 'rol_id')
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
