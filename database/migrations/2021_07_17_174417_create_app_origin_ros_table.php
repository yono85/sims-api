<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppOriginRosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_origin_ros', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('provinsi_code');
            $table->string('provinsi_name');
            $table->string('city_code');
            $table->string('city_name');
            $table->string('district_code');
            $table->string('district_name');
            $table->string('branch_code');
            $table->integer('origin_city');
            $table->integer('origin_kecamatan');
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
        Schema::dropIfExists('app_origin_ros');
    }
}
