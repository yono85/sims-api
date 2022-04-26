<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_companies', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->integer('produsen_id');
            $table->string('name');
            $table->string('nosurat');
            $table->text('address');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->integer('kodepos');
            $table->text('contact');
            $table->string('owner');
            $table->text('owner_contact');
            $table->integer('order_uniqnum');
            $table->integer('expire_payment');
            $table->bigInteger('user_id');
            $table->string('verify');
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
        Schema::dropIfExists('user_companies');
    }
}
