<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasGameAdmin;
use App\Http\Requests\Traits\HasGameServer;
use Illuminate\Foundation\Http\FormRequest;

class GameBuildTestMergeUpdateRequest extends FormRequest
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
            'pr_id' => 'nullable|integer',
            'commit' => 'nullable|string',
        ];
    }
}
