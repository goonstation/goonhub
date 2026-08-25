<?php

namespace App\Http\Requests\Players;

use App\Http\Requests\IndexQueryRequest;
use App\Rules\DateRange;
use App\Rules\Range;

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
            'filters.ckey' => 'string',
            'filters.key' => 'string',
            'filters.name' => 'string',
            'filters.connections_count' => new Range,
            'filters.participations_count' => new Range,
            'filters.byond_version' => 'string',
            'filters.comp_id' => 'string',
            'filters.ip' => 'string',
            'filters.mentor' => 'string',
            'filters.hos' => 'string',
            'filters.whitelist' => 'string',
            'filters.bypass_cap' => 'string',
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
        ];
    }
}
