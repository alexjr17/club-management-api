<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\Config;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\Config>
 */
class ConfigFactory extends Factory
{
    protected $model = Config::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'modulo' => $this->faker->randomElement(Config::MODULOS),
            'configuracion' => [
                'sistema' => [
                    'modo_tema' => [
                        'value' => $this->faker->randomElement(['light', 'dark']),
                        'status' => true
                    ]
                ],
                'pagos' => [
                    'notificar_vencimiento_menbresia' => [
                        'value' => $this->faker->numberBetween(1, 30),
                        'status' => true
                    ]
                ]
            ],
            'activo' => true
        ];
    }
}
