<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use League\Flysystem\Filesystem;

class ImageUploadService
{
    protected $storage;

    public function __construct(Filesystem $storage = null)
    {
        $this->storage = $storage ?? Storage::disk('public');
    }

    public function upload(array $images, string $storageType): array
    {
        $urls = [];

        foreach ($images as $image) {
            try {
                $filename = $this->generateFilename($image);
                switch ($storageType) {
                    case 'local':
                        $this->storage->putFileAs('images', $image, $filename);
                        break;
                    case 's3':
                        try {
                            $this->uploadToS3($image, $filename);
                        } catch (\Exception $s3Exception) {
                            // Si hay una excepción al cargar en S3, intenta guardar localmente
                            $this->storage->putFileAs('images', $image, $filename);
                        }
                        break;
                    case 'ftp':
                        try {
                            $this->uploadToFtp($image, $filename);
                        } catch (\Exception $ftpException) {
                            // Si hay una excepción al cargar en FTP, intenta guardar localmente
                            $this->storage->putFileAs('images', $image, $filename);
                        }
                        break;
                    default:
                        throw new \InvalidArgumentException("Ubicación de almacenamiento no válida: $storageType");
                }

                $urls[] = $this->storage->url('images/'. $filename);
            } catch (\Exception $e) {
                // Manejo de la excepción: puedes registrar el error, enviar una respuesta de error, etc.
                // Por ejemplo:
                // Log::error('Error al cargar imagen: ' . $e->getMessage());
                // return response()->json(['error' => 'Ocurrió un error al cargar la imagen.'], 500);
            }
        }

        return $urls;
    }

    public function downloadImage($url, $storageType)
    {
        $fileName = uniqid();

        try {
            $client = new Client();
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                $contentType = $response->getHeader('Content-Type')[0];
                $extension = $this->getExtensionFromMimeType($contentType);

                if ($extension) {
                    $fileName .= '.' . $extension;
                    $path = 'public/images/' . $fileName;

                    try {
                        return $this->storeImage($path, $response->getBody(), $storageType);
                    } catch (\Throwable $th) {
                        return $this->storeImage($path, $response->getBody(), "local");
                    }
                }
            }
        } catch (\Exception $e) {
            // Manejar errores, por ejemplo, registrando el error o lanzando una excepción
            // Logging o excepciones pueden ser útiles para rastrear problemas en la descarga
            // Aquí puedes adaptar la lógica para el manejo de errores según tus necesidades
        }

        return '/assets/productDefault.png';
    }

    private function storeImage($path, $data, $storageType)
    {
        switch ($storageType) {
            case 'local':
                Storage::disk('local')->put($path, $data);
                // return Storage::disk('local')->url($path);
                return asset(Storage::url($path));
            case 's3':
                Storage::disk('s3')->put($path, $data);
                return Storage::disk('s3')->url($path);
            case 'ftp':
                // Agrega la lógica para cargar la imagen en un servidor FTP
                // Ejemplo: utilizar la biblioteca phpseclib o alguna otra biblioteca FTP
                $this->uploadToFtp($path, $data);
                // Retorna la URL del archivo en el servidor FTP (si es aplicable)
                // Ejemplo: return 'ftp://tu-servidor-ftp.com/ruta/' . $path;
                break;
            default:
                throw new \InvalidArgumentException("Tipo de almacenamiento no válido: $storageType");
        }
    }

    protected function generateFilename(UploadedFile $image): string
    {
        return uniqid() . '.' . $image->extension();
    }

    protected function uploadToS3(UploadedFile $image, string $filename): void
    {
        $s3 = new S3Client([
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_REGION'),
        ]);

        $s3->putObject(
            ['Bucket' => env('AWS_BUCKET_NAME'), 'Key' => $filename],
            file_get_contents($image->path())
        );
    }

    protected function uploadToFtp(UploadedFile $image, string $filename): void
    {
        $ftp = new \FTP();
        $ftp->connect(env('FTP_HOST'), env('FTP_PORT'), env('FTP_USERNAME'), env('FTP_PASSWORD'));
        $ftp->login();
        $ftp->put($image->path(), $filename);
        $ftp->close();
    }

    private function getExtensionFromMimeType($mimeType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'image/webp' => 'webp',
            'image/x-icon' => 'ico',
            'image/svg+xml' => 'svg',
            'image/x-png' => 'png',
            'image/vnd.microsoft.icon' => 'ico',
            'image/svg+xml' => 'svg',
            'image/jpeg2000' => 'jp2',
        ];

        if (array_key_exists($mimeType, $mimeTypes)) {
            return $mimeTypes[$mimeType];
        }

        return null;
    }
}
