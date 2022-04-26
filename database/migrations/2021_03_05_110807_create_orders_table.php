<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('type');
            $table->integer('uniq');
            $table->string('token');
            $table->string('invoice');
            $table->bigInteger('customer_id');
            $table->bigInteger('promo_id');
            $table->text('search');
            $table->text('field');
            $table->integer('checkout');
            $table->integer('payment');
            $table->integer('verify');
            $table->integer('paid');
            $table->text('notes');
            $table->bigInteger('user_id');
            $table->bigInteger('company_id');
            $table->string('expire_payment_date');
            $table->integer('bulking');
            $table->integer('bulking_keep');
            $table->integer('bulking_paid');
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
        Schema::dropIfExists('orders');
    }
}
