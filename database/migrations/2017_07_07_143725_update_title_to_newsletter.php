<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTitleToNewsletter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('newsletters', function (Blueprint $table) {
            if (Schema::hasColumn('newsletters', 'title')) {
                $table->dropColumn('title');
            }
        });

        Schema::table('newsletters', function (Blueprint $table) {
            if (!Schema::hasColumn('newsletters', 'title')) {
                $table->string('title')->nullable()->after('newsletter_key');
            }
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
