<?php

/**
 * Vercel Serverless Function Entry Point
 * 
 * This file serves as the entry point for all requests
 * when deployed to Vercel's serverless infrastructure.
 */

// Point to the project root
$projectRoot = __DIR__ . '/..';

// Set working directory
chdir($projectRoot);

// Autoload
require_once $projectRoot . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once $projectRoot . '/bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
