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
        // Crear configuraciones para clubes existentes
        Club::all()->each(function ($club) {
            // Configuración de pagos
            Config::create([
                'club_id' => $club->id,
                'tipo' => 'admin',
                'modulo' => 'pagos',
                'configuracion' => Config::getDefaultConfig('admin', 'pagos'),
                'activo' => true
            ]);

            // Configuración de clases
            Config::create([
                'club_id' => $club->id,
                'tipo' => 'admin',
                'modulo' => 'clases',
                'configuracion' => Config::getDefaultConfig('admin', 'clases'),
                'activo' => true
            ]);
        });

        // Crear algunas configuraciones de usuario aleatorias
        User::all()->take(5)->each(function ($usuario) {
            Config::factory()->usuario()->create([
                'usuario_id' => $usuario->id
            ]);
        });
    }
}
