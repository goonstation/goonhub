<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions\RemoteMusicPermissions;
use App\Helpers\HasAnyAbility;
use App\Http\Controllers\Controller;
use App\Jobs\RemoteMusic;
use App\Services\CommonRequest;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

#[Group('Remote Music')]
class RemoteMusicController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            HasAnyAbility::using(RemoteMusicPermissions::ADD, only: ['store']),
        ];
    }

    /**
     * Play
     *
     * Queue a piece of music from youtube to be played in a given round
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            /**
             * A full youtube video URL, or youtube video ID
             *
             * @example https://www.youtube.com/watch?v=dQw4w9WgXcQ
             */
            'video' => 'required',
            'round_id' => 'required|exists:game_rounds,id',
            'game_admin_ckey' => 'nullable|alpha_num',
        ]);

        $playerAdmin = app(CommonRequest::class)->targetGameAdmin();
        RemoteMusic::dispatch($data['video'], $data['round_id'], $playerAdmin);

        return ['message' => 'Success'];
    }
}
