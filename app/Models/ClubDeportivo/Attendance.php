<?php

namespace App\Models\ClubDeportivo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'asistencias';

    protected $fillable = ['clase_id', 'alumno_id', 'estado'];

    protected $casts = [
        'estado' => 'string',
    ];

    public static $rules = [
        'clase_id' => 'required|exists:clases,id',
        'alumno_id' => 'required|exists:usuarios,id',
        'estado' => 'required|string|max:255',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'clase_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }
}
