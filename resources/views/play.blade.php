<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="dark">

  <meta http-equiv="refresh" content="1;url={{ $server->byond_link }}" />

  <meta name="title" content="Play Goonstation" />
  <meta name="description" content="Join {{ $server->name }}">

  <meta property="og:title" content="Play Goonstation">
  <meta property="og:type" content="website">
  <meta property="og:description" content="Join {{ $server->name }}">

  <?= sprintf('<meta name="baggage" content="%s"/>', \Sentry\getBaggage()) ?>
  <?= sprintf('<meta name="sentry-trace" content="%s"/>', \Sentry\getTraceparent()) ?>

  <title>Play Goonstation</title>

  <style>
    html,
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 16px;
      color: #fff;
      background: #303030;
    }

    .info {
      margin: 1em;
      padding: 1em;
      max-width: 500px;
      background: #0f0f0f;
      text-align: center;
      border-radius: 4px;
    }

    .logo {
      display: block;
      width: 100px;
      margin: -65px auto 0;
    }

    h1 {
      margin-top: .25em;
      color: #ffd125;
    }

    a {
      color: #ffd125;
    }
  </style>
</head>

<body>
  <div class="info">
    <img src="{{ asset('/storage/img/logo-goonstation.png') }}" alt="Logo" class="logo" />
    <h1>Launching {{ $server->name }}</h1>
    <p>The game will launch in the background, please check your taskbar.</p>
    <p>
      If the game doesn't automatically launch, please click here:<br>
      <a href="{{ $server->byond_link }}">{{ $server->byond_link }}</a>
    </p>
    <p>
      Are you a new player and have no idea how you got here?<br>
      <a href="https://wiki.ss13.co/Getting_Started#Connecting">Read this getting started guide</a>.
    </p>
  </div>
</body>

</html>
