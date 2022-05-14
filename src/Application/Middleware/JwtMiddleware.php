<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Application\Handlers\JWTTokenHandler as JWT;
use App\Application\Models\Tracker;

class JwtMiddleware implements Middleware
{

    private $secret = null;
    private $ignore = [];
    private $scopes = [];

    /**
     * JwtAuthentication constructor.
     * 
     * @since   1.0
     * @param   string
     * @param   array
     * @param   array
     */
    public function __construct(
        $secret,
        $ignore = [],
        $scopes = []
    ) {
        $this->secret = $secret;
        $this->ignore = $ignore;
        $this->scopes = $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        
        // If the request is not an OPTIONS request, then we skip authorization..
        if ( $request->getMethod() === 'OPTIONS' ) {
            return $handler->handle($request);
        }

        if ( !empty($this->ignore) ) {
            if ( $this->ignore($request) ) {
                return $handler->handle($request);
            }
        }

        if ( !isset($_SERVER['HTTP_AUTHORIZATION']) ) {
            throw new \Exception("AUTHORIZATION FAILED", 401);   
        }
        
        // spliting token from Bearer 
        $token = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

        // If authorization value on header is not right
        if ( !isset($token[1]) )
            throw new \Exception("Token is not valid token.", 401);
            
        $token = $token[1];
        
        if ( ! JWT::validate($token, $this->secret) ) {
            throw new \Exception("Access denied.", 401);
        }

        $payload = JWT::payload( $token, $this->secret );

        // If expiration field is not implemented on jwt token
        if ( !isset( $payload['exp'] ) ) {
            throw new \Exception("Expiration value missing.", 401);
        }else {
            // If token is expired
            if ( $payload['exp'] < time() ) {
                throw new \Exception("Token is expired.", 401);
            }
        }

        // Adding jwt payload to token attribute
        $request = $request->withAttribute( 'token', $payload);

        // Add user scope to attribute
        // foreach ($this->scopes as $scope) {
        //     if ( $scope === $payload['scope'] ) {
        //         $request = $request->withAttribute( 'scope', $scope);
        //     }else{
        //         $request = $request->withAttribute( 'scope', 'none');
        //     }
        // }

        $tracker = new Tracker();
        $tracker->tracked_at = time();
        $tracker->uri = $request->getUri()->getPath();
        $tracker->ip = $request->getServerParams()['REMOTE_ADDR'];
        $tracker->save();

        return $handler->handle($request);
    }

    /**
     * If some paths are ignored for authentication this will handle them
     * 
     * Can handle * pattern
     * for ex: /load/*
     */
    private function ignore( $request ) {
        $path = $request->getUri()->getPath();

        // If it's already matches the path
        foreach ($this->ignore as $ignore) {
            if ( $ignore === $path ) {
                return true;
            }
        }

        // Other wise check * pattern

        $path = explode('/', $path);

        foreach ($this->ignore as $ignore) {

            $ignore = explode('/', $ignore);

            if ( sizeof($ignore) <= 0 )
                return true;
            
            foreach ($ignore as $i => $value) {
                if ( isset( $path[$i] ) ) {
                    if ( $path[$i] === $value ) {
                        continue;
                    }else {
                        if ( $value === "*" ) {
                            return true;
                        }
                        break;
                    }
                }else {
                    break;
                }
            }
        }

        return false;
    }
}