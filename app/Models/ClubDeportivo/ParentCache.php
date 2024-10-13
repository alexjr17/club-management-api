<?php

namespace App\Models\ClubDeportivo;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCache extends Model
{
    use HasFactory;
    protected $table = 'padre_cache';

    protected $fillable = ['rol_usuario_id'];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    public static $rules = [
        'rol_usuario_id' => 'required|exists:rol_usuario,id',
    ];

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'rol_usuario_id');
    }
}
