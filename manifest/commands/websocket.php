<?php

return [

    'web:websocket' => [
        \App\WebSocket\Commands\WebSocketCommand::class,
        'usage'   => "\tStart a micro service",
        'options' => [
            [['d', 'daemon'], 'usage' => "Run in the background"],
        ],
    ],

];
