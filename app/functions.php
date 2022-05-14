<?php
/**
 * Common functions to avoid repetition
 * 
 * @since   1.0.0
 */

declare(strict_types=1);

/**
 * Make a response with a JSON body
 * 
 * @param  \Psr\Http\Message\ResponseInterface  response
 * @param   array                               data
 * @param   int                                 status
 * @param   bool                                error
 * @param   string                              message
 * @return \Psr\Http\Message\ResponseInterface
 */
function make($response, $data, $code = 200, $error = false, $message = null ) {
    $payload['statusCode'] = $code;

    if ( $error ) {
        $payload['error'] = [
            'type' => $code,
            'description' => $message
        ];
    }

    if ( !$error )
        $payload['data'] = $data;
    
    $response->getBody()->write(
        json_encode($payload,
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        )
    );

    return $response
        ->withHeader("Content-Type", "application/json")
        ->withStatus($code);
    exit;
}

function make_error($response, $message, $code = 400) {
    return make($response, null, $code, true, $message);
}

function get_request() {
    $request = json_decode(file_get_contents('php://input'), true);

    if (null !== $request)
        return $request;

    throw new Exception("Request body is empty.", 400);
    
}

function have( $request, $args, $throw = true ) {
    foreach ($args as $arg) {

        if ( !array_key_exists($arg, $request) ) {
            if ( $throw ) {
                throw new Exception("Field `{$arg}` not implemented in the request.", 400);
            }
        }else {
            return false;
        }

    }

    return true;
}

function optional($request, $field, $default = null) {
    return (array_key_exists($field, $request)) ? $request[$field] : $default;
}

function public_path($path = '') {
    return __DIR__ . '/../public/' . $path;
}

function is_curse( $word ) {
    $curses = [
        'fuck',
        'کص',
        'کصخل',
        'کص دست',
        'کصدست',
        'کصخل',
        'کصکش',
        'کیر',
        'کیری',
        'کیر خر',
        'کص نگو',
        'کصشر',
        'کص شعر',
        'تخمی',
        'کونی',
        'کون',
        'کاندوم',
        'کبص',
        'کبصشر',
        'کبص',
        'کبص نگو',
        'کس',
        'کس نگو',
        'کس کش',
        'کس کون',
        'کس کونی',
        'کس کص',
        'کس کص نگو',
        'کس کصخل',
        'کس کصدست',
        'کس کصشر',
        'کس کصخل',
        'دیوث',
        'بوبول',
        'بولی',
        'ممه',
        'بیا اینو بخور',
        'بیا بخورش',
        'بیا بخور',
        'بخورش',
        'پی فا',
        'پیفا',
        'پی فا 24',
        'اسکل',
        'اسکول',
        'kir',
        'kos',
        'koskesh',
        'kirkesh',
        'koni',
        'konni',
        'خایه',
        'بی خایه',
        'باسنی',
    ];

    if ( in_array(strtolower($word), $curses) ) return true;

    foreach ($curses as $word) {
        if ( strpos($word, $word) !== false ) {
            return true;
        }
    }

    return false;
}