<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST')) {
            $sessionToken = null;
            $tokensMatch = false;
            
            try {
                if ($request->hasSession()) {
                    $sessionToken = $request->session()->token();
                    $tokensMatch = $request->input('_token') === $sessionToken;
                }
            } catch (\Exception $e) {
                // Session not available yet
            }
            
            Log::info("POST Request Received", [
                'url' => $request->url(),
                'has_csrf_token' => $request->has('_token'),
                'csrf_token' => $request->input('_token') ? substr($request->input('_token'), 0, 20) . '...' : 'NONE',
                'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : 'NONE',
                'tokens_match' => $tokensMatch,
                'has_file' => $request->hasFile('zip_file'),
                'content_length' => $request->header('Content-Length'),
            ]);
        }

        return $next($request);
    }
}
