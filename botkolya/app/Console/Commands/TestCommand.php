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
     $c = \App\Models\Chat::updateOrCreate(['telegram_id' => -1001130950080], ['title' => 'test group 2']);
    }
}
