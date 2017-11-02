<?php

namespace App\ComModules;

use App\One\One;
use Exception;



class TrackingRegistror
{

    public static function saveTrackingRequestsDataToDB($tableKey, $url, $moduleToken, $method, $result, $time_start)
    {

        $response = One::Post(
            [

                "component" => "logs",
                "api" => "TrackingController",
                "method" => "saveTrackingRequestDataToDB",
                'params' => [
                    'table_key' => $tableKey,
                    'url' => $url,
                    'method' => $method,
                    'module_token' => $moduleToken,
                    'result' => $result,
                    'time_start' => $time_start

                ]
            ]
        );
        // dd($response->content());

    }

    public static function getLastTrackingKey()
    {

        $response = One::Post(
            [

                "component" => "logs",
                "api" => "TrackingController",
                "method" => "getLastTrackingKey"
            ]
        );
        return $response->content();

    }

}