<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Validation\Validator;

abstract class FormRequest extends BaseFormRequest
{
    public function withValidator(Validator $validator)
    {
        foreach (class_uses(static::class) as $trait) {
            foreach (get_class_methods($trait) as $method) {
                if (str_starts_with($method, 'hook')) {
                    $this->$method($validator);
                }
            }
        }
    }
}
