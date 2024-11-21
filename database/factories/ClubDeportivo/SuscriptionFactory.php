<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\subscriptions>
 */
class SuscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = $this->faker->dateTimeBetween('-1 year', 'now');
        $fechaFin = clone $fechaInicio;
        $fechaFin->modify('+1 year');

        return [
            'club_id' => Club::inRandomOrder()->first()->id,
            'plan_id' => Plan::inRandomOrder()->first()->id,
            'estado' => $this->faker->randomElement(['activa', 'inactiva', 'pendiente']),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ];
    }

    public function activa()
    {
        return $this->state(function (array $attributes) {
            return ['estado' => 'activa'];
        });
    }

    public function pendiente()
    {
        return $this->state(function (array $attributes) {
            return ['estado' => 'pendiente'];
        });
    }
}
