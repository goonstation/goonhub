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
        Schema::create('player_bypass_cap_servers', function (Blueprint $table) {
            $table->id();
            $table->integer('player_bypass_cap_id');
            $table->integer('server_id');
            $table->timestamps();

            $table->foreign('player_bypass_cap_id')->references('id')->on('player_bypass_cap')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('game_servers')->onDelete('cascade');

            $table->unique(['player_bypass_cap_id', 'server_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_bypass_cap_servers');
    }
};
