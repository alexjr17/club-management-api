<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_logs'; // Cambia esto si tu tabla tiene un nombre diferente
    protected $primaryKey = 'id'; // Clave primaria

    protected $fillable = [
        'user_id',
        'accion_id',
        'accion',
        'modulo',
        'detalles',
        'ip_address',
        'user_agent',
        'mobile',
    ];

    /**
     * Las reglas de validación para crear un log.
     */
    static $rules = [
        'user_id' => 'required|exists:users,id', // Asegúrate de que la tabla de usuarios existe
        'accion' => 'required|string|max:255',
        'modulo' => 'required|string|max:255',
        'detalles' => 'nullable|string',
        'ip_address' => 'required|ip',
        'user_agent' => 'required|string',
        'mobile' => 'required|boolean',
    ];
}
