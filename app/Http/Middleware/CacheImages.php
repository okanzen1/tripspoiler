<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheImages
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            $request->is('images/*') ||
            $request->is('storage/*') ||
            $request->is('favicon*')
        ) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=31536000, immutable'
            );
        }

        return $response;
    }
}