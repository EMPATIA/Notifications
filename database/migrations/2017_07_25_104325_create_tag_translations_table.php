<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->text("description");

            $table->string("language_code");
            $table->integer('tag_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();
        });

        $tagTranslations = array(
            array(
                'id' => 1,
                'name' => 'User name',
                'description' => 'The name of the User that will receive the confirmation email',
                'language_code' => 'en',
                'tag_id' => 1,
            ),
            array(
                'id' => 2,
                'name' => 'Email Confirmation link',
                'description' => 'The link to the email confirmation',
                'language_code' => 'en',
                'tag_id' => 2,
            ),
            array(
                'id' => 3,
                'name' => 'User name',
                'description' => 'The name of the User that will receive the email confirming the registration',
                'language_code' => 'en',
                'tag_id' => 3,
            ),
            array(
                'id' => 4,
                'name' => 'Platform link',
                'description' => 'The link to the platform',
                'language_code' => 'en',
                'tag_id' => 4,
            ),
            array(
                'id' => 5,
                'name' => 'User name',
                'description' => 'The name of the User that will receive the votes confirmation email',
                'language_code' => 'en',
                'tag_id' => 5,
            ),
            array(
                'id' => 6,
                'name' => 'Platform link',
                'description' => 'The link to the platform',
                'language_code' => 'en',
                'tag_id' => 6,
            ),
            array(
                'id' => 7,
                'name' => 'Votes count',
                'description' => 'Count of the submitted votes',
                'language_code' => 'en',
                'tag_id' => 7,
            ),
            array(
                'id' => 8,
                'name' => 'Votes list',
                'description' => 'List of the submitted votes',
                'language_code' => 'en',
                'tag_id' => 8,
            ),
            array(
                'id' => 9,
                'name' => 'Votes Unique ID',
                'description' => 'Unique ID of the submitted votes',
                'language_code' => 'en',
                'tag_id' => 9,
            ),
            array(
                'id' => 10,
                'name' => 'User name',
                'description' => 'The name of the User that will receive the email confirming to recover the password',
                'language_code' => 'en',
                'tag_id' => 10,
            ),
            array(
                'id' => 11,
                'name' => 'Platform link',
                'description' => 'The link to the password recovery form',
                'language_code' => 'en',
                'tag_id' => 11,
            ),
            array(
                'id' => 12,
                'name' => 'User name',
                'description' => 'The name of the User that will receive the email confirming the password recovery',
                'language_code' => 'en',
                'tag_id' => 12,
            ),
            array(
                'id' => 13,
                'name' => 'Platform link',
                'description' => 'The link to the platform',
                'language_code' => 'en',
                'tag_id' => 13,
            ),
            array(
                'id' => 14,
                'name' => 'Topic Title',
                'description' => 'Title of the topic that had the status updated',
                'language_code' => 'en',
                'tag_id' => 14,
            ),
            array(
                'id' => 15,
                'name' => 'Topic Link',
                'description' => 'Link to the topic that had the status updated',
                'language_code' => 'en',
                'tag_id' => 15,
            ),
            array(
                'id' => 16,
                'name' => 'CB Link',
                'description' => 'Link to the CB of the topic that had the status updated',
                'language_code' => 'en',
                'tag_id' => 16,
            ),
            array(
                'id' => 17,
                'name' => 'User Name',
                'description' => 'Name of the user that sent the Message',
                'language_code' => 'en',
                'tag_id' => 17,
            ),
            array(
                'id' => 18,
                'name' => 'Message content',
                'description' => 'Content of the sent message',
                'language_code' => 'en',
                'tag_id' => 18,
            ),
            array(
                'id' => 19,
                'name' => 'Entity name',
                'description' => 'Name of the Entity that sent the message',
                'language_code' => 'en',
                'tag_id' => 19,
            ),
            array(
                'id' => 20,
                'name' => 'Platform link',
                'description' => 'The link to the platform',
                'language_code' => 'en',
                'tag_id' => 20,
            ),
            array(
                'id' => 21,
                'name' => 'Message content',
                'description' => 'Content of the sent message',
                'language_code' => 'en',
                'tag_id' => 21,
            ),
            array(
                'id' => 22,
                'name' => 'Entity name',
                'description' => 'Name of the Entity that sent the message',
                'language_code' => 'en',
                'tag_id' => 22,
            ),
            array(
                'id' => 23,
                'name' => 'User Name',
                'description' => 'Name of the user that sent the Message',
                'language_code' => 'en',
                'tag_id' => 23,
            ),
            array(
                'id' => 24,
                'name' => 'Platform link',
                'description' => 'The link to the platform',
                'language_code' => 'en',
                'tag_id' => 24,
            ),
        );

        DB::table('tag_translations')->insert($tagTranslations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_translations');
    }
}
