<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        'message' => Gerardojbaez\Messenger\Models\Message::class,
        'thread' => Gerardojbaez\Messenger\Models\MessageThread::class,
        'participant' => Gerardojbaez\Messenger\Models\MessageThreadParticipant::class,
    ],

];
