<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // API / JSON istekleri
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        // LOCAL + DEBUG → Laravel debug ekranı
        if (app()->environment('local') && config('app.debug')) {
            return parent::render($request, $e);
        }

        // PRODUCTION → Custom error UI
        $status = 500;

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        }

        $message = match ($status) {
            403 => 'You do not have permission to access this page.',
            404 => 'The page you are looking for could not be found.',
            419 => 'Your session has expired. Please refresh and try again.',
            default => 'Something went wrong. Please try again later.',
        };

        return response()->view(
            'errors.error',
            [
                'code' => $status,
                'message' => $message,
            ],
            $status
        );
    }
}
