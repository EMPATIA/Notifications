<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->string('sender_email')->after('sent')->nullable();
            $table->string('sender_name')->after('sender_email')->nullable();
            $table->unsignedInteger('newsletter_id')->after('entity_key')->default(0);
            $table->unsignedInteger('errors')->after('content')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
