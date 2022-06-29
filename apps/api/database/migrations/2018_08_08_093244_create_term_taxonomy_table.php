<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermTaxonomyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_taxonomy', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('store_id');
            $table->bigInteger('term_taxonomy_id', 20);
            $table->unique(['store_id', 'term_taxonomy_id']);
            $table->bigInteger('term_id', 20);
            $table->string('name', 200);
            $table->string('slug', 200);
            $table->string('taxonomy', 32);
            $table->longText('description');
            $table->bigInteger('parent', 20);
            $table->bigInteger('count', 20);
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
        Schema::dropIfExists('term_taxonomy');
    }
}
