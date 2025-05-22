<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ $page['props']['meta']['title'] }}</title>

        @if (!$page['props']['meta']['disable'])
          <meta property="og:type" content="website" />

          @if (!empty($page['props']['meta']['title']))
            <meta inertia name="title" content="{{ $page['props']['meta']['title'] }}" />
            <meta inertia property="og:title" content="{{ $page['props']['meta']['title'] }}">
            <meta inertia property="twitter:title" content="{{ $page['props']['meta']['title'] }}" />
          @endif

          @if (!empty($page['props']['meta']['description']))
            <meta inertia name="description" content="{{ $page['props']['meta']['description'] }}">
            <meta inertia property="og:description" content="{{ $page['props']['meta']['description'] }}">
            <meta inertia property="twitter:description" content="{{ $page['props']['meta']['description'] }}" />
          @endif

          @if (!empty($page['props']['meta']['url']))
            <meta inertia property="og:url" content="{{ $page['props']['meta']['url'] }}">
            <meta inertia property="twitter:url" content="{{ $page['props']['meta']['url'] }}" />
          @endif

          @if (!empty($page['props']['meta']['image']))
            <meta inertia property="og:image" content="{{ $page['props']['meta']['image'] }}">
            <meta property="twitter:card" content="summary_large_image" />
            <meta inertia property="twitter:image" content="{{ $page['props']['meta']['image'] }}" />
          @endif
        @endif

        <?= sprintf('<meta name="baggage" content="%s"/>', \Sentry\getBaggage()); ?>
        <?= sprintf('<meta name="sentry-trace" content="%s"/>', \Sentry\getTraceparent()); ?>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @if ($page['props']['schema'])
          {!! $page['props']['schema'] !!}
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @if (config('broadcasting.connections.reverb.key'))
        <script type="module">
          window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 80) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 443) }},
            forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS') ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
          })
        </script>
        @endif
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
