<?php

namespace App\Middlewares;

use App\Repositories\UserRepository;
use Sockeon\Sockeon\Connection\Server;
use Sockeon\Sockeon\Contracts\Http\HttpMiddleware;
use Sockeon\Sockeon\Http\Request;
use Sockeon\Sockeon\Http\Response;

class HttpAuthenticationMiddleware implements HttpMiddleware
{
    public function handle(Request $request, callable $next, Server $server): mixed
    {
        $userRepository = new UserRepository();

        $accessToken = $request->getHeader('Authorization');
        if (!$accessToken || (!($user = $userRepository->getUserByToken($accessToken)))) {
            return Response::json([
                'status' => false,
                'errors' => [],
                'message' => 'You have to be authenticated to access the resource'
            ], 401);
        };

        $request->setAttribute('user', $user);
        return $next($request);
    }
}