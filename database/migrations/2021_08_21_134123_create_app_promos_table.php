<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppPromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_promos', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('type'); // type promo 1 for product
            $table->string('item_id'); // kondidi 0 all
            $table->integer('min');
            $table->integer('multiple');
            $table->string('code'); //kode max 12 digit
            $table->string('token'); //
            $table->string('name'); //
            $table->text('description'); //
            $table->text('field'); //
            $table->bigInteger('company_id');
            $table->bigInteger('user_id');
            $table->string('start_date'); //
            $table->string('expire_date'); //
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
        Schema::dropIfExists('app_promos');
    }
}
