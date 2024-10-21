<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'foto' => $this->faker->imageUrl(),
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'estado' => 1,
            'nombre_usuario' => $this->faker->unique()->userName(),
            'telefono' => $this->faker->phoneNumber(),
            'pais' => 'Colombia',
            'ciudad' => 'Sincelejo',
            'tipo_documento' => $this->faker->randomElement(['CC', 'TI', 'CE']),
            'numero_documento' => $this->faker->unique()->numerify('##########'),
            'fecha_nacimiento' => $this->faker->date(),
            'tutorial' => false,
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
