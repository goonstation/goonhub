<?php

namespace App\Http\Requests\GameBuilds;

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
            'filters.server' => 'string',
            'filters.started_by' => 'string',
            'filters.branch' => 'string',
            'filters.commit' => 'string',
            'filters.map_id' => 'string',
            'filters.failed' => 'boolean',
            'filters.cancelled' => 'boolean',
            'filters.map_switch' => 'boolean',
            'filters.cancelled_by' => 'string',
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
            'filters.ended_at' => new DateRange,
        ];
    }
}
