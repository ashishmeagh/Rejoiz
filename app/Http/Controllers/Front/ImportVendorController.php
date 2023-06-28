<?php
namespace App\Http\Controllers\Front;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MakerModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\CountryModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductsModel;
use App\Models\BrandsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\ProductImagesNotExist;
use App\Common\Services\ElasticSearchService;
use DB;
use Sentinel;
use Validator;
use Flash;
use Storage;
/*Author : priyanka date 10/1/2019*/
class ImportVendorController extends Controller
{
    public function __construct( MakerModel $MakerModel,
                                 UserModel $UserModel,
                                 RoleModel $RoleModel,
                                 RoleUsersModel $RoleUsersModel,
                                 ProductsModel $ProductsModel,
                                 ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                 ElasticSearchService $ElasticSearchService
                                )
    {
        $this->MakerModel = $MakerModel;
        $this->UserModel  = $UserModel;
        $this->RoleModel  = $RoleModel;
        $this->ProductsModel = $ProductsModel;
        $this->RoleUsersModel = $RoleUsersModel;
        $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->ElasticSearchService = $ElasticSearchService;
        $this->ProductDetailsModel = new ProductDetailsModel();
        $this->ProductImagesNotExist = new ProductImagesNotExist();
    }
    public function import(Request $request)
    {

        $delimiter = ",";
        $header    = null;
        $data      = array();
        // $filename   = storage_path('app/Vendors - Southeast Home.csv'); 
        // $filename   = storage_path('app/Vendors - West.csv'); 
        // $filename   = storage_path('app/Vendors - Southeast Glam.csv'); 
        // $filename   = storage_path('app/Vendors - Southeast Gift.csv'); 
        // $filename   = storage_path('app/Vendors - Northeast.csv'); 
        // $filename   = storage_path('app/Vendors - Mid-Atlantic Trend.csv'); 
        $filename   = storage_path('app/Vendors - MINK.csv'); 
        // $filename   = storage_path('app/Vendors - Mid-Atlantic Lifestyle.csv'); 
        
        
            if(!file_exists($filename) || !is_readable($filename))
            {
               
                return false;
            }
            $delimiter   = ",";
            $header      = null;
            $data        = array();
            if (($handle = fopen($filename, 'r')) !== false)
            {
              
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
                {
                    if (!$header)
                    {
                       $header = $row;
                    }
                    elseif(count($header) !=count($row))
                    {
                        continue;
                    }
                    else
                    {
                      $data[] = array_combine($header, $row);
                    }
                }

                fclose($handle);
            }

            $arr_input_data = $data;  
          
        $records_inserted = 0;
        /*insert into user and maker table*/
        try
        {
            DB::beginTransaction();

            $not_inserted = [];
            if(isset($arr_input_data) && count($arr_input_data)>0)
            {
                foreach ($arr_input_data as $key => $vendor_data)
                {
                    $vendor_data['email_address'] = trim($vendor_data['email_address']);
                    if($vendor_data['email_address'] == "")
                    {
                        array_push($not_inserted,$vendor_data);
                        continue;
                    }
                    $user_data = $arr_maker = [];
                    $count =0;
                    /*check email id duplication*/
                    if(isset($vendor_data['email_address']) && $vendor_data['email_address']!='')
                    {
                        $email = trim($vendor_data['email_address']);
                        $count = $this->UserModel->where('email',$email)->count();
                    }
                    if($count >= 1)
                    {
                        array_push($not_inserted,$vendor_data);
                    }
                    if($count == 0)
                    {
                            $user_data['first_name'] = isset($vendor_data['first_name'])?trim($vendor_data['first_name']):'';
                            $user_data['last_name']  = isset($vendor_data['last_name'])?trim($vendor_data['last_name']):'';
                            $user_data['email']      = isset($vendor_data['email_address'])?trim($vendor_data['email_address']):'';
                            $user_data['password']   = isset($vendor_data['password'])?trim($vendor_data['password']):'';
                            $user_data['post_code']  = isset($vendor_data['zip_code'])?trim($vendor_data['zip_code']):'';
                            $user_data['tax_id']     = isset($vendor_data['tax_id'])?trim($vendor_data['tax_id']):'';
                            $user_data['contact_no'] = isset($vendor_data['mobile_number'])?trim($vendor_data['mobile_number']):'';
                            $user_data['is_approved'] = '1';
                            $user_data['is_login']    ='1';
                            $user_data['country_id'] = isset($vendor_data['country'])?trim($vendor_data['country']):'';
                            $user = Sentinel::registerAndActivate($user_data);
                            if($user)
                            {
                                /*attach role*/
                                $role = Sentinel::findRoleBySlug('maker');
                                $role->users()->attach($user);
                                /*insert into maker table*/
                                $arr_maker['user_id']       = $user->id;
                                $arr_maker['company_name']  = isset($vendor_data['company_name'])?trim($vendor_data['company_name']):'';
                                $arr_maker['website_url']   = isset($vendor_data['website_url'])?trim($vendor_data['website_url']):'';
                                $arr_maker['primary_category_id'] = isset($vendor_data['primary_category'])?trim($vendor_data['primary_category']):'';
                                $arr_maker['no_of_stores']   = isset($vendor_data['no_of_stores'])?trim($vendor_data['no_of_stores']):'';
                                $arr_maker['insta_url']      = isset($vendor_data['instagram_url'])?trim($vendor_data['instagram_url']):'';
                                $store_maker_details = $this->MakerModel->create($arr_maker);
                            }
                         $records_inserted++;
                    }
                }
                DB::commit();
                $response['status']      = 'success';
                $response['description'] = 'Vendor has been inserted, records detected ('.sizeof($arr_input_data).'), records inserted ('.$records_inserted.')';
                $response['not_inserted'] = $not_inserted;
                return response()->json($response);
            }
            else
            {
                $response['status']      = 'error';
                $response['description'] = 'File is empty.';
                return response()->json($response);
            }
        }
        catch(Exception $e)
        {
            DB::rollback();
            $response['status']        = 'error';
            $response['description']   = $e->getMessage();
            return response()->json($response);
        }
    }
    

    public function update_img_path()
    {
        // dd(444);
        set_time_limit(0);
        /*756:creative brand
          833:paddywax
          */ 
        $vendor_id = 756;
        $cb_products = DB::table('products')
            ->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
            ->where('products.user_id',$vendor_id)
            // ->where('products.product_name','8" Hummel Madonna & Child')
            // ->whereNull('products.product_image')
            // ->take(4000)
            ->get()->toArray();
         
        try
        {    
            foreach ($cb_products as $cb_key => $cb_value)
            {
                $product_sku = $cb_value->sku;
                $product_id = $cb_value->product_id;
                $product_image_path = 'product_image/'.$product_sku.'.jpg';

                
                $file = basename($product_image_path);
                $exists = file_exists(base_path().'/storage/app/product_image/'.$file);

                if($exists=="true")
                {
                    DB::table('products')->where('id',$product_id)->update(array(
                                 'product_image'=>$product_image_path,'product_image_thumb'=>$product_image_path));

                    DB::table('products_details')->where('sku',$product_sku)->update(array(
                                 'image'=>$product_image_path,'image_thumb'=>$product_image_path));

                    DB::table('product_images')->where('product_id',$product_id)->update(array(
                                 'product_image'=>$product_image_path));
                }
            }
        }
        catch(Exception $e)
        {
            return $e;
        }
    }



    public function check_missing_images()
    {
        set_time_limit(0);
        /*756:creative brand
          833:paddywax
          715:blueQ
          722:fread
          949:europian soaps
          */ 
        $vendor_id = 756;
        $missing_images = [];
        $missing_images_arr =[];
        /*$cb_products = DB::table('products')
            ->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
            ->where('products_details.image','product_image/default-product.png')
             ->whereNotExists(function ($query)
                    {
                        $query->select(DB::raw(1))
                        ->from('product_images_not_exists')
                        ->whereRaw('product_images_not_exists.sku_no = products_details.sku');
                    })
            ->get()->toArray();*/

          /* $cb_products = DB::table('products_details')
            ->where('products_details.image','product_image/default-product.png')
            ->get()->toArray();*/


            
           $cb_products = DB::table('products')
            ->rightJoin('products_details', 'products.id', '=', 'products_details.product_id')

            ->where('is_active','!=',1)
            
            ->get()->toArray();
            //dd(count($cb_products));






        try
        {    

            foreach ($cb_products as $cb_key => $cb_value)
            {
                $product_sku = $cb_value->sku;
                // $product_name = $cb_value->product_name;
                // $product_id = $cb_value->id;

                $cb_all_img = 'images/350'.$product_sku.'.jpg';
                $file1 = basename($cb_all_img);


                $product_image_path1 = base_path().'/storage/app/images/350/'.$file1;
                $exists1 = file_exists($product_image_path1);
// DD($product_image_path1);
                dump($exists1);


// Cretive brand-all images

                $product_image_path = 'images/'.$product_sku.'.jpg';

                
                $file = basename($product_image_path);

                // $exists = file_exists(base_path().'/storage/app/final_product_image/'.$file);
                // $exists = file_exists(base_path().'/storage/app/final_product_image/'.$file);

                $product_image_path = base_path().'/storage/app/images/'.$file;

            /* check image is exists or not*/

               /* $exists = file_exists($product_image_path);

                if($exists == true)
                {
                    $file = basename($product_image_path);

                    $copyImagePath = Storage::disk('local')->getDriver()->getAdapter()
                            ->applyPathPrefix('fake/' . basename($file)); 
                  

                    $move = \File::move($product_image_path, $copyImagePath);

                    // if (\File::copy($product_image_path , $copyImagePath))
                    if ($move)
                    {
                        dump($file);
                    // dd(44444);
                    }


                    
                }*/
                
                /*if($exists!=true)
                {
                    $missing_images['sku']=  $product_sku;
                    $missing_images['product_name'] = $product_name;
                    array_push($missing_images_arr,$missing_images);
                }*/
                

            }

            dd(3333);
            /*$product_sku = array_column($missing_images_arr, 'sku');
            $product_name = array_column($missing_images_arr, 'product_name');*/
           
        }
        catch(Exception $e)
        {
            return $e;
        }
    }


    public function hide_products()
    {
       set_time_limit(0);
        /*756:creative brand
          833:paddywax
          715:blueQ
          722:fread
          949:europian soaps
          */ 
        //$vendor_id = 756;


        
        $cb_products = DB::table('products')
            ->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
            ->get()->toArray();



        try
        {    
            foreach ($cb_products as $cb_key => $cb_value)
            {
                
        

                $product_sku = $cb_value->sku;
                $product_name = $cb_value->product_name;
                $product_id = $cb_value->id;
                $product_image_path = $cb_value->product_image;

                
                $file = basename($product_image_path);
                //dd($file);
                $exists = file_exists(base_path().'/storage/app/product_image/'.$file);
                
             
            

                if($exists!=true)
                {

                    $is_product_deactive = $this->ProductsModel->where('id',$product_id)->update(['is_active'=>0]);
                    //$this->ElasticSearchService->decactivate_product($product_id,0);
                    
                }

                if($file=="")
                {
                    $is_product_deactive = $this->ProductsModel->where('id',$product_id)->update(['is_active'=>0]);
                    //$this->ElasticSearchService->decactivate_product($product_id,0);
                    
                }

            }

            return "success";
          
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    public function show_products()
    {
        set_time_limit(0);
        /*756:creative brand
          833:paddywax
          715:blueQ
          722:fread
          949:europian soaps
          */ 
        //$vendor_id = 756;
        $missing_images = [];
        $missing_images_arr =[];
        $cb_products = DB::table('products')
            ->leftJoin('products_details', 'products.id', '=', 'products_details.product_id')
            ->get()->toArray();

        try
        {    
            foreach ($cb_products as $cb_key => $cb_value)
            {
                $product_sku = $cb_value->sku;
                $product_name = $cb_value->product_name;
                $product_id = $cb_value->id;
                $product_image_path = 'product_image/'.$product_sku.'.jpg';

                
                $file = basename($product_image_path);
                $exists = file_exists(base_path().'/storage/app/product_image/'.$file);

                if($exists!=true)
                {
                    $this->ProductsModel->where('id',$product_id)->update(['is_active'=>1]);
                    $this->ElasticSearchService->activate_product($product_id,$cb_value->is_active);
                }
                

            }

            return "success";
          
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    public function update_product_brand()
    {
        set_time_limit(0);

        $delimiter = ",";
        $header    = null;
        $data  = $available_product = $cb_brands = array();
        $filename   = storage_path('app/Brands - Sheet1.csv'); 
        
      
        if(!file_exists($filename) || !is_readable($filename))
        {
            return false;
        }

        $delimiter   = ",";
        $header      = null;
        $data        = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                {
                   $header = $row;
                }
                elseif(count($header) !=count($row))
                {
                    continue;
                }
                else
                {
                  $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        $arr_input_data = $data;  
        
        $cb_products = DB::table('products')->where('user_id',756)->where('products.category_id',0)
            ->Join('products_details', 'products.id', '=', 'products_details.product_id')
            ->get()->toArray();
       
        $cb_brands = DB::table('brands')->where('user_id',756)->get()->toArray();

        try
        {    
            foreach ($arr_input_data as $cb_key => $ex_product)
            {
               
                foreach ($cb_products as $key => $product) {
                    
                    if ($product->sku == $ex_product['sku']) {
                        if (isset($cb_brands)) {
                            
                            foreach ($cb_brands as $brand) {
                                $brand_arr['user_id']       = 756;
                                    $brand_arr['brand_name']    = $ex_product['brand'];
                                    $brand_arr['is_active']     = 1;
                                    // $brand_arr['brand_image']   = '';

                               

                                $brand_id = BrandsModel::firstOrCreate($brand_arr);


                                    $update_brand[] = DB::table('products')

                                                    ->Join('products_details', 'products.id', '=', 'products_details.product_id')
                                                    ->where('sku',$ex_product['sku'])
                                                    ->update(['brand'=>$brand_id['id']]);

                            }
                        }
   
                    }
                }
                
            

            }
            

            return "success";
          
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    public function ftp_data_import()
    {
       /* dd('are you sure');
        $this->find_not_available_images_sku();*/
        set_time_limit(0);

        $arr_sku = $this->importExcel();

        // FTP server details
        $ftpHost   = 'ftp.christianbrands.com';
        $ftpUsername = 'ResellerImages';
        $ftpPassword = 'Res3ller!';
        $basePath = "/opt/lampp/htdocs/just-got-to-have-it-data";
        // open an FTP connection
        $connId = ftp_connect($ftpHost) or die("Couldn't connect to $ftpHost");
        // login to FTP server
        $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
        
        /* get product Skuno */
       /* $arr_sku = [];
        $get_sku = $this->ProductDetailsModel->get(['sku'])->toArray();
        $arr_sku = array_column($get_sku, 'sku');*/
        // $files = ftp_nlist( $connId, '/Product Images' );
        $files_250 = ftp_nlist( $connId, '/Product Images/250px JPGs' );
        $files_500 = ftp_nlist( $connId, '/Product Images/500px JPGs' );
        $files_800 = ftp_nlist( $connId, '/Product Images/800px JPGs' );
        $filteredFiles = [];
        // dd($connId,534546546);
        // dd($arr_sku);
        foreach ($arr_sku as $sku) {

            /* check file is already exists or not*/
       
            $exists = file_exists(base_path().'/storage/app/creative brand Invalid_images/'.$sku.'.jpg');
           
            if($exists!="true")
            {
                // $img = preg_grep( '/'.$sku.'.jpg/', $files );
                // $img250 = preg_grep("/\b$sku\b/", $files_250);
                // $sku = preg_quote($sku, '/');
                
                // $files_250 = str_replace("/", "", $files_250);

                try
                {
                    
                    $img250 = preg_grep("/\b$sku\b/", $files_250);    
                    if(isset($img250) && sizeof($img250)>0)
                    {
                        $filteredFiles[$sku] = array_pop($img250);
                        // continue;
                    }
                    if(isset($filteredFiles[$sku]) == false)
                    {
                        $img500 = preg_grep("/-\b$sku\b-/", $files_500);
                        if(isset($img500) && sizeof($img500)>0)
                        {
                            $filteredFiles[$sku] = array_pop($img500);
                            // continue;
                        }
                    }
                    if(isset($filteredFiles[$sku]) == false)
                    {
                        $img800 = preg_grep("/-\b$sku\b-/", $files_800);
                        if(isset($img800) && sizeof($img800)>0)
                        {
                            $filteredFiles[$sku] = array_pop($img800);
                            // continue;
                        }
                    }

                    if(isset($filteredFiles[$sku]))
                    {
                        if(ftp_get($connId, $basePath."/".basename($filteredFiles[$sku]),$filteredFiles[$sku], FTP_BINARY)){
                              dump($filteredFiles[$sku]);
                            // echo "File transfer successful - $localFilePath";
                        }else{
                            // echo "There was an error while downloading $localFilePath";
                        }
                    }
                }
                catch(\Exception $e)
                {
                    dump($e->getMessage());   
                }
            }
            else
            {
                //echo "file exists";
            }                
            
            
        }
        
        // dd($filteredFiles,$arr_sku);
        // dd($files[3],$files,$filteredFiles);
        // local & server file path
        $localFilePath  = 'index.php';
        $remoteFilePath = 'public_html/index.php';
        // try to download a file from server
        if(ftp_get($connId, $localFilePath, $remoteFilePath, FTP_BINARY)){
            echo "File transfer successful - $localFilePath";
        }else{
            echo "There was an error while downloading $localFilePath";
        }
        // close the connection
        ftp_close($connId);
    }


    // public function ftp_data_import()
    // {
    //     dd('Are you sure');
    //     ini_set('memory_limit', '-1');
    //     $arr_sku = $this->importExcel();

    //     $arrImgNotExists = $arrImgExists = [];

    //     /* list of extensions */
    //     $ext = 'jpg';

    //    foreach ($arr_sku as $sku) {

    //     /* biuld image path  */
    //     $final_product_image_path = base_path().'/storage/app/final_product_image/'.$sku.'.'.$ext;

    //     /* Check image is already exists or not */
    //     $finalImgExists = file_exists($final_product_image_path);

    //     if($finalImgExists == false)
    //     {

    //         /* Get images from extracted zip folder */
    //         $product_image_path = base_path().'/storage/app/images/'.$sku.'.'.$ext;

    //         /* check image is exists or not*/

    //         $exists = file_exists($product_image_path);

    //         if($exists == true)
    //         {
    //             $file = basename($product_image_path);

    //             $copyImagePath = Storage::disk('local')->getDriver()->getAdapter()
    //                     ->applyPathPrefix('final_product_image/' . basename($file)); 
              

    //             if (\File::copy($product_image_path , $copyImagePath))
    //             {
    //                 array_push($arrImgExists,$sku);           
    //             }
               
    //         }
    //         else
    //         {
    //             $inserted_data_arr = [];

    //             $inserted_data_arr['sku_no'] =  $sku;

    //             $isExitsSku = $this->ProductImagesNotExist->where('sku_no',$sku)
    //                                                       ->count();

    //             if($isExitsSku == 0)
    //             {
    //                $createEntry = $this->ProductImagesNotExist->create($inserted_data_arr);
    //             }
                
    //             array_push($arrImgNotExists,$sku);
    //         }
    //     }



    //     }
    //     dd(count($arrImgNotExists),count($arrImgExists));       
    // }

    public function remove_images()
    {
        $arr_sku = [];

        /* get BlueQ brands SKU*/

        $arr_sku = $this->ProductDetailsModel->whereHas('productDetails',function($q)
                                            {
                                                $q->where('user_id','715');
                                            })                                           
                                           ->get(['sku'])
                                           ->toArray();

        $missing_sku_img = $match_sku_img = [];
         
        if($arr_sku)
        {
            foreach ($arr_sku as  $sku) 
            {
                $product_image_path = 'sorted_images/'.$sku['sku'].'.jpg';
                    
                $file = basename($product_image_path);
                
                $exists = file_exists(base_path().'/storage/app/BlueQ_imges/'.$file);

                if($exists!=true)
                {
                    array_push($missing_sku_img, $sku);
                }
                else
                {   
                    $currentFilePath = base_path().'/storage/app/BlueQ_imges/'.$file;
                    $newFilePath = base_path().'/storage/app/match/'.$file;

                    // $move = \File::move($currentFilePath, $newFilePath);

                    array_push($match_sku_img, $sku);
                }
            }

            //dd($missing_sku_img);
        }
    }

    public function update_product_category_subcstegory()
    {

        set_time_limit(0);
        
        $product_data = $update_arr_data = $product_cat_att = [];

        $delimiter = ",";
        $header    = null;
        $data  = $available_product = $cb_brands = array();

        $filename   = storage_path('app/creative brand product category list - Sheet1 (2).csv'); 
              
        if(!file_exists($filename) || !is_readable($filename))
        {

            return false;
        }

        $delimiter   = ",";
        $header      = null;
        $data        = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                {
                   $header = $row;
                }
                elseif(count($header) != count($row))
                {
                    continue;
                }
                else
                {
                  $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        $arr_input_data = $data;  


        /*$cb_products = ProductsModel::whereHas('productDetails')
                           
                            ->where('user_id',833)
                           
                            
                            ->get()->toArray();*/
       $cb_products = DB::table('products')->where('user_id',756)
                            ->Join('products_details', 'products.id', '=', 'products_details.product_id')
                            ->groupBy('products_details.product_id')

                            ->get()->toArray();

                           
        // dd($cb_products);

                     
        try
        {    
            foreach ($arr_input_data as $cb_key => $ex_product)
            {
                foreach ($cb_products as $key => $product)
                {
                    if($product->sku == $ex_product['sku']) 
                    {
                        if( isset($ex_product['category']) && $ex_product['category'] != '' && $ex_product['category'] != 0)
                        {
                          
                           
                            $product_cat_att['category_id'] = (int)$ex_product['category'];

                            $update_product_cat = $this->ProductsModel->where('id',$product->product_id)->update($product_cat_att);
                            
                            $update_arr_data['category_id']     = $ex_product['category'];

                            if($ex_product['subcategory'] != 0)
                            {
                                $update_arr_data['sub_category_id'] = $ex_product['subcategory'];
                            }
                        } 
                    

                        $obj_products = $this->ProductsSubCategoriesModel->where('product_id',$product->product_id);

                        $product_is_exits =  $obj_products->count();

                        if($product_is_exits > 0 && count($update_arr_data) > 0)
                        {
                            $update_product_cat_subcat = $obj_products->update($update_arr_data);
                        }
                        else
                        {

                           
                            $product_data['product_id']      = isset($product->product_id)?trim($product->product_id):0;
                            $product_data['category_id']     = isset($product->category)?trim($product->category):0;
                            $product_data['sub_category_id'] = isset($product->subcategory)?trim($product->subcategory):0;

                            $this->ProductsSubCategoriesModel->create($product_data);
                        }                  
                    }

                   
                }
            }

            return "success";
          
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }


    // public function importExcel()
    // {
    //   // dd('are you sure?');
    //   set_time_limit(0);
         
    //   $arrSku = $notExist = $alreadyExist = [];

    //  /* $arrProductSku = ProductDetailsModel::whereHas('productDetails',function($q){
    //                                                     // $q->where('is_active','1');
    //                                                     $q->where('is_active','0');
    //                                                     return $q->where('user_id','756');
    //                                                  })
    //                                                 ->get(['sku'])
    //                                                 ->toArray();

    //   if(isset($arrProductSku) && count($arrProductSku))
    //   {
    //     $arrSku = array_column($arrProductSku, 'sku');
    //   }
    
    //   return $arrSku;*/

    //   /* Get data from xlsx file */
    //   $path = storage_path('app/CB-Slant Inventory.xlsx'); 

    //   $data = \Excel::selectSheetsByIndex(0)->load($path)->get()->toArray();
      
    //   // $data = \Excel::selectSheetsByIndex(0)->load($path)->skip(15000)->get()->toArray();
    //   $data = array_column($data, null, 'sku');
        
    //   $arr_sku = array_column($data,'itemsku');

    //   $arr_sku = array_map(function($sku){

    //   $sku = is_numeric($sku) ? intval($sku) : $sku;
    //       $sku = "".$sku."";
    //       return $sku;
    //   }, $arr_sku);
      
    //   foreach ($arr_sku as $sku) 
    //   {
    //       /* check sku duplication */

    //       $arrAlreadyExistsSkuCount = ProductDetailsModel::where('sku',$sku)->count();

    //       if($arrAlreadyExistsSkuCount > 0)
    //       {
    //         array_push($alreadyExist, $sku);
    //       }
    //       else
    //       {
    //         array_push($notExist, $sku);
    //       }
    //   }

    //   /*$arrAvailableSku = ProductDetailsModel::get(['sku'])->toArray();
      
    //   $arrAvailableSku = array_column($arrAvailableSku, 'sku');

    //   $arr_sku         = array_diff($arr_sku, $arrAvailableSku);*/
    
    //   return $notExist;
    // }

    public function importExcel()
    {
      // dd('are you sure?');
      set_time_limit(0);
         
      $arrSku = $arrProductSku = [];

   
      /* Get data from xlsx file */
     /* $path = storage_path('app/CB-Inventory.xlsx'); 

      $data = \Excel::selectSheetsByIndex(0)->load($path)
                                            ->skip(24000)
                                            ->take(700)
                                            ->get()
                                            ->toArray();*/
     $cb_products = DB::table('products')
            ->rightJoin('products_details', 'products.id', '=', 'products_details.product_id')

            ->where('is_active','!=',1)
            ->take(100)
            ->get()->toArray();

                   // dd($data);

      
      // $data = \Excel::selectSheetsByIndex(0)->load($path)->skip(15000)->get()->toArray();
      $data = array_column($cb_products, null, 'sku');


      $arr_sku = array_column($data,'sku');

      $arr_sku = array_map(function($sku){

      $sku = is_numeric($sku) ? intval($sku) : $sku;
          $sku = "".$sku."";
          return $sku;
      }, $arr_sku);
      
      foreach ($arr_sku as $sku) 
      {
        /* return array of product sku */

          array_push($arrProductSku, $sku);
      }


      return $arrProductSku;
    }

    public function update_retail_price()
    {
        set_time_limit(0);

        $delimiter = ",";
        $header    = null;
        $data  = $available_product = $cb_brands = array();
        $filename   = storage_path('app/CB-retail_price - Sheet1.csv'); 
        
      
        if(!file_exists($filename) || !is_readable($filename))
        {
            return false;
        }

        $delimiter   = ",";
        $header      = null;
        $data        = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                {
                   $header = $row;
                }
                elseif(count($header) !=count($row))
                {
                    continue;
                }
                else
                {
                  $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        $arr_input_data = $data;  
        
        
        $cb_products = DB::table('products')->where('user_id',756)
            ->Join('products_details', 'products.id', '=', 'products_details.product_id')
            ->get()->toArray();
        // dd($arr_input_data); 
        try
        {    
            foreach ($arr_input_data as $cb_key => $ex_product)
            {
               
                foreach ($cb_products as $key => $product) {
                    

                    // if ($product->sku == $ex_product['SKU']) {

                        $update_brand = DB::table('products')->where('product_name',$ex_product['Description'])->update(['retail_price'=>$ex_product['Retail Price'],'unit_wholsale_price'=>$ex_product['Wholesale price']]);
                    // }

                }
            }

            return "success";
          
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    /* 
        Desc : Get sku numbers those have no images
    */
    public function find_not_available_images_sku()
    {
         /*756:creative brand
          833:paddywax
          715:blueQ
          722:fread
          949:europian soaps
          */ 



        // Creative Brand : (missing : 6844, Available : 9490 , Total : 16334) 
        // BlueQ Brand    : (missing : 0, Available : 0 , Total : 0) products(593)
        // Paddywax Brand : (missing : 4859, Available : 1278 ) products(523)

         /* Total : 13876 */
         set_time_limit(0);
         $arrImgNotExists = $arrExistImg = [];
          $arrProducts = ProductsModel::with('productDetails')
                                      ->where('user_id','715')
                                      // ->skip(10000)
                                      // ->take(8000)
                                      ->get()
                                      ->toArray();

                                      // dd(count($arrProducts));

         foreach ($arrProducts as $product)
         {
            foreach ($product['product_details'] as $productData)
            {      
                $sku = $productData['sku'];

                $exists = file_exists(base_path().'/storage/app/product_image/'.$sku.'.jpg');

                if($exists!="true")
                {
                    array_push($arrImgNotExists, $sku);
                    //echo $sku.'<br>';
                }
                else
                {
                    array_push($arrExistImg, $sku);
                }
            }
         }

         dd(count($arrImgNotExists),count($arrExistImg));
    }


}