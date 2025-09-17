<?php

use App\Http\Controllers\Web\Admin\AuditController;
use App\Http\Controllers\Web\Admin\BansController;
use App\Http\Controllers\Web\Admin\DiscordSettingsController;
use App\Http\Controllers\Web\Admin\ErrorsController;
use App\Http\Controllers\Web\Admin\EventsController;
use App\Http\Controllers\Web\Admin\GameAdminRanksController;
use App\Http\Controllers\Web\Admin\GameAdminsController;
use App\Http\Controllers\Web\Admin\GameBuildsController;
use App\Http\Controllers\Web\Admin\GameBuildSettingsController;
use App\Http\Controllers\Web\Admin\GameBuildTestMergesController;
use App\Http\Controllers\Web\Admin\HosController;
use App\Http\Controllers\Web\Admin\JobBansController;
use App\Http\Controllers\Web\Admin\LogsController;
use App\Http\Controllers\Web\Admin\MapsController;
use App\Http\Controllers\Web\Admin\MedalsController;
use App\Http\Controllers\Web\Admin\MentorsController;
use App\Http\Controllers\Web\Admin\OrchestrationController;
use App\Http\Controllers\Web\Admin\PlayerNotesController;
use App\Http\Controllers\Web\Admin\PlayersController;
use App\Http\Controllers\Web\Admin\RedirectsController;
use App\Http\Controllers\Web\Admin\RoundsController;
use App\Http\Controllers\Web\Admin\UsersController;
use App\Http\Controllers\Web\Admin\WhitelistController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::controller(UsersController::class)->prefix('users')->middleware([EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', 'index')->name('admin.users.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Users', 'admin.users.index'));
    Route::get('/create', 'create')->name('admin.users.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.users.index')->push('Create', 'admin.users.create'));
    Route::post('/', 'store')->name('admin.users.store');
    Route::get('/edit/{user}', 'edit')->name('admin.users.edit')
        ->breadcrumbs(fn (Trail $trail, $user) => $trail->parent('admin.users.index')->push('Edit', route('admin.users.edit', $user)));
    Route::put('/{user}', 'update')->whereNumber('user')->name('admin.users.update');
});

Route::controller(GameAdminRanksController::class)->prefix('game-admin-ranks')->group(function () {
    Route::get('/', 'index')->name('admin.game-admin-ranks.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Admin Ranks', 'admin.game-admin-ranks.index'));
    Route::get('/create', 'create')->name('admin.game-admin-ranks.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.game-admin-ranks.index')->push('Create', 'admin.game-admin-ranks.create'));
    Route::post('/', 'store')->name('admin.game-admin-ranks.store');
});

Route::controller(GameAdminsController::class)->prefix('game-admins')->group(function () {
    Route::get('/', 'index')->name('admin.game-admins.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Admins', 'admin.game-admins.index'));
    Route::get('/{gameAdmin}', 'show')
        ->whereNumber('gameAdmin')
        ->name('admin.game-admins.show')
        ->breadcrumbs(fn (Trail $trail, $gameAdmin) => $trail->parent('admin.game-admins.index')->push('Show', route('admin.game-admins.show', $gameAdmin)));
});

Route::controller(RoundsController::class)->prefix('rounds')->group(function () {
    Route::get('/', 'index')->name('admin.rounds.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Rounds', 'admin.rounds.index'));
    Route::get('/{round}', 'show')
        ->whereNumber('round')
        ->name('admin.rounds.show')
        ->breadcrumbs(fn (Trail $trail, $round) => $trail->parent('admin.rounds.index')->push('Show', route('admin.rounds.show', $round)));
});

Route::controller(PlayersController::class)->prefix('players')->group(function () {
    Route::get('/', 'index')->name('admin.players.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Players', 'admin.players.index'));
    Route::get('/{player}', 'show')
        ->whereNumber('player')
        ->name('admin.players.show')
        ->breadcrumbs(fn (Trail $trail, $player) => $trail->parent('admin.players.index')->push('Show', route('admin.players.show', $player)));
    Route::get('/{ckey}', 'showByCkey')
        ->name('admin.player.show-by-ckey');
});

Route::controller(BansController::class)->prefix('bans')->group(function () {
    Route::get('/', 'index')->name('admin.bans.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Bans', 'admin.bans.index'));
    Route::get('/removed', 'indexRemoved')->name('admin.bans.index-removed')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Removed Bans', 'admin.bans.index-removed'));
    Route::get('/details', 'getDetails')->name('admin.bans.get-details');
    Route::post('/details/{ban}', 'storeDetail')->whereNumber('ban')->name('admin.bans.store-detail');
    Route::put('/details/{banDetail}', 'updateDetail')->whereNumber('banDetail')->name('admin.bans.update-detail');
    Route::delete('/details/{banDetail}', 'destroyDetail')->whereNumber('banDetail')->name('admin.bans.destroy-detail');
    Route::get('/create', 'create')->name('admin.bans.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.bans.index')->push('Create', 'admin.bans.create'));
    Route::get('/{ban}', 'show')->whereNumber('ban')->name('admin.bans.show')
        ->breadcrumbs(fn (Trail $trail, $ban) => $trail->parent('admin.bans.index')->push('Show', route('admin.bans.show', $ban)));
    Route::post('/', 'store')->name('admin.bans.store');
    Route::get('/edit/{ban}', 'edit')->whereNumber('ban')->name('admin.bans.edit')
        ->breadcrumbs(fn (Trail $trail, $ban) => $trail->parent('admin.bans.index')->push('Edit', route('admin.bans.edit', $ban)));
    Route::put('/{ban}', 'update')->whereNumber('ban')->name('admin.bans.update');
    Route::delete('/{ban}', 'destroy')->whereNumber('ban')->name('admin.bans.delete');
    Route::delete('/', 'destroyMulti')->name('admin.bans.delete-multi');

    Route::get('/remove', 'showRemoveDetails')->name('admin.bans.show-remove-details')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.bans.index')->push('Remove', 'admin.bans.show-remove-details'));
    Route::post('/remove/lookup', 'lookupDetails')->name('admin.bans.lookup-details');
    Route::post('/remove', 'removeLookupDetails')->name('admin.bans.remove-lookup-details');
});

Route::controller(JobBansController::class)->prefix('job-bans')->group(function () {
    Route::get('/', 'index')->name('admin.job-bans.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Job Bans', 'admin.job-bans.index'));
    Route::get('/create', 'create')->name('admin.job-bans.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.job-bans.index')->push('Create', 'admin.job-bans.create'));
    Route::get('/{jobBan}', 'show')->whereNumber('jobBan')->name('admin.job-bans.show')
        ->breadcrumbs(fn (Trail $trail, $jobBan) => $trail->parent('admin.job-bans.index')->push('Show', route('admin.job-bans.show', $jobBan)));
    Route::post('/', 'store')->name('admin.job-bans.store');
    Route::get('/edit/{jobBan}', 'edit')->whereNumber('jobBan')->name('admin.job-bans.edit')
        ->breadcrumbs(fn (Trail $trail, $jobBan) => $trail->parent('admin.job-bans.index')->push('Edit', route('admin.job-bans.edit', $jobBan)));
    Route::put('/{jobBan}', 'update')->whereNumber('jobBan')->name('admin.job-bans.update');
    Route::delete('/{jobBan}', 'destroy')->whereNumber('jobBan')->name('admin.job-bans.delete');
    Route::delete('/', 'destroyMulti')->name('admin.job-bans.delete-multi');
});

Route::controller(PlayerNotesController::class)->prefix('notes')->group(function () {
    Route::get('/', 'index')->name('admin.notes.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Notes', 'admin.notes.index'));
    Route::get('/create', 'create')->name('admin.notes.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.notes.index')->push('Create', 'admin.notes.create'));
    Route::get('/{note}', 'show')->whereNumber('note')->name('admin.notes.show')
        ->breadcrumbs(fn (Trail $trail, $note) => $trail->parent('admin.notes.index')->push('Show', route('admin.notes.show', $note)));
    Route::post('/', 'store')->name('admin.notes.store');
    Route::get('/edit/{note}', 'edit')->whereNumber('note')->name('admin.notes.edit')
        ->breadcrumbs(fn (Trail $trail, $note) => $trail->parent('admin.notes.index')->push('Edit', route('admin.notes.edit', $note)));
    Route::put('/{note}', 'update')->whereNumber('note')->name('admin.notes.update');
    Route::delete('/{note}', 'destroy')->whereNumber('note')->name('admin.notes.delete');
    Route::delete('/', 'destroyMulti')->name('admin.notes.delete-multi');
});

Route::controller(MapsController::class)->prefix('maps')->group(function () {
    Route::get('/', 'index')->name('admin.maps.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Maps', 'admin.maps.index'));
    Route::get('/upload', 'showUpload')->name('admin.maps.upload')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.maps.index')->push('Upload', 'admin.maps.upload'));
    Route::post('/upload', 'upload')->name('admin.maps.upload-update');
    Route::post('/upload-file', 'uploadFile')->name('admin.maps.upload-file');
    Route::get('/create', 'create')->name('admin.maps.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.maps.index')->push('Create', 'admin.maps.create'));
    Route::post('/', 'store')->name('admin.maps.store');
    Route::get('/edit/{map}', 'edit')->whereNumber('map')->name('admin.maps.edit')
        ->breadcrumbs(fn (Trail $trail, $map) => $trail->parent('admin.maps.index')->push('Edit', route('admin.maps.edit', $map)));
    Route::put('/{map}', 'update')->whereNumber('map')->name('admin.maps.update');
    Route::delete('/{map}', 'destroy')->whereNumber('map')->name('admin.maps.delete');
});

Route::controller(MedalsController::class)->prefix('medals')->group(function () {
    Route::get('/', 'index')->name('admin.medals.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Medals', 'admin.medals.index'));
    Route::get('/create', 'create')->name('admin.medals.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.medals.index')->push('Create', 'admin.medals.create'));
    Route::post('/', 'store')->name('admin.medals.store');
    Route::get('/edit/{medal}', 'edit')->whereNumber('medal')->name('admin.medals.edit')
        ->breadcrumbs(fn (Trail $trail, $medal) => $trail->parent('admin.medals.index')->push('Edit', route('admin.medals.edit', $medal)));
    Route::put('/{medal}', 'update')->whereNumber('medal')->name('admin.medals.update');
    Route::delete('/{medal}', 'destroy')->whereNumber('medal')->name('admin.medals.delete');

    Route::get('/unawarded-to-player/{player}', 'medalsPlayerDoesntHave')->name('admin.medals.unawarded-to-player');
    Route::post('/add-to-player', 'addToPlayer')->name('admin.medals.add-to-player');
    Route::delete('/remove-from-player/{player}/{medal}', 'removeFromPlayer')->whereNumber(['player'])->name('admin.medals.remove-from-player');
});

Route::controller(MentorsController::class)->prefix('mentors')->group(function () {
    Route::get('/', 'index')->name('admin.mentors.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Mentors', 'admin.mentors.index'));
    Route::get('/create', 'create')->name('admin.mentors.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.mentors.index')->push('Create', 'admin.mentors.create'));
    Route::post('/', 'store')->name('admin.mentors.store');
    Route::post('/bulk-toggle', 'bulkToggle')->name('admin.mentors.bulk-toggle');

    Route::delete('/{mentor}', 'destroy')->whereNumber('mentor')->name('admin.mentors.delete');
    Route::delete('/', 'destroyMulti')->name('admin.mentors.delete-multi');
});

Route::controller(HosController::class)->prefix('hos')->group(function () {
    Route::get('/', 'index')->name('admin.hos.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Heads of Security', 'admin.hos.index'));
    Route::get('/create', 'create')->name('admin.hos.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.hos.index')->push('Create', 'admin.hos.create'));
    Route::post('/', 'store')->name('admin.hos.store');
    Route::post('/bulk-toggle', 'bulkToggle')->name('admin.hos.bulk-toggle');

    Route::delete('/{hos}', 'destroy')->whereNumber('hos')->name('admin.hos.delete');
    Route::delete('/', 'destroyMulti')->name('admin.hos.delete-multi');
});

Route::controller(WhitelistController::class)->prefix('whitelist')->group(function () {
    Route::get('/', 'index')->name('admin.whitelist.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Whitelist', 'admin.whitelist.index'));
    Route::get('/create', 'create')->name('admin.whitelist.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.whitelist.index')->push('Create', 'admin.whitelist.create'));
    Route::post('/', 'store')->name('admin.whitelist.store');
    Route::get('/edit/{whitelistedPlayer}', 'edit')->whereNumber('whitelistedPlayer')->name('admin.whitelist.edit')
        ->breadcrumbs(fn (Trail $trail, $whitelistedPlayer) => $trail->parent('admin.whitelist.index')->push('Edit', route('admin.whitelist.edit', $whitelistedPlayer)));
    Route::put('/{whitelistedPlayer}', 'update')->whereNumber('whitelistedPlayer')->name('admin.whitelist.update');
    Route::post('/bulk-toggle', 'bulkToggle')->name('admin.whitelist.bulk-toggle');

    Route::delete('/{whitelistedPlayer}', 'destroy')->whereNumber('whitelistedPlayer')->name('admin.whitelist.delete');
    Route::delete('/', 'destroyMulti')->name('admin.whitelist.delete-multi');
});

Route::controller(EventsController::class)->prefix('events')->group(function () {
    Route::get('/', 'index')->name('admin.events.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Events', 'admin.events.index'));
});

Route::controller(LogsController::class)->prefix('logs')->group(function () {
    Route::get('/', 'index')->name('admin.logs.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Logs', 'admin.logs.index'));
    Route::get('/{gameRound}', 'show')
        ->whereNumber('gameRound')
        ->name('admin.logs.show')
        ->breadcrumbs(fn (Trail $trail, $gameRound) => $trail->parent('admin.logs.index')->push('Show', route('admin.logs.show', $gameRound)));
    Route::get('/get-logs/{gameRound}', 'getLogs')
        ->whereNumber('gameRound')
        ->name('admin.logs.get-logs');
});

Route::controller(ErrorsController::class)->prefix('errors')->group(function () {
    Route::get('/', 'index')->name('admin.errors.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Errors', 'admin.errors.index'));
    Route::get('/summary', 'summary')->name('admin.errors.summary')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Error Summary', 'admin.errors.summary'));
    Route::get('/{gameRound}', 'show')
        ->whereNumber('gameRound')
        ->name('admin.errors.show')
        ->breadcrumbs(fn (Trail $trail, $gameRound) => $trail->parent('admin.errors.index')->push('Show', route('admin.errors.show', $gameRound)));
    Route::get('/get-errors/{gameRound}', 'getErrors')
        ->whereNumber('gameRound')
        ->name('admin.errors.get-errors');
});

Route::controller(RedirectsController::class)->prefix('redirects')->group(function () {
    Route::get('/', 'index')->name('admin.redirects.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Redirects', 'admin.redirects.index'));
    Route::get('/create', 'create')->name('admin.redirects.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.redirects.index')->push('Create', 'admin.redirects.create'));
    Route::post('/', 'store')->name('admin.redirects.store');
    Route::get('/edit/{redirect}', 'edit')->whereNumber('redirect')->name('admin.redirects.edit')
        ->breadcrumbs(fn (Trail $trail, $redirect) => $trail->parent('admin.redirects.index')->push('Edit', route('admin.redirects.edit', $redirect)));
    Route::put('/{redirect}', 'update')->whereNumber('redirect')->name('admin.redirects.update');
    Route::delete('/{redirect}', 'destroy')->whereNumber('redirect')->name('admin.redirects.delete');
});

Route::controller(GameBuildsController::class)->prefix('builds')->group(function () {
    Route::get('/', 'index')->name('admin.builds.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Builds', 'admin.builds.index'));
    Route::get('/status', 'status')->name('admin.builds.status');
    Route::post('/start', 'store')->name('admin.builds.store');
    Route::get('/{build}', 'show')->whereNumber('build')->name('admin.builds.show')
        ->breadcrumbs(fn (Trail $trail, $build) => $trail->parent('admin.builds.index')->push('Show', route('admin.builds.show', $build)));
    Route::post('/cancel', 'cancel')->name('admin.builds.cancel');
});

Route::controller(GameBuildSettingsController::class)->prefix('builds/settings')->group(function () {
    Route::get('/', 'index')->name('admin.builds.settings.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.index')->push('Build Settings', 'admin.builds.settings.index'));
    Route::get('/create', 'create')->name('admin.builds.settings.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.settings.index')->push('Create', 'admin.builds.settings.create'));
    Route::post('/', 'store')->name('admin.builds.settings.store');
    Route::get('/{setting}', 'edit')->whereNumber('setting')->name('admin.builds.settings.edit')
        ->breadcrumbs(fn (Trail $trail, $setting) => $trail->parent('admin.builds.settings.index')->push('Edit', route('admin.builds.settings.edit', $setting)));
    Route::put('/{setting}', 'update')->whereNumber('setting')->name('admin.builds.settings.update');
    Route::delete('/{setting}', 'destroy')->whereNumber('setting')->name('admin.builds.settings.delete');
});

Route::controller(GameBuildTestMergesController::class)->prefix('builds/test-merges')->group(function () {
    Route::get('/', 'index')->name('admin.builds.test-merges.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.index')->push('Build Test Merges', 'admin.builds.test-merges.index'));
    Route::get('/create', 'create')->name('admin.builds.test-merges.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.test-merges.index')->push('Create', 'admin.builds.test-merges.create'));
    Route::post('/', 'store')->name('admin.builds.test-merges.store');
    Route::get('/{testMerge}', 'edit')->whereNumber('testMerge')->name('admin.builds.test-merges.edit')
        ->breadcrumbs(fn (Trail $trail, $testMerge) => $trail->parent('admin.builds.test-merges.index')->push('Edit', route('admin.builds.test-merges.edit', $testMerge)));
    Route::put('/{testMerge}', 'update')->whereNumber('testMerge')->name('admin.builds.test-merges.update');
    Route::delete('/{testMerge}', 'destroy')->whereNumber('testMerge')->name('admin.builds.test-merges.delete');
    Route::delete('/', 'destroyMulti')->name('admin.builds.test-merges.delete-multi');
    Route::get('/pr', 'pullRequests')->name('admin.builds.test-merges.pr');
    Route::get('/pr/{prId}', 'pullRequestDetails')->whereNumber('prId')->name('admin.builds.test-merges.pr-details');
    Route::get('/{testMerge}/commit', 'editCommit')->whereNumber('testMerge')->name('admin.builds.test-merges.edit-commit')
        ->breadcrumbs(fn (Trail $trail, $testMerge) => $trail->parent('admin.builds.test-merges.index')->push('Edit', route('admin.builds.test-merges.edit-commit', $testMerge)));
    Route::put('/{testMerge}/commit', 'updateCommit')->whereNumber('testMerge')->name('admin.builds.test-merges.update-commit');
    Route::put('/{prId}/commits', 'updateCommits')->whereNumber('prId')->name('admin.builds.test-merges.update-commits');
});

Route::controller(OrchestrationController::class)->prefix('orchestration')->group(function () {
    Route::get('/status', 'status')->name('admin.orchestration.status');
    Route::post('/restart', 'restart')->name('admin.orchestration.restart');
});

Route::controller(AuditController::class)->prefix('audit')->group(function () {
    Route::get('/', 'index')->name('admin.audit.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Audit', 'admin.audit.index'));
    Route::get('/types', 'getTypes')->name('admin.audit.types');
    Route::get('/{audit}', 'show')->whereNumber('audit')->name('admin.audit.show')
        ->breadcrumbs(fn (Trail $trail, $audit) => $trail->parent('admin.audit.index')->push('Show', route('admin.audit.show', $audit)));
});

Route::controller(DiscordSettingsController::class)->prefix('discord-settings')->group(function () {
    Route::get('/', 'index')->name('admin.discord-settings.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Discord Settings', 'admin.discord-settings.index'));
    Route::post('/', 'store')->name('admin.discord-settings.store');
    Route::put('/', 'update')->name('admin.discord-settings.update');

    Route::get('/roles', 'roles')->name('admin.discord-settings.roles');
});
