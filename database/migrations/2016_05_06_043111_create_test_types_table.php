<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_types', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name')->nullable();
            $table->string('shortname')->nullable();
            $table->string('longname')->nullable();
            $table->string('crop')->nullable();
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
		Schema::drop('test_types');
	}

}
