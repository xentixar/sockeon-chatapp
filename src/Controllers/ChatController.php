<?php

namespace App\Controllers;

use App\Middlewares\HttpAuthenticationMiddleware;
use App\Repositories\MessageRepository;
use Sockeon\Sockeon\Controllers\SocketController;
use Sockeon\Sockeon\Http\Attributes\HttpRoute;
use Sockeon\Sockeon\Http\Request;
use Sockeon\Sockeon\Http\Response;
use Sockeon\Sockeon\Validation\Validator;
use Sockeon\Sockeon\WebSocket\Attributes\SocketOn;
use Throwable;

class ChatController extends SocketController
{
    private MessageRepository $messageRepository;

    public function __construct()
    {
        $this->messageRepository = new MessageRepository();
    }

    #[SocketOn('send.message')]
    public function handleSendMessageEvent(string $clientId, array $data): void
    {
        try {
            $validator = new Validator();
            $validator->validate($data, [
                'message' => 'required'
            ]);

            $currentUser = $this->getClientData($clientId, 'user');
            $this->messageRepository->storeMessage($data['message'], $currentUser['id']);

            $users = $this->getAllClients();
            foreach ($users as $user) {
                $this->emit($user, 'message.received', [
                    'message' => $data['message'],
                    'username' => $currentUser['name'],
                    'timestamps' => date('Y-m-d H:i:s'),
                    'is_me' => $user == $clientId
                ]);
            }

        } catch (Throwable $e) {
            $this->emit($clientId, 'error', [
                'message' => $e->getMessage()
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

    #[HttpRoute('GET', '/api/messages', [HttpAuthenticationMiddleware::class])]
    public function getMessages(Request $request): Response
    {
        return Response::ok([
            'status' => true,
            'message' => 'Messages fetched successfully',
            'data' => $this->messageRepository->getMessages($request->getAttribute('user')['id'])
        ]);
    }
}