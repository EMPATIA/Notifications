<?php
namespace App\Http\Middleware;

use App\One\One;
use Closure;
use Exception;
use Illuminate\Auth\Guard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Request;
use Session;
use HttpClient;
use App\ComModules\TrackingRegistror;


class OneAuth
{
    /**
     * Create a new filter instance.
     *
     * @internal param Guard $auth
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validation = Cache::get($request->header('X-MODULE-TOKEN'));
        if(empty($validation)){
            if (!empty(env('COMPONENT_ORCHESTRATOR', null))){
                $requestModuleToken = [
                    'url' => env('COMPONENT_ORCHESTRATOR'). '/module/checkToken',
                    "headers"   => ["X-MODULE-TOKEN: ". $request->header('X-MODULE-TOKEN')],
                    'json' => true
                ];
                $response = HttpClient::GET($requestModuleToken);

                if($response->statuscode() == 200 || $response->statuscode() == 401){
                    Cache::put($request->header('X-MODULE-TOKEN') , $response->json(), 60);
                    if ( $response->statuscode() == 401){
                        return response()->json(['error' => 'Unauthorized Module'], 401)->send();
                    }
                }
                else{
                    return response()->json(['error' => 'Failed to verify Module Authorization'], 500)->send();
                }
            }else{
                Cache::put($request->header('X-MODULE-TOKEN') , true, 60);
            }

        }elseif($validation === 'false'){
            return response()->json(['error' => 'Unauthorized, Module blocked'], 401)->send();
        }

        if($request->header('PERFORMANCE')=='1') { 

            $methodRequest = $request->method();
            $urlRequest = $request->url();
            $result = app('Illuminate\Http\Response')->status();
            $moduleToken = env('MODULE_TOKEN');
            $trackingTableKey = TrackingRegistror::getLastTrackingKey();
            $time_start = microtime(true);

            TrackingRegistror::saveTrackingRequestsDataToDB($trackingTableKey, $urlRequest, $moduleToken, $methodRequest, $result, $time_start);
        }

        return $next($request);
    }

    public function terminate($request){

        if($request->header('PERFORMANCE')=='1'){

            $time_end = microtime(true);

            $response = One::Post([

                'component' => 'logs',
                'api' => 'TrackingController',
                'method' => 'updateTrackingRequestDataToDB',
                'params' => ["time_end"=> $time_end]
            ]);
        }

    }

}