<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id')->unsigned();
            $table->string('language_code');
            $table->string('name');

            $table->timestamps();
            $table->softDeletes();
        });

        $type_translations = array(
            array('id' => '1',    'type_id' => '1',   'language_code' => 'en',  'name' => 'Registration Confirmed',                   'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '2',    'type_id' => '2',   'language_code' => 'en',  'name' => 'Account Authorized',                       'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '3',    'type_id' => '3',   'language_code' => 'en',  'name' => 'Votes Submitted',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '4',    'type_id' => '4',   'language_code' => 'en',  'name' => 'Email Recovery',                           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '5',    'type_id' => '5',   'language_code' => 'en',  'name' => 'Reset Password Confirmation',              'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '6',    'type_id' => '6',   'language_code' => 'en',  'name' => 'Topic Status Update',                      'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '7',    'type_id' => '9',   'language_code' => 'en',  'name' => 'Topic Review',                             'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '8',    'type_id' => '10',  'language_code' => 'en',  'name' => 'Message Notification',                     'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '9',    'type_id' => '11',  'language_code' => 'en',  'name' => 'Group Message Notification',               'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '10',   'type_id' => '12',  'language_code' => 'en',  'name' => 'Question Submitted',                       'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '11',   'type_id' => '12',  'language_code' => 'de',  'name' => 'Question Submitted',                       'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '12',   'type_id' => '13',  'language_code' => 'en',  'name' => 'Changed Status QA Notification',           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '13',   'type_id' => '13',  'language_code' => 'de',  'name' => 'Changed Status QA Notification',           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '14',   'type_id' => '14',  'language_code' => 'en',  'name' => 'New Thematic Consultation Notification',   'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '15',   'type_id' => '14',  'language_code' => 'pt',  'name' => 'Notificação de Nova Consulta Temática',    'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '16',   'type_id' => '15',  'language_code' => 'en',  'name' => 'Generic CB Notifications',                 'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '17',   'type_id' => '15',  'language_code' => 'de',  'name' => 'Generic CB Notifications',                 'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '18',   'type_id' => '16',  'language_code' => 'pt',  'name' => 'Generic Entity Notifications',             'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '19',   'type_id' => '16',  'language_code' => 'en',  'name' => 'Generic Entity Notifications',             'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '20',   'type_id' => '17',  'language_code' => 'pt',  'name' => 'Notificação de Análise Técnica',           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '21',   'type_id' => '17',  'language_code' => 'en',  'name' => 'Technical Analysis Notification',          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL)
        );
        DB::table('type_translations')->insert($type_translations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('type_translations');
    }
}
