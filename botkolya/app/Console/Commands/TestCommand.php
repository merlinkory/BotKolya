<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bot;
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
        Bot::sendMessage([
            'chat_id'=>430902348,
            'text'=>'Hello from Facade'
        ]);
        return 0;
    }
}
