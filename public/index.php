<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appBase = dirname(__DIR__);
$envFile = $appBase.'/.env';
$envExample = $appBase.'/.env.example';
$configCache = $appBase.'/bootstrap/cache/config.php';

if (! file_exists($envFile) && file_exists($envExample)) {
    copy($envExample, $envFile);
}

if (file_exists($envFile)) {
    $envContents = file_get_contents($envFile);
    if (! preg_match('/^APP_KEY=.+/m', $envContents)) {
        $appKey = base64_encode(random_bytes(32));
        file_put_contents($envFile, PHP_EOL.'APP_KEY=base64:'.$appKey.PHP_EOL, FILE_APPEND | LOCK_EX);
        if (file_exists($configCache)) {
            @unlink($configCache);
        }
    }
}

if (file_exists($configCache) && ! getenv('APP_KEY')) {
    @unlink($configCache);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
