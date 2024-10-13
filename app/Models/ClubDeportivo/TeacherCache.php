<?php

namespace App\Models\ClubDeportivo;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCache extends Model
{
    use HasFactory;
    protected $table = 'profesor_cache';

    protected $fillable = ['rol_usuario_id', 'calificacion', 'numero_calificaciones', 'filosofia', 'especializaciones'];

    protected $casts = [
        'calificacion' => 'float',
        'numero_calificaciones' => 'integer',
        'last_updated' => 'datetime',
    ];

    public static $rules = [
        'rol_usuario_id' => 'required|exists:rol_usuario,id',
        'calificacion' => 'nullable|numeric|min:0|max:5',
        'numero_calificaciones' => 'nullable|integer|min:0',
        'filosofia' => 'nullable|string',
        'especializaciones' => 'nullable|string',
    ];

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'rol_usuario_id');
    }
}
