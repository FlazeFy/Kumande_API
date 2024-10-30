<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('personal_access_tokens'); // for prevent old personal access token conflict
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tokenable_type', 255);
            $table->string('tokenable_id', 36);
            $table->string('name', 255);
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();

            // Props
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // References
            $table->foreign('tokenable_id')->references('id')->on('user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
