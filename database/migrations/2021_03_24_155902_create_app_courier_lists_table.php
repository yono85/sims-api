<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppCourierListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_courier_lists', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('type');
            $table->string('name');
            $table->string('weight_up');
            $table->string('weight_type');
            $table->string('cod_cost_percent');
            $table->integer('pickup');
            $table->string('description');
            $table->string('image');
            $table->integer('config_id');
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
        Schema::dropIfExists('app_courier_lists');
    }
}
