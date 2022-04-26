<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkingImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulking_images', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('token');
            $table->text('url');
            $table->bigInteger('bulking_id');
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
        Schema::dropIfExists('bulking_images');
    }
}
