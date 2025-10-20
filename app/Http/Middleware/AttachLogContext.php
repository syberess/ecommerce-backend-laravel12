<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttachLogContext
{
    public function handle($request, Closure $next)
    {
        Log::withContext([
            'trace_id' => $request->header('X-Request-Id') ?? (string) Str::uuid(),
            'ip'       => $request->ip(),
            'method'   => $request->method(),
            'url'      => $request->fullUrl(),
            'user_id'  => optional($request->user())->id,
        ]);
        return $next($request);
    }
}
