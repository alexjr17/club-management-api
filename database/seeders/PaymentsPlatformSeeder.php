<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\PaymentPlatform;
use App\Models\ClubDeportivo\Suscription;
use App\Models\ClubDeportivo\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentsPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear pagos para cada suscripción
        Suscription::all()->each(function ($suscripcion) {
            // Crear entre 1 y 4 pagos por suscripción
            $numPagos = rand(1, 4);

            PaymentPlatform::factory()
                ->count($numPagos)
                ->create([
                    'suscripcion_id' => $suscripcion->id,
                    'monto' => Plan::find($suscripcion->plan_id)->precio
                ]);
        });
    }
}
