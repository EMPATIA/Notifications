<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class   CreateEmailTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_template_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('email_template_id')->unsigned();
            $table->string('language_code');
            $table->string('subject');
            $table->text('header');
            $table->text('content');
            $table->text('footer');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('email_template_translations');
    }
}
