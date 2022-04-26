<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumables', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->string('name');
            $table->string('code');
            $table->integer('type');
            $table->integer('quantity');
            $table->integer('quantity_limit');
            $table->string('expired_date');
            $table->integer('expired_status');
            $table->integer('reminder_duration');
            $table->text('description');
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
        Schema::dropIfExists('consumables');
    }
}
