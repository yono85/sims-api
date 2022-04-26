<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('progress');
            $table->string('progress_done');
            $table->string('label');
            $table->text('text');
            $table->text('text_code');
            $table->string('start_date');
            $table->string('end_date');
            $table->bigInteger('verify_id');
            $table->integer('verify_status');
            $table->string('verify_date');
            $table->bigInteger('user_id');
            $table->timestamps();
            $table->string('date');
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
        Schema::dropIfExists('tasks');
    }
}
