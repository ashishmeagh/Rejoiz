<?php

namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Common\Traits\MultiActionTrait;
use App\Models\RepresentativeMakersModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\RepAreaModel;
use App\Models\RepresentativeModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\MakerModel;
/*use App\Common\Services\GeneralService;*/
use Validator;
use Flash;
Use Sentinel;
use DB;
 
class MakersController extends Controller
{
    use MultiActionTrait;
    
    public function __construct(RepresentativeMakersModel $RepresentativeMakersModel,
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                RepAreaModel $RepAreaModel,
                                RepresentativeModel $RepresentativeModel,
                                MakerModel $MakerModel
                                )
    {      
        $this->RepresentativeMakersModel = $RepresentativeMakersModel;
        $this->UserModel                 = $UserModel;
        $this->RoleModel                 = $RoleModel;
        $this->RoleUsersModel            = $RoleUsersModel;
        $this->MakerModel                = $MakerModel;
        $this->RepAreaModel               =$RepAreaModel;
        $this->RepresentativeModel        =$RepresentativeModel;
        $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
        /*$this->GeneralService            = new GeneralService();*/

        $this->BaseModel         = $this->RepresentativeMakersModel;
        $this->module_title      = "My Vendors";
        $this->module_url_slug   = "vendors";
        $this->module_view_folder= "representative/makers"; 
        $this->module_url_path   = url(config('app.project.representative_panel_slug')."/vendors");

        $this->role = 'maker';
    }
    
    public function index()
    {
        $this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function makers_listing(Request $request)
    {   
      
        $user = Sentinel::check();
        $loggedIn_userId = 0;
        $post_code = '';

        if($user)
        {
            $loggedIn_userId = $user->id;
            // dd($loggedIn_userId);
            $post_code  = $user->post_code;
        } 
            
        //$obj_user = $this->GeneralService->get_makers($post_code);

        $arr_search_column = $request->input('column_filter');

        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $maker_table        =  $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $vendor_representative_mapping_table = $this->VendorRepresentativeMappingModel->getTable();
        $prefix_vendor_representative_mapping_table = DB::getTablePrefix().$vendor_representative_mapping_table;

        $representative_table = $this->RepresentativeModel->getTable();
        $prefix_representative_table = DB::getTablePrefix().$representative_table;

        $rep_area_table = $this->RepAreaModel->getTable();
        $prefix_rep_area_table = DB::getTablePrefix().$rep_area_table;



         $obj_user = DB::table($vendor_representative_mapping_table)
                   ->select(DB::raw($prefix_user_table.".id as uid,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_maker_table.".company_name, ".
                                    $vendor_representative_mapping_table.".vendor_id, ".
                                    $representative_table.".id as rid,".
                                    $representative_table.".area_id,".
                                    $rep_area_table.".id as aid,".
                                    $rep_area_table.".area_name,".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                       ->leftJoin($prefix_user_table,$vendor_representative_mapping_table.'.vendor_id','=',$user_table.'.id')

                       ->leftJoin($representative_table,$vendor_representative_mapping_table.'.representative_id','=',$representative_table.'.user_id')

                       ->leftJoin($prefix_rep_area_table,$representative_table.'.area_id','=',$rep_area_table.'.id')
                       // ->leftJoin($maker_table,$vendor_representative_mapping_table.'.vendor_id','=',$maker_table.'.user_id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')
                       ->where('representative_id',$loggedIn_userId);
                       // ->get();

        // dd($obj_user);




      
       /* ---------------- Filtering Logic ----------------------------------*/  
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term  = $arr_search_column['q_name'];
            $obj_user     = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_brand_name']) && $arr_search_column['q_brand_name']!="")
        {
            $search_term  = $arr_search_column['q_brand_name'];
            $obj_user     = $obj_user->where($maker_table.'.company_name','LIKE', '%'.$search_term.'%');
        }

        // if(isset($arr_search_column['q_tax_id']) && $arr_search_column['q_tax_id']!="")
        // {
        //     $search_term  = $arr_search_column['q_tax_id'];
        //     $obj_user     = $obj_user->where($user_table.'.tax_id','LIKE', '%'.$search_term.'%');
        // }   
        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term = $arr_search_column['q_email'];
            $obj_user    = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_contact_no']) && $arr_search_column['q_contact_no']!="")
        {
            $search_term  = $arr_search_column['q_contact_no'];
            $obj_user     = $obj_user->where($user_table.'.contact_no','LIKE', '%'.$search_term.'%');
        }
        
        $json_result     = \Datatables::of($obj_user);
        
       $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->uid);
                            })   
                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->uid);
                                $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>';
                                return $build_view_action;
                            })
                             ->editColumn('contact_no',function($data)
                            { 
                                if($data->country_code != "")
                                {
                                    $countryCode = ltrim($data->country_code,'+');
                                    $data->contact_no = str_replace($countryCode, "", $data->contact_no);
                                    $contact_no = '+'.$countryCode .'-'.get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                                else
                                {
                                    $contact_no = get_contact_no($data->contact_no);                              
                                    return $contact_no;
                                }
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        // dd($build_result);
        
        return response()->json($build_result);
    }

    public function add_maker($enc_id=null)
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        $user_id = base64_decode($enc_id);
        //add maker in representative list
        $arr_data = [];
        $arr_data['representative_id'] = $loggedIn_userId;
        $arr_data['maker_id']          = $user_id;

        $create = $this->RepresentativeMakersModel->create($arr_data);

        if($create)
        {
            Flash::success($this->module_title.' has been added in your list.');                
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' addition.');
        }

        return redirect($this->module_url_path.'/my_maker_list');
    }

    public function my_maker_list()
    {
        $this->arr_view_data['page_title']      = "Manage ".str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.my_maker',$this->arr_view_data);
    }

    public function load_my_maker_listing(Request $request)
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        $arr_search_column = $request->input('column_filter');

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $rep_maker_table =  $this->RepresentativeMakersModel->getTable();
        $prefix_rep_maker_table = DB::getTablePrefix().$rep_maker_table;

        $maker_table =  $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;    

        
        $obj_user = DB::table($rep_maker_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $maker_table.".brand_name, ".
                                     $prefix_user_table.".tax_id, ".
                                     $prefix_rep_maker_table.".commission, ".
                                     $prefix_rep_maker_table.".is_lock, ".
                                     $prefix_rep_maker_table.".is_confirm, ".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                        ->leftJoin($maker_table,$prefix_maker_table.'.user_id','=',$rep_maker_table.'.maker_id')
                        ->leftJoin($user_table,$prefix_user_table.'.id','=',$rep_maker_table.'.maker_id')
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->where($user_table.'.status','=',1)
                        ->where($rep_maker_table.'.representative_id','=',$loggedIn_userId)
                        ->orderBy($user_table.'.created_at','DESC');           

                        /*->get();*/   
       /* ---------------- Filtering Logic ----------------------------------*/  
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term      = $arr_search_column['q_name'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }   
        if(isset($arr_search_column['q_brand_name']) && $arr_search_column['q_brand_name']!="")
        {
            $search_term      = $arr_search_column['q_brand_name'];
            $obj_user = $obj_user->where($maker_table.'.brand_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_tax_id']) && $arr_search_column['q_tax_id']!="")
        {
            $search_term      = $arr_search_column['q_tax_id'];
            $obj_user = $obj_user->where($user_table.'.tax_id','LIKE', '%'.$search_term.'%');
        } 
        if(isset($arr_search_column['q_commission']) && $arr_search_column['q_commission']!="")
        {
            $search_term      = $arr_search_column['q_commission'];
            $obj_user = $obj_user->where($prefix_rep_maker_table.'.commission','LIKE', '%'.$search_term.'%');
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
        
        $json_result     = \Datatables::of($obj_user);
        $json_result     = 
        $json_result->editColumn('commission',function($data)
                            {
                                $is_lock    = $data->is_lock;
                                $is_confirm = $data->is_confirm;
                                $commission = "-";

                                if($is_lock==1 && $is_confirm==1)
                                {
                                    $commission  = $data->commission;
                                }
                                
                                return $commission;
                            })                          
                            ->make(true);

        $build_result = $json_result->getData();
      
        return response()->json($build_result);
    }


    public function remove_maker($enc_id=null)
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }   

        $user_id = base64_decode($enc_id);
        
        //add maker in representative list
        $arr_data = [];
        $arr_data['representative_id'] = $loggedIn_userId;
        $arr_data['maker_id']          = $user_id;

        $delete = $this->RepresentativeMakersModel->where('representative_id',$loggedIn_userId)
                        ->where('maker_id',$user_id)->delete();

        if($delete)
        {
            Flash::success($this->module_title.' has been deleted from your list.');                
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' deletion.');
        }

        return redirect($this->module_url_path.'/my_maker_list');
    }


    public function view(Request $request,$uid = 0)
    {
        $uid = base64_decode($uid);
        $obj_rep = $this->UserModel->where('id',$uid)->with(['maker_details','sales_manager_details.area_details'])->first();
        if($obj_rep)
        {
           $arr_data = $obj_rep->toArray(); 
           
        }

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['arr_data']        = $arr_data;    
        $this->arr_view_data['page_title']      = 'Vendor Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
       
        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

}