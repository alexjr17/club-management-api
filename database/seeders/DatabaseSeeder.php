<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ClubDeportivo\Club;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Crear roles primero
        $this->call(RoleSeeder::class);

        // Datos del usuario
        $usuario = [
            'nombre' => 'Alex',
            'apellido' => 'Rodriguez',
            'email' => 'alexjose.r.r@gmail.com',
            'password' => bcrypt('12345678'), // Considera usar Hash::make() en lugar de bcrypt() en versiones más recientes de Laravel
            'estado' => 1, // 80% probabilidad de estar activo
            'nombre_usuario' => 'Alexjr17',
            'telefono' => 3016913855,
            'ciudad' => 'Sincelejo',
            'pais' => 'Colombia', // Cambié 'ciudad' a 'pais' para evitar duplicados
            'tipo_documento' => 'CC',
            'numero_documento' => 1005604925, // 10 dígitos aleatorios
            'tutorial' => true, // 20% probabilidad de haber completado el tutorial
        ];

        // Crear el usuario
        $user = User::create($usuario);

        // Crear el rol del usuario
        UserRole::factory()->create([
            'usuario_id' => $user->id,
            'rol_id' => 1,
            'rol_personalizado_id' => null,
            'club_id' => Club::factory()->create()->id, // Asegúrate de crear el club y obtener su ID
        ]);

        // Llamar a otros seeders según sea necesario
        $this->call([
            // UserRoleSeeder::class,
            TeacherCacheSeeder::class,
        ]);
    }
}
