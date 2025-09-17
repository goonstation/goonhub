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
        Schema::table('player_notes', function (Blueprint $table) {
            $table->integer('server_group')->nullable();

            $table->foreign('server_group')->references('id')->on('game_server_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_notes', function (Blueprint $table) {
            $table->dropForeign('server_group');
            $table->dropColumn('server_group');
        });
    }
};
