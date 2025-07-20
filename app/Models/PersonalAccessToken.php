<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array<array-key, mixed>|null $abilities
 * @property mixed|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereAbilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereTokenableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereTokenableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PersonalAccessToken whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected function lastUsedAt(): Attribute
    {
        return Attribute::make(
            set: function (mixed $value): void {
                // disable updating the last_used_at attribute as it's not used

            },
        );
    }
}
