<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sms_key')->unique();
            $table->string('module_token');
            $table->boolean('sent')->default(0);
            $table->string('recipient');
            $table->text('content');
            $table->string('site_key');
            $table->string('created_by');
            $table->string('updated_by');
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
        Schema::dropIfExists('sms');
    }
}
