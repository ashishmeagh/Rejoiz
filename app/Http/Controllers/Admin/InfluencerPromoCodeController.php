<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\InfluencerPromoCodeModel;
use App\Models\InfluencerSettingModel;
use App\Models\UserModel;
use App\Models\PromoCodeInfluencerMappingModel;
use App\Common\Traits\MultiActionTrait;

use App\Models\StripeAccountDetailsModel;
use App\Models\GeneralSettingModel;

use App\Common\Services\StripePaymentService;
use App\Common\Services\InfluencerService;
use App\Common\Services\EmailService;
use App\Models\SiteSettingModel;
use App\Models\UserStripeAccountDetailsModel;



use Validator;
use DB;
use Datatables;
use Sentinel;
use Flash;

class InfluencerPromoCodeController extends Controller
{   
    use MultiActionTrait;
	
	public function __construct(InfluencerPromoCodeModel $InfluencerPromoCodeModel,
								InfluencerSettingModel $InfluencerSettingModel,
								UserModel $UserModel,
								PromoCodeInfluencerMappingModel $PromoCodeInfluencerMappingModel,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                GeneralSettingModel $GeneralSettingModel,
                                StripePaymentService $StripePaymentService,
                                InfluencerService $InfluencerService,
                                EmailService $EmailService,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel)
	{
		$this->arr_view_data              = [];
       	$this->InfluencerPromoCodeModel   = $InfluencerPromoCodeModel;
       	$this->BaseModel                  = $this->InfluencerPromoCodeModel;
       	$this->InfluencerSettingModel     = $InfluencerSettingModel;
       	$this->UserModel                  = $UserModel;
       	$this->PromoCodeInfluencerMappingModel = $PromoCodeInfluencerMappingModel;

        $this->StripeAccountDetailsModel = $StripeAccountDetailsModel;
        $this->GeneralSettingModel       = $GeneralSettingModel;
        $this->UserStripeAccountDetailsModel = $UserStripeAccountDetailsModel;
        $this->StripePaymentService      = $StripePaymentService;
        $this->InfluencerService         = $InfluencerService;
        $this->EmailService              = $EmailService;

       	$this->module_title           = 'Influencer Promo Code';
       	$this->module_view_folder     = 'admin.influencer_promo_code';
       	$this->admin_panel_slug       = config('app.project.admin_panel_slug');
       	$this->module_url_path        = url($this->admin_panel_slug.'/influencer_promo_code');
	}

	/*
		index() - It will show the listing of influencer promo code
	*/
    public function index()
    {
        $arr_influencer_settings = [];
        $obj_influencer_settings = $this->InfluencerSettingModel->first();

        if($obj_influencer_settings){
            $arr_influencer_settings = $obj_influencer_settings->toArray();
        }

        $arr_influencer = $this->get_influencer_records();

        $this->arr_view_data['arr_influencer']          = $arr_influencer;
        $this->arr_view_data['arr_influencer_settings'] = $arr_influencer_settings;

        $this->arr_view_data['module_title']     = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['admin_panel_slug'] = $this->admin_panel_slug;
        $this->arr_view_data['page_title']       = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_influencer_records()
    {
        $arr_influencer  = [];

        $influencer_role = Sentinel::findRoleBySlug('influencer'); 

        if($influencer_role){
            $arr_influencer =   $this->UserModel
                                    ->with(['role_details'])
                                    ->whereHas('role_details',function($q) use($influencer_role){
                                        return $q->where('role_id',$influencer_role->id);
                                })
                                ->where('status',1)
                                ->get()
                                ->toArray();
        }

        return $arr_influencer;
    }

    public function get_influencer_promo_code_listing(Request $request)
    {
        // $loggedInUserId = 0;
        // $user = Sentinel::check();

        $arr_search_column = $request->input('column_filter');

       /* if($user)
        {
            $loggedInUserId = $user->id;
        }*/

        $influencer_promo_code_tbl              = $this->BaseModel->getTable();        
        $prefix_influencer_promo_code_tbl       = DB::getTablePrefix().$this->BaseModel->getTable();

        $promo_code_influencer_mapping_tbl        = $this->PromoCodeInfluencerMappingModel->getTable();
        $prefix_promo_code_influencer_mapping_tbl = DB::getTablePrefix().$this->PromoCodeInfluencerMappingModel->getTable();

        $user_tbl        = $this->UserModel->getTable();
        $prefix_user_tbl = DB::getTablePrefix().$this->UserModel->getTable();

        $obj_promo_code = DB::table($influencer_promo_code_tbl)
                              ->select(DB::raw($prefix_influencer_promo_code_tbl.".id,".  
                             $prefix_influencer_promo_code_tbl.'.promo_code_name,'.
                             $prefix_influencer_promo_code_tbl.".vendor_id,".
                             $prefix_influencer_promo_code_tbl.".is_active,".  
                             $prefix_influencer_promo_code_tbl.".is_assigned,".

                             $prefix_promo_code_influencer_mapping_tbl.".assigned_date,".
                             $prefix_promo_code_influencer_mapping_tbl.".expiry_date,".

                            "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                                              .$prefix_user_tbl.".last_name) as user_name"
                                
                         ))

                        ->leftJoin($promo_code_influencer_mapping_tbl,$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id','=',$influencer_promo_code_tbl.'.id')

                        ->leftJoin($user_tbl,$user_tbl.'.id','=',$promo_code_influencer_mapping_tbl.'.influencer_id')
                        
                        ->where($prefix_influencer_promo_code_tbl.'.vendor_id',0)
                        ->orderBy($influencer_promo_code_tbl.'.created_at','DESC');

        /* ---------------- Filtering Logic ----------------------------------*/                           
         
            if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
            {
                $search_term    = $arr_search_column['q_promo_code'];
                $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.promo_code_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
            {
                $search_term    = $arr_search_column['q_user_name'];
                $obj_promo_code = $obj_promo_code->having('user_name','LIKE', '%'.$search_term.'%');
            }


            if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
            {
              $search_term    = $arr_search_column['q_status'];

              $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.is_active','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_is_assigned']) && $arr_search_column['q_is_assigned']!="")
            {
              $search_term    = $arr_search_column['q_is_assigned'];

              $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.is_assigned','LIKE', '%'.$search_term.'%');
            }
        
            $current_context = $this;

            $json_result     = Datatables::of($obj_promo_code);

            /* Modifying Columns */
            $json_result =  $json_result->editColumn('id',function($data) use ($current_context)
                            {
                                if(isset($data->id) && $data->id != '')
                                {
                                   return  $id = base64_encode($data->id);
                                }
                                
                            })
                            
                            ->editColumn('promo_code_name',function($data)use ($current_context) 
                            {   
                               return $promo_code_name = isset($data->promo_code_name)?$data->promo_code_name:'N/A';
                            })

                            ->editColumn('is_active',function($data)use ($current_context)
                            {
                              return $status = $data->is_active;
                            })

                            ->editColumn('user_name',function($data)use($current_context)
                            {
                                return $user_name = isset($data->user_name)?$data->user_name:'N/A';
                            })

                            ->editColumn('is_assigned',function($data)use ($current_context) 
                            {   
                               return $is_assigned = $data->is_assigned;
                            })
                            ->editColumn('assigned_date',function($data)use($current_context)
                            {
                                return isset($data->assigned_date)?$data->assigned_date:'N/A';

                            })
                            ->editColumn('expiry_date',function($data)use($current_context)
                            {
                                return isset($data->expiry_date)?$data->expiry_date:'N/A';
                            })

                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }

    /*
		create() - Admin create the promo code for influencer
    */
    public function create()
    {
    	$this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Create '.$this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function edit($enc_id)
    {       
        $promo_code_details = [];
        $promo_code_id      = base64_decode($enc_id);

        $obj_promo_code_details = $this->BaseModel->where('id',$promo_code_id)->first();

        if($obj_promo_code_details)
        {
           $promo_code_details = $obj_promo_code_details->toArray();
        }

        /* If promo code is already assigned then redirect vendor to access forbidden page */
        if(isset($promo_code_details['is_assigned']) && 
                 $promo_code_details['is_assigned'] == 1)
        {
            return view('errors.403');
        }

        $this->arr_view_data['module_title']      = $this->module_title;
        $this->arr_view_data['promo_code_arr']    = $promo_code_details;
        $this->arr_view_data['page_title']        = 'Edit '.$this->module_title;
        $this->arr_view_data['module_url_path']   = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function store(Request $request)
    {
        $form_data = $request->all();
    
        $user      = \Sentinel::getUser();

        if($user)
        {
            $user_id = $user->id;
        }
          
        $arr_rules = ['promo_code_name'=>'required'];

        $validator = Validator::make($request->all(),$arr_rules);
        $response  = [];

        if($validator->fails())
        {   
            $response['status']      = 'warning';
            $response['description'] = 'Form validations failed, please check all fields.';

            return response()->json($response);
        }


        $promo_code_id = isset($request->enc_id)?base64_decode($request->enc_id):false;

        $title         = isset($form_data['promo_code_name'])?$form_data['promo_code_name']:false;

        /*---------check promo code duplication--------*/

        /*Here vendor_id means admin*/
        $is_exists    = $this->BaseModel->where('promo_code_name',$title);
        								// ->where('vendor_id',$user_id);

        if($promo_code_id)
        {
            $is_exists = $is_exists->where('id','<>',$promo_code_id)->count();
        }
        else
        {
            $is_exists = $is_exists->count();
        }

        if($is_exists)
        {
            $response['status']      = 'error';
            $response['description'] = 'Promo code is already exists.';
            return response()->json($response);
        }
 
        $promo_code = $this->BaseModel->firstOrNew(['id' =>$promo_code_id]);

        $promo_code->vendor_id          = 0;
        $promo_code->promo_code_name    = isset($request->promo_code_name)?trim($request->promo_code_name):'';
        // $promo_code->status             = 'Not Used';
    
        if(isset($form_data['is_active']) && $form_data['is_active']!='')
        { 
            $promo_code->is_active = '1';
        }
        else
        {
            $promo_code->is_active = '0';
        }

        $promo_code_details = $promo_code->save();


        if($promo_code_details)
        {

            $response['status']      = 'success';
            $response['description'] = 'Promo code has been saved.';

            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }

    public function change_status(Request $request)
    {  
      
        $id             = isset($request->promo_code_id)?base64_decode($request->promo_code_id):false;
        $status         = isset($request->status)?$request->status:false;

        $response_msg = '';

        if($status == 'activate')
        {
            $is_active = '1';
            $response_msg = 'Influencer promo code has been activated.';
        }
        else if($status == 'deactivate')
        {   
            $is_active = '0';
            $response_msg = 'Influencer promo code has been deactivated.';
        }

        $data['is_active'] = $is_active;

      
        $update = $this->BaseModel->where('id',$id)->update($data);


        if($update)
        {
            $response['status']      = 'success';
            $response['description'] =  $response_msg;
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }

    public function delete($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }
        if($this->perform_delete(base64_decode($enc_id)))
        {   
            Flash::success($this->module_title.' has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while '.str_plural($this->module_title).' deletion.');
        }

        return redirect()->back();

    }

    public function perform_delete($id)
    {   
        $promo_code_arr = [];

        /*check if promo code already used so cant delete*/
        $promo_code_detail = $this->BaseModel->where('id',$id)->first();
  
        if(isset($promo_code_detail))
        {
            $promo_code_arr = $promo_code_detail->toArray();
        }

  
        if($promo_code_arr['is_assigned'] == '1')
        {
            Flash::error("Promo Code ".$promo_code_arr['promo_code_name']." has already used it can't be deleted.");
            return FALSE;
        }
        else
        {
            $delete = $this->BaseModel->where('id',$id)->delete();
    
            if($delete)
            {  
                // $result = $this->PromotionsModel->where('promo_code',$id)->delete();
               return TRUE;
            }

            return FALSE;

        }
        /*------------------------------------------------*/
       
    }


    public function activate($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_activate(base64_decode($enc_id)))
        {
            Flash::success($this->module_title. ' has been activated.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' activation.');
        }

        return redirect()->back();
    }

    public function perform_activate($id)
    {   
        $static_page = $this->BaseModel->where('id',$id)->first();
        
        if($static_page)
        {
           return $static_page->update(['is_active'=>'1']);
        }

        return FALSE;
    }

    public function deactivate($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_deactivate(base64_decode($enc_id)))
        {
            Flash::success($this->module_title. ' has been deactivated.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deactivation.');
        }

        return redirect()->back();
    }

    public function perform_deactivate($id)
    {   
        $static_page = $this->BaseModel->where('id',$id)->first();
        
        if($static_page)
        {
           return $static_page->update(['is_active'=>'0']);
        }

        return FALSE;
    }

    
    public function multi_action(Request $request)
    {
        $arr_rules = array();
        $arr_rules['multi_action'] = "required";
        $arr_rules['checked_record'] = "required";

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Flash::error('Please select '.$this->module_title.' to perform multi actions.');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $multi_action = $request->input('multi_action');
        $checked_record = $request->input('checked_record');

        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Flash::error('Error occurred while doing multi action.');
            return redirect()->back();
        }

        foreach ($checked_record as $key => $record_id) 
        {  
           
            if($multi_action=="delete")
            {   //dd($record_id);
               $this->perform_delete(base64_decode($record_id));    
               Flash::success($this->module_title.' has been deleted.'); 
            } 
            elseif($multi_action=="activate")
            {
               $this->perform_activate(base64_decode($record_id)); 
               Flash::success($this->module_title.' has been activated.'); 
            }
            elseif($multi_action=="deactivate")
            {
               $this->perform_deactivate(base64_decode($record_id));    
               Flash::success($this->module_title.' has been deactivated.');  
            }
        }

        return redirect()->back();
    }

    /* Assign Promo Code to influencer */
    public function assign_promo_code(Request $request)
    {
        $form_data = $request->all();

        $influencer_promo_code_id = isset($form_data['current_promo_code_id'])?base64_decode($form_data['current_promo_code_id']):'';

        $influencer_id = isset($form_data['influencer_id'])?$form_data['influencer_id']:'';

        $promo_code_name = isset($form_data['current_promo_code_name'])?$form_data['current_promo_code_name']:'';


        /* Check whether promo code is already assigned to any influencer or not */

        $assigned_count = $this->PromoCodeInfluencerMappingModel->where('influencer_promo_code_id',$influencer_promo_code_id)->count();


        if($assigned_count > 0){
            $response['status']      = 'error';
            $response['description'] = 'Promo code is already assigned to influencer.';
            return response()->json($response);
        }

        /*Get Influencer Settings for promo code validity (in days)*/
        $arr_influencer_settings = [];
        $obj_influencer_settings = $this->InfluencerSettingModel->first();

        if($obj_influencer_settings){
            $arr_influencer_settings = $obj_influencer_settings->toArray();
        }

        $promo_code_validity_in_days =  isset($arr_influencer_settings['promo_code_validity_in_days'])?$arr_influencer_settings['promo_code_validity_in_days']:0;

        $expiry_date = date('Y-m-d', strtotime('+'.$promo_code_validity_in_days.' days'));

        $arr_input_data =   [
                                'influencer_promo_code_id' => $influencer_promo_code_id,
                                'influencer_id'            => $influencer_id,
                                'assigned_date'            => date('Y-m-d'),
                                'expiry_date'              => $expiry_date
                            ];

        $is_assigned = $this->PromoCodeInfluencerMappingModel->create($arr_input_data);

        if($is_assigned)
        {
            /* Update is_assigned field in influencer_promo_code table */
            $this->BaseModel->where('id',$influencer_promo_code_id)->update(['is_assigned'=>'1']);


            /* Send Notification to Influencer (START)*/
            $admin_id = 0;
            $user = Sentinel::check();

            if($user)
            {
                $admin_id = $user->id;
            }

            $influencer_panel_slug = config('app.project.influencer_panel_slug');
            $view_href             = url($influencer_panel_slug.'/promo_code');

            /*Get site setting data from helper*/
            $site_setting_arr = get_site_settings(['site_name','website_url']);

            $arr_notify_data                 = [];
            $arr_notify_data['from_user_id'] = $admin_id or '';
            $arr_notify_data['to_user_id']   = $influencer_id or '';

            $arr_notify_data['description']  = $site_setting_arr['site_name'].' has assigned promo code. Promo Code: '.$promo_code_name;

            $arr_notify_data['title']        = 'Assigned Promo Code';
            $arr_notify_data['type']         = 'influencer';
            $arr_notify_data['link']         = $view_href;

            $this->InfluencerService->save_notification($arr_notify_data);

            /* Send Notification to Influencer (END)*/




            /* Send Email to Influencer */

            $redirection_url = '<a target="_blank" style="background:#fa8612; color:#fff; text-align:center;border-radius: 4px; padding: 15px 18px; text-decoration: none;" href="'.$view_href.'">View Details</a><br/>';

            $user = Sentinel::findById($influencer_id);
        
            if(isset($user) && $user)
            {
                $arr_user = $user->toArray(); 
              
                /*Get site setting data from helper*/
                $site_setting_arr = get_site_settings(['site_name','website_url']);

                $arr_built_content = ['USER_FNAME'           => $arr_user['first_name'],
                                      'APP_NAME'             => $site_setting_arr['site_name'],
                                      'REDIRECTION_URL'      => isset($redirection_url)?$redirection_url:'',
                                      'EMAIL_DESCRIPTION'    => isset($arr_notify_data['description'])?$arr_notify_data['description']:'',
                                      'SITE_URL'             => $site_setting_arr['website_url']
                                     ];

                $arr_mail_data                          = [];
                $arr_mail_data['email_template_id']     = '63';
                $arr_mail_data['arr_built_content']     = $arr_built_content;
                $arr_mail_data['arr_user']              = $arr_user;

                $email_status  = $this->EmailService->send_mail($arr_mail_data);
                
            }                

            /* Send Email to Influencer (END)*/

            $response['status']      = 'success';
            $response['description'] = 'Promo code has been assigned to influencer.';
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
        }

        return response()->json($response); 
    }

    /*
        Author - Bhavana
               - Check whether influencer stripe account connected to admin stripe account, if yes then send is_connected = 1 else send is_connected = 0
    */
    public function check_stripe_account_connected_or_not(Request $request)
    {
        $influencer_id = $request->input('influencer_id');

        if(isset($influencer_id))
        {
            /*Check whether influencer is connected to admin stripe account or not*/
            $obj_stripe_account_details = $this->StripeAccountDetailsModel
                                                ->where('user_id',$influencer_id)
                                                ->first();

            /*If yes then send is_connected = 1 else 0 in response else send the false*/
            if($obj_stripe_account_details)
            {
                $response['data'] = [
                                        'influencer_id'=>$influencer_id,
                                        'is_connected'=>'1'
                                    ];
            }
            else
            {
                $response['data'] = [
                                        'influencer_id'=>$influencer_id,
                                        'is_connected'=>'0'
                                    ];
            }
            
            $response['status'] = 'success';

        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
        }

        return response()->json($response);
    }

    public function send_stripe_acc_creation_link(Request $request)
    {
        $user_id = $request->input('user_id');

        $admin_id = get_admin_id();


        if(isset($user_id))
        {
             /* get vendor client id */
          $client_id = $this->UserStripeAccountDetailsModel->where('user_id',$admin_id)
                                                      ->where('is_active','1')
                                                      ->pluck('client_id')
                                                      ->first();

            if($client_id!='')
            {

                $connection_response = $this->StripePaymentService->connection_request($user_id);
         
                if($connection_response)
                {
                   $response['status']        = 'success';
                   $response['description']   = 'Link has been sent.';
                }
                else
                {
                   $response['status']      = 'error';
                   $response['description'] = 'Something went wrong, please try again.';
                }
                }
            else
            {
               $response['status']      = 'error';
               $response['description'] = 'Client ID is missing, please update your Client ID from account settings.';
            }
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
        }
      
        return response()->json($response);
    }
}
