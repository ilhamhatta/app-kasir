<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogger
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000);

        $time   = now()->format('H:i:s');
        $method = $request->method();
        $path   = '/' . ltrim($request->path(), '/');
        $status = $response->getStatusCode();

        Log::info(sprintf(
            '%s %s %s %d %dms',
            $time,
            $method,
            $path,
            $status,
            $duration
        ));

        return $response;
    }
}
