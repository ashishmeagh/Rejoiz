<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Events\ActivityLogEvent;
use App\Models\ActivityLogsModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\CardModel;
use App\Common\Services\GeneralService;
use App\Common\Services\StripePaymentService;

use Validator;
use Flash;
use Sentinel;
use Hash;
 
class AccountSettingsController extends Controller
{

    public function __construct (
                                  UserModel $user,
                                  ActivityLogsModel $activity_logs,
                                  UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                  StripeAccountDetailsModel $StripeAccountDetailsModel,
                                  GeneralService $GeneralService,
                                  StripePaymentService $StripePaymentService,
                                  CardModel $CardModel
                                )
    {
        $this->UserModel            = $user;
        $this->BaseModel            = $this->UserModel;
        $this->UserStripeAccountDetailsModel  = $UserStripeAccountDetailsModel;
        $this->StripeAccountDetailsModel  = $StripeAccountDetailsModel;
        $this->ActivityLogsModel    = $activity_logs;
        $this->GeneralService       = $GeneralService;
        $this->StripePaymentService = $StripePaymentService;
        $this->CardModel            = $CardModel;
        $this->arr_view_data        = [];
        $this->admin_url_path       = url(config('app.project.admin_panel_slug'));
        $this->module_url_path      = $this->admin_url_path."/account_settings";
        $this->user_base_img_path   = base_path().config('app.project.img_path.user_profile_image');
        $this->user_public_img_path = url('/').config('app.project.img_path.user_profile_image');
        $this->module_title         = "Account Settings";
        $this->module_view_folder   = "admin.account_settings";
        $this->module_icon          = "fa-cogs";
        $this->profile_image       = base_path().'/storage/app/';
        $this->admin_user_id       = get_admin_id();

    }


    public function index()
    {
        $loginUserId = 0;

        $loginUser = Sentinel::getUser();

        if($loginUser != null && $loginUser != false)
        {
           $loginUserId = $loginUser->id;
        }

        $arr_account_settings = array();

        $arr_data  = $arrStripeKeyData = [];

        $secretKey = $clientId = '';

        
        $obj_data  = Sentinel::getUser();
        
        if($obj_data)
        {
           $arr_data = $obj_data->toArray();    
        }

        if(isset($arr_data) && sizeof($arr_data)<=0)
        {
            return redirect($this->admin_url_path.'/login');
        }

        $objStripeKeyData = $this->UserStripeAccountDetailsModel
                                       ->where('is_active','1')
                                       ->where('user_id',$loginUserId)
                                       ->first();

        if($objStripeKeyData != 'null' && $objStripeKeyData != '')
        {
            $arrStripeKeyData = $objStripeKeyData->toArray();

           $secretKey = isset($arrStripeKeyData['secret_key'])?$arrStripeKeyData['secret_key']:'';

           $clientId = isset($arrStripeKeyData['client_id'])?$arrStripeKeyData['client_id']:'';
        }


        $this->arr_view_data['arrStripeKeyData']= $arrStripeKeyData;
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function update(Request $request)
    {
        
        $arr_rules  = array();
       
        $obj_data   = Sentinel::getUser();

        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        $inputs     = request()->validate([
                                        'first_name'=>'required',
                                        'last_name'=>'required',
                                        'email'=>'required|email'
                                     ]);
        
        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count()==1)
        {
            Flash::error('This email id already present in our system, please try another one');
            return redirect()->back();
        }

        $profile_file_path = '';
        if($request->hasFile('image'))
        {
            $profile_image = $request->file('image');

            if($profile_image!=null){
                $profile_file_path = $profile_image->store('profile_image');

                //unlink old image
                if($request->input('old_image')!="null")
                {
                   $old_img_path   = $this->profile_image.$request->input('old_image');
                   $unlink_old_img = $this->GeneralService->unlink_old_image($old_img_path);
                }
            }

            $arr_data['profile_image']= $profile_file_path;
        }
        
        $arr_data['first_name']   = $request->input('first_name');
        $arr_data['last_name']    = $request->input('last_name');
        $arr_data['email']        = $request->input('email');     

       $obj_data = Sentinel::update($obj_data, $arr_data);

        if($obj_data)
        {
            Flash::success('Account settings has been updated.'); 
        }
        else
        {
            Flash::error('Problem occurred, while updating '.$this->module_title);  
        } 
      

        return redirect()->back();
    }
    
     public function update_stripe_settings(Request $request)
    {
     
        $arr_rules  = array();
        $obj_data   = Sentinel::getUser();

        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        $inputs     = request()->validate([
                                        'stripe_secret_key'=>'required',
                                        'stripe_client_id'=>'required',
                                        'account_holder'=>'required',
                                     ]);       
        
        $stripeDataUpdate['stripe_secret_key'] = $request->input('stripe_secret_key');
        $stripeDataUpdate['stripe_client_id']  = $request->input('stripe_client_id');
        $stripeDataUpdate['account_holder']    = $request->input('account_holder');

         /* Check account holder name is exists or not */
        $isAccHolderIsExists = $this->UserStripeAccountDetailsModel
                                    ->where('account_holder',$stripeDataUpdate['account_holder'])
                                    ->where('is_active','1')
                                    ->where('user_id',$obj_data->id)
                                    ->where('client_id','<>',$stripeDataUpdate['stripe_client_id'])
                                    ->where('secret_key','<>',$stripeDataUpdate['stripe_secret_key'])
                                    ->count();
        
        /* Check provided stripe details is already reserved for vendor user or not */

        $isKeyAllow = $this->UserStripeAccountDetailsModel->where('user_id','<>',$obj_data->id)
                            ->where('is_active','1')
                            ->where('client_id',$stripeDataUpdate['stripe_client_id'])
                            ->where('secret_key',$stripeDataUpdate['stripe_secret_key'])
                            ->count();

        if($isKeyAllow > 0)
        {
            Flash::error('Stripe details is already reserved for another user, Please Try another again'); 
            return redirect()->back();
        }

        if($isAccHolderIsExists > 0)                             
        {
            Flash::error('Stripe Account Holder is already exists, Please Try another name'); 
            return redirect()->back();
        }

        $stripeDataUpdate['is_active']         = '1';
        $stripeDataUpdate['account_holder']    =  $stripeDataUpdate['account_holder'];

        /* get existing activated stripe secret key */
        $getExistingActivatedStripeKey = $this->UserStripeAccountDetailsModel->where('is_active','1')
                                                                        ->where('user_id',$obj_data->id)
                                                                        ->first(['secret_key','id']);

        $existingActivatedStripeKey = isset($getExistingActivatedStripeKey->secret_key)?$getExistingActivatedStripeKey->secret_key:false; 
        
        /* get cuurent activated secret key Id*/
        $existingActivatedStripeKeyId = isset($getExistingActivatedStripeKey->id)?$getExistingActivatedStripeKey->id:false;

        /* get existing activated stripe secret key */
        $objGetkeyData = $this->UserStripeAccountDetailsModel
                              ->where('user_id',  $obj_data->id)
                              ->where('client_id',$stripeDataUpdate['stripe_client_id'])
                              ->where('secret_key',$stripeDataUpdate['stripe_secret_key']);

       
        $isExistStrpeDetailsCount = $objGetkeyData->count();

        if($isExistStrpeDetailsCount > 0)                              
        {
            $isKeyExist = $objGetkeyData->first();
            
            $arr_strip_keys['is_active']         = '1';
            $arr_strip_keys['account_holder']    = $stripeDataUpdate['account_holder'];

            /* get cuurent activated secret key */

            $providedDetailsKeyId = isset($isKeyExist->id)?$isKeyExist->id:false;

            $update_strip_keys = UserStripeAccountDetailsModel::where('id',$providedDetailsKeyId)
                                                              ->update($arr_strip_keys);

            // dd(UserStripeAccountDetailsModel::where('id',$providedDetailsKeyId)->first());

            if($update_strip_keys)
            {
              /* deactivate existing key */
               UserStripeAccountDetailsModel::where('id','!=',$providedDetailsKeyId)
                                            ->where('user_id',$obj_data->id)
                                            ->update(['is_active'=> 0]);              
            }

            if($existingActivatedStripeKeyId != $providedDetailsKeyId)
            {
                /* Clone user account data into new stripe account */
               // $this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$providedDetailsKeyId,$stripeDataUpdate['stripe_secret_key']);

                /* clone existing card data into new stripe account */
                $this->clone_existing_card_details($existingActivatedStripeKey,$existingActivatedStripeKeyId,$stripeDataUpdate['stripe_secret_key'],$providedDetailsKeyId);                
            }      
        }              
        else
        {
            /* create new stripe key */
            $arr_strip_keys['user_id']           = $obj_data->id;
            $arr_strip_keys['secret_key']        = $stripeDataUpdate['stripe_secret_key'];
            $arr_strip_keys['client_id']         = $stripeDataUpdate['stripe_client_id'];
            $arr_strip_keys['account_holder']    = $stripeDataUpdate['account_holder'];
            $arr_strip_keys['is_active']         = '1';
            $insert_strip_keys                   = UserStripeAccountDetailsModel::create($arr_strip_keys);

            if($insert_strip_keys)
            {
              /* deactivate existing keys , After inserting new key */
                UserStripeAccountDetailsModel::where('id','!=',$insert_strip_keys->id)
                                                   ->where('user_id',$obj_data->id)
                                                   ->update(['is_active'=> 0]);  
            }

             //$this->clone_user_stripe_accounts($existingActivatedStripeKeyId,$insert_strip_keys->id,$stripeDataUpdate['stripe_secret_key']);

             /* clone existing card data into new stripe account */
             $this->clone_existing_card_details($existingActivatedStripeKey,$existingActivatedStripeKeyId,$stripeDataUpdate['stripe_secret_key'],$insert_strip_keys->id);
        }


        if(isset($update_strip_keys) ||isset($insert_strip_keys))
        {
            Flash::success('Stripe settings has been updated.');             
        }
        else
        {
            Flash::error('Problem occurred, while updating stripe settings'); 
        }      

        return redirect()->back();
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

    public function clone_existing_card_details($fromAccSecretKey,$fromAccKeyId,$toAccSecretKey,$toAccKeyId)
    {
        $arrCardDetails = $arrCards = [];
        
        $arrCardDetails = $this->CardModel->where('stripe_key_id',$fromAccKeyId)
                                          ->get()
                                          ->toArray();


        if($arrCardDetails && count($arrCardDetails))
        {
          $arrCards = $this->StripePaymentService->clone_card_data_into_new_account($fromAccSecretKey,$fromAccKeyId,$toAccSecretKey,$toAccKeyId,$arrCardDetails);
        }

        return true;
       
    }

    public function clone_user_stripe_accounts($existingActivatedStripeKeyId,$providedDetailsKeyId,$providedDetailsKey)
    {
        $response['status'] = 'success';

        /*get existing stripe connected accounts */
        $arrExistingStripeCloneAccounts = StripeAccountDetailsModel::where('admin_stripe_key_id',$existingActivatedStripeKeyId)
                                ->get()->toArray();


        if(isset($arrExistingStripeCloneAccounts) && count($arrExistingStripeCloneAccounts) > 0)
        {
            foreach ($arrExistingStripeCloneAccounts as $key => $arrCloneAccounts) 
            {
                $objVerifyUserStripeAccount = StripeAccountDetailsModel::where('admin_stripe_key_id',$providedDetailsKeyId)
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

                        if(isset($createdAccount['status']) &&$createdAccount['status'] == 'Error')
                       {
                        $response['status'] = 'error';
                        $response['description'] = isset($createdAccount['description'])?$createdAccount['description']:'Soemething went wrong while clonning accounts';
                       }
                       else
                       {
                           $userAccData = [];

                           $userAccData['admin_stripe_key_id']  = $providedDetailsKeyId;
                           $userAccData['stripe_acc_id']        = $createdAccount['account_id'];
                           $userAccData['user_id']              = $arrCloneAccounts['user_id'];
                           $userAccData['vendor_stripe_key_id'] = $arrCloneAccounts['vendor_stripe_key_id'];
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

    
}
