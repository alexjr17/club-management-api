<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\Club;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Club::factory()->create([
            'usuario_admin_id' => 1, // Asegúrate de que este ID exista en la tabla `usuarios`
        ]);
    }
}
