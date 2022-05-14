<?php
/**
 * This file runs every 5 minutes to handle game ends
 * 
 * @since   1.0.0
 */

$f = fopen( 'logs.sc', 'w' );
fwrite( $f, 'Running => ' . time() );
fclose( $f );


// get game ids that handled before

// get games that are not in the list

// proccess games

// add game ids to handled games

class EndGameTasks {
    public function run() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://quizofcrypto.com/internal/tasks/endgame',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "token":"secret"
        }',
        CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }

        curl_close($curl);
    }
}

$task = new EndGameTasks();
$task->run();