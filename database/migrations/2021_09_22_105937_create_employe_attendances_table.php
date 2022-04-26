<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employe_attendances', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('type'); //1 hadir
            $table->integer('employe_id');
            $table->integer('location_type');
            $table->string('checkin');
            $table->string('checkout');
            $table->integer('location_checkin');
            $table->integer('location_checkout');
            $table->integer('time_count');
            $table->integer('late');
            $table->text('field');
            $table->integer('info');
            $table->text('note');
            $table->integer('updated');
            $table->string('date');
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
        Schema::dropIfExists('employe_attendances');
    }
}
