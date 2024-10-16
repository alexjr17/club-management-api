<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles con nombres específicos
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Acceso total a todas las funcionalidades.'],
            ['nombre' => 'profesor', 'descripcion' => 'Acceso a funciones básicas del sistema.'],
            ['nombre' => 'alumno', 'descripcion' => 'Gestiona contenido y usuarios dentro del sistema.'],
            ['nombre' => 'padre', 'descripcion' => 'Gestiona contenido y usuarios dentro del sistema.'],
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
    }
}
