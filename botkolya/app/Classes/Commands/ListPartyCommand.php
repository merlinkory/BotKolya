<?php

namespace App\Classes\Commands;

use App\Models\Party;
use Bot;
/**
 * Description of ListPartyCommand
 *
 * @author max
 */
class ListPartyCommand {
    //put your code here
    public function start(array $message){
        
        $partyList = Party::where('date', '>', time())->get();
//        dd($partyList);
        $output = '';
        
        $counter = 1;
        foreach ($partyList as $party){            
            $output .= "<b>{$counter}.</b> {$party->title} ($party->place) <i>". date('Y-m-d H:i:s',$party->date) ."</i>\n";
            $counter++;
        }
        
        Bot::send($message['from']['id'], $output);
    }
}
