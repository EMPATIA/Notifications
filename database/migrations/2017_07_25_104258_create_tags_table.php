<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');

            $table->integer('type_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        $tags = array(
            array(
                'id' => 1,
                'code' => 'name',
                'type_id' => 1,
            ),
            array(
                'id' => 2,
                'code' => 'link',
                'type_id' => 1,
            ),
            array(
                'id' => 3,
                'code' => 'name',
                'type_id' => 2,
            ),
            array(
                'id' => 4,
                'code' => 'link',
                'type_id' => 2,
            ),
            array(
                'id' => 5,
                'code' => 'name',
                'type_id' => 3,
            ),
            array(
                'id' => 6,
                'code' => 'link',
                'type_id' => 3,
            ),
            array(
                'id' => 7,
                'code' => 'votesCount',
                'type_id' => 3,
            ),
            array(
                'id' => 8,
                'code' => 'voteList',
                'type_id' => 3,
            ),
            array(
                'id' => 9,
                'code' => 'uniqueID',
                'type_id' => 3,
            ),
            array(
                'id' => 10,
                'code' => 'name',
                'type_id' => 4,
            ),
            array(
                'id' => 11,
                'code' => 'link',
                'type_id' => 4,
            ),
            array(
                'id' => 12,
                'code' => 'name',
                'type_id' => 5,
            ),
            array(
                'id' => 13,
                'code' => 'link',
                'type_id' => 5,
            ),
            array(
                'id' => 14,
                'code' => 'topic_title',
                'type_id' => 6,
            ),
            array(
                'id' => 15,
                'code' => 'topic_link',
                'type_id' => 6,
            ),
            array(
                'id' => 16,
                'code' => 'cb_link',
                'type_id' => 6,
            ),
            array(
                'id' => 17,
                'code' => 'name',
                'type_id' => 8,
            ),
            array(
                'id' => 18,
                'code' => 'message',
                'type_id' => 8,
            ),
            array(
                'id' => 19,
                'code' => 'sender',
                'type_id' => 8,
            ),
            array(
                'id' => 20,
                'code' => 'link',
                'type_id' => 8,
            ),
            array(
                'id' => 21,
                'code' => 'message',
                'type_id' => 9,
            ),
            array(
                'id' => 22,
                'code' => 'sender',
                'type_id' => 9,
            ),
            array(
                'id' => 23,
                'code' => 'user_who_sent',
                'type_id' => 9,
            ),
            array(
                'id' => 24,
                'code' => 'link',
                'type_id' => 9,
            ),
        );

        DB::table('tags')->insert($tags);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
