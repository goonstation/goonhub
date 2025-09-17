<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasGameAdmin;
use Illuminate\Foundation\Http\FormRequest;

class GameBuildTestMergeCreateRequest extends FormRequest
{
    use HasGameAdmin;

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
            'pr_id' => 'required|integer',
            /** Required without server_ids */
            'server_id' => 'required_without:server_ids|string|exists:game_servers,server_id',
            /** Required without server_id */
            'server_ids' => 'required_without:server_id|array|exists:game_servers,server_id',
            'commit' => 'nullable|string',
        ];
    }
}
