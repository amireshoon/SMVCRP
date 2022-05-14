<?php
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use \RedBeanPHP\R as R;

require __DIR__ . '/../vendor/autoload.php';

// set timezone to tehran
date_default_timezone_set('Asia/Tehran');

// $bs = new App\Application\Models\BaseModel();
define( 'REDBEAN_MODEL_PREFIX', '' );

R::setup(
	'mysql:host=localhost;dbname=pq',
	'root',
	''
);

// Loading environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// $user = R::dispense( 'user' );
// $user->name = 'John';
// $user->email = '';
// $user->password = '';
// $user->created_at = date( 'Y-m-d H:i:s' );
// $user->updated_at = date( 'Y-m-d H:i:s' );
// $user->deleted_at = null;
// $user->id = R::store( $user );
// $user = new App\Application\Models\User( );
// $user->phone = '09226742397';
// $user->fullname = 'Amirhossein Meydani';
// $user->sf_password = 'safasf';
// $user->avatar = 1;
// $user->nickname = 'Amirhwsin';
// $user->last_login = date( 'Y-m-d H:i:s' );
// $user->created_at = date( 'Y-m-d H:i:s' );
// $user->updated_at = date( 'Y-m-d H:i:s' );
// $id = $user->save();
// echo 'Row id is: ' . $id;
// if ( $user->is_john() ) {
// 	echo 'John is here';
// }
// $token = new App\Application\Models\Token();
// $token->content = '123';
// $token->user_id = 1;
// $token->type = 'ACCESS_TOKEN';
// $token->parent_id = 1;
// $token->created_at = date( 'Y-m-d H:i:s' );
// $token->expired_at = date( 'Y-m-d H:i:s' );
// $token->save();
// exit;
// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Loaded common function
require_once __DIR__ . '/../app/functions.php';

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Upload directory
$container->set('upload_directory', __DIR__ . '/assets');

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
