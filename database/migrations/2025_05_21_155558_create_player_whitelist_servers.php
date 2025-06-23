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
        Schema::create('player_whitelist_servers', function (Blueprint $table) {
            $table->id();
            $table->integer('player_whitelist_id');
            $table->integer('server_id');
            $table->timestamps();

            $table->foreign('player_whitelist_id')->references('id')->on('player_whitelist')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('game_servers')->onDelete('cascade');

            $table->unique(['player_whitelist_id', 'server_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_whitelist_servers');
    }
};
