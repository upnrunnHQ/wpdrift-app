<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEddUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eddusers', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('ID', 20);
            $table->unique(['store_id', 'ID']);
            $table->string('user_login', 100);
            $table->string('user_pass', 100);
            $table->string('user_nicename', 100);
            $table->string('user_email', 100);
            $table->string('user_url', 200);
            $table->dateTime('user_registered');
            $table->string('user_activation_key', 200);
            $table->integer('user_status', 3);
            $table->string('display_name', 200);
            $table->foreign(['store_id', 'ID'])->references(['store_id', 'user_id'])->on('eddusers_metas');
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
        Schema::dropIfExists('eddusers');
    }
}
