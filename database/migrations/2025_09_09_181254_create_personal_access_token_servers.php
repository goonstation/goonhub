<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_token_servers', function (Blueprint $table) {
            $table->id();
            $table->integer('personal_access_token_id');
            $table->integer('server_id')->nullable();
            $table->integer('server_group_id')->nullable();
            $table->timestamps();

            $table->foreign('personal_access_token_id')->references('id')->on('personal_access_tokens')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('game_servers')->onDelete('cascade');
            $table->foreign('server_group_id')->references('id')->on('game_server_groups')->onDelete('cascade');

            $table->unique(['personal_access_token_id', 'server_id', 'server_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_token_servers');
    }
};
