<x-open-graph-layout>
  <div class="header">{{ $data->server->name ?? 'Goonstation' }}</div>
  <div class="title">
    <span>#{{ number_format($data->id) }}</span>
    <strong>{{ $data->latestStationName->name ?? 'Space Station 13' }}</strong>
  </div>
  <div class="content-wrap">
    <div class="content">
      <div class="details">
        <div>
          {!! File::get(resource_path('img/icons/game-controller-outline.svg')) !!}
          {{ $data->game_type }} Mode
        </div>
        <div>
          {!! File::get(resource_path('img/icons/map-outline.svg')) !!}
          {{ $data->mapRecord->name ?? $data->map }}
        </div>
      </div>
      @if ($data->created_at)
        <div class="footer">
          <div>
            {!! File::get(resource_path('img/icons/calendar-outline.svg')) !!}
            {{ $data->created_at->format('D, M j, Y g:i A e') }}
          </div>
        </div>
      @endif
    </div>
    <div class="logo">
      <img src="@base64img(resource_path('img/logo.png'))" width="200" height="200" />
    </div>
  </div>
</x-open-graph-layout>
