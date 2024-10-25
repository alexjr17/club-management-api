<?php

namespace App\Models\ClubDeportivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Config extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'usuario_id',
        'club_id',
        'tipo',
        'modulo',
        'configuracion',
        'activo'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean'
    ];

    // Reglas de validación
    public static $rules = [
        'usuario_id' => 'nullable|exists:usuarios,id',
        'club_id' => 'nullable|exists:clubs,id',
        'tipo' => 'required|in:admin,usuario',
        'modulo' => 'required|in:sistema,pagos,clases,notificaciones',
        'configuracion' => 'required|json',
        'activo' => 'boolean'
    ];

    // Validaciones personalizadas
    public static function customValidationRules($tipo, $modulo)
    {
        $rules = [];

        if ($tipo === 'admin') {
            switch ($modulo) {
                case 'pagos':
                    $rules['configuracion.notificaciones.dias_previos_vencimiento'] = 'required|integer|min:1|max:30';
                    $rules['configuracion.notificaciones.dias_notificacion_vencido'] = 'required|integer|min:1|max:30';
                    $rules['configuracion.notificaciones.max_recordatorios'] = 'required|integer|min:1|max:10';
                    break;
                case 'clases':
                    $rules['configuracion.asistencia.tolerancia_minutos'] = 'required|integer|min:0|max:60';
                    $rules['configuracion.reservas.dias_anticipacion'] = 'required|integer|min:1|max:30';
                    break;
            }
        }

        return $rules;
    }

    // Configuraciones por defecto
    public static function getDefaultConfig($tipo, $modulo)
    {
        $defaults = [
            'usuario' => [
                'sistema' => [
                    'interfaz' => [
                        'modo_oscuro' => false,
                        'tamano_fuente' => 'normal',
                        'idioma' => 'es',
                        'mostrar_tutorial' => true
                    ],
                    'notificaciones' => [
                        'email' => true,
                        'push' => false
                    ]
                ]
            ],
            'admin' => [
                'pagos' => [
                    'notificaciones' => [
                        'dias_previos_vencimiento' => 3,
                        'dias_notificacion_vencido' => 1,
                        'max_recordatorios' => 3
                    ],
                    'politicas' => [
                        'permitir_pagos_parciales' => true,
                        'dias_gracia' => 5,
                        'porcentaje_mora' => 5
                    ]
                ]
            ]
        ];

        return $defaults[$tipo][$modulo] ?? [];
    }

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
