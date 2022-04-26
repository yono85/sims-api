<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_companies', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->integer('type');
            $table->string('name');
            $table->string('file');
            $table->string('url');
            $table->string('path');
            $table->string('expired_date');
            $table->integer('reminder_duration');
            $table->bigInteger('reminder_id');
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
        Schema::dropIfExists('document_companies');
    }
}
