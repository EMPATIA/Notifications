<?php

namespace App\Http\Controllers;

use Exception;
use App\One\One;
use Carbon\Carbon;
use App\ReceivedSms;
use App\ComModules\Vote;
use App\ComModules\Notify;
use App\ComModules\EMPATIA;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReceivedSMSController extends Controller {

    public function store(Request $request) {
	    try {
		    if(strlen($request->get("sender")) > 0) {
			    \Log::info("SMS [Id:" . $request->get("sms_id") . "] >> " . $request->get("sender") . "####" . $request->get("content"));
		    } else {
			    \Log::info("SMS [Id:" . $request->get("sms_id") . "] >> " . $request->get("sender")." WRONG SENDER!");
			    return;
		    }

		    $content = $request->get("content");
		if(strlen($request->get("content")) <= 0) {
			    \Log::info("SMS [Id:" . $request->get("sms_id") . "] >> " . $request->get("sender")." WRONG CONTENT!");
			$content = "WRONG_CONTENT";
		}

            $key = '';
            do {
                $rand = str_random(32);
                if (!($exists = ReceivedSms::whereReceivedSmsKey($rand)->exists()))
                    $key = $rand;
            } while ($exists);

            $receivedSMS = ReceivedSms::create([
                'received_sms_key'       => $key,
                'entity_key'             => $request->header('X-ENTITY-KEY'),
                'site_key'               => $request->header('X-SITE-KEY'),
                'content'                => $content,
                'sender'                 => $request->get("sender"),
                'receiver'               => $request->get("receiver"),
                'event'                  => $request->get("event"),
                'processed'              => 0,
                'answers'                => null,
                'service_sms_identifier' => $request->get("sms_id"),
                'service_sms_date'       => $request->get("sms_date"),
            ]);

            return response()->json($receivedSMS, 200);
        } catch (Exception $e) {
            \Log::info($e);
            return response()->json(['error' => 'Failed to store Received SMS'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    static public function processSMS() {
        /* Hardcoded data */
        $voteValue = 1;
        // $messages = array(
        //     "empatia" => [
        //         -1 => "invalid sms format",
        //         -2 => "invalid cc format",
        //         -3 => "invalid vote event",
        //         -4 => "invalid cb",
        //         -5 => "invalid topic number",

        //         -10 => 'exception occurred',
        //         -11 => "no cc param key",
        //         -12 => "no phone number param key",
        //         -13 => "no fake email domain",
        //     ],
        //     "vote" => [
        //         -1 => "invalid vote event",
        //         -2 => "invalid vote method",
        //         -3 => "vote event closed",
        //         -4 => "you can't vote",
        //         -5 => "already submitted",
                
        //         -10 => 'exception occurred'
        //     ],
        //     "internal" => "internal Error",
        //     "success" => "succesfully voted"
        // );
        $messages = array(
            "empatia" => [
                -1 => "Houve um erro com o teu voto. A mensagem recebida nao segue o formato. Verifica a tua mensagem ou contacta-nos se tiveres duvidas. A equipa OPJovem",
                -2 => "Houve um erro com o teu voto. O numero de Cartao de Cidadao e invalido. Verifica a tua mensagem ou contacta-nos se tiveres duvidas. A equipa OPJovem",
                -3 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -4 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -5 => "Houve um erro com o teu voto. O numero do projeto que votaste e invalido. Verifica a tua mensagem ou contacta-nos se tiveres duvidas. A equipa OPJovem",

                -10 => 'Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem',
                -11 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -12 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -13 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
            ],
            "vote" => [
                -1 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -2 => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
                -3 => "A votação no OPJovem nao esta ativa. Consulta as datas de votacao em http://opjovem.gov.pt. Equipa OPJovem",
                -4 => "Houve um erro com o teu voto. Ja existe um voto registado com o teu numero de Cartao de Cidadao ou numero de telemovel. A equipa OPJovem",
                -5 => "Houve um erro com o teu voto. Ja existe um voto registado com o teu numero de Cartao de Cidadao ou numero de telemovel. A equipa OPJovem",
                
                -10 => 'Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem'
            ],
            "internal" => "Houve um erro com o teu voto. Verifica a tua mensagem e tenta mais tarde. Contacta-nos se continuares a ter problemas. Equipa OPJovem",
            "success" => "O teu voto foi recebido e sera validado. Agradecemos a tua participacao! Equipa OPJovem"
        );

        try {
            $receivedSMSs = ReceivedSms::whereProcessed('0')->limit(30)->get();
            $sitesSmsConfigurations = array();
        
            foreach ($receivedSMSs as $receivedSMS) {
                $log = "Started at: " . Carbon::now() . "\n";
                $answer = "";
                $failureType = "";
                $empatiaUserFailed = false;
                $smsApiData = [];

                $userKey = null;
                $topicKey = null;
                $eventKey = null;
		
		\Log::info("SMS [Id:" . $receivedSMS->service_sms_identifier . "][N:" . $receivedSMS->sender . "] MSG: " . $receivedSMS->content);

                /* EMPATIA Request: get user, topic and vote event */
                try {
                    $empatiaResult = EMPATIA::processSMSVote($receivedSMS);
                    
                    $userKey = $empatiaResult["data"]->user ?? null;
                    $topicKey = $empatiaResult["data"]->topic ?? null;
                    $eventKey = $empatiaResult["data"]->event ?? null;

                    if (empty($userKey) || empty($topicKey) || empty($eventKey) || $empatiaResult["http"]!=200) {
                        $empatiaUserFailed = true;
                        $log .= "-EMPATIA (User) failed: ";

                        if ($empatiaResult["http"]!=200) {
                            $log .= $empatiaResult["data"]->code;

                            switch($empatiaResult["data"]->code) {
                                case -1:
                                    $log .= " (Invalid SMS Format)";
                                    break;
                                case -2:
                                    $log .= " (Invalid CC Format)";
                                    break;
                                case -3:
                                    $log .= " (Invalid Vote event)";
                                    break;
                                case -4:
                                    $log .= " (Invalid CB)";
                                    break;
                                case -5:
                                    $log .= " (Invalid Topic Number)";
                                    break;

                                case -10:
                                    $log .= " (Internal Exception)";
                                    break;
                                case -11:
                                    $log .= " (No CC Parameter Key)";
                                    break;
                                case -12:
                                    $log .= " (No Phone Number Parameter Key)";
                                    break;
                                case -13:
                                    $log .= " (No Fake Email domain)";
                                    break;
                            }

                            $answer = $messages["empatia"][$empatiaResult["data"]->code];
                            $failureType = "empatia[" . $empatiaResult["data"]->code . "]";
                        } else {
                            if (empty($userKey))
                                $log .= "user missing. ";
                            if (empty($topicKey))
                                $log .= "topic missing. ";
                            if (empty($eventKey))
                                $log .= "event missing. ";

                            $answer = $messages["internal"];
                            $failureType = "empatia[internal]";
                        }

                        $log .= "\n";
                    } else
                        $log .= "-EMPATIA (User) succeded (" . $userKey . "| " . $topicKey . "| " . $eventKey . ")\n";
                        
                } catch(Exception $e) {
                    $empatiaUserFailed = true;
                    $log .= "-EMPATIA (User) Exception: " . $e->getMessage() . " (f=" . $e->getFile() . "|l=" .$e->getLine() . ")\n";
                    \Log::info($e);
                }
                
                /* Vote Request: make the vote if EMPATIA (User) didn't failed*/
                if (!$empatiaUserFailed) {
                    try {
                        $voteResult = Vote::processSMSVote($userKey, $topicKey, $eventKey, $voteValue);

                        $voteId = $voteResult["data"]->id ?? null;

                        if (empty($voteId) || $voteResult["http"]!=200) {
                            $log .= "-Vote Failed: ";

                            if ($voteResult["http"]!=200) {
                                $log .= $voteResult["data"]->code;

                                switch($voteResult["data"]->code) {
                                    case -1:
                                        $log .= " (Invalid Vote Event)";
                                        break;
                                    case -2:
                                        $log .= " (Invalid Event Method)";
                                        break;
                                    case -3:
                                        $log .= " (Vote Event closed)";
                                        break;
                                    case -4:
                                        $log .= " (User can't vote)";
                                        break;
                                    case -4:
                                        $log .= " (User already submitted votes)";
                                        break;
                                    
                                    case -10:
                                        $log .= " (Internal Exception)";
                                        break;
                                }

                                $answer = $messages["vote"][$voteResult["data"]->code];
                                $failureType = "vote[" . $voteResult["data"]->code . "]";
                            } else if (empty($voteId)) {
                                $log .= "vote missing. ";

                                $answer = $messages["internal"];
                                $failureType = "vote[internal]";
                            }

                            $log .= "\n";
                        } else {
                            $log .= "-Vote succeded (" . $voteId . ")\n";

                            $answer = $messages["success"];
                        }
                    } catch (Exception $e) {
                        $log .= "-Vote Exception: " . $e->getMessage() . " (f=" . $e->getFile() . "|l=" .$e->getLine() . ")\n";
                        \Log::info($e);
                    }
                } else {
                    $log .= "-Vote Bypased: EMPATIA (User) failed.\n";
                    \Log::info("Vote Bypassed: EMPATIA (User) failed");
                }
                \Log::info("SMS [Id:" . $receivedSMS->service_sms_identifier . "][N:" . $receivedSMS->sender . "][Key:" . $receivedSMS->received_sms_key . "] " . (empty($failureType) ? "Success" : "Failure ". $failureType));
                \Log::info("SMS [Id:" . $receivedSMS->service_sms_identifier . "][N:" . $receivedSMS->sender . "][Key:" . $receivedSMS->received_sms_key . "] REPLY " . $answer);

                /* EMPATIA Request: get the SMS API Configurations */
                try {
                    if (!array_key_exists($receivedSMS->site_key,$sitesSmsConfigurations)) {
                        $smsConfigurations = EMPATIA::getSiteSMSConfigurations($receivedSMS->site_key);
                        
                        if ($smsConfigurations["http"]!=200) {
                            $log .= "-EMPATIA (SMS) failed\n";
                        } else {
                            $SMSuserName = $smsConfigurations["data"]->sms_service_username;
                            $SMSpassword = $smsConfigurations["data"]->sms_service_password;
                            $SMSsenderName = $smsConfigurations["data"]->sms_service_sender_name;
                            $SMSserviceCode = $smsConfigurations["data"]->sms_service_code;

                            $smsApiData = array(
                                "username" => $SMSuserName,
                                "password" => $SMSpassword,
                                "sender_name" => $SMSsenderName,
                                "service" => $SMSserviceCode
                            );
                            $log .= "-EMPATIA succeded to return SMS Service Configurations (" . $SMSuserName . "|" . $SMSpassword . "|"  . $SMSsenderName . "|" . $SMSserviceCode ."\n";
                            
                            $sitesSmsConfigurations[$receivedSMS->site_key] = $smsApiData;
                        }
                    } else
                        $smsApiData = $sitesSmsConfigurations[$receivedSMS->site_key];
                } catch (Exception $e) {
                    $log .= "-EMPATIA SMS Configs: " . $e->getMessage() . " (f=" . $e->getFile() . "|l=" .$e->getLine() . ")\n";
                    \Log::info($e);
                }

                /* Create the SMS to be sent if EMPATIA (SMS) didn't failed*/
                try {
                    if (!empty($smsApiData)) {
                        $result = Notify::sendSMS($smsApiData,$receivedSMS->sender,$answer,$receivedSMS->site_key);

                        if (!isset($result->success) || !$result->success)
                            $log .= "-Notify failed to send SMS\n";
                        else
                            $log .= "-Notify succeded to send SMS\n";
                    } else
                        $log .="-Notify no SMS Sent: empty API data";
                } catch (Exception $e) {
                    $log .= "-Notify Exception: " . $e->getMessage() . " (f=" . $e->getFile() . "|l=" .$e->getLine() . ")\n";
                    \Log::info($e);
                }

                $log .= "Ended at: " . Carbon::now() . "\n";
                $log .= "----------\n";

                $receivedSMS->logs .= $log;
                $receivedSMS->answer .= Carbon::now() . ": " . $answer . "\n";
                $receivedSMS->processed = 1;
                $receivedSMS->save();
            }
        } catch(Exception $e) {
            \Log::info($e);
        }
    }
}
