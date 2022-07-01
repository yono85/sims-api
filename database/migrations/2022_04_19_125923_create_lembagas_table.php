<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLembagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lembagas', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->text('search'); //name, npwp, email
            $table->string('name');
            $table->string('kumham');
            $table->string('kumham_tgl');
            $table->string('npwp');
            $table->string('phone');
            $table->string('email');
            $table->text('owner');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->text('address');
            $table->text('field');
            $table->integer('complete');
            $table->integer('verify');
            $table->bigInteger('verify_user');
            $table->string('verify_date');
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
        Schema::dropIfExists('lembagas');
    }
}
