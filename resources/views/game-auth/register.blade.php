<x-game-auth-layout>
  <x-slot:title>
    Register
  </x-slot>

  <x-slot:header>
    Register
  </x-slot>

  <form method="POST" action="{{ route('game-auth.register') }}">
    @csrf

    <fieldset>
      <input type="text" name="name" placeholder="Username" value="{{ old('name') }}"
        maxlength="255" pattern="^[a-zA-Z0-9\s]+$"
        title="The username can only contain letters, numbers, and spaces." required />
    </fieldset>

    <fieldset>
      <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
        maxlength="255" required />
    </fieldset>

    <fieldset>
      <input type="password" name="password" placeholder="Password" required />
    </fieldset>

    <fieldset>
      <input type="password" name="password_confirmation" placeholder="Confirm Password" required />
    </fieldset>

    <button type="submit">Register</button>

    <div style="margin-top: 1.5rem;">
      <a href="{{ route('game-auth.show-login') }}">Already have an account?</a>
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
