<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect as FacadesRedirect;

class RedirectController extends Controller
{
    /**
     * For short URLs. This redirects a visitor to another website.
     */
    public function redirect(Request $request, string $path)
    {
        $redirect = Redirect::where('from', $path)->firstOrFail();
        $redirect = Redirect::withoutAuditing(function () use ($redirect) {
            return $redirect->increment('visits');
        });

        return FacadesRedirect::to($redirect->to);
    }
}
