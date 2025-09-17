<?php

namespace App\Http\Requests\Polls;

use App\Http\Requests\FormRequest;
use App\Http\Requests\Traits\HasGameAdmin;

class StoreRequest extends FormRequest
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
            'question' => 'required',
            'multiple_choice' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
            'options' => 'required|array',
            'options.*' => 'sometimes|required',
            'servers' => 'nullable|array',
            'servers.*' => 'sometimes|required|string',
        ];
    }
}
