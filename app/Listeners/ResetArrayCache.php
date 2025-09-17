<?php

namespace App\Listeners;

class ResetArrayCache
{
    /**
     * Handle the event.
     *
     * @param  mixed  $event
     */
    public function handle($event): void
    {
        if (! $event->sandbox->resolved('cache')) {
            return;
        }

        $driver = $event->sandbox->make('cache')->driver('array');
        $driver->clear();
    }
}
