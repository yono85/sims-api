<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_configs', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('type');
            $table->bigInteger('user_id');
            $table->integer('company_id');
            $table->string('homepage');
            $table->integer('aside_id');
            $table->text('aside_menu');
            $table->bigInteger('admin_id');
            $table->integer('terms');
            $table->string('terms_date');
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
        Schema::dropIfExists('user_configs');
    }
}
