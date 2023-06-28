<?php

namespace App\Common\Services;

// use App\Common\Services\GeneralService;
use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\RetailerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\MakerModel;
use App\Models\BrandsModel;
use App\Models\PromoCodeModel;
use App\Models\ProductInventoryModel;
use App\Models\CustomerQuotesModel;
use DB;
use DateTime;

// use Illuminate\Database\Eloquent\Builder;
// use Carbon\Carbon;

class ProductService 
{

	public function __construct()
	{	
		$this->BaseModel                  = New ProductsModel;
		$this->module_title               = "Products";
		$this->SubCategoryModel           = New SubCategoryModel;
    $this->CategoryModel              = New CategoryModel;
    $this->UserModel                  = New UserModel;
    $this->RoleModel                  = New RoleModel;
    $this->RoleUsersModel             = New RoleUsersModel;
    $this->MakerModel                 = New MakerModel;
    $this->BrandsModel                = New BrandsModel;
    $this->ProductDetailsModel        = New ProductDetailsModel;
    $this->ProductImagesModel         = New ProductImagesModel;
    $this->CategoryTranslationModel   = New CategoryTranslationModel;
    $this->ProductsSubCategoriesModel = New ProductsSubCategoriesModel;
    $this->RetailerQuotesModel        = New RetailerQuotesModel;
    $this->RepresentativeLeadsModel   = New RepresentativeLeadsModel;
    $this->PromoCodeModel             = New PromoCodeModel;
    $this->ProductInventoryModel      = New ProductInventoryModel;
    $this->CustomerQuotesModel        = New CustomerQuotesModel;
    // $this->GeneralService = New GeneralService;
	}
  
  

  
  //---------------Done(Test with admin and maker panel)-------------------
  public function get_productDetails_from_productId($product_id,$mp_arr)
  {
    $product_obj = $this->BaseModel->with($mp_arr)
                                   ->where('id',$product_id)->first();

    if($product_obj)
    {
      $product_arr = $product_obj->toArray();
      return $product_arr;
    }

  }
  
	public function get_product_list($loggedInUserId = null, $arr_search_column)
	{		
    //dd("12");
   
		$product_tbl_name     = $this->BaseModel->getTable();        
    $prefixed_product_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

    $product_details_tbl  = $this->ProductDetailsModel->getTable();
    $prefixed_product_details_tbl  = DB::getTablePrefix().$this->ProductDetailsModel->getTable();

    $category__tbl        = $this->CategoryModel->getTable();
    $prefix_category_tbl  = DB::getTablePrefix().$this->CategoryModel->getTable();

    $maker_tbl            = $this->MakerModel->getTable();
    $prefixed_maker_tbl   = DB::getTablePrefix().$this->MakerModel->getTable(); 

    $brand_tbl            = $this->BrandsModel->getTable();
    $prefixed_brand_tbl   = DB::getTablePrefix().$this->BrandsModel->getTable();

    $category_trans_tbl_name      = $this->CategoryTranslationModel->getTable();        
    $prefixed_category_trans_tbl  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

    $user_table        =  $this->UserModel->getTable();
    $prefix_user_table = DB::getTablePrefix().$user_table;

    $product_inventory_table = $this->ProductInventoryModel->getTable();
    $prefixed_inventory_table  = DB::getTablePrefix().$product_inventory_table;
    

    $obj_products = DB::table($product_tbl_name)
                      ->select(DB::raw($prefixed_product_tbl.".id,".  
                         $prefixed_product_tbl.'.is_active,'.
                         $prefixed_product_tbl.'.is_archive,'.
                         $prefixed_product_tbl.'.product_name,'.
                         $prefixed_product_tbl.".product_complete_status,".
                         $prefix_user_table.'.first_name,'.
                         $prefixed_product_tbl.".brand,".
                         $prefixed_product_tbl.".product_status,".
                         $prefixed_product_tbl.".product_image,".

                         $prefixed_brand_tbl.".id as bid,".
                         $prefixed_brand_tbl.".brand_name,".


                         $prefixed_product_details_tbl.".sku,".
    
                         $prefixed_maker_tbl.".id as mid,".
                         $prefixed_maker_tbl.".user_id,".
                         $prefixed_maker_tbl.".company_name,".

                         $prefixed_product_tbl.".shipping_type,".
                         $prefixed_product_tbl.".shipping_charges,".

                         $prefixed_product_tbl.".created_at,".  
                         "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                          ))
                               
                      ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_product_tbl.'.user_id')

                      ->leftjoin($brand_tbl,$prefixed_brand_tbl.'.id','=',$prefixed_product_tbl.'.brand')

                      ->leftjoin($maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_product_tbl.'.user_id')

                      ->leftjoin($prefixed_product_details_tbl,$prefixed_product_details_tbl.'.product_id','=',$prefixed_product_tbl.'.id')

                      ->where($prefixed_product_tbl.'.is_archive',0)
                      ->where($prefixed_product_tbl.'.is_deleted',0)

                      // ->where($prefixed_product_details_tbl.'.is_deleted',0);
                      // ->where($prefixed_product_tbl.'.product_complete_status',4)
                      //->groupBy($prefixed_product_tbl.'.product_name')

                      ->orderBy($product_tbl_name.'.updated_at','DESC');

      if($loggedInUserId == null)
      {
          $obj_products =$obj_products->where($prefixed_product_tbl.'.product_complete_status',4)
                                      ->groupBy($prefixed_product_details_tbl.'.product_id');
      }

      if (isset($loggedInUserId) && $loggedInUserId!=null) 
      {
    		$obj_products = DB::table($product_tbl_name)
                      ->select(DB::raw($prefixed_product_tbl.".id,".  
                        $prefixed_product_tbl.'.is_active,'.
                        $prefixed_product_tbl.'.is_archive,'.
                        $prefixed_product_tbl.'.product_name,'.
                        $prefix_user_table.'.first_name,'.
                        $prefixed_product_tbl.".created_at,".
                        $prefixed_product_tbl.".product_image,".
                        $prefixed_product_tbl.".product_image_thumb,".
                        $prefixed_product_tbl.".product_complete_status,".
                        $prefixed_category_trans_tbl.".category_name,".
                        $prefixed_product_tbl.".shipping_type,".
                        $prefixed_product_tbl.".shipping_charges,".
                        $prefixed_product_tbl.".unit_wholsale_price,".
                        $prefixed_product_tbl.".retail_price,".
                        $prefixed_product_tbl.".brand,". 
                        $prefixed_product_tbl.".product_status,". 

                        $prefixed_product_details_tbl.".sku,".

                        $prefixed_inventory_table.".quantity,".

                        $prefixed_brand_tbl.".id as bid,".
                        $prefixed_brand_tbl.".brand_name"

                      ))                                
                    ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_product_tbl.'.user_id')

                    ->leftjoin($brand_tbl,$prefixed_brand_tbl.'.id','=',$prefixed_product_tbl.'.brand')

                    ->leftjoin($category_trans_tbl_name,$category_trans_tbl_name.'.category_id','=',$prefixed_product_tbl.'.category_id')

                    ->leftjoin($prefixed_product_details_tbl,$prefixed_product_details_tbl.'.product_id','=',$prefixed_product_tbl.'.id')

                    ->leftjoin($prefixed_inventory_table,$prefixed_inventory_table.'.sku_no','=',$prefixed_product_details_tbl.'.sku')

                    /*->leftJoin($prefixed_inventory_table, function($join) use ($prefixed_product_details_tbl,$prefixed_product_tbl,$prefixed_inventory_table) {
                          $join->on($prefixed_inventory_table.'.sku_no', '=', $prefixed_product_details_tbl.'.sku');
                          $join->on($prefixed_inventory_table.'.product_id', '=', $prefixed_product_tbl.'.id');
                          $join->on($prefixed_inventory_table.'.is_deleted','=',DB::raw('0'));
                         
                    })*/


                    ->where($prefixed_product_tbl.'.user_id',$loggedInUserId)
                    ->where($prefixed_product_tbl.'.is_archive',0)
                    ->where($prefixed_product_tbl.'.is_deleted',0)
                    

                    // ->where($prefixed_product_details_tbl.'.is_deleted',0)
                    // ->where($prefixed_inventory_table.'.is_deleted',0)

                    //->groupBy($prefixed_product_tbl.'.product_name')
                    ->groupBy($prefixed_product_details_tbl.'.product_id')
                    ->orderBy($prefixed_product_tbl.".id",'DESC');

                   
    	}
      // else
      // {
      //   $obj_products = $obj_products->where($prefixed_product_tbl.'.product_complete_status','4');
      // }        
    /* ---------------- Filtering Logic ----------------------------------*/   
    if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
    {
      $search_term  = $arr_search_column['q_user_name'];
      $obj_user     = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
    }   
    
    if(isset($arr_search_column['q_product_name']) && $arr_search_column['q_product_name']!="")
    {
      $search_term  = $arr_search_column['q_product_name'];
      $obj_products = $obj_products->where($prefixed_product_tbl.'.product_name','LIKE', '%'.$search_term.'%');
    }

    if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
    {
      $search_term  = $arr_search_column['q_company_name'];
      $obj_products = $obj_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
    }  

    if(isset($arr_search_column['q_brand_name']) && $arr_search_column['q_brand_name']!="")
    {
      $search_term  = $arr_search_column['q_brand_name'];
      $obj_products = $obj_products->where($prefixed_brand_tbl.'.brand_name','LIKE', '%'.$search_term.'%');
    }         

    if(isset($arr_search_column['q_product_status']) && $arr_search_column['q_product_status']!="")
    {
        $search_term  = $arr_search_column['q_product_status'];
        $obj_products = $obj_products->where($prefixed_product_tbl.'.product_complete_status',$search_term);
    }



    if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
    {
      $search_term      = $arr_search_column['q_status'];
      
      $obj_products     = $obj_products->where($prefixed_product_tbl.'.is_active',$search_term);
    }


    if(isset($arr_search_column['q_product_sts']) && $arr_search_column['q_product_sts']!="")
    { 
      $search_term      = $arr_search_column['q_product_sts'];
      
      $obj_products     = $obj_products->where($prefixed_product_tbl.'.product_status',$search_term);
    }

    if(isset($arr_search_column['q_create_at']) && $arr_search_column['q_create_at']!="")
    {
      $search_term      = $arr_search_column['q_create_at'];
      $date             = DateTime::createFromFormat('m/d/Y',$search_term);
      $date             = $date->format('Y-m-d');
      //$search_term      = date('Y-m-d',strtotime($search_term));
     
      $obj_products     = $obj_products->where($prefixed_product_tbl.'.created_at','LIKE', '%'.$date.'%');
    }
    if(isset($arr_search_column['q_unit_wholsale_price']) && $arr_search_column['q_unit_wholsale_price']!="")
    {
      $search_term      = $arr_search_column['q_unit_wholsale_price'];
      $obj_products = $obj_products->where($prefixed_product_tbl.'.unit_wholsale_price','LIKE', '%'.$search_term.'%');
    }
    if(isset($arr_search_column['q_category_name']) && $arr_search_column['q_category_name']!="")
    {
      $search_term      = $arr_search_column['q_category_name'];
      $obj_products = $obj_products->where($prefixed_category_trans_tbl .'.category_name','LIKE', '%'.$search_term.'%');
    }

    if(isset($arr_search_column['q_sku']) && $arr_search_column['q_sku']!="")
    {
      $search_term      = $arr_search_column['q_sku'];
      $obj_products     = $obj_products->where($prefixed_product_details_tbl .'.sku','LIKE', '%'.$search_term.'%');
    }
    if(isset($arr_search_column['q_retail_price']) && $arr_search_column['q_retail_price']!="")
    {
      $search_term      = $arr_search_column['q_retail_price'];
      $obj_products = $obj_products->where($prefixed_product_tbl.'.retail_price','LIKE', '%'.$search_term.'%');
    }

   // dd($obj_products->toSql(),$obj_products->getBindings());
    return $obj_products;
	}
  
	public function changeStatus($product_status,$product_id)
  {
    if($product_status=='1')
    {
      $this->BaseModel->where('id',$product_id)->update(['is_active'=>1]);
      
      $response['status']  = 'SUCCESS';
      $response['message'] = $this->module_title.' approved successfully';

    }
    elseif($product_status=='0')
    {
      $this->BaseModel->where('id',$product_id)->update(['is_active'=>0]);

      $response['status']  = 'SUCCESS';
      $response['message'] = $this->module_title.' disapproved successfully';
    }
    else
    {
      $response['status']  = 'ERROR';
      $response['message'] = 'Oops... Something went wrong please try again later';
    }
    return $response; 
  }

  public function does_exists_edit($form_data,$param,$pro_id=false)
  {            
    $product_id = $pro_id;
       
    if($product_id)
    {
      // $sku_array = $form_data['new_sku'];
      if(isset($form_data['new_sku']))
          {
            $sku_array = $form_data['new_sku'];
          }
          else
          {
            $sku_array = $form_data['old_sku']; # code...
          }    
         
      $array_keys = array_keys($sku_array);
      $sku_no = $sku_array[$array_keys[0]];
            
      $existing_sku_count = ProductDetailsModel:: where('product_id','=',$product_id)
                                                 ->where('sku','=',$sku_no)    
                                                 ->where('is_deleted','=',0)        
                                                 ->get()->count();
        
      $sku_count = ProductDetailsModel::where('sku','=',$sku_no)->where('is_deleted','=',0)->get()->count();

      // dd($sku_count);
      if($existing_sku_count==1)
      {
          $result='success';     
      }
      else
      {
        // dd($sku_count);
        if($sku_count==0)
        {
          $result='success';  

        }
        else{
          $result='failed'; 
        }         
      }      
    }
      
    else
    {  
      // return 2;
      if (isset($form_data['new_sku']['0'])) {
        $sku_no = $form_data['new_sku']['0'];
      }
      else{
        $sku_no = $form_data['sku']['0'];
      }
      

      $sku_count = ProductDetailsModel::where('sku','=',$sku_no)->where('is_deleted','=',0)->get()->count();
      $existing_sku_count = 0;

      if($sku_count!=0)
      {
        $result='failed';        
        
      }
      else
      {
        $result='success';  

      }
      
    }
    return $result;
  }

  public function does_exists($form_data,$param,$pro_id=false)
  { 
    $product_id = $pro_id;
       
    if($product_id)
    {
      $sku_array = $form_data['old_sku'];
      $array_keys = array_keys($sku_array);
      $sku_no = $sku_array[$array_keys[0]];
         
      $existing_sku_count = ProductDetailsModel:: where('product_id','=',$product_id)
                                         ->where('sku','=',$sku_no)  
                                         ->where('is_deleted','=',0)      
                                         ->get()->count();
        
      $sku_count = ProductDetailsModel::where('sku','=',$sku_no)->where('is_deleted','=',0)->get()->count();

      if($existing_sku_count==1)
      {
        $result='success';     
      }
      else
      {
        if($sku_count!=0)
        {
          $result='failed';  

        }
        else{
          $result='success';  
        }       
      }     
    }
      
    else
    { 
      if(isset($form_data['new_sku']['0']))
      {
        $sku_no = $form_data['new_sku']['0'];
      } 
      else{
        $sku_no = $form_data['sku']['0'];
      }  
      
      $sku_count = ProductDetailsModel::where('sku','=',$sku_no)->where('is_deleted','=',0)->get()->count();
      $existing_sku_count = 0;

      if($sku_count!=0)
        {
          $result='failed';  

        }
      else
      {
        $result='success'; 
      }
    }
    return $result;
  }

  public function check_products_availability($form_data)
  {
     $unavailable_products = $all_products = []; $prod_cnt = 0;
     if(isset($form_data['order_no']) && $form_data['order_no']!="" && isset($form_data['maker_id']))
     {
        if(isset($form_data['order_from']) && $form_data['order_from']!="" && $form_data['order_from']=='retailer')
        {  
             $arr_order_details = $this->RetailerQuotesModel->with('quotes_details.product_details')             ->where('order_no',$form_data['order_no'])
                                                           ->where('maker_id',$form_data['maker_id'])
                                                           ->get()->toArray();
            $order_product_data       = array_column($arr_order_details, 'quotes_details');
                                               
        }

        if(isset($form_data['order_from']) && $form_data['order_from']!="" && $form_data['order_from']=='customer')
        {  
             $arr_order_details = $this->CustomerQuotesModel->with('quotes_details.product_details')             ->where('order_no',$form_data['order_no'])
                                                           ->where('maker_id',$form_data['maker_id'])
                                                           ->get()->toArray();
            $order_product_data       = array_column($arr_order_details, 'quotes_details');
                                               
        }


        else
        {
           $arr_order_details = $this->RepresentativeLeadsModel->with('leads_details.product_details')             ->where('order_no',$form_data['order_no'])
                                                           ->where('maker_id',$form_data['maker_id'])
                                                           ->get()->toArray();  
          $order_product_data        = array_column($arr_order_details, 'leads_details');
                                                  
        } 

        if(isset($order_product_data) && count($order_product_data)>0)
        {
             foreach ($order_product_data[0] as $key => $value)
            {
              $all_products[]          = $value['product_id'];

              if($value['product_details']['is_active']==0)
              {  
               $unavailable_products[] = $value['product_id'];
              }
            }
        }
    
        if(count($unavailable_products) == count($all_products))
        {
           $cnt_unavailable_products    =  count($all_products); 
          //$cnt_unavailable_products    =  'all'; 
        }

        else if(count($unavailable_products)==0)
        {
           $cnt_unavailable_products   =  0;
        } 

        else
        {  
          $cnt_unavailable_products    =  count($unavailable_products);
        }
     }

     return $cnt_unavailable_products;
  }


  public function product_availability($form_data)
  {
     
    $unavailable_products = $all_products = $valid_product_arr = []; $prod_cnt = 0;

    $vendor_active = $product_active = $category_active = $error_message = $brand_active = '';

    if(isset($form_data['order_no']) && $form_data['order_no']!="" && isset($form_data['maker_id']))
    { 
        if(isset($form_data['order_from']) && $form_data['order_from']!="" && $form_data['order_from']=='retailer')
        {  
             $arr_order_details = $this->RetailerQuotesModel
                                        ->with('quotes_details.product_details')             
                                        ->where('order_no',$form_data['order_no'])
                                        ->where('maker_id',$form_data['maker_id'])
                                        ->get()->toArray();

            $order_product_data       = array_column($arr_order_details, 'quotes_details');
                                             
        }


        if(isset($form_data['order_from']) && $form_data['order_from']!="" && $form_data['order_from']=='customer')
        {  
             $arr_order_details = $this->CustomerQuotesModel
                                        ->with('quotes_details.product_details')
                                        ->where('order_no',$form_data['order_no'])
                                        ->where('maker_id',$form_data['maker_id'])
                                        ->get()->toArray();

            $order_product_data       = array_column($arr_order_details, 'quotes_details');
                                               
        }
        
        if(isset($form_data['order_from']) && $form_data['order_from']!="" && $form_data['order_from']=='rep')
        { 
         
           $arr_order_details = $this->RepresentativeLeadsModel
                                      ->with('leads_details.product_details')             
                                      ->where('order_no',$form_data['order_no'])
                                      ->where('maker_id',$form_data['maker_id'])
                                      ->get()->toArray();  


          $order_product_data        = array_column($arr_order_details,'leads_details');
                                                  
        } 


        if(isset($order_product_data) && count($order_product_data)>0)
        {
            foreach ($order_product_data[0] as $key => $value)
            {
                $all_products[] = $value['product_id'];

                //check vendor is active order 
                $vendor_active = $this->UserModel
                                      ->where('id',$value['product_details']['user_id'])
                                      ->where('status',1)
                                      ->where('is_approved',1)
                                      ->count();

               
                //product active or not
                $product_active = $this->BaseModel
                                       ->where('id',$value['product_id'])
                                       ->where('is_active',1)
                                       ->where('product_status',1)
                                       ->where('product_complete_status',4)
                                       ->count();  

                
                $category_active = $this->CategoryModel
                                        ->where('admin_confirm_status',0)
                                        ->where('id',$value['product_details']['category_id'])
                                        ->where('is_active',1)
                                        ->count();


                $brand_active = $this->BrandsModel
                                     ->where('is_active',1)
                                     ->where('id',$value['product_details']['brand'])
                                     ->count();


              if($vendor_active == 1 && $product_active == 1 && $category_active == 1 && $brand_active == 1)
              {
                 $valid_product_arr[] = $value;
              }  


            }

        }
    
    }  
   
  
    if(count($valid_product_arr) == 0)
    { 
       return false;
    }
    else
    {
      return true;
    }

     
  }

  public function get_promotion_and_prodo_code_details($promoCodeTitle=false)
  {
    $arrData = [];
    if($promoCodeTitle)
    {
      $objPromocodeData = $this->PromoCodeModel->where('promo_code_name',$promoCodeTitle)
                                                ->with(['get_promotions_offer_details.get_prmotion_type'])
                                                // ->with(['get_promotions_offer_details'])
                                                 ->get();

      if($objPromocodeData)
      {
        $arrData = $objPromocodeData->toArray();
      }
    }
    // dd($arrData);
    return $arrData;
  }
	
}