<?php

namespace App\Models\ClubDeportivo;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'suscripciones';

    protected $fillable = ['club_id', 'plan_id', 'estado', 'fecha_inicio', 'fecha_fin'];

    protected $casts = [
        'estado' => 'string',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'plan_id' => 'required|exists:planes,id',
        'estado' => 'required|string|max:255',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after:fecha_inicio',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function platformPayments()
    {
        return $this->hasMany(PaymentPlatform::class);
    }
}
