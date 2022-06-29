<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_metas', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('meta_id', 20);
            $table->unique(['store_id', 'meta_id']);
            $table->bigInteger('customer_id', 20);
            $table->string('meta_key', 255)->nullable();
            $table->longText('meta_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers_metas');
    }
}
