<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ClubDeportivo\Club;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(1)->create();

        \App\Models\User::factory()->create([
            'nombre' => 'Alex',
            'apellido' => 'Rodriguez',
            'email' => 'alexjose.r.r@gmail.com',
            'password' => bcrypt('12345678'), // Considera usar Hash::make() en lugar de bcrypt() en versiones más recientes de Laravel
            'estado' => 1, // 80% probabilidad de estar activo
            'nombre_usuario' => 'Alexjr17',
            'telefono' => 3016913855,
            'ciudad' => 'Sincelejo',
            'tipo_documento' => 'CC',
            'numero_documento' => 1005604925, // 10 dígitos aleatorios
            'tutorial' => true, // 20% probabilidad de haber completado el tutorial
        ]);
        $this->call([
            RoleSeeder::class,
            // ClubSeeder::class,
        ]);
    }
}
