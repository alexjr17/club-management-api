<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $newPassword;

    public function __construct($newPassword)
    {
        $this->newPassword = $newPassword;
    }

    public function build()
    {
        return $this->subject('Recuperación de contraseña')
                    ->html($this->getHtmlContent());
    }

    private function getHtmlContent()
    {
        $htmlContent = '
            <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            margin: 0;
                            padding: 20px;
                        }
                        .container {
                            background-color: #ffffff;
                            border-radius: 5px;
                            padding: 20px;
                            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                        }
                        h1 {
                            font-size: 24px;
                            color: #333;
                        }
                        p {
                            font-size: 16px;
                            color: #555;
                        }
                        .button {
                            display: inline-block;
                            padding: 10px 15px;
                            background-color: #28a745;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Recuperación de Contraseña</h1>
                        <p>Hola,</p>
                        <p>Has solicitado restablecer tu contraseña. Aquí están tus nuevas credenciales:</p>
                        <p><strong>Nueva Contraseña: </strong> ' . $this->newPassword . '</p>
                        <p>Te recomendamos que cambies tu contraseña después de iniciar sesión.</p>
                        <p>Si no solicitaste este cambio, ignora este correo.</p>
                        <p>¡Gracias!</p>
                    </div>
                </body>
            </html>
        ';

        return $htmlContent;
    }
}
