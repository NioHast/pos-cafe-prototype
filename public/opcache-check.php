<?php
// Temporary file to check OPcache status
// Delete after verification

$status = opcache_get_status();

if ($status === false) {
    die("❌ OPcache is DISABLED");
}

echo "<h2>✅ OPcache is ACTIVE</h2>";
echo "<pre>";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 OPcache Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Memory Usage
$memory = $status['memory_usage'];
echo "💾 Memory Usage:\n";
echo "   Used:       " . round($memory['used_memory'] / 1024 / 1024, 2) . " MB\n";
echo "   Free:       " . round($memory['free_memory'] / 1024 / 1024, 2) . " MB\n";
echo "   Wasted:     " . round($memory['wasted_memory'] / 1024 / 1024, 2) . " MB\n";
echo "   Total:      " . round(($memory['used_memory'] + $memory['free_memory'] + $memory['wasted_memory']) / 1024 / 1024, 2) . " MB\n";
echo "\n";

// Cache Statistics
$stats = $status['opcache_statistics'];
echo "📈 Cache Statistics:\n";
echo "   Cached Files:    " . number_format($stats['num_cached_scripts']) . " / 20,000\n";
echo "   Hit Rate:        " . round($stats['opcache_hit_rate'], 2) . "%\n";
echo "   Hits:            " . number_format($stats['hits']) . "\n";
echo "   Misses:          " . number_format($stats['misses']) . "\n";
echo "\n";

// Configuration
$config = opcache_get_configuration()['directives'];
echo "⚙️  Configuration:\n";
echo "   Enabled:                    " . ($config['opcache.enable'] ? 'Yes' : 'No') . "\n";
echo "   Memory Consumption:         " . $config['opcache.memory_consumption'] . " MB\n";
echo "   Interned Strings Buffer:    " . $config['opcache.interned_strings_buffer'] . " MB\n";
echo "   Max Accelerated Files:      " . number_format($config['opcache.max_accelerated_files']) . "\n";
echo "   Validate Timestamps:        " . ($config['opcache.validate_timestamps'] ? 'Yes (Dev)' : 'No (Prod)') . "\n";

// Performance Analysis
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Performance Analysis\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$usage_percent = ($stats['num_cached_scripts'] / $config['opcache.max_accelerated_files']) * 100;
$memory_percent = ($memory['used_memory'] / ($memory['used_memory'] + $memory['free_memory'])) * 100;

echo "✅ File Cache Capacity:  " . round($usage_percent, 1) . "% used\n";
if ($usage_percent > 90) {
    echo "   ⚠️  WARNING: Consider increasing max_accelerated_files!\n";
} else {
    echo "   ✓ Healthy capacity\n";
}

echo "\n✅ Memory Usage:         " . round($memory_percent, 1) . "% used\n";
if ($memory_percent > 90) {
    echo "   ⚠️  WARNING: Consider increasing memory_consumption!\n";
} else {
    echo "   ✓ Healthy memory usage\n";
}

if ($stats['opcache_hit_rate'] < 95) {
    echo "\n⚠️  Hit rate is low. Cache may still be warming up.\n";
    echo "   Refresh your Laravel app several times to warm the cache.\n";
} else {
    echo "\n✅ Excellent hit rate! OPcache is working optimally.\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "</pre>";

echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER VERIFICATION!</p>";
echo "<p>This file exposes sensitive server information.</p>";
