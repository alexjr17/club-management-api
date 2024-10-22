<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'inventarios';

    protected $fillable = ['club_id', 'nombre', 'descripcion', 'cantidad', 'precio', 'marca'];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'float',
    ];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'cantidad' => 'required|integer|min:0',
        'precio' => 'required|numeric|min:0',
        'marca' => 'nullable|string|max:255',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
