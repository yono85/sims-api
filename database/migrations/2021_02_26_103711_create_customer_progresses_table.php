<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProgressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_progresses', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('background');
            $table->string('color');
            $table->bigInteger('user_id');
            $table->bigInteger('companies_id');
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
        Schema::dropIfExists('customer_progresses');
    }
}
