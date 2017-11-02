<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropGenericEmailTemplateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('generic_email_template_tags');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('generic_email_template_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('generic_email_template_id')->unsigned();
            $table->string('tag');

            $table->timestamps();
            $table->softDeletes();
        });
    }
}
