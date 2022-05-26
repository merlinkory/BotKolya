<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bot;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use DF;
class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//     $c = \App\Models\Chat::updateOrCreate(['telegram_id' => -1001130950080], ['title' => 'test group 2']);
//        $buttons[][] = [
//            'text' => 'click here',
//            'web_app' => [
//                'url' => "https://max.xbeach.info/webapp.html"
//            ] 
//        ];
//        $keyboard['inline_keyboard'] = $buttons;
//
//        Bot::sendMessage([
//            'chat_id' => 430902348,
//            'text' => 'web apps',
//            'reply_markup' => $keyboard
//        ]);
        
//        $this->detect_intent_texts("botkolya-qvuh", "Привет", 123);
        
//        $text = "@AcademPolyanaBot  ddsadfs";
//        dump(substr($text, 0, 17));
        
          dd(DF::getAnswer('Привет'));;
        
        
    }
    
    public function detect_intent_texts($projectId, $text, $sessionId, $languageCode = 'ru-RU') {
        // new session
        $test = array('credentials' => storage_path('botkolya-qvuh-5f366e6e440d.json'));
        $sessionsClient = new SessionsClient($test);
        $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
        printf('Session path: %s' . PHP_EOL, $session);

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode($languageCode);

        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();
        $queryText = $queryResult->getQueryText();
        $intent = $queryResult->getIntent();
        $displayName = $intent->getDisplayName();
        $confidence = $queryResult->getIntentDetectionConfidence();
        $fulfilmentText = $queryResult->getFulfillmentText();

        // output relevant info
        print(str_repeat("=", 20) . PHP_EOL);
        printf('Query text: %s' . PHP_EOL, $queryText);
        printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
                $confidence);
        print(PHP_EOL);
        printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);

        $sessionsClient->close();
    }

}
