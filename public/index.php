<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| OSPanel subdirectory routing (GET/POST/formlar uchun)
|--------------------------------------------------------------------------
|
| /81-maktab/public/logout kabi yo'llar Laravel route'iga to'g'ri tushishi uchun.
|
*/

$localPublicBase = '/81-maktab/public';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (str_starts_with($requestPath, $localPublicBase.'/')
    && ! str_starts_with($requestPath, $localPublicBase.'/index.php')
    && ! preg_match('#^'.preg_quote($localPublicBase, '#').'/(?:storage|temp|build|panel-assets)/#', $requestPath)) {
    $routePath = substr($requestPath, strlen($localPublicBase)) ?: '/';

    $_SERVER['SCRIPT_NAME'] = $localPublicBase.'/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/index.php';
    $_SERVER['PHP_SELF'] = $localPublicBase.'/index.php'.$routePath;
    $_SERVER['PATH_INFO'] = $routePath;
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
