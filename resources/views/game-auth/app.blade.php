<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="color-scheme" content="dark">

  <title inertia>{{ $page['props']['meta']['title'] }}</title>

  <?= sprintf('<meta name="baggage" content="%s"/>', \Sentry\getBaggage()) ?>
  <?= sprintf('<meta name="sentry-trace" content="%s"/>', \Sentry\getTraceparent()) ?>

  @routes('game-auth')
  @vite(['resources/game-auth/js/app.js', "resources/game-auth/js/Pages/{$page['component']}.vue"], 'build-game-auth')
  @inertiaHead
</head>

<body>
  @inertia
</body>

</html>
