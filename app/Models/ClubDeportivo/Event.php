<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = 'eventos';

    protected $fillable = ['club_id', 'nombre', 'descripcion', 'fecha_inicio', 'fecha_fin'];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'fecha_inicio' => 'required|date_format:Y-m-d H:i:s',
        'fecha_fin' => 'required|date_format:Y-m-d H:i:s|after:fecha_inicio',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
