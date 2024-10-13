<?php

namespace App\Helpers;

use App\Models\ClubDeportivo\Log;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogHelper
{
    public static function LogRegister(string $action, string $module, $action_id = 0, string $detail = "")
    {
        $token = JWTAuth::getToken();

        // Decodifica el token para obtener el payload
        $payload = JWTAuth::getPayload($token);
        if (!is_int($action_id) && !is_array($action_id)) {
            throw new \InvalidArgumentException('$action_id debe ser un entero o un arreglo. ' . $action_id);
        }

        $insertData = [];
        $agent = new Agent();
        if (is_array($action_id)) {
            for ($i = 0; $i < count($action_id); $i++) {
                $insertData[] = [
                    "user_id" => $payload->get('user_id'),
                    "accion_id" => $action_id[$i],
                    "accion" => $action,
                    "modulo" => $module,
                    "detalles" => $detail,
                    "ip_address" => request()->ip(),
                    "user_agent" => request()->userAgent(),
                    "mobile" => $agent->isMobile()
                ];
            }
        } else {
            $insertData[] = [
                "user_id" => $payload->get('user_id'),
                "accion_id" => $action_id,
                "accion" => $action,
                "modulo" => $module,
                "detalles" => $detail,
                "ip_address" => request()->ip(),
                "user_agent" => request()->userAgent(),
                "mobile" => $agent->isMobile()
            ];
        }
        Log::insert($insertData);
    }
}
