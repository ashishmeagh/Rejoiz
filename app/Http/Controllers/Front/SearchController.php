<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\SearchService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use App\Models\UserModel;
use App\Models\RepresentativeMakersModel;

use App\Models\FavoriteModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel; 
use App\Models\ShopImagesModel;
use App\Models\ShopSettings;
use App\Models\ProductsModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\CategoryModel;

use Session;
use Paginate;
use Sentinel;
use DB;


use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class SearchController extends Controller
{

    public function __construct(SearchService $SearchService,
                                ElasticSearchService $ElasticSearchService,
                                UserModel     $UserModel,
                                RepresentativeMakersModel $RepresentativeMakersModel,
                                FavoriteModel $FavoriteModel,
                                MakerModel $MakerModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                ShopImagesModel $ShopImagesModel,
                                ShopSettings $ShopSettings,
                                ProductsModel $ProductsModel,
                                ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                CategoryModel $CategoryModel,
                                HelperService $HelperService

                                )
    {        
        $this->SearchService              = $SearchService;
        $this->ElasticSearchService       = $ElasticSearchService;   
        $this->UserModel                  = $UserModel;
        $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->RepresentativeMakersModel  = $RepresentativeMakersModel; 
        $this->FavoriteModel              = $FavoriteModel;
        $this->MakerModel                 = $MakerModel;
        $this->RoleModel                  = $RoleModel;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->ShopImagesModel            = $ShopImagesModel;
        $this->ProductsModel              = $ProductsModel;
        $this->ShopSettings               = $ShopSettings;
        $this->CategoryModel              = $CategoryModel;
        $this->HelperService              = $HelperService;

      

        $this->module_title               = "Products";
        $this->module_view_folder         = 'front';  
        $this->arr_view_data              = [];
    }

    public function reindex_products(Request $request)
    {
        $this->ElasticSearchService->reindex_products($request);

    }

    public function test_reindex_products()
    {
        $this->ElasticSearchService->test_reindex_products();
    }


    public function test_curl_call()
    {
        $this->ElasticSearchService->test_curl_call();
    }


    public function search(Request $request)
    { 
       if(Session::has('category')){Session::forget('category');}
            
        $request_all = $request->all();


        $category = isset($request_all['category'])?$request_all['category']:"";


        $active_cat_id   = array_column(get_category(),'id');
        $category_id_arr = [];


    
        //$request_category_id_arr = isset($request_all['category_id_arr'])?$request_all['category_id_arr']:'';


        /*if(isset($request_category_id_arr))
        {
            $user = Sentinel::check();
            $user_id = 0;
            if($user)
            {
                $user_id = $user->id;
            }
            $result = $this->UserModel->where('id',$user_id)->update(['is_login'=>1]);

        }
        
        $category_id_arr = isset($request_category_id_arr)?json_decode($request_category_id_arr):'';*/

       
/*        if($category=="new_arrivals")
        {   
            $arr_product = [];
            $category = [];
            $arr_category = [];
            $arr_product = $this->ProductsModel
                            //->groupBy('category_id')
                            ->orderBy('created_at','DESC')
                            //->with(['categoryDetails'])
                            ->get(['category_id'])
                            ->toArray();
            if(isset($arr_product) && sizeof($arr_product)>0)
            {

                foreach($arr_product as $key=> $product)
                {
                    if(!in_array($product['category_id'], $category))
                    {
                        $category[$key] = $product['category_id'];
                    }
                }

                if(isset($category) && sizeof($category)>0)
                {
                    $obj_arr = false;
                    foreach($category as $key=>$cat)
                    {
                        //$arr_category[$key]  = $this->CategoryModel->where('id',$cat)->first()->toArray();
                        $obj_arr = $this->CategoryModel->where('id',$cat)->where('is_active','1')->first();
                        if($obj_arr)
                        {
                            $arr_category[$key] = $obj_arr->toArray();
                        }
                    }
                } 
        }
            $this->arr_view_data['arr_category']   = $arr_category;
            $this->arr_view_data['page_title']     = 'New arrivals'; 
            return view('front.search.new_arrivals_list',$this->arr_view_data);    
        }
        elseif($category=="shop_now")*/



        $country_id = isset($request_all['country_id'])?$request_all['country_id']:false;    
   
        $country_id = isset($country_id)?base64_decode($country_id):false;

        $brands_arr = $this->HelperService->get_all_brands($country_id);

        if(count($brands_arr)==0)
        {
             $brands_arr = [];
        }

        $this->arr_view_data['search_value'] = $request->all();
        $this->arr_view_data['brands_arr']   = $brands_arr;

        if($category=="shop_now")            
        {   
            $arr_category = [];
            $arr_category = CategoryModel::with(['subcategory_details'=>function ($query){
              $query->where('is_active',1);
            }])->where('is_active',1)->get()->sortBy(function($CategoryModel){return $CategoryModel->category_name;})->toArray();

            

            if(isset($arr_category) && count($arr_category)>0){
              foreach ($arr_category as $k => $category) {
                if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0){
                  $temp_arr = [];
                   foreach ($category['subcategory_details'] as $key => $sub_category) {
                      if(isset($sub_category['subcategory_slug']) && strtolower($sub_category['subcategory_slug']) == 'general'){
                        $temp_arr = $sub_category;
                        unset($arr_category[$k]['subcategory_details'][$key]);
                      }
                    } 

                    if(count($temp_arr) > 0)
                    {
                      array_push($arr_category[$k]['subcategory_details'],$temp_arr);  
                    }
                    
                }
              }
            }
           
            $this->arr_view_data['arr_category']   = $arr_category;
            $this->arr_view_data['page_title']     = 'Shop Now'; 
            return view('front.search.new_arrivals_list',$this->arr_view_data);    
        }
       /* elseif(isset($category_id_arr) && count($category_id_arr) > 0)
        { 

           $view_data   = $this->ElasticSearchService->search($request);
    
           return view('front.search.product_list',$view_data);  
        }*/
        else
        { 
            $view_data   = $this->ElasticSearchService->search($request);
          
            $view_data['search_value'] = $this->arr_view_data['search_value'];
            $view_data['brands_arr']   = $brands_arr; 
     

        if(isset($view_data['response']) && sizeof($view_data['response'])!=0)
            {
               return $view_data['response'];
            }

           // dd($view_data);
            return view('front.search.product_list',$view_data);  

        }                
            

    }

    public function flush_index_products(Request $request)
    {
        $view_data = $this->ElasticSearchService->flush_index_products($request);
       
        
        dd($view_data);
    }

    public function search_db(Request $request)
    {    

        $form_data = [];
        $user_id = 0;
        $form_data = $request->all();

        /*get current user*/

        $user = Sentinel::check();

        if(isset($user))
        {
           $user_id = $user->id;
        }
            
        $search_type = isset($form_data['search_type'])?$form_data['search_type']:"";
        
        $search = $this->SearchService->search($form_data);
        
        if($request->ajax())
        {
            $response['status'] = "SUCCESS";
            return response()->json($response);
        }
        else
        {
            $arr_data = []; 
            $obj_paginate = false;

            
            if($request->has('page'))
            {
                $pageStart = $request->input('page'); /* pageStart : Indicates from which page to start.*/
            }
            else
            {
                $pageStart = 1; /* pageStart : Indicates from which page to start.*/
            } 

            $paginator = $this->get_pagination_data($search, $pageStart,12, $request->all());
        
            if($paginator)
            {
                $arr_user_pagination   =  $paginator;  
                $arr_data  =  $paginator->items(); /* To Get Pagination Record */
              
               
            }




            /*get favorite product data*/
            $fav_product_arr = $this->FavoriteModel->where('retailer_id',$user_id)->where('type','product')->get()->toArray();

            // dd("ok");

            $fav_product_id_arr = array_column($fav_product_arr,'product_id');


            /*get favorite maker data*/
            $fav_maker_arr = $this->FavoriteModel->where('retailer_id',$user_id)->where('type','maker')->get()->toArray();

            $fav_maker_id_arr = array_column($fav_maker_arr,'maker_id');


            $this->arr_view_data['total_results']             =  isset($search)?count($search):0;
            $this->arr_view_data['is_search_box_visible']     =  'yes';
          
            if($search_type=='maker')
            {
                $this->arr_view_data['arr_data']         = json_decode(json_encode((array)$arr_data), true);
                $this->arr_view_data['arr_pagination']   = $arr_user_pagination;
                $this->arr_view_data['search_value']     = $request->all();
               
                $this->arr_view_data['page_title']       = 'Vendors';

                $this->arr_view_data['obj_paginate']     = $obj_paginate;
                $this->arr_view_data['arr_data']         = $arr_data;

             
                return view('front.search.maker_list',$this->arr_view_data);
            }  
            elseif($search_type=="representative")
            {
                $this->arr_view_data['arr_data']         = json_decode(json_encode((array)$arr_data), true);
                $this->arr_view_data['arr_pagination']   = $arr_user_pagination;
                $this->arr_view_data['search_value']     = $request->all();
                $this->arr_view_data['page_title']       = isset($form_data['search_term'])?$form_data['search_term']:'Representative(s)';
                $this->arr_view_data['obj_paginate']     = $obj_paginate;
                $this->arr_view_data['arr_data']         = $arr_data;

                return view('front.search.representative_list',$this->arr_view_data);
            }    
            else
            {
                
               
                $this->arr_view_data['arr_pagination']     = $arr_user_pagination;
                $this->arr_view_data['search_value']       = $request->all();
                $this->arr_view_data['page_title']         = isset($form_data['search_term'])?$form_data['search_term']:'Product(s)';
                $this->arr_view_data['obj_paginate']       = $obj_paginate;
                $this->arr_view_data['arr_data']           = $arr_data;
                $this->arr_view_data['fav_product_arr']    = $fav_product_id_arr;
                $this->arr_view_data['fav_maker_id_arr']   = $fav_maker_id_arr;
               
                /*dd($arr_data);*/
                return view('front.search.product_list',$this->arr_view_data);
            }    
        }
            
    }

    //array sort by date call back function
    public function date_compare($a, $b)
    {
        $t1 = strtotime($a['created_at']);
        $t2 = strtotime($b['created_at']);
            return $t1 - $t2;
    }   


    public function search_DEPRECATED(Request $request,$search_term = false,$page=false)
    {
        
        //dd($request->all());
        if(isset($search_term))
        {
         $search_term = strtolower($search_term);
        }
        $active_cat_id = array_column(get_category(),'id');
        $category_id_arr = [];
        $search_request  = $request->all();

        $request_category_id_arr = isset($search_request['category_id_arr'])?$search_request['category_id_arr']:'';


        if(isset($request_category_id_arr))
        {
            $user = Sentinel::check();
            $user_id = 0;
            if($user)
            {
                $user_id = $user->id;
            }
            $result = $this->UserModel->where('id',$user_id)->update(['is_login'=>1]);

        }
        
        $category_id_arr = isset($request_category_id_arr)?json_decode($request_category_id_arr):'';

        session()->push('products.name', $search_term );
        $recently_searched = $request->session()->get('products.name');
        $recently_searched = array_unique($recently_searched);
        $recently_searched = array_reverse($recently_searched);

        if (($key = array_search(false,$recently_searched)) == false)
                {
                    unset($recently_searched[$key]);
                }

        if(isset($search_request['category_id']))
        {  
            $category_id = $search_request['category_id'];
            $cat_id = base64_decode($category_id);
            $result = ProductsModel::search()
                        ->size(500)
                        ->must()
                        ->term('is_active','1')
                        ->match('category_id',$cat_id);
            $append_data['category_id'] = $category_id;

            
            if(isset($search_request['subcateFgory']))
            {  
                 $sub_category_id = $search_request['subcategory'];
            $sub_cat_id = base64_decode($sub_category_id);
            $result = ProductsModel::search()
                        ->size(500)
                        ->must()
                        ->term('is_active','1')
                        ->match('subcategory_id',$sub_cat_id);
            $append_data['subcategory'] = $sub_category_id;

           
            }

            $result =  $result->get();

        $products = $result->hits();

        }


        elseif(isset($category_id_arr) && count($category_id_arr) > 0)
        { 

            $arr_cat = array_diff($active_cat_id,$category_id_arr);
           
            $result = ProductsModel::search()
                        ->size(500)
                        ->term('is_active','1');
            
                        foreach($arr_cat as $key => $category_id)
                        { 
                            $result = $result->mustnot()->match('category_id',$category_id);

                        }

                        $result =  $result->get();

        $products = $result->hits();
       
        $append_data['category_id_arr'] = $request_category_id_arr;
        }
        elseif(isset($search_request['category']) && $search_request['category']=="new_arrivals")
        {
            
            $products = ProductsModel::search()
                        ->size(500)
                        ->must()
                        ->term('is_active','1')
                        ->term('product_complete_status','4')
                        ->sortBy('created_at')->get()->hits();

                       

            $append_data['new_arrivals'] = "new_arrivals";
                        
            
        }
        
        else
        {


         $result = ProductsModel::search()
        ->size(500)
        ->must()
        ->term('is_active','1')
        ->term('product_complete_status','4')
        ->get()->hits()->toArray();

            if($search_term !=false && $search_term !="initial-search")
            {    


                $result_product_name =  ProductsModel::search()->must()->term('is_active','1')
                                        ->term('product_complete_status','4')
                                        ->wildcard('product_name',$search_term.'*');

                

                $result_product_name_two =  ProductsModel::search()->must()->term('is_active','1')
                                            ->term('product_complete_status','4')
                                            ->match('product_name',$search_term.'*');

               
                $result_category_name = ProductsModel::search()->must()->term('is_active','1')
                                        ->term('product_complete_status','4')
                                        ->wildcard('category_name',$search_term.'*'); 
                        
                $result_brand_name =    ProductsModel::search()->must()
                                        ->term('product_complete_status','4')
                                        ->wildcard('brand_name',$search_term.'*');

                $result_maker_name =    ProductsModel::search()->must()->term('is_active','1')
                                        ->term('product_complete_status','4')
                                        ->wildcard('maker_name',$search_term.'*'); 
                
                

                 $search_term = preg_replace('/[^A-Za-z0-9]/', '', $search_term);

                


                $result_product_name_score = $result_product_name->get()->hits()->toArray();

                $result_product_name_score_two = $result_product_name_two->get()->hits()->toArray();
                
                $result_category_name_score = $result_category_name->get()->hits()->toArray();

                $result_brand_name_score = $result_brand_name->get()->hits()->toArray();
        
                $result_maker_name_score = $result_maker_name->get()->hits()->toArray();          
                $range_result = ProductsModel::whereBetween('unit_wholsale_price',[1,20])->get();

                $range_result_arr = $range_result->toArray();

               
            


                $merged_result_arr = array_merge($result_maker_name_score,$result_category_name_score,$result_brand_name_score,$result_product_name_score ,$result_product_name_score_two); 
                
                $merged_result_arr = $input = array_map("unserialize", array_unique(array_map("serialize", $merged_result_arr)));
                
                 
            }

            elseif($search_term =="initial-search")
            {
                 $merged_result_arr = $result;
            }
            else
            {   
                
                $merged_result_arr = $result;
            }

                $append_data = url()->current();
                //dd($append_data);
       
        }
        
        $recently_searched = array_unique($recently_searched);
            
         if(isset($merged_result_arr))
         {
            $products_arr = $merged_result_arr;
            //$products->toArray();

         }  
         else
         {  
            $products_arr = $products->toArray();
            if($search_request['category'])
            {   
                usort($products_arr, array($this, "date_compare")); 
                $products_arr = array_reverse($products_arr);
            }

         } 
       
        //$products->toArray();
        $total_results = count($products_arr);

        $products = $products_arr;

        $response['status'] = "success";
        $response['data'] = $products_arr;

        $response['recently_searched'] = $recently_searched;

        if($request->has('page'))
        {   
            $pageStart = $request->input('page'); 
        }
        else
        {
            $pageStart = 1; 
        } 
        
        //$append_data['category_id']= "ok";
        $paginator = $this->get_pagination_data($products_arr, $pageStart, 12 , $append_data);
       
        //dd($paginator);
        if($paginator)
        {
            $pagination_links    =  $paginator; 
             //dd($pagination_links); 
            $arr_data            =  $paginator->items(); /* To Get Pagination Record */ 
        }   

    
        if($paginator)
        { 
            $arr_user_pagination   =  $paginator;  
            $arr_product           =  $paginator->items();
            $arr_data           = $arr_product;
            $arr_view_data['arr_data']=  $arr_data;
            $arr_view_data['total_results'] = $total_results;
            $arr_pagination = $paginator;
            $fav_product_arr = $this->FavoriteModel->where('retailer_id',$user_id)->where('type','product')->get()->toArray();
            
            $fav_product_id_arr = array_column($fav_product_arr,'product_id');

            
            $html = view('front.search.product_list')->with(compact('arr_data','arr_pagination','total_results','fav_product_id_arr'))->render();
            //view for pagination links
            $html_view = view('front.search.product_list')->with(compact('arr_data','arr_pagination','total_results','recently_searched','fav_product_id_arr'));
         
            $suggested_products = [];
           
            foreach ($products as $suggetion_key => $suggetion_value) 
            {
             $suggetion_value['product_name'] =  isset($suggetion_value['product_name'])?ucfirst($suggetion_value['product_name']):'';

             $suggetion_value['category_name'] = isset($suggetion_value['category_name'])?ucfirst($suggetion_value['category_name']):'';

             $suggetion_value['company_name'] = isset($suggetion_value['company_name'])?ucfirst($suggetion_value['company_name']):'';

             array_push($suggested_products,$suggetion_value);



            }

           //dd($suggested_products);
            $response['status'] = "success";
            $response['data'] = $suggested_products;
            $response['html'] =  $html;
            $response['search_term'] =  $search_term;

          
            if($request->input('by_ajax')==1)
            {//dd($html);
              

              return $response;
            

            }
            else
            {   
                
                $fav_product_arr = $this->FavoriteModel->where('retailer_id',$user_id)->where('type','product')->get()->toArray();
                $fav_product_id_arr = array_column($fav_product_arr,'product_id');
              
                $this->arr_view_data['arr_data'] =  $arr_data ;
                $this->arr_view_data['arr_pagination'] = $arr_pagination;
                $this->arr_view_data['total_results'] = $total_results;
                $this->arr_view_data['page_title'] = $this->module_title;
               // $this->arr_view_data['fav_product_arr'] = $fav_product_arr;
                
                $this->arr_view_data['fav_product_id_arr'] = $fav_product_id_arr;
                $this->arr_view_data['search_term'] = $search_term;
                
               //dd($this->arr_view_data);
                return view('front.search.product_list',$this->arr_view_data);
            }
        } 
    }


    public function representative_details(Request $request)
    {
        $enc_id = $request->input('representative_id',null);
        $representative_id = base64_decode($enc_id);
        
        $arr_data = [];    
        $obj_data = $this->UserModel->where('id',$representative_id)->with(['representative_details'])->first();
        if($obj_data)
        {
            $arr_data = $obj_data->toArray();
        }            
      
        $arr_maker = [];
        $arr_maker = $this->RepresentativeMakersModel->where('representative_id',$representative_id)
                          ->with('maker_details','store_details')->get()->toArray();

        if($request->has('page'))
        {
            $pageStart = $request->input('page'); /* pageStart : Indicates from which page to start.*/
        }
        else
        {
            $pageStart = 1; /* pageStart : Indicates from which page to start.*/
        } 

        $paginator = $this->get_pagination_data($arr_maker, $pageStart, 12 , $request->all());
       
        if($paginator)
        {
            $arr_user_pagination   =  $paginator;  
            $arr_maker             =  $paginator->items(); /* To Get Pagination Record */ 
           
        } 
        
        $this->arr_view_data['is_search_box_visible']     =  'yes';
        $this->arr_view_data['arr_maker']        = json_decode(json_encode((array)$arr_maker), true);
        $this->arr_view_data['arr_pagination']   = $arr_user_pagination;
        $this->arr_view_data['search_value']     = $request->all();
        $this->arr_view_data['arr_data']         = $arr_data;
        $this->arr_view_data['page_title']       = 'Representative Details';

        return view('front.search.representative_details',$this->arr_view_data);
        
    }

    public function get_pagination_data($arr_data = [], $count = 1, $per_page = 0, $append_data = [])
    {
        /* Pagination to an Array() */

         $paginator =  new LengthAwarePaginator($arr_data, $count, $per_page,Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));      
    
        $paginator->appends($append_data); /* Appends all input parameter to Links */

        return $paginator;
    }

    public function set_guest_url(Request $request)
    {
        $url = $request->get('guest_link');


        if(isset($url))
        {
            $response['status'] = 'success';
            $response['redirect_link'] = $url;
            
            Session::put('guest_back_url',$url);

            return $response; 
        }
        else{
            $response['status'] ='failiure';
            return $response;
        }

    }



    public function search_vendor($letter = false)
    {   
        $searching_word = $letter;
       
        $vendors_details_arr = [];
        /*get all vendors*/
    
        /*row query for get all vendors*/

        $role_slug = 'maker';
            
        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $shop_table        =  $this->ShopImagesModel->getTable();
        $prefix_shop_table = DB::getTablePrefix().$shop_table;

        $maker_table        = $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;

        $shop_setting_table        = $this->ShopSettings->getTable();
        $prefix_shop_setting_table = DB::getTablePrefix().$shop_table;
        
        $obj_vendors_details = DB::table($maker_table)
                                ->select(DB::raw($prefix_user_table.".id as id,".
                                             $prefix_user_table.".email as email, ".
                                             $prefix_user_table.".status, ".
                                             $prefix_user_table.".contact_no as contact_no, ".
                                             $shop_table.".store_profile_image, ".
                                             $maker_table.".brand_name, ".
                                             $maker_table.".company_name, ".
                                             $prefix_user_table.".first_name, ".
                                            
                                             "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                      .$prefix_user_table.".last_name) as user_name"
                                             ))
                                ->leftJoin($user_table,$prefix_user_table.'.id','=',$maker_table.'.user_id')
                                ->leftJoin($shop_table,$prefix_shop_table.'.maker_id','=',$maker_table.'.user_id')
                                ->where($user_table.'.status',1)
                                ->where($user_table.'.is_approved',1)
                                ->whereNull($user_table.'.deleted_at')
                                ->where($user_table.'.country_id','=',2)
                                ->where($maker_table.'.company_name','!=',"")
                                ->orderBy($maker_table.'.listing_sequence_no','ASC');

                                if($letter!= false)
                                {  
                                    if($letter == "&")
                                    { 
                                        $obj_vendors_details = $obj_vendors_details->whereNotIn(DB::raw('substr(company_name, 1, 1)'),['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z']);

                                    }
                                    else
                                    {
                                      
                                       $obj_vendors_details = $obj_vendors_details->where('company_name','LIKE', $searching_word.'%');
                                    }
                                }
                                // dd($obj_vendors_details->toSql(),$obj_vendors_details->getBindings());
                            $obj_vendors_details = $obj_vendors_details->paginate(8);


       
       
        if(isset($obj_vendors_details))
        {
            $arr_vendor_agination = clone $obj_vendors_details;
            $vendors_details_arr  =  $obj_vendors_details->toArray();
        }        
          
       
        $this->arr_view_data['vendor_pagination'] = $arr_vendor_agination; 
        $this->arr_view_data['vendors_arr']       = $vendors_details_arr; 
        $this->arr_view_data['page_title']        = 'American Best Sellers';
        // dd($this->arr_view_data);
        return view('front.search_vendors',$this->arr_view_data);
    }

    public function filter(Request $request,$price =false,$minimum=false,$specials=false)
    {
        //dd($request->all());
        $view_data = $this->ElasticSearchService->filter($request);
             //dd($view_data);
             //dd("ok1");
           /* if(isset($view_data['response']) && sizeof($view_data['response'])!=0)
            {
               dd("ok");
               return $view_data['response'];
            }*/
            return view('front.search.product_list',$view_data);  

    }

  
   
}
