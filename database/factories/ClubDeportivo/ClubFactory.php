<?php

namespace Database\Factories\ClubDeportivo;

use App\Models\User;
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
            'foto' => $this->faker->imageUrl(),
            'nombre' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'descripcion' => $this->faker->paragraph(),
            'barrio' => $this->faker->streetName(),
            'nombre_ubicacion' => $this->faker->city(),
            'correo' => $this->faker->companyEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'fecha_fundacion' => $this->faker->date(),
            // 'usuario_admin_id' => User::factory(),
            'usuario_admin_id' => 1,
            'ciudad' => $this->faker->city(),
            'database_connection' => null,
            'referencia' => $this->faker->unique()->word(),
        ];
    }
}
