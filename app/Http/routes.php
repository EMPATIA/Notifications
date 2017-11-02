<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['authOne']], function () {

    /**
     * Route for the requests of Email Model
     */
    Route::post('email/createEmails', 'EmailsController@createEmails');
    Route::post('email/send/{email_template_key}', 'EmailsController@send');
    Route::post('email/emailSend/{type_code}', 'EmailsController@emailSend');
    Route::post('email/sendMany/{type_code}', 'EmailsController@sendManyEmails');
    Route::get('email/entityEmail', 'EmailsController@getEntityEmail');
    Route::resource('email', 'EmailsController',['only' => ['show', 'store', 'update', 'destroy']]);



    Route::get('sms/entitySms', 'SMSController@getEntitySms');
    Route::post('sms/sendSMS', 'SMSController@sendSMS');
    Route::resource('sms', 'SMSController',['only' => ['show', 'store', 'update', 'destroy']]);
    /**
     * Route for the requests of Email Template Model
     */
    Route::get('emailTemplate/getEmailTemplate', 'EmailTemplatesController@getEmailTemplate');
    Route::get('emailTemplate/list', 'EmailTemplatesController@index');
    Route::get('emailTemplate/{email_template_key}/edit', 'EmailTemplatesController@edit');
    Route::resource('emailTemplate', 'EmailTemplatesController',['only' => ['show', 'store', 'update', 'destroy']]);



    /**
     * Route for the requests of Type Model
     */
    Route::get('type/list', 'TypesController@index');
    Route::get('type/getTypeKey', 'TypesController@getTypeKey');
    Route::get('type/available', 'TypesController@availableTypes');
    Route::get('type/{type_key}/edit', 'TypesController@edit');
    Route::resource('type', 'TypesController',['only' => ['show', 'store', 'update', 'destroy']]);

    /**
     * Route for the requests of Email Group Model
     */
    Route::get('emailGroup/siteTemplates/{site_key}', 'EmailGroupsController@siteEmailTemplates');

    /**
     * Route for the requests of Generic Email Templates
     */
    Route::post('genericEmailTemplate/newSiteTemplates', 'GenericEmailTemplatesController@newSiteTemplates');

    /**
     * Route for the SMS API of Skebby
     */
    Route::post('skebby/sendSMS', 'SMSController@skebbyGatewaySendSMS');
    Route::get('skebby/credit/{username}/{password}/{charset?}', 'SMSController@skebbyGatewayGetCredit');

    /**
     * Route for the SMS API of BulkSMS
     */
    Route::post('bulkSMS/sendSMS', 'SMSController@bulkSMSGatewaySendSMS');

    /**
     * Routes related to newsletters
     */
    Route::get('newsletters/list','NewslettersController@index');
    Route::post('newsletters/testNewsletter/{newsletterKey}','NewslettersController@testNewsletter');
    Route::post('newsletters/sendNewsletter/{newsletterKey}','NewslettersController@sendNewsletter');
    Route::resource('newsletters', 'NewslettersController',['only' => ['show', 'store', 'update', 'destroy']]);
});

Route::group(['middleware' => ['web']], function () {
    //
});
