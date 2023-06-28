<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\UserService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\InfluencerPromoCodeModel;
use App\Models\PromoCodeInfluencerMappingModel;

use App\Models\CustomerQuotesModel;
use App\Models\MakerModel;
use App\Models\CustomerModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\TransactionMappingModel;
use App\Models\StripeTransactionModel;
use App\Models\ActivationModel;
use App\Models\RetailerQuotesModel;

use App\Models\StripeAccountDetailsModel;
use App\Models\GeneralSettingModel;
use App\Models\UserStripeAccountDetailsModel;


use App\Common\Traits\MultiActionTrait;


use DB;
use Validator;
use Sentinel;
use Flash;
use DataTable;
use DateTime;
use Carbon\Carbon;
use App\Common\Services\PdfReportService;
use App\Common\Services\ReportService;
use PDF;

class InfluencerController extends Controller
{   
    use MultiActionTrait;

    public function __construct(UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                InfluencerPromoCodeModel $InfluencerPromoCodeModel,
                                PromoCodeInfluencerMappingModel $PromoCodeInfluencerMappingModel,

                                CustomerQuotesModel $CustomerQuotesModel,
                                MakerModel $MakerModel,
                                CustomerModel $CustomerModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                TransactionMappingModel $TransactionMappingModel,
                                StripeTransactionModel $StripeTransactionModel,

                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                GeneralSettingModel $GeneralSettingModel,

                                UserService $UserService,
                                CustomerOrderService $CustomerOrderService,
                                StripePaymentService $StripePaymentService,
                                ActivationModel $ActivationModel,
                                UserStripeAccountDetailsModel $UserStripeAccountDetailsModel,
                                PdfReportService $PdfReportService,
                                ReportService $ReportService,
                                GeneralService $GeneralService,
                                RetailerQuotesModel $RetailerQuotesModel

                            )
    {
        $this->UserModel          = $UserModel;
        $this->BaseModel          = $UserModel;
        
        $this->RoleModel          = $RoleModel;
        $this->RoleUsersModel     = $RoleUsersModel;

        $this->InfluencerPromoCodeModel        = $InfluencerPromoCodeModel;
        $this->PromoCodeInfluencerMappingModel = $PromoCodeInfluencerMappingModel;


        $this->CustomerQuotesModel        = $CustomerQuotesModel;
        $this->MakerModel                 = $MakerModel;
        $this->CustomerModel              = $CustomerModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->StripeTransactionModel     = $StripeTransactionModel;

        $this->StripeAccountDetailsModel = $StripeAccountDetailsModel;
        $this->GeneralSettingModel       = $GeneralSettingModel;
        $this->UserStripeAccountDetailsModel       = $UserStripeAccountDetailsModel;

        $this->UserService          = $UserService;
        $this->CustomerOrderService = $CustomerOrderService;
        $this->StripePaymentService = $StripePaymentService;
        $this->ActivationModel      = $ActivationModel;
        $this->PdfReportService     = $PdfReportService;
        $this->ReportService        = $ReportService;
        $this->GeneralService          = $GeneralService;
        $this->RetailerQuotesModel          = $RetailerQuotesModel;

        
        $this->arr_view_data      = [];
        $this->module_title       = "Influencer";
        $this->module_view_folder = "admin.influencer";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/influencer");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');

        $this->customer_orders_path = url($this->curr_panel_slug.'/customer_orders');

        $this->role = 'influencer';
        $this->profile_image      =  base_path().'/storage/app/';

    }

    public function index()
    {   
        $this->arr_view_data['page_title']      = str_plural( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_influencer_list(Request $request)
    {

        $arr_search_column = $request->input('column_filter');

        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_table        =  $this->RetailerQuotesModel->getTable();
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

        $stripe_account_details_table        = $this->StripeAccountDetailsModel->getTable();
        $prefix_stripe_account_details_table = DB::getTablePrefix().$stripe_account_details_table;

        $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status,".
                                     $prefix_user_table.".is_approved,".
                                     $prefix_user_table.".contact_no as contact_no,".
                                     $prefix_user_table.".country_code,".
                                     $prefix_user_table.".created_at,".
                                     $prefix_user_table.".influencer_code,".
                                     $role_table.".slug as slug,".
                                     $role_table.".name as name,".
                                     
                                     $prefix_stripe_account_details_table.".user_id as stripe_influencer_id,".
                                     $prefix_stripe_account_details_table.".stripe_acc_id,".
                                     $prefix_stripe_account_details_table.".stripe_customer_id,".

                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
                        ->leftJoin($stripe_account_details_table,$stripe_account_details_table.'.user_id','=',$user_table.'.id')
                        ->where($role_table.'.slug','=',$this->role)
                        ->where($prefix_user_table.'.influencer_code','!=',null)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC');

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_user_name']) && $arr_search_column['q_user_name']!="")
        {
            $search_term      = $arr_search_column['q_user_name'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        if(isset($arr_search_column['q_influencer_code']) && $arr_search_column['q_influencer_code']!="")
        {
            /* $search_term      = $arr_search_column['q_influencer_code'];
            $obj_user = $obj_user->where($user_table.'.influencer_code','=', $search_term); */

            $search_term      = $arr_search_column['q_influencer_code'];
            $obj_user = $obj_user->where($user_table.'.influencer_code','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term      = $arr_search_column['q_contact_no'];
            $obj_user = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            $obj_user = $obj_user->where($user_table.'.status','=', $search_term);
        }

        

        if(isset($arr_search_column['q_date']) && $arr_search_column['q_date']!="")
        {
            
            $search_term  = $arr_search_column['q_date'];

            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_user     = $obj_user->where($user_table.'.created_at','LIKE', '%'.$date.'%');
        }

        $json_result     =  \Datatables::of($obj_user);

        $json_result     =  $json_result->blacklist(['id']);
        
        $json_result     =  $json_result->editColumn('id',function($data)
                            {
                                if(isset($data->id) && $data->id != '')
                                {
                                   return  $id = base64_encode($data->id);
                                }
                            })                          
                            ->editColumn('status',function($data)
                            {
                                return $status = $data->status;
                            })
                            ->editColumn('influencer_code',function($data)
                            {
                                return $influencer_code = $data->influencer_code;
                            })
                            ->editColumn('contact_no',function($data)
                            {
                                if($data->country_code != "")
                                {
                                    $countryCode = $data->country_code;
                                    $data->contact_no = str_replace($countryCode, "", $data->contact_no);
                                    $contact_no = $countryCode .'-'.get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                                else
                                {
                                    $contact_no = get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                            })
                            ->editColumn('is_stripe_connected',function($data)
                            {
                                $stripe_influencer_id = isset($data->stripe_influencer_id)?$data->stripe_influencer_id:'';
                                $stripe_acc_id        = isset($data->stripe_acc_id)?$data->stripe_acc_id:'';
                                $stripe_customer_id   = isset($data->stripe_customer_id)?$data->stripe_customer_id:'';

                                if($stripe_influencer_id != '' && 
                                   $stripe_acc_id != '' &&  
                                   $stripe_customer_id != '')
                                {
                                    return true;
                                }
                                else
                                {
                                    return false;
                                }
                            })

                            ->editColumn('registration_date',function($data)
                            {

                                $date = '';

                                $date = isset($data->created_at)?us_date_format($data->created_at):'';

                                return $date;
                            })
                            
                            ->editColumn('orders', function($data)
                            {
                                $order_count = get_influencer_order_count($data->influencer_code);
                                $order_data = [];
                                
                                $order_data['completed'] = sprintf("%04d", $order_count['complete_order']);
                                $order_data['pending'] = sprintf("%04d", $order_count['pending_order']);
                                $order_data['cancelled'] = sprintf("%04d", $order_count['cancelled_order']);
                                
                                return $order_data;
                            })
                            
                            ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }

    // public function get_assigned_promo_code_listing(Request $request)
    // {
    //     /*$loggedInUserId = 0;
    //     $user = Sentinel::check();*/

    //     $arr_search_column = $request->input('column_filter');

    //     $enc_influencer_id = $request->input('influencer_id');

    //     $influencer_id     = isset($enc_influencer_id)?base64_decode($enc_influencer_id):0;

    //     /*if($user)
    //     {
    //         $loggedInUserId = $user->id;
    //     }*/

    //     $influencer_promo_code_tbl              = $this->InfluencerPromoCodeModel->getTable();        
    //     $prefix_influencer_promo_code_tbl       = DB::getTablePrefix().$this->InfluencerPromoCodeModel->getTable();

    //     $promo_code_influencer_mapping_tbl        = $this->PromoCodeInfluencerMappingModel->getTable();
    //     $prefix_promo_code_influencer_mapping_tbl = DB::getTablePrefix().$this->PromoCodeInfluencerMappingModel->getTable();

    //     $user_tbl        = $this->UserModel->getTable();
    //     $prefix_user_tbl = DB::getTablePrefix().$this->UserModel->getTable();

    //     /*$maker_tbl        = $this->MakerModel->getTable();
    //     $prefix_maker_tbl = DB::getTablePrefix().$this->MakerModel->getTable();*/

    //     $obj_promo_code = DB::table($promo_code_influencer_mapping_tbl)
    //                           ->select(DB::raw($promo_code_influencer_mapping_tbl.".id,".  
    //                          $promo_code_influencer_mapping_tbl.'.influencer_promo_code_id,'.
    //                          $promo_code_influencer_mapping_tbl.".influencer_id,".

    //                          $prefix_influencer_promo_code_tbl.'.promo_code_name'

    //                          /*$prefix_maker_tbl.'.company_name,'.*/

    //                          /*"CONCAT(".$prefix_user_tbl.".first_name,' ',"
    //                                    .$prefix_user_tbl.".last_name) as vendor_name"*/
    //                      ))

    //                     ->leftJoin($influencer_promo_code_tbl,$influencer_promo_code_tbl.'.id','=',$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id')

    //                     /*->leftJoin($user_tbl,$user_tbl.'.id','=',$influencer_promo_code_tbl.'.vendor_id')*/
                        
    //                     ->leftJoin($maker_tbl,$maker_tbl.'.user_id','=',$influencer_promo_code_tbl.'.vendor_id')
                        
    //                     ->where($prefix_promo_code_influencer_mapping_tbl.'.influencer_id',$influencer_id)
    //                     ->orderBy($promo_code_influencer_mapping_tbl.'.created_at','DESC');


    //     if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
    //     {
    //         $search_term    = $arr_search_column['q_promo_code'];
    //         $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.promo_code_name','LIKE', '%'.$search_term.'%');
    //     }

    //     /*if(isset($arr_search_column['q_vendor_name']) && $arr_search_column['q_vendor_name']!="")
    //     {
    //         $search_term    = $arr_search_column['q_vendor_name'];
    //         $obj_promo_code = $obj_promo_code->having('vendor_name','LIKE', '%'.$search_term.'%')
    //                                         ->Orhaving('company_name','LIKE', '%'.$search_term.'%');
    //     }*/

    //     $current_context = $this;

    //     $json_result     = \Datatables::of($obj_promo_code);

    //     /* Modifying Columns */
    //     $json_result =  $json_result->editColumn('id',function($data) use ($current_context)
    //                     {
    //                         if(isset($data->id) && $data->id != '')
    //                         {
    //                            return  $id = base64_encode($data->id);
    //                         }
                            
    //                     })
    //                     ->editColumn('promo_code_name',function($data)use ($current_context) 
    //                     {   
    //                        return $promo_code_name = isset($data->promo_code_name)?$data->promo_code_name:'N/A';
    //                     })

    //                     /*->editColumn('vendor_name',function($data)use($current_context)
    //                     {
    //                         return $vendor_name = isset($data->vendor_name)?$data->vendor_name:'N/A';
    //                     })*/

    //                     ->make(true);

    //     $build_result = $json_result->getData();
        
    //     return response()->json($build_result);
    // }

    public function change_status(Request $request)
    {  
      
        $id             = isset($request->user_id)?base64_decode($request->user_id):false;
        $status         = isset($request->status)?$request->status:false;

        $response_msg = '';

        if($status == 'activate')
        {
            $is_active = '1';
            $response_msg = 'Influencer has been activated.';
        }
        else if($status == 'deactivate')
        {   
           $is_active = '0';
           $response_msg = 'Influencer has been deactivated.';
        }

        $data['status'] = $is_active;
     
        $update = $this->BaseModel->where('id',$id)->update($data);

        if($update)
        {  
            //activation completion
            $date = date('Y-m-d H:i:s');

            $activation_completed = $this->ActivationModel->where('user_id',$id)->pluck('completed')->first();
     
            if(isset($activation_completed) && $activation_completed == 0)
            {
               $result = $this->ActivationModel->where('user_id',$id)->update(['completed'=>1,'completed_at'=>$date]);   
            }  

            $response['status']      = 'success';
            $response['description'] = $response_msg;
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }

    public function view($enc_id)
    {   
        $arr_user = [];

        $user_id = base64_decode($enc_id);
    
        $arr_user     = $this->UserService->get_user_information($user_id,$this->role);

        $this->arr_view_data['arr_user']        = $arr_user;
        $this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.view',$this->arr_view_data);
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
        $user_details = $this->BaseModel->where('id',$id)->first();
  
        if(isset($user_details))
        {
            $delete = $this->BaseModel->where('id',$id)->delete();
    
            if($delete)
            {  
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
            //activation completion
            $date = date('Y-m-d H:i:s');

            $activation_completed = $this->ActivationModel->where('user_id',$id)->pluck('completed')->first();
            
            if(isset($activation_completed) && $activation_completed == 0)
            {
                $result = $this->ActivationModel->where('user_id',$id)->update(['completed'=>1,'completed_at'=>$date]);   
            }  
            
            return $static_page->update(['status'=>'1']);

            return TRUE;
        }

        return FALSE;

    }

    public function perform_deactivate($id)
    {   
        $static_page = $this->BaseModel->where('id',$id)->first();
        
        if($static_page)
        {
           return $static_page->update(['status'=>'0']);
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

    public function get_assigned_promo_code_listing(Request $request)
    {
        /*$loggedInUserId = 0;
        $user = Sentinel::check();*/

        $arr_search_column = $request->input('column_filter');

        $enc_influencer_id = $request->input('influencer_id');

        $influencer_id     = isset($enc_influencer_id)?base64_decode($enc_influencer_id):0;

        /*if($user)
        {
            $loggedInUserId = $user->id;
        }*/

        $influencer_promo_code_tbl              = $this->InfluencerPromoCodeModel->getTable();        
        $prefix_influencer_promo_code_tbl       = DB::getTablePrefix().$this->InfluencerPromoCodeModel->getTable();

        $promo_code_influencer_mapping_tbl        = $this->PromoCodeInfluencerMappingModel->getTable();
        $prefix_promo_code_influencer_mapping_tbl = DB::getTablePrefix().$this->PromoCodeInfluencerMappingModel->getTable();

        $user_tbl        = $this->UserModel->getTable();
        $prefix_user_tbl = DB::getTablePrefix().$this->UserModel->getTable();

        /*$maker_tbl        = $this->MakerModel->getTable();
        $prefix_maker_tbl = DB::getTablePrefix().$this->MakerModel->getTable();*/

        $customer_quotes_tbl        = $this->CustomerQuotesModel->getTable();
        $prefix_customer_quotes_tbl = DB::getTablePrefix().$this->CustomerQuotesModel->getTable();

        $obj_promo_code = DB::table($influencer_promo_code_tbl)
                            ->select(DB::raw($prefix_influencer_promo_code_tbl.'.id,'.
                                    $prefix_influencer_promo_code_tbl.'.promo_code_name,'.

                                    $prefix_promo_code_influencer_mapping_tbl.'.assigned_date,'.
                                    $prefix_promo_code_influencer_mapping_tbl.'.expiry_date,'.

                                    'COUNT(DISTINCT '.$prefix_customer_quotes_tbl.'.order_no) as promo_code_used_cnt'
                                    ))
                            ->join($promo_code_influencer_mapping_tbl,$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id','=',$influencer_promo_code_tbl.'.id')
                            ->leftjoin($customer_quotes_tbl,function($join) use($customer_quotes_tbl,$promo_code_influencer_mapping_tbl){

                                    $join->on($customer_quotes_tbl.'.influencer_id','=',$promo_code_influencer_mapping_tbl.'.influencer_id')
                                    ->on($customer_quotes_tbl.'.promo_code_id','=',$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id');
                                })
                            ->where($prefix_promo_code_influencer_mapping_tbl.'.influencer_id',$influencer_id)
                            ->where($influencer_promo_code_tbl.'.is_active',1)
                            ->groupBy($influencer_promo_code_tbl.'.promo_code_name')
                            ->orderBy($promo_code_influencer_mapping_tbl.'.created_at','DESC');


        if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
        {
            $search_term    = $arr_search_column['q_promo_code'];
            $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.promo_code_name','LIKE', '%'.$search_term.'%');
        }

        /*if(isset($arr_search_column['q_vendor_name']) && $arr_search_column['q_vendor_name']!="")
        {
            $search_term    = $arr_search_column['q_vendor_name'];
            $obj_promo_code = $obj_promo_code->having('vendor_name','LIKE', '%'.$search_term.'%')
                                            ->Orhaving('company_name','LIKE', '%'.$search_term.'%');
        }*/

        $current_context = $this;

        $json_result     = \Datatables::of($obj_promo_code);

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
                        ->editColumn('assigned_date',function($data)use ($current_context) 
                        {   
                           return isset($data->assigned_date)?$data->assigned_date:'N/A';
                        })
                        ->editColumn('expiry_date',function($data)use ($current_context) 
                        {   
                           return isset($data->expiry_date)?$data->expiry_date:'N/A';
                        })

                        /*->editColumn('vendor_name',function($data)use($current_context)
                        {
                            return $vendor_name = isset($data->vendor_name)?$data->vendor_name:'N/A';
                        })*/

                        ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }

    public function customer_orders($enc_influencer_id)
    {   
        $this->arr_view_data['influencer_id'] = isset($enc_influencer_id)?base64_decode($enc_influencer_id):0;

        $this->arr_view_data['customer_orders_path'] = $this->customer_orders_path;

        $this->arr_view_data['module_title']    = 'Customer Orders of Influencer';
        $this->arr_view_data['page_title']      = 'Customer Orders of Influencer';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        // dd($this->arr_view_data);
        return view($this->module_view_folder.'.customer_orders',$this->arr_view_data);
    }

    public function get_customer_orders_listing(Request $request)
    {   
        $form_data = $request->all();

        $obj_qutoes = $this->CustomerOrderService->get_customer_orders_of_influencer($form_data);

        // dd($obj_qutoes->get());
        
        $current_context = $this;

        $json_result  = \Datatables::of($obj_qutoes);
        
        $json_result  = $json_result->editColumn('created_at',function($data) use ($current_context)
                        {
                            //return date('d-M-Y',strtotime($data->created_at));
                            return us_date_format($data->created_at);

                        })
                        ->editColumn('promo_code',function($data) use($current_context)
                        {
                            return isset($data->promo_code)?$data->promo_code:'--';
                        })
                        ->editColumn('payment_status',function($data) use ($current_context)
                        {   
                            return $payment_status = isset($data->transaction_status)?get_payment_status($data->transaction_status):'N/A'; 
                     
                        })
                        ->editColumn('company_name',function($data) use ($current_context){
                            return $company_name = isset($data->company_name)?$data->company_name:'N/A';
                        })
                        ->editColumn('customer_name',function($data) use ($current_context){
                            return $company_name = isset($data->first_name)?$data->first_name." ".$data->last_name:'N/A';
                        })

                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $products_arr = [];
                            $products_arr = get_customer_quote_products($data->id);
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";
                            // if(isset($products_arr) && count($products_arr)>0)
                            // {
                            //     $products = '';

                            //     foreach ($products_arr as $key => $product) 
                            //     {
                            //         $products .= '<tr>
                            //                         <td>'.$product['product_details']['product_name'].'</td>
                            //                         <td>'.$product['qty'].'</td>
                            //                       </tr>';
                            //     }
                            // }
                            // else
                            // {
                            //     $products = 'No Record Found';
                            // }

                            // return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
            
                            //     <td colspan="5">
                            //         <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                            //             <thead>
                            //               <tr>
                            //                 <th>Product Title</th>
                            //                 <th>Quantity</th>                                
                            //               </tr>
                            //             </thead>
                            //             <tbody>'.$products.'</tbody>
                            //           </table>
                            //     </td>';

                            return $product_html = $this->GeneralService->order_products_for_list($id,$order_no,$products_arr);

                        })
                        ->editColumn('vendor_payment_status',function($data) use ($current_context)
                        {   
                            
                            if($data->stripe_trxn_status == '2')
                            {
                               $vendor_payment_status = '<span class="label label-success">Paid</span>';
                            }
                            else if($data->stripe_trxn_status == '3')
                            {
                                $vendor_payment_status = '<span class="label label-warning">Failed</span>';
                            }
                            elseif($data->stripe_trxn_status == '1') 
                            {
                               $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                            }
                            else
                            {
                                $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                            }

                            return $vendor_payment_status;
                     
                        })
                        ->editColumn('build_action_btn',function($data) use ($current_context)
                        {   
                            $view_href   =  $this->customer_orders_path.'/view/'.base64_encode($data->id);
                            
                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" target="_blank" href="'.$view_href.'">View</a>';
                            
                            return $build_action = $build_view_action;
                        });

        $build_result = $json_result->make(true)->getData();

        return response()->json($build_result);
    }

    /*
        Send Stripe account creation link to influencer via email
    */
    public function send_stripe_acc_creation_link(Request $request)
    {
        $enc_user_id = $request->input('user_id');

        if(isset($enc_user_id))
        {   
            $user_id = base64_decode($enc_user_id);
            $user_details = \Sentinel::findById($user_id);

            $admin_id = get_admin_id();

              /* get vendor client id */
            $client_id = $this->UserStripeAccountDetailsModel->where('user_id',$admin_id)
                                                      ->where('is_active','1')
                                ->pluck('client_id')
                                ->first();


            if($client_id!='')
            {
                $connection_response = $this->StripePaymentService->connection_request($user_id,$client_id);
         
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

    public function report_generator(Request $request,$type=false)
    {         $type  = \Request::segment(4);

       
        if($type=="pdf")
        {
         $inventory_pdf =  $this->PdfReportService->downloadPdfInfluencer($type);
         
          return $inventory_pdf->download('influencer report.pdf');
        }
        else
        {
            $this->ReportService->downloadExcelInfluencer($type);
        }
    }
}
 