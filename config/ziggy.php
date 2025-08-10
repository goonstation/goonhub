<?php

return [
    'except' => ['horizon.*', 'ignition.*', 'larecipe.*', 'pulse', 'scramble.*', 'telescope'],

    'groups' => [
        'game-auth' => ['game-auth.*', 'password.email'],
    ],
];
