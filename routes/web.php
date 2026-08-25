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

Route::controller(OgImageController::class)->prefix('/og-image')->name('og-image.')->group(function () {
    Route::get('/{type}/{id}', 'index')->whereAlpha('type')->whereNumber('id')->name('index')->withoutMiddleware('web');
    Route::get('/preview/{type}/{id}', 'preview')->whereAlpha('type')->whereNumber('id')->name('preview')->middleware([EnsureUserIsAdmin::class]);
});

Route::controller(HomeController::class)->prefix('/')->group(function () {
    Route::get('/', 'index')->name('home')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Home', route('web.home')));
});

Route::controller(AuthController::class)->prefix('/auth')->name('auth.')->group(function () {
    Route::get('/discord-redirect', 'discordRedirect')->name('discord-redirect');
    Route::get('/discord-callback', 'discordCallback')->name('discord-callback');
});

Route::controller(GameAuthController::class)->prefix('/game-auth')->name('game-auth.')
    ->withoutMiddleware([AddLinkHeadersForPreloadedAssets::class])
    ->group(function () {
        Route::middleware('gameauth.state')->group(function () {
            Route::middleware('gameauth.redirect')->group(function () {
                Route::get('/login', 'showLogin')->name('show-login')
                    ->breadcrumbs(fn (Trail $trail) => $trail->push('Login', route('web.game-auth.show-login')));
                Route::post('/login', 'login')->name('login');
                Route::get('/register', 'showRegister')->name('show-register')
                    ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.game-auth.show-login')->push('Register', route('web.game-auth.show-register')));
                Route::post('/register', 'register')->name('register');
                Route::get('/forgot', 'showForgot')->name('show-forgot')
                    ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.game-auth.show-login')->push('Forgot Password', route('web.game-auth.show-forgot')));
            });
            Route::get('/authed', 'authed')->name('authed');
            Route::get('/discord-redirect', 'discordRedirect')->name('discord-redirect');
            Route::get('/discord-callback', 'discordCallback')->name('discord-callback');
        });
        Route::get('/logout', 'logout')->name('logout');
        Route::get('/error', 'showError')->name('error');
    });

Route::controller(ChangelogController::class)->prefix('/changelog')->group(function () {
    Route::get('/', 'index')->name('changelog')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Changelog', 'web.changelog'));
});

Route::controller(PlayersController::class)->prefix('/players')->name('players.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Players', 'web.players.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/search', 'search')->name('search')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.players.index')->push('Search', 'web.players.search'));
        Route::get('/{player}', 'show')->whereNumber('player')->name('show')
            ->breadcrumbs(fn (Trail $trail, $player) => $trail->parent('web.players.search')->push('Show', route('web.players.show', $player)));
        Route::get('/{ckey}', 'showByCkey')->name('show-by-ckey');
    });
});

Route::controller(RoundsController::class)->prefix('/rounds')->name('rounds.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Rounds', 'web.rounds.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/{round}', 'show')->whereNumber('round')->name('show')
            ->breadcrumbs(fn (Trail $trail, $round) => $trail->parent('web.rounds.index')->push('Show', route('web.rounds.show', $round)));
    });
});

Route::prefix('/events')->name('events.')->group(function () {
    Route::controller(EventsController::class)->prefix('/')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->push('Events', 'web.events.index'));
        Route::get('/stats', 'stats')->name('stats');
    });

    Route::controller(DeathsController::class)->prefix('/deaths')->name('deaths.')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.events.index')->push('Deaths', 'web.events.deaths.index'));
        Route::get('/{death}', 'show')->whereNumber('death')->name('show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $death) => $trail->parent('web.events.deaths.index')->push('Show', route('web.events.deaths.show', $death)));
    });

    Route::controller(TicketsController::class)->prefix('/tickets')->name('tickets.')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.events.index')->push('Tickets', 'web.events.tickets.index'));
        Route::get('/{ticket}', 'show')->whereNumber('ticket')->name('show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $ticket) => $trail->parent('web.events.tickets.index')->push('Show', route('web.events.tickets.show', $ticket)));
    });

    Route::controller(FinesController::class)->prefix('/fines')->name('fines.')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.events.index')->push('Fines', 'web.events.fines.index'));
        Route::get('/{fine}', 'show')->whereNumber('fine')->name('show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $fine) => $trail->parent('web.events.fines.index')->push('Show', route('web.events.fines.show', $fine)));
    });

    Route::controller(AntagsController::class)->prefix('/antags')->name('antags.')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.events.index')->push('Antagonists', 'web.events.antags.index'));
        Route::get('/{antag}', 'show')->whereNumber('antag')->name('show')->middleware('noindex')
            ->breadcrumbs(fn (Trail $trail, $antag) => $trail->parent('web.events.antags.index')->push('Show', route('web.events.antags.show', $antag)));
    });

    Route::controller(ErrorsController::class)->prefix('/errors')->name('errors.')->group(function () {
        Route::get('/', 'index')->name('index')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('web.events.index')->push('Errors', 'web.events.errors.index'));
    });
});

Route::controller(MapsController::class)->prefix('/maps')->name('maps.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Maps', 'web.maps.index'));
    Route::get('/{map}', 'show')->name('show')
        ->breadcrumbs(fn (Trail $trail, $map) => $trail->parent('web.maps.index')->push('Show', route('web.maps.show', $map)));
    Route::middleware('noindex')->group(function () {
        Route::get('/private/{file}', 'getPrivateTile')->where('file', '.*')->name('private');
    });
});

Route::controller(RedirectController::class)->prefix('/r')->group(function () {
    Route::get('/{path}', 'redirect')->where('path', '.*')->name('redirect');
});

Route::controller(TerminalController::class)->prefix('/secret')->middleware('noindex')->name('terminal.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/login', 'login')->name('login');
    Route::post('/sudo', 'sudo')->name('sudo');
    Route::post('/print', 'print')->name('print');
});

Route::controller(GameServersController::class)->prefix('/game-servers')->name('game-servers.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/status', 'status')->name('status');
});

Route::controller(VotesController::class)->prefix('/votes')->name('votes.')->group(function () {
    Route::post('/up', 'upVote')->name('up');
    Route::post('/down', 'downVote')->name('down');
});

Route::controller(MedalsController::class)->prefix('/medals')->name('medals.')->group(function () {
    Route::get('/', 'index')->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push('Medals', 'web.medals.index'));
    Route::middleware('noindex')->group(function () {
        Route::get('/{uuid}', 'show')->whereUuid('uuid')->name('show')
            ->breadcrumbs(fn (Trail $trail, $uuid) => $trail->parent('web.medals.index')->push('Show', route('web.medals.show', $uuid)));
        Route::get('/players/{uuid}', 'players')->whereUuid('uuid')->name('players');
    });
});

require __DIR__.'/fortify.php';
require __DIR__.'/jetstream.php';
