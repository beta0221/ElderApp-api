<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadEventBodyImageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_event_body_image_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug');
            $table->string('imageUrl');
            $table->string('imagePath');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_event_body_image_logs');
    }
}
