<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPlatform extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'pagos_plataforma';

    protected $fillable = ['suscripcion_id', 'monto', 'fecha_pago', 'estado', 'referencia_pago'];

    protected $casts = [
        'monto' => 'float',
        'fecha_pago' => 'date',
        'estado' => 'string',
    ];

    public static $rules = [
        'suscripcion_id' => 'required|exists:suscripciones,id',
        'monto' => 'required|numeric|min:0',
        'fecha_pago' => 'required|date',
        'estado' => 'required|string|max:255',
        'referencia_pago' => 'required|string|max:255|unique:pagos_plataforma,referencia_pago',
    ];

    public function subscription()
    {
        return $this->belongsTo(Suscription::class, 'suscripcion_id');
    }
}
