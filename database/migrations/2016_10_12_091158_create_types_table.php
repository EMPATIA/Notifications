<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_key')->unique();
            $table->string('code');

            $table->timestamps();
            $table->softDeletes();
        });

        $types = array(
            array('id' => '1',	'type_key' => 'x8oqb9433fv6apR6RvjJEhCMV5JqHx2N',	'code' => 'registry_confirmation',		        'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '2',	'type_key' => '8buO6541pV6I7t7sP7Fi15SOaFVJTjKy',	'code' => 'account_authorized',			        'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '3',	'type_key' => '9Ju95ew51K2505wH0V4N005SAjy26DXE',	'code' => 'vote_submitted',				        'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '4',	'type_key' => '39jxMTW55y8P2y39P89PEugx66feHkYz',	'code' => 'password_recovery',			        'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '5',	'type_key' => 'Y63fL63J31Awjvn9MH5G5626UW82a4Ta',	'code' => 'reset_password_confirmation',	    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '6',	'type_key' => '8YQ9kE6TIew3RzkdwnZSW3G8E8rmENTR',	'code' => 'topic_status_update',			    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '7',	'type_key' => '8IaitozqfiqAA63ipHXbWvXXruqewjgp',	'code' => 'topic_review',				        'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '8',	'type_key' => 'kdwnZSW3G8E8YQ9kE6TIew3Rz8rmENTR',	'code' => 'message_notification',			    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '9',	'type_key' => 'KGnw01txgLYeevk2T9lWK6u1hs3NGBGW',	'code' => 'group_message_sent_notification',	'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '10',	'type_key' => '6u1hw01txgLYNGBeevk2T9lWKs3GWKGn',	'code' => 'qa_message_send_success',			'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '11',	'type_key' => 'k2T9lWKxgLYNGBeevs3GWKGn6u1hw01t',	'code' => 'qa_changed_status_notification',		'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '12',	'type_key' => 'NGBeevYu1hw01ts3GWKGn6k2T9lWKxgL',	'code' => 'thematic_consultation_notification',	'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '13',	'type_key' => 'tYeu1WT9Awjvn9MH5G8buO6541pV6I7F',	'code' => 'generic_cb_notifications',			'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '14',	'type_key' => 'H5G8buO6541pV6I7FXX1OtYeu1WT9Awj',   'code' => 'generic_entity_notifications',		'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '15',	'type_key' => '9Awjvn9MHwnZ41pV6I7FXX5Gzkd1OtYe',   'code' => 'technical_analysis_notification',	'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null)

        );
        DB::table('types')->insert($types);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('types');
    }
}
