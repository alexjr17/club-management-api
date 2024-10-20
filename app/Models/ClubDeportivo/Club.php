<?php

namespace App\Models\ClubDeportivo;

use App\Models\CustomRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Club extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'clubes';

    protected $fillable = [
        'foto', 'nombre', 'direccion', 'descripcion', 'barrio', 'nombre_ubicacion',
        'correo', 'telefono', 'fecha_fundacion', 'sede_id', 'usuario_admin_id',
        'ciudad', 'referencia'
    ];

    protected $casts = [
        'fecha_fundacion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $rules = [
        'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'nombre' => 'required|string|max:255',
        'direccion' => 'required|string|max:255',
        'barrio' => 'nullable|string|max:255',
        'nombre_ubicacion' => 'nullable|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|string|max:20',
        'fecha_fundacion' => 'required|date',
        'sede_id' => 'nullable|exists:sedes,id',
        'usuario_admin_id' => 'required|exists:usuarios,id',
        'ciudad' => 'required|string|max:255',
        'referencia' => 'nullable|string|max:255',
    ];

    public static function updateRules($id)
    {
        $rules = self::$rules;
        $rules['correo'] = 'sometimes|required|email|max:255|unique:clubes,correo,' . $id;
        return $rules;
    }

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset(Storage::url($this->foto));
        }
        return null;
    }

    public function getDatabaseConnection()
    {
        return $this->database_connection ?: config('database.default');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'usuario_admin_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function activities()
    {
        return $this->hasMany(Event::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function customRoles()
    {
        return $this->hasMany(CustomRole::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
