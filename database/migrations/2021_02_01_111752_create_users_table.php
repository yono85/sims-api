<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->text('search');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('username');
            $table->integer('company_id');
            $table->integer('level');
            $table->integer('sub_level');
            $table->integer('gender');
            $table->string('phone');
            $table->string('phone_code');
            $table->integer('registers');
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
        Schema::dropIfExists('users');
    }
}
