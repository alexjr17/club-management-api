<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ImageUploadService
{
    protected $storage;
    protected $maxSize = 1024; // 1MB
    protected $maxWidth = 1200; // pixels

    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }

    public function upload(UploadedFile $image, string $path = 'images'): string
    {
        $filename = $this->generateFilename($image);
        $fullPath = $path . '/' . $filename;

        // Obtener el tamaño de la imagen en KB
        $imageSize = $image->getSize() / 1024;

        // Crear una instancia de la imagen
        $img = Image::make($image->getRealPath());

        // Si la imagen es más grande que el tamaño máximo, optimizarla
        if ($imageSize > $this->maxSize) {
            // Redimensionar la imagen si es más ancha que el ancho máximo
            if ($img->width() > $this->maxWidth) {
                $img->resize($this->maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Comprimir la imagen
            $img->encode($image->getClientOriginalExtension(), 80);

            // Guardar la imagen optimizada
            $this->storage->put($fullPath, $img->stream());
        } else {
            // Si la imagen es pequeña, guardarla sin modificaciones
            $this->storage->putFileAs($path, $image, $filename);
        }

        return $this->storage->url($fullPath);
    }

    public function uploadMultiple(array $images, string $path = 'images'): array
    {
        $urls = [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $urls[] = $this->upload($image, $path);
            }
        }

        return $urls;
    }

    protected function generateFilename(UploadedFile $image): string
    {
        return uniqid() . '.' . $image->getClientOriginalExtension();
    }

    // Método para futura implementación de descarga de imágenes
    public function downloadImage($url, $path = 'images')
    {
        // Implementación futura
    }

    // Métodos para futuras implementaciones de otros servicios de almacenamiento
    protected function uploadToS3(UploadedFile $image, string $filename): void
    {
        // Implementación futura
    }

    protected function uploadToFtp(UploadedFile $image, string $filename): void
    {
        // Implementación futura
    }
}
