<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firebase_id',28)->unique();
            $table->string('consume_id',36);
            $table->string('payment_method',20);
            $table->integer('payment_price')->length(9);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('consume_id')->references('id')->on('consume')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment');
    }
};
