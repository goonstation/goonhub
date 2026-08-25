<?php

return [
    'except' => ['horizon.*', 'ignition.*', 'larecipe.*', 'pulse', 'scramble.*', 'telescope'],

    'groups' => [
        'web' => ['web.*', 'admin.*', 'api.*'],
        'game-auth' => ['game-auth.*', 'password.email'],
    ],
];
