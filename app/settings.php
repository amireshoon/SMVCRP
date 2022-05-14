<?php
declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => ( $_ENV['DISPLAY_ERROR_DETAILS'] === 'true') ? true : false, // Should be set to false in production
                'logError'            => ( $_ENV['LOG_ERROR'] === 'true') ? true : false,
                'logErrorDetails'     => ( $_ENV['LOG_ERROR_DETAILS'] === 'true') ? true : false,
                'logger' => [
                    'name' => 'payfaQuiz',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ]);
        }
    ]);
};
