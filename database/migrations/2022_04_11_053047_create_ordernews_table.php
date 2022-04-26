<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdernewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordernews', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->string('code');
            $table->bigInteger('poid');
            $table->string('search');
            $table->text('content_po');
            $table->integer('progress');
            $table->timestamps();
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ordernews');
    }
}
