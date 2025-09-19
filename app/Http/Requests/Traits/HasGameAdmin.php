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

        $validator->after(function (Validator $validator) {
            if (! $this->getGameAdmin()) {
                if ($validator->getValue('game_admin_id')) {
                    $validator->errors()->add('game_admin_id', 'Game admin not found.');
                }
                if ($validator->getValue('game_admin_ckey')) {
                    $validator->errors()->add('game_admin_ckey', 'Game admin not found.');
                }
            }
        });
    }

    public function getGameAdmin()
    {
        return app(CommonRequest::class)->targetGameAdmin();
    }
}
