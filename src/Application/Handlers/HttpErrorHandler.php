<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use Error;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ActionError(
            ActionError::SERVER_ERROR,
            'An internal error has occurred while processing your request.'
        );

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $error->setDescription($exception->getMessage());

            if ($exception instanceof HttpNotFoundException) {
                $error->setType(ActionError::RESOURCE_NOT_FOUND);
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $error->setType(ActionError::NOT_ALLOWED);
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $error->setType(ActionError::UNAUTHENTICATED);
            } elseif ($exception instanceof HttpForbiddenException) {
                $error->setType(ActionError::INSUFFICIENT_PRIVILEGES);
            } elseif ($exception instanceof HttpBadRequestException) {
                $error->setType(ActionError::BAD_REQUEST);
            } elseif ($exception instanceof HttpNotImplementedException) {
                $error->setType(ActionError::NOT_IMPLEMENTED);
            }
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {
            $statusCode = $exception->getCode();
            
            if ( $statusCode == 400 ) {
                $error->setType(ActionError::BAD_REQUEST);
            } elseif ( $statusCode == 401 ) {
                $error->setType(ActionError::UNAUTHENTICATED);
            } elseif ( $statusCode == 405 ) {
                $error->setType(ActionError::NOT_ALLOWED);
            } elseif ( $statusCode == 404 ) {
                $error->setType(ActionError::RESOURCE_NOT_FOUND);
            } elseif ( $statusCode == 403 ) {
                $error->setType(ActionError::INSUFFICIENT_PRIVILEGES);
            } elseif ( $statusCode == 409 ) {
                $error->setType(ActionError::CONFLICT_ERROR);
            } else {
                $error->setType(ActionError::SERVER_ERROR);
            }

            $error->setDescription($exception->getMessage());
        }

        // Custom error code 56 is for server database error
        if (
            $exception instanceof \PDOException
        ) {
            $statusCode = 500;
            $error->setType(ActionError::SERVER_ERROR);
            
            $error->setDescription($exception->getMessage());
        }

        // Custom error code 57 for errors that instance of Error object
        if (
            $exception instanceof Error
        ) {
            $statusCode = 500;
            $error->setType(ActionError::SERVER_ERROR);

            $error->setDescription($exception->getMessage());
        }

        $payload = new ActionPayload($statusCode, null, $error);
        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);
        
        try {
            $response = $this->responseFactory->createResponse($statusCode);
        } catch (\Throwable $th) {
            $response = $this->responseFactory->createResponse(500);
        }
        
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}