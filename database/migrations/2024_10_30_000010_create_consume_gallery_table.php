<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consume_gallery', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('consume_id',36)->unique();
            $table->string('gallery_url',500);
            $table->string('gallery_desc',144)->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('consume_id')->references('id')->on('consume')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consume_gallery');
    }
};
