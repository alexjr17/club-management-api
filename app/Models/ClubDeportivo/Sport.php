<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;
    protected $table = 'deportes';

    protected $fillable = ['nombre', 'descripcion', 'referencia'];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'referencia' => 'required|string|max:255|unique:deportes,referencia',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class, 'deporte_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'deporte_id');
    }
}
