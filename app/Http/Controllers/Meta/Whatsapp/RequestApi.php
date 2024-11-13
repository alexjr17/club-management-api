<?php

namespace App\Http\Controllers\Meta\Whatsapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $this->apiUrl = "https://graph.facebook.com/v21.0/"; // Base URL para la API de WhatsApp Business
        $this->accessToken = 'your_whatsapp_business_api_access_token'; // Reemplaza con tu token de acceso
        $this->secretKey = "5b094bfd92b0f96fb8f37fe17caf4893";
        $this->redirectUrl = "https://club-management-api-production.up.railway.app/auth/callback";
        $this->appId = "1241134183884602";
    }

    public function conectar(Request $request) {
        return response()->json($request->all());
    }

    public function autentication() {
        $params = [
            "client_id" => $this->appId,
            "redirect_uri" => $this->redirectUrl,
            "scope" => "whatsapp_business_messaging",
            "state" => "what1797tt",
            "client_secret" => $this->secretKey
        ];

        $url = "https://www.facebook.com/v21.0/dialog/oauth?" . http_build_query($params);

        return redirect()->away($url);
    }

    /**
     * sendMessage
     * Enviar un mensaje usando la API de WhatsApp
     */
    public function sendMessage($phoneNumber, $message)
    {
        $url = $this->apiUrl . 'your_phone_number_id/messages'; // Reemplaza con tu "phone_number_id"

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ]
        ];

        return $this->post($url, $data);
    }

    /**
     * post
     * Método para realizar una solicitud POST con cURL
     */
    public function post($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * get
     * Método para realizar una solicitud GET con cURL
     */
    public function get($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * delete
     * Método para realizar una solicitud DELETE con cURL
     */
    public function delete($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * requestCurl
     * Método genérico para realizar cualquier tipo de solicitud con cURL
     */
    public function requestCurl($method, $url, $data = null)
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
        ];

        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}
