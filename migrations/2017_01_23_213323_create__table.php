<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('', function (Blueprint $table) {
			$table->string("lid_imcrements");
			$table->string("emaill");
			$table->string("password");
			$table->string("username_var_crtar");
			$table->string("s_admin_boc_lean");

		});
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('');
	}
}