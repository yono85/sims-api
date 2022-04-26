<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->string('code');
            $table->string('name');
            $table->integer('type');
            $table->text('assesoris');
            $table->integer('quantity');
            $table->intger('kalibrasi_status');
            $table->string('kalibrasi_date');
            $table->text('description');
            $table->integer('reminder_day');
            $table->bigInteger('project_id');
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
        Schema::dropIfExists('assets');
    }
}
