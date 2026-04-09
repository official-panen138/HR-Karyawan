<?php

namespace App\Http\Middleware;

use App\Models\IpWhitelist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOfficeIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();

        $isWhitelisted = IpWhitelist::where('ip_address', $clientIp)
            ->where('is_active', true)
            ->exists();

        if (!$isWhitelisted) {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya dari jaringan kantor',
            ], 403);
        }

        return $next($request);
    }
}
