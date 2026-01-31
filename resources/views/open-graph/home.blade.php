<x-open-graph-layout>
  <style>
    body {
      padding: 50px;
    }

    .content-wrap {
      gap: 30px;
    }

    .content {
      flex-grow: 1;
      display: grid;
      grid-template-columns: auto;
      gap: 20px;
    }

    .status {
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
      padding: 15px 20px;
      background: rgba(255, 255, 255, 0.05);
      border-left: 5px solid #ffd125;
      border-radius: 4px;
      font-size: 36px;
    }

    .status__name {
      color: #ffd125;
    }

    .status__error {
      color: red;
      font-size: 1.25em;
      margin: auto 0;
    }

    .status__details {
      display: flex;
      gap: 20px;
      font-size: 0.9em;
    }

    .status__details > span {
      display: inline-block;
      padding-right: 20px;
      border-right: 1px solid rgba(255, 255, 255, 0.2);
    }

    .status__details > span:last-child {
      border-right: 0;
    }

    .status__map {
      position: absolute;
      top: 50%;
      right: 0;
      transform: translate(10%, -50%);
      z-index: -1;
      max-width: 300px;
      filter: blur(1px);
      mask-image: linear-gradient(to left, black 0%, transparent 100%);
    }
  </style>

  <div class="content-wrap">
    <div class="content">
      @foreach ($data['servers'] as $server)
      <div class="status">
        <div class="status__name">{{ $server['name'] }}</div>
        @if (isset($server['error']) && $server['error'])
        <div class="status__error">
          Unable to reach the server.
        </div>
        @else
        <div class="status__details">
          <span>
            {{ Str::ucfirst(isset($server['status']['mode']) ? $server['status']['mode'] : 'Secret') }}
          </span>
          <span class="text-primary">
            {{ $server['status']['players'] }}
            player{{ (int) $server['status']['players'] === 1 ? '' : 's' }}
          </span>
          <span>
            {{-- GAME_STATE_PREGAME = 5 --}}
            @if ((int) $server['status']['gamestate'] <= 5)
            Lobby
            {{-- GAME_STATE_FINISHED = 8 --}}
            @elseif ((int) $server['status']['gamestate'] === 8)
            Ended
            @else
            {{ date("G\h i\m", (int) $server['status']['round_duration']) }}
            @endif
          </span>
          @isset ($server['status']['map_name'])
          <span>{{ $server['status']['map_name'] }}</span>
          @endisset
        </div>
        @endif
        @isset ($server['status']['map_id'])
        @php
          $mapId = str_replace(' ', '', Str::lower($server['status']['map_id']));
        @endphp
        <img src="@base64img(storage_path("app/public/maps/$mapId/thumb.png"))" alt="" class="status__map" />
        @endisset
      </div>
      @endforeach
    </div>
    <div class="logo">
      <img src="@base64img(resource_path('img/logo.png'))" width="200" height="200" />
    </div>
  </div>
</x-open-graph-layout>
