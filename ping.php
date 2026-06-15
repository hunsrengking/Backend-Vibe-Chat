<?php

/**
 * Render Self-Ping Service
 * This script runs in the background of the Docker container.
 * It periodically pings the public Render URL to keep the container active and prevent auto-sleeping.
 */

// 1. Initial startup delay to allow Apache to fully start up and serve requests
$startupDelay = 60;
echo "[Self-Ping] Service started. Waiting {$startupDelay} seconds for Apache to initialize...\n";
sleep($startupDelay);

// 2. Determine the target URL to ping
// Render automatically provides RENDER_EXTERNAL_URL in its environment.
// Fallback to APP_URL if RENDER_EXTERNAL_URL is not set.
$targetUrl = getenv('RENDER_EXTERNAL_URL') ?: getenv('APP_URL');

if (!$targetUrl || $targetUrl === 'http://127.0.0.1:8000' || $targetUrl === 'http://localhost') {
    echo "[Self-Ping] No valid external URL found (RENDER_EXTERNAL_URL or APP_URL). Self-pinging disabled.\n";
    exit(0);
}

// Normalize the URL
if (!str_starts_with($targetUrl, 'http://') && !str_starts_with($targetUrl, 'https://')) {
    $targetUrl = 'https://' . $targetUrl;
}

// Use the API health check endpoint (enables CORS and uses route middlewares)
$pingUrl = rtrim($targetUrl, '/') . '/api/health';

echo "[Self-Ping] Target URL configured: {$pingUrl}\n";
echo "[Self-Ping] Starting periodic pings (every 10 minutes)...\n";

while (true) {
    try {
        $startTime = microtime(true);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 15,
                'header' => "User-Agent: VibeChat-SelfPinger/1.0\r\n"
            ],
            // Ignore SSL verification issues if any (common in some internal routing scenarios)
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $response = file_get_contents($pingUrl, false, $context);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        if ($response !== false) {
            echo "[" . date('Y-m-d H:i:s') . "] [Self-Ping] Ping successful! Response time: {$duration}ms\n";
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] [Self-Ping] Ping failed (no response content).\n";
        }
    } catch (\Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] [Self-Ping] Ping failed: " . $e->getMessage() . "\n";
    }
    
    // Sleep for 10 minutes (600 seconds) - well below Render's 15-minute auto-sleep limit
    sleep(600);
}
