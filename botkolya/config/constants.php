<?php

return [
    
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'telegram_bot_username' => env('TELEGRAM_BOT_USERNAME'),
    
    'alert_period_seconds' => 15 * 24 * 60 * 60,
    'chat_to_notify' => 430902348, //-1001387085871 (тусим когда сможем)
    'NotNotifyPeriodFromLastPary' => 15 * 24 * 60 * 60,
];
