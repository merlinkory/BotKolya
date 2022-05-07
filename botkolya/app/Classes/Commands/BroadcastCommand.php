<?php
namespace App\Classes\Commands;

use Bot;
use App\Models\CommandDialog;

class BroadcastCommand {
    public function start(array $message){
         
        $dialogs = CommandDialog::where('telegram_user_id',$message['from']['id'])
                ->where('telegram_chat_id',$message['chat']['id'])
                ->get();
        foreach ($dialogs as $dialog){
            $dialog->delete();
        }
        
        $from = $message['from']['id'];
        Bot::send($from, 'Введине текст');

        $data['chat_id'] = $from;
        $data['step'] = 'save_message';
        $dialog = new CommandDialog();
        $dialog->telegram_user_id = $from;
        $dialog->telegram_chat_id = $message['chat']['id'];
        $dialog->command = 'App\Classes\Commands\BroadcastCommand';
        $dialog->data = json_encode($data);
        $dialog->save();
        
    }
    
       public function next(array $data, array $message, CommandDialog $dialog) {

        $this->dialog = $dialog;

        switch ($data['step']) {
            case 'save_message':
                $this->saveMessage($data, $message);
                break;
            case 'save_chat_dist':
                $this->saveChatDist($data, $message);
                break;            
        }
    }
    
      protected function saveMessage($data, $message) {

        $data['message'] = $message['text'];
        $data['step'] = "save_chat_dist";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

        Bot::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => 'Введите чат назначения',
        ]);
    }
    
      protected function saveChatDist($data, $message) {

        $data['chat'] = $message['text'];
        $data['step'] = "confirm";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

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
            'chat_id' => $data['chat_id'],
            'text' => 'Подтвердите отправку сообщения',
            'reply_markup' => $keyboard
        ]);
    }
    
     public function callback($callback, CommandDialog $dialog, $calback_result) {

         $data = json_decode($dialog->data, true);
        if ($calback_result == 'confirm' && isset($data['message']) && isset($data['chat'])) {
            
                  
             Bot::sendMessage([
            'chat_id' => $data['chat'],
            'text' => $data['message'],
        ]);
        }
        $this->deleteKeyboardMessage($callback);
        $dialog->delete();
    }
    
    
     //TODO перенести в updateTrate
    protected function deleteKeyboardMessage($callback) {
        return Bot::deleteMessage([
                    'chat_id' => $callback['message']['chat']['id'],
                    'message_id' => $callback['message']['message_id']
        ]);
    }
}
