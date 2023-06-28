<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\NotificationsModel;
use App\Models\UserLastActiveModel;
use App\Models\ProductInventoryModel;
use App\Models\ThirdSubCategoryModel;
use App\Models\FourthSubCategoryModel;
use App\Models\VisitorsEnquiryModel;

use Mail;

Use Sentinel;
class CommonController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 27 June 2019
    */

    public function __construct(CategoryModel $CategoryModel,
     							 SubCategoryModel $SubCategoryModel,
                                 NotificationsModel $NotificationsModel,
                                 VisitorsEnquiryModel $VisitorsEnquiryModel,
                                 UserLastActiveModel $UserLastActiveModel,
                                 ProductInventoryModel $ProductInventoryModel,
                                 ThirdSubCategoryModel $ThirdSubCategoryModel,
                                 FourthSubCategoryModel $FourthSubCategoryModel
                             )
    {
        $this->locale = \App::getLocale();               
    	$this->SubCategoryModel   = $SubCategoryModel;
    	$this->CategoryModel      = $CategoryModel;
        $this->NotificationsModel = $NotificationsModel;
        $this->VisitorsEnquiryModel = $VisitorsEnquiryModel;
        $this->UserLastActiveModel= $UserLastActiveModel;
        $this->ProductInventoryModel = $ProductInventoryModel;
        $this->ThirdSubCategoryModel = $ThirdSubCategoryModel;
        $this->FourthSubCategoryModel = $FourthSubCategoryModel;

    }

    public function get_sub_categories($category_id = 0)
    {
    	$sub_categories_arr = $this->SubCategoryModel->where('is_active',1)
    						->where('category_id',$category_id)
    						->whereTranslation('locale',$this->locale)
    						->get()->toArray();

    	$response['sub_categories_arr'] = $sub_categories_arr;
        
    	if($sub_categories_arr)
    	{
    		$response['status'] = 'SUCCESS';    	
    	}
    	else
    	{
    		$response['status'] = 'FAILURE';    		
    	}
    	
    	return response()->json($response);
    }

    public function get_third_sub_categories($sub_category_id = 0)
    {
        $third_sub_categories_arr = [];

        $arr_sub_category_id = explode(",",$sub_category_id);
        if($arr_sub_category_id != "" && $arr_sub_category_id != null)
        {
            foreach($arr_sub_category_id as $sub_cat_id)
            {
                $third_sub_categories_arr[] = $this->ThirdSubCategoryModel->where('is_active',1)
                                                ->where('sub_category_id',$sub_cat_id)
                                                ->whereTranslation('locale',$this->locale)
                                                ->get()
                                                ->toArray();
                                              
            }
        }
        
        $response['third_sub_categories_arr'] = $third_sub_categories_arr;            
    	if($third_sub_categories_arr)
    	{
    		$response['status'] = 'SUCCESS';    	
    	}
    	else
    	{
    		$response['status'] = 'FAILURE';    		
    	}
    	
    	return response()->json($response);
    }

    public function get_fourth_sub_categories($sub_category_id = 0)
    {
        $fourth_sub_categories_arr = [];
        $arr_sub_category_id = explode(",",$sub_category_id);
        
        if($arr_sub_category_id != "" && $arr_sub_category_id != null)
        {
            foreach($arr_sub_category_id as $sub_cat_id)
            {
                $fourth_sub_categories_arr[] = $this->FourthSubCategoryModel->where('is_active',1)
                                                ->where('third_sub_category_id',$sub_cat_id)
                                                ->whereTranslation('locale',$this->locale)
                                                ->get()->toArray();
            }
        }
    	$response['fourth_sub_categories_arr'] = $fourth_sub_categories_arr;
        
    	if($fourth_sub_categories_arr)
    	{
    		$response['status'] = 'SUCCESS';    	
    	}
    	else
    	{
    		$response['status'] = 'FAILURE';    		
    	}
    	
    	return response()->json($response);
    }

    public function notifications()
    {   
        $response=[];

        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
            $arr_data = [];
            $obj_data = $this->NotificationsModel->orderBy('id','DESC')
                                   /* ->where('type','representative')*/
                                    ->where('status','0')
                                    ->where('to_user_id',$loggedInUserId)
                                    ->get();

            if($obj_data)
            {
                $arr_data    = $obj_data->toArray();
              
                $ids         = array_column($arr_data, 'id');

                $update_data = $this->NotificationsModel->whereIn('id',$ids)->update(['status'=>'1']);  
            }                        
            
            $response['logo']     = url('/').config('app.project.img_path.notification_logo').'/notification_logo.png';        
            $response['arr_data'] = $arr_data;                                
            $response['status']   = 'SUCCESS'; 
        }
        else
        {
            $response['status'] = 'FAILURE';  
        }

        return response()->json($response);   
    }

    public function update_user_active_time()
    {
        // $current_date_time = date('d-m-y H:i:s');
        $current_date_time = date('Y-m-d H:i:s');
        
        $response = [];

        $loggedInUserId = 0;

        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
        else
        {
            $response['status'] = 'failure';                  
            return response()->json($response);         
        }

        $data_arr['last_active_time'] = $current_date_time;
        $data_arr['user_id']          = $loggedInUserId;

        $is_exist = $this->UserLastActiveModel->where('user_id',$loggedInUserId)->count()>0;

        if($is_exist)
        {
            $is_update = $this->UserLastActiveModel->where('user_id',$loggedInUserId)->update($data_arr);

            if($is_update)
            {
                $response['status'] = 'success';    
            }
            else
            {
                $response['status'] = 'failure';       
            }            
        }
        else
        {
            $is_created = $this->UserLastActiveModel->create($data_arr);    

            if($is_created)
            {
                $response['status'] = 'success';    
            }
            else
            {
                $response['status'] = 'failure';       
            }
        }

        return response()->json($response);        
    }


    /*product quantity check mail- Vishal*/
    public function check_product_inventory()
    {
        $arr_prod_inventory = $this->ProductInventoryModel
                                    ->where('quantity',0)
                                    ->get(['product_id','user_id','sku_no','quantity'])
                                    ->toArray();                                    
        if(isset($arr_prod_inventory) && count($arr_prod_inventory) > 0)
        {

            $arr_email = ['vishal.vetal@rwaltz.com',                            
                            'bhushan.pagar@rwaltz.com',
                            'abhijeet.bhosale@rwaltz.com',
                            'akshay.nair@rwaltz.com',
                            'bhavana.shirude@rwaltz.com',
                            'jaydip.bachhav@rwaltz.com',
                            'mona.bapte@rwaltz.com',
                            'nitish.kasar@rwaltz.com',
                            'pravin.thakare@rwaltz.com',
                            'priyanka.kedare@rwaltz.com',                           
                            'shital.more@rwaltz.com'
                        ];  

            $content = json_encode($arr_prod_inventory);
        
            $to_mail_id = $arr_email;
    
            $arr_site_setting = get_site_settings(['site_name','website_url']);
            $send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id)
            {

                $message->from('admin@justgot2haveit.com',$arr_site_setting['site_name']. ' Admin');
                $message->to($to_mail_id)                       
                ->subject('Product Inventory Reminder (Following Product Quantity is 0)')
                ->setBody($content, 'text/html');
            });

            dd('yes');
                
            /*end*/
        }
        dd('no');
    }
    /*end*/

    /*product quantity check mail- Vishal*/
    public function test_email()
    {
                                         
       
           /* $arr_email = ['vishal.vetal@rwaltz.com',                            
                            'bhushan.pagar@rwaltz.com',
                            'abhijeet.bhosale@rwaltz.com',
                            'akshay.nair@rwaltz.com',
                            'bhavana.shirude@rwaltz.com',
                            'jaydip.bachhav@rwaltz.com',
                            'mona.bapte@rwaltz.com',
                            'nitish.kasar@rwaltz.com',
                            'pravin.thakare@rwaltz.com',
                            'priyanka.kedare@rwaltz.com',                           
                            'shital.more@rwaltz.com'
                        ];  */

            $arr_email = ['amit.rwaltzsoftware@gmail.com',
                          'nitish.rwaltzsoftware@gmail.com'
                        ];  

            $content = "Hello this is a test email";
        
            $to_mail_id = $arr_email;
            $send_mail = Mail::send(array(),array(), function($message) use($content,$to_mail_id)
            {
                $message->from('admin@justgot2haveit.com', $project_name.' Admin');
                $message->to($to_mail_id)                       
                ->subject('Test email')
                ->setBody($content, 'text/html');
            });

            dd('yes');
                
            /*end*/
        
    }
    /*end*/

}
