<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'permisos';

    protected $fillable = ['nombre', 'descripcion', 'referencia', 'modulo', 'acciones', 'es_default'];

    protected $casts = [
        'es_default' => 'boolean',
    ];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'referencia' => 'required|string|max:255|unique:permisos,referencia',
        'modulo' => 'required|string|max:255',
        'acciones' => 'required|string',
        'es_default' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_permiso', 'permiso_id', 'rol_id');
    }
}
