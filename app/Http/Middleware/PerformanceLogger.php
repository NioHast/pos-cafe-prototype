<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceLogger
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Track query time
        DB::listen(function ($query) {
            Log::channel('performance')->info('Query', [
                'sql' => $query->sql,
                'time' => $query->time . 'ms',
                'bindings' => $query->bindings,
            ]);
        });

        $response = $next($request);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsed = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);
        
        // Log performance metrics
        Log::channel('performance')->info('Request Performance', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => $executionTime . 'ms',
            'memory_used' => $memoryUsed . 'MB',
            'timestamp' => now()->toDateTimeString(),
            'db_queries' => DB::getQueryLog(),
        ]);

        return $response;
    }
}
