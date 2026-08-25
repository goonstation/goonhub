<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;
use Spatie\SchemaOrg\Type;

class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Set various meta data properties
     *
     * @throws BindingResolutionException
     */
    public function setMeta(string $title = '', string $description = '', array $image = []): void
    {
        if ($title) {
            Session::now('meta_title', $title);
        }

        if ($description) {
            Session::now('meta_description', $description);
        }

        if (isset($image['type']) && isset($image['key'])) {
            Session::now('meta_image', route('web.og-image.index', [$image['type'], $image['key']]));
        }
    }

    public function setSchema(Type $schema)
    {
        Session::now('schema', $schema->toScript());
    }
}
