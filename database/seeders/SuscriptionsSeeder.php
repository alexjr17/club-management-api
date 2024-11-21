<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\Suscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 20 suscripciones con diferentes estados
        Suscription::factory()
            ->count(10)
            ->activa()
            ->create();

        Suscription::factory()
            ->count(5)
            ->pendiente()
            ->create();

        Suscription::factory()
            ->count(5)
            ->state(['estado' => 'inactiva'])
            ->create();
    }
}
