<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\InfluencerPromoCodeModel;
use App\Models\InfluencerSettingModel;
use App\Models\UserModel;
use App\Models\PromoCodeInfluencerMappingModel;
use App\Common\Traits\MultiActionTrait;

use Validator;
use DB;
use Datatables;
use Sentinel;
use Flash;

class InfluencerPromoCodeController extends Controller
{
    use MultiActionTrait;

    public function __construct(
    								InfluencerPromoCodeModel $InfluencerPromoCodeModel, 
                                    InfluencerSettingModel $InfluencerSettingModel,
                                    UserModel $UserModel,
                                    PromoCodeInfluencerMappingModel $PromoCodeInfluencerMappingModel
                               )
    {
       $this->arr_view_data              = [];
       $this->InfluencerPromoCodeModel   = $InfluencerPromoCodeModel;
       $this->BaseModel                  = $this->InfluencerPromoCodeModel;
       $this->InfluencerSettingModel     = $InfluencerSettingModel;
       $this->UserModel                  = $UserModel;
       $this->PromoCodeInfluencerMappingModel = $PromoCodeInfluencerMappingModel;

       $this->module_title           = 'Influencer Promo Code';
       $this->module_view_folder     = 'maker.influencer_promo_code';
       $this->maker_panel_slug       = config('app.project.maker_panel_slug');
       $this->module_url_path        = url($this->maker_panel_slug.'/influencer_promo_code');
    }

    public function index()
    {
        /*If influencer mode is off then redirect to access forbidden page*/
        $b2c_privacy_settings = get_b2c_privacy_settings_detail();

        if(isset($b2c_privacy_settings['is_influencer_module_on']) &&
                 $b2c_privacy_settings['is_influencer_module_on'] == 0)
        {
           return view('errors.403');
        }

        $arr_influencer_settings = [];
        $obj_influencer_settings = $this->InfluencerSettingModel->first();

        if($obj_influencer_settings){
            $arr_influencer_settings = $obj_influencer_settings->toArray();
        }

        $arr_influencer = $this->get_influencer_records();

        $this->arr_view_data['arr_influencer']          = $arr_influencer;
        $this->arr_view_data['arr_influencer_settings'] = $arr_influencer_settings;

        $this->arr_view_data['module_title']     = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;
        $this->arr_view_data['page_title']       = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }	


    public function get_influencer_promo_code_listing(Request $request)
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();

        $arr_search_column = $request->input('column_filter');

        if($user)
        {
            $loggedInUserId = $user->id;
        }

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

                            "CONCAT(".$prefix_user_tbl.".first_name,' ',"
                                              .$prefix_user_tbl.".last_name) as user_name"
                                
                         ))

                        ->leftJoin($promo_code_influencer_mapping_tbl,$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id','=',$influencer_promo_code_tbl.'.id')

                        ->leftJoin($user_tbl,$user_tbl.'.id','=',$promo_code_influencer_mapping_tbl.'.influencer_id')
                        
                        ->where($prefix_influencer_promo_code_tbl.'.vendor_id',$loggedInUserId)
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
                       
                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }

    public function create()
    {       
        /*If influencer mode is off then redirect to access forbidden page*/
        $b2c_privacy_settings = get_b2c_privacy_settings_detail();

        if(isset($b2c_privacy_settings['is_influencer_module_on']) &&
                 $b2c_privacy_settings['is_influencer_module_on'] == 0)
        {
           return view('errors.403');
        }

        $this->arr_view_data['module_title']       = $this->module_title;
        $this->arr_view_data['page_title']         = 'Create '.$this->module_title;
        $this->arr_view_data['module_url_path']    = $this->module_url_path;

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function edit($enc_id)
    {       
        /*If influencer mode is off then redirect to access forbidden page*/
        $b2c_privacy_settings = get_b2c_privacy_settings_detail();

        if(isset($b2c_privacy_settings['is_influencer_module_on']) &&
                 $b2c_privacy_settings['is_influencer_module_on'] == 0)
        {
           return view('errors.403');
        }

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

        $title        = isset($form_data['promo_code_name'])?$form_data['promo_code_name']:false;


        /*---------check promo code duplication--------*/

        $is_exists    = $this->BaseModel->where('promo_code_name',$title)->where('vendor_id',$user_id);

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

        $promo_code->vendor_id          = $user_id or '';
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
    
    public function perform_activate($id)
    {   
        $static_page = $this->BaseModel->where('id',$id)->first();
        
        if($static_page)
        {
           return $static_page->update(['is_active'=>'1']);
        }

        return FALSE;
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

    /* Assign Promo Code to influencer */
    public function assign_promo_code(Request $request)
    {
        $form_data = $request->all();

        $influencer_promo_code_id = isset($form_data['current_promo_code_id'])?base64_decode($form_data['current_promo_code_id']):'';

        $influencer_id = isset($form_data['influencer_id'])?$form_data['influencer_id']:'';


        /* Check whether promo code is already assigned to any influencer or not */

        $assigned_count = $this->PromoCodeInfluencerMappingModel->where('influencer_promo_code_id',$influencer_promo_code_id)->count();


        if($assigned_count > 0){
            $response['status']      = 'error';
            $response['description'] = 'Promo code is already assigned to influencer.';
            return response()->json($response);
        }



        $arr_input_data =   [
                                'influencer_promo_code_id' => $influencer_promo_code_id,
                                'influencer_id'            => $influencer_id
                            ];

        $is_assigned = $this->PromoCodeInfluencerMappingModel->create($arr_input_data);

        if($is_assigned)
        {
            /* Update is_assigned field in influencer_promo_code table */
            $this->BaseModel->where('id',$influencer_promo_code_id)->update(['is_assigned'=>'1']);

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

}
    