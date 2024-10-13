<?php

namespace App\Models;

use App\Models\ClubDeportivo\Club;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomRole extends Model
{
    use HasFactory;
    protected $table = 'roles_personalizados';

    protected $fillable = ['nombre', 'descripcion', 'club_id'];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'club_id' => 'required|exists:clubes,id',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'rol_usuario', 'rol_personalizado_id', 'usuario_id')
                    ->withPivot('club_id', 'rol_id')
                    ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(CustomPermission::class, 'rol_personalizado_permiso', 'rol_personalizado_id', 'permiso_personalizado_id');
    }
}
