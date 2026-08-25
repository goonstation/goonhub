<?php

namespace App\Http\Requests\GameBuildSettings;

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
            'server_id' => 'required|string|exists:game_servers,server_id|unique:game_build_settings,server_id',
            'branch' => 'required|string',
            'byond_major' => 'required|integer',
            'byond_minor' => 'required|integer',
            'rustg_version' => 'required|string',
            'rp_mode' => 'nullable|boolean',
            'map_id' => 'nullable|string|exists:maps,map_id',
        ];
    }
}
