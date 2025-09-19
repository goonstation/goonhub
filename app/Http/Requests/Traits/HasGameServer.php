<?php

namespace App\Http\Requests\Traits;

use App\Services\CommonRequest;
use Illuminate\Validation\Validator;

trait HasGameServer
{
    public function hookHasGameServer(Validator $validator)
    {
        $validator->addRules([
            'server_id' => [
                $this->serverIdRequired ? 'required' : 'nullable',
                function ($attribute, $value, $fail) {
                    $value = $value === 'all' ? null : $value;

                    if ($value && ! $this->getGameServer()) {
                        $fail('The server ID is invalid.');
                    }
                },
            ],
        ]);

        $validator->after(function (Validator $validator) {
            $serverId = $validator->getValue('server_id');
            $validator->setValue('server_id', $serverId === 'all' ? null : $serverId);
        });
    }

    public function getGameServer()
    {
        return app(CommonRequest::class)->targetServer();
    }

    public function getGameServerGroup()
    {
        return app(CommonRequest::class)->targetServerGroup();
    }
}
