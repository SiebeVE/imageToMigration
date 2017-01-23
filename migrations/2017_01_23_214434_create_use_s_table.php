<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUse_sTable extends Migration{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('use_s', function (Blueprint $table) {
			$table->increments("id");
			$table->string("email_va_r_char");
			$table->string("password");
			$table->string("username");
			$table->string("is_admin_boo_lean");

		});
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('use_s');
	}
}