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
    Route::get('email/getCountTotalSentEmails', 'EmailsController@getCountTotalSentEmails');
    Route::get('email/getCountTotalNotSentEmails', 'EmailsController@getCountTotalNotSentEmails');
    Route::get('email/countTotalMailsErrors', 'EmailsController@getCountTotalMailsErrors');
    Route::get('email/countTotalSentEmails30DPersonalized', 'EmailsController@getCountTotalSentEmails30DPersonalized');
    Route::get('email/countTotalNotSentEmails30DPersonalized', 'EmailsController@getCountTotalNotSentEmails30DPersonalized');
    Route::get('email/countTotalEmailsErrors30DPersonalized', 'EmailsController@getCountTotalEmailsErrors30DPersonalized');
    Route::resource('email', 'EmailsController',['only' => ['show', 'store', 'update', 'destroy']]);



    Route::get('sms/entitySms', 'SMSController@getEntitySms');
    Route::get('sms/receivedEntitySms', 'SMSController@getReceivedEntitySms');
    Route::get('sms/getCountTotalSendedSms', 'SMSController@getCountTotalSendedSms');
    Route::get('sms/getCountTotalReceivedSms', 'SMSController@getCountTotalReceivedSms');
    Route::get('sms/getCountTotalSmsVotes', 'SMSController@getCountTotalSmsVotes');
    Route::get('sms/getCountTotalSendedSmsLast30D', 'SMSController@getCountTotalSendedSmsLast30D');
    Route::get('sms/getCountTotalSendedSmsLast24H', 'SMSController@getCountTotalSendedSmsLast24H');
    Route::get('sms/getCountTotalSendedSmsLastHour', 'SMSController@getCountTotalSendedSmsLastHour');
    Route::get('sms/getCountTotalReceivedSmsErrors', 'SMSController@getCountTotalReceivedSmsErrors');
    Route::get('sms/getCountTotalReceivedSmsLast24H', 'SMSController@getCountTotalReceivedSmsLast24H');
    Route::get('sms/getCountTotalReceivedSmsLast24hErrors', 'SMSController@getCountTotalReceivedSmsLast24hErrors');
    Route::get('sms/getCountTotalReceivedSmsLast48H', 'SMSController@getCountTotalReceivedSmsLast48H');
    Route::get('sms/getCountTotalReceivedSmsLast30D', 'SMSController@getCountTotalReceivedSmsLast30D');
    Route::get('sms/getCountTotalSendedSmsLast48H', 'SMSController@getCountTotalSendedSmsLast48H');
    Route::get('sms/getCountTotalSendedSmsLast30dPerDay', 'SMSController@getCountTotalSendedSmsLast30dPerDay');
    Route::get('sms/getCountTotalSmsVotesLast48H', 'SMSController@getCountTotalSmsVotesLast48H');
    Route::get('sms/getCountTotalSmsVotesLast30D', 'SMSController@getCountTotalSmsVotesLast30D');
    Route::get('sms/getCountTotalSmsVotesErrorsLast48H', 'SMSController@getCountTotalSmsVotesErrorsLast48H');
    Route::get('sms/getCountTotalSmsVotesErrorsLast30D', 'SMSController@getCountTotalSmsVotesErrorsLast30D');

    Route::get('sms/countTotalSendedSms24hPersonalized', 'SMSController@countTotalSendedSms24hPersonalized');
    Route::get('sms/countTotalReceivedSms24hPersonalized/', 'SMSController@countTotalReceivedSms24hPersonalized');
    Route::get('sms/countTotalSmsVotes24hPersonalized/', 'SMSController@countTotalSmsVotes24hPersonalized');
    Route::get('sms/countTotalSmsVotesErrors24hPersonalized/', 'SMSController@countTotalSmsVotesErrors24hPersonalized');

    Route::get('sms/countTotalSendedSms30DPersonalized', 'SMSController@countTotalSendedSms30DPersonalized');
    Route::get('sms/countTotalReceivedSms30DPersonalized/', 'SMSController@countTotalReceivedSms30DPersonalized');
    Route::get('sms/countTotalSmsVotes30DPersonalized/', 'SMSController@countTotalSmsVotes30DPersonalized');
    Route::get('sms/countTotalSmsVotesErrors30DPersonalized/', 'SMSController@countTotalSmsVotesErrors30DPersonalized');

    Route::get('sms/getReceivedSmsDetails/{receivedSmsKey}', 'SMSController@getReceivedSmsDetails');
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


    /**
     * Store Received SMS
     */
    Route::post("receivedSMS","ReceivedSMSController@store");
});

Route::group(['middleware' => ['web']], function () {
    //
});
