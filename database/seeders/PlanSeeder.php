<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Planes predefinidos
        $planes = [
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Ideal para clubes pequeños',
                'precio' => 29.99,
                'duracion_dias' => 30
            ],
            [
                'nombre' => 'Plan Trimestral',
                'descripcion' => 'Ahorra con 3 meses de servicio',
                'precio' => 79.99,
                'duracion_dias' => 90
            ],
            [
                'nombre' => 'Plan Semestral',
                'descripcion' => 'Mayor ahorro con 6 meses de servicio',
                'precio' => 149.99,
                'duracion_dias' => 180
            ],
            [
                'nombre' => 'Plan Anual',
                'descripcion' => 'Máximo ahorro con servicio anual',
                'precio' => 299.99,
                'duracion_dias' => 365
            ],
        ];

        foreach ($planes as $plan) {
            Plan::create($plan);
        }
    }
}
