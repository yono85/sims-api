<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->string('name');
            $table->string('owner');
            $table->string('phone');
            $table->string('email');
            $table->text('search');
            $table->text('address');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->integer('kodepos');
            $table->bigInteger('user_id');
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
        Schema::dropIfExists('customers');
    }
}
