<?php

namespace App\Http\Controllers;

use App\Models\Image;

class ImageController extends Controller
{
    public function show(Image $image)
    {
        $fullPath = storage_path('app/private/' . $image->path);

        abort_unless(is_file($fullPath), 404);

        // Görsel değişince yolu da değişir (yeni dosya): tarayıcı bir yıl önbellekte tutabilir
        return response()->file($fullPath, [
            'Content-Type' => 'image/webp',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
