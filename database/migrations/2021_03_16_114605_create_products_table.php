<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('type');
            $table->string('token');
            $table->string('name');
            $table->text('description');
            $table->string('price');
            $table->string('price_discount');
            $table->string('discount');
            $table->string('price_reseller');
            $table->string('price_maklon');
            $table->string('weight');
            $table->string('weight_type');
            $table->integer('max');
            $table->bigInteger('user_id');
            $table->bigInteger('company_id');
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
        Schema::dropIfExists('products');
    }
}
