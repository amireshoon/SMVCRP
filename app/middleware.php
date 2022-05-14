<?php
declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use App\Application\Middleware\JwtMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(new JwtMiddleware(
        $_ENV['JWT_SECRET'],
        [ '/oauth/*', '/o2/*', '/ping', '/internal/tasks/*' ],  // Ignore these routes
        [ 'player', 'manager' ]     // Only these scopes
    ));
};
