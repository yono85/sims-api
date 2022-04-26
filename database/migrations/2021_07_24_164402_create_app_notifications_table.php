<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('level'); //0 enduser 1 admin
            $table->integer('type');
            $table->integer('subtype');
            $table->integer('to_type'); // if type 0 enduser, 1 to admin
            $table->bigInteger('to_id'); //level admin
            $table->integer('to_companyid');
            $table->bigInteger('from_id');
            $table->integer('from_companyid');
            $table->text('content');
            $table->integer('open');
            $table->string('open_date');
            $table->string('read_date');
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
        Schema::dropIfExists('app_notifications');
    }
}
