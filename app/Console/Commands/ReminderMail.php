<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Services\GeneralService;

use DB;

class ReminderMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GeneralService  $GeneralService)
    {
        parent::__construct();

        $this->GeneralService = $GeneralService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    
        // $totalUsers = \DB::table('demo')
                           //->delete();
        
        $this->GeneralService->mail_after_duedate_over();
        
    }
}
