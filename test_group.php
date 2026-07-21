<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(2);
$request = Illuminate\Http\Request::create('/chat/groups', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });

$controller = new App\Http\Controllers\ChatGroupController();
$response = $controller->index($request);

echo json_encode($response->getData());
