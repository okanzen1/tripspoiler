<?php

namespace App\Http\Controllers;

use App\Models\Image;

class ImageController extends Controller
{
    public function show(Image $image)
    {
        $fullPath = base_path('../tripspoiler-admin/storage/app/private/' . $image->path);

        abort_unless(is_file($fullPath), 404);

        return response()->file($fullPath);
    }
}
