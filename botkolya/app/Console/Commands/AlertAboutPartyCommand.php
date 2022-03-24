<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TotalData;
use Bot;

class AlertAboutPartyCommand extends Command {

    /**
     * The name and signature of the console command.<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TotalData;
use Bot;
     *
     * @var string
     */
    protected $signature = 'alertParty';

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
    public function handle() {

        $lastParyDate = TotalData::where('key', 'lastPartyDate')->first()->value; 
        $NotNotifyPeriodFromLastPary = config('constants.NotNotifyPeriodFromLastPary');;// не напоминаем, если с прошлой пати прошло мение 20 sec
        
        $lastAlertDateModel = TotalData::where('key', 'lastAlertDate')->first();
        $lastAlertDate = isset($lastAlertDateModel->value) ? $lastAlertDateModel->value : 0;
        $alertPeriod = config('constants.alert_period_seconds', 30 * 24 * 60 * 60); // 1 month by default
        $chatToNotify = config('constants.chat_to_notify');

        //Если с момента последнего алерта прошло более чем $alertPeriod и с момента последней встречи прошло  более чем $NotNotifyPeriodFromLastPary тогда уведомляем в группу
        if (($lastAlertDate < time() - $alertPeriod) && (time() - $NotNotifyPeriodFromLastPary > $lastParyDate) ) {

            Bot::sendMessage([
                'chat_id' => $chatToNotify,
                'text' => 'Давно не собирались! пора прибухнуть!'
            ]);

            TotalData::updateOrCreate(
                    ['key' => 'lastAlertDate'],
                    ['value' => time()]
            );
        }
    }
}
