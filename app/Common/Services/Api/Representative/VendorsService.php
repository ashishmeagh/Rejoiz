<?php

namespace App\Common\Services\Api\Representative;

use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RepAreaModel;
use App\Models\SiteSettingModel;
use App\Models\RepresentativeModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\ProductsModel;
use App\Models\CategoryModel;
use App\Models\RoleModel;
use App\Models\VendorRepresentativeMappingModel;

use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\Api\Common\CommonService;


use \DB;
use \paginate;
use \Sentinel;

class VendorsService {

	public function __construct(
									RoleModel 			$RoleModel,
									UserModel 			$UserModel,
									CategoryModel 		$CategoryModel,
									ProductsModel 		$ProductsModel,
									SubCategoryModel    $SubCategoryModel,
									MakerModel 			$MakerModel,
									RepAreaModel 		$RepAreaModel,
									RoleUsersModel 		$RoleUsersModel,
									RepresentativeModel $RepresentativeModel,
									ProductsSubCategoriesModel $ProductsSubCategoriesModel,
									VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                    CommonService       $CommonService

								) 
	{
			$this->UserModel 			= $UserModel;
			$this->RoleModel 			= $RoleModel;
			$this->MakerModel 			= $MakerModel;
			$this->RepAreaModel 		= $RepAreaModel;
			$this->SubCategoryModel 	= $SubCategoryModel;
			$this->CategoryModel 		= $CategoryModel;
			$this->ProductsModel 		= $ProductsModel;
			$this->RoleUsersModel 		= $RoleUsersModel;
			$this->RepresentativeModel 	= $RepresentativeModel;
			$this->ProductsSubCategoriesModel 	= $ProductsSubCategoriesModel;
			$this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
            $this->CommonService        = $CommonService;
	}

    public function get_list($user,$perpage,$search) {

        try {

            $loggedIn_userId    = $user->id;         
            $arr_search_column  = $search;

            $user_table         =  $this->UserModel->getTable();
            $prefix_user_table  = DB::getTablePrefix().$user_table;

            $maker_table        =  $this->MakerModel->getTable();
            $prefix_maker_table = DB::getTablePrefix().$maker_table;

            $vendor_representative_mapping_table        = $this->VendorRepresentativeMappingModel->getTable();
            $prefix_vendor_representative_mapping_table = DB::getTablePrefix().$vendor_representative_mapping_table;

            $representative_table                       = $this->RepresentativeModel->getTable();
            $prefix_representative_table                = DB::getTablePrefix().$representative_table;

            $rep_area_table                             = $this->RepAreaModel->getTable();
            $prefix_rep_area_table                      = DB::getTablePrefix().$rep_area_table;

            $obj_user = DB::table($vendor_representative_mapping_table)
                            ->select(DB::raw(
                                            $prefix_user_table.".profile_image,".
                                            $prefix_user_table.".id as uid,".
                                            $prefix_user_table.".email as email, ".
                                            $prefix_user_table.".contact_no as contact_no,".
                                            $prefix_user_table.".country_code, ".
                                            $prefix_user_table.".post_code, ".
                                            $prefix_maker_table.".company_name, ".
                                            $prefix_maker_table.".brand_name, ".
                                            $prefix_maker_table.".primary_category_id, ".
                                            $prefix_maker_table.".description, ". 
                                            $vendor_representative_mapping_table.".vendor_id, ".
                                            $representative_table.".id as rid,".
                                            $representative_table.".area_id,".
                                            $rep_area_table.".id as aid,".
                                            $rep_area_table.".area_name,".
                                            "CONCAT(".$prefix_user_table.".first_name,' '," .$prefix_user_table.".last_name) as user_name"
                                        )
                                    )
                            ->leftJoin($prefix_user_table,$vendor_representative_mapping_table.'.vendor_id','=',$user_table.'.id')
                            ->leftJoin($representative_table,$vendor_representative_mapping_table.'.representative_id','=',$representative_table.'.user_id')
                            ->leftJoin($prefix_rep_area_table,$representative_table.'.area_id','=',$rep_area_table.'.id')
                            ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                            ->where('representative_id',$loggedIn_userId)
                            ->orderBy($prefix_user_table.".id", 'desc');
;
                            //->get();

           /* ---------------- Filtering Logic ----------------------------------*/  

            if(isset($arr_search_column) && $arr_search_column!="") {

                $search_term  = $arr_search_column;

                $obj_user     = $obj_user->whereRaw(
                                                "(  `".$prefix_user_table."`  .`first_name` LIKE '%".$search_term."%' OR 
                                                    `".$prefix_user_table."`  .`last_name` LIKE '%".$search_term."%'  OR 
                                                    `".$prefix_maker_table."` .`company_name` LIKE '%".$search_term."%' OR 
                                                    `".$prefix_user_table."`  .`email` LIKE '%".$search_term."%' OR 
                                                    `".$prefix_user_table."`  .`contact_no` LIKE '%".$search_term."%')"
                                                );
            }

            $obj_user = $obj_user->paginate($perpage);

            $arr_user = $obj_user->toArray();

            if (isset($arr_user['data']) && !empty($arr_user['data'])) {

                foreach ($arr_user['data'] as $key => $value) {


                    $user_data['vendor_data'][$key]['profile_image']    = imagePath($value->profile_image, "user", 0);
                    $user_data['vendor_data'][$key]['uid']              = $value->uid;
                    $user_data['vendor_data'][$key]['email']            = $value->email;
                    $user_data['vendor_data'][$key]['contact_no']       = $value->contact_no;
                    $user_data['vendor_data'][$key]['country_code']     = $value->country_code;
                    $user_data['vendor_data'][$key]['post_code']        = $value->post_code;
                    $user_data['vendor_data'][$key]['company_name']     = $value->company_name;
                    $user_data['vendor_data'][$key]['brand_name']       = $value->brand_name;
                    $user_data['vendor_data'][$key]['primary_category'] = get_catrgory_name($value->primary_category_id);
                    $user_data['vendor_data'][$key]['description']      = $value->description;
                    $user_data['vendor_data'][$key]['vendor_id']        = $value->vendor_id;
                    $user_data['vendor_data'][$key]['rid']              = $value->rid;
                    $user_data['vendor_data'][$key]['area_id']          = $value->area_id;
                    $user_data['vendor_data'][$key]['aid']              = $value->aid;
                    $user_data['vendor_data'][$key]['area_name']        = $value->area_name;
                    $user_data['vendor_data'][$key]['user_name']        = $value->user_name;
                }
            }
            else {

                $user_data['vendor_data'] = [];
            }

            $user_data['pagination']["current_page"]      = $arr_user["current_page"];
            $user_data['pagination']["first_page_url"]    = $arr_user["first_page_url"];
            $user_data['pagination']["from"]              = $arr_user["from"];
            $user_data['pagination']["last_page"]         = $arr_user["last_page"];
            $user_data['pagination']["last_page_url"]     = $arr_user["last_page_url"];
            $user_data['pagination']["next_page_url"]     = $arr_user["next_page_url"];
            $user_data['pagination']["path"]              = $arr_user["path"];
            $user_data['pagination']["per_page"]          = $arr_user["per_page"];
            $user_data['pagination']["prev_page_url"]     = $arr_user["prev_page_url"];
            $user_data['pagination']["to"]                = $arr_user["to"];
            $user_data['pagination']["total"]             = $arr_user["total"];

            $user_data['vendor_data']                     = $this->CommonService->get_status_display_names($user_data['vendor_data']); 


            $response               = [];
            $response['status']     = 'success';
            $response['message']    = 'Vendors list get successfully.';
            $response['data']       = $user_data;

            
            return $response;

        } catch(Exception $e) {
      
            $response['status']     = 'failure';
            $response['message']    = $e->getMessage();
            $response['data']       = '';

            return $response;
        }
    }

    public function product_list($user_id , $search , $perpage) {


        try {

            $loggedIn_userId    = $user_id;
            $arr_search_column  = $search;
            
            $arr_makers = []; 
            $arr_makers = $this->get_all_makers();

            $product_tbl_name     = $this->ProductsModel->getTable();        
            $prefixed_product_tbl = DB::getTablePrefix().$this->ProductsModel->getTable();

            $product_category_tbl_name     = $this->CategoryModel->getTable();        
            $prefixed_product_category_tbl = DB::getTablePrefix().$this->CategoryModel->getTable();

            $subcategory_tbl_name     = $this->SubCategoryModel->getTable();        
            $prefixed_subcategory_tbl = DB::getTablePrefix().$this->SubCategoryModel->getTable();

            $product_subcategory_tbl_name     = $this->ProductsSubCategoriesModel->getTable();        
            $prefixed_product_subcategory_tbl = DB::getTablePrefix().$this->ProductsSubCategoriesModel->getTable();

            $maker_tbl              = $this->MakerModel->getTable();        
            $prefixed_maker_tbl     = DB::getTablePrefix().$this->MakerModel->getTable();

            $user_table        =  $this->UserModel->getTable();
            $prefix_user_table = DB::getTablePrefix().$user_table;

            $vendor_rep_mapping_table        = $this->VendorRepresentativeMappingModel->getTable();
            $prefix_vendor_rep_mapping_table = DB::getTablePrefix().$this->VendorRepresentativeMappingModel->getTable();
           
            $obj_products = DB::table($vendor_rep_mapping_table)
                            ->select(DB::raw($prefixed_product_tbl.".id,".
                                                     $prefixed_product_tbl.".user_id,".
                                                     $prefixed_product_tbl.".product_image,".
                                                     $prefixed_maker_tbl.".company_name,".
                                                     $prefixed_product_tbl.'.is_active,'.                                                     $prefixed_product_tbl.'.category_id,'.
                                                     $prefixed_product_tbl.'.product_name,'.
                                                      "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                              .$prefix_user_table.".last_name) as user_name,".
                                                     $prefixed_product_tbl.".created_at,".
                                                     $prefixed_product_tbl.".unit_wholsale_price,".
                                                     $prefixed_product_tbl.".retail_price"
                                                   ))
                            ->leftjoin($maker_tbl,$maker_tbl.'.user_id','=',$vendor_rep_mapping_table.'.vendor_id')
                            ->leftjoin($user_table,$user_table.'.id','=',$vendor_rep_mapping_table.'.vendor_id')
                            ->leftjoin($product_tbl_name,$product_tbl_name.'.user_id','=',$vendor_rep_mapping_table.'.vendor_id')
                            ->where($vendor_rep_mapping_table.'.representative_id',$loggedIn_userId)
                            ->where($user_table.'.status','1')
                            ->where($user_table.'.is_approved','1')
                            ->where($product_tbl_name.'.product_complete_status','4')
                            ->where($product_tbl_name.'.is_active','1')
                            ->where($product_tbl_name.'.product_status','1')
                            ->where($product_tbl_name.'.is_deleted','0')
                            ->groupBy($product_tbl_name.'.id')
                            ->orderBy($product_tbl_name.'.updated_at',"DESC");
                                  
                /* ---------------- Filtering Logic ----------------------------------*/    

                    if(isset($search) && $search!="") {

                         $obj_products = $obj_products->whereRaw(
                                                    "(  `".$prefixed_product_tbl."`.`product_name` LIKE '%".$search."%' OR
                                                        `".$prefixed_maker_tbl."`.`company_name` LIKE '%".$search."%' OR
                                                        `".$prefixed_product_tbl."`.`unit_wholsale_price` LIKE '%".$search."%' OR
                                                        `".$prefixed_product_tbl."`.`retail_price` LIKE '%".$search."%' )"
                                                   );
                    }

                /* ---------------- Filtering Logic ----------------------------------*/    

                $obj_products = $obj_products->paginate($perpage);

                $arr_products = $obj_products->toArray();


            if (isset($arr_products['data']) && !empty($arr_products['data'])) {

                foreach ($arr_products['data'] as $key => $value) {   

                    $product_data['product_data'][$key]['id']                   =   $value->id;
                    $product_data['product_data'][$key]['image']                =   $this->CommonService->imagePathProduct($value->product_image, 'product', 0);
                    $product_data['product_data'][$key]['user_id']              =   $value->user_id;
                    $product_data['product_data'][$key]['company_name']         =   $value->company_name;
                    $product_data['product_data'][$key]['is_active']            =   $value->is_active;
                    $product_data['product_data'][$key]['category_id']          =   $value->category_id;
                    $product_data['product_data'][$key]['product_name']         =   $value->product_name;
                    $product_data['product_data'][$key]['user_name']            =   $value->user_name;
                    $product_data['product_data'][$key]['created_at']           =   $value->created_at;
                    $product_data['product_data'][$key]['unit_wholsale_price']  =   $value->unit_wholsale_price;
                    $product_data['product_data'][$key]['retail_price']         =   $value->retail_price;   
                }
            }
            else {

                $product_data['product_data'] = [];
            }

            $product_data['pagination']["current_page"]       = $arr_products["current_page"];
            $product_data['pagination']["first_page_url"]     = $arr_products["first_page_url"];
            $product_data['pagination']["from"]               = $arr_products["from"];
            $product_data['pagination']["last_page"]          = $arr_products["last_page"];
            $product_data['pagination']["last_page_url"]      = $arr_products["last_page_url"];
            $product_data['pagination']["next_page_url"]      = $arr_products["next_page_url"];
            $product_data['pagination']["path"]               = $arr_products["path"];
            $product_data['pagination']["per_page"]           = $arr_products["per_page"];
            $product_data['pagination']["prev_page_url"]      = $arr_products["prev_page_url"];
            $product_data['pagination']["to"]                 = $arr_products["to"];
            $product_data['pagination']["total"]              = $arr_products["total"];

            $response               = [];
            $response['status']     = 'success';
            $response['message']    = 'Product list get successfully.';
            $response['data']       = $product_data;
            
            return $response;
            
        } catch(Exception $e) {
      
            $response['status']     = 'failure';
            $response['message']    = $e->getMessage();
            $response['data']       = '';

            return $response;
        }        
    }

    public function get_all_makers() {

        $arr_maker_list = $arr_maker = [];
        $user           = Sentinel::check();
        $role_id        = Sentinel::findRoleBySlug('Maker');
        $post_code      = '';

        $user_table             =  $this->UserModel->getTable();
        $prefix_user_table      = DB::getTablePrefix().$user_table;

        $maker_table            =  $this->MakerModel->getTable();
        $prefix_maker_table     = DB::getTablePrefix().$maker_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $role_table             =  $this->RoleModel->getTable();
        $prefix_role_table      = DB::getTablePrefix().$role_table;

        $arr_maker = DB::table($user_table)
                            ->select(DB::raw($prefix_user_table.".id as id" ))
                            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
                            ->where($role_table.'.slug','=','Maker')
                            ->whereNull($user_table.'.deleted_at')
                            ->where('status','1')
                            ->where($user_table.'.id','!=','1')
                            // ->where($user_table.'.post_code',$post_code)
                            ->orderBy($user_table.'.created_at','DESC')
                            ->get()
                            ->toArray();

        if(isset($arr_maker) &&count($arr_maker) > 0) {

            foreach ($arr_maker as $key => $maker) {

                $arr_maker_list[]['maker_id'] = $maker->id;
            }
        }
        return $arr_maker_list;
    }
}

?>