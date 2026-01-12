<?php

namespace App\Http\Controllers;

use App\Models\Image;

class ImageController extends Controller
{
    public function show(Image $image)
    {
        $fullPath = storage_path('app/private/' . $image->path);

        abort_unless(is_file($fullPath), 404);

        return response()->file($fullPath, [
            'Content-Type' => 'image/webp',
            'Content-Disposition' => 'inline',
        ]);
    }
}
