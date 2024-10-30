<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rel_reminder_used', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reminder_id',36);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('reminder_id')->references('id')->on('reminder')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rel_reminder_used');
    }
};
