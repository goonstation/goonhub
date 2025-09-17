<?php

namespace App\Http\Requests\Bans;

use App\Http\Requests\FormRequest;
use App\Http\Requests\Traits\HasGameAdmin;
use App\Http\Requests\Traits\HasGameServer;

class StoreRequest extends FormRequest
{
    use HasGameAdmin;
    use HasGameServer;

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
            'round_id' => 'nullable|integer|exists:game_rounds,id',
            'ckey' => 'required_without_all:comp_id,ip|nullable',
            'comp_id' => 'required_without_all:ckey,ip|nullable',
            'ip' => 'required_without_all:ckey,comp_id|nullable|ip',
            'reason' => 'required|string',
            'duration' => 'nullable|integer',
            'requires_appeal' => 'nullable|boolean',
        ];
    }
}
