<x-game-auth-layout>
  <x-slot:title>
    Login
  </x-slot>

  <x-slot:header>
    Login
  </x-slot>

  <style>
    .links {
      display: flex;
      gap: 1rem;
      justify-content: space-between;
      margin-top: 1.5rem;
    }

    .login-discord {
      margin-top: 1rem;
      background: #7289da;
      color: white;
    }

    .login-discord:hover {
      background: #677bc5;
      color: white;
    }

    .login-discord:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>

  <form method="POST" action="{{ route('game-auth.login') }}">
    @csrf

    <fieldset>
      <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
        maxlength="255" required />
    </fieldset>

    <fieldset>
      <input type="password" name="password" placeholder="Password" required />
    </fieldset>

    <button type="submit">Login</button>

    <button class="button login-discord" disabled>
      Login with Discord
    </button>

    <div class="links">
      <a href="{{ route('game-auth.show-forgot') }}">Reset password</a>
      <a href="{{ route('game-auth.show-register') }}">Create an account</a>
    </div>

    @if ($errors->any())
      <div class="alert alert--error" style="margin-top: 1rem;">
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    @php
      $state = Str::random(32);
    @endphp

    @if (config('broadcasting.connections.reverb.key'))
      <script>
        window.DiscordRedirectUrl = '{{ route('game-auth.discord-redirect', $state) }}';
        window.EchoPageState = '{{ $state }}';
        window.EchoConfig = {
          broadcaster: 'reverb',
          key: '{{ config('broadcasting.connections.reverb.key') }}',
          wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
          wsPort: {{ config('broadcasting.connections.reverb.options.port', 80) }},
          wssPort: {{ config('broadcasting.connections.reverb.options.port', 443) }},
          forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS') ? 'true' : 'false' }},
          enabledTransports: ['ws', 'wss'],
        };
      </script>
    @endif

    <script src="{{ Vite::asset('vite/legacy-polyfills-legacy', 'build-game-auth') }}"></script>
    <script src="{{ Vite::asset('resources/js/game-auth-legacy.js', 'build-game-auth') }}"></script>
  </form>
</x-game-auth-layout>
