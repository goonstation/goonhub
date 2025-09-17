<?php

namespace App\Http\Requests\Bans;

use App\Http\Requests\FormRequest;

class CheckRequest extends FormRequest
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
            'ckey' => 'required_without_all:comp_id,ip,player_id|string|nullable',
            'comp_id' => 'required_without_all:ckey,ip,player_id|string|nullable',
            'ip' => 'required_without_all:ckey,comp_id,player_id|ip|nullable',
            'player_id' => 'required_without_all:ckey,comp_id,ip|integer|nullable',
        ];
    }
}
