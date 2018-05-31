<?php

namespace App\ComModules;

use App\One\One;
use Exception;

class EMPATIA {
    public static function processSMSVote($data) {
        $response = ONE::post([
            'component' => 'empatia',
            'api'       => 'smsVote',
            'params'    => [
                'sms' => $data
            ]
        ]);
        
        return [
            "data" => $response->json(),
            "http" => $response->statusCode()
        ];
    }

    public static function getSiteSMSConfigurations($siteKey) {
        $response = ONE::post([
            'headers' => [
                "X-SITE-KEY: " . $siteKey
            ],
            'component' => 'empatia',
            'api'       => 'smsVote',
            'method'    => 'getSMSConfigurations'
        ]);
        
        return [
            "data" => $response->json(),
            "http" => $response->statusCode()
        ];
    }
    /**
     * @return array with all users
     * @throws Exception
     */
    public static function getUsers()
    {
        $response = ONE::post([
            'component' => 'empatia',
            'api'       => 'auth',
            'method'    => 'listNames',
            'params' => [
                'analytics'=> 1,
            ]
        ]);
        if($response->statusCode() != 200){
            throw new Exception(trans("comModulesEMPATIA.errorGetUsers"));
        }
        return $response->json()->users;
    }
}