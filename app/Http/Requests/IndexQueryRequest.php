<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Str;

class IndexQueryRequest extends FormRequest
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
            /**
             * Filter the results by column and value pairs
             *
             * @example filters[id]=1
             */
            'filters' => 'array',
            /**
             * What column to sort results by
             *
             * @example id
             */
            'sort_by' => 'string',
            /**
             * Which direction to sort the results by
             *
             * @example true
             */
            'descending' => 'in:true,false,1,0',
            /**
             * How many items to show per page
             *
             * @example 30
             */
            'per_page' => [
                'numeric',
                'min:1',
                function (string $attribute, mixed $value, Closure $fail) {
                    $user = Auth::user();
                    if (! $user || ! $user->isAdmin()) {
                        if ($value > 100) {
                            $fail('The per page must be between 1 and 100.');
                        }
                    }
                },
            ],
            /**
             * What page of results to display
             *
             * @example 1
             */
            'page' => 'numeric|min:1',
        ];
    }

    public function attributes()
    {
        $attributes = [
            'sort_by' => 'sort by',
            'per_page' => 'per page',
        ];

        foreach ($this->rules() as $key => $rule) {
            if (Str::startsWith($key, 'filters.')) {
                $field = Str::remove('filters.', $key);
                $field = Str::replace('_', ' ', $field);
                $attributes[$key] = "$field filter";
            }
        }

        return $attributes;
    }
}
