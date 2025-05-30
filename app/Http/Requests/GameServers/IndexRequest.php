<?php

namespace App\Http\Requests\GameServers;

use App\Http\Requests\IndexQueryRequest;

class IndexRequest extends IndexQueryRequest
{
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
            'sort_by' => 'in:id,server,active,name',
            'filters.server' => 'string',
            'filters.active' => 'boolean',
            'filters.issuer' => 'string',
        ]);
    }
}
