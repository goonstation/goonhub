<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;

/**
 * @property int $id
 * @property int $team_id
 * @property string $email
 * @property string|null $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\TeamInvitation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TeamInvitation extends JetstreamTeamInvitation
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}
