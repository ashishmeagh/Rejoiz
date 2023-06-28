<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeModel;
use App\Common\Services\ReportService;
use App\Common\Services\PdfReportService;
use App\Models\RepAreaModel;
use App\Models\SalesManagerModel;
use App\Models\StateModel;
use App\Models\CategoryModel;
use App\Models\CategoryDivisionModel;
use DataTable;



use App\Common\Traits\MultiActionTrait;
use DB;
use Flash;
use Validator;
use Sentinel;


class RepresentativeAreaController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 22 June 2019
    */
    use MultiActionTrait;
    public function __construct(
                                UserModel $UserModel,
    							UserService $UserService,
    							EmailService $EmailService,
                                ReportService $ReportService,
                                RepAreaModel $RepAreaModel,
    							RoleModel $RoleModel,
                                SalesManagerModel $SalesManagerModel,
                                StateModel $StateModel,
    							RoleUsersModel $RoleUsersModel,
                                RepresentativeModel $RepresentativeModel,
                                CategoryModel $CategoryModel,
                                PdfReportService $PdfReportService,
                                CategoryDivisionModel $CategoryDivisionModel
    						)
    {
    	$this->UserModel    	  = $UserModel;
    	$this->BaseModel    	  = $UserModel;
        $this->RepresentativeModel= $RepresentativeModel;
        $this->SalesManagerModel  = $SalesManagerModel;
    	$this->UserService  	  = $UserService;
        $this->RepAreaModel       = $RepAreaModel;
    	$this->EmailService 	  = $EmailService;
        $this->ReportService      = $ReportService;
        $this->PdfReportService   = $PdfReportService;
    	$this->RoleModel          = $RoleModel;
    	$this->RoleUsersModel     = $RoleUsersModel;
        $this->StateModel         = $StateModel;
        $this->CategoryModel      = $CategoryModel;
        $this->CategoryDivisionModel  = $CategoryDivisionModel;
    	$this->arr_view_data 	  = [];
    	$this->module_title       = " Area/Regions";
        $this->module_view_folder = "admin.rep_area";
        $this->admin_panel_slug   = config('app.project.admin_panel_slug');
        $this->module_url_path    = url($this->admin_panel_slug.'/rep_area');
        $this->curr_panel_slug    = config('app.project.admin_panel_slug');

    	// $this->role = 'representative';
    }

    public function index()
    {
    	$this->arr_view_data['page_title']      =  str_plural($this->module_title);
        $this->arr_view_data['module_title']    =  str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function create()
    {
        $state_names = $this->StateModel->get()->toArray();
        
        //$category_names = $this->CategoryModel->get()->toArray();

        $category_div_names = $this->CategoryDivisionModel->where('is_active',1)->orderBy('cat_division_name','ASC')->get()->toArray();
       
        $this->arr_view_data['page_title']      = 'Create Area/Region';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;
        $this->arr_view_data['state_names']     = isset($state_names)?$state_names:'';
         $this->arr_view_data['category_names'] = isset($category_div_names)?$category_div_names:'';
        return view($this->module_view_folder.'.create',$this->arr_view_data); 
    }


    public function save(Request $request)
    {
       
        $state_arr  = [];
        $category   = '';
        $form_data  = $request->all();
      
        $is_update  = false;

        $form_data  = $request->all();
       
        $area_id    = $request->input('area_id');
       


        if($request->has('area_id'))
        {
           $is_update = true;
        }        

       
        $arr_rules = [
                'area_name'      => 'required',
                'rep_state'      => 'required'
            ];
        $validator = Validator::make($request->all(),$arr_rules);
       
        $response  = [];

        if($validator->fails())
        {   
            $response['status'] = "failure";
            return response()->json($response); 
        }
     
        /* Check if area already exists with given name*/
          $is_duplicate = $this->RepAreaModel->where('area_name',$form_data['area_name']);

          if($is_update)
          {
            $is_duplicate->where('id','<>',$area_id);
          }
          
          $is_duplicate = $is_duplicate->count()>0;        
         
          if($is_duplicate)
          {  
             $response['status']      = 'warning';
             $response['description'] = 'Area already exist, please try with new one.';

             return response()->json($response);
          } 
          //dd(isset($request['category_names']));
           $arr_user_data = [];
           $arr_user_data['area_name']     = $request->input('area_name');
           $arr_user_data['id']            = $area_id;
           $state_id                       = $request->input('rep_state');
           $category_id                    = isset($request['category_names'])?$request['category_names']:'';

        if(isset($form_data['area_status']) && !empty($form_data['area_status']))
        {
           $area_status = $form_data['area_status'];
        }
        else
        {
           $area_status = '0';
        }

        if(isset($form_data['rep_state']) && !empty($form_data['rep_state']))
        {
            $rep_state = $form_data['rep_state'];
            $state     = json_encode($rep_state);
            
        }
        if(isset($form_data['category_names']) && !empty($form_data['category_names']))
        {
            $category_names = $form_data['category_names'];
            $category       = json_encode($category_names);
           
        }
        // dd($category);
        $area =  RepAreaModel::firstOrNew(['id' => $area_id]);
        
        $area->area_name    = $arr_user_data['area_name'];
        $area->status       = $area_status;
        $area->state_id     = $state;
        $area->category_id  = $category;
        $area->save();

   

        if($is_update==false)
        {
            $response['description']     = str_singular($this->module_title)." has been saved.";
        }
        else
        {
            $response['description']     = str_singular($this->module_title)." has been saved.";
        }
    
        $response['status']      = "success";
        
        $response['link'] = $this->module_url_path.'/';

        return response()->json($response); 
    }


    public function get_area_list(Request $request)
    {
        
        $arr_search_column = $request->input('column_filter');

        $area_table        =  $this->RepAreaModel->getTable();
       
        $prefix_area_table = DB::getTablePrefix().$area_table;
       


        $obj_area = DB::table($area_table)
                                ->select(DB::raw($prefix_area_table.".id,".  
                                                 $prefix_area_table.'.area_name,'.
                                                 $prefix_area_table.'.status'
                                                ))
                                 ->whereNull($prefix_area_table.'.deleted_at')
                                ->orderBy('id','DESC');
      

         $arr_search_column = $request->input('column_filter');
        
        if(isset($arr_search_column['q_area_name']) && $arr_search_column['q_area_name']!="")
        {
            $search_term      = $arr_search_column['q_area_name'];
            $obj_area = $obj_area->where($prefix_area_table.'.area_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            $obj_area = $obj_area->where($prefix_area_table.'.status','LIKE', '%'.$search_term.'%');
        }


        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term      = $arr_search_column['q_status'];
            
            $obj_area     = $obj_area->where($prefix_area_table.'.status',$search_term);
        }
  
        $json_result     = \Datatables::of($obj_area);

        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
                            })   
                             
                            ->editColumn('area_name',function($data)
                            {
                               return isset($data->area_name) && $data->area_name!='' ?$data->area_name:'N/A';
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
                                  $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                              }
                              return $build_status_btn;
                            })

                            ->editColumn('build_action_btn',function($data) 
                            {   
                                 
                                $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);
                                $build_delete_action = '<a class="btn btn-outline btn-danger btn-circle show-tooltip" onclick="confirm_delete($(this),event)" href="'.$delete_href.'" title="Delete">Delete</a>';

                                return $build_action =  $build_delete_action;
                            })

                          
                            ->make(true);

        $build_result = $json_result->getData();

      
        return response()->json($build_result);
    }

    public function edit($area_id)
    {
       
        $id              = base64_decode($area_id);
        $area            = $this->RepAreaModel->where('id', $id)->first();
      
        $state_arr       = json_decode($area['state_id']);
       
        $category_arr    = json_decode($area['category_id']);
       
        $state_names     = $this->StateModel->get()->toArray();
       
        //$category_names  = $this->CategoryModel->get()->toArray();

        $category_div_names  = $this->CategoryDivisionModel->where('is_active',1)->get()->toArray();
        
        $this->arr_view_data['area_id']         = $id; 
        $this->arr_view_data['rep_area']        = $area;
        $this->arr_view_data['state_arr']       = $state_arr;
        $this->arr_view_data['category_arr']    = $category_arr;
        $this->arr_view_data['state_names']     = isset($state_names)?$state_names:'';
        $this->arr_view_data['category_names']  = isset($category_div_names)?$category_div_names:'';
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;

        
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }


    public function delete($enc_id)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_delete(base64_decode($enc_id)))
        {
            Flash::success('Area has been deleted.');
        }
        else
        {
            Flash::error('Error occurred while area deletion.');
        }

        return redirect()->back();
    }

    public function perform_delete($id)
    {
        /*check if area already assign or not*/
        $user = Sentinel::check();

        $area_count_in_rep   = $this->RepresentativeModel->where('area_id',$id)->count();

        $area_count_in_sales = $this->SalesManagerModel->where('area_id',$id)->count(); 


        if($area_count_in_rep>0 && $area_count_in_sales>0)
        {  
           Flash::error("Area can't deleted, first delete from sales manager and representative.");
           return redirect()->back();
        }
        else
        {
            DB::beginTransaction();
            $deletearea = $this->RepAreaModel->where('id',$id)->delete();
            ;
            
            if($deletearea)
            {          
                /*-------------------------------------------------------
                |   Activity log Event
                --------------------------------------------------------*/
                    $arr_event                 = [];
                    $arr_event['ACTION']       = 'REMOVED';
                    $arr_event['MODULE_ID']    = $id;
                    $arr_event['MODULE_TITLE'] = $this->module_title;
                    $arr_event['MODULE_DATA']  = json_encode(['id'=>$id,'status'=>'REMOVED']);
                    $arr_event['USER_ID']      = $user->id;
                    
                    $this->save_activity($arr_event);
                /*----------------------------------------------------------------------*/
                DB::commit();
                Flash::success('Area has been deleted.');
            }
            else
            {
               DB::rollback();
               Flash::error('Error occurred while areas deletion.');
            }

        }
       

    }


    public function status_update(Request $request)
    {
        
        $area_id = base64_decode($request->input('area_id'));
      
        $status = $request->input('status');

        $user = Sentinel::check();


        if($status == 'active')
        {
            $is_active = '1';
        }
        else if($status == 'inactive')
        {
            $is_active = '0';
        }

        $data['status'] = $is_active;

        $update = $this->RepAreaModel->where('id',$area_id)->update($data);

        if($update)
        {
            $response['status']  = 'success';
            $response['message'] = 'Status has been changed.';

            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $category_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id'=>$category_id,'status'=>$status]);
            $arr_event['USER_ID']      = $user->id;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
        }
        else
        {
            $response['status'] = 'error';
            $response['message'] = 'Error occurred while updating status.';
        }

        return response()->json($response);
    }


    public function activate(Request $request)
    {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
          return redirect()->back();
        }

        if($this->perform_activate(base64_decode($enc_id)))
        {
           $arr_response['status']  = 'SUCCESS';
           $arr_response['message'] = 'Area has been activated.';

        }
        else
        {
           $arr_response['status']  = 'ERROR';
           $arr_response['message'] = 'Error occurred while deactivating area.';
        }

        $arr_response['data'] = 'ACTIVE';
        return response()->json($arr_response);
    }

    public function deactivate(Request $request)
    {
        $status = '';
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $status = $this->perform_deactivate(base64_decode($enc_id));
     
        if($status == 'TRUE')
        {
            $arr_response['status']  = 'SUCCESS';
            $arr_response['data']    = 'DEACTIVE';
            $arr_response['message'] = 'Area has been deactivated.';
             return response()->json($arr_response);

        }
  /*      elseif($status == 'Warning') 
        {
           $arr_response['status']  = 'WARNING';
           $arr_response['message'] = "Area can't deactivated first delete from sales manager and representative";
           return response()->json($arr_response);
        }*/
        elseif($status == 'FALSE')
        {  
            $arr_response['status']   = 'ERROR';
            $arr_response['message']  = 'Error occurred while deactivating area.';
             return response()->json($arr_response);
        }

        
        
       
    }

    public function perform_activate($id)
    {
        $entity = $this->RepAreaModel->where('id',$id)->first();
        
        if($entity)
        {   
          $this->RepAreaModel->where('id',$id)->update(['status'=>'1']);

          return TRUE;
        }

        return FALSE;
    }

    public function perform_deactivate($id)
    {  
        /*check that area is already assign or not */
          /*check if area already assign or not*/

      /*  $area_count_in_rep   = $this->RepresentativeModel->where('area_id',$id)->count();

        $area_count_in_sales = $this->SalesManagerModel->where('area_id',$id)->count(); 
        
        if($area_count_in_rep>0 || $area_count_in_sales>0)
        { 
           return 'Warning';
        }
        else
        { */
            $entity = $this->RepAreaModel->where('id',$id)->first();
        
            if($entity)
            {   
              //deactivate the user
              $this->RepAreaModel->where('id',$id)->update(['status'=>'0']);

              return TRUE;
            }
            else
            {
               return FALSE;
            }
       // }

        /*----------------------------------------*/

        
  }






     

}

