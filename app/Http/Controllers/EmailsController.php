<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailGroup;
use App\EmailTemplate;
use App\Mail\GenericEmail;
use App\One\One;
use App\QueuedEmailData;
use App\Type;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

/**
 * Class EmailsController
 * @package App\Http\Controllers
 */

/**
 * @SWG\Tag(
 *   name="Email",
 *   description="Everything about Email",
 * )
 *
 *  @SWG\Definition(
 *      definition="emailErrorDefault",
 *      @SWG\Property(property="error", type="string", format="string")
 *  )
 *
 *  @SWG\Definition(
 *   definition="emailCreate",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"recipient", "content"},
 *           @SWG\Property(property="user_key", format="string", type="string"),
 *           @SWG\Property(property="recipient", format="string", type="string"),
 *           @SWG\Property(property="content", type="array", @SWG\Items(type="string")),
 *           @SWG\Property(property="no_reply", format="string", type="string"),
 *           @SWG\Property(property="sender_name", format="string", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="emailReply",
 *   type="object",
 *   allOf={
 *      @SWG\Schema(
 *           @SWG\Property(property="id", format="integer", type="integer"),
 *           @SWG\Property(property="email_key", format="string", type="string"),
 *           @SWG\Property(property="module_token", format="string", type="string"),
 *           @SWG\Property(property="entity_key", format="string", type="string"),
 *           @SWG\Property(property="sent", format="string", type="string"),
 *           @SWG\Property(property="recipient", format="string", type="string"),
 *           @SWG\Property(property="content", format="array", type="string"),
 *           @SWG\Property(property="created_by", format="string", type="string"),
 *           @SWG\Property(property="updated_by", format="string", type="string"),
 *           @SWG\Property(property="created_at", format="date", type="string"),
 *           @SWG\Property(property="updated_at", format="date", type="string"),
 *           @SWG\Property(property="deleted_at", format="date", type="string")
 *       )
 *   }
 * )
 */

class EmailsController extends Controller
{
    protected $required = [
        "send_email" => ['recipient', 'content']
    ];

    /**
     * @param Request $request
     * @param $emailKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $emailKey)
    {
        try {
            $emails = Email::whereEmailKey($emailKey)->get();

            $email = $emails->first();
            $recipients = array();
            foreach ($emails as $uniqueEmail) {
                $recipients[] = $uniqueEmail->recipient;
            }
            $email->recipient = str_replace(",",", ",json_encode($recipients));

            return response()->json($email, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\post(
     *  path="/email/send/{email_template_key}",
     *  summary="Send an Email given a template",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Email Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="email_template_key",
     *      in="path",
     *      description="Email Template Key",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="LANG-CODE",
     *      in="header",
     *      description="User Language",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="LANG-CODE-DEFAULT",
     *      in="header",
     *      description="Entity default Language",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=false,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-ENTITY-KEY",
     *      in="header",
     *      description="Entity Key",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="The Email Sent",
     *      @SWG\Schema(ref="#/definitions/emailReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Email Template not Found",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to send Email",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailTemplateKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request, $emailTemplateKey)
    {
        if(is_null($request->json('user_key'))){
            $user = ONE::verifyToken($request);
        } else {
            $user = $request->json('user_key');
        }


        try {
            $emailMessage = [];

            if (empty($request->header('X-MODULE-TOKEN'))) {
                return response()->json(['error' => 'Failed to get module'], 400);
            } else {
                $moduleToken = $request->header('X-MODULE-TOKEN');
            }

            $emailTemplate = EmailTemplate::whereEmailTemplateKey($emailTemplateKey)->firstOrFail();

            if (!($emailTemplate->translation($request->header('LANG-CODE')))) {
                if (!$emailTemplate->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!($emailTemplate->translation('en'))) {
                        return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }
            $populateContent = $request->json('content');

            $emailContent = $emailTemplate->header.$emailTemplate->content.$emailTemplate->footer;

            if($populateContent){
                foreach ($populateContent as $key => $content) {
                    $emailContent = str_replace('#' . $key, $content, $emailContent);
                }
            }

//            $emailMessage['to'] = $request->json('recipient');
            $emailMessage['no_reply'] = $request->json('no_reply');
            $emailMessage['sender_name'] = $request->json('sender_name');
            $emailMessage['subject'] = $emailTemplate->subject;
            $emailMessage['content'] = html_entity_decode($emailContent);
            $emailMessage['newsletter_id'] = !is_null($request->input('newsletter_id')) ? $request->input('newsletter_id') : '0';

            $recipient = $request->json('recipient');

            if (is_array($recipient)) {
                foreach ($recipient as $oneRecipient) {
                    do {
                        $rand = str_random(32);

                        if (!($exists = Email::whereEmailKey($rand)->exists())) {
                            $key = $rand;
                        }
                    } while ($exists);

                    $email = Email::create([
                        'email_key'     => $key,
                        'recipient'     => $oneRecipient,
                        'subject'       => $emailTemplate->subject,
                        'module_token'  => $moduleToken,
                        'entity_key'    => $request->header('X-ENTITY-KEY'),
                        'content'       => $emailContent,
                        'created_by'    => $user,
                        'sent'          => '0',
                        'newsletter_id' => $emailMessage['newsletter_id'],
                        "sender_email"  => $emailMessage['no_reply'],
                        "sender_name"   => $emailMessage['sender_name'],

                    ]);
                }
            } else {
                do {
                    $rand = str_random(32);

                    if (!($exists = Email::whereEmailKey($rand)->exists())) {
                        $key = $rand;
                    }
                } while ($exists);

                $email = Email::create([
                    'email_key'     => $key,
                    'recipient'     => $recipient,
                    'subject'       => $emailTemplate->subject,
                    'module_token'  => $moduleToken,
                    'entity_key'    => $request->header('X-ENTITY-KEY'),
                    'content'       => $emailContent,
                    'created_by'    => $user,
                    'sent'          => '0',
                    'newsletter_id' => $emailMessage['newsletter_id'],
                    "sender_email"  => $emailMessage['no_reply'],
                    "sender_name"   => $emailMessage['sender_name'],

                ]);
            }


            /*if ($this->sendEmail($emailMessage)){
                $email->sent = '1';
                $email->save();
            }*/

            return response()->json($email, 200);

        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to send Email'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\post(
     *  path="/email/emailSend/{type_code}",
     *  summary="Send an Email given a type",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Email Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="type_code",
     *      in="path",
     *      description="Type Code",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-SITE-KEY",
     *      in="header",
     *      description="Site Key",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="LANG-CODE",
     *      in="header",
     *      description="User Language",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="LANG-CODE-DEFAULT",
     *      in="header",
     *      description="Entity default Language",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=false,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-ENTITY-KEY",
     *      in="header",
     *      description="Entity Key",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="The Email Sent",
     *      @SWG\Schema(ref="#/definitions/emailReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Email Template not Found",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to send Email",
     *      @SWG\Schema(ref="#/definitions/emailErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $typeCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailSend(Request $request, $typeCode)
    {
        if(is_null($request->json('user_key'))){
            $user = ONE::verifyToken($request);
        } else {
            $user = $request->json('user_key');
        }

        ONE::verifyKeysRequest($this->required["send_email"], $request);

        try {
            $emailMessage = [];

            if (empty($request->header('X-MODULE-TOKEN'))) {
                return response()->json(['error' => 'Failed to get module'], 400);
            } else {
                $moduleToken = $request->header('X-MODULE-TOKEN');
            }

            $emailTemplate = EmailTemplate::whereEmailGroupId(EmailGroup::whereSiteKey($request->header('X-SITE-KEY'))->first()->id)->whereTypeId(Type::whereCode($typeCode)->first()->id)->first();

            if (!($emailTemplate->translation($request->header('LANG-CODE')))) {
                if (!$emailTemplate->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!($emailTemplate->translation('en'))) {
                        return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }

            $populateContent = $request->json('content');
            $emailContent = $emailTemplate->header.$emailTemplate->content.$emailTemplate->footer;
            foreach ($populateContent as $key => $content) {
                $emailContent = str_replace("#".$key, $content, $emailContent);
            }

            if (is_array($request->json('recipient'))){
                $recipient = [];
                foreach ($request->json('recipient') as $recipientUser) {
                    $recipient[] = $recipientUser['email'];
                }
            } else {
                $recipient = $request->json('recipient');
            }

            $emailMessage['to'] = $recipient;
            $emailMessage['no_reply'] = $request->json('no_reply');
            $emailMessage['sender_name'] = $request->json('sender_name');
            $emailMessage['subject'] = $emailTemplate->subject;
            $emailMessage['content'] = html_entity_decode($emailContent);
            $emailMessage['newsletter_id'] = !is_null($request->input('newsletter_id')) ? $request->input('newsletter_id') : '0';

            do {
                $rand = str_random(32);

                if (!($exists = Email::whereEmailKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $email = Email::create([
                'email_key'     => $key,
                'recipient'     => is_array($recipient) ? json_encode($recipient) : $recipient,
                'subject'       => $emailTemplate->subject,
                'module_token'  => $moduleToken,
                'entity_key'    => $request->header('X-ENTITY-KEY'),
                'content'       => $emailContent,
                'created_by'    => $user,
                'sent'          => '0',
                'newsletter_id' => $emailMessage['newsletter_id'],
                "sender_email"  => $emailMessage['no_reply'],
                "sender_name"   => $emailMessage['sender_name'],
            ]);

            /*if ($this->sendEmail($emailMessage)){
                $email->sent = '1';
                $email->save();
            }*/

            return response()->json($email, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to send Email'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /** DEPRECATED
     * Send an Email and Store it in the database
     * Return the Attributes of the Email created
     * @param $emailMessage
     * @return bool
     */
    private function sendEmail($emailMessage)
    {
        try{
            Mail::to($emailMessage['to'])
                ->send(new GenericEmail($emailMessage));
        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEntityEmail(Request $request){

        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-ENTITY-KEY');
            $emailList = Email::whereEntityKey($entityKey);
            $tableData = $request->input('tableData') ?? null;
            $recordsTotal = $emailList->count();
            /* $recordsTotal = $emailList->count(\DB::raw('DISTINCT email_key'));*/
            $query = $emailList/*->groupBy("email_key")*/;

            $query = $query
                ->orderBy($tableData['order']['value'], $tableData['order']['dir']);

            if(!empty($tableData['search']['value'])) {
                $query = $query
                    ->where('recipient', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('created_by', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('created_at', 'like', '%'.$tableData['search']['value'].'%');
            }

            $recordsFiltered = $query->count();

            $emails = $query
                ->skip($tableData['start'])
                ->take($tableData['length'])
                ->get();

            $counts = Email::whereIn("email_key",$emails->pluck("email_key"))->select(\DB::raw("email_key,COUNT(*) as count"))->groupBy("email_key")->get()
                ->pluck("count","email_key")->toArray();

            foreach ($emails as $email) {
                if (isset($counts[$email->email_key]))
                    $email->recipients_count = $counts[$email->email_key];
            }

            $data['emails'] = $emails;
            $data['recordsTotal'] = $recordsTotal;
            $data['recordsFiltered'] = $recordsFiltered;

            return response()->json($data, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Entity Emails'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $typeCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendManyEmails(Request $request, $typeCode)
    {
        $user = ONE::verifyToken($request);

        try {
            $counters = array(
                "success" => 0,
                "failed" => 0,
            );

            if (empty($request->header('X-MODULE-TOKEN'))) {
                return response()->json(['error' => 'Failed to get module'], 400);
            } else {
                $moduleToken = $request->header('X-MODULE-TOKEN');
            }

            $emailTemplate = EmailTemplate::whereEmailGroupId(EmailGroup::whereSiteKey($request->header('X-SITE-KEY'))->firstOrFail()->id)->whereTypeId(Type::whereCode($typeCode)->firstOrFail()->id)->firstOrFail();

            if (!($emailTemplate->translation($request->header('LANG-CODE')))) {
                if (!$emailTemplate->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!($emailTemplate->translation('en'))) {
                        return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }

            $noReplyEmail = $request->json('no_reply');
            $senderName = $request->json("sender_name");
            $newsletter_id = !is_null($request->input('newsletter_id')) ? $request->input('newsletter_id') : '0';

            do {
                $rand = str_random(32);

                if (!($exists = Email::whereEmailKey($rand)->exists())) {
                    $emailKey = $rand;
                }
            } while ($exists);

            foreach ($request->emailData as $userKey=>$emailData) {
                try {
                    $currentEmailContent = $emailTemplate->header . $emailTemplate->content . $emailTemplate->footer;
                    $tags = $emailData["tags"];
                    foreach ($tags as $key => $content) {
                        $currentEmailContent = str_replace("#" . $key, $content, $currentEmailContent);
                    }

                    $email = Email::create([
                        'email_key' => $emailKey,
                        'recipient' => is_array($emailData["destiny"]) ? json_encode($emailData["destiny"]) : $emailData["destiny"],
                        'subject' => $emailTemplate->subject,
                        'module_token' => $moduleToken,
                        'entity_key' => $request->header('X-ENTITY-KEY'),
                        'content' => $currentEmailContent,
                        'created_by' => $user,
                        'sent' => '0',
                        'newsletter_id' => $newsletter_id,
                        "sender_email"  => $noReplyEmail,
                        "sender_name"   => $senderName,
                    ]);

                    $counters["success"]++;
                } catch (Exception $exception) {
                    $counters["failed"]++;
                }
            }

            return response()->json(["success"=>$counters["success"],"failed"=>$counters["failed"]], 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to send Email'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     *
     */
    public static function sendFailedEmails() {
        $failedEmails = Email::whereEntityKey('bhDlS9uuMdWcm1YINEupAK7GzQVAtT9C')->whereSent(false)->get()->take(30);

        foreach ($failedEmails as $failedEmail){
            $emailMessage['to'] = $failedEmail['recipient'];
            $emailMessage['no_reply'] = 'no-reply@buergerbudget.wuppertal.de';
            $emailMessage['sender_name'] = 'Bürgerbudget Wuppertal';
            $emailMessage['subject'] = 'Bürgerbudget Wuppertal: Neue Nachricht';
            $emailMessage['content'] = html_entity_decode($failedEmail['content']);

            Mail::send(
                ['email.email', 'email.emailText'],
                [
                    'email' => $emailMessage['to'],
                    'subject' => $emailMessage['subject'],
                    'content' => $emailMessage['content'],
                ],
                function ($message) use ($emailMessage) {
                    $message->to($emailMessage['to']);
                    $message->from($emailMessage['no_reply'], $emailMessage['sender_name']);
                    $message->subject($emailMessage['subject']);

                    $swiftMessage = $message->getSwiftMessage();
                    $headers = $swiftMessage->getHeaders();

                    $headers->addTextHeader('Return-Path', $emailMessage['no_reply']);
                    $headers->addTextHeader('X-Return-Path', $emailMessage['no_reply']);
                    $headers->addTextHeader('Reply-To', $emailMessage['no_reply']);
                });
            if (count(Mail::failures()) > 0){
                \Log::info("WUPPERTAL EMAIL:".'ERROR');
            }else{
                $failedEmail->sent = 1;
                $failedEmail->save();
                \Log::info("WUPPERTAL EMAIL:".'OK');
            }

        }
    }

    /**
     *
     */
    public static function sendEmails() {
        $time = -microtime(true);
        try {
            $emails = Email::whereSent('0')->where("errors","<","2")->orderBy('newsletter_id', 'desc')->take(30)->get();

            foreach ($emails as $email) {
                try {
                    $emailMessage = [];
                    $emailMessage['to'] = $email->recipient;
                    $emailMessage['no_reply'] = $email->sender_email;
                    $emailMessage['sender_name'] = $email->sender_name;
                    $emailMessage['subject'] = $email->subject;
                    $emailMessage['content'] = html_entity_decode($email->content);

//                    Send Email
                    Mail::to($emailMessage['to'])->send(new GenericEmail($emailMessage));

                    Log::info('Email Sent: ', ['id' => $email->id, 'recipient' => $email->recipient] );

                    $email->sent = '1';
                    $email->save();

                } catch (\Exception $e) {
                    Log::error('Email NOT Sent: ', ['id' => $email->id, 'recipient' => $email->recipient, 'message' => $e->getMessage()] );

                    $email->errors = $email->errors + 1;
                    $email->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error Sending Emails: ', ['message' => $e->getMessage()]);
        }
        $time += microtime(true);
        Log::info('Execution Time: '.$time.' seconds');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEmails(Request $request)
    {
        if(is_null($request->json('user_key'))){
            $user = ONE::verifyToken($request);
        } else {
            $user = $request->json('user_key');
        }

        $actionUrl = is_null($request->json('action_url')) ? null : $request->json('action_url');

        try {
            $emailMessage = [];
            $queuedListOfEmails = array();
            if (empty($request->header('X-MODULE-TOKEN'))) {
                return response()->json(['error' => 'Failed to get module'], 400);
            } else {
                $moduleToken = $request->header('X-MODULE-TOKEN');
            }

            $templateKey = $request->json('template_key');

            if ($templateKey){
                $emailTemplate = EmailTemplate::whereEmailTemplateKey($templateKey)->firstOrFail();

                if (!($emailTemplate->translation($request->header('LANG-CODE')))) {
                    if (!$emailTemplate->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!($emailTemplate->translation('en'))) {
                            return response()->json(['error' => 'No translation found'], 404);
                        }
                    }
                }
                $populateContent = $request->json('tags');
                $emailContent = $emailTemplate->header.$emailTemplate->content.$emailTemplate->footer;
                if($populateContent){
                    foreach ($populateContent as $key => $content) {
                        $emailContent = str_replace('#' . $key, $content, $emailContent);
                    }
                }

                $emailMessage['subject'] = $emailTemplate->subject;
                $emailMessage['content'] = html_entity_decode($emailContent);
            } else {
                $emailMessage['subject'] = $request->json('subject');
                $emailMessage['content'] = html_entity_decode($request->json('message'));
            }

            $emailMessage['no_reply'] = $request->json('no_reply');
            $emailMessage['sender_name'] = $request->json('sender_name');
            $emailMessage['newsletter_id'] = !is_null($request->input('newsletter_id')) ? $request->input('newsletter_id') : '0';

            DB::beginTransaction();
            if(!empty($request->json('to'))){
                foreach($request->json('to') as $recipient){

                    $message = null;
                    if (!is_null($actionUrl)){
                        $message = str_replace('#questionnaire', $actionUrl[$recipient], $emailMessage['content']);
                    }

                    do {
                        $rand = str_random(32);
                        if (!($exists = Email::whereEmailKey($rand)->exists())) {
                            $key = $rand;
                        }
                    } while ($exists);

                    $email = Email::create([
                        'email_key'     => $key,
                        'recipient'     => $recipient,
                        'subject'       => $emailMessage['subject'],
                        'module_token'  => $moduleToken,
                        'entity_key'    => $request->header('X-ENTITY-KEY'),
                        'content'       => is_null($message) ? $emailMessage['content'] : $message,
                        'created_by'    => $user,
                        'sent'          => '0',
                        'created_at'    => Carbon::now(),
                        'newsletter_id' => $emailMessage['newsletter_id'],
                        "sender_email"  => $emailMessage['no_reply'],
                        "sender_name"   => $emailMessage['sender_name'],
                    ]);
                }
            }
            DB::commit();

            return response()->json('OK', 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to send Email'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    private function joinEmailsAndQueued(){
        $queuedEmails =  QueuedEmailData::all();
        foreach ($queuedEmails as $queuedEmail){
            $email = $queuedEmail->email()->first();

            $email->sender_name = $queuedEmail->sender_name;
            $email->sender_email = $queuedEmail->sender_email;
            $email->subject = $queuedEmail->subject;

            $email->save();
        }
    }
}
