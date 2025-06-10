<?php

namespace App\Http\Requests\Events;

use App\Http\Requests\IndexQueryRequest;
use App\Rules\Range;

class ErrorsIndexRequest extends IndexQueryRequest
{
    protected $redirectRoute = 'errors.index';

    protected $errorBag = 'table';

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
        return array_merge(parent::rules(), [
            'sort_by' => 'in:overview_count,overview_round_count,overview_name,file,line',
            'per_page' => 'numeric|min:1',
            'filters.server' => 'string',
            'filters.time_range' => 'in:1week,3days,1day',
            'filters.overview_count' => new Range,
            'filters.overview_round_id' => 'numeric|min:1',
            'filters.overview_round_count' => new Range,
            'filters.overview_name' => 'string',
            'filters.file' => 'string',
            'filters.line' => 'numeric|min:1',
        ]);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'filters.overview_count' => 'count filter',
            'filters.overview_round_id' => 'round id filter',
            'filters.overview_round_count' => 'round count filter',
            'filters.overview_name' => 'name filter',
        ]);
    }
}
