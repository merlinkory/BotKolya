<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bot;
use App\Models\TotalData;
class TelegramHandleUpdateCommand extends Command
{
   use \App\Traits\UpdateTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handleUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command. 
     */
    public function handle()
    {
        $lastUpdateIdModel = TotalData::where('key','lastUpdateId')->first();
        $lastUpdateId = (int) $lastUpdateIdModel->value;
        while(true){    
            dump("lastUpdateId = {$lastUpdateId}");
            $updates = !$lastUpdateId ? Bot::getUpdates() : Bot::getUpdates(['offset'=>$lastUpdateId]);
            foreach ($updates['result'] as $update){
                $lastUpdateId = $update['update_id'] + 1;
                try{
                    $this->handleUpdate($update);
                } catch (Exception $ex) {
                    // log
                }
                $lastUpdateIdModel->value = $lastUpdateId;
                $lastUpdateIdModel->save();
            }           
            sleep(1);
        }
    }    
}
