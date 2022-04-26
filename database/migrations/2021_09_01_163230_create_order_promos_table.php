<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_promos', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('type'); //1. produk
            $table->bigInteger('promo_id');
            $table->bigInteger('order_id');
            $table->text('field');
            $table->integer('total');
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
        Schema::dropIfExists('order_promos');
    }
}
