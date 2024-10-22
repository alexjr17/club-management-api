<?php

namespace App\Models\ClubDeportivo;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubPayment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'pagos_club';

    protected $fillable = ['membresia_id', 'rol_usuario_id', 'monto', 'fecha_pago', 'estado', 'referencia_pago'];

    protected $casts = [
        'monto' => 'float',
        'fecha_pago' => 'date',
        'estado' => 'string',
    ];

    public static $rules = [
        'membresia_id' => 'required|exists:membresias,id',
        'rol_usuario_id' => 'required|exists:rol_usuario,id',
        'monto' => 'required|numeric|min:0',
        'fecha_pago' => 'required|date',
        'estado' => 'required|string|max:255',
        'referencia_pago' => 'required|string|max:255|unique:pagos_club,referencia_pago',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membresia_id');
    }

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'rol_usuario_id');
    }
}
