<?php

namespace App\Http\Controllers;

use App\EmailGroup;
use App\EmailTemplate;
use App\One\One;
use App\Type;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class TypesController
 * @package App\Http\Controllers
 */

/**
 * @SWG\Tag(
 *   name="Type",
 *   description="Everything about Types",
 * )
 *
 *  @SWG\Definition(
 *      definition="typeErrorDefault",
 *      @SWG\Property(property="error", type="string", format="string")
 *  )
 *
 *  @SWG\Definition(
 *   definition="typeTranslations",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"language_code", "subject", "header", "content", "footer"},
 *           @SWG\Property(property="language_code", format="string", type="string"),
 *           @SWG\Property(property="name", format="string", type="string"),
 *           @SWG\Property(property="subject", format="string", type="string"),
 *           @SWG\Property(property="header", format="string", type="string"),
 *           @SWG\Property(property="content", format="string", type="string"),
 *           @SWG\Property(property="footer", format="string", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="typeCreate",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"code", "translations"},
 *           @SWG\Property(property="code", format="string", type="string"),
 *           @SWG\Property(
 *             property="translations",
 *             type="array",
 *             @SWG\Items(ref="#/definitions/typeTranslations")
 *           ),
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="typeReply",
 *   type="object",
 *   allOf={
 *      @SWG\Schema(
 *           @SWG\Property(property="id", format="integer", type="integer"),
 *           @SWG\Property(property="type_key", format="string", type="string"),
 *           @SWG\Property(property="code", format="string", type="string"),
 *           @SWG\Property(property="name", format="string", type="string"),
 *           @SWG\Property(property="created_at", format="date", type="string"),
 *           @SWG\Property(property="updated_at", format="date", type="string"),
 *           @SWG\Property(property="deleted_at", format="date", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *     definition="typeDeleteReply",
 *     @SWG\Property(property="string", type="string", format="string")
 * )
 */

class TypesController extends Controller
{
    protected $keysRequired = [
        'translations'
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $types = Type::all();

            $primaryLanguage = $request->header('LANG-CODE');
            $defaultLanguage = $request->header('LANG-CODE-DEFAULT');

            foreach ($types as $type) {
                $type->newTranslation($primaryLanguage,$defaultLanguage);
            }

            return response()->json(['data' => $types], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Types'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Get(
     *  path="/type/{type_key}",
     *  summary="Show a Type",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Type"},
     *
     *  @SWG\Parameter(
     *      name="type_key",
     *      in="path",
     *      description="Type Key",
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
     *      description="Show the Type data",
     *      @SWG\Schema(ref="#/definitions/typeReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Type not Found",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Type",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $typeKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $typeKey)
    {
        try {
            $type = Type::whereTypeKey($typeKey)->firstOrFail();

            if (!($type->translation($request->header('LANG-CODE')))) {
                if (!$type->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!($type->translation('en'))) {
                        return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }

            return response()->json($type, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $typeKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $typeKey)
    {
        try {
            $type = Type::whereTypeKey($typeKey)->firstOrFail();

            $type->translations();

            return response()->json($type, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Parameter User Option not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Post(
     *  path="/type",
     *  summary="Create a Type",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Type"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Type Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/typeCreate")
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
     *      description="the newly created Type",
     *      @SWG\Schema(ref="#/definitions/typeReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Type not found",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to store Type",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);
        ONE::verifyKeysRequest(['code'], $request);

        try {
            $key = '';
            do {
                $rand = str_random(32);

                if (!($exists = Type::whereTypeKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $type = Type::create(
                [
                    'type_key' => $key,
                    'code' => $request->json('code'),
                ]
            );

            foreach ($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name'])){
                    $typeTranslations = $type->typeTranslations()->create(
                        [
                            'language_code' => $translation['language_code'],
                            'name'          => $translation['name']
                        ]
                    );
                }
            }

            return response()->json($type, 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to store new Type'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/type/{type_key}",
     *  summary="Update a Type",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Type"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Type Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/typeCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="type_key",
     *      in="path",
     *      description="Type Key",
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
     *      description="The updated Type",
     *      @SWG\Schema(ref="#/definitions/typeReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Type not Found",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to update Type",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $typeKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $typeKey)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);

        try {
            $translationsOld = [];
            $translationsNew = [];

            $type = Type::whereTypeKey($typeKey)->firstOrFail();

            if(!is_null($request->json('code'))){
                $type->code = $request->json('code');
                $type->save();
            }

            $translationsId = $type->typeTranslations()->get();
            foreach ($translationsId as $translationId){
                $translationsOld[] = $translationId->id;
            }

            foreach($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name'])){
                    $typeTranslations = $type->typeTranslations()->whereLanguageCode($translation['language_code'])->first();
                    if (empty($typeTranslations)) {
                        $typeTranslations = $type->typeTranslations()->create(
                            [
                                'language_code' => $translation['language_code'],
                                'name'          => $translation['name']
                            ]
                        );
                    }
                    else {
                        $typeTranslations->name = $translation['name'];
                        $typeTranslations->save();
                    }
                }
                $translationsNew[] = isset($typeTranslations) ? $typeTranslations->id : null;
            }

            $deleteTranslations = array_diff($translationsOld, $translationsNew);
            foreach ($deleteTranslations as $deleteTranslation) {
                $deleteId = $type->typeTranslations()->whereId($deleteTranslation)->first();
                $deleteId->delete();
            }

            return response()->json($type, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to update Type'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/type/{type_key}",
     *  summary="Delete an Type",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Type"},
     *
     * @SWG\Parameter(
     *      name="type_key",
     *      in="path",
     *      description="Type Key",
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
     *      @SWG\Schema(ref="#/definitions/typeDeleteReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Type not Found",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Type",
     *      @SWG\Schema(ref="#/definitions/typeErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $typeKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $typeKey)
    {
        $userKey = ONE::verifyToken($request);

        try {
            $type = Type::whereTypeKey($typeKey)->firstOrFail();
            $type->typeTranslations()->delete();
            $type->delete();

            return response()->json('OK', 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to delete Type'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableTypes(Request $request)
    {
        try {
            $typesUsed = EmailTemplate::whereEmailGroupId(EmailGroup::whereSiteKey($request->header('X-SITE-KEY'))->firstOrFail()->id)->distinct()->pluck('type_id');
            $availableTypes = Type::whereIn('id', Type::pluck('id')->diff($typesUsed))->get();

            foreach ($availableTypes as $type) {
                if (!($type->translation($request->header('LANG-CODE')))) {
                    if (!$type->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!($type->translation('en'))) {
                            return response()->json(['error' => 'No translation found'], 404);
                        }
                    }
                }
            }

            return response()->json(['data' => $availableTypes], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Types not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getTypeKey(Request $request){
        try {
            $code = $request->typeCode;
            $type = Type::whereCode($code)->firstOrFail();

            return response()->json($type->type_key, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Type not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
