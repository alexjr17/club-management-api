<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\ClubDeportivo\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\Config>
 */
class ConfigFactory extends Factory
{
    protected $model = Config::class;

    public function definition()
    {
        $tipo = $this->faker->randomElement(['admin', 'usuario']);
        $modulo = $this->faker->randomElement(['sistema', 'pagos', 'clases', 'notificaciones']);

        return [
            'tipo' => $tipo,
            'modulo' => $modulo,
            'configuracion' => Config::getDefaultConfig($tipo, $modulo),
            'activo' => true
        ];
    }

    public function usuario()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => 'usuario',
                'modulo' => 'sistema',
                'configuracion' => Config::getDefaultConfig('usuario', 'sistema')
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => 'admin',
                'modulo' => 'pagos',
                'configuracion' => Config::getDefaultConfig('admin', 'pagos')
            ];
        });
    }
}
