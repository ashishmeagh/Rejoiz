<?php
namespace App\Common\Services;

use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\CityModel;
use App\Models\ProductsModel;
use App\Models\UserModel;
use App\Models\BrandsModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\MakerModel;
use App\Models\ShopImagesModel;
use App\Models\ShopSettings;
use App\Models\ProductsSubCategoriesModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;

use \Session;
use \Sentinel;
use \Activation;
use \DB;


class SearchService
{
	public function __construct(CountryModel $CountryModel,
								StateModel $StateModel,
								BrandsModel $BrandsModel,
								CityModel $CityModel,
								ProductsModel $ProductsModel,
								UserModel $UserModel,
								RoleModel $RoleModel,
								RoleUsersModel $RoleUsersModel,
								MakerModel $MakerModel,
								ShopImagesModel $ShopImagesModel,
								ShopSettings $ShopSettings,
								ProductsSubCategoriesModel $ProductsSubCategoriesModel,
								CategoryModel $CategoryModel,
								CategoryTranslationModel $CategoryTranslationModel
								)
	{
		$this->CountryModel                    = $CountryModel;
		$this->StateModel                      = $StateModel;
		$this->CityModel                       = $CityModel;
		$this->UserModel 					   = $UserModel;
		$this->ProductsModel                   = $ProductsModel;
		$this->RoleModel                       = $RoleModel; 
		$this->RoleUsersModel                  = $RoleUsersModel;
		$this->MakerModel                      = $MakerModel;
		$this->BrandsModel                     = $BrandsModel;
		$this->ShopImagesModel                 = $ShopImagesModel;
		$this->ShopSettings                    = $ShopSettings;
		$this->ProductsSubCategoriesModel      = $ProductsSubCategoriesModel;
		$this->CategoryModel                   = $CategoryModel;
		$this->CategoryTranslationModel        = $CategoryTranslationModel; 

	}

	public function search($arr_data = [])
	{
		$search_term = isset($arr_data['search_term'])?$arr_data['search_term']:"";
		$search_type = isset($arr_data['search_type'])?$arr_data['search_type']:"";
		$subcategory_id = isset($arr_data['subcategory'])?base64_decode($arr_data['subcategory']):"";

		// if($search_type!="" && ($search_type=='brand' || $search_type=='maker'))
		if($search_type!="" &&  $search_type=='maker')
		{
		    $role_slug = 'maker';
			
			$user_table =  $this->UserModel->getTable();
			$prefix_user_table = DB::getTablePrefix().$user_table;

			$role_table =  $this->RoleModel->getTable();
			$prefix_role_table = DB::getTablePrefix().$role_table;

			$role_user_table =  $this->RoleUsersModel->getTable();
			$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

			$shop_table =  $this->ShopImagesModel->getTable();
			$prefix_shop_table = DB::getTablePrefix().$shop_table;

			$maker_table        = $this->MakerModel->getTable();
			$prefix_maker_table = DB::getTablePrefix().$maker_table;

			$shop_setting_table        = $this->ShopSettings->getTable();
			$prefix_shop_setting_table = DB::getTablePrefix().$shop_table;
			
			$obj_user = DB::table($maker_table)
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
						->where($user_table.'status',1)
						->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->where($maker_table.'.company_name','!=',"")
                        ->orderBy($maker_table.'.company_name','ASC');


            if(isset($search_term) && $search_term!="" && $search_type == 'brand')
	        {
	            $obj_user = $obj_user->where($maker_table.'.brand_name','LIKE', '%'.$search_term.'%');
	        }

	        if(isset($search_term) && $search_term!="" && $search_type == 'maker')
	        {

	            $obj_user = $obj_user->where($maker_table.'.company_name','LIKE', '%'.$search_term.'%');
	        }elseif(isset($search_term) && $search_term!="")
	        {
	            $obj_user = $obj_user->orwhere($prefix_user_table.'.first_name','LIKE', '%'.$search_term.'%')->orwhere($prefix_user_table.'.last_name','LIKE', '%'.$search_term.'%');
	        }

	        if($obj_user)
	        {
	        	$obj_search_data = $obj_user->get();
                $arr_data = $obj_search_data->toArray();
	        }
	      	
	        return $arr_data; 
		}
		elseif($search_type!="" && $search_type=='representative')
		{
			$this->role = 'representative';
	    	$user_table =  $this->UserModel->getTable();
			$prefix_user_table = DB::getTablePrefix().$user_table;

			$role_table =  $this->RoleModel->getTable();
			$prefix_role_table = DB::getTablePrefix().$role_table;

			$role_user_table =  $this->RoleUsersModel->getTable();
			$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

			$obj_user = DB::table($user_table)
							->select(DB::raw($prefix_user_table.".id as id,".
	                                     $prefix_user_table.".email as email, ".
	                                     $prefix_user_table.".status, ".
	                                     $prefix_user_table.".is_approved, ".
	                                     $prefix_user_table.".profile_image, ".
	                                     $prefix_user_table.".nationality, ".
	                                     $prefix_user_table.".wallet_address as wallet_address, ".
	                                     $prefix_user_table.".contact_no as contact_no, ".
	                                     $prefix_user_table.".post_code, ".
	                                     $role_table.".slug as slug, ".
	                                     $role_table.".name as name, ".
	                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
	                                              .$prefix_user_table.".last_name) as user_name"
	                                     ))
							->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
							->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
							->where($role_table.'.slug','=',$this->role)
							->whereNull($user_table.'.deleted_at')
	                        ->where($user_table.'.id','!=',1)
	                        ->orderBy($user_table.'.created_at','DESC');
	        

	        
	        //dd($obj_user->get()->toArray());
	        /*if(isset($search_term) && $search_term!="")
	        {
	            $obj_user = $obj_user->Where($user_table.'.post_code','LIKE', '%'.$search_term.'%');
	        }*/	
	        if(isset($search_term) && $search_term!="")
	        {
	            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
	        }

	        if($obj_user)
	        {
	        	$obj_search_data = $obj_user->get();
                $arr_data = $obj_search_data->toArray();
	        }
	       	//dd($arr_data);
	        return $arr_data; 

	                    
        /* ---------------- Filtering Logic ----------------------------------*/  
		}
		else
		{
			
			$product_table =  $this->ProductsModel->getTable();
			$prefix_product_table = DB::getTablePrefix().$product_table;

			$brands_table =  $this->BrandsModel->getTable();
			$prefix_brands_table = DB::getTablePrefix().$brands_table;

			$user_table =  $this->UserModel->getTable();
			$prefix_user_table = DB::getTablePrefix().$user_table;

			$maker_table        = $this->MakerModel->getTable();
			$prefix_maker_table = DB::getTablePrefix().$maker_table;

			$category_table        = $this->CategoryModel->getTable();
			$prefix_category_table = DB::getTablePrefix().$category_table;					

			$category_trans_tbl         = $this->CategoryTranslationModel->getTable();                
        	$prefixed_category_trans_tbl= DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

			$subcategory_table 		   = $this->ProductsSubCategoriesModel->getTable();
			$prefix_subcategory_table = DB::getTablePrefix().$subcategory_table;

			$obj_product = DB::table($product_table)
						->select(DB::raw($prefix_product_table.".id as id,".
									 $prefix_product_table.".user_id,".	
                                     $prefix_product_table.".product_name, ".
                                     $prefix_product_table.".description, ".
                                     $prefix_product_table.".unit_wholsale_price, ".
                                     $prefix_product_table.".retail_price, ".	
                                     $prefix_product_table.".available_qty, ".
                                     $prefix_product_table.".is_active, ".
                                     $prefix_product_table.".product_image, ".
                                     $prefix_product_table.".product_complete_status,".
                                     $prefix_product_table.".category_id,".
                                     $prefix_product_table.".product_image_thumb,".
                                     $prefixed_category_trans_tbl.'.category_name,'.
                                     $prefix_maker_table.'.company_name,'.
                                     $prefix_brands_table.".brand_name,".
                                     $prefix_brands_table.".id as brand_id"
                                     ))
						->leftJoin($user_table,$prefix_user_table.'.id','=',$product_table.'.user_id')
						->leftJoin($brands_table,$prefix_brands_table.'.id','=',$product_table.'.brand')
						->leftJoin($maker_table,$prefix_maker_table.'.user_id','=',$product_table.'.user_id')
						->leftjoin($category_trans_tbl,$prefixed_category_trans_tbl.'.category_id','=',$product_table.'.category_id')
						->where($prefix_product_table.'.is_active',1)
						->where($prefix_product_table.'.product_complete_status',4)
                        ->orderBy($prefix_product_table.'.created_at','DESC');

             //d($obj_product->get()->toArray());           
            if(isset($search_type) && $search_type=="category" && $search_term=="")
	        {
	        	$category_id = "";
	        	$enc_category_id = isset($arr_data['category-id'])?$arr_data['category-id']:"";
		    	if($enc_category_id)
		    	{
		    		$category_id = base64_decode($enc_category_id);
		    	}
		    	if($category_id!="")
		    	{
	        		$obj_product = $obj_product->where($prefix_product_table.'.category_id',$category_id);
		    	}
	        }
	     
	        if(isset($search_type) && $search_type=="subcategory")
	        {
	        	$enc_category_id = isset($arr_data['category-id'])?$arr_data['category-id']:"";
	        	if($enc_category_id)
	        	{
	        		$category_id = base64_decode($enc_category_id);
	        	}

	        	
	        	$obj_product = $obj_product->where($prefix_product_table.'.category_id',$category_id);

	        	if(isset($subcategory_id) && $subcategory_id != "")
		        {		

		        	$obj_product = $obj_product->leftJoin($subcategory_table,$product_table.'.id','=',$prefix_subcategory_table.'.product_id')
		        								->where($prefix_subcategory_table.'.sub_category_id','=',$subcategory_id)
		        								;
		        }

	        }

	      
	        if(isset($search_type) && $search_type=="category" && $search_term!="")
	        {
	        	$obj_product = $obj_product->where($prefixed_category_trans_tbl.'.category_name','LIKE', '%'.$search_term.'%');    
	        }
	        elseif(isset($search_type) && $search_type=="brand" && $search_term!="")
	        {
	        	$obj_product = $obj_product->where($prefix_brands_table.'.brand_name','LIKE', '%'.$search_term.'%');   
	        }
            elseif(isset($search_term) && $search_term!="")
	        {
	            $obj_product = $obj_product->where($prefix_product_table.'.product_name','LIKE', '%'.$search_term.'%');      
	        }

	        /*
	        if(isset($subcategory_id) && $subcategory_id != "")
	        {			


	        	$obj_product = $obj_product->leftJoin($subcategory_table,$product_table.'.id','=',$prefix_subcategory_table.'.product_id')
	        								
	        								->where($prefix_subcategory_table.'.sub_category_id','=',$subcategory_id)	;
	        }
	        */
	        if($obj_product)
	        {
	        	$obj_search_data = $obj_product->get();
                $arr_data = $obj_search_data->toArray();
	        }

	        return $arr_data;

		}

		
	}
	

}


?>