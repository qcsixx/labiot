<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Add custom JSON response for API exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = 500;
                $message = 'Terjadi kesalahan pada server';
                
                if ($e instanceof ValidationException) {
                    $status = 422;
                    $message = $e->getMessage();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validasi gagal',
                        'errors' => $e->errors(),
                    ], $status);
                } elseif ($e instanceof AuthenticationException) {
                    $status = 401;
                    $message = 'Anda belum login atau sesi telah berakhir';
                } elseif ($e instanceof AuthorizationException) {
                    $status = 403;
                    $message = 'Anda tidak memiliki izin untuk melakukan tindakan ini';
                } elseif ($e instanceof ModelNotFoundException) {
                    $status = 404;
                    $message = 'Data yang diminta tidak ditemukan';
                } elseif ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $message = 'Endpoint API tidak ditemukan';
                }
                
                // Log the real error in production
                if (app()->environment('production')) {
                    Log::error($e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                    ], $status);
                } else {
                    // In development, include more details
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ], $status);
                }
            }
        });
    }
} 