<?php

namespace App\Models\ClubDeportivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Config extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'usuario_id',
        'modulo',
        'tipo',
        'club_id',
        'configuraciones',
        'activo'
    ];

    protected $casts = [
        'configuraciones' => 'array',
        'activo' => 'boolean'
    ];

    protected $hidden = [
        'updated_at',
        'created_at'
    ];

    public static $rules = [
        'configuraciones' => 'required|array',
        'configuraciones.*.data' => 'required|array',
        'configuraciones.*.data.*.value' => 'required',
        'configuraciones.*.data.*.status' => 'required|boolean',
        'configuraciones.*.modulo' => 'required|string|max:255|in:sistema,pagos,notificaciones',
        'configuraciones.*.tipo' => 'required|in:admin,usuario',
        'club' => 'required_if:configuraciones.*.tipo,admin|exists:clubes,id|nullable',
        'usuario_id' => 'required|exists:usuarios,id'
    ];

    public static $message = [
        'configuraciones.required' => 'Las configuraciones son requeridas',
        'configuraciones.*.data.required' => 'Los datos de configuración son requeridos',
        'configuraciones.*.modulo.required' => 'El módulo es requerido',
        'configuraciones.*.modulo.in' => 'El módulo debe ser: sistema, pagos o notificaciones',
        'configuraciones.*.modulo.max' => 'El módulo no puede exceder los 255 caracteres',
        'configuraciones.*.tipo.required' => 'El tipo es requerido para cada configuración',
        'configuraciones.*.tipo.in' => 'El tipo debe ser admin o usuario',
        'club.required_if' => 'El club es requerido cuando alguna configuración es de tipo admin',
        'club.exists' => 'El club seleccionado no existe',
        'usuario_id.required' => 'El ID de usuario es requerido',
        'usuario_id.exists' => 'El usuario seleccionado no existe'
    ];

    // Módulos disponibles
    public const MODULOS = [
        'sistema',
        'pagos',
        'notificaciones'
    ];

    // Relación con el usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scope para filtrar por módulo
    public function scopeModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    // Scope para configuraciones activas
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    // Método para actualizar configuración
    public function actualizarConfiguracion(array $nuevaConfig): void
    {
        $configuracionActual = $this->configuracion;
        $this->configuracion = array_merge($configuracionActual, $nuevaConfig);
        $this->save();
    }
}
