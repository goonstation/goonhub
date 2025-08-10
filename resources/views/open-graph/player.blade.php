<x-open-graph-layout>
  <style>
    .stats>div:not(:last-child) {
      margin-bottom: 20px;
    }
  </style>

  <div class="title">
    <strong>
      {{ $data->key ?? $data->ckey }}
    </strong>
  </div>
  <div class="content-wrap">
    <div class="content">
      <div class="stats">
        <div>
          {{ number_format($data->participations_count - $data->participations_rp_count) }}
          <span class="dimmed">Classic Rounds</span>
        </div>
        <div>
          {{ number_format($data->participations_rp_count) }}
          <span class="dimmed">Roleplay Rounds</span>
        </div>
        <div>
          {{ number_format($data->hours_played, 2) }}
          <span class="dimmed">Hours</span>
        </div>
        <div>
          {{ number_format($data->deaths_count) }}
          <span class="dimmed">Deaths</span>
        </div>
      </div>
      @if ($data->firstConnection && $data->firstConnection->created_at)
        <div class="footer">
          <div>
            {!! File::get(resource_path('img/icons/calendar-outline.svg')) !!}
            Started playing {{ $data->firstConnection->created_at->ago() }}
          </div>
        </div>
      @endif
    </div>
    <div class="logo">
      <img src="@base64img(resource_path('img/logo.png'))" width="200" height="200" />
    </div>
  </div>
</x-open-graph-layout>
