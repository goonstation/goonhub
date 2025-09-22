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
        Schema::table('game_build_secrets', function (Blueprint $table) {
            $table->foreignId('server_id')->nullable()->constrained('game_servers')->nullOnDelete();
            $table->foreignId('server_group_id')->nullable()->constrained('game_server_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_build_secrets', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropForeign(['server_group_id']);
            $table->dropColumn(['server_id', 'server_group_id']);
        });
    }
};
