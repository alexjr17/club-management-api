<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        // Planes predefinidos con sus características
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Ideal para clubes pequeños o que están iniciando',
                'precio' => 29.99,
                'duracion_dias' => 30,
            ],
            [
                'nombre' => 'Plan Trimestral',
                'descripcion' => 'Perfecto para clubes en crecimiento',
                'precio' => 79.99,
                'duracion_dias' => 90,
            ],
            [
                'nombre' => 'Plan Semestral',
                'descripcion' => 'Recomendado para clubes establecidos',
                'precio' => 149.99,
                'duracion_dias' => 180,
            ],
            [
                'nombre' => 'Plan Anual',
                'descripcion' => 'La mejor opción para clubes grandes',
                'precio' => 299.99,
                'duracion_dias' => 365,
            ],
        ];

        // Seleccionar un plan aleatorio de los predefinidos
        $plan = $this->faker->randomElement($planes);

        return [
            'nombre' => $plan['nombre'],
            'descripcion' => $plan['descripcion'],
            'precio' => $plan['precio'],
            'duracion_dias' => $plan['duracion_dias'],
        ];
    }

    // Estado para Plan Básico
    public function basico()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Ideal para clubes pequeños o que están iniciando',
                'precio' => 29.99,
                'duracion_dias' => 30,
            ];
        });
    }

    // Estado para Plan Trimestral
    public function trimestral()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Plan Trimestral',
                'descripcion' => 'Perfecto para clubes en crecimiento',
                'precio' => 79.99,
                'duracion_dias' => 90,
            ];
        });
    }

    // Estado para Plan Semestral
    public function semestral()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Plan Semestral',
                'descripcion' => 'Recomendado para clubes establecidos',
                'precio' => 149.99,
                'duracion_dias' => 180,
            ];
        });
    }

    // Estado para Plan Anual
    public function anual()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Plan Anual',
                'descripcion' => 'La mejor opción para clubes grandes',
                'precio' => 299.99,
                'duracion_dias' => 365,
            ];
        });
    }

    // Estado para plan personalizado
    public function personalizado(float $precio, int $duracion)
    {
        return $this->state(function (array $attributes) use ($precio, $duracion) {
            return [
                'nombre' => "Plan Personalizado {$duracion} días",
                'descripcion' => "Plan adaptado a necesidades específicas",
                'precio' => $precio,
                'duracion_dias' => $duracion,
            ];
        });
    }

    // Estado para plan de prueba
    public function prueba()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Plan de Prueba',
                'descripcion' => 'Plan gratuito para probar el sistema',
                'precio' => 0.00,
                'duracion_dias' => 15,
            ];
        });
    }
}
