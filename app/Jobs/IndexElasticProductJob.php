<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Common\Services\ElasticSearchService;

class IndexElasticProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $productId;
    protected $ElasticSearchService;
    public function __construct($productId = false,ElasticSearchService $objElasticSearchService)
    {
        $this->productId = $productId;
        $this->ElasticSearchService = $objElasticSearchService;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->ElasticSearchService->initiate_index_product($this->productId);
    }
}
