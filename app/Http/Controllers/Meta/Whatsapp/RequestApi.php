<?php

namespace App\Http\Controllers\Meta\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Importación de Str

class RequestApi extends Controller
{
    private $redirectUrl;
    private $apiUrl;
    private $appId;
    private $secretKey;
    private $accessToken;

    public function __construct($token)
    {
        $this->apiUrl = "https://graph.facebook.com/v21.0/";
        $this->secretKey = env('META_APP_SECRET');
        $this->redirectUrl = env('APP_URL') . "/callback";
        $this->appId = env('META_APP_ID');
        // $this->accessToken = $token;
        $this->accessToken = 'EAAybvgbhpgABOzOYCpIgOoZCHEnFgZBVrBA9YsZCx2RWb1sfmrv8hAn6w3UGVsSGf12oHINVh1fxlHuK8R9gZASrSlhIHHujoXElUy03042GVoevdJecZBz6339XEscswxaNmo1ZC0BUukO0xXEhw6OpZAxIB958KYZCGXXqYNV2PMpDv13KdliJ9lbtFLKWbvGRp1SmtRNTeVWPeVniBoXzAZBuFaRMZD';
    }

    public function connect()
    {
        // Generar un estado único para prevenir CSRF
        $state = Str::random(40);

        // Guardar el estado en la sesión para verificación posterior
        session()->put('oauth_state', $state);

        $params = [
            "client_id" => $this->appId, // Asegúrate de que esta variable esté configurada
            "redirect_uri" => $this->redirectUrl, // URL de callback registrada
            "scope" => "whatsapp_business_management whatsapp_business_messaging", // Scopes necesarios
            "state" => $state, // Estado único generado
            "response_type" => "code" // Necesario para OAuth
        ];

        // Construir la URL de autorización de Facebook
        $url = "https://www.facebook.com/v21.0/dialog/oauth?" . http_build_query($params);

        // Redirigir al usuario a la página de autorización de Facebook
        // return redirect()->away($url);
        return redirect('auth/callback');
    }

    public function callback(Request $request)
    {
        // Validar los parámetros de entrada
        // if ($request->has('error')) {
        //     return response()->json([
        //         'error' => $request->input('error'),
        //         'error_description' => $request->input('error_description', 'Unknown error')
        //     ], 400);
        // }

        try {
            // Validar que existe el código
            // if (!$request->has('code')) {
            //     return response()->json([
            //         'error' => 'Missing authorization code'
            //     ], 400);
            // }

            // Intercambiar el código por un token de acceso
            // $response = Http::withHeaders([
            //     'Content-Type' => 'application/x-www-form-urlencoded'
            // ])->asForm()->post($this->apiUrl . '/oauth/access_token', [
            //     'client_id' => $this->appId,
            //     'client_secret' => $this->secretKey,
            //     'redirect_uri' => $this->redirectUrl,
            //     'code' => $request->input('code')
            // ]);

            // // Verificar si la respuesta fue exitosa
            // if (!$response->successful()) {
            //     return response()->json([
            //         'error' => 'Failed to obtain access token',
            //         'details' => $response->json()
            //     ], $response->status());
            // }

            // Extract access token from response
            // $accessTokenResponse = $response->json();
            $accessToken = $accessTokenResponse['access_token'] ?? $this->accessToken;

            // Obtener usuario autenticado y club
            $user = auth()->user();
            $club = $user->club ?? null;
            $clubId = $club->id ?? 1;

            // Buscar configuración existente
            $configMeta = Config::where('nombre', "tokenMeta")
                ->where('modulo', "procesos")
                // ->where('club_id', $clubId)
                ->first();

            if (!$configMeta) {
                // Crear nueva configuración
                $configMeta = Config::create([
                    "nombre" => "tokenMeta",
                    "tipo" => "admin",
                    "modulo" => "procesos",
                    "usuario_id" => $user->id ?? 1,
                    "club_id" => $clubId,
                    "configuraciones" => [
                        "access_token_whatsapp" => $accessToken
                    ]
                ]);
            } else {
                // Actualizar configuración existente
                $configuraciones = json_decode($configMeta->configuraciones, true) ?? [];
                $configuraciones["access_token_whatsapp"] = $accessToken;

                $configMeta->update([
                    "configuraciones" => $configuraciones,
                    // "usuario_id" => $user->id ?? $configMeta->usuario_id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token actualizado correctamente',
                'access_token' => $accessToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en el proceso de callback',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage($phoneAdmin, $phoneNumber, $message)
    {
        try {
            // Formatear los números de teléfono quitando símbolos como el "+"
            $formattedPhoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            $formattedPhoneAdmin = preg_replace('/[^0-9]/', '', $phoneAdmin);

            // Realizar la solicitud a la API de WhatsApp
            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl . "$formattedPhoneAdmin/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $formattedPhoneNumber, // Usar el número formateado correctamente
                    "type" => "template",
                    "template" => [
                        "name" => "hello_world",
                        "language" => [
                            "code" => "en_US"
                        ]
                    ]
                ]);

            // Verificar si la respuesta es exitosa
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'data' => $response->json()
                ]);
            }

            // Si hay un error en la respuesta de la API
            return response()->json([
                'status' => 'error',
                'error' => $response->json()
            ], $response->status());
        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra
            return response()->json([
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function webhooks(Request $request)
    {
        $resposneDarta = [
            "hub_mode" => "subscribe",
            "hub_challenge" => "alex12345678",
            "hub_verify_token" => "alex12345678"
        ];
        return response()->json($resposneDarta);
    }
}
