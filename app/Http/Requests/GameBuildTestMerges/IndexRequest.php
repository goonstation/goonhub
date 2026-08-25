<?php

namespace App\Http\Requests\GameBuildTestMerges;

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
            'filters.pr' => 'int',
            'filters.server' => 'string',
            'filters.added_by' => 'string',
            'filters.updated_by' => 'string',
            'filters.commit' => 'string',
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
        ];
    }
}
