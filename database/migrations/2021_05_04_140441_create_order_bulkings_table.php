<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderBulkingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_bulkings', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->string('invoice');
            $table->text('order_id'); //many order id for bulking
            $table->text('search');
            $table->text('field');
            $table->bigInteger('user_id'); //user bulking
            $table->string('payment_user'); //bank tujuan
            $table->string('payment_name');
            $table->string('payment_date');
            $table->integer('payment_total');
            $table->bigInteger('company_user');
            $table->integer('total_paid'); //total paid
            $table->integer('quantity'); //item order
            $table->bigInteger('company'); //produsen or maklon
            $table->string('payment_company'); //payment produsen
            $table->string('expire_payment_date');
            $table->integer('paid');
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
        Schema::dropIfExists('order_bulkings');
    }
}
