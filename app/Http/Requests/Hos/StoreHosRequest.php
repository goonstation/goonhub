<?php

namespace App\Http\Requests\Hos;

use Illuminate\Foundation\Http\FormRequest;

class StoreHosRequest extends FormRequest
{
    protected $errorBag = 'table';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'player_ids' => 'required|array|min:1',
            'player_ids.*' => 'required|integer|distinct|exists:players,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'player_ids.required' => 'At least one player must be selected',
            'player_ids.*.exists' => 'One or more selected players do not exist',
            'player_ids.*.integer' => 'Invalid player ID format',
            'player_ids.*.distinct' => 'Duplicate players were selected',
        ];
    }
}
