<?php

namespace App\Models\ClubDeportivo;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanLimit extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'limites_planes';

    protected $fillable = ['plan_id', 'tipo_limite', 'valor_limite'];

    protected $casts = [
        'valor_limite' => 'integer',
    ];

    public static $rules = [
        'plan_id' => 'required|exists:planes,id',
        'tipo_limite' => 'required|string|max:255',
        'valor_limite' => 'required|integer|min:0',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
