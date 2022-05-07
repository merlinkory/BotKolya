<?php

namespace App\Classes\Commands;

use Bot;
use App\Models\CommandDialog;
use App\Models\Party;

class NewPartyCommand {

    protected $dialog;

    public function __construct() {
        
    }

    public function start(array $message) {
        // delete all dialogs   
        $dialogs = CommandDialog::where('telegram_user_id',$message['from']['id'])
                ->where('telegram_chat_id',$message['chat']['id'])
                ->get();
        foreach ($dialogs as $dialog){
            $dialog->delete();
        }
        
        $from = $message['from']['id'];
        Bot::send($from, 'Введине название вечеринки');

        $data['chat_id'] = $from;
        $data['step'] = 'save_title';
        $dialog = new CommandDialog();
        $dialog->telegram_user_id = $from;
        $dialog->telegram_chat_id = $message['chat']['id'];
        $dialog->command = 'App\Classes\Commands\NewPartyCommand';
        $dialog->data = json_encode($data);
        $dialog->save();
    }

    public function next(array $data, array $message, CommandDialog $dialog) {

        $this->dialog = $dialog;

        switch ($data['step']) {
            case 'save_title':
                $this->saveTitle($data, $message);
                break;
            case 'save_place':
                $this->savePlace($data, $message);
                break;
            case 'save_date':
                $this->saveDate($data, $message);
                break;
        }
    }

    protected function saveTitle($data, $message) {

        $data['title'] = $message['text'];
        $data['step'] = "save_place";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

        Bot::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => 'Введите место проведения',
        ]);
    }

    protected function savePlace($data, $message) {

        $data['place'] = $message['text'];
        $data['step'] = "save_date";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

        Bot::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => 'Введите время проведения',
        ]);
    }

    protected function saveDate($data, $message) {

        $data['date'] = $message['text'];
        $data['step'] = "confirm";

        $this->dialog->data = json_encode($data);
        $this->dialog->save();

        $buttons[] = [[
            'text' => 'Подтвердить',
            'callback_data' => "new:confirm"
        ],
            [
            'text' => 'Отменить',
            'callback_data' => "new:cancel"
        ]];
        $keyboard['inline_keyboard'] = $buttons;

        Bot::sendMessage([
            'chat_id' => $data['chat_id'],
            'text' => 'Подтвердите создание мероприятия',
            'reply_markup' => $keyboard
        ]);
    }

    public function callback($callback, CommandDialog $dialog, $calback_result) {

        $data = json_decode($dialog->data, true);
        
        if ($calback_result == 'confirm' && isset($data['title']) && isset($data['place']) && isset($data['date'])) {                                   
            $party = new Party();
            $party->title = $data['title'];
            $party->place = $data['place'];
            $party->date = strtotime($data['date']);
            $party->save();
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
