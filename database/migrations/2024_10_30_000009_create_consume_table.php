<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consume', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firebase_id',28)->unique();
            $table->string('slug_name',85)->unique();
            $table->string('consume_name',75);
            $table->string('consume_type',10);
            $table->string('consume_from',15);
            $table->string('consume_comment',255)->nullable();
            $table->longText('consume_detail');
            $table->longText('consume_tag')->nullable();
            $table->boolean('is_favorite');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('deleted_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consume');
    }
};
