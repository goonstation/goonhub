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
        Schema::create('game_build_settings', function (Blueprint $table) {
            $table->id();
            $table->text('server_id');
            $table->text('branch')->default('master');
            $table->smallInteger('byond_major');
            $table->smallInteger('byond_minor');
            $table->text('rustg_version');
            $table->boolean('rp_mode')->default(false);
            $table->text('map_id')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('server_id')->on('game_servers');
            $table->foreign('map_id')->references('map_id')->on('maps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_build_settings');
    }
};
