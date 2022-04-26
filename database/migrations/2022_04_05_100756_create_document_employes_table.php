<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentEmployesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_employes', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->integer('subtype');
            $table->bigInteger('employe_id');
            $table->string('file');
            $table->string('url');
            $table->string('path');
            $table->string('expired_date');
            $table->integer('reminder_duration');
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
        Schema::dropIfExists('document_employes');
    }
}
