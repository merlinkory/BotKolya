<?php

namespace App\Traits;
use App\Classes\Commands\NewPartyCommand;
use App\Classes\Commands\ListPartyCommand;
use App\Classes\Commands\BroadcastCommand;
use App\Models\CommandDialog;
trait UpdateTrait {

    /**
     * 
     * @param array $update
     */
    protected function handleUpdate(array $update) {

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['new_chat_member'])) {
            dump($update);
        }elseif (isset ($update['callback_query'])){
            $this->handleCallbackQuery($update['callback_query']);
        }
    }

    /**
     * 
     * @param array $callback_query
     * @return type
     */
    protected function handleCallbackQuery(array $callback_query){
        $commandData = explode(':',$callback_query['data']);
        
        $dialog = CommandDialog::where('telegram_user_id', $callback_query['from']['id'])
                ->where('telegram_chat_id', $callback_query['message']['chat']['id'])->get()->first();
            
        if(!$dialog) return;
        
        switch($commandData[0]){
            case "new" : (new NewPartyCommand)->callback($callback_query,$dialog, $commandData[1]); break;
            case "broadcast" : (new BroadcastCommand)->callback($callback_query,$dialog, $commandData[1]); break;
        }
    }
    
    /**
     * 
     * @param array $message
     */
    protected function handleMessage(array $message) {

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

    /**
     * 
     * @param array $message
     * @return int
     */
    protected function handleGroupdMessage(array $message) {

        if ($this->isBotCommand($message)) {
            $this->handleBotCommand($message, 'group');
            return 0;
        }

        dump('handleGroupdMessage', $message);
    }

    /**
     * 
     * @param array $message
     * @return int
     */
    protected function handlePrivateMessage(array $message) {

        if ($this->isBotCommand($message)) {
            $this->handleBotCommand($message, 'private');
            return 0;
        }
        
        //если была введена не команда, то проверяем если ли открытый диалог
        $dialog = CommandDialog::where('telegram_user_id', $message['from']['id'])
                ->where('telegram_chat_id', $message['chat']['id'])->get()->first();
        
        if(!$dialog) return;
       
        
       //если есть открытый диалог извлекаем, для какой он команды и вызываем метд next 
       $command = $dialog->command;
        
       $cmd =  new $command();
       $cmd->next(json_decode($dialog->data, true), $message, $dialog);
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
                break;
            case "/list": 
                if($from == 'private') (new ListPartyCommand())->start($message);  
                break;
            case "/broadcast": 
                if($from == 'private') (new BroadcastCommand())->start($message);  
                break;
        }
        //dump('handleBotCommand', $message, $from);
    }
    
    /**
     * 
     * @param array $message
     * @return array
     * @throws Exception
     */
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
