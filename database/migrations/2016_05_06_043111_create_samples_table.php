<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSamplesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('samples', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('sample_number', 45);
			$table->string('status', 45)->index('status');
			$table->string('variety', 45)->nullable();
			$table->string('croptype', 150)->nullable();
			$table->string('itemcount', 15)->nullable();
			$table->string('samplesource', 45)->nullable();
			$table->string('area', 45)->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('samples');
	}

}
