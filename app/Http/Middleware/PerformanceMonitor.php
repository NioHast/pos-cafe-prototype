<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = round(($endTime - $startTime) * 1000, 2); // ms
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2); // MB
        
        // Log performance metrics
        Log::channel('performance')->info('Request Performance', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time_ms' => $executionTime,
            'memory_used_mb' => $memoryUsed,
            'queries' => DB::getQueryLog(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Add headers for debugging
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $memoryUsed . 'MB');
        
        return $response;
    }
}
