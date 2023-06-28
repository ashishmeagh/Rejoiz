<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Common\Services\LanguageService;  
use App\Models\RepresentativeMakersModel;
use App\Models\UserModel;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;

use Datatables;
use Validator;
use Flash;
use Sentinel;
use DB; 

class CommissionController extends Controller
{
    use MultiActionTrait;
    public $NotificationsModel; 
    public function __construct(RepresentativeMakersModel $RepresentativeMakersModel,
                                UserModel $UserModel,GeneralService $GeneralService)
    {      
        $this->RepresentativeMakersModel  = $RepresentativeMakersModel;
        $this->UserModel         = $UserModel; 
        $this->GeneralService    = $GeneralService;   
        $this->HelperService     = $HelperService;    
        $this->module_title      = "Commission";
        $this->module_url_slug   = "commission";
        $this->module_view_folder= "admin/commission"; 
        $this->module_url_path   = url(config('app.project.admin_panel_slug')."/commission");
    }
     /*
    | Index  : Display listing of Notifications
    | auther : Shital More
    | Date   : 25/06/2016
    | @return \Illuminate\Http\Response
    */ 

    public function index()
    {
        $this->arr_view_data['page_title']      = "Manage ".str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function representative_listing($enc_id=null)
    {
       
       $id = base64_decode($enc_id);

       $this->arr_view_data['page_title']      = "Manage ".str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['maker_id'] = $id;

        return view($this->module_view_folder.'.representative_listing',$this->arr_view_data);
    }


    public function get_representative_listing(Request $request,$maker_id=null)
    {
    
       $representative_maker_tbl_name     = $this->RepresentativeMakersModel->getTable();        
      $prefixed_representative_maker_tbl = DB::getTablePrefix().$this->RepresentativeMakersModel->getTable();

      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;


      $obj_products = DB::table($representative_maker_tbl_name)
                                ->select(DB::raw($prefixed_representative_maker_tbl.".id,".  
                                                 $prefixed_representative_maker_tbl.'.representative_id,'.
                                                 $prefixed_representative_maker_tbl.'.maker_id,'.
                                                 $prefixed_representative_maker_tbl.'.commission,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                )
                                               ) 

                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_maker_tbl.'.representative_id')
                                ->where($prefixed_representative_maker_tbl.'.maker_id','=',$maker_id);
                                
        //dd($obj_products->get()->toArray());
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term = $arr_search_column['q_username'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        $current_context = $this;

        $json_result  = \Datatables::of($obj_products);
            
        /* Modifying Columns */
        $json_result =  $json_result->
                      editColumn('build_action_btn',function($data) 
                        {     
                            $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="Update Commission Details" class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'">Update Commission</a>';

                            return $build_action = $build_view_action;
                        })

                        
                        ->make(true);

        $build_result = $json_result->getData();

        //dd($build_result);
         
        return response()->json($build_result);
      

    
    }


    public function get_maker_list(Request $request)
    {
     
      $representative_maker_tbl_name     = $this->RepresentativeMakersModel->getTable();        
      $prefixed_representative_maker_tbl = DB::getTablePrefix().$this->RepresentativeMakersModel->getTable();

      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;


      $obj_products = DB::table($representative_maker_tbl_name)
                                ->select(DB::raw($prefixed_representative_maker_tbl.".id,".  
                                                 $prefixed_representative_maker_tbl.'.representative_id,'.
                                                 $prefixed_representative_maker_tbl.'.maker_id,'.
                                                 $prefixed_representative_maker_tbl.'.commission,'.
                                                 /*$prefix_user_table.'.first_name,'.*/
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                )
                                               )                                
                                ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_representative_maker_tbl.'.maker_id')
                                ->groupBy($prefixed_representative_maker_tbl.'.maker_id');
                                
        //dd($obj_products->get()->toArray());
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term = $arr_search_column['q_username'];
            $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
        }
        
        $current_context = $this;

        $json_result  = \Datatables::of($obj_products);
            
        /* Modifying Columns */
        $json_result =  $json_result->editColumn('representative_html',function($data) use ($current_context)
                        {   
                            $rep_arr = [];
                            $rep_arr = get_maker_representative($data->maker_id);

                            if(isset($rep_arr) && count($rep_arr)>0)
                            {
                                $representative = '';
                                foreach ($rep_arr as $key => $rep) 
                                {
                                    $representative .= '<tr>
                                                    <td>'.ucfirst($rep['representative_details']['first_name']).' '.$rep['representative_details']['last_name'].'</td>
                                                    <td>'.$rep['commission'].'</td>
                                                  </tr>';
                                }

                            }
                            else
                            {
                                $representative = 'No Record Found';
                            }

                            return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_representative_list($(this))">View Representative<span> '.count($rep_arr).'</span></a>
            
                                <td colspan="5">
                                    <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                        <thead>
                                          <tr>
                                            <th>Representative Name</th>
                                            <th>Commission%</th>                                
                                          </tr>
                                        </thead>
                                        <tbody>'.$representative.'</tbody>
                                      </table>
                                </td>';

                        })
                        ->editColumn('build_action_btn',function($data) 
                        {     
                            $view_href   =  $this->module_url_path.'/representative_listing/'.base64_encode($data->maker_id);

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Commission Details" class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'">View</a>';

                            return $build_action = $build_view_action;
                        })

                        
                        ->make(true);

        $build_result = $json_result->getData();
         
        return response()->json($build_result);
    }

    public function view($enc_id=null)
    {
       
        $id = base64_decode($enc_id);

        $arr_data = [];
        $obj_data = $this->RepresentativeMakersModel->where('id',$id)
                         ->with(['representative_details'=>function($query)
                        {
                            $query->select('id','first_name','last_name');
                        }])->first();
        if($obj_data)
        {
            $arr_data = $obj_data->toArray();
        }

        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['module_title']    = 'Manage Representative Commission';
        $this->arr_view_data['page_title']      = 'Representative Commission';
        $this->arr_view_data['id']              = $enc_id;
         $this->arr_view_data['module_url_path'] =  url($this->module_url_path);
       /* dd($this->arr_view_data);*/
        //return view($this->module_view_folder.'.create_commission',$this->arr_view_data);
        //dd($id);
       return view($this->module_view_folder.'.view',$this->arr_view_data);
        
        
    }

    public function set_commission(Request $request)
    {
        $id         = $request->input('tran_id',null);
        $commission = $request->input('commission',null);
        $maker_id   = $request->input('maker_id',null);
        $rep_id     = $request->input('rep_id',null);

        if($id)
        {
            $update_comm = $this->RepresentativeMakersModel->where('id',$id)->update(['commission'=>$commission,'is_lock'=>'1']);
            if($update_comm)
            {
                /******************Notification to maker START*******************************/
                  $loggedInUserId = 0;
                  $user = Sentinel::check();

                  if($user)
                  {
                      $loggedInUserId = $user->id;
                  }

                  $first_name = isset($user->first_name)?$user->first_name:"";
                  $last_name  = isset($user->last_name)?$user->last_name:"";  

                  $arr_event                 = [];
                  $arr_event['from_user_id'] = $loggedInUserId;
                  $arr_event['to_user_id']   = 1;
                  $arr_event['description']  = 'Commission confirm request sent by a '.$first_name.' '.$last_name.' .';
                  $arr_event['title']        = 'Commission request sent by a maker';
                  $arr_event['type']         = 'admin';   
                  
                  $this->GeneralService->save_notification($arr_event);
               /**********************Notification to admin END*********************************/

                $response['status']  = 'SUCCESS';
                $response['message'] = 'Commission has been set.';
            }
            else
            {
                $response['status']  = 'ERROR';
                $response['message'] = 'Something went wrong, please try again.';
            }

        }
        else
        {
            $response['status']  = 'ERROR';
            $response['message'] = 'Something went wrong, please try again.';
        }  

        return response()->json($response);   
    }
}

