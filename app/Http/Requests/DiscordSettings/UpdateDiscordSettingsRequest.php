<?php

namespace App\Http\Requests\DiscordSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscordSettingsRequest extends FormRequest
{
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
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'exists:discord_settings,key'],
        ];
    }
}
