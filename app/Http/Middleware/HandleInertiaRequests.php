<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\SchemaOrg\Schema;
use Tabuna\Breadcrumbs\Breadcrumbs;
use Tabuna\Breadcrumbs\Crumb;

class HandleInertiaRequests extends Middleware
{
    /**
     * Defines the root template that's loaded on the first page visit.
     *
     * @return string
     */
    public function rootView(Request $request)
    {
        if ($request->route()->getPrefix() === 'game-auth') {
            return 'game-auth/app';
        }

        return 'app';
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Build a meta data object with defaults
     */
    private function getMetaData(Request $request): array
    {
        return [
            'disable' => function () use ($request) {
                return $request->session()->get('no_meta', false);
            },
            'title' => function () use ($request) {
                $title = $request->session()->get('meta_title');
                if ($title) {
                    $title .= ' - '.config('app.name');
                } else {
                    $title = config('app.name');
                }

                return $title;
            },
            'description' => function () use ($request) {
                return $request->session()->get('meta_description');
            },
            'image' => function () use ($request) {
                $image = $request->session()->get('meta_image');
                // if (! $image) {
                //     $image = asset('/storage/img/og.png');
                // }

                return $image;
            },
            'url' => url()->current(),
        ];
    }

    private function getBreadcrumbSchema(): string
    {
        $breadcrumbs = Breadcrumbs::current();
        $breadcrumbs->pop();
        if ($breadcrumbs->isEmpty()) {
            return '';
        }

        $schema = Schema::breadcrumbList();
        $items = [];
        /** @var Crumb $breadcrumb */
        foreach ($breadcrumbs->all() as $key => $breadcrumb) {
            $items[] = Schema::listItem()
                ->name($breadcrumb->title())
                ->item(Schema::thing()->url($breadcrumb->url()))
                ->position($key + 1);
        }

        $schema->itemListElement($items);

        return $schema->toScript();
    }

    private function getSchema(Request $request): string
    {
        $ldJson = $request->session()->get('schema', '');
        $ldJson .= $this->getBreadcrumbSchema();

        return $ldJson;
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'breadcrumbs' => fn () => Breadcrumbs::current(),
            'env' => [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
            'meta' => fn () => $this->getMetaData($request),
            'schema' => fn () => $this->getSchema($request),
        ]);
    }
}
