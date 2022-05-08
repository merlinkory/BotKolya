<?php
namespace App\Classes\Commands;

use Bot;
use App\Models\CommandDialog;
use App\Models\Chat;

class BroadcastCommand extends BaseCommandWithDialog {
    public function start(array $message){
         
        $this->deleteDialog($message);
        
        $from = $message['from']['id'];
        Bot::send($from, 'Введине текст');

        $data['step'] = 'save_message';
        $dialog = new CommandDialog();
        $dialog->telegram_user_id = $from;
        $dialog->telegram_chat_id = $message['chat']['id'];
        $dialog->command = 'App\Classes\Commands\BroadcastCommand';
        $dialog->data = json_encode($data);
        $dialog->save();
        
    }
    
       public function next(CommandDialog $dialog, array $message) {

        $this->dialog = $dialog;
        $data = json_decode($dialog->data, true);
        
        switch ($data['step']) {
            case 'save_message':
                $this->saveMessage($dialog, $message);
                break;
            case 'save_chat_dist':
                $this->saveChatDist($dialog, $message);
                break;            
        }
    }
    
      protected function saveMessage(CommandDialog $dialog, $message) {

        $data = json_decode($dialog->data, true);
        
        $data['message'] = $message['text'];
        $data['step'] = "save_chat_dist";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

        // Получаем список доступных чатов
        $chats = Chat::All();
        
        $buttons;
              
        foreach ($chats as $chat) {
            $buttons[][] = [
                'text' => $chat->title,
                'callback_data' => "broadcast:select_chat:{$chat->telegram_id}"
            ];
        }
        $keyboard['inline_keyboard'] = $buttons;
        
        Bot::sendMessage([
            'chat_id' => $dialog->telegram_chat_id,
            'text' => 'Введите чат назначения',
            'reply_markup' => $keyboard
        ]);
    }
    
      protected function saveChatDist(CommandDialog $dialog, $message) {

        $this->setChat($message['text'], $this->dialog);        
    }
    
     public function callback($callback, CommandDialog $dialog, $callback_result) {

         $data = json_decode($dialog->data, true);
        if ($callback_result[1] == 'confirm' && isset($data['message']) && isset($data['chat'])) {


            Bot::sendMessage([
                'chat_id' => $data['chat'],
                'text' => $data['message'],
            ]);
            
            $dialog->delete();
            
        } else if ($callback_result[1] == 'select_chat') {
            
            $data = json_decode($dialog->data, true);
            
            $this->setChat($callback_result[2], $dialog);
                       
        }
        $this->deleteKeyboardMessage($callback);
    }
    private function setChat(int $target_chat_id, CommandDialog $dialog) {

        $data = json_decode($dialog->data, true);
        $data['chat'] = $target_chat_id;
        $data['step'] = "confirm";

        $dialog->data = json_encode($data);
        $dialog->save();

        $buttons[] = [[
        'text' => 'Подтвердить',
        'callback_data' => "broadcast:confirm"
            ],
            [
                'text' => 'Отменить',
                'callback_data' => "broadcast:cancel"
        ]];
        $keyboard['inline_keyboard'] = $buttons;

        Bot::sendMessage([
            'chat_id' => $dialog->telegram_chat_id,
            'text' => 'Подтвердите отправку сообщения',
            'reply_markup' => $keyboard
        ]);
    }

}
