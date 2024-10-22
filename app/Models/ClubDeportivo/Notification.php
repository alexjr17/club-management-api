<?php

namespace App\Models\ClubDeportivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'notificaciones';

    protected $fillable = ['usuario_id', 'mensaje', 'leida'];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public static $rules = [
        'usuario_id' => 'required|exists:usuarios,id',
        'mensaje' => 'required|string',
        'leida' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
