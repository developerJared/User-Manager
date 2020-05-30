<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersGroupsComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_groups_comments', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->integer('users_id');
            $table->integer('groups_id');
            $table->integer('test_id')->index('test_id');
            $table->json('comments');
            $table->timestamps();
            $table->string('updated_by', 45)->nullable();
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
