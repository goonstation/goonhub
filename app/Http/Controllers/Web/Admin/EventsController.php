<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $eventTypes = [];
        $filterType = $request->input('filters.type');
        $filteringEvent = null;

        $eventModels = collect(File::allFiles(app_path('Models/Events')))
            ->filter(function ($item) {
                return ! str_ends_with($item->getFilename(), 'BaseEventModel.php');
            })
            ->map(function ($item) {
                $name = str_replace('.php', '', $item->getFilename());

                return "App\\Models\\Events\\$name";
            });

        foreach ($eventModels as $modelName) {
            $model = new $modelName;
            $tableName = $model->getTable();
            $eventTypes[] = $tableName;
            if (! $filterType && ! $filteringEvent) {
                $filteringEvent = $model;
            } elseif ($tableName === $filterType) {
                $filteringEvent = $model;
            }
        }

        $events = [];
        if ($filteringEvent) {
            $events = $filteringEvent->indexFilterPaginate(perPage: 30);
        }

        if ($this->wantsInertia($request)) {
            return Inertia::render('Admin/Events/Index', [
                'eventTypes' => $eventTypes,
                'events' => $events,
            ]);
        } else {
            return $events;
        }
    }
}
