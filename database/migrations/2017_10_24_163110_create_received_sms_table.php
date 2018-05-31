<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivedSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {  
        Schema::create('received_sms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('received_sms_key')->unique();

            $table->string("entity_key");
            $table->string("site_key");
            $table->text('content');
            $table->string("sender");
            $table->string("receiver");
            $table->string("event");

            $table->boolean('processed')->default(0);
            $table->text('answer')->nullable();
            $table->longText('logs')->nullable();

            $table->text("service_sms_identifier")->nullable();
            $table->string("service_sms_date")->nullable();

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
        Schema::dropIfExists('received_sms');
    }
}
