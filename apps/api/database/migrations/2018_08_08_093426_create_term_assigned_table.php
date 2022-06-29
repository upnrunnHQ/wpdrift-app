<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermAssignedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_assigned', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('object_id', 20);
            $table->bigInteger('term_taxonomy_id', 20);
            $table->integer('term_order', 11);
            $table->foreign(['store_id', 'term_taxonomy_id'])->references(['store_id', 'term_taxonomy_id'])->on('term_taxonomy');
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
        Schema::dropIfExists('term_assigned');
    }
}
