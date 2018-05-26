<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TableController;
class SMSAuto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dailyAtsms:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS to new added data in filter task every daily at 00:00';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TableController::SendSMSAuto('sms');
    }
}
