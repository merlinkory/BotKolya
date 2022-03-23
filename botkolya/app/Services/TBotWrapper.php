<?php
namespace App\Services;

use InvalidArgumentException;
use BadMethodCallException;
use Illuminate\Support\Facades\Http;
use Exception;
use Arr;

class TBotWrapper
{
    const URL = 'https://api.telegram.org';
    const ARGUMENT_METHOD_STR = "Argument 'method' must be 'get' or 'post'";
    const CURLOPT_TIMEOUT = 5;
    const UPDATE_PARAMS = [ // Все возможные типы параметров updates
        'message',
        'edited_message',
        'channel_post',
        'edited_channel_post',
        'inline_query',
        'chosen_inline_result',
        'callback_query',
        'shipping_query',
        'pre_checkout_query',
        'poll',
        'poll_answer'
    ];

    protected $token;
    protected $prefix;
    protected $username;
    protected $id;
    protected $requestMethod;
    protected $timeout;
	
    protected $_update;
    protected $command;
    protected $commandArgs;

    public function __construct(string $token, string $username, string $requestMethod = 'post', int $timeout = 30)
    {
        $errorMsg = '';
        if (!$token) $errorMsg = "\nNot defined 'token' argument";
        if (!$username) $errorMsg .= "\nNot defined 'username' argument";
        if (!preg_match('/^(\d{9,}):.+$/', $token, $matches)) $errorMsg .= "\nInvalid token";
        if ($errorMsg != '') throw new InvalidArgumentException("Errors detected: " . $errorMsg);
        $this->requestMethod = trim(strtolower($requestMethod));
        if (!in_array($this->requestMethod, ['post', 'get'])) throw new InvalidArgumentException("Errors detected: " . self::ARGUMENT_METHOD_STR);

        $this->id = $matches[1];
        $this->token = $token;
        $this->username = $username;
        $this->prefix = self::URL . "/bot{$this->token}";
		$this->timeout = $timeout;
//        if (!app()->runningInConsole()) $this->setUpdate(request()->all());
    }

    public function instance()
    {
        return $this;
    }

    public function username()
    {
        return $this->username;
    }

    public function id()
    {
        return $this->id;
    }

    public function command()
    {
        return $this->command;
    }

    public function commandArgs()
    {
        return $this->commandArgs;
    }
    
    /**
     * Установка update
     * 
     * @param array|object $newUpdate
     * @return type
     */
    public function setUpdate($newUpdate)
    {
        $update = json_decode(json_encode($newUpdate), true);
        if (count($update) < 2 || !isset($update['update_id']) || !count(array_intersect(array_keys($update), self::UPDATE_PARAMS))) 
            throw new InvalidArgumentException('Invalid update');
        
        $this->_update = $update;
        $this->command = $this->commandArgs = null;
        $messageType = $update['message']['entities'][0]['type'] ?? null;
        if ($messageType === 'bot_command' // Это команда для бота - инициализируем данные
                && preg_match('/^\s*\/(.+?)(?:\s+(.+)|@(.+)|$)/', mb_strtolower($update['message']['text']), $matches)) // 1 - команда, 2 - аргумент команды, 3 - никнейм бота
        {
            if (isset($matches[3]) && $matches[3] !== mb_strtolower($this->username)) return; // обращение не к нашему боту
            $this->command = mb_strtolower($matches[1]);
            $this->commandArgs = isset($matches[2]) ? trim($matches[2]) : null;
        }
    }
    
    /**
     * Возвращает текущий update бота или его отдельное поле
     * 
     * @param string|null $field - поля update через точку (пр: 'message.from.id')
     * @return mixed
     */
    public function update(?string $field = null)
    {
        return $field ? Arr::get($this->_update, $field) : $this->_update;
    }

    /**
     * Установка update
     *
     * @param string $name
     * @param $update - object|array
     *
     * @throws Exception
     */
    public function __set(string $name, $update)
    {
        if ($name !== 'update') throw new Exception("Inknown property '$name'");
        $this->setUpdate($update);
    }

    /**
     * Вызов telegram функции или полученный update бота (или его параметра)
     * Если функция имеет вид {$name}Back - добавляет в массив данных для telegram chat_id из полученного update
     *
     * @param string $func - вызываемая функция
     * @param array - 1-й элемент массива - массив данных для telegram, 2-й элемент массива - 'post' или 'get', необязательный параметр
     * @throws Exception
     */
    public function __call(string $func, array $arguments)
    {
        if (!$this->id) throw BadMethodCallException("TBotWrapper not initialized!");

        $telegramArgs = $arguments[0] ?? null;
        if ($telegramArgs && !is_array($telegramArgs)) throw new InvalidArgumentException('Invalid type parameter. Must be array or null');
        
        if (isset($arguments[1])) { // Проверяем наличие 2-го аргумента - method
            if (!is_string($arguments[1])) throw new InvalidArgumentException(self::ARGUMENT_METHOD_STR);

            $method = trim(strtolower($arguments[1]));
            if (!in_array($method, ['get', 'post'])) throw new InvalidArgumentException(self::ARGUMENT_METHOD_STR);
        } else {
            $method = $this->requestMethod;
        }

        $res = Http::timeout($this->timeout)->$method("$this->prefix/$func", $telegramArgs)->throw()->json();
        if (!$res['ok']) throw new Exception($res->description, $res->error_code); // Telegram вернул ошибку

        return $res;
    }
}