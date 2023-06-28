<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\CategoryModel;
use App\Models\CountryModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\RoleUsersModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\ElasticSearchService;
use Sentinel;
use Flash;
use Session;

class AccountSettingsController extends Controller
{
    
    public function __construct(UserModel $user,
                                MakerModel $MakerModel,
                                CategoryModel $CategoryModel,
                                CountryModel $CountryModel,
                                RoleUsersModel $RoleUsersModel,
                                StripePaymentService $StripePaymentService,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                ElasticSearchService $ElasticSearchService
                              )
    {   
        $this->arr_view_data      = [];
        $this->module_title       = "Account Settings";
        $this->module_view_folder = 'maker.account_settings'; 
        $this->maker_panel_slug   = config('app.project.maker_panel_slug');
        $this->module_url_path    = url($this->maker_panel_slug.'/account_settings');
        $this->UserModel          = $user;
        $this->MakerModel         = $MakerModel;
        $this->CategoryModel      = $CategoryModel;
        $this->CountryModel       = $CountryModel;
        $this->RoleUsersModel     = $RoleUsersModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->StripeAccountDetailsModel = $StripeAccountDetailsModel;
        $this->StripePaymentService = $StripePaymentService;
        $this->ElasticSearchService = $ElasticSearchService;
        $this->profile_image      = base_path().'/storage/app/';

    }

    public function index()
    {
        $arr_data  = $strip_arr = [];
        
        $obj_data  = Sentinel::getUser();
        
        $user_data = Sentinel::check();
        $loggedIn_userId = 0;

        if($user_data)
        {
            $loggedIn_userId = $user_data->id;
        }  

        if($obj_data)
        {
           $arr_data = $obj_data->toArray();    
        }

        if(isset($arr_data) && sizeof($arr_data)<=0)
        {
            return redirect($this->admin_url_path.'/login');
        }
      
        
        $categories_arr = $this->CategoryModel->where('is_active','1')
                                                ->orderBy('id','DESC')
                                                ->get()
                                                ->toArray();

        $country_arr = $this->CountryModel->where('is_active','1')
                                                ->orderBy('name','ASC')
                                                ->get()
                                                ->toArray();

        $maker_details_arr =  $this->MakerModel->where('user_id',$loggedIn_userId)->first()->toArray();

        $obj_strip         =  $this->UserStripeAccountDetailsModel->where('user_id',$loggedIn_userId)
                                                                  ->where('is_active','1')
                                                                  ->first();
        if($obj_strip)
        {
          $strip_arr       =  $obj_strip->toArray();  
        }
       
        $this->arr_view_data['arr_user_data']   = $arr_data; 
        $this->arr_view_data['maker_details']
                                                = $maker_details_arr;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Account Settings';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['categories_arr']  = $categories_arr;
        $this->arr_view_data['country_arr']     = $country_arr;
        $this->arr_view_data['strip_arr']       = isset($strip_arr)?$strip_arr:'';
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function update(Request $request)
    {

        $data = $request->all();

        $arr_rules  = $arr_strip_keys = array();
       
        $obj_data   = Sentinel::getUser();

        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        $inputs = request()->validate([
                                        'first_name'=>'required',
                                        'last_name'=>'required',
                                        'email'=>'required|email',
                                        'mobile_no'=>'required',
                                        'country_id'=>'required',
                                        'company_name'=>'required',
                                        'real_company_name'=>'required',
                                        //'website_url'=>'required',
                                        'primary_category_id'=>'required',
                                        //'other_category_name'=>'required',
                                        //'no_of_stores'=>'required',
                                        'post_code' =>'required'
                                     ]);

        // if($request->input('other_category_name') != ""){
        //     $inputs = request()->validate([ 'other_category_name'=>'required' ]);
        // }
        
        $test  = $this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count();    
                 

        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count()==1)
        {
            Flash::error('This email id already present in our system, please try another one.');
            return redirect()->back();
        }

        /*check tax_id*/                
        $tax_id      = isset($data['tax_id'])?$data['tax_id']:'';

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

        $profile_file_path = '';
        if($request->hasFile('image'))
        {
            $profile_image =$request->file('image');
            
            if($profile_image!=null){
                
                if($request->old_image!="")
                {  
                    $old_image_path  = $this->profile_image.$request->old_image;

                    if(file_exists($old_image_path))
                    {
                       @unlink($old_image_path);
                    }
                }

                $profile_file_path = $profile_image->store('profile_image');
            }

            $arr_data['profile_image']= $profile_file_path;
        }
        
        $arr_data['first_name']   = $request->input('first_name');
        $arr_data['last_name']    = $request->input('last_name');
        $arr_data['email']        = $request->input('email');
        $arr_data['contact_no']   = trim($request->input('mobile_no'));
        if($request->input('mobile_no') && $request->input('hid_country_code')){
          $arr_data['contact_no'] = str_replace($request->input('hid_country_code'),"",$request->input('mobile_no'));
        }
        $arr_data['country_code'] = $request->input('hid_country_code');
        $arr_data['country_id']   = $request->input('country_id');
        $arr_data['tax_id']       = $request->input('tax_id');
        $arr_data['post_code']    = $request->input('post_code'); 
        $arr_data['address']      = $request->input('address');


        $arr_maker_data['company_name'] = $request->input('company_name');
        $arr_maker_data['real_company_name'] = $request->input('real_company_name');
       
        // $arr_maker_data['brand_name'] = $request->input('brand_name');

        $arr_maker_data['website_url']= $request->input('website_url');
        $arr_maker_data['primary_category_id'] 
                                      = $request->input('primary_category_id');

        if($request->input('other_category_name_insert') != ""){
          $arr_maker_data['primary_category_name'] = $request->input('other_category_name');  
         
        } else { 
           $arr_maker_data['primary_category_name'] = ''; 
        }                         
        $arr_maker_data['no_of_stores']
                                      = $request->input('no_of_stores');
        $arr_maker_data['insta_url']  = $request->input('insta_url');
        $arr_maker_data['description']= $request->input('description'); 
        
      
        
        $obj_data = Sentinel::update($obj_data, $arr_data);


        $obj_maker= MakerModel::where('user_id',$obj_data->id)->update($arr_maker_data);

        /*take from gitlab (b009d1fef04681dd2fc4a497e68dc0b2e81f460b)*/
        // if($data['stripe_secret_key']!=null && $data['account_holder']!=null)
        // {

        //   /* Check account holder name is exists or not */
        //   $isAccHolderIsExists = $this->UserStripeAccountDetailsModel
        //                               ->where('account_holder',$data['account_holder'])
        //                               ->where('is_active','1')
        //                               ->where('user_id',$obj_data->id)
        //                               ->where('client_id','<>',$data['stripe_client_id'])
        //                               ->where('secret_key','<>',$data['stripe_secret_key'])
        //                               ->count();

        //   if($isAccHolderIsExists > 0)                             
        //   {
        //       Flash::error('Stripe Account Holder is already exists, Please Try another name'); 
        //       return redirect()->back();
        //   }

        //   /* get existing activated stripe secret key */
        //   $getExistingActivatedStripeKey = $this->UserStripeAccountDetailsModel->where('is_active','1')
        //                                                                 ->where('user_id',$obj_data->id)
        //                                                                 ->first(['secret_key','id']);

        //   $existingActivatedStripeKey = isset($getExistingActivatedStripeKey->secret_key)?$getExistingActivatedStripeKey->secret_key:false; 
        
        //   /* get cuurent activated secret key Id*/
        //   $existingActivatedStripeKeyId = isset($getExistingActivatedStripeKey->id)?$getExistingActivatedStripeKey->id:false;

        //   $objStripeDetails = $this->UserStripeAccountDetailsModel
        //                                           ->where('client_id',$data['stripe_client_id'])
        //                                           ->where('secret_key',$data['stripe_secret_key'])
        //                                           ->where('user_id',$obj_data->id);

        //   /* check if any secret key is or client id is exist or not */
        //   $isExistStrpeDetails= $objStripeDetails->count();


        //   if(isset($isExistStrpeDetails) && $isExistStrpeDetails > 0)
        //   {  
        //     $arr_strip_keys['is_active']         = '1';
        //     $arr_strip_keys['account_holder']    = $data['account_holder'];

        //    $obj_stripe_key_data = $objStripeDetails->first();
            
        //     /* activate existing key */
        //     $update_strip_keys = UserStripeAccountDetailsModel::where('id',$obj_stripe_key_data->id)
        //                                                        ->update($arr_strip_keys);

        //     if($update_strip_keys)
        //     {
        //       /* deactivate existing key */
        //        UserStripeAccountDetailsModel::where('id','!=',$obj_stripe_key_data->id)
        //                                     ->where('user_id',$obj_data->id)
        //                                     ->update(['is_active'=>0]);              
        //     }

        //     if($obj_stripe_key_data->id != $existingActivatedStripeKeyId)
        //     {
        //        /* Clone user account data into new stripe account */
        //         $this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$obj_stripe_key_data->id,$data['stripe_secret_key']);
        //     }

        //   }
        //   else
        //   {
        //     /* create new stripe key */
        //     $arr_strip_keys['user_id']           = $obj_data->id;
        //     $arr_strip_keys['secret_key']        = $data['stripe_secret_key'];
        //     $arr_strip_keys['client_id']         = $data['stripe_client_id'];
        //     $arr_strip_keys['account_holder']    = $data['account_holder'];
        //     $arr_strip_keys['is_active']         = '1';
        //     $insert_strip_keys                   = UserStripeAccountDetailsModel::create($arr_strip_keys);

        //     if($insert_strip_keys)
        //     {
        //       /* deactivate existing keys , After inserting new key */
        //         UserStripeAccountDetailsModel::where('id','!=',$insert_strip_keys->id)
        //                                            ->where('user_id',$obj_data->id)
        //                                            ->update(['is_active'=>'0']); 

                                                               
        //     }

        //     /* Clone user account data into new stripe account */
        //     $this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$insert_strip_keys->id,$data['stripe_secret_key']);
        //   }
        // }
        // else
        // {
        //    UserStripeAccountDetailsModel::where('user_id',$obj_data->id)
        //                                   ->update(['is_active'=>0]);
        // }

        /*end*/
       
        if($obj_data && $obj_maker)
        {
            try{

                //after change the country of vendor then update elastic search index

                $this->ElasticSearchService->delete_vendor_product($obj_data->id);

                $this->ElasticSearchService->index_vendor_product($obj_data->id);

            }
            catch(Exception $e){
            
              Flash::error($e->getMessage()); 
                
            }
            

            Flash::success('Account settings has been updated.'); 

        }
        else
        {
            Flash::error('Error occurred while updating '.str_singular($this->module_title));  
        } 
      
        return redirect()->back();
    }

    public function logout()
    {
        Sentinel::logout();
        return redirect(url('/'));
    }

    public function change_password(Request $request)
    {   
     
        if ($request->isMethod('get'))
        {
            $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;
            $this->arr_view_data['page_title']      = "Change Password";
            $this->arr_view_data['module_title']    = "Change Password";
            $this->arr_view_data['module_url_path'] = $this->module_url_path.'/change_password';
            
            return view($this->module_view_folder.'.change_password',$this->arr_view_data);  
        }


        /*Check Validatons and display custom message*/
       $inputs = request()->validate([
                                        'current_password'=> 'required',
                                        'new_password'    => 'required'
                                    ],
                                    [
                                       'current_password.required'=>'Please enter current password',
                                       'new_password.required'    =>'Please enter new password'
                                    ]);
      
      $user = Sentinel::check();

      $credentials = [];
      $credentials['password'] = $request->input('current_password');

      if (Sentinel::validateCredentials($user,$credentials)) 
      {   
            $new_credentials = [];
            $new_credentials['password'] = $request->input('new_password');

            if(Sentinel::update($user,$new_credentials))
            {  
               //Flash::success('Password changed successfully');
               //Session::flush();
               Flash::success('Your password has been changed successfully.');
              return redirect()->back();
            }
            else
            {  
               Flash::error('Error occurred while changing password.');
            }
      } 
      else
      {

        Flash::error('Invalid old password.');
      }       
      
      return redirect()->back();

     }

    public function does_exists_tax_id(Request $request , $param = false)
    { 
                
      $user_data = Sentinel::check();
      $loggedIn_userId = 0;
        
      if($user_data)
      {
        $loggedIn_userId = $user_data->id;
      }  

      if($param)
      {
        $tax_id = base64_decode($param);

        $tax_id_count = UserModel::where('tax_id',$tax_id) 
                                 ->where('id','!=',$loggedIn_userId)->count();
      }else{

          $form_data = $request->all();
      
          $tax_id = $form_data['tax_id'];

          $tax_id_count = UserModel::where('tax_id',$tax_id) 
                                     ->where('id','!=',$loggedIn_userId)->count();

      }
     
      if($tax_id_count!=0)
      {
       //return response()->json(['exists'=>'false']);
        return response()->json(['exists'=>'true','status' => 'error']);
      }
      else
      {
        return response()->json(['exists'=>'true','status' => 'success']);
      }
    }

    public function clone_user_stripe_accounts($existingActivatedStripeKeyId,$providedDetailsKeyId,$providedDetailsKey)
    {
       $response['status'] = 'success';

        /*get existing stripe connected accounts */
        $arrExistingStripeCloneAccounts = StripeAccountDetailsModel::where('vendor_stripe_key_id',$existingActivatedStripeKeyId)
                                ->get()->toArray();

        if(isset($arrExistingStripeCloneAccounts) && count($arrExistingStripeCloneAccounts) > 0)
        {
            foreach ($arrExistingStripeCloneAccounts as $key => $arrCloneAccounts) 
            {
                $objVerifyUserStripeAccount = StripeAccountDetailsModel::where('vendor_stripe_key_id',$providedDetailsKeyId)
                                            ->where('stripe_acc_id',$arrCloneAccounts['stripe_acc_id'])
                                            ->where('user_id',$arrCloneAccounts['user_id'])
                                            ->first();

                if($objVerifyUserStripeAccount)
                {
                    // need to verify account exist or not in stripe account.
                }
                else
                {
                    $userData = Sentinel::findUserById($arrCloneAccounts['user_id']);
                    
                    if($userData)
                    {
                       $createdAccount = $this->StripePaymentService->clone_user_stripe_account_details($providedDetailsKey,$userData->email);

                       if(isset($createdAccount['status']) && $createdAccount['status'] == 'Error')
                       {
                        $response['status'] = 'error';
                        $response['description'] = isset($createdAccount['description'])?$createdAccount['description']:'Soemething went wrong while clonning accounts';
                       }
                       else
                       {
                         $userAccData = [];

                         $userAccData['admin_stripe_key_id']  = $arrCloneAccounts['admin_stripe_key_id'];
                         $userAccData['stripe_acc_id']        = $createdAccount['account_id'];
                         $userAccData['user_id']              = $arrCloneAccounts['user_id'];
                         $userAccData['vendor_stripe_key_id'] = $providedDetailsKeyId;
                         $userAccData['stripe_customer_id']   = $createdAccount['customer_id'];
                         $userAccData['user_stripe_email_id'] = $userData->email;
                         $userAccData['vendor_id']            = $arrCloneAccounts['vendor_id'];

                         StripeAccountDetailsModel::create($userAccData);  

                       }
                    }

                }
            }
        }
        
        /*end*/

        return $response;
    }

     public function verify_password(Request $request)
    {
        
        $password = $request->get('password');
        $email    = $request->get('email');
        $response = [];
          

        $credentials = [
        'email' => trim($email),
        'password' => trim($password),
         ];
 
         $is_verified =false;
        //dd($credentials);
        $is_verified = Sentinel::authenticate($credentials);
       
        if($is_verified == false)
        {
            $response['status'] = "failiure";
            Flash::error('Incorrect password please try again'); 
             
        }
        else
        {
            $response['status'] = "success";
            Flash::success('Stripe settings has been updated.'); 

        }
        return $response;

    }

    public function update_stripe_settings(Request $request)
    {
        $data = $request->all();

        $obj_data   = Sentinel::getUser();

        if(isset($data['stripe_secret_key']) && $data['stripe_secret_key']!=null && isset($data['account_holder']) && $data['account_holder']!=null)
        {

          /* Check account holder name is exists or not */
          $isAccHolderIsExists = $this->UserStripeAccountDetailsModel
                                      ->where('account_holder',$data['account_holder'])
                                      ->where('is_active','1')
                                      ->where('user_id',$obj_data->id)
                                      ->where('client_id','<>',$data['stripe_client_id'])
                                      ->where('secret_key','<>',$data['stripe_secret_key'])
                                      ->count();

          if($isAccHolderIsExists > 0)                             
          {
              Flash::error('Stripe Account Holder is already exists, Please Try another name'); 
              return redirect()->back();
          }
          /* Check provided stripe details is already reserved for admin user or not */
          $user_stripe_tbl = $this->UserStripeAccountDetailsModel->getTable();
          $role_user_tbl = $this->RoleUsersModel->getTable();

          $isKeyAllow = \DB::table($user_stripe_tbl)
                            ->where($user_stripe_tbl.'.user_id','<>',$obj_data->id)
                            ->where('is_active','1')
                            ->where('client_id',$data['stripe_client_id'])
                            ->where('secret_key',$data['stripe_secret_key'])
                            ->leftjoin($role_user_tbl,$role_user_tbl.'.user_id','=',$user_stripe_tbl.'.user_id')
                            ->where($role_user_tbl.'.role_id','=' ,'1')
                            ->first();

        if(count($isKeyAllow) > 0)
        {
            Flash::error('Stripe details is already reserved for Admin, Please Try another again'); 
            return redirect()->back();
        }

          /* get existing activated stripe secret key */
          $getExistingActivatedStripeKey = $this->UserStripeAccountDetailsModel->where('is_active','1')
                                                                        ->where('user_id',$obj_data->id)
                                                                        ->first(['secret_key','id']);

          $existingActivatedStripeKey = isset($getExistingActivatedStripeKey->secret_key)?$getExistingActivatedStripeKey->secret_key:false; 
        
          /* get cuurent activated secret key Id*/
          $existingActivatedStripeKeyId = isset($getExistingActivatedStripeKey->id)?$getExistingActivatedStripeKey->id:false;

          $objStripeDetails = $this->UserStripeAccountDetailsModel
                                                  ->where('client_id',$data['stripe_client_id'])
                                                  ->where('secret_key',$data['stripe_secret_key'])
                                                  ->where('user_id',$obj_data->id);

          /* check if any secret key is or client id is exist or not */
          $isExistStrpeDetails= $objStripeDetails->count();


          if(isset($isExistStrpeDetails) && $isExistStrpeDetails > 0)
          {  
            $arr_strip_keys['is_active']         = '1';
            $arr_strip_keys['account_holder']    = $data['account_holder'];

           $obj_stripe_key_data = $objStripeDetails->first();
            
            /* activate existing key */
            $update_strip_keys = UserStripeAccountDetailsModel::where('id',$obj_stripe_key_data->id)
                                                               ->update($arr_strip_keys);

            if($update_strip_keys)
            {
              /* deactivate existing key */
               UserStripeAccountDetailsModel::where('id','!=',$obj_stripe_key_data->id)
                                            ->where('user_id',$obj_data->id)
                                            ->update(['is_active'=>0]);              
            }

            if($obj_stripe_key_data->id != $existingActivatedStripeKeyId)
            {
               /* Clone user account data into new stripe account */
                //$this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$obj_stripe_key_data->id,$data['stripe_secret_key']);
            }

          }
          else
          {
            /* create new stripe key */
            $arr_strip_keys['user_id']           = $obj_data->id;
            $arr_strip_keys['secret_key']        = $data['stripe_secret_key'];
            $arr_strip_keys['client_id']         = $data['stripe_client_id'];
            $arr_strip_keys['account_holder']    = $data['account_holder'];
            $arr_strip_keys['is_active']         = '1';
            $insert_strip_keys                   = UserStripeAccountDetailsModel::create($arr_strip_keys);

            if($insert_strip_keys)
            {
              /* deactivate existing keys , After inserting new key */
                UserStripeAccountDetailsModel::where('id','!=',$insert_strip_keys->id)
                                                   ->where('user_id',$obj_data->id)
                                                   ->update(['is_active'=>'0']); 

                                                               
            }

            /* Clone user account data into new stripe account */
            $this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$insert_strip_keys->id,$data['stripe_secret_key']);
          }
        }
        else
        {
           UserStripeAccountDetailsModel::where('user_id',$obj_data->id)
                                          ->update(['is_active'=>0]);
        }

        if($obj_data)
        {
            Flash::success('Stripe Account settings has been updated.'); 
        }
        else
        {
            Flash::error('Error occurred while updating '.str_singular($this->module_title));  
        } 
      
        return redirect()->back();
    }
}
