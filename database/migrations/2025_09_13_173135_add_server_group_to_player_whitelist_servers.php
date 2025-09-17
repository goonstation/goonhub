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
        Schema::table('player_whitelist_servers', function (Blueprint $table) {
            $table->integer('server_id')->nullable()->change();
            $table->integer('server_group_id')->nullable();

            $table->foreign('server_group_id')->references('id')->on('game_server_groups')->nullOnDelete();

            $table->dropUnique(['player_whitelist_id', 'server_id']);
            $table->unique(['player_whitelist_id', 'server_id', 'server_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_whitelist_servers', function (Blueprint $table) {
            $table->integer('server_id')->nullable(false)->change();
            $table->dropForeign('server_group_id');
            $table->dropColumn('server_group_id');

            $table->dropUnique(['player_whitelist_id', 'server_id', 'server_group_id']);
            $table->unique(['player_whitelist_id', 'server_id']);
        });
    }
};
