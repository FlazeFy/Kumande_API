<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firebase_id',28)->unique();
            $table->integer('budget_total')->length(10)->unique();
            $table->longText('budget_month_year');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('over_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget');
    }
};
