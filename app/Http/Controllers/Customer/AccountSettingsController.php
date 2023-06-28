<?php


namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RetailerStoreDetailsModel;
use App\Models\RetailerModel;
use App\Models\CustomerModel;
use App\Models\CountryModel;

use App\Common\Services\GeneralService;

use Validator;
use Flash;
use Sentinel;
use Hash;
use DB;

class AccountSettingsController extends Controller
{
   
	public function __construct(UserModel $user,
                                RetailerStoreDetailsModel $RetailerStoreDetailsModel,
                                RetailerModel $RetailerModel,
                                CustomerModel $CustomerModel,
                                CountryModel $CountryModel,
                                GeneralService $GeneralService
                                )
    {
    	$this->arr_view_data        = [];
    	$this->module_title         = "Account Settings";
    	$this->module_view_folder   = 'customer.account_settings'; 
    	$this->customer_panel_slug  = config('app.project.customer_panel_slug');
    	$this->module_url_path      = url($this->customer_panel_slug.'/account_settings');
        $this->RetailerStoreDetailsModel = $RetailerStoreDetailsModel;
        $this->GeneralService       = $GeneralService;
        $this->UserModel            = $user;
        $this->BaseModel            = $this->UserModel;
        $this->RetailerModel        = $RetailerModel;
        $this->CustomerModel        = $CustomerModel;
        $this->CountryModel         = $CountryModel;
        $this->img_path             = base_path().'/storage/app/';
        //dd($this->img_path);


    }

    public function index()
    {
        $arr_user_data     = $country_data = [];
        $arr_customer_data = [];
        $obj_data          = Sentinel::getUser();
        $store_logo        = '';
        $user_id           = 0;

        if($obj_data)
        {
           $user_id  = $obj_data->id;
           $is_login = $obj_data->is_login;
        }

        /*user details*/

        $user_details = $this->UserModel->with(['customer_details'])->where('id',$user_id)->first();

       
        if(isset($user_details))
        {
           $arr_user_data = $user_details->toArray();
        }         

        /*get country data*/

        // $country_data = $this->CountryModel->where('is_active','1')->get()->toArray();

         $country_data = $this->CountryModel->where('is_active','1')->orderBy('name','ASC')
                                                ->get()
                                                ->toArray();
    

        $this->arr_view_data['arr_user_data']     = $arr_user_data;
        //$this->arr_view_data['is_login']          = $is_login;
        $this->arr_view_data['is_login']          = "";
        $this->arr_view_data['country_data']      = $country_data;
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


        /*try {
                DB::beginTransaction();*/
                
                if(isset($form_data['tab1']) && $form_data['tab1']!='')
                {
                   $arr_rules =               [
                                                'first_name'        =>'required',
                                                'last_name'         =>'required',
                                                'email'             =>'required|email',
                                                'contact_no'        =>'required',
                                                'country_id'        =>'required|numeric',
                                                'post_code'         =>'required',
                                                'address'           =>'required',
                                               //'address2'          =>'required',
                                                'state'             =>'required',
                                                'city'              =>'required'
                                                
                                             ];

                   $validator = Validator::make($request->all(),$arr_rules);
                                   

                   if($validator->fails())
                   {
                       $response['status']      = 'error';
                       $response['description'] = 'Form validation failed, please check form fields.';
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
                                                        'billing_state'     =>'required'
                                                   ]; 
                 $validator = Validator::make($request->all(),$arr_rules_tab3);
                                   

                   if($validator->fails())
                   {
                       $response['status']      = 'error';
                       $response['description'] = 'Form validation failed, please check form fields.';
                       return response()->json($response);
                   }                                  


                }


                
                if($this->UserModel->where('email',$request->input('email'))
                                   ->where('id','!=',$obj_data->id)
                                   ->count()==1)
                {
                    Flash::error('This email id already present in our system, please try again.');
                    return redirect()->back();
                }


                if(isset($form_data['tab1']) && $form_data['tab1']!='')
                {

                    $arr_data['first_name']     = $request->input('first_name',null);
                    $arr_data['last_name']      = $request->input('last_name',null);
                    $arr_data['email']          = $request->input('email',null);
                    $arr_data['country_id']     = $request->input('country_id',null);
                    $arr_data['contact_no']     = $request->input('contact_no');

                    if($request->input('contact_no') && $request->input('hid_country_code')){
                      $arr_data['contact_no'] = str_replace($request->input('hid_country_code'),"",$request->input('contact_no'));
                    }
                    $arr_data['country_code'] = $request->input('hid_country_code');
                    
                    $arr_data['city']           = $request->input('city');
                    $arr_data['state']          = $request->input('state');
                    $arr_data['post_code']      = $request->input('post_code');
                    $arr_data['address']        = $request->input('address');
                    $arr_data['address2']       = $request->input('address2');


                    if(isset($form_data['tab2']) && $form_data['tab2'] !='')
                    {
                        //$arr_data = [];
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

                        
                    }
                    
                    $obj_data = Sentinel::update($obj_data, $arr_data);

                    if($obj_data)
                    {
                       //update state,city,address2 in to customer table

                       $customer_detail_data = [];

                       $customer_detail_data['state']    =  $arr_data['state'];
                       $customer_detail_data['city']     =  $arr_data['city'];
                       $customer_detail_data['address2'] =  $arr_data['address2'];

                       $this->CustomerModel->where('user_id',$obj_data->id)->update($customer_detail_data);

                       $response['status']      = 'success';
                       $response['description'] = 'Account settings has been updated.';
                       return response()->json($response);
                    }
                    else
                    {
                       $response['status']      = 'error';
                       $response['description'] = 'Error occurred, while updating '.str_singular($this->module_title).'.';
                       return response()->json($response);
                         
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

                    $billing_mobile_no = $request->input('bill_contact_no',null);

                    $shipping_mobile_no = $request->input('ship_contact_no',null);



                    $result = $this->CustomerModel::firstOrNew(['user_id' => $obj_data->id]);

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

                    $result->ship_contact_no         = isset($shipping_mobile_no)?$shipping_mobile_no:'';

                    $result->bill_contact_no         = isset($billing_mobile_no)?$billing_mobile_no:'';
           
                    $res = $result->save();
               
                    if($res)
                    {
                       //Flash::success(str_singular($this->module_title).' Updated Successfully'); 
                        $response['status']      = 'success';
                        $response['description'] = 'Shipping & Billing Details has been updated.';
                        return response()->json($response);
                    }
                    else
                    {
                       $response['status']      = 'error';
                       $response['description'] = 'Error occurred, while updating '.str_singular($this->module_title).'.';
                       return response()->json($response);
                        //Flash::error('Problem Occurred, While Updating '.str_singular($this->module_title));  
                    } 
                  
                  
                }
            //DB::commit();    


       /* }
        catch (Exception $e){
            
            DB::rollback();

            $response['status']      = 'error';
            $response['description'] = $e->getMessage();
            return response()->json($response);  

        }    */

     
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
            $response['description']  = 'Something went wrong, please try again.';
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
            $response['description']   = 'Something went wrong, please try again.';

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
