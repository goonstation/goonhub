<?php

namespace App\Http\Requests\GameAuth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Laravel\Jetstream\Jetstream;

class RegisterRequest extends FormRequest
{
    use PasswordValidationRules;

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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $suffix = Player::AUTH_SUFFIXES['goonhub'];
        $this->merge([
            'ckey' => ckey($this->name.$suffix),
            'email' => strtolower($this->email),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:255'],
            'ckey' => ['required', 'unique:players,ckey'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a username.',
            'name.regex' => 'The username can only contain letters, numbers, and spaces.',
            'name.max' => 'Please enter a username less than 255 characters.',
            'ckey.unique' => 'That username has already been taken.',
            'email.unique' => 'That email has already been taken.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Please enter an email less than 255 characters.',
        ];
    }
}
