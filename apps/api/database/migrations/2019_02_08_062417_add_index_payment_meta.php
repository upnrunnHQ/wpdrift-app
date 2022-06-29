<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexPaymentMeta extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table(
			'payments_metas',
			function( Blueprint $table ) {
				$table->unique( [ 'store_id', 'post_id', 'meta_key' ] );
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table(
			'payments_metas',
			function( Blueprint $table ) {
				$table->dropUnique( [ 'store_id', 'post_id', 'meta_key' ] );
			}
		);
	}
}
