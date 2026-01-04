<?php

namespace App\Controllers;

use Sockeon\Sockeon\Controllers\SocketController;
use Sockeon\Sockeon\WebSocket\Attributes\OnConnect;

class WelcomeController extends SocketController
{
    #[OnConnect]
    public function onConnect(string $clientId): void
    {
        $this->broadcast('connected', [
            'message' => 'Welcome to our chatapp',
            'clientId' => $clientId
        ]);
    }
}