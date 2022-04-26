<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppShipingOriginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_shiping_origins', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('label');
            $table->string('name');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->text('address');
            $table->integer('kodepos');
            $table->string('phone');
            $table->bigInteger('user_id');
            $table->bigInteger('company_id');
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
        Schema::dropIfExists('app_shiping_origins');
    }
}
