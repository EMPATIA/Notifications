<?php

namespace App\Http\Controllers;

use App\ReceivedSms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Sms;
use App\One\One;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

define("NET_ERROR", "Network+error,+unable+to+send+the+message");
define("SENDER_ERROR", "You+can+specify+only+one+type+of+sender,+numeric+or+alphanumeric");

define("SMS_TYPE_CLASSIC", "classic");
define("SMS_TYPE_CLASSIC_PLUS", "classic_plus");
define("SMS_TYPE_BASIC", "basic");
define("SMS_TYPE_TEST_CLASSIC", "test_classic");
define("SMS_TYPE_TEST_CLASSIC_PLUS", "test_classic_plus");
define("SMS_TYPE_TEST_BASIC", "test_basic");

class SMSController extends Controller
{
    function do_post_request($url, $data, $optional_headers = null)
    {
        if (!function_exists('curl_init')) {
            $params = array(
                'http' => array(
                    'method' => 'POST',
                    'content' => $data
                )
            );
            if ($optional_headers !== null) {
                $params['http']['header'] = $optional_headers;
            }
            $ctx = stream_context_create($params);
            $fp = @fopen($url, 'rb', false, $ctx);
            if (!$fp) {
                return 'status=failed&message=' . NET_ERROR;
            }
            $response = @stream_get_contents($fp);
            if ($response === false) {
                return 'status=failed&message=' . NET_ERROR;
            }
            return $response;
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Generic Client');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);

            if ($optional_headers !== null) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
            }

            $response = curl_exec($ch);
            curl_close($ch);
            if (!$response) {
                return 'status=failed&message=' . NET_ERROR;
            }
            return $response;
        }
    }

    public function skebbyGatewaySendSMS(Request $request)
    {

        //  return $request->all();

        $username = $request->json("username");
        $password = $request->json("password");
        $recipients = $request->json("recipients");
        $text = $request->json("text");
        $sms_type = $request->json("sms_type") ? '' : SMS_TYPE_CLASSIC;
        $sender_number = $request->json("sender_number") ?? '';
        $sender_string = $request->json("sender_string") ?? '';
        $user_reference = $request->json("user_reference") ?? '';
        $charset = $request->json("charset") ?? '';
        $optional_headers = $request->json("optional_headers");

        $url = 'http://gateway.skebby.it/api/send/smseasy/advanced/http.php';

        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        switch ($sms_type) {
            case SMS_TYPE_CLASSIC:
            default:
                $method = 'send_sms_classic';
                break;
            case SMS_TYPE_CLASSIC_PLUS:
                $method = 'send_sms_classic_report';
                break;
            case SMS_TYPE_BASIC:
                $method = 'send_sms_basic';
                break;
            case SMS_TYPE_TEST_CLASSIC:
                $method = 'test_send_sms_classic';
                break;
            case SMS_TYPE_TEST_CLASSIC_PLUS:
                $method = 'test_send_sms_classic_report';
                break;
            case SMS_TYPE_TEST_BASIC:
                $method = 'test_send_sms_basic';
                break;
        }



        if (empty($request->header('X-MODULE-TOKEN'))) {
            return response()->json(['error' => 'Failed to get module'], 400);
        } else {
            $moduleToken = $request->header('X-MODULE-TOKEN');
        }


        do {
            $rand = str_random(32);

            if (!($exists = Sms::whereSmsKey($rand)->exists())) {
                $key = $rand;
            }
        } while ($exists);


        $recipient=implode(" ",$recipients);

        $sms = Sms::create([
            'sms_key'     => $key,
            'module_token'  => $moduleToken,
            'sent'          => '0',
            'recipient'     => $recipient,
            'content'       => $text,
            'site_key' => $request->header('X-SITE-KEY'),
            'created_by' => $request->header('X-SITE-KEY'),
            'updated_by' => $request->header('X-SITE-KEY')
        ]);

        $parameters = 'method='
            . urlencode($method) . '&'
            . 'username='
            . urlencode($username) . '&'
            . 'password='
            . urlencode($password) . '&'
            . 'text='
            . urlencode($text) . '&'
            . 'recipients[]=' . implode('&recipients[]=', $recipients);

        if ($sender_number != '' && $sender_string != '') {
            parse_str('status=failed&message=' . SENDER_ERROR, $result);
            return $result;
        }
        $parameters .= $sender_number != '' ? '&sender_number=' . urlencode($sender_number) : '';
        $parameters .= $sender_string != '' ? '&sender_string=' . urlencode($sender_string) : '';

        $parameters .= $user_reference != '' ? '&user_reference=' . urlencode($user_reference) : '';


        switch ($charset) {
            case 'UTF-8':
                $parameters .= '&charset=' . urlencode('UTF-8');
                break;
            case '':
            case 'ISO-8859-1':
            default:
                break;
        }

        parse_str($this->do_post_request($url, $parameters, $optional_headers), $result);

        if ($result['status'] == 'success') {
            $sms->sent = 1;
            $sms->save();
        }

        return $result;
    }

    public function skebbyGatewayGetCredit($username, $password, $charset = '')
    {
        $url = "http://gateway.skebby.it/api/send/smseasy/advanced/http.php";
        $method = "get_credit";

        $parameters = 'method='
            . urlencode($method) . '&'
            . 'username='
            . urlencode($username) . '&'
            . 'password='
            . urlencode($password);

        switch ($charset) {
            case 'UTF-8':
                $parameters .= '&charset=' . urlencode('UTF-8');
                break;
            default:
        }

        parse_str($this->do_post_request($url, $parameters), $result);
        return $result;
    }


    public function getEntitySms(Request $request){

        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $smsList = Sms::whereSiteKey($entityKey)->get();

            return response()->json($smsList, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getReceivedEntitySms(Request $request){

        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $smsList = ReceivedSms::whereSiteKey($entityKey)->get();

            return response()->json($smsList, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSms(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSendedSms = Sms::whereSiteKey($entityKey)->count();

            return response()->json($totalSendedSms, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSms(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalReceivedSms = ReceivedSms::whereSiteKey($entityKey)->count();

            return response()->json($totalReceivedSms, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSmsVotes(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSmsVotes = ReceivedSms::whereSiteKey($entityKey)
                ->where ('answer', 'like' ,'%recebido%') ->count();

            return response()->json($totalSmsVotes, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSmsLast30D(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSmsLast30D = Sms::whereSiteKey($entityKey)
                ->whereDate('created_at', '>=' ,Carbon::now()->subMonth()->format('Y-m-d H:i:s')) ->count();

            return response()->json($totalSmsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSmsLast24H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSmsLast30D = Sms::whereSiteKey($entityKey)
                ->whereDate('created_at', '>=' ,Carbon::now()->subDay()->format('Y-m-d H:i:s')) ->count();

            return response()->json($totalSmsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSmsLast48H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalReceivedSmsLast48H = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_received_sms, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalReceivedSmsLast48H, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSmsLast30D(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalReceivedSmsLast30D = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_received_sms, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalReceivedSmsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSmsLast48H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSendedSmsLast48H = Sms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sended_sms, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSendedSmsLast48H, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSmsLast30dPerDay(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSendedSmsLast30D = Sms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sended_sms, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSendedSmsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSendedSmsLastHour(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSmsLast30D = Sms::whereSiteKey($entityKey)
                ->whereDate('created_at', '>=' ,Carbon::now()->subHour()->format('Y-m-d H:i:s')) ->count();

            return response()->json($totalSmsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSmsErrors(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalReceivedSmsErrors = ReceivedSms::whereSiteKey($entityKey)
                ->where ('answer', 'like', '%Houve um erro com o teu voto.%') -> count();

            return response()->json($totalReceivedSmsErrors, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSmsLast24H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalReceivedSmsLast24H = ReceivedSms::whereSiteKey($entityKey)
                ->whereDate('created_at', '>=' ,Carbon::now()->subDay()->format('Y-m-d H:i:s')) ->count();

            return response()->json($totalReceivedSmsLast24H, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalReceivedSmsLast24hErrors(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalReceivedSmsLast24hErrors = ReceivedSms::whereSiteKey($entityKey)
                ->whereDate('created_at', '>=' ,Carbon::now()->subDay()->format('Y-m-d H:i:s'))
                ->where ('answer', 'like', '%erro%') -> count();

            return response()->json($totalReceivedSmsLast24hErrors, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSmsVotesLast48H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSmsVotesLast48H = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sms_votes, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->where ('answer', 'like' ,'%recebido%')
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSmsVotesLast48H, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSmsVotesLast30D(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSmsVotesLast30D = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sms_votes, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->where ('answer', 'like' ,'%recebido%')
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSmsVotesLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSmsVotesErrorsLast48H(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSmsVotesErrorsLast48H = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sms_votes_errors, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(2))
                ->where ('answer', 'like', '%Houve um erro com o teu voto.%')
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSmsVotesErrorsLast48H, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getCountTotalSmsVotesErrorsLast30D(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');

            $totalSmsVotesErrorsLast30D = ReceivedSms::whereSiteKey($entityKey) // write your table name
            ->selectRaw('count(created_at) as total_sms_votes_errors, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
            ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->where ('answer', 'like', '%Houve um erro com o teu voto.%')
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSmsVotesErrorsLast30D, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function show(Request $request, $smsKey)
    {
        try {
            $sms = Sms::whereSmsKey($smsKey)->firstOrFail();

            return response()->json($sms, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getReceivedSmsDetails(Request $request, $receivedSmsKey)
    {
        try {
            $sms = ReceivedSms::whereReceivedSmsKey($receivedSmsKey)->firstOrFail();

            return response()->json($sms, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSendedSms24hPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= Sms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sended_sms, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalReceivedSms24hPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_received_sms, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSmsVotes24hPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sms_votes, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year')
                ->where ('answer', 'like' ,'%recebido%')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSmsVotesErrors24hPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sms_votes_errors, MINUTE(created_at) as minute, HOUR(created_at) as hour, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year')
                ->where ('answer', 'like', '%Houve um erro com o teu voto.%')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('HOUR(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSendedSms30DPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= Sms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sended_sms, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalReceivedSms30DPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_received_sms, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSmsVotes30DPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sms_votes, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
                ->where ('answer', 'like' ,'%recebido%')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function countTotalSmsVotesErrors30DPersonalized(Request $request){
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-SITE-KEY');
            $totalSms= ReceivedSms::whereSiteKey($entityKey)
                ->selectRaw('count(created_at) as total_sms_votes_errors, DAY(created_at) as day, MONTH(created_at) as month, YEAR(created_at) as year') // use your field for count
                ->where ('answer', 'like', '%Houve um erro com o teu voto.%')
                ->whereDate('created_at', '>=' ,$request->startDate)
                ->whereDate('created_at', '<=', $request->endDate)
                ->groupBy(\DB::raw('DAY(created_at)'))
                ->get();

            return response()->json($totalSms, 200);

        } catch (QueryException $e) {
//            print_r($e);
            return response()->json(['error' => 'Failed to get Entity Sms'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sms not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function bulkSMSGatewaySendSMS(Request $request) {
        function send_message( $post_body, $url ) {
            $ch = curl_init( );
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
            curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
            curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

            $response_string = curl_exec( $ch );
            $curl_info = curl_getinfo( $ch );

            $sms_result = array();
            $sms_result['success'] = 0;
            $sms_result['details'] = '';
            $sms_result['transient_error'] = 0;
            $sms_result['http_status_code'] = $curl_info['http_code'];
            $sms_result['api_status_code'] = '';
            $sms_result['api_message'] = '';
            $sms_result['api_batch_id'] = '';

            if ( $response_string == FALSE ) {
                $sms_result['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
            } elseif ( $curl_info[ 'http_code' ] != 200 ) {
                $sms_result['transient_error'] = 1;
                $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
            }
            else {
                $sms_result['details'] .= "Response from server: $response_string\n";
                $api_result = explode( '|', $response_string );
                $status_code = $api_result[0];
                $sms_result['api_status_code'] = $status_code;
                $sms_result['api_message'] = $api_result[1];
                if ( count( $api_result ) != 3 ) {
                    $sms_result['details'] .= "Error: could not parse valid return data from server.\n" . count( $api_result );
                } else {
                    if ($status_code == '0') {
                        $sms_result['success'] = 1;
                        $sms_result['api_batch_id'] = $api_result[2];
                        $sms_result['details'] .= "Message sent - batch ID $api_result[2]\n";
                    } else if ($status_code == '1') {
                        # Success: scheduled for later sending.
                        $sms_result['success'] = 1;
                        $sms_result['api_batch_id'] = $api_result[2];
                    } else {
                        $sms_result['details'] .= "Error sending: status code [$api_result[0]] description [$api_result[1]]\n";
                    }
                }
            }
            curl_close( $ch );

            return $sms_result;
        }

        if (empty($request->header('X-MODULE-TOKEN')))
            return response()->json(['error' => 'Failed to get module'], 400);
        else
            $moduleToken = $request->header('X-MODULE-TOKEN');

        $username = $request->json("username");
        $password = $request->json("password");
        $recipient_indicative = $request->json("indicative_number");
        $recipient = $request->json("recipient");
        $message = $request->json("text");
        $sender_string = $request->json("sender_string") ?? '';

        do {
            $rand = str_random(32);
            if (!($exists = Sms::whereSmsKey($rand)->exists()))
                $key = $rand;
        } while ($exists);


        $sms = Sms::create([
            'sms_key'     => $key,
            'module_token'  => $moduleToken,
            'sent'          => '0',
            'recipient'     => $recipient,
            'content'       => $message,
            'site_key'      => $request->header('X-SITE-KEY'),
            'created_by'    => $request->header('X-SITE-KEY'),
            'updated_by'    => $request->header('X-SITE-KEY')
        ]);

        $recipient = ltrim('+'.$recipient_indicative.$sms->recipient);

        $url = 'http://bulksms.vsms.net/eapi/submission/send_sms/2/2.0';

        $post_fields = array (
            'username'      => $username,
            'password'      => $password,
            'message'       => bin2hex(mb_convert_encoding($sms->content, "UTF-16", "UTF-8")),
            'msisdn'        => $recipient,
            'dca'           => '16bit',
        );

        $post_body = '';
        foreach($post_fields as $key=>$value) {
            $post_body .= urlencode($key).'='.urlencode($value).'&';
        }
        $post_body = rtrim( $post_body,'&' );

        /* Envio de Mensagem */
        $result = send_message($post_body, $url);

        if( $result['success'] ) {
            $sms->sent = 1;
            $sms->save();
            return true;
        } else
            return false;
    }

    /* New Methods to Send SMSs */
    public function sendSMS(Request $request) {
        try {
            if (empty($request->header('X-MODULE-TOKEN')))
                return response()->json(['error' => 'Failed to get module token'], 400);
            else
                $moduleToken = $request->header('X-MODULE-TOKEN');

            $configurations = $request->get("configurations");
            $recipients = $request->get("recipients");
            $content = $request->get("content");

            $configurations["service"] = strtolower($configurations["service"] ?? null);
            
            DB::beginTransaction();
            if(!empty($recipients)) {
                foreach ($recipients as $recipient) {

                    do {
                        $rand = str_random(32);
                        if (!($exists = Sms::whereSmsKey($rand)->exists()))
                            $key = $rand;
                    } while ($exists);

                    $sms = Sms::create([
                        'sms_key' => $key,
                        'module_token' => $moduleToken,
                        'sent' => '0',
                        'recipient' => $recipient,
                        'content' => $content,
                        'service' => $configurations["service"] ?? null,
                        'site_key' => $request->header('X-SITE-KEY'),
                        'created_by' => $request->header('X-SITE-KEY'),
                        'updated_by' => $request->header('X-SITE-KEY'),
                    ]);

                    if (!empty($configurations["service"])) {
                        if (in_array($sms->service, ["skebby", "bulksms", "go4mobility"])) {
                            $dispatchResult = false;

                            if ($sms->service == "skebby")
                                $dispatchResult = $this->sendSMSUsingSkebby($sms, $configurations);
                            elseif ($sms->service == "bulksms")
                                $dispatchResult = $this->sendSMSUsingBulkSMS($sms, $configurations);
                            elseif ($sms->service == "go4mobility")
                                $dispatchResult = $this->sendSMSUsingGo4Mobility($sms, $configurations);

                            if ($dispatchResult["flag"]) {
                                $sms->sent = 1;
                                $sms->save();

                            } else {
                                $sms->error_log = "Service: " . $dispatchResult["details"];
                                $sms->save();

                            }
                        } else {
                            $sms->error_log = "EMPATIA: Unrecongnized service";
                            $sms->save();

                            return response()->json(["error" => "Unrecongnized service"], 400);
                        }
                    } else {
                        $sms->error_log = "EMPATIA: No service Received";
                        $sms->save();

                        return response()->json(["error" => "No service received"], 400);
                    }
                }
            }
            DB::commit();

            if ($dispatchResult["flag"]) {
                return response()->json(["success"=>true]);
            } else {
                return response()->json(["failed"=>true],500);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Failed to sent SMS'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    private function sendSMSUsingSkebby($sms,$configurations) {
        try {
            $url = env("SMS_SKEBBY_URL","");
            if (empty($url))
                $url = "http://gateway.skebby.it/api/send/smseasy/advanced/http.php";

            $recipient = $sms->recipient;
            if (!is_array($recipient))
                $recipient = array($recipient);

            $dataToService = array (
                'username'      => $configurations["username"],
                'password'      => $configurations["password"],
                'sender_string' => $configurations["sender_name"],
                'text'          => $sms->content,
                'recipients[]'  => implode('&recipients[]=', $recipient),
                'method'        => 'send_sms_classic',
            );

            $parameters = '';
            foreach($dataToService as $key=>$value) {
                $parameters .= urlencode($key).'='.urlencode($value).'&';
            }

            parse_str($this->do_post_request($url, $parameters), $result);


            if ($result['status'] == 'success')
                return array("flag" => true);
            else
                return array("flag" => false, "details" => $result['message'] ?? "no details provided");
        } catch (\Exception $e) {
            return false;
        }
    }
    private function sendSMSUsingBulkSMS($sms,$configurations) {
        function sendBulkSMSMessage($url, $post_body) {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $post_body);
            curl_setopt($ch,CURLOPT_TIMEOUT, 20);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);

            $response_string = curl_exec($ch);
            $curl_info = curl_getinfo($ch);

            $sms_result = array();
            $sms_result['success'] = 0;
            $sms_result['details'] = '';
            $sms_result['transient_error'] = 0;
            $sms_result['http_status_code'] = $curl_info['http_code'];
            $sms_result['api_status_code'] = '';
            $sms_result['api_message'] = '';
            $sms_result['api_batch_id'] = '';

            if ( $response_string == FALSE ) {
                $sms_result['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
            } elseif ( $curl_info[ 'http_code' ] != 200 ) {
                $sms_result['transient_error'] = 1;
                $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
            }
            else {
                $sms_result['details'] .= "Response from server: $response_string\n";
                $api_result = explode( '|', $response_string );
                $status_code = $api_result[0];
                $sms_result['api_status_code'] = $status_code;
                $sms_result['api_message'] = $api_result[1];
                if ( count( $api_result ) != 3 ) {
                    $sms_result['details'] .= "Error: could not parse valid return data from server.\n" . count( $api_result );
                } else {
                    if ($status_code == '0') {
                        $sms_result['success'] = 1;
                        $sms_result['api_batch_id'] = $api_result[2];
                        $sms_result['details'] .= "Message sent - batch ID $api_result[2]\n";
                    } else if ($status_code == '1') {
                        # Success: scheduled for later sending.
                        $sms_result['success'] = 1;
                        $sms_result['api_batch_id'] = $api_result[2];
                    } else {
                        $sms_result['details'] .= "Error sending: status code [$api_result[0]] description [$api_result[1]]\n";
                    }
                }
            }
            curl_close($ch);

            return $sms_result;
        }
        try {
            $url = env("SMS_BULKSMS_URL","");
            if (empty($url))
                $url = "http://bulksms.vsms.net/eapi/submission/send_sms/2/2.0";

            $dataToService = array (
                'username'      => $configurations["username"],
                'password'      => $configurations["password"],
                'sender'        => $configurations["sender_name"],
                'message'       => bin2hex(mb_convert_encoding($sms->content, "UTF-16", "UTF-8")),
                'msisdn'        => ltrim($sms->recipient, '+'),
                'dca'           => '16bit',
                'stop_dup_id'   => $sms->id
            );

            $parameters = '';
            foreach($dataToService as $key=>$value) {
                $parameters .= urlencode($key).'='.urlencode($value).'&';
            }

            $result = sendBulkSMSMessage($url,rtrim($parameters,'&'));

            if ($result['success']==1)
                return array("flag" => true);
            else
                return array("flag" => false, "details" => $result["details"] ?? "no details provided");
        } catch (\Exception $e) {
            return false;
        }
    }
    private function sendSMSUsingGo4Mobility($sms,$configurations) {

        try {
            $url = env("SMS_GO4MOBILITY_URL","");
            if (empty($url))
                $url = "http://sms.go4mobility.com/api/mtwrite";

            $dataToService = array (
                'username'      => $configurations["username"],
                'password'      => $configurations["password"],
                'text'          => $sms->content,
                'to'            => $sms->recipient,
                'from'          => $configurations["sender_name"],
                'enc'           => 'UTF8',
                //Enviar o momId
                'type'          => 'json'
            );


            $array[] = $dataToService;



            $parameters = '';
            foreach($dataToService as $key=>$value) {
                $parameters .= urlencode($key).'='.urlencode($value).'&';
            }
            $result = $this->sendGo4MobilityMessage($url,rtrim($parameters,'&'));
            if ($result['success']==1)
                return array("flag" => true);
            else {
                $details = ($result["details"]??"No details provided") . "\nHTTP: " . $result["http_status_code"] . " | API: " . $result["api_status_code"];
                return array("flag" => false, "details" => $details);

            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sendGo4MobilityMessage($url, $post_body){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        $response_string = curl_exec($ch);
        $curl_info = curl_getinfo($ch);

        $response_string = json_decode($response_string);

        $sms_result = array();
        $sms_result['success'] = 0;
        $sms_result['details'] = '';
        $sms_result['transient_error'] = 0;
        $sms_result['http_status_code'] = $curl_info['http_code'];
        $sms_result['api_status_code'] = '';
        $sms_result['api_message'] = '';
        $sms_result['api_batch_id'] = '';

        if ($response_string == FALSE) {
            $sms_result['details'] .= "cURL error: " . curl_error($ch) . "\n";
        } elseif ($curl_info['http_code'] != 200) {
            $sms_result['transient_error'] = 1;
            $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info['http_code'];
        } else {
            $sms_result['details'] .= "Response from server: " . $response_string->description;
            $sms_result['api_status_code'] = $response_string->result;
            $sms_result['api_message'] = $response_string->description;

            if ($sms_result['api_status_code'] == '0')
                $sms_result['success'] = 1;
            else
                $sms_result['success'] = 0;
        }
        curl_close($ch);

        return $sms_result;
    }
}
