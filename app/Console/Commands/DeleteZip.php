<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Services\GeneralService;


class DeleteZip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:zip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete uploaded product zip file after three days';

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
        $this->GeneralService->delete_uploaded_prod_zip();

    }
}
