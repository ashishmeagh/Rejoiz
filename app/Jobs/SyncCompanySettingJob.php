<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProductsModel;
use App\Common\Services\ElasticSearchService;


class SyncCompanySettingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels ;

    protected $loggedIn_userId;
    protected $shop_lead_time;
    protected $first_order_minimum;
    protected $ElasticSearchService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($loggedIn_userId,
                                $shop_lead_time,
                                $first_order_minimum,
                                ElasticSearchService $ElasticSearchService
                               )
    {
        $this->loggedIn_userId = $loggedIn_userId;
        $this->shop_lead_time =$shop_lead_time;
        $this->first_order_minimum = $first_order_minimum;
        $this->ElasticSearchService = $ElasticSearchService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        // dd($this->loggedIn_userId,$this->shop_lead_time ,$this->first_order_minimum,$this->ElasticSearchService);
        $products_arr = ProductsModel::where('user_id',$this->loggedIn_userId)->get()->toArray();

        $this->ElasticSearchService->update_lead_time($products_arr,$this->shop_lead_time ,$this->first_order_minimum);
    }

    public function failed($exception) {

        dd($exception->getMessage());
    }
}
