<?php

namespace App\Models;

use App\Models\ClubDeportivo\Club;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPermission extends Model
{
    use HasFactory;
    protected $table = 'permisos_personalizados';

    protected $fillable = ['nombre', 'descripcion', 'referencia', 'modulo', 'acciones', 'club_id'];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'referencia' => 'required|string|max:255',
        'modulo' => 'required|string|max:255',
        'acciones' => 'required|string',
        'club_id' => 'required|exists:clubes,id',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function customRoles()
    {
        return $this->belongsToMany(CustomRole::class, 'rol_personalizado_permiso', 'permiso_personalizado_id', 'rol_personalizado_id');
    }
}
