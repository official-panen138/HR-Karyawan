<?php

namespace App\Http\Middleware;

use App\Models\FeatureFlag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlag
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $flag = FeatureFlag::where('feature_key', $featureKey)->first();

        if (!$flag || !$flag->isEnabledFor($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur tidak tersedia',
            ], 403);
        }

        return $next($request);
    }
}
