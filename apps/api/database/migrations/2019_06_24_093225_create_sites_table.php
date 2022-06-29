<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create(
			'sites',
			function ( Blueprint $table ) {
				$table->increments( 'id' );
				$table->bigInteger( 'site_id' );
				$table->string( 'site_name', 100 );
				$table->longText( 'site_description' );
				$table->string( 'site_url', 255 );
				$table->string( 'site_logo', 255 );
				$table->string( 'site_status', 100 );
				$table->date( 'site_last_synced' );
				$table->timestamps();
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'sites' );
	}
}
