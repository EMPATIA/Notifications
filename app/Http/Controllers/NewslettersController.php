<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailGroup;
use App\EmailTemplate;
use App\Newsletter;
use App\One\One;
use App\QueuedEmailData;
use App\Type;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Exception;

class NewslettersController extends Controller {


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        ONE::verifyToken($request);

        try{
            $entityKey = $request->header('X-ENTITY-KEY');
            $newsletters = Newsletter::whereEntityKey($entityKey);

            $tableData = $request->input('tableData') ?? null;
            $recordsTotal = $newsletters->count();
            $query = $newsletters;
            $query = $query
                ->orderBy($tableData['order']['value'], $tableData['order']['dir']);

            if(!empty($tableData['search']['value'])) {
                $query = $query
                    ->orWhere('created_by', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('created_at', 'like', '%'.$tableData['search']['value'].'%');
            }

            $recordsFiltered = $query->count();
            $newsletters = $query
                ->skip($tableData['start'])
                ->take($tableData['length'])
                ->get();

            $data['newsletters'] = $newsletters;
            $data['recordsTotal'] = $recordsTotal;
            $data['recordsFiltered'] = $recordsFiltered;

            return response()->json($data, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to get Newsletters'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Failed to get Newsletters'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        ONE::verifyToken($request);
        try {
            $entityKey = $request->header('X-ENTITY-KEY');

            $key = '';
            do {
                $rand = str_random(32);
                if (!($exists = Newsletter::whereNewsletterKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $extraData = [];
            if (!empty($request->json("questionnaire"))){
                $extraData["questionnaire"] = $request->json("questionnaire");
            }

            $newsletter = Newsletter::create([
                'newsletter_key' => $key,
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'title' => !empty($request->input('title')) ? $request->input('title') : null,
                'created_by' => $request->input('created_by'),
                'entity_key' => $entityKey,
                'tested' => '0',
                'extra_data' => json_encode($extraData)
            ]);

            return response()->json($newsletter, 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to store new Newsletter'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $newsletterKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $newsletterKey)
    {
        try {
            $newsletter = Newsletter::whereNewsletterKey($newsletterKey)->firstOrFail();

            return response()->json($newsletter, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Newsletter not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Newsletter'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $newsletterKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $newsletterKey){
        try {
            $newsletter = Newsletter::whereNewsletterKey($newsletterKey)->firstOrFail();

            $newsletter->title = !is_null($request->input('title')) ? $request->input('title') : null;
            $newsletter->content  = $request->input('content');
            $newsletter->subject = $request->input('subject');
            $newsletter->tested = '0';
            $newsletter->tested_by = null;

            $newsletter->save();

            return response()->json($newsletter, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Newsletter not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Newsletter'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $newsletterKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $newsletterKey){
        try {
            $newsletter = Newsletter::whereNewsletterKey($newsletterKey)->firstOrFail();
            $newsletter->delete();

            return response()->json('OK', 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Failed to delete Newsletter'], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Newsletter not Found'], 404);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $newsletterKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function testNewsletter(Request $request, $newsletterKey){
        try {
            $newsletter = Newsletter::whereNewsletterKey($newsletterKey)->firstOrFail();

            $newsletter->tested = '1';
            $newsletter->tested_by = $request->input('user_key');
            $newsletter->save();

            return response()->json($newsletter, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Newsletter not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Newsletter'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

}
