<?php

namespace App\Application\Handlers;

use ReallySimpleJWT\Token;
use App\Application\Models\Token as TokenModel;

class JWTTokenHandler {
    

    public static function new( $user_id, $expiration = 86400, $scope = 'user')  {
        
        $token = Token::customPayload([
            'iat'   =>  time(),
            'sub'   =>  $user_id,
            'exp'   =>  time() + $expiration,
        ], $_ENV['JWT_SECRET']);
        return $token;
    }

    public static function validate( $jwt, $secret ) {

        $token = new TokenModel();
        $token = $token->with_token( $jwt );

        // If token is not found in database
        if ( is_bool( $token ) )
            return false;
        
        return Token::validate( $jwt, $secret);
    }

    public static function payload( $jwt, $secret ) {
        return Token::getPayload( $jwt, $secret );
    }
}