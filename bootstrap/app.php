<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // ✅ bu mutlaka burada olmalı
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        $middleware->append(\App\Http\Middleware\AttachLogContext::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Problem+JSON üreten yardımcı
        $problem = function (int $status, string $code, string $message, array $extra = []) {
            $payload = array_merge([
                'status'   => $status,
                'code'     => $code,
                'message'  => $message,
                'trace_id' => request()->header('X-Request-Id') ?? (string) Str::uuid(),
            ], $extra);

            return response()->json($payload, $status, [
                'Content-Type' => 'application/problem+json'
            ]);
        };

        // Beklenmeyen her şeyi merkezi logla
        $exceptions->report(function (Throwable $e) {
            Log::error('Unhandled exception', [
                'type' => get_class($e),
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });

        // Yaygın hatalar (tek tip JSON)
        $exceptions->render(function (ValidationException $e) use ($problem) {
            return $problem(422, 'validation_error', 'Doğrulama hatası', ['errors' => $e->errors()]);
        });

        $exceptions->render(function (AuthenticationException $e) use ($problem) {
            return $problem(401, 'unauthenticated', 'Giriş yapmanız gerekiyor');
        });

        $exceptions->render(function (AuthorizationException $e) use ($problem) {
            return $problem(403, 'forbidden', 'Bu işlemi yapma yetkiniz yok');
        });

        $exceptions->render(function (ModelNotFoundException $e) use ($problem) {
            return $problem(404, 'not_found', 'Kayıt bulunamadı');
        });

        $exceptions->render(function (NotFoundHttpException $e) use ($problem) {
            return $problem(404, 'route_not_found', 'Endpoint bulunamadı');
        });

        $exceptions->render(function (ThrottleRequestsException $e) use ($problem) {
            return $problem(429, 'too_many_requests', 'Çok fazla istek');
        });

        // JWT kullanıyorsan (tymon/php-open-source-saver)
        if (class_exists(\Tymon\JWTAuth\Exceptions\TokenExpiredException::class)) {
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) use ($problem) {
                return $problem(401, 'token_expired', 'Oturum süresi doldu');
            });
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) use ($problem) {
                return $problem(401, 'token_invalid', 'Geçersiz token');
            });
            $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e) use ($problem) {
                return $problem(401, 'token_missing', 'Token bulunamadı');
            });
        }

        // Fallback
        $exceptions->render(function (Throwable $e) use ($problem) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $msg    = config('app.debug') ? $e->getMessage() : 'Beklenmeyen bir hata oluştu';
            return $problem($status, 'server_error', $msg);
        });
    })

    ->withProviders([
        App\Providers\AppServiceProvider::class,      // Gate::policy burada kayıtlı
        // Eğer ayrı bir AuthServiceProvider oluşturduysan onu da ekle:
        // App\Providers\AuthServiceProvider::class,
    ])

    ->create();
