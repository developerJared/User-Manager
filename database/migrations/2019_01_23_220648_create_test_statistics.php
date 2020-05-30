<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_statistics', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->string('legacy_staff_type_id', 15);
            $table->string('sample_number', 45);
            $table->date('sample_date');
            $table->integer('test_id')->index('test_id');
            $table->integer('tray')->nullable();
            $table->integer('tray_size')->nullable();
            $table->timestamp('scan_time')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('avg_seconds')->nullable();
            $table->timestamps();

            $table->unique([
                'sample_number',
                'test_id',
                'tray'
            ], 'UniqueSampleTestTray');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_statistics');
    }
}
