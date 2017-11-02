<?php

namespace App\Http\Controllers;

use App\EmailGroup;
use App\One\One;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class EmailGroupsController
 * @package App\Http\Controllers
 */

/**
 * @SWG\Tag(
 *   name="Email Group",
 *   description="Everything about Email Groups",
 * )
 *
 *  @SWG\Definition(
 *      definition="emailGroupErrorDefault",
 *      @SWG\Property(property="error", type="string", format="string")
 *  )
 *
 *  @SWG\Definition(
 *   definition="emailGroupCreate",
 *   type="object",
 *   allOf={
 *       @SWG\Schema(
 *           required={"site_key"},
 *           @SWG\Property(property="site_key", format="string", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *   definition="emailGroupReply",
 *   type="object",
 *   allOf={
 *      @SWG\Schema(
 *           @SWG\Property(property="id", format="integer", type="integer"),
 *           @SWG\Property(property="email_group_key", format="string", type="string"),
 *           @SWG\Property(property="site_key", format="string", type="string"),
 *           @SWG\Property(property="created_at", format="date", type="string"),
 *           @SWG\Property(property="updated_at", format="date", type="string"),
 *           @SWG\Property(property="deleted_at", format="date", type="string")
 *       )
 *   }
 * )
 *
 *  @SWG\Definition(
 *     definition="emailGroupDeleteReply",
 *     @SWG\Property(property="string", type="string", format="string")
 * )
 */

class EmailGroupsController extends Controller
{
    protected $keysRequired = [
        'site_key'
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $emailGroups = EmailGroup::all();

            return response()->json(['data' => $emailGroups], 200);

        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Email Groups'], 500);
        }
    }

    /**
     * @SWG\Get(
     *  path="/emailGroup/{email_group_key}",
     *  summary="Show a Email Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Group"},
     *
     *  @SWG\Parameter(
     *      name="email_group_key",
     *      in="path",
     *      description="Email Group Key",
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
     *      description="Show the Email Group data",
     *      @SWG\Schema(ref="#/definitions/emailGroupReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Group not Found",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  )
     * )
     */

    /**
     * @param $emailGroupKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($emailGroupKey)
    {
        try{
            $emailGroup = EmailGroup::whereEmailGroupKey($emailGroupKey)->firstOrFail();

            return response()->json($emailGroup, 200);

        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Group not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Email Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Post(
     *  path="/emailGroup",
     *  summary="Create a Email Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Group"},
     *
     *  @SWG\Parameter(
     *      name="EmailGroup",
     *      in="body",
     *      description="Email Group Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailGroupCreate")
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
     *      description="the newly created Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Group not found",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to store Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);

        try{
            $key = '';
            do {
                $rand = str_random(32);

                if (!($exists = EmailGroup::whereEmailGroupKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $emailGroup = EmailGroup::create(
                [
                    'email_group_key'   => $key,
                    'site_key'          => $request->json('site_key')
                ]
            );
            return response()->json($emailGroup, 201);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to store new Email Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/emailGroup/{email_group_key}",
     *  summary="Update an Email Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Group"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Email Group Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/emailGroupCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="email_group_key",
     *      in="path",
     *      description="Email Group Key",
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
     *      description="The updated Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Email Group not Found",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to update Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailGroupKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $emailGroupKey)
    {
        ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);
        try{
            $emailGroup = EmailGroup::whereEmailGroupKey($emailGroupKey)->firstOrFail();

            $emailGroup->site_key = $request->json('site_key');
            $emailGroup->save();

            return response()->json($emailGroup, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Group not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to update Email Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/emailGroup/{email_group_key}",
     *  summary="Delete an Email Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Email Group"},
     *
     * @SWG\Parameter(
     *      name="email_group_key",
     *      in="path",
     *      description="Email Group Key",
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
     *      @SWG\Schema(ref="#/definitions/emailGroupDeleteReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Email Group not Found",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Email Group",
     *      @SWG\Schema(ref="#/definitions/emailGroupErrorDefault")
     *  )
     * )
     */

    /**
     * @param Request $request
     * @param $emailGroupKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $emailGroupKey)
    {
        ONE::verifyToken($request);
        try{
            $emailGroup = EmailGroup::whereEmailGroupKey($emailGroupKey)->firstOrFail();
            $emailGroup->delete();

            return response()->json('Ok', 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Group not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Email Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param $siteKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function siteEmailTemplates($siteKey)
    {
        try{
            $emailGroup = EmailGroup::whereSiteKey($siteKey)->first();

            $emailTemplates = [];
            if($emailGroup){
                $emailTemplates = $emailGroup->emailTemplates()->get();
            }

            return response()->json(['data' => $emailTemplates], 200);
            
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Email Templates not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Email Templates'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
