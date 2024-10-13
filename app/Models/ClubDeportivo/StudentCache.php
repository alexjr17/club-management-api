<?php

namespace App\Models\ClubDeportivo;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCache extends Model
{
    use HasFactory;
    protected $table = 'alumno_cache';

    protected $fillable = ['rol_usuario_id', 'padre_rol_usuario_id'];

    public static $rules = [
        'rol_usuario_id' => 'required|exists:rol_usuario,id',
        'padre_rol_usuario_id' => 'nullable|exists:rol_usuario,id',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'rol_usuario_id');
    }

    public function parent()
    {
        return $this->belongsTo(UserRole::class, 'padre_rol_usuario_id');
    }
}
