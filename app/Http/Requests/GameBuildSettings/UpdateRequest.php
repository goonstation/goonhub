<?php

namespace App\Http\Requests\GameBuildSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'branch' => 'nullable|string',
            'byond_major' => 'nullable|integer',
            'byond_minor' => 'nullable|integer',
            'rustg_version' => 'nullable|string',
            'rp_mode' => 'nullable|boolean',
            'map_id' => 'nullable|string|exists:maps,map_id',
        ];
    }
}
