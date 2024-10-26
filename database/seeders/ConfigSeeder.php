<?php

namespace Database\Seeders;

use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\Config;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    public function run()
    {
        // Crear configuraciones para usuarios existentes
        User::all()->each(function ($user) {
            // Configuración del sistema
            Config::create([
                'usuario_id' => $user->id,
                'modulo' => 'sistema',
                'configuracion' => [
                    'modo_tema' => [
                        'value' => 'light',
                        'status' => true
                    ]
                ],
                'activo' => true
            ]);

            // Configuración de pagos
            Config::create([
                'usuario_id' => $user->id,
                'modulo' => 'pagos',
                'configuracion' => [
                    'notificar_vencimiento_menbresia' => [
                        'value' => 7,
                        'status' => true
                    ]
                ],
                'activo' => true
            ]);
        });
    }
}
