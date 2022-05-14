<?php

namespace App\Application\Controllers;

use App\Application\Controllers\BaseController as BaseController;

class SampleController implements BaseController {
    
    public function __invoke( $request, $response ) {

        $req = get_request();

        have( $req, ['some_json_body'] );

        return make( $response, [
            'data' => [
                'key' => 'value',
            ],
        ]);
    }

}
