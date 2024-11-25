<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use App\Models\ClubDeportivo\Club;
use App\Models\ClubDeportivo\ParentCache;
use App\Models\ClubDeportivo\Plan;
use App\Models\ClubDeportivo\PaymentPlatform;
use App\Models\ClubDeportivo\StudentCache;
use App\Models\ClubDeportivo\Suscription;
use App\Models\ClubDeportivo\TeacherCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->command->info('Iniciando proceso de seeding...');

        // 1. Seedear datos básicos
        $this->seedBasicData();

        // 2. Crear Club Principal y Admin
        $mainClub = $this->createMainClubAndAdmin();

        // 3. Crear datos de prueba para el club principal
        $this->createTestDataForClub($mainClub);
        die;

        // 4. Crear clubes adicionales
        $this->createAdditionalClubs();

        // $this->command->info('Proceso de seeding completado exitosamente.');
    }

    /**
     * Seedear datos básicos del sistema
     */
    private function seedBasicData()
    {
        $this->command->info('Creando datos básicos del sistema...');
        $this->call([
            RoleSeeder::class,
            PlanSeeder::class,
        ]);
    }

    /**
     * Crear el club principal y el usuario administrador
     */
    private function createMainClubAndAdmin()
    {
        $this->command->info('Creando club principal y administrador...');


        // Crear admin
        $adminUser = User::create([
            'nombre' => 'Alex',
            'apellido' => 'Rodriguez',
            'email' => 'alexjose.r.r@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 1,
            'nombre_usuario' => 'Alexjr17',
            'telefono' => 3016913855,
            'ciudad' => 'Sincelejo',
            'pais' => 'Colombia',
            'tipo_documento' => 'CC',
            'numero_documento' => 1005604925,
            'tutorial' => true,
        ]);

        // Crear club principal
        $mainClub = Club::factory()->create();

        // Asignar rol admin
        UserRole::create([
            'usuario_id' => $adminUser->id,
            'rol_id' => 1,
            'rol_personalizado_id' => null,
            'club_id' => $mainClub->id,
        ]);

        return $mainClub;
    }

    /**
     * Crear clubes adicionales
     */
    private function createAdditionalClubs()
    {
        $this->command->info('Creando clubes adicionales...');

        Club::factory()
            ->count(1)
            ->sequence(
                ['nombre' => 'Club Deportivo Norte'],
                ['nombre' => 'Club Deportivo Sur'],
                ['nombre' => 'Club Deportivo Este']
            )
            ->create()
            ->each(function ($club) {
                $this->createTestDataForClub($club);
            });
    }

    /**
     * Crear datos de prueba para un club específico
     */
    private function createTestDataForClub($club)
    {
        $this->command->info("Creando datos de prueba para el club: {$club->nombre}");

        // 1. Crear usuarios con roles
        $this->createClubUsers($club);

        // 2. Crear suscripciones y pagos
        $this->createSubscriptionsAndPayments($club);
    }

    /**
     * Crear usuarios para un club
     */
    private function createClubUsers($club)
    {
        $roles = [
            ['role' => 2, 'count' => 60], // profesores
            ['role' => 4, 'count' => 60],  // padres
            ['role' => 3, 'count' => 60],  // alumnos
        ];

        foreach ($roles as $roleData) {
            User::factory()
                ->count($roleData['count'])
                ->create()
                ->each(function ($user) use ($club, $roleData) {
                    // Primero crear el UserRole
                    $userRole = UserRole::create([
                        'usuario_id' => $user->id,
                        'club_id' => $club->id,
                        'rol_id' => $roleData['role'],
                    ]);
                    sleep(1);

                    // Si es profesor, crear el TeacherCache
                    if ($roleData['role'] == 2) {
                        TeacherCache::factory()->create([
                            'rol_usuario_id' => $userRole->id
                        ]);
                    }
                    // Si es padres, crear el CoachCache
                    if ($roleData['role'] == 4) {
                        $parent = ParentCache::factory()->create([
                            'rol_usuario_id' => $userRole->id
                        ]);
                        // para padres no hay campos adicionales de momento
                    }

                    if ($roleData['role'] == 3) {
                        StudentCache::factory()->create([
                            'rol_usuario_id' => $userRole->id,
                            'padre_rol_usuario_id' => UserRole::where('rol_id', 4)->inRandomOrder()->first()->id
                        ]);
                        // para padres no hay campos adicionales de momento
                    }
                });
        }
    }

    /**
     * Crear suscripciones y pagos para un club
     */
    private function createSubscriptionsAndPayments($club)
    {
        // Crear suscripción activa
        $activeSub = Suscription::create([
            'club_id' => $club->id,
            'plan_id' => Plan::inRandomOrder()->first()->id,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addYear(),
        ]);
        $activeSub->load('plan');
        // Crear pagos para suscripción activa
        PaymentPlatform::factory()
            ->count(6)
            ->sequence(fn($sequence) => [
                'fecha_pago' => now()->subMonths(6 - $sequence->index),
                'estado' => 'completado',
                'monto' => $activeSub->plan->precio,
            ])
            ->create(['suscripcion_id' => $activeSub->id]);

        // Crear suscripción vencida
        $inactiveSub = Suscription::factory()->create([
            'club_id' => $club->id,
            'plan_id' => Plan::inRandomOrder()->first()->id,
            'estado' => 'inactiva',
            'fecha_inicio' => now()->subYear(),
            'fecha_fin' => now()->subMonth(),
        ]);

        // Crear pagos para suscripción vencida
        PaymentPlatform::factory()
            ->count(3)
            ->sequence(fn($sequence) => [
                'fecha_pago' => now()->subMonths(12 - $sequence->index),
                'estado' => 'completado',
                'monto' => $inactiveSub->plan->precio,
            ])
            ->create(['suscripcion_id' => $inactiveSub->id]);
    }
}
