<?php

namespace App\Http\Requests\JobBans;

use App\Http\Requests\IndexQueryRequest;
use App\Rules\DateRange;

class IndexRequest extends IndexQueryRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            ...parent::rules(),
            'filters.id' => 'int',
            'filters.round' => 'int',
            'filters.game_admin' => 'string',
            'filters.server' => 'string',
            'filters.ckey' => 'string',
            'filters.banned_from_job' => 'string',
            'filters.reason' => 'string',
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
        ];
    }
}
