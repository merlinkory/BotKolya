<?php

namespace App\Traits;

trait UpdateTrait {

    protected function handleUpdate(array $update) {

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['new_chat_member'])) {
            dump($update);
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

        dump('handlePrivateMessage', $message);
    }

    protected function handleBotCommand(array $message, string $from) {
        dump('handleBotCommand', $message, $from);
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
