<?php


namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RetailerStoreDetailsModel;
use App\Models\RetailerModel;
use App\Models\CountryModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;

use App\Common\Services\GeneralService;

use Validator;
use Flash;
use Sentinel;
use Hash;
class AccountSettingsController extends Controller
{
    /* 
	|  Author : Shital Vijay More
	|  Date   : 1 July 2019
	*/
	public function __construct(UserModel $user,
                                RetailerStoreDetailsModel $RetailerStoreDetailsModel,
                                RetailerModel $RetailerModel,
                                CountryModel $CountryModel,
                                GeneralService $GeneralService,
                                CategoryModel $CategoryModel,
                                CategoryTranslationModel $CategoryTranslationModel
                                )
    {
        $this->locale = \App::getLocale(); 
    	$this->arr_view_data        = [];
    	$this->module_title         = "Account Settings";
    	$this->module_view_folder   = 'retailer.account_settings'; 
    	$this->retailer_panel_slug  = config('app.project.retailer_panel_slug');
    	$this->module_url_path      = url($this->retailer_panel_slug.'/account_settings');
        $this->RetailerStoreDetailsModel = $RetailerStoreDetailsModel;
        $this->GeneralService       = $GeneralService;
        $this->UserModel            = $user;
        $this->BaseModel            = $this->UserModel;
        $this->RetailerModel        = $RetailerModel;
        $this->CategoryTranslationModel = $CategoryTranslationModel;
        $this->CategoryModel        = $CategoryModel;
        $this->CountryModel         = $CountryModel;
        $this->img_path             = base_path().'/storage/app/';

       

    }

    public function index()
    { 
        $arr_user_data     = $country_data = [];
        $arr_retailer_data = [];
        $obj_data          = Sentinel::getUser();
        $store_logo        = '';
        $user_id           = 0;

        if($obj_data)
        {
           $user_id  = $obj_data->id;
           $is_login = $obj_data->is_login;
        }

        /*user details*/

        $user_details = $this->UserModel->with(['retailer_details'])->where('id',$user_id)->first();

       
        if(isset($user_details))
        {
           $arr_user_data = $user_details->toArray();
        } 
        
        $retailer_details = $this->RetailerModel->where('user_id',$user_id)->first();
       
        if(isset($retailer_details))
        {
           $arr_retailer_data = $retailer_details->toArray();
          
        } 
    
        /*get store detail from retailer id*/

        $retailer_store_details = $this->RetailerStoreDetailsModel->where('retailer_id',$arr_user_data['id'])->first();

        if(isset($retailer_store_details))
        {
            $store_logo = $retailer_store_details->store_logo;
        }

        /*get country data*/

        // $country_data = $this->CountryModel->where('is_active','1')->get()->toArray();
        $country_data = $this->CountryModel->where('is_active','1')
                                                ->orderBy('name','ASC')
                                                ->get()
                                                ->toArray();
    

        //get all active categories
        $category_arr = $this->CategoryModel->where('is_active', 1)
                          ->whereTranslation('locale', $this->locale)
                          ->get()
                          ->toArray();


        // Format mobile number
        if(isset($arr_user_data['country_code']) && $arr_user_data['country_code'] != "")
        {
            $contactNo = str_replace($arr_user_data['country_code'],"",$arr_user_data['contact_no']);
            $arr_user_data['contact_no'] = $contactNo;
        }
            

        $this->arr_view_data['category_arr']      = $category_arr;                                          
        $this->arr_view_data['arr_user_data']     = $arr_user_data;
        $this->arr_view_data['is_login']          = $is_login;
        $this->arr_view_data['country_data']      = $country_data;
        $this->arr_view_data['arr_retailer_data'] = $arr_retailer_data;
        $this->arr_view_data['store_logo']        = $store_logo;
        $this->arr_view_data['module_title']      = $this->module_title;
    	$this->arr_view_data['page_title'] 	      = 'Account Settings';
    	$this->arr_view_data['module_url_path']   = $this->module_url_path;
    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function update(Request $request)
    {
        $form_data  = $request->all();

        $arr_rules  = array();
        $arr_store_details = [];
        $obj_data   = Sentinel::getUser();
      
        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        if(isset($form_data['tab1']) && $form_data['tab1']!='')
        {
           $arr_rules =               [
                                        'first_name'        =>'required',
                                        'last_name'         =>'required',
                                        'email'             =>'required|email',
                                        'country_id'        =>'required|numeric',
                                       
                                        'address'           =>'required',
                                        'state'             =>'required',
                                        'city'              =>'required',
                                        'country_id'        =>'required',
                                        'tax_id'            =>'required',
                                        'category'          => 'required'
                                        
                                     ];

           $validator = Validator::make($request->all(),$arr_rules);
                           

           if($validator->fails())
           {
               $response['status']      = 'error';
               $response['description'] = 'Form validation failed,please check form fields.';
               return response()->json($response);
           } 

        } 

        if(isset($form_data['tab3'])&&$form_data['tab3']!='')
        {
           $arr_rules_tab3 =  [
                                                'shipping_addr'     =>'required',
                                                'billing_addr'      =>'required',
                                                'shipping_zip_code' =>'required',
                                                'billing_zip_code'  =>'required',
                                                'shipping_city'     =>'required',
                                                'shipping_state'    =>'required',
                                                'shipping_country'  =>'required',
                                                'billing_city'      =>'required',
                                                'billing_country'   =>'required',
                                                'billing_state'     =>'required',
                                                'bill_contact_no'   =>'required|min:10',
                                                'ship_contact_no'   =>'required|min:10'
                                           ]; 
         $validator = Validator::make($request->all(),$arr_rules_tab3);
                           

           if($validator->fails())
           {
               $response['status']      = 'error';
               $response['description'] = 'Form validation failed,please check form fields.';
               return response()->json($response);
           }                                  


        }

        
        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count()==1)
        {
            Flash::error('This email id already present in our system,please try again.');
            return redirect()->back();
        }

        /*check tax_id*/                
        $tax_id      = isset($form_data['tax_id'])?$form_data['tax_id']:'';

        if($tax_id != '')
        {
          $tax_id_count = UserModel::where('tax_id', $tax_id);

          if ($obj_data->id) {
            $tax_id_count = $tax_id_count->where('id', '<>', $obj_data->id)->count();
          } else {
            $tax_id_count = $tax_id_count->count();
          }

          if($tax_id_count > 0)
          {
            Flash::error('This tax id already present in our system,please try again.');
            return redirect()->back();
          }
        } 


        if(isset($form_data['tab1']) && $form_data['tab1']!='')
        {

            $arr_data['first_name']     = $request->input('first_name',null);
            $arr_data['last_name']      = $request->input('last_name',null);
            $arr_data['email']          = $request->input('email',null);
            $arr_data['tax_id']          = $request->input('tax_id',null);
            $arr_data['country_id']     = $request->input('country_id',null);

            $arr_data['tax_id']         = $request->input('tax_id');
            $arr_data['contact_no']     = $request->input('contact_no');

            if($request->input('contact_no') && $request->input('hid_country_code')){
            $arr_data['contact_no'] = str_replace($request->input('hid_country_code'),"",$request->input('contact_no'));
            }
            $arr_data['country_code']   = $request->input('hid_country_code');
            $arr_data['buying_status']  = $request->input('buying_status',null);
            
            $arr_data['address']        = $request->input('address',null);


            if(isset($arr_data['buying_status']) && $arr_data['buying_status']!=null)
            {
                if($arr_data['buying_status'] == 1)
                {
                   $years_in_business          = $request->input('years_in_business',null);
                   $Annual_Sales               = $request->input('Annual_Sales',null);
                   $store_website              = '';
                   $arr_data['post_code']      = '';
                }

                if($arr_data['buying_status'] == 2)
                {
                   $store_website              = $request->input('store_website',null);
                   $years_in_business          = ''; 
                   $Annual_Sales               = ''; 
                   $arr_data['post_code']      = '';
                }

                if($arr_data['buying_status'] == 3)
                {
                    $years_in_business          = ''; 
                    $Annual_Sales               = ''; 
                    $store_website              = '';
                    $arr_data['post_code']      = $request->input('post_code',null);

                }

                if($arr_data['buying_status'] == 4)
                {
                    $years_in_business          = ''; 
                    $Annual_Sales               = ''; 
                    $store_website              = '';
                    $arr_data['post_code']      = '';
                }

            }

            $address2                   = $request->input('address2',null);
            $city                       = $request->input('city',null);
            $state                      = $request->input('state',null);
            $country                    = $request->input('country',null);
            $store_name                 = $request->input('store_name',null);
            $dummy_store_name           = $request->input('dummy_store_name',null);
            
            
            $store_description          = $request->input('store_description',null);
            $category                   = $request->input('category',null);
           

            $obj_data = Sentinel::update($obj_data, $arr_data);

    
            /*store some info into retailer table*/
            $result1 = $this->RetailerModel::firstOrNew(['user_id' => $obj_data->id]);

            $result1->state              = isset($state)?$state:'';
            $result1->city               = isset($city)?$city:'';
            $result1->address2           = isset($address2)?$address2:'';
            $result1->country            = isset($country)?$country:'';
            $result1->store_name         = isset($store_name)?$store_name:'';
            $result1->dummy_store_name   = isset($dummy_store_name)?$dummy_store_name:'';
            $result1->store_website      = isset($store_website)?$store_website:'';
            $result1->years_in_business  = isset($years_in_business)?$years_in_business:'';
            $result1->annual_sales       = isset($Annual_Sales)?$Annual_Sales:'';
            $result1->store_description  = isset($store_description)?$store_description:'';
            $result1->category           = isset($category)?$category:'';
            

            $res = $result1->save();



            if($res)
            {
               //Flash::success(str_singular($this->module_title).' Updated Successfully');
               $response['status']      = 'success';
               $response['description'] = 'Account settings has been updated.';
               return response()->json($response);
            }
            else
            {
               $response['status']      = 'error';
               $response['description'] = 'Error occurred,while updating '.str_singular($this->module_title).'.';
               return response()->json($response);
                 
            } 
        }


        if(isset($form_data['tab2']) && $form_data['tab2'] !='')
        {
            $arr_data = [];
            $profile_file_path = '';
            $store_logo_path  = '';

            if($request->hasFile('image'))
            {
                $profile_image =$request->file('image');
               
                if($profile_image!=null)
                {
                    $profile_file_path        = $profile_image->store('profile_image');

                    if($request->old_image!=null)
                   { 
                     $old_img_path         = $this->img_path.$request->old_image;
                     $unlink_old_img       = $this->GeneralService->unlink_old_image($old_img_path);
                   }

                }

                $arr_data['profile_image'] = $profile_file_path;
            }
            else
            {
                $arr_data['profile_image'] = $request->old_image;
            }

            if($request->hasFile('store_logo'))
            {
                $store_logo = $request->file('store_logo');

                if($store_logo !=null)
                {
                   $store_logo_path        = $store_logo->store('store_logo');

                   if($request->old_store_logo!=null)
                   { 
                     $old_img_path         = $this->img_path.$request->old_store_logo;
                     $unlink_old_img       = $this->GeneralService->unlink_old_image($old_img_path);
                   }
                }
            }
            else
            {
                $store_logo_path = $request->old_store_logo;
            }


            /* store logo info into retailer store table table*/
            $result = RetailerStoreDetailsModel::firstOrNew(['retailer_id' => $obj_data->id]); 
            $result->retailer_id =  isset($obj_data->id)?$obj_data->id:0;
            $result->store_logo  =  $store_logo_path;

            $entity = $result->save();

            /*profile_image update*/
  
            $obj_data = Sentinel::update($obj_data,$arr_data);


            if($entity)
            {
               //Flash::success(str_singular($this->module_title).' Updated Successfully'); 
               $response['status']      = 'success';
               $response['description'] = 'Account settings has been updated.';
               return response()->json($response);
            }
            else
            {
               $response['status']      = 'error';
               $response['description'] = 'Error occurred,while updating '.str_singular($this->module_title).'.';
               return response()->json($response);
                //Flash::error('Problem Occurred, While Updating '.str_singular($this->module_title));  
            } 
          
           
        }


        if(isset($form_data['tab3']) && $form_data['tab3']!='')
        { 
            
            $shipping_addr     = $request->input('shipping_addr',null);
            $shipping_zip_postal_code  = $request->input('shipping_zip_code',null);
            $shipping_country  = $request->input('shipping_country',null);
            $shipping_state    = $request->input('shipping_state',null);
            $shipping_city     = $request->input('shipping_city',null);

            $shipping_suit_apt = $request->input('shipping_address2',null);

            $billing_address   = $request->input('billing_addr',null);
            $billing_zip_postal_code = $request->input('billing_zip_code',null);
            $billing_country   = $request->input('billing_country',null);
            $billing_state     = $request->input('billing_state',null);
            $billing_city      = $request->input('billing_city',null);

            $billing_suit_apt = $request->input('billing_address2',null);

            $ship_mobile_no   = $request->input('ship_contact_no',null);
            $bill_mobile_no   = $request->input('bill_contact_no',null);



            $result = $this->RetailerModel::firstOrNew(['user_id' => $obj_data->id]);

            $result->user_id                  = isset($obj_data->id)?$obj_data->id:0;
            $result->shipping_addr            = isset($shipping_addr)?$shipping_addr:'';
            $result->shipping_city            = isset($shipping_city)?$shipping_city:'';
            $result->shipping_state           = isset($shipping_state)?$shipping_state:'';
            $result->shipping_country         = isset($shipping_country)?$shipping_country:'';
            $result->shipping_zip_postal_code = isset($shipping_zip_postal_code)?$shipping_zip_postal_code:''; 

            $result->shipping_suit_apt        = isset($shipping_suit_apt)?$shipping_suit_apt:'';

            $result->billing_country          = isset($billing_country)?$billing_country:'';
            $result->billing_state            = isset($billing_state)?$billing_state:'';
            $result->billing_city             = isset($billing_city)?$billing_city:'';
            $result->billing_address          = isset($billing_address)?$billing_address:'';
            $result->billing_zip_postal_code  = isset($billing_zip_postal_code)?$billing_zip_postal_code:'';
             
            $result->billing_suit_apt        = isset($billing_suit_apt)?$billing_suit_apt:'';

            $result->ship_contact_no         = isset($ship_mobile_no)?$ship_mobile_no:'';
            $result->bill_contact_no         = isset($bill_mobile_no)?$bill_mobile_no:'';
   
            $res = $result->save();
       
            if($res)
            {
               //Flash::success(str_singular($this->module_title).' Updated Successfully'); 
                $response['status']      = 'success';
                $response['description'] = 'Account settings has been updated.';
                return response()->json($response);
            }
            else
            {
               $response['status']      = 'error';
               $response['description'] = 'Error occurred,while updating '.str_singular($this->module_title).'.';
               return response()->json($response);
                //Flash::error('Problem Occurred, While Updating '.str_singular($this->module_title));  
            } 
          
          
        }

     
    }
    
    public function does_exists_tax_id(Request $request , $param = false)
    { 
      
      $form_data = $request->all();
     
      $tax_id = $form_data['tax_id'];

      $user_data = Sentinel::check();
      $loggedIn_userId = 0;
        
        if($user_data)
        {
            $loggedIn_userId = $user_data->id;
        }  
      
      $tax_id_count = UserModel::where('tax_id',$tax_id) 
                                ->where('id','!=',$loggedIn_userId)->count();
      
      

      if($tax_id_count!=0)
      {
       //return response()->json(['exists'=>'false']);
        return response()->json(['exists'=>'true'],404);
      }
      else
      {
        return response()->json(['exists'=>'true']);
      }
    }



    public function is_login_update()
    {  
        $user = Sentinel::check();
        $user_id = 0;
        if($user)
        {
            $user_id = $user->id;
        }
        $result = $this->UserModel->where('id',$user_id)->update(['is_login'=>1]);

        if($result)
        {
            $response['status']        = 'success';
            $response['description']   = 'is login status has been updated.';
            $response['error_message'] = 'Please fill all the required profile fields.';
            

            return response()->json($response);
        }
        else
        { 
            $response['error']        = 'error';
            $response['description']  = 'Something went wrong,please try again.';
            return response()->json($response);

        }
    }



    /*function for check profile info complete or not*/

     public function check_profile_complete()
     {
        $user_id = 0;

        $user = Sentinel::check();

        if(isset($user))
        {
           $user_id = $user->id;
        }

        $result = $this->GeneralService->is_profile_complete($user);

        if($result == false)
        {
            $response['status']        = 'error';
            $response['description']   = 'Please fill all the required profile fields.';

            return response()->json($response);
        }
        elseif($result == true)
        {
            $response['status']        = 'success';
            $response['description']   = 'Profile completed.';

            return response()->json($response);
        }
        else
        {
            $response['status']        = 'error';
            $response['description']   = 'Something went wrong,please try again.';

            return response()->json($response);
        }
     }


    /*--------------------------------------------------*/

    public function logout()
    {
        Sentinel::logout();
        return redirect(url('/'));
    }
}
