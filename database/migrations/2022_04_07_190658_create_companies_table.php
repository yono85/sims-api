<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->string('name');
            $table->text('contact');
            $table->text('owner');
            $table->text('address');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->integer('kodepos');
            $table->string('user_id');
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
        Schema::dropIfExists('companies');
    }
}
