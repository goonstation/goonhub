<x-game-auth-layout>
  <x-slot:title>
    Validating
  </x-slot>

  <x-slot:header>
    Validating
  </x-slot>

  <style>
    .authed {
      margin: auto 0;
    }

    .spinner {
      width: 4rem;
      height: 4rem;
      margin: 0 auto 2rem auto;
      border: .4rem solid rgba(255, 255, 255, 0.2);
      border-top-color: #ffd125;
      border-radius: 50%;
      -webkit-animation: spin 1s linear infinite;
      animation: spin 1s linear infinite;
    }

    @-webkit-keyframes spin {
      0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
      }

      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @keyframes spin {
      0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
      }

      100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }
  </style>

  <div class="authed">
    <div class="spinner"></div>

    <div class="alert alert--info">
      <p><strong>Validating your credentials...</strong></p>
      <p>This window will close automatically when the validation is complete.</p>
    </div>
  </div>

  @if (Auth::check())
    <script>
      window.parent.postMessage({
        type: 'authenticated',
        user: {
          id: {{ Auth::user()->id }},
          name: '{{ Auth::user()->name }}',
          email: '{{ Auth::user()->email }}',
          session: '{{ Auth::user()->getRememberToken() }}',
        }
      }, '*');
    </script>
  @endif
</x-game-auth-layout>
