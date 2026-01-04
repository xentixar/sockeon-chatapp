<?php

namespace App\Controllers;

use Sockeon\Sockeon\Controllers\SocketController;
use Sockeon\Sockeon\WebSocket\Attributes\SocketOn;

class ChatController extends SocketController
{
    #[SocketOn('send.message')]
    public function handleSendMessageEvent(string $clientId, array $data): void
    {
        $users = $this->getAllClients();
        foreach ($users as $user) {
            $this->emit($user, 'message.received', [
                'message' => $data['message'],
                'username' => $clientId,
                'timestamps' => date('Y-m-d H:i:s'),
                'is_me' => $user == $clientId
            ]);
        }
    }

    #[SocketOn('message.typing.start')]
    public function handleMessageTypingStartEvent(string $clientId, array $data): void
    {
        $users = $this->getAllClients();
        foreach ($users as $user) {
            if ($user == $clientId) {
                continue;
            }

            $this->emit($user, 'message.typing.start.received', [
                'clientId' => $clientId
            ]);
        }
    }

    #[SocketOn('message.typing.stop')]
    public function handleMessageTypingStopEvent(string $clientId, array $data): void
    {
        $users = $this->getAllClients();
        foreach ($users as $user) {
            if ($user == $clientId) {
                continue;
            }

            $this->emit($user, 'message.typing.stop.received', [
                'clientId' => $clientId
            ]);
        }
    }
}