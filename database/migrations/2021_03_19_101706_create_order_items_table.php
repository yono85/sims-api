<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('order_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->integer('weight');
            $table->integer('weight_total');
            $table->integer('price');
            $table->integer('price_reseller');
            $table->integer('price_total');
            $table->integer('price_total_reseller');
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
        Schema::dropIfExists('order_items');
    }
}
