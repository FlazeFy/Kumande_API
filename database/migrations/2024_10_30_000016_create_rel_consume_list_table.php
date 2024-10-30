<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rel_consume_list', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('consume_id',36);
            $table->string('list_id',36);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('consume_id')->references('id')->on('consume')->onDelete('cascade');
            $table->foreign('list_id')->references('id')->on('consume_list')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rel_consume_list');
    }
};
