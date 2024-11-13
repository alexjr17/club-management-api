<?php

namespace App\Http\Controllers\Meta\Whatsapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Colors\Rgb\Channels\Red;

class RequestApi extends Controller
{
	private $redirectUrl;
    private $apiUrl;
    private $accessToken;
    private $appId;
    private $secretKey;

    public function __construct()
    {
        $this->apiUrl = "https://graph.facebook.com/v19.0/"; // Versión más estable actual
        $this->secretKey = env('META_APP_SECRET');
        $this->redirectUrl = env('APP_URL') . "/auth/callback";
        $this->appId = env('META_APP_ID');
    }

    public function connect()
    {
        $params = [
            "client_id" => $this->appId,
            "redirect_uri" => $this->redirectUrl,
            "scope" => "whatsapp_business_management whatsapp_business_messaging", // Scopes necesarios
            "state" => csrf_token(), // Usar CSRF token para seguridad
            "response_type" => "code" // Necesario para OAuth
        ];

        $url = "https://www.facebook.com/v19.0/dialog/oauth?" . http_build_query($params);
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return response()->json([
                'error' => $request->error,
                'error_description' => $request->error_description
            ], 400);
        }

        try {
            // Intercambiar el código por un token de acceso
            $response = Http::post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $this->appId,
                'client_secret' => $this->secretKey,
                'redirect_uri' => $this->redirectUrl,
                'code' => $request->code
            ]);

            $accessToken = $response->json()['access_token'];

            // Guardar el token en la base de datos o en cache
            // Cache::put('whatsapp_access_token', $accessToken, now()->addDays(60));

            return response()->json([
                'success' => true,
                'access_token' => $accessToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener el token de acceso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage($phoneNumber, $message)
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl . 'YOUR_PHONE_NUMBER_ID/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
