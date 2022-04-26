<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppCourierConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_courier_configs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('host');
            $table->text('sub_host');
            $table->string('key');
            $table->string('user');
            $table->string('password');
            $table->string('dir');
            $table->string('database_origin');
            $table->text('description');
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
        Schema::dropIfExists('app_courier_configs');
    }
}
