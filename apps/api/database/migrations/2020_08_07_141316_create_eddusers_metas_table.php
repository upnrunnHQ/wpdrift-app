<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEddUsersMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eddusers_metas', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('umeta_id', 20);
            $table->unique(['store_id', 'umeta_id']);
            $table->bigInteger('user_id', 20);
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
        Schema::dropIfExists('eddusers_metas');
    }
}
