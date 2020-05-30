<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('nl_name', 100);
			$table->string('firstName', 45);
			$table->string('lastName', 45);
			$table->string('address', 45)->nullable();
			$table->string('phone', 25)->nullable();
			$table->string('mobile', 25)->nullable();
			$table->string('username', 25);
			$table->string('password');
			$table->string('pin', 100)->nullable();
			$table->string('current_lab', 45)->nullable();
			$table->integer('roles_id')->default(1);
			$table->integer('active')->default(1);
			$table->timestamps();
			$table->string('legacy_staff_type_id', 15)->nullable();
			$table->integer('legacy_user_id')->nullable();
			$table->integer('legacy_staff_id')->nullable();
			$table->tinyInteger('administrator');
			$table->tinyInteger('admin_enabled');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
