<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function show(Image $image)
    {
        $fullPath = storage_path('app/private/' . $image->path);

        abort_unless(file_exists($fullPath), 404);

        return response()->file($fullPath);
    }
}
