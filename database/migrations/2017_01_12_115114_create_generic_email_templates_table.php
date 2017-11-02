<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGenericEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generic_email_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('generic_email_template_key')->unique();
            $table->integer('type_id')->integer();

            $table->timestamps();
            $table->softDeletes();
        });

        $genericEmailTemplates = array(
            array('id' => '1',	'generic_email_template_key' => 'XyNfzacDP3xwVelzWEuUTKn4vTCXZ2',   'type_id' => 1,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '2',	'generic_email_template_key' => 'XA8X0ZaqGt6BdbgsJ7zuFjApOrWJnb',   'type_id' => 2,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '3',	'generic_email_template_key' => 'sazhQ1Q9apwGNl160HjX1mr0bWfPAi',   'type_id' => 3,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '4',	'generic_email_template_key' => 'YU8B0Z4HDD1pF8Q4C7TEuRLo7cCbam',   'type_id' => 4,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '5',	'generic_email_template_key' => 'qXipqOsvoNkiYEbu38NYvBimtkWFXR',   'type_id' => 5,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '6',	'generic_email_template_key' => 'KVpxJO6GcMqEkN2VEvnMcsHukjRYhV',   'type_id' => 6,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '7',	'generic_email_template_key' => 'apCGb4UhGfZ6KOksRgE537cYl6l7PF',   'type_id' => 7,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '8',	'generic_email_template_key' => 'oDrUbda2E685JMRCPz1f1qaSVvEgNb',   'type_id' => 8,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '9',	'generic_email_template_key' => 'Pc2kHfc1paYr5M0FenYx6rxTr5qSzB',   'type_id' => 9,     'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '10',	'generic_email_template_key' => 'b5i6VajzK6Sweqnyoin1I3uRW0mIL6',   'type_id' => 10,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '11',	'generic_email_template_key' => 'Otmki17kNBTK10LpZzutzlxTBbQB0H',   'type_id' => 11,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '12',	'generic_email_template_key' => 'TT1wUJyBawMyIOKygzHfW2YughQZ1G',   'type_id' => 12,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '13',	'generic_email_template_key' => 'hqhu7uRUw366UI12Z3MXOoVTLE7Bwy',   'type_id' => 13,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '14',	'generic_email_template_key' => 'WcagsG4gAwGHeljtsqWGoAz29hnJfv',   'type_id' => 14,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null),
            array('id' => '15',	'generic_email_template_key' => 'LAmict9RUyoJ1GEfFshe5ETpOCQp9O',   'type_id' => 15,    'created_at' => Carbon::now(),	'updated_at' => Carbon::now(), 'deleted_at' => null)

        );
        DB::table('generic_email_templates')->insert($genericEmailTemplates);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generic_email_templates');
    }
}
