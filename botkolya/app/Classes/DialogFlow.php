<?php

namespace App\Classes;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;


class DialogFlow {
    
    private $keyFile;
    private $projectID;

    public function __construct() {
        $this->keyFile = storage_path(config('constants.dialog_flow_keyfile'));
        $this->projectID = config('constants.dialog_flow_project_id');
    }

    public function getAnswer($text) {      
        // new session
        $credent = array('credentials' => $this->keyFile);
        $sessionsClient = new SessionsClient($credent);
        $session = $sessionsClient->sessionName($this->projectID, uniqid());
        printf('Session path: %s' . PHP_EOL, $session);

        // create text input
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('ru-RU');

        // create query input
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // get response and relevant info
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();       
        $fulfilmentText = $queryResult->getFulfillmentText();

        $sessionsClient->close();
        
        
        return $fulfilmentText;
    }
}
