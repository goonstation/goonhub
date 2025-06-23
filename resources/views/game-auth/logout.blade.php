<x-game-auth-layout>
  <x-slot:title>
    Logging out
  </x-slot>

  <x-slot:header>
    Logging out
  </x-slot>

  <style>
    .logout {
      margin: auto 0;
    }
  </style>

  <div class="logout">
    <div class="alert alert--info">
      <p><strong>Logging out...</strong></p>
      <p>You have been logged out.</p>
    </div>
  </div>

  <script>
    window.parent.postMessage({
      type: 'logout'
    }, '*');
  </script>
</x-game-auth-layout>
