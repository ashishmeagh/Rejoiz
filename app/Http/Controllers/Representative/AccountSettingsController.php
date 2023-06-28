<?php

namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\CountryModel;
use App\Common\Services\GeneralService;



use Sentinel;
use Flash;
use Session;
use Validator;



class AccountSettingsController extends Controller
{
    
	public function __construct(UserModel $user,RepresentativeModel $RepresentativeModel,                                CountryModel $CountryModel,GeneralService $GeneralService)
    {
    	$this->arr_view_data      = [];
    	$this->module_title       = "Account Settings";
    	$this->module_view_folder = 'representative.account_settings'; 
    	$this->representative_panel_slug   = config('app.project.representative_panel_slug');
    	$this->module_url_path    = url($this->representative_panel_slug);
        $this->UserModel          = $user;
        $this->RepresentativeModel= $RepresentativeModel;
        $this->CountryModel       = $CountryModel;
        $this->GeneralService     = $GeneralService;
        $this->profile_image      = base_path().'/storage/app/';
    }

    public function index()
    {
        $arr_data  = [];
        
        $obj_data  = Sentinel::getUser();

        $loggedIn_userId = 0;
        if($obj_data)
        {
            $loggedIn_userId = $obj_data->id;
        }  

        // $obj_rep = $this->UserModel->where('id',$loggedIn_userId)->with(['representative_details'])->first();

        $obj_rep = $this->RepresentativeModel->with(['get_user_details','get_area_details','sales_manager_details.get_user_data'])
                                                  ->where('user_id',$loggedIn_userId)
                                                  ->first();


        if($obj_rep)
        {
           $arr_data = $obj_rep->toArray();    
        }
     
        if(isset($arr_data) && sizeof($arr_data)<=0)
        {
            //return redirect($this->admin_url_path.'/login');
            return redirect(url('/').'/login');
        }

     //   dd($arr_data);
      
        $this->arr_view_data['arr_data']        = $arr_data;    
    	$this->arr_view_data['module_title']    = $this->module_title;
    	$this->arr_view_data['page_title'] 	    = 'Account Settings';
    	$this->arr_view_data['module_url_path'] = $this->module_url_path.'/account_settings';
        $country_arr                            = $this->CountryModel->orderBy('id','ASC')
                                                 ->get()
                                                 ->toArray();
                  
        $this->arr_view_data['country_arr']   = isset($country_arr)?$country_arr:'';
    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function update(Request $request)
    {
        $arr_rules = array();
        $form_data = $request->all();
       
        $obj_data  = Sentinel::getUser();

        $first_name = $obj_data->first_name;
        $last_name  = $obj_data->last_name;

        $arr_rules = [
                                        'first_name'=>'required',
                                        'last_name'=>'required',
                                        'email'=>'required|email',
                                        'country_id'=>'required',
                                        'post_code'=>'required'
                                     ];
         if(Validator::make($form_data,$arr_rules)->fails())
        {
             Flash::error('Form validation failed,please check form fields.');
            return redirect()->back();
        }
        
        if($this->UserModel->where('email',$request->input('email'))
                           ->where('id','!=',$obj_data->id)
                           ->count()==1)
        {
            Flash::error('This email id already present in our system, please try another one.');
            return redirect()->back();
        }

        if($request['country_id']== "" && $request['post_code']!="")
        {
           Flash::error('Invalid zip/postal code.');
            return redirect()->back(); 
        }

        $profile_file_path = '';
        if($request->hasFile('image'))
        {
            $profile_image =$request->file('image');

            if($profile_image!=null){
                $profile_file_path = $profile_image->store('profile_image');

                //Unlink old image
                if(isset($form_data['old_image']) && $form_data['old_image']!="")
                {
                   $old_img_path  =  $this->profile_image.$form_data['old_image'];
                   $this->GeneralService->unlink_old_image($old_img_path);
                }
            }

            //Validation for product image
                $file_extension = strtolower( $profile_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {   
                    Flash::error('please select valid file type.');
                        return redirect()->back();
                }



            $arr_data['profile_image']= $profile_file_path;
        }
        
        $arr_data['first_name']   = $request->input('first_name',null);
        $arr_data['last_name']    = $request->input('last_name',null);
        $arr_data['email']        = $request->input('email',null);
        $arr_data['post_code']    = $request->input('post_code',null);
        $arr_data['country_id']   = $request->input('country_id',null);



        $description = $request->input('description',null);
        $obj_data = Sentinel::update($obj_data, $arr_data);

        
        if($obj_data)
        {
            $update_representative = $this->RepresentativeModel->where('user_id',$obj_data->id)->update(['description'=>$description]);
           
            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
            /*$arr_event                 = [];
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_TITLE'] = $this->module_title;

            $this->save_activity($arr_event);*/
            /*----------------------------------------------------------------------*/
            //Flash::success(str_singular($this->module_title).' Updated Successfully'); 
            //Session::Flash('message','Account settings has been updated.'); 
            Flash::success('Account settings has been updated.'); 
        }
        else
        {
            Flash::error('Error occurred, while updating '.str_singular($this->module_title));  
        } 
      
        return redirect()->back();
    }

    public function logout()
    {
        Session::flush();
        Sentinel::logout();
        return redirect(url('/'));
    }

    public function change_password(Request $request)
    {
         $form_data = $request->all();
        
        if ($request->isMethod('get'))
        {
           
            $this->arr_view_data['page_title']      = "Change Password";
            $this->arr_view_data['module_title']    = "Change Password";
            $this->module_url_path    = url($this->representative_panel_slug.'/change_password');
            $this->arr_view_data['representative_panel_slug'] = $this->representative_panel_slug;
            $this->arr_view_data['module_url_path'] = $this->module_url_path;
           
            
            return view($this->module_view_folder.'.change_password',$this->arr_view_data);  
        }

        /*Check Validatons and display custom message*/
       /*$inputs = request()->validate([
        'current_password'=> 'required',
        'new_password' => 'required'
        ],
        [
          'current_password.required'=>'Please enter current password.',
          'new_password.required'=>'Please enter new password'
        ]);*/

         $arr_rules = [
                         'current_password'=>'required',
                         'new_password'    =>'required',
                         'new_password_confirmation'=> 'required|same:new_password'

                      ];
         if(Validator::make($form_data,$arr_rules)->fails())
        {
             Flash::error('Form validation failed, please check form fields.');
             return redirect()->back();
        }
        
      
      $user = Sentinel::check();

      $credentials = [];
      $credentials['password'] = $request->input('current_password');

      if (Sentinel::validateCredentials($user,$credentials)) 
      { 
        $new_credentials = [];
        $new_credentials['password'] = $request->input('new_password');

        if(Sentinel::update($user,$new_credentials))
        {
          //Flash::success('Password Change Successfully');
            //Session::flush();
            Flash::success('Your password has been changed successfully.');
            return redirect()->back();
        }
        else
        {
          Flash::error('Error occurred, while changing password.');
        }
      } 
      else
      {
        Flash::error('Invalid old password.');
      }       
      
      return redirect()->back();

        

          
    }
}
