<?php

namespace App\Console\Commands\Meta\whatsapp;

use App\Http\Controllers\Meta\Whatsapp\RequestApi;
use App\Models\ClubDeportivo\Config;
use App\Models\ClubDeportivo\Notification;
use App\Models\ClubDeportivo\PaymentPlatform;
use App\Models\ClubDeportivo\Suscription;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:check-expiring';
    protected $description = 'Check for subscriptions about to expire and queue notifications';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Fecha actual del servidor: " . $now->format('Y-m-d') . "\n");

        $subscriptions = Suscription::where('estado', 'activa')
        ->whereDate('fecha_inicio', '<=', $now)
        ->whereDate('fecha_fin', '>=', $now)
        ->whereHas('platformPayments', function ($query) {
            $query->where('estado', 'completado');
        })
            ->with(['platformPayments', 'club.admin'])
            ->get();


            foreach ($subscriptions as $subscription) {
                $user = $subscription->club->admin;
                $club = $subscription->club;
                $confi_meta_access_token = Config::where('modulo', 'procesos')->where('nombre', 'tokenMeta')->where('usuario_id', $user->id)->first();
                $configuraciones = $confi_meta_access_token->configuraciones;
                $meta_request = new RequestApi($configuraciones['access_token_whatsapp']);

                $teachers = UserRole::with('user')->where('club_id', $club->id)->where('rol_id', 2)->limit(10)->get();
                foreach ($teachers as $key => $teacher) {
                    $message = "notificacion vencimienton de menbresia $key";
                    $this->info("usuarios_admin: ".$user->telefono);
                    $this->info("usuarios_club: ".$teacher->user->telefono. "\n");
                    $this->info($meta_request->sendMessage($user->telefono, $teacher->user->telefono, $message));
                    sleep(2);
                }

                // if($confi_meta_access_token){
                //     $this->info($confi_meta_access_token. "\n");
                // }
        }

        $this->info('Total de suscripciones activas con pagos completados: ' . $subscriptions->count());
        $this->info('Comando ejecutado exitosamente');
    }
}
