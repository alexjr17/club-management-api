<?php

namespace App\Models\ClubDeportivo;

use App\Models\ClubDeportivo\PlanLimit;
use App\Models\ClubDeportivo\Suscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'planes';

    protected $fillable = ['nombre', 'descripcion', 'precio', 'duracion_dias'];

    protected $casts = [
        'precio' => 'float',
        'duracion_dias' => 'integer',
    ];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric|min:0',
        'duracion_dias' => 'required|integer|min:1',
    ];

    public function planLimit()
    {
        return $this->hasOne(PlanLimit::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Suscription::class);
    }
}
