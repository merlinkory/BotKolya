<?php

namespace App\Classes\Commands;

use Bot;
use App\Models\CommandDialog;

abstract class BaseCommandWithDialog {
     
    protected function deleteKeyboardMessage($callback) {
        return Bot::deleteMessage([
                    'chat_id' => $callback['message']['chat']['id'],
                    'message_id' => $callback['message']['message_id']
        ]);
    }
    
    protected function deleteDialog(array $message) {

        $dialogs = CommandDialog::where('telegram_user_id', $message['from']['id'])
                ->where('telegram_chat_id', $message['chat']['id'])
                ->get();
        foreach ($dialogs as $dialog) {
            $dialog->delete();
        }
    }

    abstract function start(array $message);
    
    abstract function callback($callback, CommandDialog $dialog, $calback_result);
    
    abstract function next(CommandDialog $dialog, array $message);
}
