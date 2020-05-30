<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolesGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles_groups', function(Blueprint $table)
		{
			$table->integer('roles_id');
			$table->integer('groups_id')->index('fk_roles_groups_groups1_idx');
			$table->timestamps();
			$table->primary(['roles_id','groups_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('roles_groups');
	}

}
