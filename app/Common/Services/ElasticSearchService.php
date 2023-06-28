<?php
namespace App\Common\Services;

use App\Common\Services\SearchService;
use App\Jobs\IndexElasticProductJob;
use App\Jobs\BulkiIndexProductJob;
use App\Models\CustomerFavoriteModel;
use App\Models\FavoriteModel;
use App\Models\MakerModel;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\RepresentativeMakersModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\ShopImagesModel;
use App\Models\ShopSettings;
use App\Models\UserModel;
use DB;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Sentinel;
use Session;
use App\Events\NotificationEvent;


class ElasticSearchService
{
    public function __construct(
        SearchService $SearchService,
        UserModel $UserModel,
        RepresentativeMakersModel $RepresentativeMakersModel,
        FavoriteModel $FavoriteModel,
        MakerModel $MakerModel,
        RoleModel $RoleModel,
        RoleUsersModel $RoleUsersModel,
        ShopImagesModel $ShopImagesModel,
        ShopSettings $ShopSettings,
        ProductsModel $ProductsModel,
        ProductsSubCategoriesModel $ProductsSubCategoriesModel,
        CustomerFavoriteModel $CustomerFavoriteModel

    ) {
        $this->SearchService = $SearchService;
        $this->UserModel = $UserModel;
        $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->RepresentativeMakersModel = $RepresentativeMakersModel;
        $this->FavoriteModel = $FavoriteModel;
        $this->MakerModel = $MakerModel;
        $this->RoleModel = $RoleModel;
        $this->RoleUsersModel = $RoleUsersModel;
        $this->ShopImagesModel = $ShopImagesModel;
        $this->ProductsModel = $ProductsModel;
        $this->ShopSettings = $ShopSettings;
        $this->CustomerFavoriteModel = $CustomerFavoriteModel;
    }

    public function reindex_products(Request $request, $isZipRequest = false)
    {
       /* Index Elastic Search */
        ini_set('max_execution_time', 0);

        $cursor_data = ProductsModel::where('is_deleted', 0)
                                    ->where('is_active', 1)
                                    ->whereHas('userDetails',function($q){
                                        return $q->where('status',1)->where('is_approved',1);
                                    })
                                    ->cursor();



        foreach ($cursor_data as $product) {
            $this->index_product($product->id);
        }

        if ($isZipRequest != false) {
            return true;
        } else {
            return 'success';
            dd("Indexing Successful");
        }

    }


    public function curl_search($param=false)
    {
        try
        {
            $elastic_host = env("ELASTIC_HOST");
            $elastic_index= env("ELASTIC_INDEX_NAME");
            $baseUrl = $elastic_host."/".$elastic_index."/_search";
            
            $temp_param = $param['body'];
            $param = json_encode($temp_param);
          
            $header = array(
                "content-type: application/json"
            );
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,$baseUrl);
            curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
            $response = curl_exec($curl);
            curl_close($curl);
            
            return json_decode($response, true);
        }
        catch(\ Exception $e)
        {
            return false;
        }
    }


    public function curl_index($data_arr=false)
    {
        try
        {
            $elastic_host = env("ELASTIC_HOST");
            $elastic_index= env("ELASTIC_INDEX_NAME");
        
            $header = array(
               "Content-Type: application/json"
            );

            $product_arr = json_encode($data_arr['body']);
            $baseUrl = $elastic_host.'/'.$elastic_index.'/'.$data_arr['type'].'/'.$data_arr['id'];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $baseUrl);
            curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, 0);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $product_arr);
            $response = curl_exec($curl);
            curl_close($curl);
            return;
        }
        catch(\Exception $e)
        {
            return false;
        }
    } 


    public function bulk_index($products_arr=false)
    {
       
        try{
            $elastic_host = env("ELASTIC_HOST");
            $elastic_index= env("ELASTIC_INDEX_NAME");
            //dd($elastic_host,$elastic_index);
            $products_arr = array_map(function($_chunk){
                return json_encode($_chunk);
            }, $products_arr);

            $data = implode($products_arr,"\n")."\n";
            $baseUrl = $elastic_host.'/'.$elastic_index.'/_bulk';
           
            $header = array(
                "content-type: application/x-ndjson"
            );
           
            $curl = curl_init($baseUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $res = curl_exec($curl);
            curl_close($curl);
            //dd($res);
            return $res;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function curl_count($param=false)
    {
        try
        {
            $elastic_host = env("ELASTIC_HOST");
            $elastic_index= env("ELASTIC_INDEX_NAME");
            $baseUrl = $elastic_host."/".$elastic_index."/_count";
            
            $header = array(
                "content-type: application/json"
            );

            $param = json_encode($param['body']);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $baseUrl);
            curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
            $temp_response = curl_exec($curl);
            
            curl_close($curl);   
           
            $temp_response = json_decode($temp_response);
            $count = isset($temp_response->count)?intval($temp_response->count):0;
            
            return $count;
        }
        catch(\ Exception $e)
        {
            return false;
        }
    }

    public function zip_reindex_products($isZipRequest = false)
    {
       
        /* Index Elastic Search */
        ini_set('max_execution_time', 0);
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        foreach (ProductsModel::cursor() as $product) {
            $final_search_arr = $product->toSearchArray();
            $sub_category_arr = $product->productSubCategories->toArray();
            $sub_cat_arr = [];
            $sub_cat_name_arr = [];
            $sub_cat_plurls = [];

            foreach ($sub_category_arr as $sub_cat_key => $sub_cat_value) {
                $sub_category_name = get_subcategory_name($sub_cat_value['sub_category_id']);
                $sub_cat_name_arr[] = $sub_category_name;
                $sub_cat_arr[] = $sub_cat_value['sub_category_id'];
                $sub_cat_plurls[] = str_plural($sub_category_name);
            }

            $vendor_id = $product->user_id;

            $is_vendor_active_obj = $this->UserModel->select('status')
                ->where('id', $vendor_id)
                ->first();

            if (isset($is_vendor_active_obj)) {
                $is_vendor_active = $is_vendor_active_obj->toArray();
            } 

            $final_search_arr['is_vendor_active'] = isset($is_vendor_active['status']) ? intval($is_vendor_active['status']) : 0;
             $final_search_arr['is_vendor_approved'] = isset($is_vendor_active['is_approved']) ? intval($is_vendor_active['is_approved']) : 0;
            $final_search_arr['category_name'] = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;

            $final_search_arr['is_category_set'] = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;

            $final_search_arr['brand_name'] = isset($product->brand_details->brand_name) ? $product->brand_details->brand_name : null;
            $final_search_arr['created_at_formatted'] = strtotime($final_search_arr['created_at']);
            $final_search_arr['updated_at_formatted'] = strtotime($final_search_arr['updated_at']);
            $final_search_arr['unit_wholsale_price'] = floatval($final_search_arr['unit_wholsale_price']);
            $final_search_arr['retail_price'] = floatval($final_search_arr['retail_price']);
            $final_search_arr['available_qty'] = floatval($final_search_arr['available_qty']);
            $final_search_arr['is_active'] = intval($final_search_arr['is_active']);
            $final_search_arr['previous_status'] = intval($final_search_arr['is_active']);
            $final_search_arr['product_complete_status'] = intval($final_search_arr['product_complete_status']);

            $final_search_arr['product_status'] = intval($final_search_arr['product_status']);

            $final_search_arr['sub_cat_arr'] = $this->clean_array($sub_cat_arr);
            $final_search_arr['sub_cat_plural_arr'] = $this->clean_array($sub_cat_plurls);

            $final_search_arr['sub_cat_name_arr'] = $this->clean_array($sub_cat_name_arr);
            $final_search_arr['cat_name_plural'] = isset($final_search_arr['category_name']) ? str_plural($final_search_arr['category_name']) : '';

            $final_search_arr['product_name_plural'] = isset($final_search_arr['product_name']) ? str_plural($final_search_arr['product_name']) : '';

            $final_search_arr['category_name_alphanumeric_only'] = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['category_name']);
            $final_search_arr['brand_name_alphanumeric_only'] = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['brand_name']);
            $final_search_arr['product_name_alphanumeric_only'] = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['product_name']);
            // dd($final_search_arr);
            try {
                $elasticsearch->index(
                    [
                        'index' => $product->getSearchIndex(),
                        'type' => $product->getSearchType(),
                        'id' => $product->getKey(),
                        'body' => $final_search_arr,
                    ]
                );
            } catch (\Exception $e) {
                dump($final_search_arr);
                echo $e->getMessage();
            }
            // dump($final_search_arr);
        }

        if ($isZipRequest != false) {
            return true;
        } else {
            return 'success';
            dd("Indexing Successful");
        }
    }

    public function clean_array($arr_tmp = [])
    {
        $tmp = json_encode(array_filter($arr_tmp));
        // return json_decode($tmp,true);
        return $tmp;
    }

    public function flush_index_products()
    {
        try
        { 
            $elastic_host   = env("ELASTIC_HOST");
            $elastic_index  = env("ELASTIC_INDEX_NAME");
            $baseUrl        = $elastic_host.'/'.$elastic_index;

            $header = array(
                    "content-type: application/json"
            );
       
          
            $curl = curl_init($baseUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            $response = curl_exec($curl);
            curl_close($curl);
            
            return true;
        }
        catch(\ Exception $e)
        {
            return false;
        }
    }

   
   
    public function search(Request $request, $per_page = 12)
    {
        $elastic_host  = env("ELASTIC_HOST");
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $category_id_arr = [];

        $user_id = 0;
        $user = Sentinel::check();



        if ($user) {
            $user_id = $user->id;
        }

       
        $current_page = $request->input('page');

        /*  Converting Negative to Positive */
        $current_page = isset($current_page)?abs(intval($current_page)):1;

        $page_offset = 0;

        $search_term = $request->input('search_term');
        if ($search_term == null) {
            $search_term = '';
        }

        $category_id = $request->input('category_id');
        $subcategory_id = $request->input('subcategory');

        $third_subcategory_id = $request->input('thirdsubcategory');
        $fourth_subcategory_id = $request->input('fourthsubcategory');

        $vendor_id = $request->input('vendor_id');
        $brand_id = $request->input('brand_id');
        $country_id = $request->input('country_id');
        
        /* getting and converting category id array into array*/
        
        $temp_category_id_arr = $request->input('category_id_arr');
        
        if(isset($temp_category_id_arr))
        {
         $temp_category_id_arr = explode(',',$temp_category_id_arr);
         $temp_category_id_arr = str_replace('"','',$temp_category_id_arr);
         $temp_category_id_arr = str_replace(']','',$temp_category_id_arr);
         $temp_category_id_arr = str_replace('[','',$temp_category_id_arr);
         $category_id_arr = $temp_category_id_arr;
        }

        if(isset($category_id))
        {
            unset($category_id_arr);
        }
        /* unset category id when request has category id in it*/
        
        $low_price  = $request->input('price:low');
        $high_price = $request->input('price:high');
        
        $vendor_minimum_high = $request->input('vendor_minimum_high');
        $vendor_minimum_low  = $request->input('vendor_minimum_low');

        $free_shipping = $request->input('free_shipping');
        $percent_of = $request->input('percent_of');
        $doller_of = $request->input('doller_of');

        $lead_time_min = $request->input('lead_time_min');
        $lead_time_max = $request->input('lead_time_max');

        $brands = $request->input('brands');

        if(isset($lead_time_min) && isset($lead_time_max))
        {
            $lead_time_min = intval($lead_time_min);
            $lead_time_max = intval($lead_time_max);
        }

        if(isset($vendor_minimum_low) && isset($vendor_minimum_high))
        {
            $vendor_minimum_low = intval($vendor_minimum_low);
            $vendor_minimum_high = intval($vendor_minimum_high);
        }

        if(isset($low_price) && isset($high_price))
        {
            $low_price = intval($low_price);
            $high_price = intval($high_price);
        }

        if(isset($free_shipping))
        {
            $free_shipping = intval($free_shipping);
        }

        if(isset($percent_of))
        {
            $percent_of = intval($percent_of);
        }

        if(isset($doller_of))
        {
            $doller_of = intval($doller_of);
        }
        $filter = $request->input('category');
        $initial_search = $request->input('name');
        $total_results =0;

        /*Elasticsearch search query start*/
        /*search request base conditions*/
        $search_param = [
            'index' => $elastic_index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ["is_active" => 1]],
                            ['term' => ["is_vendor_active" => 1]],
                            ['term' => ["is_vendor_approved" => 1]],
                            ['term' => ["product_complete_status" => 4]],
                            ['term' => ["is_deleted" => 0]],
                            ['term' => ["product_status" => 1]]
                        ],
                        'must_not' => [
                            ['terms' => ["product_complete_status" => [1, 2, 3]]],
                            ['terms' => ["is_active" => [0, 2]]],
                            ['terms' => ["is_deleted" => [1]]],
                            ['terms' => ["product_status" => [0]]],
                            ['terms' => ["is_vendor_approved" => [0]]],
                            ['terms' => ["is_vendor_active" => [0]]]
                        ]
                    ]
                ]
            ],
        ];

      
        if(isset($vendor_id))
        {
            $vendor_id = intval(base64_decode($vendor_id));
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["user_id" => $vendor_id]];

            $search_param['body']['sort'] = [
                ["updated_at_formatted" => ["order" => "desc"]]
            ];
        }    

        if(isset($country_id))
        { 
            $country_id = intval(base64_decode($country_id));

            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["country_id" => $country_id]];

            $search_param['body']['sort'] = [
                ["updated_at_formatted" => ["order" => "desc"]]
            ];

           
        }  

        if(isset($brands) && sizeof($brands)>0)
        {  
            $brand_arr = [];
            $brands = explode(',', $brands[0]);
            if($brands[0]!='')
            {   
                for($i=0;$i<sizeof($brands);$i++)
                {   
                    $filter_brand_id = intval($brands[$i]);
                    array_push($brand_arr,$filter_brand_id);
                }
                $search_param['body']['query']['bool']['must'][] = ['terms' => ["brand" => $brand_arr]];
            }
        }  


 
        if(isset($category_id_arr) && count($category_id_arr)>0)
        {
            $search_param['body']['query']['bool']['must'][] = ['terms' => ["category_id" => $category_id_arr]];
        }

        if(isset($category_id))
        {   
            $category_id = intval(base64_decode($category_id));
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["category_id" => $category_id]];
        }       

        if(isset($subcategory_id))
        {
            $subcategory_id = intval(base64_decode($subcategory_id));
            $sub_cat_name =  get_subcategory_name($subcategory_id);

            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["category_id" => $category_id]];
            $search_param['body']['query']['bool']['must'][] = ['match_phrase' => ["sub_cat_name_arr" => $sub_cat_name]];

        }  

        if(isset($third_subcategory_id))
        {
            $third_subcategory_id = intval(base64_decode($third_subcategory_id));
            $sec_sub_cat_name =  get_second_subcategory_name($third_subcategory_id);
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["category_id" => $category_id]];
            // $search_param['body']['query']['bool']['must'][] = ["term" =>  ["subcategory_id" => $subcategory_id]];
            $search_param['body']['query']['bool']['must'][] = ['match_phrase' => ["sec_sub_cat_name_arr" => $sec_sub_cat_name]];
        } 
        
        if(isset($fourth_subcategory_id))
        {
            $fourth_subcategory_id = intval(base64_decode($fourth_subcategory_id));
            $third_sub_cat_name =  get_third_subcategory_name($fourth_subcategory_id);
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["category_id" => $category_id]];
            // $search_param['body']['query']['bool']['must'][] = ["term" =>  ["subcategory_id" => $subcategory_id]];
            // $search_param['body']['query']['bool']['must'][] = ["term" =>  ["sec_subcategory_id" => $third_subcategory_id]];
            $search_param['body']['query']['bool']['must'][] = ['match_phrase' => ["third_sub_cat_name_arr" => $third_sub_cat_name]];
        }  


        if(isset($low_price) && isset($high_price))
        {
            $user = \Sentinel::check();
           
            if($user && $user->inRole('maker'))
            {   
                  if($low_price==100)
                 {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price]]];
                 }
                 else
                 {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price,"lte"=>$high_price]]];
                 }
                 $search_param['body']['sort'] = [
                 ["retail_price" => ["order" => "asc"]]
                 ];
            }
            else if($user && $user->inRole('customer'))
            {   
                if($low_price==100)
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price]]];
                }
                else
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price,"lte"=>$high_price]]];
                }
                $search_param['body']['sort'] = [
                ["retail_price" => ["order" => "asc"]]
                ];
            }
            else if($user && $user->inRole('influencer'))
            {   
                if($low_price==100)
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price]]];
                }
                else
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price,"lte"=>$high_price]]];
                }
                $search_param['body']['sort'] = [
                ["retail_price" => ["order" => "asc"]]
                ];
            }
            else if($user)
            {
                if($low_price==100)
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["unit_wholsale_price" => ["gte"=> $low_price]]];
                }
                else
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["unit_wholsale_price" => ["gte"=> $low_price,"lte"=>$high_price]]];
                }
                $search_param['body']['sort'] = [
                ["unit_wholsale_price" => ["order" => "asc"]]
                ]; 
            }
            else
            {     
                if($low_price==100)
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price]]];
                }
                else
                {
                    $search_param['body']['query']['bool']['must'][] = ['range'=> ["retail_price" => ["gte"=> $low_price,"lte"=>$high_price]]];
                }
                $search_param['body']['sort'] = [
                ["retail_price" => ["order" => "asc"]]
                ];
            } 
        }

        
        if(isset($vendor_minimum_low) && isset($vendor_minimum_high))
        {   
           /* cond 1 :No minimum*/
            if($vendor_minimum_high==0 && $vendor_minimum_low!=301)
            {
                $search_param['body']['query']['bool']['must'][] = ["term" =>  ["vendor_minimum" => 0]];
            }
             /* cond 2 : 301 or more.*/
            elseif($vendor_minimum_low==301)
            {   
               $search_param['body']['query']['bool']['must'][] = ['range'=> ["vendor_minimum" => ["gte"=> $vendor_minimum_low]]]; 
            }
           /* cond 3 : vendor minimum between range*/
            else
            {
            $search_param['body']['query']['bool']['must'][] = ['range'=> ["vendor_minimum" => ["gte"=> $vendor_minimum_low,"lte"=>$vendor_minimum_high]]];
            }
        }

        if(isset($lead_time_min) && isset($lead_time_max))
        {
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["lead_time_min" => $lead_time_min]];
            $search_param['body']['query']['bool']['must'][] = ["term" =>  ["lead_time_max" => $lead_time_max]];
        }  
              

        if(isset($percent_of) && isset($doller_of) && isset($free_shipping))
        {
            $search_param['body']['query']['bool']['must'][] = ['terms' => ["shipping_type" => [1,2,3]]];
        }

       else if(isset($percent_of) && isset($doller_of))
        {  
            $search_param['body']['query']['bool']['must'][] = ['terms' => ["shipping_type" => [2,3]]];
        }

        else if(isset($free_shipping) && isset($doller_of))
        {
            $search_param['body']['query']['bool']['must'][] = ['terms' => ["shipping_type" => [1,3]]];
        }

        else if(isset($free_shipping) && isset($percent_of))
        {
            $search_param['body']['query']['bool']['must'][] = ['terms' => ["shipping_type" => [1,2]]];
        }

        else if(isset($free_shipping))
        {
            $search_param['body']['query']['bool']['must'][] = ['term' => ["shipping_type" => 1]];
        }
        
        else if(isset($percent_of))
        {
            $search_param['body']['query']['bool']['must'][] = ['term' => ["shipping_type" => 2]];
        }

        else if(isset($doller_of))
        {
            $search_param['body']['query']['bool']['must'][] = ['term' => ["shipping_type" => 3]];
        }
        if(isset($brand_id))
        {
            $brand_id = intval(base64_decode($brand_id));
            $search_param['body']['query']['bool']['must'][] = ['term' => ["brand" => $brand_id]];
             $search_param['body']['sort'] = [
                ["updated_at_formatted" => ["order" => "desc"]]
            ];
        }

        if (isset($filter) && $filter == "new_arrivals") {
            $search_param['body']['sort'] = [
                ["created_at_formatted" => ["order" => "desc"]]
            ];
        }

        if (isset($filter) && $filter == "best_seller") {
            $search_param['body']['query']['bool']['must'][] = [
                'term' => ["is_best_seller" => 1]
            ];
        }

        $sort = [];
        if(isset($search_param['body']['sort']))
        {
            $sort =  $search_param['body']['sort'];
        }
        unset($search_param['body']['sort']);
        
        if ($search_term != "") 
        {
            $response['hits']['hits'] = []; 

            $is_suggetion = $request->input('is_suggetion');
            if($is_suggetion!="1")
            {   
                session()->push('products.name', $search_term);
            }
          
           
            try {
                   /*first query to check vendor name with exact input search name is avilable*/

                   $search_param['body']['query']['bool']['must'][] = [
                        'simple_query_string' => [
                        "query" => $search_term,
                        "fields" => ["brand_name"
                        ],
                        "minimum_should_match"=>'100%',
                    ]
                    ];
                    $total_results = $this->curl_count($search_param);

                //if brand name query returns zero result will fire general text search query
                if($total_results==0)
                {              
                  
                    $condition_count = count($search_param['body']['query']['bool']['must']);
                    $condition_count = isset($condition_count)?intval($condition_count-1):0;

                    $search_param['body']['query']['bool']['must'][$condition_count] = [
                    'simple_query_string' => [
                    "query" => "*" . $search_term . "*",
                    "fields" => [
                        "product_name", "category_name", "product_name_alphanumeric_only", 'brand_name^5',
                        'brand_name_alphanumeric_only^5', 'sub_cat_name_arr', 'sub_cat_plural_arr','sec_sub_cat_name_arr','third_sub_cat_name_arr', 'cat_name_plural', 'product_name_plural'
                    ],
                  
                    "default_operator" => "or",
                    "analyze_wildcard" => true,
                    "minimum_should_match"=>'100%',
                    ]
                    ];  
                    
                    $total_results = $this->curl_count($search_param);


                    
                }
            } catch (\Exception $e) {
                
                $total_results = 0;
               
            }
        }


        //search query when no input search term is avilable
        if($total_results==0)
        {  


            try 
            {
                $total_results = $this->curl_count($search_param);
            } 
            catch(\Exception $e)
            {
                $total_results = 0;
            }

            
        }
       /*pagination logic start*/
        
        $total_page = intval(ceil($total_results / $per_page));

        if ($current_page > $total_page) {
            $current_page = $total_page;
        }

        $page_offset = ($current_page - 1) * $per_page;
        /* Apply Offset */

        $search_param['body']['size'] = $per_page;
        $search_param['body']['from'] = $page_offset;

        if (isset($search_param['filter_path'])) {
            unset($search_param['filter_path']);
        }   

        /*pagination logic end*/

        if ($total_results == 0) {
            $this->arr_view_data['arr_data'] = [];

            if(isset($country_id) && $country_id!='')
            {
                 $this->arr_view_data['page_title'] = 'American Best Sellers';
            }
            else{
                $this->arr_view_data['page_title'] = isset($form_data['search_term']) ? $form_data['search_term'] : 'Product(s)';  
            }
          
            return $this->arr_view_data;
        }
        //final search on recived search parameters
        try 
        {   
            $search_param['body']['sort'] = $sort;
            $response = $this->curl_search($search_param);
        } 
        catch (\Exception $e)
        {
            $total_results = 0;
            
            $this->arr_view_data['arr_data'] = [];
            
            if(isset($country_id) && $country_id!='')
            {
                 $this->arr_view_data['page_title'] = 'American Best Sellers';
            }
            else{
                $this->arr_view_data['page_title'] = isset($form_data['search_term']) ? $form_data['search_term'] : 'Product(s)';  
            }
          

            return $this->arr_view_data;
        }
        /*Elasticsearch search query end*/
        /*get favorite products id's for logged in user start*/
        $fav_product_arr  = $fav_maker_arr = [];
        if($user != false && $user->inRole('retailer'))
        {
            $fav_product_arr = $this->FavoriteModel
                                    ->where('retailer_id', $user_id)
                                    ->where('type', 'product')
                                    ->get()
                                    ->toArray();

            $fav_maker_arr  = $this->FavoriteModel
                                   ->where('retailer_id', $user_id)   
                                   ->where('type', 'maker')
                                   ->get()
                                   ->toArray();
        }
        else if($user != false && $user->inRole('customer'))
        {
            $fav_product_arr = $this->CustomerFavoriteModel
                                    ->where('customer_id', $user_id)
                                    ->where('type', 'product')
                                    ->get()
                                    ->toArray();

            $fav_maker_arr  = $this->CustomerFavoriteModel
                                   ->where('customer_id', $user_id)   
                                   ->where('type', 'maker')
                                   ->get()
                                   ->toArray();
        }

        $fav_product_id_arr = array_column($fav_product_arr, 'product_id');
        $fav_maker_id_arr   = array_column($fav_maker_arr, 'maker_id');

        /*get favorite products id's for logged in user end*/
        
        if(isset($country_id) && $country_id!='')
        {
             $this->arr_view_data['page_title'] = 'American Best Sellers';
        }
        else{
            $this->arr_view_data['page_title'] = isset($form_data['search_term']) ? $form_data['search_term'] : 'Product(s)';  
        }

        $this->arr_view_data['arr_data']           = $response['hits']['hits'];
        $this->arr_view_data['total_results']      = $total_results;
        $this->arr_view_data['arr_pagination']     = $this->get_pagination_data($this->arr_view_data['arr_data'], $total_results, $per_page, $request->all());
        $this->arr_view_data['fav_product_arr']    = $fav_product_id_arr;
        $this->arr_view_data['fav_maker_id_arr']   = $fav_maker_id_arr;
       // $this->arr_view_data['page_title']         = isset($form_data['search_term']) ? $form_data['search_term'] : 'Product(s)';

        $is_by_ajax = $request->input('by_ajax');
        
        //get category suggetions
        $category_arr = get_category();
        $category_name_arr = [];
        if(count($category_arr)>0)
        {
            foreach ($category_arr as $cat_key => $cat_value) {
            $category_name_arr[$cat_key]['category_name'] = $cat_value['category_name'];
            $category_name_arr[$cat_key]['category_id'] = $cat_value['id'];
            }
        }
            
        
       //get vendor suggetions
        $vendor_arr = $this->get_search_vendor();
        $vendor_name_arr = [];
        if(count($vendor_arr)>0)
        {
            foreach ($vendor_arr as $vendor_key => $vendor_value) {
            $vendor_name_arr[$vendor_key]['brand_name'] = $vendor_value->company_name;
            $vendor_name_arr[$vendor_key]['user_id'] = $vendor_value->id;
        } 
        }        
        $response = $recently_searched = [];
        
        if ($is_by_ajax == 1)
        {
            //get recently search suggetions
            $recently_searched = $request->session()->get('products.name');

            if (isset($recently_searched) && count($recently_searched) > 0) {
                $recently_searched = array_unique($recently_searched);
                $recently_searched = array_reverse($recently_searched);
            }

            $response['status'] = "success";
            $response['data'] = $this->arr_view_data['arr_data'];
            $response['data'] = array_column($response['data'], '_source');
            $response['recently_searched'] = $recently_searched;
            $response['category_data'] = $category_name_arr;
            $response['vendor_data'] = $vendor_name_arr;
        }
        $this->arr_view_data['is_by_ajax'] = $is_by_ajax;
        $this->arr_view_data['response'] = $response;
        return $this->arr_view_data;
    }

    public function initiate_index_product($product_id = false)
    {
        
        ini_set('max_execution_time', 0);
        
        $elastic_host  = env("ELASTIC_HOST");
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $type          = "products";
        $elasticsearch = ClientBuilder::create()
                        ->setHosts([$elastic_host])
                        ->build();
        
        $product_id = isset($product_id) ? $product_id : 0;
        $product = ProductsModel::where('id', $product_id)->first();
        
        $final_search_arr = $product->toSearchArray();
        $sub_category_arr = $product->productSubCategories->toArray();

        $sub_cat_arr = [];
        $sub_cat_arr = [];
        $sub_cat_name_arr = [];
        $sub_cat_plurls = [];

        $sec_sub_cat_arr = [];
        $sec_sub_cat_name_arr = [];
        $sec_sub_cat_plurls = [];

        $third_sub_cat_arr = [];
        $third_sub_cat_name_arr = [];
        $third_sub_cat_plurls = [];

        foreach ($sub_category_arr as $sub_cat_key => $sub_cat_value) {
            $sub_category_name = get_subcategory_name($sub_cat_value['sub_category_id']);
            $sub_cat_name_arr[] = $sub_category_name;
            $sub_cat_arr[] = $sub_cat_value['sub_category_id'];
            $sub_cat_plurls[] = str_plural($sub_category_name);

            if($sub_cat_value['third_sub_category_id'])
            {
                $sec_sub_category_name = get_second_subcategory_name($sub_cat_value['third_sub_category_id']);
                $sec_sub_cat_name_arr[] = $sec_sub_category_name;
                $sec_sub_cat_arr[] = $sub_cat_value['third_sub_category_id'];
                $sec_sub_cat_plurls[] = str_plural($sec_sub_category_name);
            }

            if($sub_cat_value['fourth_sub_category_id'])
            {
                $third_sub_category_name = get_third_subcategory_name($sub_cat_value['fourth_sub_category_id']);
                $third_sub_cat_name_arr[] = $third_sub_category_name;
                $third_sub_cat_arr[] = $sub_cat_value['fourth_sub_category_id'];
                $third_sub_cat_plurls[] = str_plural($third_sub_category_name);
            }
        }
        
        $vendor_id = $product->user_id;

        $is_vendor_active_obj = $this->UserModel->select(['status','is_approved','country_id'])
            ->where('id',$vendor_id)
            ->first();

        if (isset($is_vendor_active_obj)) {
            $is_vendor_active = $is_vendor_active_obj->toArray();
        } 
        $vendor_minimum_obj = $this->ShopSettings->select('first_order_minimum', 'shop_lead_time')
                                                  ->where('maker_id', $vendor_id)
                                                  ->first();

        $country_id = isset($is_vendor_active_obj['country_id'])?$is_vendor_active_obj['country_id']:0;

        if (isset($vendor_minimum_obj)) {
            $vendor_minimum = $vendor_minimum_obj->toArray();
        } else {
            $vendor_minimum['first_order_minimum'] = 0;
            $vendor_minimum['shop_lead_time'] = 0;
        }

        if (isset($vendor_minimum['shop_lead_time'])) {
            if ($vendor_minimum['shop_lead_time'] != "0") {
                $lead_time_min = isset($vendor_minimum['shop_lead_time'][0]) ? intval(explode("-", $vendor_minimum['shop_lead_time'])[0]) : 2;

                $lead_time_max = isset($vendor_minimum['shop_lead_time'][1]) ? intval(explode("-", $vendor_minimum['shop_lead_time'])[1]) : 4;
            }
        }


        $final_search_arr['lead_time_min']          = isset($lead_time_min) ? intval($lead_time_min) : 0;
        $final_search_arr['lead_time_max']          = isset($lead_time_max) ? intval($lead_time_max) : 0;
        $final_search_arr['country_id']            =  $country_id;
        $final_search_arr['vendor_minimum']         = isset($vendor_minimum['first_order_minimum']) ? intval($vendor_minimum['first_order_minimum']) : 0;
        $final_search_arr['shipping_type']          = isset($final_search_arr['shipping_type']) ? intval($final_search_arr['shipping_type']) : 0;
        $final_search_arr['is_vendor_active']       = isset($is_vendor_active['status']) ? intval($is_vendor_active['status']) : 0;
        $final_search_arr['is_vendor_approved']     = isset($is_vendor_active['is_approved']) ? intval($is_vendor_active['is_approved']) : 0;
        $final_search_arr['category_name']          = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;
        $final_search_arr['is_category_set']        = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;
        $final_search_arr['brand_name']             = isset($product->brand_details->brand_name) ? $product->brand_details->brand_name : null;
        $final_search_arr['created_at_formatted']   = strtotime($final_search_arr['created_at']);
        $final_search_arr['updated_at_formatted']   = strtotime($final_search_arr['updated_at']);
        $final_search_arr['unit_wholsale_price']    = floatval($final_search_arr['unit_wholsale_price']);
        $final_search_arr['retail_price']           = floatval($final_search_arr['retail_price']);
        $final_search_arr['available_qty']          = floatval($final_search_arr['available_qty']);
        $final_search_arr['is_active']              = isset($final_search_arr['is_active'])?intval($final_search_arr['is_active']):0;
        $final_search_arr['is_deleted']             = isset($final_search_arr['is_deleted'])?intval($final_search_arr['is_deleted']):1;
        $final_search_arr['previous_status']        = intval($final_search_arr['is_active']);
        $final_search_arr['product_complete_status']= isset($final_search_arr['product_complete_status'])?intval($final_search_arr['product_complete_status']):0;
        $final_search_arr['product_status']         = intval($final_search_arr['product_status']);
        $final_search_arr['sub_cat_arr']            = $this->clean_array($sub_cat_arr);
        $final_search_arr['sub_cat_plural_arr']     = $this->clean_array($sub_cat_plurls);
        $final_search_arr['sub_cat_name_arr']       = $this->clean_array($sub_cat_name_arr);

        $final_search_arr['sec_sub_cat_arr']        = $this->clean_array($sec_sub_cat_arr);
        $final_search_arr['sec_sub_cat_plurls']     = $this->clean_array($sec_sub_cat_plurls);
        $final_search_arr['sec_sub_cat_name_arr']   = $this->clean_array($sec_sub_cat_name_arr);

        $final_search_arr['third_sub_cat_arr']      = $this->clean_array($third_sub_cat_arr);
        $final_search_arr['third_sub_cat_plurls']   = $this->clean_array($third_sub_cat_plurls);
        $final_search_arr['third_sub_cat_name_arr'] = $this->clean_array($third_sub_cat_name_arr);

        $final_search_arr['cat_name_plural']        = isset($final_search_arr['category_name']) ? str_plural($final_search_arr['category_name']) : '';
        $final_search_arr['product_name_plural']    = isset($final_search_arr['product_name']) ? str_plural($final_search_arr['product_name']) : '';
        $final_search_arr['category_name_alphanumeric_only'] = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['category_name']);
        $final_search_arr['brand_name_alphanumeric_only']    = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['brand_name']);
        $final_search_arr['product_name_alphanumeric_only']  = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['product_name']);
        
        $temp_index_arr  = [
                'index' => $elastic_index,
                'type'  => $type,
                'id'    => $product_id,
                'body'  => $final_search_arr,
            ];
        
        $this->curl_index($temp_index_arr);
        return;
    }

    
    public function bulk_index_products($products_arr=false)
    {

        set_time_limit(0);
        if($products_arr==false || count($products_arr)<0)
        { 
            return;
        }
        try{
            $elastic_host = env("ELASTIC_HOST");
            $elastic_index= env("ELASTIC_INDEX_NAME");
            
            $elasticsearch = ClientBuilder::create()
                            ->setHosts([$elastic_host])
                            ->build();
            $temp_param =[];
            $id_arr     =[];

            $arr_notify_data                 = [];
            $arr_notify_data['from_user_id'] = 1;
            $arr_notify_data['to_user_id']   = 1;
            $arr_notify_data['description']  = "Vendor Active Process start";
            $arr_notify_data['title']        = 'Job Notification';
            $arr_notify_data['type']         = 'admin';  
            $this->save_notification($arr_notify_data);
            
            foreach ($products_arr as $key => $value) 
            {
                $product_id       = isset($value) ? $value : 0;
                $product          = ProductsModel::where('id', $product_id)->first();
                $final_search_arr = $product->toSearchArray();
                $sub_category_arr = $product->productSubCategories->toArray();
                $sub_cat_arr      = [];
                $sub_cat_arr      = [];
                $sub_cat_name_arr = [];
                $sub_cat_plurls   = [];
                $temp_param       = [];
            
                foreach($sub_category_arr as $sub_cat_key => $sub_cat_value)
                {
                    $sub_category_name = get_subcategory_name($sub_cat_value['sub_category_id']);
                    $sub_cat_name_arr[] = $sub_category_name;
                    $sub_cat_arr[] = $sub_cat_value['sub_category_id'];
                    $sub_cat_plurls[] = str_plural($sub_category_name);
                }

                $vendor_id = $product->user_id;
                $is_vendor_active_obj = $this->UserModel->select(['status','is_approved','country_id'])
                                                        ->where('id',$vendor_id)
                                                        ->first();

                if(isset($is_vendor_active_obj))
                {
                    $is_vendor_active = $is_vendor_active_obj->toArray();
                } 

                $country_id = isset($is_vendor_active_obj['country_id'])?$is_vendor_active_obj['country_id']:0;
                
                $vendor_minimum_obj = $this->ShopSettings->select('first_order_minimum', 'shop_lead_time')
                                                         ->where('maker_id', $vendor_id)
                                                         ->first();
                if(isset($vendor_minimum_obj))
                {
                    $vendor_minimum = $vendor_minimum_obj->toArray();
                } 
                else
                {
                    $vendor_minimum['first_order_minimum'] = 0;
                    $vendor_minimum['shop_lead_time'] = 0;
                }

                if (isset($vendor_minimum['shop_lead_time'])) {
                    if ($vendor_minimum['shop_lead_time'] != "0") {
                        
                        $lead_time_min = isset($vendor_minimum['shop_lead_time'][0]) ? intval(explode("-", $vendor_minimum['shop_lead_time'])[0]) : 2;

                        $lead_time_max = isset($vendor_minimum['shop_lead_time'][1]) ? intval(explode("-", $vendor_minimum['shop_lead_time'])[1]) : 4;
                    }
                }

                $final_search_arr['lead_time_min']          = isset($lead_time_min) ? intval($lead_time_min) : 0;
                $final_search_arr['lead_time_max']          = isset($lead_time_max) ? intval($lead_time_max) : 0;
                $final_search_arr['country_id']             =  isset($country_id)?intval($country_id):0;
                $final_search_arr['vendor_minimum']         = isset($vendor_minimum['first_order_minimum']) ? intval($vendor_minimum['first_order_minimum']) : 0;
                $final_search_arr['shipping_type']          = isset($final_search_arr['shipping_type']) ? intval($final_search_arr['shipping_type']) : 0;
                $final_search_arr['is_vendor_active']       = isset($is_vendor_active['status']) ? intval($is_vendor_active['status']) : 0;
                $final_search_arr['is_vendor_approved']     = isset($is_vendor_active['is_approved']) ? intval($is_vendor_active['is_approved']) : 0;
                $final_search_arr['category_name']          = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;
                $final_search_arr['is_category_set']        = isset($product->categoryDetails->category_name) ? $product->categoryDetails->category_name : null;
                $final_search_arr['brand_name']             = isset($product->brand_details->brand_name) ? $product->brand_details->brand_name : null;
                $final_search_arr['created_at_formatted']   = strtotime($final_search_arr['created_at']);
                $final_search_arr['updated_at_formatted']   = strtotime($final_search_arr['updated_at']);
                $final_search_arr['unit_wholsale_price']    = floatval($final_search_arr['unit_wholsale_price']);
                $final_search_arr['retail_price']           = floatval($final_search_arr['retail_price']);
                $final_search_arr['available_qty']          = floatval($final_search_arr['available_qty']);
                $final_search_arr['is_active']              = isset($final_search_arr['is_active'])?intval($final_search_arr['is_active']):0;
                $final_search_arr['is_deleted']             = isset($final_search_arr['is_deleted'])?intval($final_search_arr['is_deleted']):1;
                $final_search_arr['previous_status']        = intval($final_search_arr['is_active']);
                $final_search_arr['product_complete_status']= isset($final_search_arr['product_complete_status'])?intval($final_search_arr['product_complete_status']):0;
                $final_search_arr['product_status']         = intval($final_search_arr['product_status']);
                $final_search_arr['sub_cat_arr']            = $this->clean_array($sub_cat_arr);
                $final_search_arr['sub_cat_plural_arr']     = $this->clean_array($sub_cat_plurls);
                $final_search_arr['sub_cat_name_arr']       = $this->clean_array($sub_cat_name_arr);
                $final_search_arr['cat_name_plural']        = isset($final_search_arr['category_name']) ? str_plural($final_search_arr['category_name']) : '';
                $final_search_arr['product_name_plural']    = isset($final_search_arr['product_name']) ? str_plural($final_search_arr['product_name']) : '';
                $final_search_arr['category_name_alphanumeric_only'] = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['category_name']);
                $final_search_arr['brand_name_alphanumeric_only']    = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['brand_name']);
                $final_search_arr['product_name_alphanumeric_only']  = preg_replace("/[^a-zA-Z0-9]+/", "", $final_search_arr['product_name']);

                    $id_arr[] = [
                                    'index' => [
                                        '_id' => $product_id,
                                        '_index' => $elastic_index,
                                        '_type' => 'products'
                                    ]
                                ];
                    $id_arr[] =  $final_search_arr;

            }
                  //dd($final_search_arr);
                $this->bulk_index($id_arr);
                
                $arr_notify_data                 = [];
                $arr_notify_data['from_user_id'] = 1;
                $arr_notify_data['to_user_id']   = 1;
                $arr_notify_data['description']  = "Vendor Active Process Complete";
                $arr_notify_data['title']        = 'Job Notification';
                $arr_notify_data['type']         = 'admin';  
                $this->save_notification($arr_notify_data);   
                return;
        }
        catch(\Exception $e)
        {
                dd($e);
                $arr_notify_data                 = [];
                $arr_notify_data['from_user_id'] = 1;
                $arr_notify_data['to_user_id']   = 1;
                $arr_notify_data['description']  = "Vendor Active Process Failed,Please Try Again";
                $arr_notify_data['title']        = 'Job Notification';
                $arr_notify_data['type']         = 'admin';  
                $this->save_notification($arr_notify_data); 
                return;
        }
    }



    public function index_product($product_id = false)
    {
        ini_set('max_execution_time', 0);
        return dispatch(new IndexElasticProductJob($product_id, $this));
    }

    public function index_vendor_product($vendor_id = false)
    {   //dd("vendor",$vendor_id);
        set_time_limit(0);
        return dispatch(new BulkiIndexProductJob($vendor_id,
                                        $this));     
    }

    public function delete_product($product_id = false)
    {
        $product_id = isset($product_id)?intval($product_id):0;
        
        if ($product_id!=0) 
        {
            $elastic_host  =  env("ELASTIC_HOST");
            $elastic_index =  env("ELASTIC_INDEX_NAME");
            $baseUri = $elastic_host.'/'.$elastic_index.'/_delete_by_query';
           
            $header = array(
                "content-type: application/json"
            );
           
            $param = 
            [   'query' => [
                        'match' => [
                            '_id' => $product_id,
                        ],
                ],
                
            ];
            
            try
            {   
                $param = json_encode($param);
                $conn = curl_init($baseUri);
                curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($conn,CURLOPT_HTTPHEADER, $header);
                curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($conn, CURLOPT_FAILONERROR, false);
                curl_setopt($conn, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($conn, CURLOPT_POSTFIELDS, $param);
                $res = curl_exec($conn);
                
                return $res;
            }
            catch(\ Exception $e)
            {
                return false;
            }
        }
    }

    public function delete_vendor_product($vendor_id = false)
    {
        
        ini_set('max_execution_time', 0);
        $elastic_host   = env("ELASTIC_HOST");
        $elastic_index  = env("ELASTIC_INDEX_NAME");
        $vendor_id      = isset($vendor_id)?intval($vendor_id):0;
        
        if ($vendor_id!=0) 
        {
            $baseUri = $elastic_host.'/'.$elastic_index.'/_delete_by_query';
           
            $header = array(
                "content-type: application/json"
            );
           
            $param = 
            [   'query' => [
                        'match' => [
                            'user_id' => $vendor_id,
                        ],
                ],
                
            ];
            
            try
            {   
                $param = json_encode($param);
                $conn = curl_init($baseUri);
                curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($conn,CURLOPT_HTTPHEADER, $header);
                curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($conn, CURLOPT_FAILONERROR, false);
                curl_setopt($conn, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($conn, CURLOPT_POSTFIELDS, $param);
                $res = curl_exec($conn);
                
                return $res;
            }
            catch(\ Exception $e)
            {
                return false;
            }
        }
    }

     public function change_vendor_product_status($vendor_id=false)
    {
        ini_set('max_execution_time', 0);
        if(isset($vendor_id))
        {
            $this->index_vendor_product($vendor_id);
        }
        return;
    }

    public function activate_product($product_id = false, $previous_status = false)
    {
        if(isset($product_id))
        {
            $this->initiate_index_product($product_id);
        }
        return;
    }

    public function decactivate_product($product_id = false, $previous_status = false)
    {
       if(isset($product_id))
       {
            $this->initiate_index_product($product_id);
       }
        return;
    }

    public function decactivate_vendor($product_id)
    {
       if(isset($product_id))
       {
         $this->initiate_index_product($product_id);
       }
       return;
    }

    public function deactivate_vendor_product($vendor_id=false)
    {
       if(isset($delete_vendor_product))
       {
        $this->delete_vendor_product($vendor_id);
       }
       return;
    }

    public function activate_vendor($product_id)
    {
       if(isset($product_id))
       {
         $this->initiate_index_product($product_id);
       }
       return;
    }

    public function activate_vendor_product($vendor_id)
    {
        $vendor_id = isset($vendor_id) ? intval($vendor_id) : 0;
        if($vendor_id!= 0)
        {
         $this->change_vendor_product_status($vendor_id);
        }
        return;
    }

    

    public function activate_category_product($category_id = false)
    {
        $category_id = isset($category_id) ? intval($category_id) : 0;
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $search_param['body']['query']['bool']['must'] = [
            [
                ['term' => ["category_id" => $category_id]],
                ['term' => ["product_complete_status" => 4]],
            ],
        ];

        $size = $this->ProductsModel->where('category_id', $category_id)->count();

        $search_param['body']['size'] = $size;

        $response = $elasticsearch->search($search_param);

        foreach ($response['hits']['hits'] as $result_key => $result_value) {
            if ($result_value['_source']['previous_status'] == 1) {
                if ($result_value['_index'] == $elastic_index) {
                    $this->activate_product($result_value['_id'], $result_value['_source']['previous_status']);
                }
            }
        }
    }

    public function deactivate_category_product($category_id = false)
    {
        $category_id = isset($category_id) ? intval($category_id) : 0;
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $search_param['body']['query']['bool']['must'] = [
            [
                ['term' => ["category_id" => $category_id]],

            ],

        ];

        $size = $this->ProductsModel->where('category_id', $category_id)->count();

        $search_param['body']['size'] = $size;
        $response = $elasticsearch->search($search_param);

        foreach ($response['hits']['hits'] as $result_key => $result_value) {
            if ($result_value['_index'] == $elastic_index) {
                $this->decactivate_product($result_value['_id'], $result_value['_source']['previous_status']);
            }
        }
    }

    public function deactivate_sub_category_product($category_id = false, $sub_category_id = false)
    {
        $category_id = isset($category_id) ? intval($category_id) : 0;
        $sub_category_id = isset($sub_category_id) ? intval($sub_category_id) : 0;
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $search_param['body']['query']['bool']['must'] = [
            [
                ['term' => ["category_id" => $category_id]],
                ["term" => ["sub_cat_arr" => $sub_category_id]],
            ],

        ];

        $size = $this->ProductsModel->where('category_id', $category_id)->count();

        $search_param['body']['size'] = $size;
        $response = $elasticsearch->search($search_param);

        foreach ($response['hits']['hits'] as $result_key => $result_value) {
            if ($result_value['_index'] == $elastic_index) {
                $this->decactivate_product($result_value['_id'], $result_value['_source']['previous_status']);
            }
        }
    }

    public function activate_sub_category_product($category_id = false, $sub_category_id = false)
    {
        $category_id = isset($category_id) ? intval($category_id) : 0;
        $sub_category_id = isset($sub_category_id) ? intval($sub_category_id) : 0;
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $search_param['body']['query']['bool']['must'] = [
            [
                ['term' => ["category_id" => $category_id]],
                ["term" => ["sub_cat_arr" => $sub_category_id]],
            ],

        ];

        $size = $this->ProductsModel->where('category_id', $category_id)->count();

        $search_param['body']['size'] = $size;
        $response = $elasticsearch->search($search_param);

        foreach ($response['hits']['hits'] as $result_key => $result_value) {
            if ($result_value['_index'] == $elastic_index) {
                $this->activate_product($result_value['_id'], $result_value['_source']['previous_status']);
            }
        }
    }

    public function update_complete_status($product_id = false, $complete_status)
    {
        $complete_status = isset($complete_status) ? intval($complete_status) : 0;
        $product_id = isset($product_id) ? intval($product_id) : 0;
        //dd($complete_status,$product_id);
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");
        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $params = [
            'index' => $elastic_index,
            'id' => $product_id,
            'body' => [
                'doc' => [
                    'product_complete_status' => $complete_status,
                    'is_vendor_active' => 1,
                ],
            ],
        ];
        // Update doc at /my_index/_doc/my_id
        $response = $elasticsearch->update($params);
    }

    public function change_brand_name($products_arr = false, $brand_name)
    {
        ini_set('max_execution_time', 0);
        $brand_name = isset($brand_name) ? $brand_name : 0;

        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");
        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        foreach ($products_arr as $prod_key => $prod_value) {
            $product_id = $prod_value['id'];

            $params = [
                'index' => $elastic_index,
                'id' => $product_id,
                'body' => [
                    'doc' => [
                        'brand_name' => $brand_name,
                    ],
                ],
            ];
            // Update doc at /my_index/_doc/my_id
            $response = $elasticsearch->update($params);
        }
    }

    public function update_lead_time($products_arr = false, $lead_time, $first_order_minimum)
    {
        ini_set('max_execution_time', 0);
        $brand_name = isset($brand_name) ? $brand_name : 0;

        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");
        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        if (isset($lead_time)) {
            $lead_time_min = explode("-", $lead_time)[0];

            $lead_time_max = explode("-", $lead_time)[1];

        }

        if (isset($lead_time_min) && isset($lead_time_max)) {
            $lead_time_min = intval($lead_time_min);
            $lead_time_max = intval($lead_time_max);
        }

        foreach ($products_arr as $prod_key => $prod_value) {

            $product_id = $prod_value['id'];

            $params = [
                'index' => $elastic_index,
                'id' => $product_id,
                'body' => [
                    'doc' => [
                        'lead_time_min' => $lead_time_min,
                        'lead_time_max' => $lead_time_max,
                        'vendor_minimum' => isset($first_order_minimum) ? intval($first_order_minimum) : 0,
                    ],
                ],
            ];
            // Update doc at /my_index/_doc/my_id
            $response = $elasticsearch->update($params);

        }
    }

    public function get_pagination_data($arr_data = [], $count = 1, $per_page = 0, $append_data = [])
    {
        //dd($append_data);
        /* Pagination to an Array() */
        $paginator = new LengthAwarePaginator($arr_data, $count, $per_page, Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));
        $paginator->appends($append_data);
        /* Appends all input parameter to Links */

        return $paginator;
    }

    public function get_search_vendor()
    {
        $vendors_details_arr = [];
        /*get all vendors*/
        /*  $obj_vendors_details = $this->MakerModel->with(['shop_store_images'=>function($query){
        $query->select('id','maker_id','store_profile_image');
        },'user_details'=>function($query1){

        $query1->where('status',1);
        $query1->whereNull('deleted_at');
        $query1->where('id','!=',1);

        }])

        ->where('company_name','!=',"");

        if($letter!= false)
        {
        $obj_vendors_details = $obj_vendors_details->where('company_name','LIKE', $searching_word.'%');
        }

        $obj_vendors_details = $obj_vendors_details->paginate(8);*/

        /*row query for get all vendors*/

        $role_slug = 'maker';

        $user_table = $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix() . $user_table;

        $role_table = $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix() . $role_table;

        $role_user_table = $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix() . $role_user_table;

        $shop_table = $this->ShopImagesModel->getTable();
        $prefix_shop_table = DB::getTablePrefix() . $shop_table;

        $maker_table = $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix() . $maker_table;

        $shop_setting_table = $this->ShopSettings->getTable();
        $prefix_shop_setting_table = DB::getTablePrefix() . $shop_table;

        $obj_vendors_details = DB::table($maker_table)
            ->select(
                DB::raw(
                    $prefix_user_table . ".id as id,"
                    . $prefix_user_table . ".email as email, "
                    . $prefix_user_table . ".status, "
                    . $prefix_user_table . ".contact_no as contact_no, "
                    . $shop_table . ".store_profile_image, "
                    . $maker_table . ".brand_name, "
                    . $maker_table . ".company_name, "
                    . $prefix_user_table . ".first_name, "
                    . "CONCAT(" . $prefix_user_table . ".first_name,' ',"
                    . $prefix_user_table . ".last_name) as user_name"
                )
            )
            ->leftJoin($user_table, $prefix_user_table . '.id', '=', $maker_table . '.user_id')
            ->leftJoin($shop_table, $prefix_shop_table . '.maker_id', '=', $maker_table . '.user_id')
            ->where($user_table . '.status', 1)
            ->where($user_table . '.is_approved', 1)
            ->whereNull($user_table . '.deleted_at')
            ->where($user_table . '.id', '!=', 1)
            ->where($maker_table . '.company_name', '!=', "")->get();

        if (isset($obj_vendors_details)) {
            $arr_vendor_agination = clone $obj_vendors_details;
            $vendors_details_arr = $obj_vendors_details->toArray();
        }

        return $vendors_details_arr;
    }

    /*prodcut active and block*/

    public function activate_product_status($product_id = false, $previous_status = false)
    {
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");

        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $params = [
            'index' => $elastic_index,
            'id' => $product_id,
            'body' => [
                'doc' => [
                    'product_status' => 1,
                    //'previous_status' => $previous_status
                ],
            ],
        ];
        // Update doc at /my_index/_doc/my_id
        $response = $elasticsearch->update($params);
    }

    public function decactivate_product_status($product_id = false, $previous_status = false)
    {
        $elastic_index = env("ELASTIC_INDEX_NAME");
        $elastic_host = env("ELASTIC_HOST");
        $elasticsearch = ClientBuilder::create()
            ->setHosts([$elastic_host])
            ->build();

        $params = [
            'index' => $elastic_index,
            'id' => $product_id,
            'body' => [
                'doc' => [
                    'product_status' => 0,
                    //'previous_status' => $previous_status
                ],
            ],
        ];
        // Update doc at /my_index/_doc/my_id
        $response = $elasticsearch->update($params);
    }

     /************************Notification Event START**************************/

    public function save_notification($ARR_DATA = [])
    {  
        if(isset($ARR_DATA) && count($ARR_DATA)>0)
        {
            $ARR_EVENT_DATA                 = [];
            $ARR_EVENT_DATA['from_user_id'] = $ARR_DATA['from_user_id'];
            $ARR_EVENT_DATA['to_user_id']   = $ARR_DATA['to_user_id'];
            $ARR_EVENT_DATA['description']  = $ARR_DATA['description'];
            $ARR_EVENT_DATA['title']        = $ARR_DATA['title'];
            $ARR_EVENT_DATA['type']         = $ARR_DATA['type'];


            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }

    /************************Notification Event END  **************************/

}
