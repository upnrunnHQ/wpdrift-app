<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDownloadsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads_logs', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('ID', 20);
            $table->unique(['store_id', 'ID']);
            $table->string('type', 200);
            $table->bigInteger('user_id', 20);
            $table->string('user_ip', 200);
            $table->string('user_agent', 200);
            $table->bigInteger('download_id', 20);
            $table->bigInteger('version_id', 20);
            $table->string('version', 200);
            $table->dateTime('download_date');
            $table->string('download_status', 200)->nullable();
            $table->string('download_status_message', 200)->nullable();
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
        Schema::dropIfExists('downloads_logs');
    }
}
