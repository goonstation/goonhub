<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ErrorsIndexRequest;
use App\Traits\ManagesErrorSummary;
use Inertia\Inertia;

class ErrorsController extends Controller
{
    use ManagesErrorSummary;

    public function index(ErrorsIndexRequest $request)
    {
        $this->setMeta(title: 'Errors', description: 'Review recent errors that occurred ingame');

        return Inertia::render('Events/Errors/Index', [
            'errors' => Inertia::lazy(fn () => $this->getErrorSummary($request)),
        ]);
    }
}
