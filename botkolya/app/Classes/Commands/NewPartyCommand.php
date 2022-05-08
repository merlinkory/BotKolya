<?php

namespace App\Classes\Commands;

use Bot;
use App\Models\CommandDialog;
use App\Models\Party;

class NewPartyCommand extends BaseCommandWithDialog{

    protected $dialog;

    public function __construct() {
        
    }

    public function start(array $message) {
       
        $this->deleteDialog($message);
        
        $from = $message['from']['id'];
        Bot::send($from, 'Введине название вечеринки');

        $data['step'] = 'save_title';
        $dialog = new CommandDialog();
        $dialog->telegram_user_id = $from;
        $dialog->telegram_chat_id = $message['chat']['id'];
        $dialog->command = 'App\Classes\Commands\NewPartyCommand';
        $dialog->data = json_encode($data);
        $dialog->save();
    }

    public function next(CommandDialog $dialog, array $message) {

        $data = json_decode($dialog->data, true);
        
        switch ($data['step']) {
            case 'save_title':
                $this->saveTitle($dialog, $message);
                break;
            case 'save_place':
                $this->savePlace($dialog, $message);
                break;
            case 'save_date':
                $this->saveDate($dialog, $message);
                break;
        }
    }

    protected function saveTitle(CommandDialog $dialog, array $message) {

        $data = json_decode($dialog->data, true);
        
        $data['title'] = $message['text'];
        $data['step'] = "save_place";

        $dialog->data = json_encode($data);
        $dialog->save();

        Bot::sendMessage([
            'chat_id' => $dialog->telegram_chat_id,
            'text' => 'Введите место проведения',
        ]);
    }

    protected function savePlace(CommandDialog $dialog, array $message) {

        $data = json_decode($dialog->data, true);
        
        $data['place'] = $message['text'];
        $data['step'] = "save_date";

        $dialog->data = json_encode($data);
        $dialog->save();

        Bot::sendMessage([
            'chat_id' => $dialog->telegram_chat_id,
            'text' => 'Введите время проведения',
        ]);
    }

    protected function saveDate(CommandDialog $dialog, array $message) {

        $data = json_decode($dialog->data, true);
        
        $data['date'] = $message['text'];
        $data['step'] = "confirm";

        $dialog->data = json_encode($data);
        $dialog->save();

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
            'chat_id' => $dialog->telegram_chat_id,
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
}
