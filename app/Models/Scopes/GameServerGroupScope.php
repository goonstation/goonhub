<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GameServerGroupScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $group = 'default';

        $user = request()->user();
        if ($user) {
            /** @var \App\Models\PersonalAccessToken|null */
            $token = $user->currentAccessToken();
            if ($token) {
                // foreach ($token->abilities as $ability) {
                //     if (str_starts_with($ability, 'server-group:')) {
                //         $group = str_replace('server-group:', '', $ability);
                //     }
                // }

            }
        }

        $builder->whereHas('group', fn (Builder $query) => $query->where('name', $group));
    }
}
