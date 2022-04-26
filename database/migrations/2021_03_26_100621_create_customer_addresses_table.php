<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->bigInteger('customer_id');
            $table->string('label');
            $table->string('name');
            $table->string('phone');
            $table->integer('provinsi');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->string('address');
            $table->integer('kodepos');
            $table->integer('keep');
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
        Schema::dropIfExists('customer_addresses');
    }
}
