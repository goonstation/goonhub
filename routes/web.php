<?php

use App\Http\Controllers\Web\AntagsController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ChangelogController;
use App\Http\Controllers\Web\DeathsController;
use App\Http\Controllers\Web\ErrorsController;
use App\Http\Controllers\Web\EventsController;
use App\Http\Controllers\Web\FinesController;
use App\Http\Controllers\Web\GameAuthController;
use App\Http\Controllers\Web\GameServersController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MapsController;
use App\Http\Controllers\Web\MedalsController;
use App\Http\Controllers\Web\OgImageController;
use App\Http\Controllers\Web\PlayController;
use App\Http\Controllers\Web\PlayersController;
use App\Http\Controllers\Web\RedirectController;
use App\Http\Controllers\Web\RoundsController;
use App\Http\Controllers\Web\TerminalController;
use App\Http\Controllers\Web\TicketsController;
use App\Http\Controllers\Web\VotesController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::domain('play.'.preg_replace('(^https?://)', '', config('app.url')))->group(function () {
    Route::controller(PlayController::class)->prefix('/')->group(function () {
        Route::get('/{serverId?}', 'index')->name('play')->withoutMiddleware('web');
    });
});

Route::controller(OgImageController::class)->prefix('/og-image')->group(function () {
    Route::get('/{type}/{id}', 'index')->whereAlpha('type')->whereNumber('id')->name('og-image')->withoutMiddleware('web');
    Route::get('/preview/{type}/{id}', 'preview')->whereAlpha('type')->whereNumber('id')->name('og-image-preview')->middleware([EnsureUserIsAdmin::class]);
});

Route::controller(HomeController::class)->prefix('/')->group(function () {
    Route::get('/', 'index')->name('home')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Home', route('home')));
});

Route::controller(AuthController::class)->prefix('/auth')->group(function () {
    Route::get('/discord-redirect', 'discordRedirect')->name('auth.discord-redirect');
    Route::get('/discord-callback', 'discordCallback')->name('auth.discord-callback');
});

Route::controller(GameAuthController::class)->prefix('/game-auth')
    ->withoutMiddleware([AddLinkHeadersForPreloadedAssets::class])
    ->group(function () {
        Route::middleware('gameauth.state')->group(function () {
            Route::middleware('gameauth.redirect')->group(function () {
                Route::get('/login', 'showLogin')->name('game-auth.show-login')
                    ->breadcrumbs(fn (Trail $trail) => $trail->push('Login', route('game-auth.show-login')));
                Route::post('/login', 'login')->name('game-auth.login');
                Route::get('/register', 'showRegister')->name('game-auth.show-register')
                    ->breadcrumbs(fn (Trail $trail) => $trail->parent('game-auth.show-login')->push('Register', route('game-auth.show-register')));
                Route::post('/register', 'register')->name('game-auth.register');
                Route::get('/forgot', 'showForgot')->name('game-auth.show-forgot')
                    ->breadcrumbs(fn (Trail $trail) => $trail->parent('game-auth.show-login')->push('Forgot Password', route('game-auth.show-forgot')));
            });
            Route::get('/authed', 'authed')->name('game-auth.authed');
            Route::get('/discord-redirect', 'discordRedirect')->name('game-auth.discord-redirect');
            Route::get('/discord-callback', 'discordCallback')->name('game-auth.discord-callback');
        });
        Route::get('/logout', 'logout')->name('game-auth.logout');
        Route::get('/error', 'showError')->name('game-auth.error');
    });

Route::controller(ChangelogController::class)->prefix('/changelog')->group(function () {
    Route::get('/', 'index')->name('changelog')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Changelog', 'changelog'));
});

Route::controller(PlayersController::class)->prefix('/players')->group(function () {
    Route::get('/', 'index')->name('players.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Players', 'players.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/search', 'search')->name('players.search')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('players.index')->push('Search', 'players.search'));
        Route::get('/{player}', 'show')->whereNumber('player')->name('players.show')
            ->breadcrumbs(fn (Trail $trail, $player) => $trail->parent('players.search')->push('Show', route('players.show', $player)));
        Route::get('/{ckey}', 'showByCkey')->name('players.show-by-ckey');
    });
});

Route::controller(RoundsController::class)->prefix('/rounds')->group(function () {
    Route::get('/', 'index')->name('rounds.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Rounds', 'rounds.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/{round}', 'show')->whereNumber('round')->name('rounds.show')
            ->breadcrumbs(fn (Trail $trail, $round) => $trail->parent('rounds.index')->push('Show', route('rounds.show', $round)));
    });
});

Route::prefix('/events')->group(function () {
    Route::controller(EventsController::class)->prefix('/')->group(function () {
        Route::get('/', 'index')->name('events.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->push('Events', 'events.index'));
        Route::get('/stats', 'stats')->name('events.stats');
    });

    Route::controller(DeathsController::class)->prefix('/deaths')->group(function () {
        Route::get('/', 'index')->name('deaths.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('events.index')->push('Deaths', 'deaths.index'));
        Route::get('/{death}', 'show')->whereNumber('death')->name('deaths.show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $death) => $trail->parent('deaths.index')->push('Show', route('deaths.show', $death)));
    });

    Route::controller(TicketsController::class)->prefix('/tickets')->group(function () {
        Route::get('/', 'index')->name('tickets.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('events.index')->push('Tickets', 'tickets.index'));
        Route::get('/{ticket}', 'show')->whereNumber('ticket')->name('tickets.show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $ticket) => $trail->parent('tickets.index')->push('Show', route('tickets.show', $ticket)));
    });

    Route::controller(FinesController::class)->prefix('/fines')->group(function () {
        Route::get('/', 'index')->name('fines.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('events.index')->push('Fines', 'fines.index'));
        Route::get('/{fine}', 'show')->whereNumber('fine')->name('fines.show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $fine) => $trail->parent('fines.index')->push('Show', route('fines.show', $fine)));
    });

    Route::controller(AntagsController::class)->prefix('/antags')->group(function () {
        Route::get('/', 'index')->name('antags.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('events.index')->push('Antagonists', 'antags.index'));
        Route::get('/{antag}', 'show')->whereNumber('antag')->name('antags.show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $antag) => $trail->parent('antags.index')->push('Show', route('antags.show', $antag)));
    });

    Route::controller(ErrorsController::class)->prefix('/errors')->group(function () {
        Route::get('/', 'index')->name('errors.index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('events.index')->push('Errors', 'errors.index'));
    });
});

Route::controller(MapsController::class)->prefix('/maps')->group(function () {
    Route::get('/', 'index')->name('maps.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Maps', 'maps.index'));
    Route::get('/{map}', 'show')->name('maps.show')
        ->breadcrumbs(fn (Trail $trail, $map) => $trail->parent('maps.index')->push('Show', route('maps.show', $map)));
    Route::middleware('noindex')->group(function () {
        Route::get('/private/{file}', 'getPrivateTile')->where('file', '.*')->name('maps.private');
    });
});

Route::controller(RedirectController::class)->prefix('/r')->group(function () {
    Route::get('/{path}', 'redirect')->where('path', '.*')->name('redirect');
});

Route::controller(TerminalController::class)->prefix('/secret')->middleware('noindex')->group(function () {
    Route::get('/', 'index')->name('terminal.index');
    Route::post('/login', 'login')->name('terminal.login');
    Route::post('/sudo', 'sudo')->name('terminal.sudo');
    Route::post('/print', 'print')->name('terminal.print');
});

Route::controller(GameServersController::class)->prefix('/game-servers')->group(function () {
    Route::get('/', 'index')->name('game-servers.index');
    Route::get('/status', 'status')->name('game-servers.status');
});

Route::controller(VotesController::class)->prefix('/votes')->group(function () {
    Route::post('/up', 'upVote')->name('votes.up');
    Route::post('/down', 'downVote')->name('votes.down');
});

Route::controller(MedalsController::class)->prefix('/medals')->group(function () {
    Route::get('/', 'index')->name('medals.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Medals', 'medals.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/{uuid}', 'show')->whereUuid('uuid')->name('medals.show')
            ->breadcrumbs(fn (Trail $trail, $uuid) => $trail->parent('medals.index')->push('Show', route('medals.show', $uuid)));
        Route::get('/players/{uuid}', 'players')->whereUuid('uuid')->name('medals.players');
    });
});

require __DIR__.'/fortify.php';
require __DIR__.'/jetstream.php';
