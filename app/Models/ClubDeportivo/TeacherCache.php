<?php

namespace App\Models\ClubDeportivo;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TeacherCache extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'profesor_cache';

    protected $fillable = [
        'rol_usuario_id',
        'calificacion',
        'numero_calificaciones',
        'filosofia',
        'especializaciones'
    ];

    protected $casts = [
        'especializaciones' => 'json',
        'calificacion' => 'float',
        'numero_calificaciones' => 'integer',
    ];

    public static $rules = [
        'especializaciones' => 'nullable|json', // Reglas para array o JSON
        'calificacion' => 'nullable|numeric|min:0|max:5',
        'numero_calificaciones' => 'nullable|integer|min:0',
        'filosofia' => 'required|string',
    ];

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'rol_usuario_id');
    }
}
