<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('firebase_id',28)->unique();
            $table->string('telegram_user_id', 36)->nullable()->unique();
            $table->boolean('telegram_is_valid');
            $table->string('firebase_fcm_token', 255)->nullable()->unique();
            $table->string('line_user_id', 144)->nullable()->unique();
            $table->string('fullname', 50);
            $table->string('username', 36);
            $table->string('email', 255)->unique();
            $table->string('password', 500);
            $table->string('gender', 6);
            $table->string('image_url', 500)->nullable();
            $table->string('timezone', 9)->nullable();
            $table->date('born_at', $precision = 0)->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('deleted_at', $precision = 0)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user');
    }
};
