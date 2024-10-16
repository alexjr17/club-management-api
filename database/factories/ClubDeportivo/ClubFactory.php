<?php

namespace Database\Factories\ClubDeportivo;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubDeportivo\Club>
 */
class ClubFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'foto' => null,
            'nombre' => $this->faker->company,
            'direccion' => $this->faker->address,
            'barrio' => $this->faker->word,
            'nombre_ubicacion' => $this->faker->city,
            'correo' => $this->faker->safeEmail,
            'telefono' => $this->faker->phoneNumber,
            'fecha_fundacion' => $this->faker->date(),
            'sede_id' => null, // Cambiar si es necesario
            'usuario_admin_id' => 1, // Asegúrate de que este ID exista en la tabla `usuarios`
            'ciudad' => $this->faker->city,
            'database_connection' => 'mysql', // Cambia si es necesario
            'referencia' => null,
        ];
    }
}
