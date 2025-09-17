<?php

namespace App\Http\Requests\JobBans;

use App\Http\Requests\FormRequest;
use App\Http\Requests\Traits\HasGameServer;

class UpdateRequest extends FormRequest
{
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
            'round_id' => 'nullable|integer',
            'job' => 'required',
            'reason' => 'nullable|string',
            'duration' => 'nullable|integer',
        ];
    }
}
