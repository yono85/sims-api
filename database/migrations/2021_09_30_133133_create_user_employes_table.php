<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserEmployesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_employes', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('level'); //0=atuhor, 1 = owner, dir
            $table->integer('sublevel');
            $table->integer('groups');
            $table->string('joins');
            $table->string('leaves');
            $table->string('name');
            $table->string('nick');
            $table->integer('gender');
            $table->string('birth');
            $table->string('place_birth');
            $table->string('religion');
            $table->integer('last_education');
            $table->integer('relationship');
            $table->text('address');
            $table->integer('kodepos');
            $table->integer('kecamatan');
            $table->integer('city');
            $table->integer('provinsi');
            $table->string('phone');
            $table->string('email');
            $table->bigInteger('user_id');
            $table->bigInteger('project_id');
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
        Schema::dropIfExists('user_employes');
    }
}
