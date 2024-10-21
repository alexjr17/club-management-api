<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\TeacherCache;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\TeacherCache>
 */
class TeacherCacheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = User::factory()->create();
        $roleId = ($user->id == 1) ? 1 : 2; // Asigna el rol 1 solo al usuario 1, de lo contrario, asigna el rol 2

        // Devuelve un array con los atributos necesarios
        return [
            'rol_usuario_id' => UserRole::factory()->create(['rol_id' => $roleId, 'club_id' => 1])->id,
            'calificacion' => $this->faker->randomFloat(1, 0, 5),
            'numero_calificaciones' => $this->faker->numberBetween(0, 100),
            'filosofia' => json_encode([$this->faker->sentence()]), // Genera un JSON con una oración corta
            'especializaciones' => json_encode($this->faker->words(3)), // Genera un JSON con 3 palabras cortas
        ];
    }
}
