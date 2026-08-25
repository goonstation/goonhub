<?php

namespace App\Http\Requests\GameBuildSettings;

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
            'filters.branch' => 'string',
            'filters.byond_major' => 'int',
            'filters.byond_minor' => 'int',
            'filters.rustg_version' => 'string',
            'filters.rp_mode' => 'boolean',
            'filters.map_id' => 'string',
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
        ];
    }
}
