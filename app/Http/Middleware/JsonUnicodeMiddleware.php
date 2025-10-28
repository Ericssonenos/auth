<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;

class JsonUnicodeMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE);
        }

        return $response;
    }
}

