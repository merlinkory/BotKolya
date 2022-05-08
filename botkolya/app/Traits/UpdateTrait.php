<?php

namespace App\Traits;
use App\Classes\Commands\NewPartyCommand;
use App\Classes\Commands\ListPartyCommand;
use App\Classes\Commands\BroadcastCommand;
use App\Models\CommandDialog;
use App\Models\Chat;
use App\Models\Admin;
trait UpdateTrait {

    /**
     * 
     * @param array $update
     */
    protected function handleUpdate(array $update) {

        if (isset($update['message']['text'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['message']['new_chat_members'])) {
            $this->addChat($update['message']);
        } elseif (isset($update['message']['left_chat_member'])){
            $this->removeChat($update['message']);
        } elseif (isset ($update['callback_query'])){
            $this->handleCallbackQuery($update['callback_query']);
        }
//        dump($update);
    }

    
    protected function removeChat(array $message){
        
        dump('removeChat');
        $chat = Chat::where('telegram_id', $message['chat']['id'])->get()->first();
        if(!$chat) return false;
        if($message['left_chat_member']['username'] == config('constants.telegram_bot_username')){
            $chat->delete();
        }
    }
    
    /**
     * @title Добавляет в БД запись о чате в который добавили бота
     * @param array $message
     */
    protected function addChat(array $message){   
        dump('addChat');
        foreach ($message['new_chat_members'] as $member){
            if(isset($member['username']) && $member['username'] == config('constants.telegram_bot_username')){
               
                $chat = Chat::updateOrCreate(
                        ['telegram_id' => $message['chat']['id']],
                        ['title' => $message['chat']['title']]
                ); 
                
                break;
            }
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
            case "broadcast" : (new BroadcastCommand)->callback($callback_query,$dialog, $commandData); break;
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

//        dump('handleGroupdMessage', $message);
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
       $cmd->next($dialog, $message);
    }

    /**
     * handle bot text command
     * @param array $message
     * @param string $from
     */
    protected function handleBotCommand(array $message, string $from) {
        
        if(!$this->isAdmin($message)) return false;
        
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
    
    protected function isAdmin(array $message){
     
        $username = $message['from']['username'];
        
        $admin = Admin::where('username',$username)->get()->first();
        
        return ($admin) ? true : false;
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
