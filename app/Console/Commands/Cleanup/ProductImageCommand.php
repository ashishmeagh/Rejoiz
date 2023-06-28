<?php

namespace App\Console\Commands\Cleanup;

use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\ProductsModel;
use Illuminate\Console\Command;
use Storage;

class ProductImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:product_images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup Non Existing Product Images';

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
        $all_existing_images = Storage::allFiles('product_image');

        /* Get All Image including soft deleted products */
        $arr_db_images = ProductDetailsModel::whereIn('image', $all_existing_images)
            ->get(['image'])
            ->toArray();

        $arr_db_primary_images = ProductsModel::whereIn('product_image', $all_existing_images)
            ->get(['product_image as image'])
            ->toArray();

        $arr_db_primary_images_thumb = ProductsModel::whereIn('product_image_thumb', $all_existing_images)
            ->get(['product_image_thumb as image'])
            ->toArray();

        $arr_db_other_images = ProductImagesModel::whereIn('product_image', $all_existing_images)
            ->get(['product_image as image'])
            ->toArray();

        $arr_db_images = array_column($arr_db_images, 'image', 'image');
        $arr_db_primary_images = array_column($arr_db_primary_images, 'image', 'image');
        $arr_db_other_images = array_column($arr_db_other_images, 'image', 'image');
        $arr_db_primary_images_thumb = array_column($arr_db_primary_images_thumb, 'image', 'image');

        $arr_db_images = array_merge($arr_db_images, $arr_db_primary_images);
        $arr_db_images = array_merge($arr_db_images, $arr_db_primary_images_thumb);
        $arr_db_images = array_merge($arr_db_images, $arr_db_other_images);

        $deleted_count = 0;
        if (sizeof($all_existing_images) > 0) {
            foreach ($all_existing_images as $image) {

                /* Check if DB has it , if not then delete  */
                if (isset($arr_db_images[$image]) == false) {
                    /* Remove Image Physically */
                    Storage::delete($image);
                    $deleted_count++;
                }
            }
        }
        dump("Deleted Orphan Product Image:  " . $deleted_count);
    }
}
