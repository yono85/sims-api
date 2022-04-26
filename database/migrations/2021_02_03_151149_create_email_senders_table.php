<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailSendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_senders', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('host');
            $table->string('tls');
            $table->integer('port');
            $table->string('email');
            $table->string('user');
            $table->string('password');
            $table->string('label');
            $table->integer('user_id');
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
        Schema::dropIfExists('email_senders');
    }
}
