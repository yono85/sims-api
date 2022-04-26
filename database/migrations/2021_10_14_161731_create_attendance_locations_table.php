<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('token');
            $table->string('token_static');
            $table->string('token_dinamis');
            $table->string('name');
            $table->text('address');
            $table->string('kodepos');
            $table->integer('kecamatan');
            $table->integer('city');
            $table->integer('provinsi');
            $table->text('description');
            $table->text('field');
            $table->integer('diff_minute');
            $table->integer('reload_limit');
            $table->integer('reload_time'); //on minute
            $table->integer('company_id');
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
        Schema::dropIfExists('attendance_locations');
    }
}
