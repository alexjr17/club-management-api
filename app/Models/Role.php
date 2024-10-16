<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['nombre', 'descripcion', 'es_default'];

    protected $casts = [
        'es_default' => 'boolean',
    ];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'es_default' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'roles_usuarios', 'rol_id', 'usuario_id')
                    ->withPivot('club_id', 'rol_personalizado_id')
                    ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }
}
