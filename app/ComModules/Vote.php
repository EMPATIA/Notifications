<?php

namespace App\ComModules;

use Exception;
use App\One\One;

class Vote {
    public static function processSMSVote($userKey, $topicKey, $eventKey, $voteValue) {
        $response = ONE::post([
            'component' => 'vote',
            'api'       => 'smsVote',
            'params'    => [
                'user'  => $userKey,
                'vote'  => $topicKey,
                'event' => $eventKey,
                'value' => $voteValue
            ]
        ]);
        
        return [
            "data" => $response->json(),
            "http" => $response->statusCode()
        ];
    }
}