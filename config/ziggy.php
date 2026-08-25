<?php

return [
    'except' => ['horizon.*', 'ignition.*', 'larecipe.*', 'pulse', 'scramble.*', 'telescope'],

    'groups' => [
        'game-auth' => ['game-auth.*', 'password.email'],
    ],

    'output' => [
        'script' => App\Helpers\ZiggyScript::class,
        'merge_script' => App\Helpers\ZiggyMergeScript::class,
    ],
];
