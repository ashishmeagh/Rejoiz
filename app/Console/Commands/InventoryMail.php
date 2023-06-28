<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Services\GeneralService;


class InventoryMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Product Inventory(Qantity) Reminder';

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
        $this->GeneralService->check_product_inventory();

    }
}
