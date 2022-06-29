<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned();
            // oauth server - client information for REST API calls
            $table->string('auth_client_id')->nullable();
            $table->string('auth_client_secret')->nullable();
            $table->string('auth_server_url')->nullable();
            // Also save the infomation for Retrieved token
            $table->text('companies_store_credentials')->nullable();
            // References
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
