<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_materials', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->string('name');
            $table->text('description');
            $table->integer('unit');
            $table->integer('quantity');
            $table->integer('qty_min');
            $table->integer('qty_max');
            $table->string('price_unit');
            $table->string('price_total');
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
        Schema::dropIfExists('inventory_materials');
    }
}
