<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_admins', function (Blueprint $table) {
            $table->id();
            $table->integer('player_id')->unique();
            $table->integer('rank_id')->nullable();
            $table->text('alias')->nullable();
            $table->timestamps();

            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreign('rank_id')->references('id')->on('game_admin_ranks')->onDelete('cascade');
        });

        Schema::create('player_admin_servers', function (Blueprint $table) {
            $table->id();
            $table->integer('player_admin_id');
            $table->integer('server_id')->nullable();
            $table->integer('server_group_id')->nullable();
            $table->timestamps();

            $table->foreign('player_admin_id')->references('id')->on('player_admins')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('game_servers')->onDelete('cascade');
            $table->foreign('server_group_id')->references('id')->on('game_server_groups')->onDelete('cascade');

            $table->unique(['player_admin_id', 'server_id', 'server_group_id']);
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->dropForeign('bans_game_admin_id_foreign');
            $table->dropForeign('bans_deleted_by_foreign');
        });

        Schema::table('job_bans', function (Blueprint $table) {
            $table->dropForeign('job_bans_game_admin_id_foreign');
            $table->dropForeign('job_bans_deleted_by_foreign');
        });

        Schema::table('player_notes', function (Blueprint $table) {
            $table->dropForeign('player_notes_game_admin_id_foreign');
        });

        Schema::table('map_switches', function (Blueprint $table) {
            $table->dropForeign('map_switches_game_admin_id_foreign');
        });

        Schema::table('vpn_whitelist', function (Blueprint $table) {
            $table->dropForeign('vpn_whitelist_game_admin_id_foreign');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign('polls_game_admin_id_foreign');
        });

        Schema::table('remote_music_plays', function (Blueprint $table) {
            $table->dropForeign('remote_music_plays_game_admin_id_foreign');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->dropForeign('maps_last_built_by_foreign');
        });

        Schema::table('game_builds', function (Blueprint $table) {
            $table->dropForeign('game_builds_started_by_foreign');
            $table->dropForeign('game_builds_cancelled_by_foreign');
        });

        Schema::table('game_build_test_merges', function (Blueprint $table) {
            $table->dropForeign('game_build_test_merges_added_by_foreign');
            $table->dropForeign('game_build_test_merges_updated_by_foreign');
        });

        $gameAdmins = DB::table('game_admins')->get();

        $playerAdminGameAdminMap = collect();

        foreach ($gameAdmins as $gameAdmin) {
            $playerId = null;
            $user = DB::table('users')->where('game_admin_id', $gameAdmin->id)->first();

            if ($user) {
                $playerId = $user->player_id;
            } else {
                $playerId = DB::table('players')->where('ckey', $gameAdmin->ckey)->first()?->id;
            }

            if (! $playerId) {
                continue;
            }

            DB::table('player_admins')->insert([
                'player_id' => $playerId,
                'rank_id' => $gameAdmin->rank_id,
                'alias' => $gameAdmin->name,
                'created_at' => $gameAdmin->created_at,
                'updated_at' => $gameAdmin->updated_at,
            ]);

            $playerAdmin = DB::table('player_admins')->where('player_id', $playerId)->first();

            $playerAdminGameAdminMap->push([
                'player_admin' => $playerAdmin,
                'game_admin' => $gameAdmin,
            ]);
        }

        foreach ($playerAdminGameAdminMap as $mapping) {
            DB::table('bans')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('bans')->where('deleted_by', $mapping['game_admin']->id)->update(['deleted_by' => $mapping['player_admin']->id]);
            DB::table('job_bans')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('job_bans')->where('deleted_by', $mapping['game_admin']->id)->update(['deleted_by' => $mapping['player_admin']->id]);
            DB::table('player_notes')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('map_switches')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('vpn_whitelist')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('polls')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('remote_music_plays')->where('game_admin_id', $mapping['game_admin']->id)->update(['game_admin_id' => $mapping['player_admin']->id]);
            DB::table('maps')->where('last_built_by', $mapping['game_admin']->id)->update(['last_built_by' => $mapping['player_admin']->id]);
            DB::table('game_builds')->where('started_by', $mapping['game_admin']->id)->update(['started_by' => $mapping['player_admin']->id]);
            DB::table('game_builds')->where('cancelled_by', $mapping['game_admin']->id)->update(['cancelled_by' => $mapping['player_admin']->id]);
            DB::table('game_build_test_merges')->where('added_by', $mapping['game_admin']->id)->update(['added_by' => $mapping['player_admin']->id]);
            DB::table('game_build_test_merges')->where('updated_by', $mapping['game_admin']->id)->update(['updated_by' => $mapping['player_admin']->id]);

            DB::table('game_builds')->whereRaw(
                'EXISTS (SELECT 1 FROM json_array_elements(test_merges) WHERE value->>\'added_by\'::text = ? OR value->>\'updated_by\'::text = ?)',
                [(string) $mapping['game_admin']->id, (string) $mapping['game_admin']->id]
            )->get()->each(function ($gameBuild) use ($mapping) {
                $gameBuild->test_merges = json_encode(array_map(function ($testMerge) use ($mapping) {
                    if ($testMerge['added_by'] == $mapping['game_admin']->id) {
                        $testMerge['added_by'] = $mapping['player_admin']->id;
                    }
                    if ($testMerge['updated_by'] == $mapping['game_admin']->id) {
                        $testMerge['updated_by'] = $mapping['player_admin']->id;
                    }

                    return $testMerge;
                }, json_decode($gameBuild->test_merges, true)));
                DB::table('game_builds')->where('id', $gameBuild->id)->update(['test_merges' => $gameBuild->test_merges]);
            });
        }

        Schema::table('bans', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
            $table->foreign('deleted_by')->references('id')->on('player_admins');
        });

        Schema::table('job_bans', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
            $table->foreign('deleted_by')->references('id')->on('player_admins');
        });

        Schema::table('player_notes', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
        });

        Schema::table('map_switches', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
        });

        Schema::table('vpn_whitelist', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
        });

        Schema::table('remote_music_plays', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('player_admins');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->foreign('last_built_by')->references('id')->on('player_admins');
        });

        Schema::table('game_builds', function (Blueprint $table) {
            $table->foreign('started_by')->references('id')->on('player_admins');
            $table->foreign('cancelled_by')->references('id')->on('player_admins');
        });

        Schema::table('game_build_test_merges', function (Blueprint $table) {
            $table->foreign('added_by')->references('id')->on('player_admins');
            $table->foreign('updated_by')->references('id')->on('player_admins');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('game_admin_id');
        });

        Schema::dropIfExists('game_admins');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $playerAdmins = DB::table('player_admins')->get();
        $players = DB::table('players')->whereIn('id', $playerAdmins->pluck('player_id'))->get();

        Schema::create('game_admins', function (Blueprint $table) {
            $table->id();
            $table->text('ckey');
            $table->text('name')->nullable();
            $table->integer('rank_id')->nullable();
            $table->timestamps();

            $table->foreign('rank_id')->references('id')->on('game_admin_ranks');

            $table->unique('ckey');
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->dropForeign('bans_game_admin_id_foreign');
            $table->dropForeign('bans_deleted_by_foreign');
        });

        Schema::table('job_bans', function (Blueprint $table) {
            $table->dropForeign('job_bans_game_admin_id_foreign');
            $table->dropForeign('job_bans_deleted_by_foreign');
        });

        Schema::table('player_notes', function (Blueprint $table) {
            $table->dropForeign('player_notes_game_admin_id_foreign');
        });

        Schema::table('map_switches', function (Blueprint $table) {
            $table->dropForeign('map_switches_game_admin_id_foreign');
        });

        Schema::table('vpn_whitelist', function (Blueprint $table) {
            $table->dropForeign('vpn_whitelist_game_admin_id_foreign');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign('polls_game_admin_id_foreign');
        });

        Schema::table('remote_music_plays', function (Blueprint $table) {
            $table->dropForeign('remote_music_plays_game_admin_id_foreign');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->dropForeign('maps_last_built_by_foreign');
        });

        Schema::table('game_builds', function (Blueprint $table) {
            $table->dropForeign('game_builds_started_by_foreign');
            $table->dropForeign('game_builds_cancelled_by_foreign');
        });

        Schema::table('game_build_test_merges', function (Blueprint $table) {
            $table->dropForeign('game_build_test_merges_added_by_foreign');
            $table->dropForeign('game_build_test_merges_updated_by_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('game_admin_id')->nullable();
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        foreach ($playerAdmins as $playerAdmin) {
            $player = $players->firstWhere('id', $playerAdmin->player_id);

            DB::table('game_admins')->insert([
                'ckey' => $player->ckey,
                'name' => $playerAdmin->alias,
                'rank_id' => $playerAdmin->rank_id,
                'created_at' => $playerAdmin->created_at,
                'updated_at' => $playerAdmin->updated_at,
            ]);

            $gameAdmin = DB::table('game_admins')->where('ckey', $player->ckey)->first();

            DB::table('users')->where('player_id', $playerAdmin->player_id)->update([
                'game_admin_id' => $gameAdmin->id,
            ]);

            DB::table('bans')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('bans')->where('deleted_by', $playerAdmin->id)->update(['deleted_by' => $gameAdmin->id]);
            DB::table('job_bans')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('job_bans')->where('deleted_by', $playerAdmin->id)->update(['deleted_by' => $gameAdmin->id]);
            DB::table('player_notes')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('map_switches')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('vpn_whitelist')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('polls')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('remote_music_plays')->where('game_admin_id', $playerAdmin->id)->update(['game_admin_id' => $gameAdmin->id]);
            DB::table('maps')->where('last_built_by', $playerAdmin->id)->update(['last_built_by' => $gameAdmin->id]);
            DB::table('game_builds')->where('started_by', $playerAdmin->id)->update(['started_by' => $gameAdmin->id]);
            DB::table('game_builds')->where('cancelled_by', $playerAdmin->id)->update(['cancelled_by' => $gameAdmin->id]);
            DB::table('game_build_test_merges')->where('added_by', $playerAdmin->id)->update(['added_by' => $gameAdmin->id]);
            DB::table('game_build_test_merges')->where('updated_by', $playerAdmin->id)->update(['updated_by' => $gameAdmin->id]);

            DB::table('game_builds')->whereRaw(
                'EXISTS (SELECT 1 FROM json_array_elements(test_merges) WHERE value->>\'added_by\'::text = ? OR value->>\'updated_by\'::text = ?)',
                [(string) $playerAdmin->id, (string) $playerAdmin->id]
            )->get()->each(function ($gameBuild) use ($playerAdmin, $gameAdmin) {
                $gameBuild->test_merges = json_encode(array_map(function ($testMerge) use ($playerAdmin, $gameAdmin) {
                    if ($testMerge['added_by'] == $playerAdmin->id) {
                        $testMerge['added_by'] = $gameAdmin->id;
                    }
                    if ($testMerge['updated_by'] == $playerAdmin->id) {
                        $testMerge['updated_by'] = $gameAdmin->id;
                    }

                    return $testMerge;
                }, json_decode($gameBuild->test_merges, true)));
                DB::table('game_builds')->where('id', $gameBuild->id)->update(['test_merges' => $gameBuild->test_merges]);
            });
        }

        Schema::table('bans', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
            $table->foreign('deleted_by')->references('id')->on('game_admins');
        });

        Schema::table('job_bans', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
            $table->foreign('deleted_by')->references('id')->on('game_admins');
        });

        Schema::table('player_notes', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        Schema::table('map_switches', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        Schema::table('vpn_whitelist', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        Schema::table('polls', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        Schema::table('remote_music_plays', function (Blueprint $table) {
            $table->foreign('game_admin_id')->references('id')->on('game_admins');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->foreign('last_built_by')->references('id')->on('game_admins');
        });

        Schema::table('game_builds', function (Blueprint $table) {
            $table->foreign('started_by')->references('id')->on('game_admins');
            $table->foreign('cancelled_by')->references('id')->on('game_admins');
        });

        Schema::table('game_build_test_merges', function (Blueprint $table) {
            $table->foreign('added_by')->references('id')->on('game_admins');
            $table->foreign('updated_by')->references('id')->on('game_admins');
        });

        Schema::dropIfExists('player_admin_servers');

        Schema::dropIfExists('player_admins');
    }
};
