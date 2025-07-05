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

    <a href="{{ $discordRedirect }}" class="button login-discord">
      Login with Discord
    </a>

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
  </form>
</x-game-auth-layout>
