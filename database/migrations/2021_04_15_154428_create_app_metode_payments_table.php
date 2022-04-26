<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppMetodePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_metode_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('type');
            $table->integer('bank_id');
            $table->string('account_name');
            $table->string('account_norek');
            $table->text('description');
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
        Schema::dropIfExists('app_bank_payments');
    }
}
