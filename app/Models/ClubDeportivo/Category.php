<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categorias';

    protected $fillable = ['club_id', 'deporte_id', 'nombre', 'descripcion', 'referencia'];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'deporte_id' => 'required|exists:deportes,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'referencia' => 'required|string|max:255|unique:categorias,referencia',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'deporte_id');
    }
}
