<?php

namespace App\Http\Requests\GameRounds;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'server_id' => 'required|string',
            'map' => 'required|string',
            'rp_mode' => 'nullable|boolean',
            'test_merges' => 'nullable|array',
            'test_merges.*' => 'sometimes|required|numeric',
        ];
    }
}
