<?php

use App\Http\Controllers\Api\BansController;
use App\Http\Controllers\Api\DectalkController;
use App\Http\Controllers\Api\GameAdminRanksController;
use App\Http\Controllers\Api\GameAdminsController;
use App\Http\Controllers\Api\GameAuthController;
use App\Http\Controllers\Api\GameBuildArtifactsController;
use App\Http\Controllers\Api\GameBuildsController;
use App\Http\Controllers\Api\GameBuildSettingsController;
use App\Http\Controllers\Api\GameBuildTestMergesController;
use App\Http\Controllers\Api\GameRoundsController;
use App\Http\Controllers\Api\GauntletController;
use App\Http\Controllers\Api\JobBansController;
use App\Http\Controllers\Api\MapsController;
use App\Http\Controllers\Api\NumbersStationController;
use App\Http\Controllers\Api\OrchestrationController;
use App\Http\Controllers\Api\PlayerAntagsController;
use App\Http\Controllers\Api\PlayerMedalsController;
use App\Http\Controllers\Api\PlayerMetadataController;
use App\Http\Controllers\Api\PlayerNotesController;
use App\Http\Controllers\Api\PlayerParticipationsController;
use App\Http\Controllers\Api\PlayerPlaytimeController;
use App\Http\Controllers\Api\PlayerSavesController;
use App\Http\Controllers\Api\PlayersController;
use App\Http\Controllers\Api\PollsController;
use App\Http\Controllers\Api\RandomEntriesController;
use App\Http\Controllers\Api\RedirectsController;
use App\Http\Controllers\Api\RemoteMusicController;
use App\Http\Controllers\Api\ServerPerformanceController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\VpnChecksController;
use App\Http\Controllers\Api\VpnWhitelistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test', [TestController::class, 'index'])->name('test');
Route::get('test/status', [TestController::class, 'status'])->name('test.status');

Route::middleware(['isadmin'])->group(function () {
    Route::controller(UsersController::class)->prefix('users')->name('users.')->group(function () {
        Route::post('/discord-link', 'discordLink')->name('discord-link');
        Route::post('/discord-unlink', 'discordUnlink')->name('discord-unlink');
    });
    Route::controller(GameAuthController::class)->prefix('game-auth')->name('game-auth.')->group(function () {
        Route::post('/begin', 'begin')->name('begin');
    });
    Route::controller(GameRoundsController::class)->prefix('rounds')->name('rounds.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{gameRound}', 'update')->name('update');
        Route::put('/end/{gameRound}', 'endRound')->name('end');
    });
    Route::controller(PlayersController::class)->prefix('players')->name('players.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::get('/get-ips', 'getIps')->name('get-ips');
        Route::get('/get-compids', 'getCompIds')->name('get-compids');
        Route::get('/stats', 'stats')->name('stats');
    });
    Route::controller(PlayerParticipationsController::class)->prefix('players/participations')->name('players.participations.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('/bulk', 'storeBulk')->name('bulk');
    });
    Route::controller(PlayerPlaytimeController::class)->prefix('players/playtime')->name('players.playtime.')->group(function () {
        Route::post('/bulk', 'storeBulk')->name('bulk');
    });
    Route::controller(PlayerAntagsController::class)->prefix('players/antags')->name('players.antags.')->group(function () {
        Route::post('/', 'store')->name('store');
    });
    Route::controller(PlayerNotesController::class)->prefix('players/notes')->name('players.notes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{note}', 'update')->name('update');
        Route::delete('/{note}', 'destroy')->name('destroy');
    });
    Route::controller(PlayerSavesController::class)->prefix('players/saves')->name('players.saves.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/data', 'storeData')->name('data');
        Route::post('/file', 'storeFile')->name('file');
        Route::post('/data-bulk', 'storeDataBulk')->name('data-bulk');
        Route::post('/transfer-files', 'transferSaves')->name('transfer-files');
        Route::delete('/data', 'destroyData')->name('destroy-data');
        Route::delete('/file', 'destroyFile')->name('destroy-file');
    });
    Route::controller(PlayerMetadataController::class)->prefix('players/metadata')->name('players.metadata.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-by-player/{ckey}', 'getByPlayer')->name('get-by-player');
        Route::get('/get-by-data/{data}', 'getByData')->name('get-by-data');
        Route::post('/', 'store')->name('store');
        Route::delete('/clear-by-player/{ckey}', 'destroyByPlayer')->name('destroy-by-player');
        Route::delete('/clear-by-data/{metadata}', 'destroyByData')->name('destroy-by-data');
    });
    Route::controller(PlayerMedalsController::class)->prefix('players/medals')->name('players.medals.')->group(function () {
        Route::get('/', 'index')->name('index');
        // Route::get('/{player}', 'show');
        Route::get('/has/{player}', 'has')->name('has');
        Route::post('/', 'store')->name('store');
        Route::post('/transfer', 'transfer')->name('transfer');
        Route::delete('/', 'destroy')->name('destroy');
    });
    Route::controller(BansController::class)->prefix('bans')->name('bans.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/check', 'check')->name('check');
        Route::post('/', 'store')->name('store');
        Route::put('/{ban}', 'update')->name('update');
        Route::delete('/{ban}', 'destroy')->name('destroy');
        Route::post('/details/{ban}', 'addDetails')->name('add-details');
        Route::delete('/details/{banDetail}', 'destroyDetail')->name('destroy-detail');
    });
    Route::controller(JobBansController::class)->prefix('job-bans')->name('job-bans.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/check', 'check')->name('check');
        Route::get('/get-for-player', 'getForPlayer')->name('get-for-player');
        Route::post('/', 'store')->name('store');
        Route::put('/{jobBan}', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });
    Route::controller(MapsController::class)->prefix('maps')->name('maps.')->group(function () {
        Route::post('/generate', 'generate')->name('generate');
    });
    Route::controller(VpnChecksController::class)->prefix('vpncheck')->name('vpncheck.')->group(function () {
        Route::get('/{ip}', 'check')->name('check');
    });
    Route::controller(VpnWhitelistController::class)->prefix('vpncheck-whitelist')->name('vpncheck.whitelist.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'destroy')->name('destroy');
    });
    Route::controller(RemoteMusicController::class)->prefix('remote-music')->name('remote-music.')->group(function () {
        Route::post('/', 'store')->name('store');
    });
    Route::controller(RandomEntriesController::class)->prefix('random-entries')->name('random-entries.')->group(function () {
        Route::get('/', 'index')->name('index');
    });
    Route::controller(PollsController::class)->prefix('polls')->name('polls.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{poll}', 'show')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/{poll}', 'update')->name('update');
        Route::delete('/{poll}', 'destroy')->name('destroy');
        Route::post('/option/{poll}', 'addOption')->name('add-option');
        Route::put('/option/{pollOption}', 'updateOption')->name('update-option');
        Route::delete('/option/{pollOption}', 'destroyOption')->name('destroy-option');
        Route::post('/option/pick/{pollOption}', 'pickOption')->name('pick-option');
        Route::post('/option/unpick/{pollOption}', 'unpickOption')->name('unpick-option');
    });
    Route::controller(GameAdminRanksController::class)->prefix('game-admin-ranks')->name('game-admin-ranks.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{gameAdminRank}', 'update')->name('update');
        Route::delete('/{gameAdminRank}', 'destroy')->name('destroy');
    });
    Route::controller(GameAdminsController::class)->prefix('game-admins')->name('game-admins.')->group(function () {
        Route::get('/', 'index')->name('index');
        // Route::post('/', 'store');
        // Route::put('/{gameAdmin}', 'update');
        // Route::delete('/{gameAdmin}', 'destroy');
    });
    Route::controller(RedirectsController::class)->prefix('redirects')->name('redirects.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{redirect}', 'update')->name('update');
        Route::delete('/{redirect}', 'destroy')->name('destroy');
    });
    Route::controller(NumbersStationController::class)->prefix('numbers-station')->name('numbers-station.')->group(function () {
        Route::get('/', 'index')->name('index');
    });
    Route::controller(GauntletController::class)->prefix('gauntlet')->name('gauntlet.')->group(function () {
        Route::get('/get-previous', 'getPrevious')->name('get-previous');
    });
    Route::controller(DectalkController::class)->prefix('dectalk')->name('dectalk.')->group(function () {
        Route::post('/play', 'play')->name('play');
    });
    Route::controller(ServerPerformanceController::class)->prefix('server-performance')->name('server-performance.')->group(function () {
        Route::get('/', 'index')->name('index');
    });
    Route::controller(GameBuildsController::class)->prefix('game-builds')->name('game-builds.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/status', 'status')->name('status');
        Route::post('/build', 'build')->name('build');
        Route::post('/cancel', 'cancel')->name('cancel');
    });
    Route::controller(GameBuildSettingsController::class)->prefix('game-build-settings')->name('game-build-settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{setting}', 'update')->name('update');
        Route::delete('/{setting}', 'destroy')->name('destroy');
    });
    Route::controller(GameBuildTestMergesController::class)->prefix('game-build-test-merges')->name('game-build-test-merges.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{testMerge}', 'update')->name('update');
        Route::delete('/{testMerge}', 'destroy')->name('destroy');
    });
    Route::controller(GameBuildArtifactsController::class)->prefix('game-build-artifacts')->name('game-build-artifacts.')->group(function () {
        Route::get('/check', 'check')->name('check');
        Route::get('/game', 'game')->name('game');
        Route::get('/byond', 'byond')->name('byond');
        Route::get('/rustg', 'rustg')->name('rustg');
        Route::get('/byond-tracy-writer', 'byondTracyWriter')->name('byond-tracy-writer');
    });
    Route::controller(OrchestrationController::class)->prefix('orchestration')->name('orchestration.')->group(function () {
        Route::get('/status', 'status')->name('status');
        Route::post('/restart', 'restart')->name('restart');
    });
});
