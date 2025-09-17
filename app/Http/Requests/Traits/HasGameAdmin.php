<?php

namespace App\Http\Requests\Traits;

use App\Services\CommonRequest;
use Illuminate\Validation\Validator;

trait HasGameAdmin
{
    public function hookHasGameAdmin(Validator $validator)
    {
        $validator->addRules([
            'game_admin_id' => 'required_without:game_admin_ckey|integer',
            'game_admin_ckey' => 'required_without:game_admin_id|string',
        ]);
    }

    public function getGameAdmin()
    {
        return app(CommonRequest::class)->targetGameAdmin();
    }
}
