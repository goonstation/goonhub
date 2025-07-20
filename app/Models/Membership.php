<?php

namespace App\Models;

use Laravel\Jetstream\Membership as JetstreamMembership;

/**
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Membership whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Membership extends JetstreamMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
