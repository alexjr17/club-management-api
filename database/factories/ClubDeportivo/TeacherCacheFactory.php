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
        $especializaciones = [
            "Desarrollo de Habilidades Técnicas",
            "Trabajo en Equipo",
            "Tácticas y Estrategia",
            "Fuerza y Acondicionamiento",
            "Velocidad y Agilidad",
            "Prevención de Lesiones",
            "Entrenamiento Mental",
            "Formación de Liderazgo",
            "Adaptación al Juego",
            "Nutrición Deportiva Básica"
        ];

        // Devuelve un array con los atributos necesarios
        return [
            'rol_usuario_id' => UserRole::factory()->create(['rol_id' => 2, 'club_id' => 1])->id,
            'calificacion' => $this->faker->randomFloat(1, 0, 5),
            'numero_calificaciones' => $this->faker->numberBetween(0, 100),
            'filosofia' => json_encode([$this->faker->sentence()]), // Genera un JSON con una oración corta
            'especializaciones' => json_encode($this->faker->randomElements($especializaciones, 3)), // Selecciona hasta 3 especializaciones aleatorias
        ];
    }
}
