<x-game-auth-layout>
  <x-slot:title>
    Password Reset
  </x-slot>

  <x-slot:header>
    Password Reset
  </x-slot>

  <form method="POST" action="{{ route('password.email') }}">
    @csrf

    @session('status')
      <div class="alert alert--success" style="margin-bottom: 1rem;">
        {{ $value }}
      </div>
    @endsession

    <fieldset>
      <input type="email" name="email" placeholder="Email" maxlength="255" required />
    </fieldset>

    <button type="submit">Reset Password</button>

    <div style="margin-top: 1.5rem;">
      <a href="{{ route('game-auth.show-login') }}">Remembered your password?</a>
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
