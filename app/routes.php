<?php
declare(strict_types=1);

use App\Application\Controllers\AdminController;
use App\Application\Controllers\RefreshToken;
use App\Application\Controllers\LoginController;
use App\Application\Controllers\OTPController;
use App\Application\Controllers\RegisterController;
use App\Application\Controllers\UserUpdateController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Controllers\GameController;
use App\Application\Controllers\HomeController;
use App\Application\Controllers\LeaderboardController;
use App\Application\Controllers\LogoutController;
use App\Application\Controllers\PlayController;
use App\Application\Controllers\TaskController;
use App\Application\Controllers\WalletController;
use App\Application\Models\Avatar;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/ping', function (Request $request, Response $response) use($app) {
        $handler = time();
        $response->getBody()->write(
            (string)$handler
        );
        return $response;
    });
};
