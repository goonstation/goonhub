<?php

namespace App\Http\Requests\Bans;

use App\Http\Requests\IndexQueryRequest;
use App\Rules\Cidr;
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
            'filters.server' => 'string',
            'filters.admin_ckey' => 'string',
            'filters.reason' => 'string',
            'filters.original_ban_ckey' => 'string',
            'filters.ckey' => 'string',
            'filters.comp_id' => 'string',
            'filters.ip' => new Cidr,
            'filters.requires_appeal' => 'boolean',
            'filters.details' => new Range,
            'filters.created_at' => new DateRange,
            'filters.updated_at' => new DateRange,
            'filters.expires_at' => new DateRange,
            'filters.deleted_at' => new DateRange,
        ];
    }
}
