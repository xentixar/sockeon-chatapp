<?php

namespace App\Middlewares;

use App\Repositories\UserRepository;
use Sockeon\Sockeon\Connection\Server;
use Sockeon\Sockeon\Contracts\WebSocket\HandshakeMiddleware;
use Sockeon\Sockeon\Contracts\WebSocket\WebsocketMiddleware;
use Sockeon\Sockeon\Http\Response;
use Sockeon\Sockeon\WebSocket\HandshakeRequest;

class HandshakeAuthenticationMiddleware implements HandshakeMiddleware
{
    public function handle(string $clientId, HandshakeRequest $request, callable $next, Server $server): bool|array
    {
        $userRepository = new UserRepository();

        $accessToken = $request->getQueryParam('authToken');
        if (!$accessToken || (!($user = $userRepository->getUserByToken($accessToken)))) {
            return [
                'status' => 401,
                'statusText' => 'Unauthorized'
            ];
        };

        $server->setClientData($clientId, 'user', $user);
        return $next($request);
    }
}