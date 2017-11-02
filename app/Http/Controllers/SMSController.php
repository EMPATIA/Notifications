<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Sms;
use App\One\One;

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



    /* New Methods to Send SMSs */
    public function sendSMS(Request $request) {
        try {
            if (empty($request->header('X-MODULE-TOKEN')))
                return response()->json(['error' => 'Failed to get module token'], 400);
            else
                $moduleToken = $request->header('X-MODULE-TOKEN');

            $configurations = $request->get("configurations");
            $recipient = $request->get("recipient");
            $content = $request->get("content");

            $configurations["service"] = strtolower($configurations["service"] ?? null);

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
                'content'       => $content,
                'service'       => $configurations["service"] ?? null,
                'site_key'      => $request->header('X-SITE-KEY'),
                'created_by'    => $request->header('X-SITE-KEY'),
                'updated_by'    => $request->header('X-SITE-KEY'),
            ]);

            if (!empty($configurations["service"])) {
                if (in_array($sms->service, [])) {
                    $dispatchResult = false;

                    if ($dispatchResult["flag"]??false) {
                        $sms->sent = 1;
                        $sms->save();

                        return response()->json(["success"=>true]);
                    } else {
                        $sms->error_log = "Service: " . $dispatchResult["details"];
                        $sms->save();

                        return response()->json(["failed"=>true],500);
                    }
                } else {
                    $sms->error_log = "EMPATIA: Unreconginzed service";
                    $sms->save();

                    return response()->json(["error" => "Unreconginzed service"], 400);
                }
            } else {
                $sms->error_log = "EMPATIA: No service Received";
                $sms->save();

                return response()->json(["error" => "No service received"], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to sent SMS'], 404);
        }
    }
}
