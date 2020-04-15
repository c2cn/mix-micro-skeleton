<?php

return [

    'grpc:start' => [
        \App\Grpc\Commands\StartCommand::class,
        'description' => "Start service",
        'options'     => [
            [['d', 'daemon'], 'description' => "\tRun in the background"],
            [['h', 'host'], 'description' => "\tListen to the specified host"],
            [['p', 'port'], 'description' => "\tListen to the specified tcp port"],
            [['r', 'reuse-port'], 'description' => "Reuse port in multiple processes"],
        ],
    ],

];