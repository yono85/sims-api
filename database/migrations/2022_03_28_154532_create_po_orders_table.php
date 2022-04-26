<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_orders', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->string('code');
            $table->string('name');
            $table->integer('customer_id');
            $table->integer('price');
            $table->text('address');
            $table->string('startdate');
            $table->string('enddate');
            $table->text('sdm');
            $table->integer('sdm_status');
            $table->text('tools');
            $table->integer('tools_status');
            $table->integer('progress');
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
        Schema::dropIfExists('po_orders');
    }
}
