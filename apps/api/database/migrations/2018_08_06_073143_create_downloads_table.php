<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('post_id', 20);
            $table->unique(['store_id', 'post_id']);
            $table->string('post_author')->nullable();
            $table->dateTime('post_date')->nullable();
            $table->longText('post_content')->nullable();
            $table->text('post_title')->nullable();
            $table->string('post_status', 20)->nullable();
            $table->string('ping_status', 20)->nullable();
            $table->string('post_password', 255)->nullable();
            $table->string('post_name', 200)->nullable();
            $table->text('to_ping')->nullable();
            $table->text('pinged')->nullable();
            $table->dateTime('post_modified')->nullable();
            $table->longText('post_content_filtered')->nullable();
            $table->bigInteger('post_parent', 20)->nullable();
            $table->string('guid', 255)->nullable();
            $table->integer('menu_order', 11)->nullable();
            $table->bigInteger('comment_count', 20)->nullable();
            $table->foreign(['store_id','post_id'])->references(['store_id','post_id'])->on('downloads_metas');
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
        Schema::dropIfExists('downloads');
    }
}
