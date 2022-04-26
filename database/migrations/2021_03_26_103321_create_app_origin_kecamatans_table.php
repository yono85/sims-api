<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppOriginKecamatansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_origin_kecamatans', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('city_id');
            $table->string('name');
            $table->string('type');
            $table->string('type_label');
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
        Schema::dropIfExists('app_origin_kecamatans');
    }
}
