<x-game-auth-layout>
  <x-slot:title>
    Authenticated
  </x-slot>

  <x-slot:header>
    Authenticated
  </x-slot>

  <style>
    .authed {
      margin: auto 0;
    }
  </style>

  <div class="authed">
    <div class="alert alert--info">
      <p><strong>Successfully authenticated with Discord!</strong></p>
      <p>You may now close this window.</p>
    </div>
  </div>
</x-game-auth-layout>
