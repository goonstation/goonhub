<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <title>{{ $title ?? 'Goonhub Auth' }}</title>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="color-scheme" content="dark">

  <?= sprintf('<meta name="baggage" content="%s"/>', \Sentry\getBaggage()) ?>
  <?= sprintf('<meta name="sentry-trace" content="%s"/>', \Sentry\getTraceparent()) ?>

  <style>
    * {
      box-sizing: border-box;
    }

    html {
      height: 100%;
      font-size: 14px;
      font-family: Arial, Helvetica, sans-serif;
      overflow: hidden;
    }

    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      margin: 0;
      padding: 2px;
      background: #0f0f0f;
    }

    .content {
      display: flex;
      flex-direction: column;
      width: 100%;
      border: 1px solid #303030;
      border-radius: 5px;
      text-align: center;
      color: white;
      overflow-y: auto;
    }

    .content-header {
      padding: 1rem;
      border-bottom: 1px solid #303030;
      font-size: 1rem;
      font-weight: bold;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }

    .content-wrap {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      padding: 2rem 2rem;
    }

    .alert {
      padding: 1rem;
      border-radius: 5px;
      text-align: center;
    }

    .alert.alert--success {
      background: rgba(0, 255, 0, 0.1);
      color: #00ff00;
    }

    .alert.alert--error {
      background: rgba(255, 0, 0, 0.1);
      color: #ff0000;
    }

    .alert.alert--info {
      background: rgba(255, 209, 37, 0.1);
      color: #ffd125;
    }

    a {
      font-weight: 600;
      color: #ffd125;
      text-decoration: none;
    }

    a:hover {
      color: #b48e05;
    }

    p {
      line-height: 1.4;
    }

    form {
      height: 100%;
    }

    fieldset {
      margin: 0 0 1rem 0;
      padding: 0;
      border: 0;
    }

    input {
      width: 100%;
      background: rgba(255, 255, 255, 0.07);
      padding: 0 0.75rem;
      border: 0;
      border-radius: 4px;
      font-size: 1rem;
      color: white;
      height: 2.875rem;
      outline: none;
    }

    button,
    .button {
      display: block;
      width: 100%;
      background: #ffd125;
      color: black;
      border: 0;
      vertical-align: middle;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 600;
      text-transform: uppercase;
      text-decoration: none;
      text-align: center;
      padding: 0.75rem 1rem;
      cursor: pointer;
      border-radius: 3px;
    }

    button:hover,
    .button:hover {
      background: #b48e05;
      color: black;
    }
  </style>
</head>

<body>
  <div class="content">
    <div class="content-header">
      {{ $header ?? 'Goonhub Auth' }}
    </div>

    <div class="content-wrap">
      @session('success')
        <div class="alert alert--success" style="margin-bottom: 1rem;">
          {{ $value }}
        </div>
      @endsession

      {{ $slot }}
    </div>
  </div>
</body>

</html>
