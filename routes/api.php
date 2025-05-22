<?php

use App\Http\Controllers\Api\BansController;
use App\Http\Controllers\Api\DectalkController;
use App\Http\Controllers\Api\GameAdminRanksController;
use App\Http\Controllers\Api\GameAdminsController;
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

Route::get('test', [TestController::class, 'index'])->can('view-test');

Route::middleware(['isadmin'])->group(function () {
    Route::controller(GameRoundsController::class)->prefix('rounds')->group(function () {
        Route::post('/', 'store');
        Route::put('/{gameRound}', 'update');
        Route::put('/end/{gameRound}', 'endRound');
    });
    Route::controller(PlayersController::class)->prefix('players')->group(function () {
        Route::post('/', 'store');
        Route::get('/search', 'search');
        Route::get('/get-ips', 'getIps');
        Route::get('/get-compids', 'getCompIds');
        Route::get('/stats', 'stats');
    });
    Route::controller(PlayerParticipationsController::class)->prefix('players/participations')->group(function () {
        Route::post('/', 'store');
        Route::post('/bulk', 'storeBulk');
    });
    Route::controller(PlayerPlaytimeController::class)->prefix('players/playtime')->group(function () {
        Route::post('/bulk', 'storeBulk');
    });
    Route::controller(PlayerAntagsController::class)->prefix('players/antags')->group(function () {
        Route::post('/', 'store');
    });
    Route::controller(PlayerNotesController::class)->prefix('players/notes')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{note}', 'update');
        Route::delete('/{note}', 'destroy');
    });
    Route::controller(PlayerSavesController::class)->prefix('players/saves')->group(function () {
        Route::get('/', 'index');
        Route::post('/data', 'storeData');
        Route::post('/file', 'storeFile');
        Route::post('/data-bulk', 'storeDataBulk');
        Route::post('/transfer-files', 'transferSaves');
        Route::delete('/data', 'destroyData');
        Route::delete('/file', 'destroyFile');
    });
    Route::controller(PlayerMetadataController::class)->prefix('players/metadata')->group(function () {
        Route::get('/', 'index');
        Route::get('/get-by-player/{ckey}', 'getByPlayer');
        Route::get('/get-by-data/{data}', 'getByData');
        Route::post('/', 'store');
        Route::delete('/clear-by-player/{ckey}', 'destroyByPlayer');
        Route::delete('/clear-by-data/{metadata}', 'destroyByData');
    });
    Route::controller(PlayerMedalsController::class)->prefix('players/medals')->group(function () {
        Route::get('/', 'index');
        // Route::get('/{player}', 'show');
        Route::get('/has/{player}', 'has');
        Route::post('/', 'store');
        Route::post('/transfer', 'transfer');
        Route::delete('/', 'destroy');
    });
    Route::controller(BansController::class)->prefix('bans')->group(function () {
        Route::get('/', 'index');
        Route::get('/check', 'check');
        Route::post('/', 'store');
        Route::put('/{ban}', 'update');
        Route::delete('/{ban}', 'destroy');
        Route::post('/details/{ban}', 'addDetails');
        Route::delete('/details/{banDetail}', 'destroyDetail');
    });
    Route::controller(JobBansController::class)->prefix('job-bans')->group(function () {
        Route::get('/', 'index');
        Route::get('/check', 'check');
        Route::get('/get-for-player', 'getForPlayer');
        Route::post('/', 'store');
        Route::put('/{jobBan}', 'update');
        Route::delete('/', 'destroy');
    });
    Route::controller(MapsController::class)->prefix('maps')->group(function () {
        Route::post('/generate', 'generate');
    });
    Route::controller(VpnChecksController::class)->prefix('vpncheck')->group(function () {
        Route::get('/{ip}', 'check');
    });
    Route::controller(VpnWhitelistController::class)->prefix('vpncheck-whitelist')->group(function () {
        Route::get('/', 'index');
        Route::get('/search', 'search');
        Route::post('/', 'store');
        Route::delete('/', 'destroy');
    });
    Route::controller(RemoteMusicController::class)->prefix('remote-music')->group(function () {
        Route::post('/', 'store');
    });
    Route::controller(RandomEntriesController::class)->prefix('random-entries')->group(function () {
        Route::get('/', 'index');
    });
    Route::controller(PollsController::class)->prefix('polls')->group(function () {
        Route::get('/', 'index');
        Route::get('/{poll}', 'show');
        Route::post('/', 'store');
        Route::put('/{poll}', 'update');
        Route::delete('/{poll}', 'destroy');
        Route::post('/option/{poll}', 'addOption');
        Route::put('/option/{pollOption}', 'updateOption');
        Route::delete('/option/{pollOption}', 'destroyOption');
        Route::post('/option/pick/{pollOption}', 'pickOption');
        Route::post('/option/unpick/{pollOption}', 'unpickOption');
    });
    Route::controller(GameAdminRanksController::class)->prefix('game-admin-ranks')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{gameAdminRank}', 'update');
        Route::delete('/{gameAdminRank}', 'destroy');
    });
    Route::controller(GameAdminsController::class)->prefix('game-admins')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{gameAdmin}', 'update');
        Route::delete('/{gameAdmin}', 'destroy');
    });
    Route::controller(RedirectsController::class)->prefix('redirects')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{redirect}', 'update');
        Route::delete('/{redirect}', 'destroy');
    });
    Route::controller(NumbersStationController::class)->prefix('numbers-station')->group(function () {
        Route::get('/', 'index');
    });
    Route::controller(GauntletController::class)->prefix('gauntlet')->group(function () {
        Route::get('/get-previous', 'getPrevious');
    });
    Route::controller(DectalkController::class)->prefix('dectalk')->group(function () {
        Route::post('/play', 'play');
    });
    Route::controller(ServerPerformanceController::class)->prefix('server-performance')->group(function () {
        Route::get('/', 'index');
    });
    Route::controller(GameBuildsController::class)->prefix('game-builds')->group(function () {
        Route::get('/', 'index');
        Route::get('/status', 'status');
        Route::post('/build', 'build');
        Route::post('/cancel', 'cancel');
    });
    Route::controller(GameBuildSettingsController::class)->prefix('game-build-settings')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{setting}', 'update');
        Route::delete('/{setting}', 'destroy');
    });
    Route::controller(GameBuildTestMergesController::class)->prefix('game-build-test-merges')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::put('/{testMerge}', 'update');
        Route::delete('/{testMerge}', 'destroy');
    });
    Route::controller(GameBuildArtifactsController::class)->prefix('game-build-artifacts')->group(function () {
        Route::get('/check', 'check');
        Route::get('/game', 'game');
        Route::get('/byond', 'byond');
        Route::get('/rustg', 'rustg');
    });
    Route::controller(OrchestrationController::class)->prefix('orchestration')->group(function () {
        Route::get('/status', 'status');
        Route::post('/restart', 'restart');
    });
});
