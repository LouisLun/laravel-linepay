<?php

return [
    'ChannelId' => env('LINE_PAY_CHANNEL_ID'),
    'channelSecret' => env('LINE_PAY_CHANNEL_SECRET_KEY'),
    'isSandBox' => env('LINE_PAY_IS_SANDBOX', false),
];
