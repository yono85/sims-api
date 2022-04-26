<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type'); //1 SDM, 2. Tools
            $table->integer('level');
            $table->bigInteger('from_id');
            $table->bigInteger('to_id');
            $table->text('content');
            $table->integer('open');
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
        Schema::dropIfExists('notifications');
    }
}
