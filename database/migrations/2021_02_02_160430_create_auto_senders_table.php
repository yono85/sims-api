<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoSendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_senders', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->integer('sub_type');
            $table->integer('sender_type');
            $table->integer('sender_id');
            $table->text('template');
            $table->text('template_body');
            $table->text('info');
            $table->bigInteger('user_id');
            $table->timestamps();
            $table->integer('sender_email');
            $table->text('status_email');
            $table->integer('sender_wa');
            $table->text('status_wa');
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
        Schema::dropIfExists('auto_senders');
    }
}
