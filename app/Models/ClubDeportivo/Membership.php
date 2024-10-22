<?php

namespace App\Models\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'membresias';

    protected $fillable = ['club_id', 'nombre', 'descripcion', 'precio', 'duracion_dias'];

    protected $casts = [
        'precio' => 'float',
        'duracion_dias' => 'integer',
    ];

    public static $rules = [
        'club_id' => 'required|exists:clubes,id',
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric|min:0',
        'duracion_dias' => 'required|integer|min:1',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function clubPayments()
    {
        return $this->hasMany(ClubPayment::class, 'membresia_id');
    }
}
