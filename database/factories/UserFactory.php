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
            'foto' => $this->faker->imageUrl(200, 200, 'people'),
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Considera usar Hash::make() en lugar de bcrypt() en versiones más recientes de Laravel
            'estado' => $this->faker->boolean(80) ? 1 : 0, // 80% probabilidad de estar activo
            'nombre_usuario' => $this->faker->unique()->userName,
            'telefono' => $this->faker->phoneNumber,
            'ciudad' => $this->faker->city,
            'tipo_documento' => $this->faker->randomElement(['CC', 'TI', 'CE']),
            'numero_documento' => $this->faker->unique()->numerify('##########'), // 10 dígitos aleatorios
            'tutorial' => $this->faker->boolean(20), // 20% probabilidad de haber completado el tutorial
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
