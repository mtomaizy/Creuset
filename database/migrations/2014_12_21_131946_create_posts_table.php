<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
			$table->text('content');
			$table->string('slug')->unique();
			$table->string('type', 20)->default('post');
			$table->string('status', 20)->default('published');
			$table->integer('user_id')->unsigned();
			$table->integer('post_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('posts', function($table)
		{
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('post_id')->references('id')->on('posts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('posts', function($table)
		{
			$table->dropForeign('posts_user_id_foreign');
			$table->dropForeign('posts_post_id_foreign');
		});
		Schema::drop('posts');
	}

}
