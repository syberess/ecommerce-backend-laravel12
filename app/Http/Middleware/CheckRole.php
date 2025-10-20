<?php

// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, $roles, true)) {
            return response()->json(['error' => 'Yetkisiz erişim'], 403);
        }
        return $next($request);
    }
}
