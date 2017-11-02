<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailGroup;
use App\EmailTemplate;
use App\EmailTemplateTranslation;
use App\One\One;
use App\Type;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

/**
 * Class EmailTemplateTemplatesController
 * @package App\Http\Controllers
 */

/**
 * @SWG\Tag(
 *   name="Email Template",
 *   description="Everything about Email Templates",
 * )
 *
 *  @SWG\Definition(
 *      definition="emailTemplateErrorDefault",
 *      @SWG\Property(property="error", type="string", format="string")
 *  )
 *
 *  @SWG\Definition(
 *   definition="emailTemplateTranslations",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"language_code", "subject", "header", "content", "footer"},
 *           @SWG\Property(property="language_code", format="string", type="string"),
 *           @SWG\Property(property="subject", format="string", type="string"),
 *           @SWG\Property(property="header", format="string", type="string"),
 *           @SWG\Property(property="content", format="string", type="string"),
 *           @SWG\Property(property="footer", format="string", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="emailTemplateCreate",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"type_key", "translations"},
 *           @SWG\Property(property="type_key", format="string", type="string"),
 *           @SWG\Property(property="site_key", format="string", type="string"),
 *           @SWG\Property(
 *             property="translations",
 *             type="array",
 *             @SWG\Items(ref="#/definitions/emailTemplateTranslations")
 *           ),
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="emailTemplateReply",
 *   type="object",
 *   allOf={
 *      @SWG\Schema(
 *           @SWG\Property(property="id", format="integer", type="integer"),
 *           @SWG\Property(property="email_template_key", format="string", type="string"),
 *           @SWG\Property(property="type_id", format="integer", type="integer"),
 *           @SWG\Property(property="email_group_id", format="integer", type="integer"),
 *           @SWG\Property(property="subject", format="string", type="string"),
 *           @SWG\Property(property="header", format="string", type="string"),
 *           @SWG\Property(property="content", format="string", type="string"),
 *           @SWG\Property(property="footer", format="string", type="string"),
 *           @SWG\Property(
 *             property="type",
 *             type="object",
 *             allOf={
 *                  @SWG\Schema(ref="#/definitions/typeReply")
 *             }
 *           ),
 *           @SWG\Property(property="site_key", format="string", type="string"),
 *           @SWG\Property(property="created_at", format="date", type="string"),
 *           @SWG\Property(property="updated_at", format="date", type="string"),
 *           @SWG\Property(property="deleted_at", format="date", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *     definition="emailTemplateDeleteReply",
 *     @SWG\Property(property="string", type="string", format="string")
 * )
 */

class EmailTemplatesController extends Controller
{
    protected $required = [
        "store_update" => ['translations']
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            if(isset($request->site_key)){
                $emailGroup = EmailGroup::whereSiteKey($request->site_key)->first();
            }else{
                $emailGroup = EmailGroup::whereSiteKey($request->header('X-SITE-KEY'))->first();
            }
            if (isset($emailGroup)){
                $emailTemplates = EmailTemplate::with('type')->whereEmailGroupId($emailGroup->id)->get();
            }else{
                $emailTemplates = [];
            }


            foreach ($emailTemplates as $emailTemplate) {
                if (!($emailTemplate->translation($request->header('LANG-CODE')))) {
                    if (!$emailTemplate->translation($request->header('LANG-CODE-DEFAULT'))) {
                        $translation = $emailTemplate->emailTemplateTranslations()->first();
                        if ($translation) {
                            $emailTemplate->translation($translation->language_code);
                        } else {
                            $emailTemplate->setAttribute('subject', 'no translation');
                            $emailTemplate->setAttribute('header', 'no translation');
                            $emailTemplate->setAttribute('content', 'no translation');
                            $emailTemplate->setAttribute('footer', 'no translation');
                        }
                    }
                }

                if (!($emailTemplate->type->translation($request->header('LANG-CODE')))) {
                    if (!$emailTemplate->type->translation($request->header('LANG-CODE-DEFAULT'))) {
                        $translation = $emailTemplate->type->typeTranslations()->first();
                        if ($translation) {
                            $emailTemplate->type->translation($translation->language_code);
                        } else {
                            $emailTemplate->type->setAttribute('name', 'no translation');
                        }
                    }
                }
            }

            return response()->json(['data' => $emailTemplates], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Email Templates'], 500);
        }
    }

    /**
     * @SWG\Get(
     *  path="/emailTemplate/{email_template_key}",
     *  summary="Show a Email Template",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Template"},
     *
     *  @SWG\Parameter(
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
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response="200",
     *      description="Show the Email Template data",
     *      @SWG\Schema(ref="#/definitions/emailTemplateReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Template not Found",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Email Template",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailTemplateKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $emailTemplateKey)
    {
        try {
            $emailTemplate = EmailTemplate::with([
                "type.tags",
                "getAllTranslations",
                "emailGroup"
            ])
                ->whereEmailTemplateKey($emailTemplateKey)
                ->firstOrFail();

            $emailTemplate->translations = $emailTemplate->getAllTranslations->keyBy("language_code");
            unset($emailTemplate->getAllTranslations);

            $emailTemplate->type->newTranslation($request->header('LANG-CODE'), $request->header('LANG-CODE-DEFAULT'));

            foreach ($emailTemplate->type->tags as $tag) {
                $tag->newTranslation($request->header('LANG-CODE'), $request->header('LANG-CODE-DEFAULT'));
            }

            $emailTemplate['type'] = $emailTemplate;
            $emailTemplate['site_key'] = $emailTemplate->emailGroup->site_key;

            return response()->json($emailTemplate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Email Template'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $emailTemplateKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $emailTemplateKey)
    {
        try {
            $emailTemplate = EmailTemplate::with([
                    "type.tags",
                    "getAllTranslations",
                    "emailGroup"
                ])
                ->whereEmailTemplateKey($emailTemplateKey)
                ->firstOrFail();

            $emailTemplate->translations = $emailTemplate->getAllTranslations->keyBy("language_code");
            unset($emailTemplate->getAllTranslations);

            $emailTemplate->type->newTranslation($request->header('LANG-CODE'), $request->header('LANG-CODE-DEFAULT'));

            foreach ($emailTemplate->type->tags as $tag) {
                $tag->newTranslation($request->header('LANG-CODE'), $request->header('LANG-CODE-DEFAULT'));
            }

            $emailTemplate['type'] = $emailTemplate;
            $emailTemplate['site_key'] = $emailTemplate->emailGroup->site_key;
            
            return response()->json($emailTemplate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Post(
     *  path="/emailTemplate",
     *  summary="Create an Email Template",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Template"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Email Template Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailTemplateCreate")
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
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
     *      response=201,
     *      description="the newly created Email Template",
     *      @SWG\Schema(ref="#/definitions/emailTemplateReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Template not found",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to store Email Template",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  )
     * )
     */

    /**
     * Store a new Event in the database
     * Return the Attributes of the Event created
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->required["store_update"], $request);

        try {
            $key = '';
            do {
                $rand = str_random(32);

                if (!($exists = EmailTemplate::whereEmailTemplateKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $type = Type::whereTypeKey($request->json('type_key'))->firstOrFail();
            $emailGroup = EmailGroup::whereSiteKey($request->json('site_key'))->first();

            if(is_null($emailGroup)){
                $emailGroupKey = '';
                do {
                    $rand = str_random(32);

                    if (!($exists = EmailGroup::whereEmailGroupKey($rand)->exists())) {
                        $emailGroupKey = $rand;
                    }
                } while ($exists);

                $emailGroup = EmailGroup::create(
                    [
                        'email_group_key'   => $emailGroupKey,
                        'site_key'          => $request->json('site_key')
                    ]
                );
            }

            $emailTemplate = EmailTemplate::create(
                [
                    'email_template_key'    => $key,
                    'email_group_id'        => $emailGroup->id,
                    'type_id'               => $type->id
                ]
            );

            foreach ($request->json('translations') as $translation) {
                if (!empty($translation['language_code']) && !empty($translation['subject']) && isset($translation['header']) && !empty($translation['content']) && isset($translation['footer'])) {
                    $emailTemplate->emailTemplateTranslations()->create(
                        [
                            'language_code' => $translation['language_code'],
                            'subject' => $translation['subject'],
                            'header' => htmlentities($translation['header'], ENT_QUOTES, "UTF-8"),
                            'content' => htmlentities($translation['content'], ENT_QUOTES, "UTF-8"),
                            'footer' => htmlentities($translation['footer'], ENT_QUOTES, "UTF-8")
                        ]
                    );
                } else {
                    EmailTemplate::destroy($emailTemplate->id);
                    return response()->json(['error' => 'Failed to store Translation to new Email Template'], 500);
                }
            }

            return response()->json($emailTemplate, 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to store new Email Template'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/emailTemplate/{email_template_key}",
     *  summary="Update an Email Template",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Template"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Email Template Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailTemplateCreate")
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
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
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
     *      description="The updated Email Template",
     *      @SWG\Schema(ref="#/definitions/emailGroupReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Email Template not Found",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to update Email Template",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailTemplateKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $emailTemplateKey)
    {
        ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->required["store_update"], $request);

        try {
            $newEmailTemplates = [];
            $oldEmailTemplates = [];

            $emailTemplate = EmailTemplate::whereEmailTemplateKey($emailTemplateKey)->firstOrFail();

            $type = Type::whereTypeKey($request->json('type_key'))->firstOrFail();

            $emailTemplate->type_id = $type->id;
            $emailTemplate->save();

            $oldEmailTemplates = $emailTemplate->emailTemplateTranslations()->get()
                ->pluck('id')
                ->toarray();

            foreach ($request->json('translations') as $translation) {
                if (!empty($translation['language_code']) && !empty($translation['subject']) && isset($translation['header']) && !empty($translation['content']) && isset($translation['footer'])) {
                    $emailTemplateTranslation = $emailTemplate->emailTemplateTranslations()->whereLanguageCode($translation['language_code'])->first();
                    if (empty($emailTemplateTranslation)) {
                        $emailTemplate->emailTemplateTranslations()->create([
                            'language_code' => $translation['language_code'],
                            'subject' => $translation['subject'],
                            'header' => htmlentities($translation['header'], ENT_QUOTES, "UTF-8"),
                            'content' => htmlentities($translation['content'], ENT_QUOTES, "UTF-8"),
                            'footer' => htmlentities($translation['footer'], ENT_QUOTES, "UTF-8")
                        ]);
                    } else {
                        $emailTemplateTranslation->subject = $translation['subject'];
                        $emailTemplateTranslation->header = htmlentities($translation['header'], ENT_QUOTES, "UTF-8");
                        $emailTemplateTranslation->content = htmlentities($translation['content'], ENT_QUOTES, "UTF-8");
                        $emailTemplateTranslation->footer = htmlentities($translation['footer'], ENT_QUOTES, "UTF-8");
                        $emailTemplateTranslation->save();

                        $newEmailTemplates[] = $emailTemplateTranslation->id;
                    }
                } else {
                    return response()->json(['error' => 'Failed to update Email Template'], 400);
                }
            }

            $deleteEmailTemplates = array_diff($oldEmailTemplates, $newEmailTemplates);

            foreach ($deleteEmailTemplates as $deleteEmailTemplate) {
                EmailTemplateTranslation::destroy($deleteEmailTemplate);
            }

            return response()->json($emailTemplate, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to update Email Template'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/emailTemplate/{email_template_key}",
     *  summary="Delete an Email Template",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Template"},
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
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="OK",
     *      @SWG\Schema(ref="#/definitions/emailTemplateDeleteReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Template not Found",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Email Template",
     *      @SWG\Schema(ref="#/definitions/emailTemplateErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailTemplateKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $emailTemplateKey)
    {
        ONE::verifyToken($request);

        try {
            $emailTemplate = EmailTemplate::whereEmailTemplateKey($emailTemplateKey)->firstOrFail();
            EmailTemplate::destroy($emailTemplate->id);

            return response()->json('OK', 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to delete Email Template'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getEmailTemplate(Request $request){
        try {
            $emailTemplate = EmailTemplate::whereEmailGroupId(EmailGroup::whereSiteKey($request->input('siteKey'))->first()->id)->whereTypeId(Type::whereCode($request->input('code'))->first()->id)->first();

            return response()->json($emailTemplate, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Email Template'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Template not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);

    }
}