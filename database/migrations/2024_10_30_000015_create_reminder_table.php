<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reminder', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firebase_id',28)->unique();
            $table->string('reminder_name',75);
            $table->string('reminder_type',36);
            $table->longText('reminder_context');
            $table->string('reminder_body',255);
            $table->longText('reminder_attachment')->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminder');
    }
};
