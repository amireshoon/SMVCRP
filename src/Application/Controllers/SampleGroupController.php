<?php

namespace App\Application\Controllers;

use App\Application\Controllers\GroupController;

class SampleGroupController extends GroupController {
    
    public function __construct() {
        parent::__construct( $this );
    }

    public function getUsers( $request, $response, $args ) {
        return make( $response, [
            'data' => [
                'key' => 'value',
            ],
        ]);
    }

    public function removeUser( $request, $response, $args ) {
        return make( $response, [
            'message' => 'User removed',
        ], 200);
    }
}
