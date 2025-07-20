<?php

use App\Models\LinkedDiscordUser;
use App\Models\User;
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
        Schema::create('linked_discord_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('discord_id')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        $users = User::whereNotNull('discord_id')->get();
        foreach ($users as $user) {
            LinkedDiscordUser::create([
                'user_id' => $user->id,
                'discord_id' => $user->discord_id,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('discord_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linked_discord_users');

        Schema::table('users', function (Blueprint $table) {
            $table->text('discord_id')->nullable();
        });
    }
};
