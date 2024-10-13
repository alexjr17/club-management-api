<?php

namespace App\Services;

use App\Models\ClubDeportivo\Club;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ClubDatabaseService
{
    public function createDatabase(Club $club)
    {
        $dbName = 'club_' . $club->id;

        DB::statement("CREATE DATABASE IF NOT EXISTS $dbName");

        $club->update(['database_connection' => $dbName]);

        // You might want to run migrations for the new database here
        // Artisan::call('migrate', ['--database' => $dbName]);
    }

    public function setConnection(Club $club)
    {
        $connection = $club->getDatabaseConnection();

        config(['database.connections.dynamic.database' => $connection]);
        DB::purge('dynamic');
        DB::reconnect('dynamic');
    }

    public function runMigrations(Club $club)
    {
        $connection = $club->getDatabaseConnection();

        // Configurar la conexión para la nueva base de datos
        config(['database.connections.temp' => config('database.connections.mysql')]);
        config(['database.connections.temp.database' => $connection]);

        // Ejecutar migraciones en la nueva base de datos
        Artisan::call('migrate', [
            '--database' => 'temp',
            '--path' => 'database/migrations/2024_10_13_011852_create_all_tables',
            '--force' => true,
        ]);

        // Limpiar la conexión temporal
        DB::purge('temp');
    }
}


// class SubscriptionController extends Controller
// {
//     protected $clubDatabaseService;
//     protected $paymentService;

//     public function __construct(ClubDatabaseService $clubDatabaseService, PaymentService $paymentService)
//     {
//         $this->clubDatabaseService = $clubDatabaseService;
//         $this->paymentService = $paymentService;
//     }

//     public function processSubscription(Request $request)
//     {
//         // Iniciar una transacción de base de datos
//         DB::beginTransaction();

//         try {
//             // 1. Procesar el pago
//             $paymentResult = $this->paymentService->processPayment($request->all());

//             if (!$paymentResult['success']) {
//                 throw new \Exception('Fallo en el pago: ' . $paymentResult['message']);
//             }

//             // 2. Crear o actualizar la suscripción
//             $club = Club::findOrFail($request->club_id);
//             $subscription = Subscription::updateOrCreate(
//                 ['club_id' => $club->id],
//                 [
//                     'plan_id' => $request->plan_id,
//                     'estado' => 'activo',
//                     'fecha_inicio' => now(),
//                     'fecha_fin' => now()->addDays($request->duracion_dias),
//                 ]
//             );

//             // 3. Registrar el pago en la plataforma
//             PlatformPayment::create([
//                 'suscripcion_id' => $subscription->id,
//                 'monto' => $paymentResult['amount'],
//                 'fecha_pago' => now(),
//                 'estado' => 'completado',
//                 'referencia_pago' => $paymentResult['transaction_id'],
//             ]);

//             // 4. Crear y configurar la base de datos del club
//             $this->clubDatabaseService->createDatabase($club);

//             // 5. Ejecutar migraciones en la nueva base de datos
//             $this->clubDatabaseService->runMigrations($club);

//             // Si todo sale bien, confirmar la transacción
//             DB::commit();

//             return response()->json(['message' => 'Suscripción procesada y base de datos configurada con éxito'], 200);

//         } catch (\Exception $e) {
//             // Si algo falla, revertir la transacción
//             DB::rollBack();
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }
// }
