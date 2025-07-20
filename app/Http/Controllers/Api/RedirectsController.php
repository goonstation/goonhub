<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\RedirectResource;
use App\Models\Redirect;
use App\Rules\DateRange;
use App\Rules\Range;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class RedirectsController extends Controller
{
    /**
     * List
     *
     * List filtered and paginated redirects
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<RedirectResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.from' => 'string',
            'filters.to' => 'string',
            /**
             * A value, comparison, or range
             *
             * @example 1 or >= 1 or 1-10
             */
            'filters.visits' => new Range,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.created_at' => new DateRange,
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.updated_at' => new DateRange,
        ]);

        return RedirectResource::collection(
            Redirect::indexFilterPaginate()
        );
    }

    /**
     * Add
     *
     * Add a new redirect
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            /**
             * The path to redirect from. Appends to the root redirect URL, for example as `https://goonhub.com/r/foo`
             *
             * @example foo
             */
            'from' => 'required|string',
            /**
             * The URL to redirect to
             *
             * @example https://example.com
             */
            'to' => 'required|url',
        ]);

        $redirect = new Redirect;
        $redirect->from = $data['from'];
        $redirect->to = $data['to'];
        $redirect->save();

        return new RedirectResource($redirect);
    }

    /**
     * Edit
     *
     * Edit an existing redirect
     */
    public function update(Request $request, Redirect $redirect)
    {
        $data = $request->validate([
            /**
             * The path to redirect from. Appends to the root redirect URL, for example as `https://goonhub.com/r/foo`
             *
             * @example foo
             */
            'from' => 'required|string',
            /**
             * The URL to redirect to
             *
             * @example https://example.com
             */
            'to' => 'required|url',
        ]);

        $redirect->from = $data['from'];
        $redirect->to = $data['to'];
        $redirect->save();

        return new RedirectResource($redirect);
    }

    /**
     * Delete
     *
     * Delete an existing redirect
     */
    public function destroy(Redirect $redirect)
    {
        $redirect->delete();

        return ['message' => 'Redirect removed'];
    }
}
