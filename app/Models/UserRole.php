<?php

namespace App\Models;

use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\ParentCache;
use App\Models\ClubDeportivo\StudentCache;
use App\Models\ClubDeportivo\TeacherCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'roles_usuarios';

    protected $fillable = [
        'usuario_id',
        'rol_id',
        'rol_personalizado_id',
        'club_id'
    ];

    public static $rules = [
        'usuario_id' => 'required|exists:usuarios,id',
        'rol_id' => 'required_without:rol_personalizado_id|exists:roles,id',
        'rol_personalizado_id' => 'required_without:rol_id|exists:roles_personalizados,id',
        'club_id' => 'required|exists:clubes,id',
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function customRole()
    {
        return $this->belongsTo(CustomRole::class, 'rol_personalizado_id');
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function teacherCache()
    {
        return $this->hasOne(TeacherCache::class, 'rol_usuario_id');
    }

    public function studentCache()
    {
        return $this->hasOne(StudentCache::class, 'rol_usuario_id');
    }

    public function parentCache()
    {
        return $this->hasOne(ParentCache::class, 'rol_usuario_id');
    }
}
