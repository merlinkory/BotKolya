<?php

namespace App\Traits;
use App\Classes\Commands\NewPartyCommand;
use App\Models\CommandDialog;
trait UpdateTrait {

    protected function handleUpdate(array $update) {

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['new_chat_member'])) {
            dump($update);
        }elseif (isset ($update['callback_query'])){
            $this->handleCallbackQuery($update['callback_query']);
        }
    }

    protected function handleCallbackQuery(array $callback_query){
        $commandData = explode(':',$callback_query['data']);
        
        $dialog = CommandDialog::where('telegram_user_id', $callback_query['from']['id'])
                ->where('telegram_chat_id', $callback_query['message']['chat']['id'])->get()->first();
            
        if(!$dialog) return;
        
        switch($commandData[0]){
            case "new" : (new NewPartyCommand)->callback($callback_query,$dialog);
        }
    }
    protected function handleMessage($message) {

        $type = $message['chat']['type'];
        switch ($type) {
            case 'group':
            case 'supergroup':
                $this->handleGroupdMessage($message);
                break;
            case 'private':
                $this->handlePrivateMessage($message);
        }
    }

    protected function handleGroupdMessage(array $message) {

        if ($this->isBotCommand($message)) {
            $this->handleBotCommand($message, 'group');
            return 0;
        }

        dump('handleGroupdMessage', $message);
    }

    protected function handlePrivateMessage(array $message) {

        if ($this->isBotCommand($message)) {
            $this->handleBotCommand($message, 'private');
            return 0;
        }
                      
        $dialog = CommandDialog::where('telegram_user_id', $message['from']['id'])
                ->where('telegram_chat_id', $message['chat']['id'])->get()->first();
        
        if(!$dialog) return;
        
       $command = $dialog->command;
        
       $cmd =  new $command();
       $cmd->next(json_decode($dialog->data, true), $message, $dialog);

//       $dialog->delete();
        dump('handlePrivateMessage', $message);
    }

    /**
     * handle bot text command
     * @param array $message
     * @param string $from
     * @return void
     */
    protected function handleBotCommand(array $message, string $from):void {
        
        $cmd = $this->getBotCommand($message);
        switch ($cmd){
            case "/new": 
                if($from == 'private') (new NewPartyCommand())->start($message);                                    
        }
        //dump('handleBotCommand', $message, $from);
    }
    
    protected function getBotCommand(array $message){
        if(!isset($message['text'])) throw new Exception("text of cmd not found");
        return $message['text'];
    }

    /**
     * 
     * @param array $message
     * @return bool
     */
    protected function isBotCommand(array $message): bool {
        if (isset($message['entities'])) {
            foreach ($message['entities'] as $e)
                if ($e['type'] === 'bot_command')
                    return true;
        }
        return false;
    }

}
