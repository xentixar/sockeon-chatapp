<?php

namespace App\Controllers;

use Sockeon\Sockeon\Controllers\SocketController;
use Sockeon\Sockeon\Http\Attributes\HttpRoute;
use Sockeon\Sockeon\Http\Request;
use Sockeon\Sockeon\Http\Response;

class StaticController extends SocketController
{
    #[HttpRoute('GET', '/')]
    public function serveHomePage(Request $request): Response
    {
        return self::getStaticContent("index.html");
    }

    #[HttpRoute('GET', '/{file}')]
    public function serveStaticFiles(Request $request): Response
    {
        return self::getStaticContent($request->getParam('file'));
    }

    private static function getStaticContent(string $file): Response
    {
        $path = __DIR__ . "/../../public/{$file}";
        if (!file_exists($path)) {
            return Response::notFound();
        }

        $content = file_get_contents($path);
        $contentType = self::getContentType(pathinfo($path, PATHINFO_EXTENSION));

        $response = new Response(body: $content, headers: [
            'Content-Type' => $contentType
        ]);
        return $response->setContentType($contentType);
    }

    private static function getContentType(string $extension): string
    {
        print_r($extension);
        return match ($extension) {
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'text/javascript',
        };
    }
}