<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\HasGameAdmin;
use App\Http\Requests\Traits\HasGameServer;
use Illuminate\Foundation\Http\FormRequest;

class GameBuildCancelRequest extends FormRequest
{
    use HasGameAdmin;
    use HasGameServer;

    protected $serverIdRequired = true;

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
            'type' => 'nullable|in:current,queued',
        ];
    }
}
