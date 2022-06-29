<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEddColumnsToStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->unsignedTinyInteger('has_edd_setup')->after('companies_store_credentials')->nullable()->default(0);
            $table->unsignedTinyInteger('edd_enabled')->after('companies_store_credentials')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('has_edd_setup');
            $table->dropColumn('edd_enabled');
        });
    }
}
