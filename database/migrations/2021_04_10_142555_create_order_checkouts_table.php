<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_checkouts', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('order_id');
            $table->integer('total');
            $table->integer('total_reseller');
            $table->integer('bayar');
            $table->integer('bayar_reseller');
            $table->integer('payment_type');
            $table->integer('payment_id');
            $table->string('payment_date');
            $table->integer('bank_id');
            $table->string('bank_user');
            $table->string('bank_norek');
            $table->string('bank_date');
            $table->integer('payment_total');
            $table->string('paid_date');
            $table->bigInteger('paid_user_id');
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
        Schema::dropIfExists('order_checkouts');
    }
}
