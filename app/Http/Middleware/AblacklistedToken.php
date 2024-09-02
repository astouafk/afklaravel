<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BlacklistedToken;
use Symfony\Component\HttpFoundation\Response;

class AblacklistedToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifie que l'utilisateur est authentifié
        if ($request->user()) {
            // Vérifie si le token d'accès est dans la liste noire
            $blacklistedAccessToken = BlacklistedToken::where('token', $request->bearerToken())
                ->where('type', 'access')
                ->first();

            if ($blacklistedAccessToken) {
                return response()->json([
                    'data' => null,
                    'status' => 'error',
                    'message' => 'Token révoqué.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }

    // public function handle(Request $request, Closure $next)
    // {
    //     $token = $request->bearerToken();

    //     if ($token && BlacklistedToken::where('token', $token)->exists()) {
    //         return response()->json(['message' => 'Token is blacklisted'], 401);
    //     }

    //     return $next($request);
    // }
}

