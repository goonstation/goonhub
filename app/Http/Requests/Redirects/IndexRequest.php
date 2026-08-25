<?php

namespace App\Http\Requests\Redirects;

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
            'filters.from' => 'string',
            'filters.to' => 'string',
            'filters.visits' => new Range,
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
        ];
    }
}
