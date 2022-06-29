<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('id', 20);
            $table->unique(['store_id', 'id']);
            $table->string('email', 50);
            $table->mediumText('name');
            $table->mediumText('purchase_value');
            $table->bigInteger('purchase_count', 20);
            $table->longText('payment_ids');
            $table->longText('notes');
            $table->dateTime('date_created');
            $table->foreign(['store_id', 'id'])->references(['store_id', 'customer_id'])->on('customers_metas');
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
        Schema::dropIfExists('customers');
    }
}
