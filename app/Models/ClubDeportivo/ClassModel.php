<?php

namespace App\Models\ClubDeportivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;
    protected $table = 'clases';

    protected $fillable = ['club_id', 'profesor_id', 'deporte_id', 'nombre', 'descripcion', 'fecha', 'hora_inicio', 'hora_fin'];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'time',
        'hora_fin' => 'time',
    ];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'profesor_id' => 'required|exists:usuarios,id',
        'deporte_id' => 'required|exists:deportes,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'fecha' => 'required|date',
        'hora_inicio' => 'required|date_format:H:i',
        'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'deporte_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'clase_id');
    }
}
