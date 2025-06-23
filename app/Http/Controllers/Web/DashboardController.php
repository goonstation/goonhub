<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Health\Facades\Health;

class DashboardController extends Controller
{
    private function getHealth()
    {
        $stores = Health::resultStores();
        /** @var \Spatie\Health\ResultStores\EloquentHealthResultStore */
        $store = $stores->first();
        $checkResults = $store->latestResults();

        return $checkResults ?? [];
    }

    public function index(Request $request)
    {
        return Inertia::render('Dashboard/Index', [
            'health' => fn () => $this->getHealth(),
        ]);
    }
}
