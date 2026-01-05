<?php

namespace App\Controllers;

use App\Middlewares\HttpAuthenticationMiddleware;
use App\Repositories\UserRepository;
use Sockeon\Sockeon\Controllers\SocketController;
use Sockeon\Sockeon\Http\Attributes\HttpRoute;
use Sockeon\Sockeon\Http\Request;
use Sockeon\Sockeon\Http\Response;
use Throwable;

class AuthController extends SocketController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    #[HttpRoute('POST', '/api/register')]
    public function register(Request $request): Response
    {
        try {
            $data = $request->validated([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|string|max:255'
            ]);

            $existingUser = $this->userRepository->first('email', $data['email']);
            if (!empty($existingUser)) {
                return Response::json([
                    'status' => false,
                    'errors' => [
                        'email' => ['The email field is already taken']
                    ],
                    'message' => 'The email field is already taken'
                ], 400);
            }

            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            $user = $this->userRepository->create($data);
            unset($user['password']);
            return Response::created([
                'status' => true,
                'data' => $user,
                'message' => 'User created successfully!!'
            ]);

        } catch (Throwable $e) {
            print_r($e);
            return Response::json([
                'status' => false,
                'errors' => $request->getValidationErrors(),
                'message' => $request->getValidationErrors()[array_keys($request->getValidationErrors())[0]][0]
            ], 400);
        }

    }

    #[HttpRoute('POST', '/api/login')]
    public function login(Request $request): Response
    {
        try {
            $data = $request->validated([
                'email' => 'required|email|max:255',
                'password' => 'required|string|max:255'
            ]);

            $user = $this->userRepository->first('email', $data['email']);
            if (empty($user)) {
                return Response::json([
                    'status' => false,
                    'errors' => [
                        'email' => ['The provided credentials are incorrect']
                    ],
                    'message' => 'The provided credentials are incorrect'
                ], 401);
            }

            if (!password_verify($data['password'], $user['password'])) {
                return Response::json([
                    'status' => false,
                    'errors' => [
                        'email' => ['The provided credentials are incorrect']
                    ],
                    'message' => 'The provided credentials are incorrect'
                ], 401);
            }

            if ($token = $this->userRepository->createToken($user['id'])) {
                unset($user['password']);
                return Response::ok([
                    'status' => true,
                    'data' => [
                        'user' => $user,
                        'token' => $token
                    ],
                    'message' => 'User logged in successfully!!'
                ]);
            } else {
                return Response::json([
                    'status' => false,
                    'errors' => [],
                    'message' => 'Some error occurred. Please try again later'
                ], 500);
            }

        } catch (Throwable $e) {
            return Response::json([
                'status' => false,
                'errors' => $request->getValidationErrors(),
                'message' => $request->getValidationErrors()[array_keys($request->getValidationErrors())[0]][0]
            ], 400);
        }
    }

    #[HttpRoute('GET', '/api/user', [HttpAuthenticationMiddleware::class])]
    public function getLoggedInUser(Request $request): Response
    {
        $user = $request->getAttribute('user');
        unset($user['password']);
        return Response::ok([
            'status' => true,
            'data' => [
                'user' => $user,
            ],
            'message' => 'User logged in successfully!!'
        ]);
    }
}