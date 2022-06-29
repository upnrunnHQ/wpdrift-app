<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEddstoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eddstores', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->unique('store_id');
            $table->string('store_url');
            $table->string('store_access_token');
            $table->timestamp('database_sync');
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
        Schema::dropIfExists('eddstores');
    }
}
