<?php

namespace App\ComModules;

use App\One\One;
use Exception;

class Notify {
    public static function sendSMS($apiData, $recipient, $text, $siteKey) {
        $response = ONE::post([
            'headers' => [
                "X-SITE-KEY: " . $siteKey
            ],
            'component' => 'notify',
            'api' => 'sms',
            'method' => 'sendSMS',
            'params' => [
                'configurations' => $apiData,
                'recipient' => $recipient,
                'content' => $text
            ]
        ]);
        
        return $response->json();
    }
}