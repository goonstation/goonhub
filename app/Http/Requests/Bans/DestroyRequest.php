<?php

namespace App\Http\Requests\Bans;

use App\Http\Requests\FormRequest;
use App\Http\Requests\Traits\HasGameAdmin;

class DestroyRequest extends FormRequest
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
}
