<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProductsModel;
use App\Common\Services\ElasticSearchService;

class BulkiIndexProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $vendor_id;
    protected $ElasticSearchService;

    public function __construct($vendor_id,ElasticSearchService $ElasticSearchService)
    {
        //
        $this->vendor_id = $vendor_id;
        $this->ElasticSearchService = $ElasticSearchService;
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);
        
        try{
            $cursor_data = ProductsModel::where('is_deleted', 0)
                                    ->where('is_active', 1)
                                    ->where('user_id',$this->vendor_id)
                                    ->whereHas('userDetails',function($q){
                                        return $q->where('status',1)->where('is_approved',1);
                                    })
                                    ->get();
            
            if(isset($cursor_data))
            {
            $cursor_data = $cursor_data->toArray();
            }  

            $products_arr = array_column($cursor_data,'id');

            $this->ElasticSearchService->bulk_index_products($products_arr);                     
        }
        catch (\Exception $e) 
        {  
            dd($e);
            return false;
        }
    }

    public function failed($exception) {

        dd($exception->getMessage());
    }
}
