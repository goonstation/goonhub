<?php

use App\Http\Controllers\Web\Admin\AuditController;
use App\Http\Controllers\Web\Admin\BansController;
use App\Http\Controllers\Web\Admin\BypassCapController;
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

Route::controller(UsersController::class)->prefix('users')->name('users.')->middleware([EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Users', 'admin.users.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.users.index')->push('Create', 'admin.users.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{user}', 'edit')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $user) => $trail->parent('admin.users.index')->push('Edit', route('admin.users.edit', $user)));
    Route::put('/{user}', 'update')->whereNumber('user')->name('update');
});

Route::controller(GameAdminRanksController::class)->prefix('game-admin-ranks')->name('game-admin-ranks.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Admin Ranks', 'admin.game-admin-ranks.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.game-admin-ranks.index')->push('Create', 'admin.game-admin-ranks.create'));
    Route::post('/', 'store')->name('store');
});

Route::controller(GameAdminsController::class)->prefix('game-admins')->name('game-admins.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Admins', 'admin.game-admins.index'));
    Route::get('/{gameAdmin}', 'show')
        ->whereNumber('gameAdmin')
        ->name('show')
        ->breadcrumbs(fn (Trail $trail, $gameAdmin) => $trail->parent('admin.game-admins.index')->push('Show', route('admin.game-admins.show', $gameAdmin)));
});

Route::controller(RoundsController::class)->prefix('rounds')->name('rounds.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Rounds', 'admin.rounds.index'));
    Route::get('/{round}', 'show')
        ->whereNumber('round')
        ->name('show')
        ->breadcrumbs(fn (Trail $trail, $round) => $trail->parent('admin.rounds.index')->push('Show', route('admin.rounds.show', $round)));
});

Route::controller(PlayersController::class)->prefix('players')->name('players.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Players', 'admin.players.index'));
    Route::get('/{player}', 'show')
        ->whereNumber('player')
        ->name('show')
        ->breadcrumbs(fn (Trail $trail, $player) => $trail->parent('admin.players.index')->push('Show', route('admin.players.show', $player)));
    Route::get('/{ckey}', 'showByCkey')
        ->name('show-by-ckey');
});

Route::controller(BansController::class)->prefix('bans')->name('bans.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Bans', 'admin.bans.index'));
    Route::get('/details', 'getDetails')->name('get-details');
    Route::post('/details/{ban}', 'storeDetail')->whereNumber('ban')->name('store-detail');
    Route::put('/details/{banDetail}', 'updateDetail')->whereNumber('banDetail')->name('update-detail');
    Route::delete('/details/{banDetail}', 'destroyDetail')->whereNumber('banDetail')->name('destroy-detail');
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.bans.index')->push('Create', 'admin.bans.create'));
    Route::get('/{ban}', 'show')->whereNumber('ban')->name('show')
        ->breadcrumbs(fn (Trail $trail, $ban) => $trail->parent('admin.bans.index')->push('Show', route('admin.bans.show', $ban)));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{ban}', 'edit')->whereNumber('ban')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $ban) => $trail->parent('admin.bans.index')->push('Edit', route('admin.bans.edit', $ban)));
    Route::put('/{ban}', 'update')->whereNumber('ban')->name('update');
    Route::delete('/{ban}', 'destroy')->whereNumber('ban')->name('delete');
    Route::delete('/', 'destroyMulti')->name('delete-multi');

    Route::get('/remove', 'showRemoveDetails')->name('show-remove-details')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.bans.index')->push('Remove', 'admin.bans.show-remove-details'));
    Route::post('/remove/lookup', 'lookupDetails')->name('lookup-details');
    Route::post('/remove', 'removeLookupDetails')->name('remove-lookup-details');
});

Route::controller(JobBansController::class)->prefix('job-bans')->name('job-bans.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Job Bans', 'admin.job-bans.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.job-bans.index')->push('Create', 'admin.job-bans.create'));
    Route::get('/{jobBan}', 'show')->whereNumber('jobBan')->name('show')
        ->breadcrumbs(fn (Trail $trail, $jobBan) => $trail->parent('admin.job-bans.index')->push('Show', route('admin.job-bans.show', $jobBan)));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{jobBan}', 'edit')->whereNumber('jobBan')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $jobBan) => $trail->parent('admin.job-bans.index')->push('Edit', route('admin.job-bans.edit', $jobBan)));
    Route::put('/{jobBan}', 'update')->whereNumber('jobBan')->name('update');
    Route::delete('/{jobBan}', 'destroy')->whereNumber('jobBan')->name('delete');
    Route::delete('/', 'destroyMulti')->name('delete-multi');
});

Route::controller(PlayerNotesController::class)->prefix('notes')->name('notes.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Notes', 'admin.notes.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.notes.index')->push('Create', 'admin.notes.create'));
    Route::get('/{note}', 'show')->whereNumber('note')->name('show')
        ->breadcrumbs(fn (Trail $trail, $note) => $trail->parent('admin.notes.index')->push('Show', route('admin.notes.show', $note)));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{note}', 'edit')->whereNumber('note')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $note) => $trail->parent('admin.notes.index')->push('Edit', route('admin.notes.edit', $note)));
    Route::put('/{note}', 'update')->whereNumber('note')->name('update');
    Route::delete('/{note}', 'destroy')->whereNumber('note')->name('delete');
    Route::delete('/', 'destroyMulti')->name('delete-multi');
});

Route::controller(MapsController::class)->prefix('maps')->name('maps.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Maps', 'admin.maps.index'));
    Route::get('/upload', 'showUpload')->name('upload')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.maps.index')->push('Upload', 'admin.maps.upload'));
    Route::post('/upload', 'upload')->name('upload-update');
    Route::post('/upload-file', 'uploadFile')->name('upload-file');
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.maps.index')->push('Create', 'admin.maps.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{map}', 'edit')->whereNumber('map')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $map) => $trail->parent('admin.maps.index')->push('Edit', route('admin.maps.edit', $map)));
    Route::put('/{map}', 'update')->whereNumber('map')->name('update');
    Route::delete('/{map}', 'destroy')->whereNumber('map')->name('delete');
});

Route::controller(MedalsController::class)->prefix('medals')->name('medals.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Medals', 'admin.medals.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.medals.index')->push('Create', 'admin.medals.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{medal}', 'edit')->whereNumber('medal')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $medal) => $trail->parent('admin.medals.index')->push('Edit', route('admin.medals.edit', $medal)));
    Route::put('/{medal}', 'update')->whereNumber('medal')->name('update');
    Route::delete('/{medal}', 'destroy')->whereNumber('medal')->name('delete');

    Route::get('/unawarded-to-player/{player}', 'medalsPlayerDoesntHave')->name('unawarded-to-player');
    Route::post('/add-to-player', 'addToPlayer')->name('add-to-player');
    Route::delete('/remove-from-player/{player}/{medal}', 'removeFromPlayer')->whereNumber(['player'])->name('remove-from-player');
});

Route::controller(MentorsController::class)->prefix('mentors')->name('mentors.')->group(function () {
    Route::delete('/', 'destroyMulti')->name('delete-multi');
    Route::post('/bulk-toggle', 'bulkToggle')->name('bulk-toggle');
    Route::post('/toggle/{player}', 'toggle')->whereNumber('player')->name('toggle');
});

Route::controller(HosController::class)->prefix('hos')->name('hos.')->group(function () {
    Route::delete('/', 'destroyMulti')->name('delete-multi');
    Route::post('/bulk-toggle', 'bulkToggle')->name('bulk-toggle');
    Route::post('/toggle/{player}', 'toggle')->whereNumber('player')->name('toggle');
});

Route::controller(WhitelistController::class)->prefix('whitelist')->name('whitelist.')->group(function () {
    Route::delete('/', 'destroyMulti')->name('delete-multi');
    Route::post('/bulk-toggle', 'bulkToggle')->name('bulk-toggle');
    Route::post('/toggle/{player}', 'toggle')->whereNumber('player')->name('toggle');
});

Route::controller(BypassCapController::class)->prefix('bypass-cap')->name('bypass-cap.')->group(function () {
    Route::delete('/', 'destroyMulti')->name('delete-multi');
    Route::post('/bulk-toggle', 'bulkToggle')->name('bulk-toggle');
    Route::post('/toggle/{player}', 'toggle')->whereNumber('player')->name('toggle');
});

Route::controller(EventsController::class)->prefix('events')->name('events.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Events', 'admin.events.index'));
});

Route::controller(LogsController::class)->prefix('logs')->name('logs.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Logs', 'admin.logs.index'));
    Route::get('/search', 'search')->name('search')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.logs.index')->push('Search All', 'admin.logs.search'));
    Route::get('/search-logs', 'searchLogs')->name('search-logs');
    Route::get('/types', 'getLogTypes')->name('types');
    Route::get('/{gameRound}', 'show')
        ->whereNumber('gameRound')
        ->name('show')
        ->breadcrumbs(fn (Trail $trail, $gameRound) => $trail->parent('admin.logs.index')->push('Show', route('admin.logs.show', $gameRound)));
    Route::get('/get-logs/{gameRound}', 'getLogs')
        ->whereNumber('gameRound')
        ->name('get-logs');
});

Route::controller(ErrorsController::class)->prefix('errors')->name('errors.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Errors', 'admin.errors.index'));
    Route::get('/summary', 'summary')->name('summary')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Error Summary', 'admin.errors.summary'));
    Route::get('/{gameRound}', 'show')
        ->whereNumber('gameRound')
        ->name('show')
        ->breadcrumbs(fn (Trail $trail, $gameRound) => $trail->parent('admin.errors.index')->push('Show', route('admin.errors.show', $gameRound)));
    Route::get('/get-errors/{gameRound}', 'getErrors')
        ->whereNumber('gameRound')
        ->name('get-errors');
});

Route::controller(RedirectsController::class)->prefix('redirects')->name('redirects.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Redirects', 'admin.redirects.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.redirects.index')->push('Create', 'admin.redirects.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/edit/{redirect}', 'edit')->whereNumber('redirect')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $redirect) => $trail->parent('admin.redirects.index')->push('Edit', route('admin.redirects.edit', $redirect)));
    Route::put('/{redirect}', 'update')->whereNumber('redirect')->name('update');
    Route::delete('/{redirect}', 'destroy')->whereNumber('redirect')->name('delete');
});

Route::controller(GameBuildsController::class)->prefix('builds')->name('builds.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Builds', 'admin.builds.index'));
    Route::get('/status', 'status')->name('status');
    Route::post('/start', 'store')->name('store');
    Route::get('/{build}', 'show')->whereNumber('build')->name('show')
        ->breadcrumbs(fn (Trail $trail, $build) => $trail->parent('admin.builds.index')->push('Show', route('admin.builds.show', $build)));
    Route::post('/cancel', 'cancel')->name('cancel');
});

Route::controller(GameBuildSettingsController::class)->prefix('builds/settings')->name('builds.settings.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.index')->push('Build Settings', 'admin.builds.settings.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.settings.index')->push('Create', 'admin.builds.settings.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/{setting}', 'edit')->whereNumber('setting')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $setting) => $trail->parent('admin.builds.settings.index')->push('Edit', route('admin.builds.settings.edit', $setting)));
    Route::put('/{setting}', 'update')->whereNumber('setting')->name('update');
    Route::delete('/{setting}', 'destroy')->whereNumber('setting')->name('delete');
});

Route::controller(GameBuildTestMergesController::class)->prefix('builds/test-merges')->name('builds.test-merges.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.index')->push('Build Test Merges', 'admin.builds.test-merges.index'));
    Route::get('/create', 'create')->name('create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('admin.builds.test-merges.index')->push('Create', 'admin.builds.test-merges.create'));
    Route::post('/', 'store')->name('store');
    Route::get('/{testMerge}', 'edit')->whereNumber('testMerge')->name('edit')
        ->breadcrumbs(fn (Trail $trail, $testMerge) => $trail->parent('admin.builds.test-merges.index')->push('Edit', route('admin.builds.test-merges.edit', $testMerge)));
    Route::put('/{testMerge}', 'update')->whereNumber('testMerge')->name('update');
    Route::delete('/{testMerge}', 'destroy')->whereNumber('testMerge')->name('delete');
    Route::delete('/', 'destroyMulti')->name('delete-multi');
    Route::get('/pr', 'pullRequests')->name('pr');
    Route::get('/pr/{prId}', 'pullRequestDetails')->whereNumber('prId')->name('pr-details');
    Route::get('/{testMerge}/commit', 'editCommit')->whereNumber('testMerge')->name('edit-commit')
        ->breadcrumbs(fn (Trail $trail, $testMerge) => $trail->parent('admin.builds.test-merges.index')->push('Edit', route('admin.builds.test-merges.edit-commit', $testMerge)));
    Route::put('/{testMerge}/commit', 'updateCommit')->whereNumber('testMerge')->name('update-commit');
    Route::put('/{prId}/commits', 'updateCommits')->whereNumber('prId')->name('update-commits');
});

Route::controller(OrchestrationController::class)->prefix('orchestration')->name('orchestration.')->group(function () {
    Route::get('/status', 'status')->name('status');
    Route::post('/restart', 'restart')->name('restart');
});

Route::controller(AuditController::class)->prefix('audit')->name('audit.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Audit', 'admin.audit.index'));
    Route::get('/types', 'getTypes')->name('types');
    Route::get('/{audit}', 'show')->whereNumber('audit')->name('show')
        ->breadcrumbs(fn (Trail $trail, $audit) => $trail->parent('admin.audit.index')->push('Show', route('admin.audit.show', $audit)));
});

Route::controller(DiscordSettingsController::class)->prefix('discord-settings')->name('discord-settings.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Discord Settings', 'admin.discord-settings.index'));
    Route::post('/', 'store')->name('store');
    Route::put('/', 'update')->name('update');

    Route::get('/roles', 'roles')->name('roles');
});
