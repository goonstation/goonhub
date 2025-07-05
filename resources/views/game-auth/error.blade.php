<x-game-auth-layout>
  <x-slot:title>
    Error
  </x-slot>

  <x-slot:header>
    Error
  </x-slot>

  {{-- <h1>{{ $status ?? 500 }}</h1> --}}

  @php
    $hasError = isset($exception) || $errors->any();
  @endphp

  <div class="alert alert--error" style="margin-bottom: 2rem;">
    @if ($hasError)
      @isset($exception)
        <div>{{ $exception->getMessage() }}</div>
        {{-- <div>{{ $exception->getTraceAsString() }}</div> --}}
      @endisset
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      @endif
    @else
      <div>An unknown error occurred.</div>
    @endif
  </div>

  <div>
    <a href="{{ route('game-auth.show-login') }}" class="button"
      style="display: inline-block; width: auto;">
      Back To Login
    </a>
  </div>
</x-game-auth-layout>
