<x-game-auth-layout>
  <x-slot:title>
    Error
  </x-slot>

  <x-slot:header>
    Error
  </x-slot>

  <h1>{{ $status }}</h1>

  <div class="alert alert--error" style="margin-bottom: 2rem;">
    <div>{{ isset($exception) ? $exception->getMessage() : 'Unknown error' }}</div>
  </div>

  <div>
    <a href="{{ route('game-auth.show-login') }}" class="button"
      style="display: inline-block; width: auto;">
      Back To Login
    </a>
  </div>
</x-game-auth-layout>
