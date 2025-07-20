<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\PollAnswerResource;
use App\Http\Resources\PollOptionResource;
use App\Http\Resources\PollResource;
use App\Models\GameAdmin;
use App\Models\Poll;
use App\Models\PollAnswer;
use App\Models\PollOption;
use App\Rules\DateRange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class PollsController extends Controller
{
    private function populatePollResults($poll)
    {
        // The ordering of options is important ok
        $poll->offsetSet('options', $poll->options->sortBy('position'));

        $totalAnswers = 0;
        $winningOption = null;
        foreach ($poll->options as $option) {
            $totalAnswers += $option->answers_count;

            // See what option is winning
            if (! $winningOption || $winningOption->answers_count < $option->answers_count) {
                $winningOption = $option;
            }

            // Flatten answers array into just player IDs
            $answers = [];
            foreach ($option->answers as $answer) {
                $answers[] = $answer->player_id;
            }
            $option->offsetSet('answers', $answers);
        }

        $poll->offsetSet('total_answers', $totalAnswers);
        $poll->offsetSet('winning_option_id', $winningOption ? $winningOption->id : $winningOption);

        return $poll;
    }

    /**
     * List
     *
     * List paginated and filtered polls
     *
     * @return AnonymousResourceCollection<LengthAwarePaginator<PollResource>>
     */
    public function index(IndexQueryRequest $request)
    {
        $request->validate([
            'filters.id' => 'int',
            'filters.game_admin' => 'string',
            'filters.question' => 'string',
            /** true or false */
            'filters.active' => 'string',
            'filters.servers' => 'nullable|array',
            /**
             * A date or date range
             *
             * @example 2023/01/30 12:00:00 - 2023/02/01 12:00:00
             */
            'filters.expires_at' => new DateRange,
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

        $pollsPaged = Poll::with([
            'gameAdmin',
            'options' => function ($query) {
                $query->withCount('answers')
                    ->with(['answers' => function ($q) {
                        $q->select('poll_option_id', 'player_id');
                    }]);
            },
        ])->indexFilterPaginate();

        $polls = PollResource::collection($pollsPaged);
        foreach ($polls as $poll) {
            $poll = $this->populatePollResults($poll);
        }

        return $polls;
    }

    /**
     * Show
     *
     * Get a specific poll
     */
    public function show(int $poll)
    {
        $poll = Poll::with([
            'options' => function ($query) {
                $query->withCount('answers')
                    ->with(['answers' => function ($q) {
                        $q->select('poll_option_id', 'player_id');
                    }]);
            },
        ])
            ->where('id', $poll)
            ->firstOrFail();

        $poll = $this->populatePollResults($poll);

        return PollResource::make($poll);
    }

    /**
     * Add
     *
     * Add a new poll
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'game_admin_ckey' => 'nullable|string|exists:game_admins,ckey',
            'question' => 'required',
            'multiple_choice' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
            'options' => 'required|array',
            'options.*' => 'sometimes|required',
            'servers' => 'nullable|array',
            'servers.*' => 'sometimes|required|string',
        ]);

        $gameAdmin = null;
        if (isset($data['game_admin_ckey'])) {
            $gameAdmin = GameAdmin::where('ckey', $data['game_admin_ckey'])->first();
        }

        $poll = new Poll;
        $poll->game_admin_id = $gameAdmin ? $gameAdmin->id : null;
        $poll->question = $data['question'];
        $poll->multiple_choice = isset($data['multiple_choice']) ? $data['multiple_choice'] : false;
        $poll->expires_at = isset($data['expires_at']) ? $data['expires_at'] : null;
        $poll->servers = isset($data['servers']) ? json_encode($data['servers']) : null;
        $poll->save();

        foreach ($data['options'] as $key => $option) {
            $pollOption = new PollOption;
            $pollOption->option = $option;
            $pollOption->position = $key + 1;
            $poll->options()->save($pollOption);
        }

        return new PollResource($poll);
    }

    /**
     * Edit
     *
     * Edit an existing poll
     */
    public function update(Request $request, Poll $poll)
    {
        $data = $request->validate([
            'question' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'servers' => 'nullable|array',
            'servers.*' => 'sometimes|required|string',
        ]);

        if (array_key_exists('question', $data) && ! is_null($data['question'])) {
            $poll->question = $data['question'];
        }
        if (array_key_exists('expires_at', $data)) {
            $poll->expires_at = $data['expires_at'];
        }
        if (array_key_exists('servers', $data)) {
            $poll->servers = is_null($data['servers']) ? null : json_encode($data['servers']);
        }
        $poll->save();

        return new PollResource($poll);
    }

    /**
     * Delete
     *
     * Delete an existing poll
     */
    public function destroy(Poll $poll)
    {
        $poll->delete();

        return ['message' => 'Poll removed'];
    }

    /**
     * Add option
     *
     * Add a new option to an existing poll
     */
    public function addOption(Request $request, Poll $poll)
    {
        $data = $request->validate([
            'option' => 'required',
        ]);

        $currentOptionCount = PollOption::where('poll_id', $poll->id)->count();

        $pollOption = new PollOption;
        $pollOption->poll_id = $poll->id;
        $pollOption->option = $data['option'];
        $pollOption->position = $currentOptionCount + 1;
        $pollOption->save();

        return new PollOptionResource($pollOption);
    }

    /**
     * Update option
     *
     * Update an existing poll option
     */
    public function updateOption(Request $request, PollOption $pollOption)
    {
        $data = $request->validate([
            'option' => 'required',
            'position' => 'nullable|numeric',
        ]);

        $pollOption->option = $data['option'];
        $pollOption->position = isset($data['position']) && $data['position'] ? $data['position'] : $pollOption->position;
        $pollOption->save();

        return new PollOptionResource($pollOption);
    }

    /**
     * Delete option
     *
     * Delete an existing poll option
     */
    public function destroyOption(PollOption $pollOption)
    {
        $pollOption->delete();

        return ['message' => 'Poll option removed'];
    }

    /**
     * Pick option
     *
     * Register that a player picked a poll option
     */
    public function pickOption(Request $request, PollOption $pollOption)
    {
        $data = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        // Check if poll is expired
        if ($pollOption->poll->expires_at && $pollOption->poll->expires_at->isPast()) {
            return response()->json(['message' => 'That poll is no longer active.'], 400);
        }

        $existingAnswers = PollAnswer::where('player_id', $data['player_id'])
            ->whereRelation('option', 'poll_id', '=', $pollOption->poll->id)
            ->get();

        // Can't pick the same thing
        foreach ($existingAnswers as $answer) {
            if ($answer->poll_option_id === $pollOption->id) {
                return response()->json(['message' => 'You have already voted for that option.'], 400);
            }
        }

        // Clear their previous pick if not multiple
        if (! $pollOption->poll->multiple_choice) {
            /** @var PollAnswer|null */
            $existingAnswer = $existingAnswers->first();
            if ($existingAnswer) {
                $existingAnswer->delete();
            }
        }

        $answer = new PollAnswer;
        $answer->poll_option_id = $pollOption->id;
        $answer->player_id = $data['player_id'];
        $answer->save();

        return new PollAnswerResource($answer);
    }

    /**
     * Unpick option
     *
     * Register that a player removed their pick of a poll option
     */
    public function unpickOption(Request $request, PollOption $pollOption)
    {
        $data = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $existingAnswers = PollAnswer::where('player_id', $data['player_id'])
            ->whereRelation('option', 'poll_id', '=', $pollOption->poll->id)
            ->get();

        $deleted = false;
        foreach ($existingAnswers as $answer) {
            if ($answer->poll_option_id === $pollOption->id) {
                $answer->delete();
                $deleted = true;
                break;
            }
        }

        if ($deleted) {
            return ['message' => 'Poll pick removed.'];
        } else {
            return ['message' => 'You haven\'t picked this option yet.'];
        }
    }
}
