<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShipingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_shipings', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->string('code');
            $table->bigInteger('order_id');
            $table->integer('courier_id');
            $table->string('courier_name');
            $table->string('courier_service');
            $table->integer('courier_weight');
            $table->integer('courier_price');
            $table->integer('cod');
            $table->integer('origin_id');
            $table->integer('origin_company_id');
            $table->bigInteger('destination_id');
            $table->string('noresi');
            $table->integer('print_status');
            $table->integer('pickup_status');
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
        Schema::dropIfExists('order_shipings');
    }
}
