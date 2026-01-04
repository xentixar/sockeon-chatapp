<?php

use App\Controllers\ChatController;
use App\Controllers\StaticController;
use App\Controllers\WelcomeController;
use Sockeon\Sockeon\Config\ServerConfig;
use Sockeon\Sockeon\Connection\Server;

require_once __DIR__ . "/../vendor/autoload.php";

$serverConfig = new ServerConfig();
$serverConfig->setHost('127.0.0.1');
$serverConfig->setPort('6001');
$serverConfig->setDebug(true);

$server = new Server($serverConfig);
$server->registerControllers([
    WelcomeController::class,
    ChatController::class,
    StaticController::class,
]);
$server->run();