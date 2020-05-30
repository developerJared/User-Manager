<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContainersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('containers', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('barcode', 25)->nullable()->index('barcode');
			$table->decimal('weight', 6, 3)->nullable()->index('weight');
			$table->string('nl_id', 60)->nullable();
			$table->string('containerType', 45)->nullable()->index('containerType');
			$table->timestamps();
			$table->unique(['barcode','containerType'], 'unique_index');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('containers');
	}

}
