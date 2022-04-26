<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_notifications', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->bigInteger('to_id');
            $table->bigInteger('from_id');
            $table->integer('groups');
            $table->string('label');
            $table->text('content');
            $table->string('link');
            $table->integer('open');
            $table->string('open_date');
            $table->integer('read_status');
            $table->string('read_date');
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
        Schema::dropIfExists('home_notifications');
    }
}
