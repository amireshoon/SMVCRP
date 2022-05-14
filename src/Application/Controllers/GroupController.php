<?php

namespace App\Application\Controllers;

abstract class GroupController {
   
    protected $controller = null;

    public function __construct( $controller ) {
        $this->controller = $controller;
    }

    public function control( $callable, $request, $response, $args ) {
        return call_user_func_array( [ $this->controller, $callable], [$request, $response, $args] );
    }

    public function __get($callable) {
        // Get controller object
        $controller = $this->controller;

        return function( $request, $response, $args ) use ($callable, $controller) {
            return $controller->control( $callable, $request, $response, $args );
        };
    }
}
