<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\ModelHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        $eventTypes = [];
        $filterType = $request->input('filters.type');
        $filteringEvent = null;
        $eventModels = ModelHelper::getModels('App\Models\Events');
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
