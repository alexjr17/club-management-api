<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\Suscription;
use App\Models\ClubDeportivo\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\PaymentsPlatform>
 */
class PaymentPlatformFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $suscripcion = Suscription::factory()->create();
        $plan = Plan::find($suscripcion->plan_id);

        return [
            'suscripcion_id' => $suscripcion->id,
            'monto' => $plan->precio ?? $this->faker->randomFloat(2, 29.99, 299.99),
            'fecha_pago' => $this->faker->dateTimeBetween($suscripcion->fecha_inicio, 'now'),
            'estado' => $this->faker->randomElement(['pendiente', 'completado', 'fallido']),
            'referencia_pago' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
        ];
    }

    public function completado()
    {
        return $this->state(function (array $attributes) {
            return ['estado' => 'completado'];
        });
    }

    public function pendiente()
    {
        return $this->state(function (array $attributes) {
            return ['estado' => 'pendiente'];
        });
    }
}
