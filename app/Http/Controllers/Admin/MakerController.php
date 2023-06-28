<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Common\Services\ReportService;
use App\Common\Services\PdfReportService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\CommissionService;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsModel;
use App\Models\MakerModel;
use App\Models\RoleUserModel;
use App\Models\RepresentativeModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\ShopSettings;
use App\Models\ActivationModel;

use App\Common\Traits\MultiActionTrait;
use DB;
use PDF;
use Sentinel;
use Validator;
use Flash;
use DateTime;

class MakerController extends Controller
{
 
  use MultiActionTrait;

  public function __construct(
                      UserService $UserService,
                      EmailService $EmailService,
                      ProductsModel $ProductsModel,
                      PdfReportService $PdfReportService,
                      ReportService $ReportService,
                      ElasticSearchService $ElasticSearchService,
                      CommissionService $CommissionService,
                      RoleModel $RoleModel,
                      RoleUsersModel $RoleUsersModel,
                      CategoryModel $CategoryModel,
                      CategoryTranslationModel $CategoryTranslationModel,
                      MakerModel $MakerModel,
                      RoleUsersModel $RoleUserModel,
                      ShopSettings $ShopSettings,
                      VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                      RepresentativeModel $RepresentativeModel,
                      ActivationModel $ActivationModel
                    )
  {
    $this->CategoryModel      = $CategoryModel;
    $this->RoleUserModel      = $RoleUserModel;
    $this->UserModel          = Sentinel::createModel();
    $this->MakerModel         = $MakerModel;
    $this->UserService        = $UserService;
    $this->PdfReportService   = $PdfReportService;
    $this->EmailService       = $EmailService;
    $this->ReportService      = $ReportService;
    $this->ElasticSearchService = $ElasticSearchService;
    $this->RoleModel          = $RoleModel;
    $this->CategoryModel      = $CategoryModel;
    $this->CategoryTranslationModel = $CategoryTranslationModel;
    $this->ProductsModel      = $ProductsModel;
    $this->RoleUsersModel     = $RoleUsersModel;
    $this->ShopSettings       = $ShopSettings;
    $this->RepresentativeModel = $RepresentativeModel;
    $this->ActivationModel    = $ActivationModel;
    $this->ProductsModel      = $ProductsModel;
    $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
    $this->arr_view_data      = [];
    $this->module_title       = "Vendor";
    $this->module_view_folder = "admin.maker";
    $this->module_url_path    = url(config('app.project.admin_panel_slug')."/vendor");        
    $this->role = 'maker';
    $this->CommissionService  = $CommissionService;
  }

  public function index()
  { 
    /*get all representative*/

    $get_rep_details = $this->RepresentativeModel->with(['get_user_details'=>function($q){
                                                   $q->where('status','1');  
                                                   $q->where('is_approved','1');

                                                 }])
                                                 ->get()
                                                 ->toArray();

    /* Get Vendor name and Id for sequencing */

    $user_table             =  $this->UserModel->getTable();
    $prefix_user_table      = DB::getTablePrefix().$user_table;

    $role_table             =  $this->RoleModel->getTable();
    $prefix_role_table      = DB::getTablePrefix().$role_table;

    $role_user_table        =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $maker_table            =  $this->MakerModel->getTable();
    $prefix_maker_table     = DB::getTablePrefix().$maker_table;


    $vendor_list = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                  $prefix_user_table.".email as email, ".
                                  
                                  $prefix_maker_table.".id as maker_id,".
                                  $prefix_maker_table.".company_name,".
                                  $prefix_maker_table.".real_company_name,".
                                  $prefix_maker_table.".listing_sequence_no,".
                                  $prefix_user_table.".status,".
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                           .$prefix_user_table.".last_name) as user_name"
                                ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                        
                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($maker_table.'.listing_sequence_no','ASC')
                        ->get();

      if($vendor_list)
      {
        $vendor_list = $vendor_list->toArray();
      }

    
    $this->arr_view_data['page_title']          = str_plural( $this->module_title);
    $this->arr_view_data['representative_arr']  = $get_rep_details;
    $this->arr_view_data['module_title']        = str_plural($this->module_title);
    $this->arr_view_data['module_url_path']     = $this->module_url_path;
    $this->arr_view_data['vendor_list']         = $vendor_list;
    
    return view($this->module_view_folder.'.index',$this->arr_view_data);
  }

    
  public function create()
  {      
    /* get all categories*/
    $categories_arr =  $this->CategoryModel->where('is_active','1')->get()->toArray();

    $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
    $this->arr_view_data['module_title']         = str_plural($this->module_title);
    $this->arr_view_data['module_url_path']      = $this->module_url_path;
    $this->arr_view_data['categories_arr']       = $categories_arr;  

    return view($this->module_view_folder.'.create',$this->arr_view_data);
  }



  public function get_makers(Request $request)
  {
    
    $arr_search_column      = $request->input('column_filter');
    
    $user_table             =  $this->UserModel->getTable();
    $prefix_user_table      = DB::getTablePrefix().$user_table;

    $maker_table            =  $this->MakerModel->getTable();
    $prefix_maker_table     = DB::getTablePrefix().$maker_table;

    $role_table             =  $this->RoleModel->getTable();
    $prefix_role_table      = DB::getTablePrefix().$role_table;

    $role_user_table        =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                  $prefix_user_table.".email as email, ".
                                  $prefix_user_table.".status, ".
                                  $prefix_user_table.".is_approved, ".
                                  $prefix_user_table.".commission, ".
                                  $prefix_user_table.".wallet_address as wallet_address, ".
                                  $prefix_user_table.".contact_no as contact_no, ".
                                  $prefix_user_table.".country_code, ".
                                  $prefix_user_table.".created_at, ".

                                  $prefix_maker_table.".brand_name, ".
                                  $prefix_maker_table.".user_id,".
                                  $prefix_maker_table.".company_name,".
                                  $prefix_maker_table.".real_company_name,".
                                  $prefix_maker_table.".admin_commission,".

                                  $role_table.".slug as slug, ".
                                  $role_table.".name as name, ".
                                  $maker_table.".is_direct_payment, ".
                                  $maker_table.".is_get_a_quote, ".
                                  $maker_table.".is_add_to_bag, ".
                    
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                           .$prefix_user_table.".last_name) as user_name"
                                ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                        
                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC');
               
        /* ---------------- Filtering Logic ----------------------------------*/  

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
          $search_term      = $arr_search_column['q_email'];
          $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }   
       
        if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
        {
          $search_term  = $arr_search_column['q_company_name'];
          $obj_user     = $obj_user->where($maker_table.'.company_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_real_company_name']) && $arr_search_column['q_real_company_name']!="")
        {
          $search_term  = $arr_search_column['q_real_company_name'];
          $obj_user     = $obj_user->where($maker_table.'.real_company_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
          $search_term  = $arr_search_column['q_username'];
          $obj_user     = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
          $search_term = $arr_search_column['q_contact_no'];
          $obj_user    = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
          $search_term = $arr_search_column['q_status'];
          $obj_user    = $obj_user->where($user_table.'.status','=', $search_term);
        }

        if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
          $search_term  = $arr_search_column['q_is_approved'];
          $obj_user     = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }

        if(isset($arr_search_column['q_commission']) && $arr_search_column['q_commission']!="")
        {
          $search_term  = $arr_search_column['q_commission'];
          $obj_user     = $obj_user->having('commission','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_get_a_quote']) && $arr_search_column['q_get_a_quote']!="")
        { 
          $search_term  = $arr_search_column['q_get_a_quote'];
          $obj_user     = $obj_user->where($maker_table.'.is_get_a_quote','=', $search_term);
        } 
         


        if(isset($arr_search_column['q_date']) && $arr_search_column['q_date']!="")
        {
            
            $search_term  = $arr_search_column['q_date'];

            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_user     = $obj_user->where($user_table.'.created_at','LIKE', '%'.$date.'%');
        }
        


        $json_result     = \Datatables::of($obj_user);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
        
                            {
                                return base64_encode($data->id);
                            })   
                             
                            ->editColumn('company_name',function($data)
                            {
                               return isset($data->company_name) && $data->company_name!='' ?$data->company_name:'N/A';
                            }) 

                            ->editColumn('real_company_name',function($data)
                            {
                               return isset($data->real_company_name) && $data->real_company_name!='' ?$data->real_company_name:'N/A';
                            }) 

                            ->editColumn('build_status_btn',function($data)
                            {
                              $build_status_btn ='';
                              if($data->status == '0')
                              {   
                                  $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                              }
                              elseif($data->status == '1')
                              {
                                  $build_status_btn = '<input type="checkbox" disabled="disabled"checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262"/>';
                              }
                              return $build_status_btn;
                            })

                            ->editColumn('get_a_quote',function($data)
                            { 
                              $get_a_quote ='';  
                              $is_add_to_bag = "";
                              // if($data->is_get_a_quote == 0)
                              // {   
                              //     $get_a_quote = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch setGetaQuote" data-type="activate" data-quotestatus="'.$data->is_get_a_quote.'" data-color="#99d683" data-secondary-color="#f96262" />';
                              // }
                              // elseif($data->is_get_a_quote == 1)
                              // { 
                              //     $get_a_quote = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch setGetaQuote" data-type="deactivate" data-quotestatus="'.$data->is_get_a_quote.'" data-color="#99d683" data-secondary-color="#f96262" />';
                              // }
                              
                              $get_a_quote = '<a onClick="viewGetAQuoteSettings('.$data->is_get_a_quote.','.$data->is_add_to_bag.','.$data->user_id.')" class="btn btn-circle btn-success btn-outline show-tooltip" href="javascript:void(0)" title="View get a quote setting">View</a>';

                              return $get_a_quote;
                            })

                            ->editColumn('build_action_btn',function($data) 
                            {   

                              $edit_href = $this->module_url_path.'/edit/'.base64_encode($data->id);

                                 $maker_id = base64_encode($data->id);
                                 $maker_name = $data->user_name;
                                 $maker_commission = $data->commission;


                                  $build_edit_action='<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip" data-toggle="modal" data-target="#myModal"data-id="'.$maker_id.'" data-name="'.$maker_name.'" data-commission="'.$maker_commission.'"id="modal-btn" onclick="setcom($(this))">Update Commission</button>';

                              if($data->admin_commission == 0)
                              {
                                $adminCommission = $this->CommissionService->get_admin_commission();
                              }
                              else
                              {
                                $adminCommission = $data->admin_commission;
                              }

                              $adminCommissionBtn = '<button type="button" class="btn btn-circle btn-success btn-outline" data-target="#exampleModalCenter" onclick="setAdminCommission('.$data->user_id.','.$adminCommission.')">Admin commission</button>';

                              $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                              $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>' . $adminCommissionBtn;

                              return $build_action = $build_view_action;
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

                            ->editColumn('registration_date',function($data)
                            {

                                $date = '';

                                $date = isset($data->created_at)?us_date_format($data->created_at):'';

                                return $date;
                            })

 
                            ->make(true);

        $build_result = $json_result->getData();

      
        return response()->json($build_result);
  }

  public function get_makers_in_modal(Request $request)
  {
    $arr_search_column      = $request->input('column_filter_in_modal');
    //dd($arr_search_column);
    $user_table             =  $this->UserModel->getTable();
    $prefix_user_table      = DB::getTablePrefix().$user_table;

    $maker_table            =  $this->MakerModel->getTable();
    $prefix_maker_table     = DB::getTablePrefix().$maker_table;

    $role_table             =  $this->RoleModel->getTable();
    $prefix_role_table      = DB::getTablePrefix().$role_table;

    $role_user_table        =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $vendor_list = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                  $prefix_user_table.".email as email, ".
                                  
                                  $prefix_maker_table.".id as maker_id,".
                                  $prefix_maker_table.".company_name,".
                                  $prefix_maker_table.".listing_sequence_no,".
                                  $prefix_user_table.".status,".
                                  $prefix_user_table.".is_approved,".
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                           .$prefix_user_table.".last_name) as modal_user_name"
                                ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                        
                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->whereNotNull($prefix_maker_table.'.company_name')
                        ->orderBy($maker_table.'.listing_sequence_no','ASC');
                        

       // dd($vendor_list);

      // if($vendor_list)
      // {
      //   $vendor_list = $vendor_list->toArray();
      // }
               
        /* ---------------- Filtering Logic ----------------------------------*/  
       
        if(isset($arr_search_column['q_modal_company_name']) && $arr_search_column['q_modal_company_name']!="")
        {
          $search_term  = $arr_search_column['q_modal_company_name'];
          $vendor_list     = $vendor_list->where($maker_table.'.company_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_modal_username']) && $arr_search_column['q_modal_username']!="")
        {
          $search_term  = $arr_search_column['q_modal_username'];
          $vendor_list     = $vendor_list->having('modal_user_name','LIKE', '%'.$search_term.'%');
        }           
        if(isset($arr_search_column['q_modal_status']) && $arr_search_column['q_modal_status']!="")
        {
          $search_term = $arr_search_column['q_modal_status'];
          $vendor_list    = $vendor_list->where($user_table.'.status','=', $search_term);
        } 
        if(isset($arr_search_column['q_modal_sequence_no']) && $arr_search_column['q_modal_sequence_no']!="")
        {
          $search_term = $arr_search_column['q_modal_sequence_no'];
          $vendor_list    = $vendor_list->where($maker_table.'.listing_sequence_no','LIKE', '%'.$search_term.'%');
        }        
         
        $json_result     = \Datatables::of($vendor_list);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
        
                            {
                                return base64_encode($data->id);
                            })   
                             
                            ->editColumn('modal_company_name',function($data)
                            {
                               return isset($data->company_name) && $data->company_name!='' ?$data->company_name:'N/A';
                            }) 

                            ->editColumn('modal_build_status_btn',function($data)
                            {
                              $build_status_btn ='';
                              if($data->status == '0' || $data->is_approved == '0')
                              {   
                                  $build_status_btn = '<div class="my_div_deactive" title="Deactive"></div>';
                              }
                              elseif($data->status == '1' && $data->is_approved == '1')
                              {
                                  $build_status_btn = '<div class="my_div_active" title="Active">';
                              }
                              return $build_status_btn;
                            })
                             ->editColumn('modal_sequence_no',function($data)
                            {

                              if($data->status == '1' && $data->is_approved == '1')
                              {
                               return '<input data-sr_no="'.$data->maker_id.'" data-prev_sequence="'.$data->listing_sequence_no.'"  data-user_name="'.$data->modal_user_name.'" data-company_name="'.$data->company_name.'" type="text" oninput="checkDuplicateSequence(this)" name="listing_sequence_no['.$data->maker_id.']" class="form-control vendor-sequence_no"  value="'.$data->listing_sequence_no.'" required>';
                             } else {
                              return '--';
                             }
                            }) 

                            ->make(true);

        $build_result = $json_result->getData();

      
        return response()->json($build_result);
  }


  /*check maker sequence already present or not*/
  public function check_sequence_no_present(Request $request)
  {
    $sequence_no = $request->all();

    if(isset($sequence_no['sequence_no']) && $sequence_no['sequence_no'] != null &&
      isset($sequence_no['prev_sequence_no']) && $sequence_no['prev_sequence_no'] != null)
    {
      $s_no = (int)$sequence_no['sequence_no'];

       $arr_search_column      = $request->input('column_filter_in_modal');
      //dd($arr_search_column);
      $user_table             =  $this->UserModel->getTable();
      $prefix_user_table      = DB::getTablePrefix().$user_table;

      $maker_table            =  $this->MakerModel->getTable();
      $prefix_maker_table     = DB::getTablePrefix().$maker_table;

      $role_table             =  $this->RoleModel->getTable();
      $prefix_role_table      = DB::getTablePrefix().$role_table;

      $role_user_table        =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $sequence_present_vendor_list = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                  $prefix_user_table.".email as email, ".
                                  
                                  $prefix_maker_table.".id as maker_id,".
                                  $prefix_maker_table.".company_name,".
                                  $prefix_maker_table.".listing_sequence_no,".
                                  $prefix_user_table.".status,".
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                           .$prefix_user_table.".last_name) as modal_user_name"
                                ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                        
                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->whereNotNull($prefix_maker_table.'.company_name')
                        ->where($maker_table.'.listing_sequence_no','=',$s_no)
                        ->where($maker_table.'.listing_sequence_no','!=',$sequence_no['prev_sequence_no'])
                        ->get()->toArray();

      // dd($s_no,$this->role,$sequence_present_vendor_list);
        if(isset($sequence_present_vendor_list) && count($sequence_present_vendor_list) > 0)
        {
          $response['status']      = 'error';
          $response['message']      = $sequence_present_vendor_list[0]->company_name;
        }
        else
        {

          if(isset($sequence_no['changed_maker_id']) && !empty($sequence_no['changed_maker_id']))
          {
            $update_resp = $this->MakerModel->where('id',$sequence_no['changed_maker_id'])->update(['listing_sequence_no'=>$sequence_no['sequence_no']]);
          }


            $response['status']     = 'success';
            $response['message']      = 'Yes';
        }        
                        
    }
    else
    {
      $response['status']     = 'success';
      $response['message']      = 'Yes';
    }

    return response()->json($response);
  }
  /*end*/

  public function activate(Request $request)
  {
    $enc_id = $request->input('id');

    if(isset($enc_id))
    {
      $id = base64_decode($enc_id);
      
      if($this->perform_activate($id))
      {
        $arr_response['status'] = 'SUCCESS';
      }
      else
      {
        $arr_response['status'] = 'ERROR';
      }

      $arr_response['data'] = 'ACTIVE';
    }
    else
    {
      $arr_response['status'] = 'ERROR';
    }
    return response()->json($arr_response);
  }

  public function deactivate(Request $request)
  {
    $enc_id = $request->input('id');
    if(isset($enc_id))
    {
      if($this->perform_deactivate(base64_decode($enc_id)))
      {
          $arr_response['status'] = 'SUCCESS';
      }
      else
      {
          $arr_response['status'] = 'ERROR';
      }

    $arr_response['data'] = 'DEACTIVE';
    }
    else
    {
       $arr_response['status'] = 'ERROR';
    }

    

    return response()->json($arr_response);
  }

  public function perform_activate($id)
  { 
    $entity = $this->UserModel->where('id',$id)->first();
    
    if($entity)
    {   
          //activation completion
          $date = date('Y-m-d H:i:s');

          $activation_completed = $this->ActivationModel->where('user_id',$id)->pluck('completed')->first();
        
          if(isset($activation_completed) && $activation_completed == 0)
          {
             $result = $this->ActivationModel->where('user_id',$id)->update(['completed'=>1,'completed_at'=>$date]);   
          }  

          //Activate the user
          
          $this->UserModel->where('id',$id)->update(['status'=>'1', 'is_approved' => 1]);
          $this->ElasticSearchService->index_vendor_product($id);
      return TRUE;
    }

    return FALSE;
  }

  public function perform_deactivate($id)
  {
    $entity = $this->UserModel->where('id',$id)->first();
    
    if($entity)
    {   
      //deactivate the user
      //$this->ElasticSearchService->deactivate_vendor_product($id);
      $this->UserModel->where('id',$id)->update(['is_approved' => 0]);
      $this->MakerModel->where('user_id',$id)->update(['listing_sequence_no'=>0]);
      //$this->ElasticSearchService->change_vendor_product_status($id);
      $this->ElasticSearchService->delete_vendor_product($id);
      return TRUE;
    }
    return FALSE;
  }

  public function view($enc_id)
  {   
      $id            = base64_decode($enc_id);
      $company_details = array();
         
      $obj_user_data = $this->UserModel->where('id', $id)
                                           ->with('maker_details')
                                           ->first();
      if($obj_user_data)
      {

        $arr_user_data = $obj_user_data->toArray();
      }
      
      // Get vendor company details
      $company_details_arr  = $this->UserModel
                                        ->with(['store_details',
                                              'shop_settings'])
                                        ->where('id',$id)
                                        ->get()
                                        ->toArray();
                                
      $company_details_arr  = reset($company_details_arr);
      $company_details["store_cover_image"] = $company_details_arr["store_details"]["store_cover_image"];
      $company_details["store_profile_image"] = $company_details_arr["store_details"]["store_profile_image"];
      $company_details["shop_story"] = $company_details_arr["shop_settings"]["shop_story"]; 
      $company_details["order_minimum"] = $company_details_arr["shop_settings"]["first_order_minimum"]; 
      $company_details["lead_time"] = $company_details_arr["shop_settings"]["shop_lead_time"];
      
      /*maker details and assigned rep details*/
      $representative_data_arr  = $this->VendorRepresentativeMappingModel
                                        ->with(['get_representative_details.get_user_details',
                                              'get_representative_details.sales_manager_details.get_user_data',
                                              'get_representative_details.get_area_details'])
                                        ->where('vendor_id',$id)
                                        ->get()
                                        ->toArray();


      $categories_obj_data =  $this->CategoryModel->where('is_active','1')->get()->toArray();


      $this->arr_view_data['edit_mode']               = TRUE;
      $this->arr_view_data['enc_id']                  = $enc_id;        
      $this->arr_view_data['arr_user_data']           = $arr_user_data;    
      $this->arr_view_data['representative_data_arr'] = $representative_data_arr;    
      $this->arr_view_data['categories_arr']          = $categories_obj_data; 
      $this->arr_view_data['page_title']              = str_singular( $this->module_title).' Details';
      $this->arr_view_data['module_title']            = str_plural($this->module_title);
      $this->arr_view_data['module_url_path']         = $this->module_url_path;
      $this->arr_view_data['company_details']         = $company_details;
      
      return view($this->module_view_folder.'.view',$this->arr_view_data);
  }

  function changeAprovalStatus(Request $request)
  {
      //echo "<pre>";print_r($request->all());die;

      $maker_status       = $request->input('makerAprovalStatus');
      $maker_id           = $request->input('maker_id');
      $type               = $request->input('type');
      $maker_id           = base64_decode($maker_id);

      $representative_arr = $request->input('representative_arr');


      if(isset($representative_arr) && count($representative_arr)>0)
      {
         
        /*assign representative to the vendor*/
        foreach($representative_arr as $key => $representative_id)
        {
           $this->VendorRepresentativeMappingModel->create(['representative_id'=>$representative_id,'vendor_id'=>$maker_id]);
        }
      
        $this->UserModel->where('id',$maker_id)->update(['is_approved'=>1]);
        
        // $response['status']  = 'SUCCESS';
        // $response['message'] = $this->module_title.' has been approved.';
        $type='activate';
        if($type=='activate')
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been approved.';
            $response['data'] = 'ACTIVE';
        }
        else
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been disapproved.';
            $response['data'] = 'DEACTIVE';
        }

      }
      elseif(!isset($representative_arr) && count($representative_arr)<=0)
      {

        // if(isset($maker_status)){
        //   $maker_status = 0;
        // } else {
        //   $maker_status = 1;
        // }

        if($type=='activate'){
          $maker_status = 1;

        } else {
          $maker_status = 0;

        }
        $this->UserModel->where('id',$maker_id)->update(['is_approved'=>$maker_status]);


        if($type=='activate')
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been approved.';
            $response['data'] = 'ACTIVE';
            $this->ElasticSearchService->index_vendor_product($maker_id);
        }
        else
        {
            $response['status']  = 'SUCCESS';
            $response['message'] = $this->module_title.' has been disapproved.';
            $response['data'] = 'DEACTIVE';
            $this->ElasticSearchService->delete_vendor_product($maker_id);

        }
       
      }
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong, please try again.';
      }
      
      return response()->json($response); 
  
  }

/*  function changeAprovalStatus(Request $request)
  {
    $maker_status       = $request->input('makerAprovalStatus');
    $maker_id           = $request->input('maker_id');
    $representative_arr = $request->input('representative_arr');

    if($maker_status=='1')
    {

      $this->UserModel->where('id',$maker_id)->update(['is_approved'=>1]);
      
      $response['status']  = 'SUCCESS';
      $response['message'] = $this->module_title.' activated successfully';

   }
    elseif($maker_status=='0')
    {
      $this->UserModel->where('id',$maker_id)->update(['is_approved'=>0]);

      $response['status']  = 'SUCCESS';
      $response['message'] = $this->module_title.' deactivated successfully';
    }
    else
    {
      $response['status']  = 'ERROR';
      $response['message'] = 'Oops... Something went wrong please try again later';
    }
    
    return response()->json($response); 
  }
*/

  function change_payment_status(Request $request)
  {
    $payment_status     = $request->input('makerAprovalStatus');
    $maker_id           = base64_decode($request->input('maker_id'));

    $changeStatus = $this->MakerModel->where('user_id',$maker_id)->update(['is_direct_payment'=>$payment_status]);
    
    if($changeStatus)
    {
      $response['status']  = 'SUCCESS';
       $response['message'] = ' Payment mode changed successfully';
    }else
    {
      $response['status']  = 'Error';
      $response['message'] = ' Something went wrong, Please try again';
    }

    return response()->json($response); 
  }


  public function get_count(Request $request)
  {  
      $count = 0;
      $maker_id = $request->input('maker_id');
      $maker_id = base64_decode($maker_id);
      $count = $this->VendorRepresentativeMappingModel->where('vendor_id',$maker_id)->count();

      $response['count'] = $count;

      return response()->json($response);
  }


  function updateCommission(Request $request)
  {

    $maker_id = $request->input('maker_id');
    $maker_commission = $request->input('makerCommission');

    $id       = base64_decode($maker_id);

    if($maker_commission!=''&& $maker_commission!="null")
    {
      $this->UserModel->where('id',$id)->update(['commission'=>$maker_commission]);
      
      $response['status']  = 'SUCCESS';
      $response['message'] = $this->module_title.' commission has been updated.';

    }
    
    else
    {
      $response['status']  = 'ERROR';
      $response['message'] = 'Something went wrong, please try again.';
    }
    
    return response()->json($response); 
  }

  public function report_generator(Request $request,$type=false)
  {         
    $type  = \Request::segment(4);

    if($type=="pdf")
    {
     $inventory_pdf =  $this->PdfReportService->download_pdf($type);
     
      return $inventory_pdf->download('vendor report.pdf');
    }
    $this->ReportService->downloadExcel($type);


  }

  
  public function delete_rep_mapping($enc_id)
  {
     
        $id = base64_decode($enc_id);
    
       
        $data = $this->VendorRepresentativeMappingModel->where('id',$id)->first();
    
        $representative_id = $data['representative_id'];
    
        $vendor_id = $data['vendor_id'];
  
        $delete = $this->VendorRepresentativeMappingModel->where('representative_id',$representative_id)
                                                         ->where('vendor_id',$vendor_id)
                                                         ->delete();
       
        if($delete)
        {
            Flash::success('Representative has been deleted.');
            return redirect()->back();
        }    
        else
        {
            Flash::error('Error occurred while representative deletion.');
            return redirect()->back();
        }
  }

  function update_admin_commission(Request $request)
  {
    $adminCommission    = $request->input('admin_commission');
    $vendor_id           = $request->input('vendor_id');

    $changeStatus = $this->MakerModel->where('user_id',$vendor_id)->update(['admin_commission'=>$adminCommission]);
    
    if($changeStatus)
    {
     Flash::success(' Admin commission updated successfully');
    }else
    {
      Flash::error('Error occurred while update commission.');
    }

    return redirect('admin/vendor');
  }

  // Update vendor status to get a quote
  function update_status_get_a_quote(Request $request)
  { 
      $quotestatus       = $request->input('quotestatus');
      $maker_id           = $request->input('id'); 
      $maker_id           = $maker_id; 
      
      if(isset($quotestatus) && count($quotestatus) != "")
      {               
        $quotestatus = (int)$quotestatus;
        if($quotestatus == 1)
          $quotestatus = 0;
        else
          $quotestatus = 1;
        
        
        $this->MakerModel->where('user_id',$maker_id)->update(['is_get_a_quote'=>$quotestatus]);
        
        $response['status']  = 'SUCCESS';
        $response['message'] = 'Get a quote status has been updated successfully.';

      }
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong, please try again.';
      }
      
      return response()->json($response); 
  }

  // Update vendor add to bag button status
  function update_status_add_to_bag(Request $request)
  {

      $status       = $request->input('quotestatus');
      $maker_id     = $request->input('id'); 
      $maker_id     = $maker_id; 
    
      if(isset($status) && count($status) != "")
      {      

        /* Check if that vendor's products has wholesale price and retail price thrn only can enable add to card */

        if($status == 0){
              $chkIsPriceNotZero = $this->ProductsModel
                                        ->select(DB::raw('sum(unit_wholsale_price) as sum_wholesale_price,sum(retail_price) as sum_retail_price'))
                                        ->where('user_id',$maker_id)
                                        ->where('is_deleted',0)
                                        ->get()
                                        ->toArray();

              $sum_wholesale_price = $sum_retail_price = 0.00;
              $concat_price = "";
              $response = [];
              if(isset($chkIsPriceNotZero)){

                    $sum_wholesale_price = $chkIsPriceNotZero[0]['sum_wholesale_price'];
                    $sum_retail_price = $chkIsPriceNotZero[0]['sum_retail_price'];
                    if($sum_wholesale_price == '0.00' || $sum_retail_price == '0.00'){

                         $concat_price = "wholesale and retail";
                          if($sum_wholesale_price == '0.00' && $sum_retail_price != '0.00'){
                              $concat_price = "wholesale";
                          }
                          else if($sum_retail_price == '0.00' && $sum_wholesale_price != '0.00'){
                              $concat_price = "retail";
                          }

                         $response['status']  = 'ERROR';
                         $response['message'] = ' Oops! You are unable to do this process, the selected vendor will need to update '.$concat_price.' price of products.';
                         return response()->json($response); 
                    }                   
                    
              }
          }
        /* Ends */



        $status = (int)$status;
        if($status == 1)
          $status = 0;
        else
          $status = 1;
        
        
        $this->MakerModel->where('user_id',$maker_id)->update(['is_add_to_bag'=>$status]);
        
        $response['status']  = 'SUCCESS';
        $response['message'] = 'Add to bag button status has been updated successfully.';

      }
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong, please try again.';
      }
      
      return response()->json($response); 
  
  }


  // Update or save vendor sequence
  function save_vendor_sequence(Request $request)
  { 
      $listing_sequence_no_array       = $request->input('listing_sequence_no');
           
      if(count($listing_sequence_no_array)>0)
      {
        $is_update_error = false;
        foreach($listing_sequence_no_array as $maker_id => $seq_no)
        {
          $update_resp = $this->MakerModel->where('id',$maker_id)->update(['listing_sequence_no'=>$seq_no]);

          if($update_resp == 0)
          {
            $is_update_error = true;
            break;
          }
          
        }

        if($is_update_error)
        {
          $response['status']  = 'error';
          $response['message'] = "Something went wrong, unable to update some vendor's sequence.";
        }else
        {
          $response['status']  = 'success';
          $response['message'] = "Vendor's sequence saved successfully.";
        }
      }
      else
      {
        $response['status']  = 'error';
        $response['message'] = "Unable to get vendor's list.";
      }
      
      
      return response()->json($response); 
  }

  function checkDuplicateSequence(Request $request){
    $seq_no      = $request->input('seq_no');

    $user_table             =  $this->UserModel->getTable();
    $prefix_user_table      = DB::getTablePrefix().$user_table;

    $role_table             =  $this->RoleModel->getTable();
    $prefix_role_table      = DB::getTablePrefix().$role_table;

    $role_user_table        =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $maker_table            =  $this->MakerModel->getTable();
    $prefix_maker_table     = DB::getTablePrefix().$maker_table;


    $vendor_list = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                  $prefix_user_table.".email as email, ".
                                  
                                  $prefix_maker_table.".id as maker_id,".
                                  $prefix_maker_table.".company_name,".
                                  $prefix_maker_table.".listing_sequence_no,".
                                  $prefix_user_table.".status,".
                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                           .$prefix_user_table.".last_name) as user_name"
                                ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                        
                        //->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->whereNotNull($prefix_maker_table.'.listing_sequence_no')
                        ->where($user_table.'.id','!=',1)
                        ->where($prefix_maker_table.'.listing_sequence_no',$seq_no)
                        ->get();
                     
      if(!empty($vendor_list->toArray()))
      {
        return 1;
      } else {
         return 0;
      }
  }

    
}
