<?php

namespace App\Http\Controllers;

use App\EmailGroup;
use App\EmailTemplate;
use App\GenericEmailTemplate;
use App\Type;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class GenericEmailTemplateTemplatesController
 * @package App\Http\Controllers
 */

/**
 * @SWG\Tag(
 *   name="Generic Email Template",
 *   description="Everything about Generic Email Templates",
 * )
 *
 *  @SWG\Definition(
 *      definition="genericEmailTemplateErrorDefault",
 *      @SWG\Property(property="error", type="string", format="string")
 *  )
 *
 *  @SWG\Definition(
 *   definition="genericEmailTemplateCreate",
 *   type="object",
 *   allOf={
 *      @SWG\Schema(
 *           required={"site_keys", "languages", "default_language"},
 *           @SWG\Property(property="site_keys", type="array", @SWG\Items(type="string")),
 *           @SWG\Property(property="languages", type="array", @SWG\Items(type="string")),
 *           @SWG\Property(property="default_language", format="string", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *     definition="genericEmailTemplateReply",
 *     @SWG\Property(property="string", type="string", format="string")
 * )
 */

class GenericEmailTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @SWG\Post(
     *  path="/genericEmailTemplate/newSiteTemplates",
     *  summary="Copy the required Generic Email Templates to the new Site/Language",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Generic Email Template"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Generic Email Template Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/genericEmailTemplateCreate")
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
     *      description="Success Message",
     *      @SWG\Schema(ref="#/definitions/genericEmailTemplateReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/genericEmailTemplateErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed",
     *      @SWG\Schema(ref="#/definitions/genericEmailTemplateErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newSiteTemplates(Request $request)
    {
        try {
            foreach ($request->json('site_keys') as $siteKey) {

                //Verifies if the site already have an Email Group
                $emailGroup = EmailGroup::whereSiteKey($siteKey)->first();

                if(is_null($emailGroup)) {
                    $key = '';
                    do {
                        $rand = str_random(32);
                        if (!($exists = EmailGroup::whereEmailGroupKey($rand)->exists())) {
                            $key = $rand;
                        }
                    } while ($exists);

                    try {
                        $emailGroup = EmailGroup::create([
                            'email_group_key' => $key,
                            'site_key' => $siteKey
                        ]);
                    } catch (Exception $e) {
                        return response()->json(['error' => 'Failed to store new Email Group'], 500);
                    }
                }

                try {
                    $languages = $request->json('languages');
                    $defaultLanguage = $request->json('default_language');

                    $genericTemplates = GenericEmailTemplate::all()->each(function ($item) use ($languages, $emailGroup, $defaultLanguage) {

                        try {
                            $emailTemplate = $emailGroup->emailTemplates()->whereTypeId($item->type_id)->first();

                            if(is_null($emailTemplate)) {
                                $key = '';
                                do {
                                    $rand = str_random(32);
                                    if (!($exists = EmailTemplate::whereEmailTemplateKey($rand)->exists())) {
                                        $key = $rand;
                                    }
                                } while ($exists);

                                $emailTemplate = EmailTemplate::create([
                                    'email_template_key' => $key,
                                    'email_group_id' => $emailGroup->id,
                                    'type_id' => $item->type_id
                                ]);
                            }

                            //get all the existing translations for the email template
                            $item->translations();

                            foreach ($languages as $language) {
                                if (!empty($item->translations[$language])) {
                                    if ($this->verifyTranslation($item, $language)) {
                                        $this->storeTranslation($emailTemplate, $item, $language);
                                    } else {
                                        EmailTemplate::destroy($emailTemplate->id);
                                        return response()->json(['error' => 'Failed to store Translation to new Email Template'], 500);
                                    }
                                } elseif (!empty($item->translations[$defaultLanguage])) {
                                    if ($this->verifyTranslation($item, $defaultLanguage)) {
                                        $this->storeTranslation($emailTemplate, $item, $language, $defaultLanguage);
                                    } else {
                                        EmailTemplate::destroy($emailTemplate->id);
                                        return response()->json(['error' => 'Failed to store Translation to new Email Template'], 500);
                                    }
                                } elseif (!empty($item->translations['en'])) {
                                    if ($this->verifyTranslation($item, 'en')) {
                                        $this->storeTranslation($emailTemplate, $item, $language, 'en');
                                    } else {
                                        EmailTemplate::destroy($emailTemplate->id);
                                        return response()->json(['error' => 'Failed to store Translation to new Email Template'], 500);
                                    }
                                }
                            }
                            return response()->json($emailTemplate, 201);
                        } catch (QueryException $e) {
                            return response()->json(['error' => 'Failed to store new Email Template'], 500);
                        }
                    });
                } catch (Exception $e) {
                    return response()->json(['error' => 'Failed to copy existing Email Templates'], 500);
                }

                return response()->json('OK', 201);
            }
        }
        catch(Exception $e){
            return response()->json(['error' => 'Failed to store new Site Email Templates'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param $item
     * @param $language
     * @return bool
     */
    private function verifyTranslation($item, $language){
        if (
            !empty($item->translations[$language]['language_code']) &&
            !empty($item->translations[$language]['subject']) &&
            isset($item->translations[$language]['header']) &&
            !empty($item->translations[$language]['content']) &&
            isset($item->translations[$language]['footer'])
        ){
            return true;
        }
        return false;
    }

    /**
     * @param $emailTemplate
     * @param $item
     * @param $language
     * @param null $altLanguage
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function storeTranslation($emailTemplate, $item, $language, $altLanguage = null){
        try {
            if (!$emailTemplate->emailTemplateTranslations()->whereLanguageCode($language)->exists()) {
                $emailTemplate->emailTemplateTranslations()->create([
                    'language_code' => $language,
                    'subject' => $item->translations[$altLanguage ?? $language]['subject'],
                    'header' => htmlentities($item->translations[$altLanguage ?? $language]['header'], ENT_QUOTES, "UTF-8"),
                    'content' => htmlentities($item->translations[$altLanguage ?? $language]['content'], ENT_QUOTES, "UTF-8"),
                    'footer' => htmlentities($item->translations[$altLanguage ?? $language]['footer'], ENT_QUOTES, "UTF-8")
                ]);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to store Email Template Translation'], 500);
        }
        return true;
    }
}
