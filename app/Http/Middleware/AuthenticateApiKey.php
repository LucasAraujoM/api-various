<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Authenticate the request via a Bearer API key.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'message' => 'API key is required. Use Authorization: Bearer <your-key>.',
            ], 401);
        }

        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        // Bind the authenticated user to the request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
