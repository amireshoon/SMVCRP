<?php

namespace App\Application\Controllers;

interface BaseController {

    public function __invoke( $request, $response );

}