<?php

namespace App\Common\Services\Api\Rejoiz\Front;

use App\Models\UserModel;
use App\Models\MenuSettingModel;
use App\Models\BannerImageModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\CountryModel;
use App\Models\SiteSettingModel;
use App\Models\EmailSubscriptiponModel;
use App\Models\CategoryDivisionModel;
use App\Models\RepAreaModel;
use App\Models\PromotionsModel;
use App\Models\RepresentativeModel;
use App\Models\FaqModel;
use App\Models\StateModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SalesManagerModel;
use App\Models\MakerModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\ShopImagesModel;
use App\Models\FavoriteModel;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\HelperService;
use \paginate;
use DB;


class HomeService {

	public function __construct(
									UserModel $UserModel,
									CountryModel $CountryModel,
                                    MenuSettingModel $MenuSettingModel,
                                    BannerImageModel $BannerImageModel,
                                    SiteSettingModel $SiteSettingModel,
                                    CategoryModel $CategoryModel,
                                    SubCategoryModel $SubCategoryModel,
                                    EmailSubscriptiponModel $EmailSubscriptiponModel,
                                    FaqModel $FaqModel,
                                    CategoryDivisionModel $CategoryDivisionModel,
                                    RepAreaModel $RepAreaModel,
                                    PromotionsModel $PromotionsModel,
                                    RepresentativeModel $RepresentativeModel,
                                    StateModel $StateModel,
                                    RoleModel $RoleModel,
                                    RoleUsersModel $RoleUsersModel,
                                    SalesManagerModel $SalesManagerModel,
                                    MakerModel $MakerModel,
                                    VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                    ShopImagesModel $ShopImagesModel,
                                    CategoryTranslationModel $CategoryTranslationModel,
                                    SubCategoryTranslationModel $SubCategoryTranslationModel,
                                    FavoriteModel $FavoriteModel,
									GeneralService $GeneralService,
									EmailService $EmailService,
									HelperService $HelperService
								) 
	{
		$this->UserModel 			= $UserModel;
        $this->MenuSettingModel     = $MenuSettingModel;
        $this->BannerImageModel     = $BannerImageModel;
		$this->CountryModel 	    = $CountryModel;
		$this->CategoryModel        = $CategoryModel;
		$this->SubCategoryModel     = $SubCategoryModel;
		$this->SiteSettingModel     = $SiteSettingModel;
		$this->EmailSubscriptiponModel = $EmailSubscriptiponModel;
		$this->FaqModel             = $FaqModel;
		$this->RepAreaModel         = $RepAreaModel;
		$this->CategoryDivisionModel= $CategoryDivisionModel;
		$this->PromotionsModel      = $PromotionsModel;
		$this->RepresentativeModel  = $RepresentativeModel;
		$this->StateModel           = $StateModel;
		$this->RoleModel            = $RoleModel;
		$this->RoleUsersModel       = $RoleUsersModel;
		$this->SalesManagerModel    = $SalesManagerModel;
		$this->MakerModel           = $MakerModel;
		$this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
		$this->CategoryTranslationModel = $CategoryTranslationModel;
		$this->SubCategoryTranslationModel = $SubCategoryTranslationModel;
		$this->ShopImagesModel      = $ShopImagesModel;
		$this->FavoriteModel        = $FavoriteModel;
		$this->GeneralService 		= $GeneralService;
		$this->EmailService         = $EmailService;
		$this->HelperService        = $HelperService;
	}

	public function get_menus() {

		try {

                 $arr_data           = "";  
		         $obj_data           = $this->MenuSettingModel->select('id','menu_name','menu_slug')->where('menu_status',1)->get(); 

		         if($obj_data!=null)
		         {
		         	$arr_data        = $obj_data->toArray();   
		         } 


                    if(isset($arr_data) && !empty($arr_data))
                    {	
    		           		$response['status']            = 'success';
      						$response['message']           = 'Menu get successfully.';
      						$response['data'] 		       =  isset($arr_data)?$arr_data:[];

			      		    return $response;
		      	        }

    		      	    else
    		      	    {
    		      	    	$response['status']            = 'failure';
      						$response['message']           = 'Something went wrong ,please try again.';
      						$response['data'] 		       = '';

      						return $response;
    		      	    }
	            } 
			
          catch (Exception $e) {
			$response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
    	}
	}


	public function get_slider_images()
	{

		try {
                 $arr_data           = $arr = [];  
		         $obj_slider_data    = $this->BannerImageModel->select('id','banner_image','banner_image_small','type','url')->get(); 

		         if($obj_slider_data!=null)
		         {
		         	$arr_slider_data   = $obj_slider_data->toArray();   

		         	foreach($arr_slider_data as $key => $val)
                    {
                    	if($val['type']==3){
                    		$type = 'slider_images';
                    	}

                    	if($val['type']==1 || $val['type']==2){
                    		$type = 'banner_images';
                    	}

                    	$arr['image']        = url('/').'/storage/app/'.$val['banner_image'];
                    	$arr['image_small']  = url('/').'/storage/app/'.$val['banner_image_small'];
                    	$arr['type']         = $val['type'];  
                    	$arr['url']          = $val['url'];	

                    	$arr_data[$type][] = $arr;
                    }  
		         } 


                    if(isset($arr_data) && !empty($arr_data))
                    {	
    		           		$response['status']            = 'success';
          					$response['message']           = 'Images get successfully.';
          					$response['data'] 		       =  isset($arr_data)?$arr_data:[];

			      		    return $response;
		      	    }

		      	    else
		      	    {
		      	    	$response['status']            = 'failure';
      					$response['message']           = 'Something went wrong ,please try again.';
      					$response['data'] 		       = '';

      				    return $response;
		      	    }
	            } 
			
          catch (Exception $e) {
			$response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
    	}
	}


/*	public function get_categories($page=null,$per_page=null)
	{
		try
		{
		
		    $per_page         = isset($per_page)?$per_page:''; 
            $obj_categories   = $this->CategoryModel->with(['subcategory_details'=>function($subcategory_details)
             	                                            {$subcategory_details->where('is_active',1);}])
                                                   ->where('is_active',1)
                                                   ->orderBy('id','ASC');


            $obj_categories = $obj_categories->paginate($per_page);  
            $arr_categories = $obj_categories->toArray();

          if(isset($arr_categories['data']) && !empty($arr_categories['data']))
          {
          	foreach($arr_categories['data'] as $key => $val)
          	{
          		$category_name = $val['category_name'];

          		foreach($val['subcategory_details'] as $subcat)
          		{
          		   $arr['subcategory_name']  = $subcat['subcategory_name'];	
          		   $arr['subcategory_slug']  = $subcat['subcategory_slug'];	

          		   $arr_data[$category_name][] = $arr;

          		}
          	}
          }  

            $arr_data['pagination']["current_page"]    = $arr_categories['current_page'];
            $arr_data['pagination']["first_page_url"]  = $arr_categories['first_page_url'];
            $arr_data['pagination']["from"]            = $arr_categories['from'];
            $arr_data['pagination']["last_page"]       = $arr_categories['last_page'];
            $arr_data['pagination']["last_page_url"]   = $arr_categories['last_page_url'];
            $arr_data['pagination']["next_page_url"]   = $arr_categories['next_page_url'];
            $arr_data['pagination']["path"]            = $arr_categories['path'];
            $arr_data['pagination']["per_page"]        = $arr_categories['per_page'];
            $arr_data['pagination']["prev_page_url"]   = $arr_categories['prev_page_url'];
            $arr_data['pagination']["to"]              = $arr_categories['to'];
            $arr_data['pagination']["total"]           = $arr_categories['total'];

           $response = [
				'status'  => 'success',
				'message' => 'Categories get successfully.',
				'data'    => isset($arr_data)?$arr_data:[]
			];

      		return $response;                                 
		}

		catch(Exception $e)
		{
            $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
		}
	}
*/

	public function get_categories($page=null,$per_page=null)
	{
		try
		{
		    $per_page         = isset($per_page)?$per_page:12; 
            $obj_categories   = $this->CategoryModel->where('is_active',1)
                                                    ->orderBy('id','ASC');

            $obj_categories       = $obj_categories->paginate($per_page);
            $arr_categories       = $obj_categories->toArray();



            if(isset($arr_categories['data']) && !empty($arr_categories['data']))
            {
            	foreach($arr_categories['data'] as $key => $val)
            	{
            		$category_names[] = $val['category_name']; 
            	}
            }


          $arr_data['categories_data'] = array();

          if(isset($arr_categories['data']) && !empty($arr_categories['data']))
          {
          	foreach($arr_categories['data'] as $category)
          	{
          		 $temp_data = array();
          		 $temp_data['category_id']    = $category['id'];
                 $temp_data['category_name']  = $category['category_name'];
                 $temp_data['category_image'] = imagePath($category['category_image'],'category',0);
                 $temp_data['subcategories']  =  array();

                 $obj_subcategories = $this->SubCategoryModel->where('is_active',1)->where('category_id',$category['id'])->get(); 
                 if($obj_subcategories)
                 {
                 	$arr_subcategories = $obj_subcategories->toArray();
                 }

                if(isset($arr_subcategories) && !empty($arr_subcategories))
                {	
	          		foreach($arr_subcategories as $key => $val)
	          		{
	          		   $arr['subcategory_id']    = $val['id'];		
	          		   $arr['subcategory_name']  = $val['subcategory_name'];	
	          		   $arr['subcategory_slug']  = $val['subcategory_slug'];	

	          		   array_push($temp_data['subcategories'],$arr);  
	          		}
          	   }
          	   array_push($arr_data['categories_data'],$temp_data);  
          	}
          }  

            $arr_data['pagination']["current_page"]    = $arr_categories['current_page'];
            $arr_data['pagination']["first_page_url"]  = $arr_categories['first_page_url'];
            $arr_data['pagination']["from"]            = $arr_categories['from'];
            $arr_data['pagination']["last_page"]       = $arr_categories['last_page'];
            $arr_data['pagination']["last_page_url"]   = $arr_categories['last_page_url'];
            $arr_data['pagination']["next_page_url"]   = $arr_categories['next_page_url'];
            $arr_data['pagination']["path"]            = $arr_categories['path'];
            $arr_data['pagination']["per_page"]        = $arr_categories['per_page'];
            $arr_data['pagination']["prev_page_url"]   = $arr_categories['prev_page_url'];
            $arr_data['pagination']["to"]              = $arr_categories['to'];
            $arr_data['pagination']["total"]           = $arr_categories['total'];

           $response = [
				'status'  => 'success',
				'message' => 'Categories get successfully.',
				'data'    => isset($arr_data)?$arr_data:[]
			];

      		return $response;                                 
		}

		catch(Exception $e)
		{
            $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
		}
	}

	public function about_us()
	{ 
		try{

			$obj_data = $this->SiteSettingModel->where('id',1)->select('site_short_description')->first();
			if($obj_data)
			{
				$data                 = $obj_data->toArray();
				$arr_data['about_us'] = $data['site_short_description'];  
			}


           $response = [
				'status'  => 'success',
				'message' => 'About us details get successfully.',
				'data'    => isset($arr_data)?$arr_data:[]
			];

      		return $response;    		
		}

		catch(Exception $e)
		{
            $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
		}

	}

	public function subscribe($email=null)
	{
       try{
       	        $arr_user_data['email'] = $email;


		        $status = $this->EmailSubscriptiponModel->create($arr_user_data);  
		        
		        /*Get site setting data from helper*/
		        $arr_site_setting = get_site_settings(['site_name','website_url']);


		        $credentials = ['email' => $arr_user_data['email']];
		      
		        $arr_user = get_user_by_credentials($credentials);


		        if($status)
		        {
		            $subscribed_email  = $arr_user_data['email'];
		           
		            $arr_built_content = [
		                                    'email'          => $subscribed_email,
		                                    'SITE_URL'       => $arr_site_setting['website_url'],
		                                    'PROJECT_NAME'   => $arr_site_setting['site_name'],
		                                ];
		   

		            $arr_mail_data                      = [];
		            $arr_mail_data['email_template_id'] = '35';
		            $arr_mail_data['arr_built_content'] = $arr_built_content;
		            $arr_mail_data['arr_user']          = $arr_user;
		               
		          
		            try
		            {                
		                $is_mail_sent = $this->EmailService->send_mail($arr_mail_data);
		                $response['status']      = 'success';
			            $response['message']     = 'You have subscribed.';
			            $response['data']        = '';

		            return $response;
		             
		            }
		            catch(\Exception $e)
		            {
		                $response['status']       = 'failure';
		                $response['message']      = $e->getMessage();
		                $response['data']         = '';

		                return $response;
		            }
		                
                }
       }

      catch(Exception $e)
		{
            $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
		}
	}

	public function faqs()
	{
	   try{

			   	$faq_data = $this->FaqModel->where('is_active',1)->get();
			   	if($faq_data)
			   	{
			   		$faq_data = $faq_data->toArray();
			   		if(isset($faq_data) && !empty($faq_data))
			   		{
			   			foreach($faq_data as $key => $val)
			   			{
			   				if($val['faq_for']==1){
		                     $type = 'Retailer';
			   			    }
			   			    if($val['faq_for']==2){
			   			     $type = 'Vendor';	
			   			    }

		                   $arr['question'] = $val['question'];
		                   $arr['answer']	= $val['answer'];
		                   
		                   $arr_data[$type][] = $arr;   			    
			   		    }
			   		}
			   	}

			   	 $response['status']      = 'success';
		         $response['message']     = 'Faqs get successfully.';
		         $response['data']        = isset($arr_data)?$arr_data:'';

		         return $response;
	   }

	   catch(Exception $e){

	    $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;

	   }
	}

	public function get_social_links()
	{
	   try{

	         $arr_data = [];
		   	 $social_links_data = $this->SiteSettingModel->where('id',1)->first();
		   	 if($social_links_data)
		   	 {
		   	 	$social_links_data = $social_links_data->toArray();
		   	 	$arr_data['facebook_url'] = $social_links_data['fb_url'];
		   	 	$arr_data['twitter_url']  = $social_links_data['twitter_url'];
		   	 	$arr_data['linkdin_url']  = $social_links_data['linkdin_url'];
		   	 	$arr_data['youtube_url']  = $social_links_data['youtube_url'];
		   	 	$arr_data['instagram_url']= $social_links_data['instagram_url'];
		   	 	$arr_data['rss_feed_url'] = $social_links_data['rss_feed_url'];
		   	 }

		   	 $response['status']      = 'success';
	         $response['message']     = 'Social links get successfully.';
	         $response['data']        = isset($arr_data)?$arr_data:'';

	         return $response;

	   }

	   catch(Exception $e){

	   	 $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;

	   }
	  
	}   


	public function special_offers()
	{
		try{

            $arr_data['offers'] = []; 
			$arr_area =  RepAreaModel::where('status',1)->orderBy("area_name")->get()->toArray();
			if(isset($arr_area) && count($arr_area)>0)
	        { 
	        	$arr['name']          = 'All Offers';
	        	$arr['area_id']       = '';
                $arr['category_id']   = '';
	        	$arr_data['offers'][] = $arr;

	            foreach ($arr_area as $key => $area)
	            {  
	                $category_id_arr = json_decode($area['category_id']);
	               
	                if(isset($category_id_arr) && count($category_id_arr)>0)
	                { 
	                    $cat_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
	                    $area_category_arr[$key]['category_arr']  = $cat_arr; 
	                }

	                $area_category_arr[$key]['area_id']    = $area['id'];
	                $area_category_arr[$key]['area_name']  = $area['area_name'];
	            }
	        }

	          foreach($area_category_arr as $key=>$area)
	          {

	          	if(isset($area['category_arr']) && count($area['category_arr'])>0)
	          	{	
                   foreach($area['category_arr'] as $key=>$category)
                   {
                       $arr['name']        = $area['area_name'].' '.$category['cat_division_name'];
                       $arr['area_id']     = $area['area_id'];
                       $arr['category_id'] = $category['id'];
                       $arr_data['offers'][]    = $arr;
                   }
                }

                else
                {
                	$arr['name']        = $area['area_name'];
                    $arr['area_id']     = "";
                    $arr['category_id'] = "";
                	$arr_data['offers'][] = $arr;
                }
              } 

             $response['status']      = 'success';
	         $response['message']     = 'Special offers get successfully.';
	         $response['data']        = isset($arr_data)?$arr_data:[];

	         return $response; 


		   }

		 catch(Exception $e){

	   	 $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;

	    }
	}  

	public function rep_center()
	{
		try{

            $arr_data['rep_center'] = []; 
			$arr_area =  RepAreaModel::where('status',1)->orderBy("area_name")->get()->toArray();
			if(isset($arr_area) && count($arr_area)>0)
	        { 
	        	$arr_data['rep_center'][] = 'All Divisions';

	            foreach ($arr_area as $key => $area)
	            {  
	                $category_id_arr = json_decode($area['category_id']);
	               
	                if(isset($category_id_arr) && count($category_id_arr)>0)
	                { 
	                    $cat_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
	                    $area_category_arr[$key]['category_arr']  = $cat_arr; 
	                }

	                $area_category_arr[$key]['area_id']    = $area['id'];
	                $area_category_arr[$key]['area_name']  = $area['area_name'];
	            }
	        }

	          foreach($area_category_arr as $key=>$area)
	          {	
	          	if(isset($area['category_arr']) && count($area['category_arr'])>0)
	          	{	
                   foreach($area['category_arr'] as $key=>$category)
                   {
                       $arr_data['rep_center'][] =  $area['area_name'].' '.$category['cat_division_name'];
                   }
                }

                else
                {
                	$arr_data['rep_center'][]    = $area['area_name'];
                }
              } 

             $response['status']      = 'success';
	         $response['message']     = 'Rep center details get successfully.';
	         $response['data']        = isset($arr_data)?$arr_data:[];

	         return $response; 


		   }

		 catch(Exception $e){

	   	 $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;

	    }
	} 

	public function get_promotions($area_id = null,$category_id = null)
	{
	 try{
              /*get all ppromotions from area */
        $promotions_arr = $representative_details = $vendor_arr = $arr_data = [];

        $current_month = $date = date('Y-m-d');
    
        if($area_id == null && $category_id == null)    
        { 
            $promotions_details = $this->PromotionsModel->with(['get_promotions_offer_details','get_maker_details.shop_store_images','get_promo_code_details','get_user_details'])          
                                    ->whereHas('get_user_details',function($q) 
                                    {  
                                        $q->where('status',1);
                                        $q->where('is_approved',1);
                                    })
                                   ->where('is_active','1')                                   
                                   ->orderBy('created_at','DESC')                                   
                                   ->where('to_date','>=',$current_month)                    
                                   ->get()
                                   ->toArray();

            //dd( $promotions_details->get()->toArray());

            $promotions_arr = $promotions_details;

        }
        else
        {
           
       
            $representative_details = $this->RepresentativeModel->with(['get_rep_vendor.get_user_details'               
                                                                        ,'get_rep_vendor.get_promotions.get_maker_details.shop_store_images'
                                                                        ,'get_rep_vendor.get_promotions.get_promotions_offer_details'
                                                                        ,'get_rep_vendor.get_promotions.get_promo_code_details'
                                                                      ])


                                                        ->with(['get_rep_vendor.get_promotions'=>function($q) use($current_month)
                                                        {  
                                                            return $q->where('to_date','>',$current_month);
                                                        }])
                                                        
                                                        ->where('area_id',$area_id);

                                                        if(isset($category_id) && $category_id!='')
                                                        {
                                                          $representative_details = $representative_details->where('category_id','LIKE','%'.$category_id.'%');
                                                        }

            $representative_details =   $representative_details->get()
                                                               ->toArray();



            if(isset($representative_details) && count($representative_details)>0) 
            {
                foreach($representative_details as $key => $representative)
                {  
                    if(isset($representative['get_rep_vendor']) && count($representative['get_rep_vendor'])>0)
                    {  
                        foreach ($representative['get_rep_vendor'] as $key => $vendor)
                        {
                           
                           $vendor_arr[] = $vendor;
                        }
                        
                    }
                }
            }


           

            if(isset($vendor_arr) && count($vendor_arr)>0)
            {
                foreach($vendor_arr as $key => $vendor)
                {
                    if(isset($vendor['get_promotions']) && count($vendor['get_promotions'])>0)
                    {
                       $promotions_arr = $vendor['get_promotions'];

                       //$promotions_arr = $promotions_arr->orderBy('created_at','DESC');
                    }
                }

            }                                            
        }



         /*get area name from area id*/
          $category_name = "";
          if($area_id == '' && $category_id == '')
          {
             $area_name = "All Offers";
          }
          else
          {
            $area_name     = get_area_name($area_id);
            //$category_name = get_catrgory_name($category_id);
            $category_name = $this->HelperService->get_cat_division($category_id);
          }



          if(isset($promotions_arr) && count($promotions_arr)>0)
          {	
	          foreach($promotions_arr as $key=>$promotions)
	          {
	          	if($promotions['to_date'] >= $date && $promotions['is_active'] == 1)
	          	{
	          		if(isset($promotions['get_maker_details']['shop_store_images']['store_profile_image']) && $promotions['get_maker_details']['shop_store_images']['store_profile_image']!='' &&  file_exists(base_path().'/storage/app/'.$promotions['get_maker_details']['shop_store_images']['store_profile_image']))
	          		{
	          			$image = url('/').'/storage/app/'.$promotions['get_maker_details']['shop_store_images']['store_profile_image'];
	          		}

	          		else
	          		{
	          			$image = url('/').'/assets/images/no-product-img-found.jpg';
	          		}

	          	}

	          	if(isset($promotions['get_promotions_offer_details']) && count($promotions['get_promotions_offer_details'])>0)
	          	{	
                   foreach($promotions['get_promotions_offer_details'] as $key=>$promotions_offer)
                   {
	                    if($promotions_offer['promotion_type_id'] == 1)
	                    {
	                         $orders_of_display[] = 'Orders of $'.$promotions_offer['minimum_ammount']. ' and above receive free shipping';
	                    }	

	                    if($promotions_offer['promotion_type_id'] == 2)
	                    {
	                    	$orders_of_display[] = 'Orders of $'.$promotions_offer['minimum_ammount']. ' and above receive' . $promotions_offer['discount'] .'% off';
	                    }
                   }    
                }  

                $arr_data[$key]['image']   = $image; 

                $arr_data[$key]['orders_of_display'] = $orders_of_display;

                $arr_data[$key]['title']     = isset($promotions['title'])?$promotions['title']:' '.isset($promotions['get_maker_details']['company_name'])?$promotions['get_maker_details']['company_name']:'-';
	          	$arr_data[$key]['valid_upto']= isset($promotions['from_date'])?date('d M Y',strtotime($promotions['from_date'])):''. 'To' . isset($promotions['to_date'])?date('d M Y',strtotime($promotions['to_date'])):''; 
	          	$arr_data[$key]['promo_code_name']= isset($promotions['get_promo_code_details']['promo_code_name'])?$promotions['get_promo_code_details']['promo_code_name']:'';

	          	$arr_data[$key]['description'] =  isset($promotions['description'])?$promotions['description']:'';
	          	$arr_data[$key]['Heading'] = $area_name.' '.$category_name.' Promotions';

	          }
          }	




          $response['status']      = 'success';
	      $response['message']     = 'Promotions get successfully.';
	      $response['data']        = isset($arr_data)?$arr_data:[];

	      return $response; 

		}

		catch(Exception $e){

	   	 $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;
	    } 
	} 

    public function find_rep($area_id = null,$category_id = null,$per_page = null)
    {
    	try{
		        $state_details_arr = $state_id_arr = [];
		       
		        $area_table                  =  $this->RepAreaModel->getTable();
		       
		        $prefix_area_table           = DB::getTablePrefix().$area_table;

		        $representative_table        = $this->RepresentativeModel->getTable();

		        $prefix_representative_table = DB::getTablePrefix().$representative_table;

		        $user_table                  = $this->UserModel->getTable();

		        $prefix_user_table           = DB::getTablePrefix().$user_table;

		        $state_table                 = $this->StateModel->getTable();

		        $prefix_state_table          = DB::getTablePrefix().$state_table;
		    
		        $role_table                  = $this->RoleModel->getTable();

		        $prefix_role_table           = DB::getTablePrefix().$role_table;

		        $role_users_table            = $this->RoleUsersModel->getTable();

		        $prefix_role_users_table     = DB::getTablePrefix().$role_users_table;

		        $sales_manager_table         = $this->SalesManagerModel->getTable();
		        $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

		   
		        $rep_details = DB::table($representative_table)
		                                ->select(DB::raw($prefix_representative_table.".*,".  
		                                                
		                                                 $prefix_state_table.".name,".
		                                                 $prefix_role_table.".name,".
		                                                 $prefix_user_table.".profile_image as profile_image, ".
		                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
		                                                          .$prefix_user_table.".last_name) as user_name"
		                                                )
		                                               )                                
		                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_representative_table.'.user_id')

		                                ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_representative_table.'.user_id')

		                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')

		                                ->leftjoin($prefix_area_table,$prefix_area_table.'.id','=',$prefix_representative_table.'.area_id')

		                                ->leftjoin($prefix_state_table,$prefix_state_table.'.id','=',$prefix_area_table.'.state_id')

		                                ->where($prefix_user_table.'.status',1)
		                                ->where($prefix_user_table.'.is_approved',1);


		                                if($area_id!="")   
		                                {
		                                    if(isset($category_id) && $category_id!='')
		                                    { 
		                                        $rep_details =  $rep_details->where('area_id',$area_id)
		                                                                     ->where($prefix_representative_table.'.category_id', 'LIKE', '%'.$category_id.'%'); 
		                                    }
		                             
		                                    
		                                    $rep_details = $rep_details->where('area_id',$area_id);

		                                   /* $rep_details = $rep_details->paginate(12);

		        

		                                    if(isset($rep_details))
		                                    {
		                                        $arr_rep_pagination   = clone $rep_details;
		                                        $rep_details_arr      = $rep_details->toArray();
		                                    }  */



		                                    /*get all state of that area*/
		                                    $area_obj = $this->RepAreaModel->where('id',$area_id)->first();

		                                    if(isset($area_obj))
		                                    {
		                                        $area_arr          = $area_obj->toArray(); 
		                                        $state_id_arr      = json_decode($area_arr['state_id']);
		                                        $state_details_arr = $this->StateModel->whereIn('id',$state_id_arr)->select('name')->get()->toArray(); 
		                                        foreach($state_details_arr as $state)
		 								        {
		                                           $arr_data['states'][] = $state['name']; 								        	
										        }

		                                    }


		                                    //$arr['state_arr'] = $state_details_arr;
		                                
		                                }
		                                else
		                                { 
		                                   /* $rep_details = $rep_details->paginate(12);

		                                    if(isset($rep_details))
		                                    {
		                                        $arr_rep_pagination   = clone $rep_details;
		                                        $rep_details_arr      = $rep_details->toArray();
		                                    } 
		                                   
		                                   */
		                                   // $this->arr_view_data['rep_details_arr']             = $rep_details_arr;

		                                }

		  $per_page        = isset($per_page)?$per_page:10; 
		  $rep_details     = $rep_details->paginate($per_page);         
		  $rep_details_arr = $rep_details->toArray(); 
		  $arr_data['representatives'] = [];



		 if(isset($rep_details_arr['data']) && !empty($rep_details_arr['data']))
		 {  
			  foreach($rep_details_arr['data'] as $key => $val)
			  {

		         $arr['name']  = $val->user_name;
			  	 $arr['image'] = imagePath($val->profile_image, 'user', 0);
			  	 $arr['area']  = get_area_name($val->area_id);
			  	 $arr_data['representatives'][] = $arr;
			  }  
		 }                    
		                                 
		   
		        if(isset($area_id) && $area_id!="")
		        {
		            $sales_manager_details = DB::table($sales_manager_table)
		                                ->select(DB::raw($prefix_sales_manager_table.".*,". 
		                                                 $prefix_role_table.".name,". 
		                                                
		                                                 $prefix_user_table.".profile_image as profile_image, ".
		                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
		                                                          .$prefix_user_table.".last_name) as user_name"))
		                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_sales_manager_table.'.user_id')
		                                ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_sales_manager_table.'.user_id')
		                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')
		                                ->leftjoin($prefix_area_table,$prefix_area_table.'.id','=',$prefix_sales_manager_table.'.area_id')

		                                ->where($prefix_user_table.'.status',1)
		                                ->where($prefix_user_table.'.is_approved',1)
		                                ->where('area_id',$area_id)

		                                ->where($prefix_user_table.'.status',1);

		                                if (isset($category_id) && $category_id != "") 
		                                {
		                                    $sales_manager_details = $sales_manager_details->where($sales_manager_table.'.category_id',$category_id);
		                                }
		                                
		                                $sales_manager_details = $sales_manager_details->where($prefix_user_table.'.is_approved',1)
		                                ->where('area_id',$area_id)
		                                ->first();
		         }

		        /*get category name and area name from there id*/
		        if($area_id!="" || $category_id!="")
		        {
		            $category_name = $this->HelperService->get_cat_division($category_id);


		            $area_name     = get_area_name($area_id);

		            $area_type     = get_area_type($area_id);
		             

		            $arr['category_name']   = $category_name;
		           // $arr['area_name']       = $area_name;
		            $arr['area_type']       = $area_type;


		        }

		        /* get all area details*/
		        $area_details = [];
		        $area_details = $this->RepAreaModel->where('status',1)
		                                           ->orderBy('area_name')
		                                           ->select('area_name')
		                                           ->get()
		                                           ->toArray();  

		        foreach($area_details as $area)
		        {
		        	$arr_data['divisions'][] = $area['area_name'];
		        }                                     

		        $sales_img                = imagePath($sales_manager_details->profile_image, 'user', 0);

		        $area_name                = isset($sales_manager_details->area_id)?get_area_name($sales_manager_details->area_id):'';

		        $arr['image']              = $sales_img;
		        $arr['area_name']          = $area_name;
		        $arr['designation']        = $sales_manager_details->name.', '.$area_name;
		        $arr['name']               = $sales_manager_details->user_name;
		        $arr_data['sales_manager_details'] = $arr;



		        /*get all active retailers*/

		        $retailer_arr = $country_arr = [];
		  

		        $user_table =  $this->UserModel->getTable();
		        $prefix_user_table = DB::getTablePrefix().$user_table;

		        $role_table =  $this->RoleModel->getTable();
		        $prefix_role_table = DB::getTablePrefix().$role_table;

		        $role_user_table =  $this->RoleUsersModel->getTable();
		        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;


		        $maker_table                 = $this->MakerModel->getTable();
		        $prefix_maker_table          = DB::getTablePrefix().$maker_table;
		       
		        $obj_user = DB::table($user_table)
		                        ->select(DB::raw($prefix_user_table.".id as id,".
		                                    $prefix_maker_table.".company_name,".
		                                     
		                                    "CONCAT(".$prefix_user_table.".first_name,' ',"
		                                             .$prefix_user_table.".last_name) as user_name"
		                                    ))

		                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
		                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

		                        ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_user_table.'.id')
		                       
		                        ->where($role_table.'.slug','=','maker')
		                        ->where($user_table.'.id','!=',1)
		                        ->where($user_table.'.status','=',1)
		                        ->where($user_table.'.is_approved','=',1)
		                        ->whereNull($user_table.'.deleted_at')
		                        ->orderBy($prefix_maker_table.".company_name");

		        $vendor_arr = $obj_user->get()->toArray();

		        $arr_data['pagination']["current_page"]    = $rep_details_arr['current_page'];
		        $arr_data['pagination']["first_page_url"]  = $rep_details_arr['first_page_url'];
		        $arr_data['pagination']["from"]            = $rep_details_arr['from'];
		        $arr_data['pagination']["last_page"]       = $rep_details_arr['last_page'];
		        $arr_data['pagination']["last_page_url"]   = $rep_details_arr['last_page_url'];
		        $arr_data['pagination']["next_page_url"]   = $rep_details_arr['next_page_url'];
		        $arr_data['pagination']["path"]            = $rep_details_arr['path'];
		        $arr_data['pagination']["per_page"]        = $rep_details_arr['per_page'];
		        $arr_data['pagination']["prev_page_url"]   = $rep_details_arr['prev_page_url'];
		        $arr_data['pagination']["to"]              = $rep_details_arr['to'];
		        $arr_data['pagination']["total"]           = $rep_details_arr['total'];


		        $arr_data['vendors'] = $vendor_arr; 


		        $response['status']      = 'success';
		        $response['message']     = 'Rep center details get successfully.';
		        $response['data']        = isset($arr_data)?$arr_data:[];

		        return $response; 
      
    	}

    	catch(Exception $e){
	   	 $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

        return $response;
	    } 
    }

    public function get_area_wise_vendors($area_id=null,$category_id=null,$per_page=null)
    {
       try
       {
		       $state_details_arr = $state_id_arr = [];
		       $per_page          = isset($per_page)?$per_page:8;

		       //get all rep from area and category
		        
		       $representative_table            = $this->RepresentativeModel->getTable();
		       $prefix_representative_table     = DB::getTablePrefix().$representative_table;
		       
		       $vendor_rep_mapping_table        = $this->VendorRepresentativeMappingModel->getTable();
		       $prefix_vendor_rep_mapping_table = DB::getTablePrefix().$vendor_rep_mapping_table;


		       $vendor_table                    = $this->MakerModel->getTable();
		       $prefix_vendor_table             = DB::getTablePrefix().$vendor_table;

		       $user_table                      = $this->UserModel->getTable();
		       $prefix_user_table               = DB::getTablePrefix().$user_table;

		       $vendor_store_image_table        = $this->ShopImagesModel->getTable();
		       $prefix_vendor_store_image_table = DB::getTablePrefix().$vendor_store_image_table;


		       $vendor_rep_details_arr = DB::table($representative_table)
		                                    ->select(DB::raw($prefix_representative_table.".*,".  
		                                                 
		                                            $prefix_vendor_table.".brand_name, ".
		                                            $prefix_vendor_table.".company_name, ".
		                                            $prefix_vendor_table.".user_id as uid,".

		                                            $prefix_vendor_store_image_table.".maker_id,".
		                                            $prefix_vendor_store_image_table.".store_profile_image,".

		                                            $prefix_user_table.'.id as uid'

		                                    ))

		                                 
		                                    ->join($prefix_vendor_rep_mapping_table,$prefix_vendor_rep_mapping_table.'.representative_id','=',$representative_table.'.user_id')

		                                    ->join($prefix_vendor_table,$prefix_vendor_table.'.user_id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

		                                    ->join($prefix_vendor_store_image_table,$prefix_vendor_store_image_table.'.maker_id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

		                                    ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

		                                    ->where($prefix_user_table.'.status',1)
		                                    ->where($prefix_user_table.'.is_approved',1);

		                                    if(isset($category_id) && $category_id!='')
		                                    { 
		                                        $vendor_rep_details_arr =  $vendor_rep_details_arr->where($prefix_representative_table.'.category_id', 'like', '%'.$category_id.'%');

		                                                                     
		                                    }

		        $vendor_rep_details_arr = $vendor_rep_details_arr->groupBy('maker_id')
		                                  ->where($prefix_representative_table.'.area_id',$area_id);

		        $vendor_rep_details_arr = $vendor_rep_details_arr->paginate($per_page);
		        $vendor_rep_details_arr = $vendor_rep_details_arr->toArray();

		        if(isset($vendor_rep_details_arr['data']) && !empty($vendor_rep_details_arr['data']))
		        {
		        	foreach($vendor_rep_details_arr['data'] as $key => $val)
		        	{
		        		$arr_data['vendors'][$key]['name']  = $val->company_name;
		        		$arr_data['vendors'][$key]['image'] = url('/').'/storage/app/'.$val->store_profile_image;
		        	}
		        }
		 
		    
		    if(isset($area_id) && $area_id!='')
		    {
		        $area_obj = $this->RepAreaModel->where('id',$area_id)->first();

		        if(isset($area_obj))
		        {
		            $area_arr = $area_obj->toArray(); 
		            $state_id_arr      = json_decode($area_arr['state_id']);
		        }


		        $area_type = get_area_type($area_id);
		           
		        $state_details_arr = $this->StateModel->whereIn('id',$state_id_arr)->select('name')->get()->toArray(); 

		        if(isset($state_details_arr) && !empty($state_details_arr))
		        {
		        	foreach($state_details_arr as $states)
		        	{
		        		$arr_data['states'][] = $states['name'];
		        	}
		        }

		        $arr_data['area_type']       = $area_type;
		   
		 
		    }
		      
		    /*get category name and area name from there id*/
		    if($area_id!="" && $category_id!="")
		    {
		        $category_name = $this->HelperService->get_cat_division($category_id);
		    
		        $area_name     = get_area_name($area_id);

		       
		        $arr_data['category_id']      = $category_id;
		        $arr_data['category_name']    = $category_name;
		        $arr_data['area_name']        = $area_name;
		    } 

		        $arr_data['pagination']["current_page"]    = $vendor_rep_details_arr['current_page'];
		        $arr_data['pagination']["first_page_url"]  = $vendor_rep_details_arr['first_page_url'];
		        $arr_data['pagination']["from"]            = $vendor_rep_details_arr['from'];
		        $arr_data['pagination']["last_page"]       = $vendor_rep_details_arr['last_page'];
		        $arr_data['pagination']["last_page_url"]   = $vendor_rep_details_arr['last_page_url'];
		        $arr_data['pagination']["next_page_url"]   = $vendor_rep_details_arr['next_page_url'];
		        $arr_data['pagination']["path"]            = $vendor_rep_details_arr['path'];
		        $arr_data['pagination']["per_page"]        = $vendor_rep_details_arr['per_page'];
		        $arr_data['pagination']["prev_page_url"]   = $vendor_rep_details_arr['prev_page_url'];
		        $arr_data['pagination']["to"]              = $vendor_rep_details_arr['to'];
		        $arr_data['pagination']["total"]           = $vendor_rep_details_arr['total'];

		        $response['status']      = 'success';
		        $response['message']     = 'Vendors get successfully.';
		        $response['data']        = isset($arr_data)?$arr_data:[];

		        return $response; 

		     }

		     catch(Exception $e){
			   	 $response = [
						'status'  => 'failure',
						'message' => 'Something went wrong.',
						'data'    => ''
					];

		      return $response;
		     } 
    }  


    public function sales_manager_details($sales_manager_id=null)
    {
    	try
    	{
	        $sales_manager_arr     = $area_category_arr = []; 

	        $sales_manager_details = $this->SalesManagerModel->where('user_id',$sales_manager_id)->with(['get_user_data'=>function($user_data){$user_data->select('id','first_name','last_name','email','contact_no','profile_image');}])->with('areas_details')->get()->toArray();


	        if(isset($sales_manager_details) && count($sales_manager_details)>0)
	        { 

	            foreach ($sales_manager_details as $key => $areas)
	            { 
	                if (isset($areas['area_id'])) {

	                    if(isset($areas['areas_details']) && count($areas['areas_details'])>0)
	                    { 
	                        $arr['area_id']   =   $areas['area_id'];
	                        $arr['area_name'] = $areas['areas_details'][0]['area_name'];
	                        $arr_data['areas'][] = $arr; 
	                    }
	                    
	  
	                }
	                if (isset($areas['category_id'])) {
	                    $division_category = $this->CategoryDivisionModel->where('id' , $areas['category_id'])->pluck('cat_division_name')->first();

	                    $arr['category_name'] = isset($division_category)?$division_category:'';
	                    $arr['category_id']   = $areas['category_id'];
	                    $arr_data['categories'][] = $arr; 
	                }
 	            }  

	            $arr_data['sales_manager_details']                 = $sales_manager_details[0]['get_user_data'];
	            $arr_data['sales_manager_details']['description']  = $sales_manager_details[0]['description'];
	        }

	         $response['status']      = 'success';
		     $response['message']     = 'Sales manager details get successfully.';
		     $response['data']        = isset($arr_data)?$arr_data:[];

		     return $response; 
        }  

        catch(Exception $e){
			   	 $response = [
						'status'  => 'failure',
						'message' => 'Something went wrong.',
						'data'    => ''
					];

		      return $response;
		} 
        
    }

    public function rep_details($rep_id=null,$per_page=null)
    {
       try
       {
       	    $per_page   = isset($per_page)?$per_page:'';

	        $representative_table = $this->RepresentativeModel->getTable();

	        $prefix_representative_table = DB::getTablePrefix().$representative_table;

	      
	        $user_table =$this->UserModel->getTable();

	        $prefix_user_table = DB::getTablePrefix().$user_table;

	        $role_table = $this->RoleModel->getTable();

	        $prefix_role_table = DB::getTablePrefix().$role_table;

	        $role_users_table = $this->RoleUsersModel->getTable();

	        $prefix_role_users_table = DB::getTablePrefix().$role_users_table;

	        $representative_details = DB::table($representative_table)
	                                ->select(DB::raw($prefix_representative_table.".*,".  
	                                                 $prefix_role_table.".name,".
	                                                 $prefix_user_table.".profile_image as profile_image, ".
	                                                 $prefix_user_table.".contact_no, ".
	                                                 $prefix_user_table.".email, ".
	                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
	                                                          .$prefix_user_table.".last_name) as user_name"))
	                                ->leftjoin($prefix_user_table,$prefix_user_table.'.id','=',$prefix_representative_table.'.user_id')
	                                 ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_representative_table.'.user_id')
	                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')
	                                ->where($prefix_representative_table.'.user_id',$rep_id)
	                                ->where($prefix_user_table.'.status',1)
	                                ->first();


	        $vendor_details = $this->VendorRepresentativeMappingModel->where('representative_id',$rep_id)
	                                                            ->with(['get_maker_details.shop_store_images','get_user_details'])

	                                                            ->whereHas('get_user_details',function($q){
	                                                                    $q->where('status',1);
	                                                                    $q->where('is_approved',1);
	                                                            });

	        $vendor_details = $vendor_details->paginate($per_page);
	        $vendor_details = $vendor_details->toArray();


	        if(isset($vendor_details['data']) && !empty($vendor_details['data']))
	        {
                foreach($vendor_details['data'] as $key => $val)
                {
                	$arr_data['vendors'][$key]['vendor_id']           = $val['vendor_id'];  
                	$arr_data['vendors'][$key]['company_name']        = $val['get_maker_details']['company_name'];

                	if(isset($val['get_maker_details']['shop_store_images']['store_profile_image']) && $val['get_maker_details']['shop_store_images']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$val['get_maker_details']['shop_store_images']['store_profile_image']))
                	{
                       $arr_data['vendors'][$key]['store_profile_image'] = url('/').'/storage/app/'.$val['get_maker_details']['shop_store_images']['store_profile_image'];
                	} 

                	else
                	{
                	   $arr_data['vendors'][$key]['store_profile_image'] = url('/assets/images/no-product-img-found.jpg');
                	}	


                    if(isset($val['get_maker_details']['shop_store_images']['store_cover_image']) && $val['get_maker_details']['shop_store_images']['store_cover_image']!='' && file_exists(base_path().'/storage/app/'.$val['get_maker_details']['shop_store_images']['store_cover_image']))
                	{
                	  $arr_data['vendors'][$key]['store_cover_image']   = url('/').'/storage/app/'.$val['get_maker_details']['shop_store_images']['store_cover_image'];
                    }

                    else
                	{
                	   $arr_data['vendors'][$key]['store_cover_image']  = url('/assets/images/no-product-img-found.jpg');
                	}	
                	
                }
	        }

	        if(isset($representative_details->area_id) && $representative_details->area_id!='')
	        {
	           $area_type = $this->RepAreaModel->where('id',$representative_details->area_id)->pluck('area_type')->first();
	           $area_name = get_area_name($representative_details->area_id);
	        }


          
	        $arr['area_type']     = isset($area_type)?$area_type:'';
	        $arr['area_name']     = isset($area_name)?$area_name:'';
	        $arr['name']          = $representative_details->user_name;
	        $arr['designation']   = $representative_details->name;
	        $arr['email']         = $representative_details->email;
	        $arr['contact_no']    = $representative_details->contact_no;
	        $arr['profile_image'] = imagePath($representative_details->profile_image,'user',0);

	        $arr_data['representative_details'] = $arr;

	        $arr_data['pagination']["current_page"]    = $vendor_details['current_page'];
	        $arr_data['pagination']["first_page_url"]  = $vendor_details['first_page_url'];
	        $arr_data['pagination']["from"]            = $vendor_details['from'];
	        $arr_data['pagination']["last_page"]       = $vendor_details['last_page'];
	        $arr_data['pagination']["last_page_url"]   = $vendor_details['last_page_url'];
	        $arr_data['pagination']["next_page_url"]   = $vendor_details['next_page_url'];
	        $arr_data['pagination']["path"]            = $vendor_details['path'];
	        $arr_data['pagination']["per_page"]        = $vendor_details['per_page'];
	        $arr_data['pagination']["prev_page_url"]   = $vendor_details['prev_page_url'];
	        $arr_data['pagination']["to"]              = $vendor_details['to'];
	        $arr_data['pagination']["total"]           = $vendor_details['total'];

	         $response['status']      = 'success';
		     $response['message']     = 'Representative details get successfully.';
		     $response['data']        = isset($arr_data)?$arr_data:[];

		     return $response;      
	   
      }

	    catch(Exception $e){
			   	 $response = [
						'status'  => 'failure',
						'message' => 'Something went wrong.',
						'data'    => ''
					];

		   return $response;
		} 
         
    }

    public function add_to_favorite($form_data=null)
   {
   	  try
   	  {
		      $data    = [];
		      $user_id = 0;

		      $user = $form_data['auth_user'];
		      if($user)
		      {
		        $user_id = $user->id;
		      }

		      $id   = $form_data['id'];
		      $type = $form_data['type'];

		      /*check duplication*/

		      if($type == 'maker')
		      {
		         $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('maker_id',$id)->count();
		         if($count > 0)
		         {
		             $response['status']      = 'failure';
		             $response['message']     = 'Vendor is already added into the favorite list.';
		             $response['data']        = '';
		             return $response;
		         }
		      }
		      else if($type == 'product')
		      {
		          $count = $this->FavoriteModel->where('retailer_id',$user_id)->where('product_id',$id)->count(); 
		          if($count > 0)
		          {
		             $response['status']      = 'failure';
		             $response['message']     = 'Product is already added into the favorite list.';
		             $response['data']        = '';
		             return $response;
		          }
		      }


		      if($type == 'maker')
		      {
		         $data['retailer_id'] = $user_id;
		         $data['maker_id']    = $id;
		         $data['product_id']  = 0;
		         $data['type']        = 'maker';

		         $result = $this->FavoriteModel->create($data);
		      }
		      else if($type == 'product')
		      {
		         $data['retailer_id'] = $user_id;
		         $data['maker_id']    = 0;
		         $data['product_id']  = $id;
		         $data['type']        = 'product';
		         
		         $result = $this->FavoriteModel->create($data);
		      }

		      if($result)
		      {  
		         if($type == 'maker')
		         {
		            $response['status']      = 'success';
		            $response['message']     = 'Vendor added to favorite list.'; 
		            $response['data']        = '';
		            return $response;
		         }
		         else if($type == 'product')
		         {
		            $response['status']      = 'success';
		            $response['message']     = 'Product added to favorite list.';
		            $response['data']        = '';
		            return $response;
		         }
		         
		      }
		      else
		      {
		            if($type == 'maker')
		              {
		                 $response['status']      = 'failure';
		                 $response['message']     = 'Error occurred while adding vendor into the favorite list.'; 
		                 $response['data']        = '';
		                 return $response;
		              }
		              else if($type == 'product')
		              {
		                $response['status']      = 'failure';
		                $response['message']     = 'Error occurred while adding product into the favorite list.';
		                $response['data']        = '';
		                 return $response;
		              }
		             
		      }
    }
    
     catch(Exception $e){
			   	 $response = [
						'status'  => 'failure',
						'message' => 'Something went wrong.',
						'data'    => ''
					];

		   return $response;
		}   
  
   }



  public function remove_from_favorite($form_data=null)
  {
        try
        {
		        $user_id = 0;

		        $user = $form_data['auth_user'];
		       
		        if(isset($user))
		        {
		           $user_id = $user->id; 
		        }

		        $id     = $form_data['id'];
		        $type   = $form_data['type'];

		        
		        if($type == 'maker')
		        {
		           $result = $this->FavoriteModel->where('retailer_id',$user_id)->where('maker_id',$id)->delete();
		        }
		        else if($type == 'product')
		        {
		           $result = $this->FavoriteModel->where('retailer_id',$user_id)->where('product_id',$id)->delete();
		        }
		        

		        if($result)
		        { 
		              if($type == 'maker')
		              {
		                 $response['status']      = 'success';
		                 $response['message']     = 'Vendor has been removed from favorite list.'; 
		                 $response['data']        = ''; 
		                 return $response;
		              }
		              else if($type == 'product')
		              {
		                $response['status']      = 'success';
		                $response['message']     = 'Product has been removed from favorite list.';
		                $response['data']        = ''; 
		                return $response;
		              }
		             
		        }
		        else
		        {
		              if($type == 'maker')
		              {
		                 $response['status']      = 'failure';
		                 $response['message']     = 'Error occurred while removing vendor from favorite list.'; 
		                 $response['data']        = ''; 
		                 return $response;
		              }
		              else if($type == 'product')
		              {
		                $response['status']      = 'failure';
		                $response['message']     = 'Error occurred while removing product from favorite list.';
		                $response['data']        = ''; 
		                 return $response;
		              }
		             
		        }
      } 
      
      catch(Exception $e){
			   	 $response = [
						'status'  => 'failure',
						'message' => 'Something went wrong.',
						'data'    => ''
					];

		   return $response;
		}    
  }

  public function my_favorite($user_id=null,$type=null)
  {
   try{

  	 	if(isset($user_id) && !empty($user_id) && isset($type) && !empty($type))
  	 	{
  	 		      if($type=='product')
  	 		      {	
				      $obj_product_data =  $this->FavoriteModel
				                                ->where('retailer_id',$user_id)
				                                ->with(['productDetails'=>function($query) 
				                                {
				                                  $query->select('id','user_id','product_name','brand','product_image','product_image_thumb','unit_wholsale_price','retail_price','is_active','product_status','product_complete_status');

				                                  $query->where('is_active',1);
				                                  $query->where('product_status',1);
				                                  $query->where('product_complete_status',4);

				                                },'productDetails.brand_details'=>function($query1)
				                                {
				                                  $query1->select('id','user_id','brand_name','is_active');
				                                  $query1->where('is_active',1);

				                                }])
				                                ->where('type','product')
				                                ->paginate(8);

				      if($obj_product_data)
				      {
				        $arr_product_data           = $obj_product_data->toArray();
				        if(isset($arr_product_data['data']) && !empty($arr_product_data['data']))
				        {
				        	foreach($arr_product_data['data'] as $key => $val)
				        	{
				        	   $arr_data['products'][$key]['product_id']    = isset($val['product_details']['id'])?$val['product_details']['id']:'';
				        	   $arr_data['products'][$key]['product_name']  = isset($val['product_details']['product_name'])?$val['product_details']['product_name']:'';
				        	   $arr_data['products'][$key]['product_image'] = isset($val['product_details']['product_image'])?imagePath($val['product_details']['product_image'],'product',0):'';
				        	   $arr_data['products'][$key]['wholesale_price'] = isset($val['product_details']['unit_wholsale_price'])?$val['product_details']['unit_wholsale_price']:'';
				        	   $arr_data['products'][$key]['retail_price']  = isset($val['product_details']['retail_price'])?$val['product_details']['retail_price']:'';
				        	   $arr_data['products'][$key]['brand_name']    = isset($val['product_details']['brand_details']['brand_name'])?$val['product_details']['brand_details']['brand_name']:'';
				        	   $arr_data['products'][$key]['vendor_id']     = isset($val['product_details']['user_id'])?$val['product_details']['user_id']:'';
				        	   $maker_details = get_maker_all_details($val['product_details']['user_id']); 
				        	   $arr_data['products'][$key]['is_get_a_quote']= isset($maker_details['is_get_a_quote'])?$maker_details['is_get_a_quote']:''; 
				        	   $arr_data['products'][$key]['is_add_to_cart']= isset($maker_details['is_add_to_bag'])?$maker_details['is_add_to_bag']:''; 
				        	}

				        	$arr_data['pagination']['last_page']            = $arr_product_data['last_page'];
				        	$arr_data['pagination']['total']                = $arr_product_data['total'];
				        }

				        else
				        {
				        	$arr_data = []; 
				        }

				      } 

			     } 
			      

                  if($type=='vendor')
                 { 	
				      $obj_maker_data = $this->FavoriteModel->where('retailer_id',$user_id)

				                                            ->with(['makerDetails'=>function($query)
				                                            {
				                                                $query->select('id','user_id','company_name');
				                                            },'store_image_details'=>function($query1){
				                                                $query1->select('id','maker_id','store_profile_image');
				                                            },'makerDetails.user_details'=>function($q1){
				                                                $q1->where('status',1);
				                                                $q1->where('is_approved',1);
				                                            }

				                                            ])
				                                            ->where('type','maker')
				                                            ->paginate(8);

				      if($obj_maker_data)
				      {
				         $arr_maker_data        = $obj_maker_data->toArray();
				         if(isset($arr_maker_data['data']) && !empty($arr_maker_data['data']))
				         {
                              foreach($arr_maker_data['data'] as $key => $val)
                              {
                                 $arr_data['maker_data'][$key]['vendor_id']    = isset($val['maker_details']['maker_id'])?strtoupper($val['maker_details']['maker_id']):'N/A';
                                 $arr_data['maker_data'][$key]['company_name'] = isset($val['maker_details']['company_name'])?strtoupper($val['maker_details']['company_name']):'N/A';
                                 if(isset($val['store_image_details']['store_profile_image']) && $val['store_image_details']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$val['store_image_details']['store_profile_image']))
                                {
                                  $shop_img = url('/storage/app/'.$val['store_image_details']['store_profile_image']);
                                }
                                else
                                {                  
                                  $shop_img = url('/assets/images/no-product-img-found.jpg');
                                }

                                $arr_data['maker_data'][$key]['store_image']   = isset($shop_img)?$shop_img:'';
                              }

                            $arr_data['pagination']['last_page']            = $arr_maker_data['last_page'];
				        	$arr_data['pagination']['total']                = $arr_maker_data['total'];
				         }

				         else
				         {
				         	$arr_data = [];
				         }
				      }
			     } 

			    $response = [
					'status'  => 'success',
					'message' => 'Favorite data get successfully.',
					'data'    => isset($arr_data)?$arr_data:[]
				];

	             return $response;
			      
			      /*-----------------------------------------------------------------------------*/

	/*		      $favorite_arr['product']  = $arr_product_data;
			      $favorite_arr['maker']    = $arr_maker_data;


			      $this->arr_view_data['arr_maker_pagination']     = $arr_maker_pagination;
			      $this->arr_view_data['arr_product_pagination']   = $arr_product_pagination;
			      $this->arr_view_data['favorite_arr']             = $favorite_arr;
			      $this->arr_view_data['page_title']               = 'My Favorites';


			      return view('retailer.favorites.my_favorite',$this->arr_view_data); */
  	 	}

  	 }

	  catch(Exception $e){
		   	 $response = [
					'status'  => 'failure',
					'message' => 'Something went wrong.',
					'data'    => ''
				];

	   return $response;
	}    

  }



   

}

?>