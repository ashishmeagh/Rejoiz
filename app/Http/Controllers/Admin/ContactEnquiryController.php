<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\ContactEnquiryModel;

use Datatables;
use DB;

class ContactEnquiryController extends Controller
{
    public function __construct(
    							ContactEnquiryModel $ContactEnquiryModel
    						   )
    {
    	$this->ContactEnquiryModel = $ContactEnquiryModel;

    	$this->arr_view_data 	  = [];
    	$this->module_title       = "Contact Enquiry";
    	$this->module_view_folder = "admin.contact_enquiry";
    	$this->module_url_path    = url(config('app.project.admin_panel_slug')."/contact_enquiry");
    }


    public function index()
    {
    	$this->arr_view_data['page_title']      = "Manage ".str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_data(Request $request){

    	$obj_data     = $this->get_contact_enquiry_data($request);

    	$module_url_path = $this->module_url_path;

        $json_result     = \Datatables::of($obj_data);

        // $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
                            })    
                            ->editColumn('build_action_btn',function($data) use($module_url_path)
                            {   
                                $view_href =  $module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'" title="View"><i class="ti-eye" ></i></a>';

                                return $build_action = $build_view_action;
                            })
                            ->make(true);

        $build_result = $json_result->getData();

        
        return response()->json($build_result);
    }


    public function get_contact_enquiry_data(Request $request){
    	$contact_enquiry_table           = $this->ContactEnquiryModel->getTable();
        $prefixed_contact_enquiry_table  = DB::getTablePrefix().$this->ContactEnquiryModel->getTable();
        $obj_contact_enquiry = DB::table($contact_enquiry_table)
                                ->select(DB::raw($prefixed_contact_enquiry_table.".*"
                                                 ))
                                ->whereNull($contact_enquiry_table.'.deleted_at')
                                ->orderBy($contact_enquiry_table.'.id','DESC');

        $arr_search_column = $request->input('column_filter');

        
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term      = $arr_search_column['q_name'];
            $obj_contact_enquiry = $obj_contact_enquiry->where($contact_enquiry_table.'.name','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_contact_enquiry = $obj_contact_enquiry->where($contact_enquiry_table.'.email','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_subject']) && $arr_search_column['q_subject']!="")
        {
            $search_term      = $arr_search_column['q_subject'];
            $obj_contact_enquiry = $obj_contact_enquiry->where($contact_enquiry_table.'.name','LIKE', '%'.$search_term.'%');
        }

        return $obj_contact_enquiry;                            
    }

    public function get_users(Request $request)
    {
    	$arr_search_column = $request->input('column_filter');

    	$response = $this->UserService->get_datatable_records($this->role,$arr_search_column,$this->module_url_path);

    	return $response;
    }


    public function view($enc_id)
    {	
    	$arr_user = [];

    	$id = base64_decode($enc_id);

    	$arr_data = [];
    	$obj_data = $this->ContactEnquiryModel->where('id',$id)->first();
    	if($obj_data){
    		$arr_data = $obj_data->toArray();
    	}


    	$this->arr_view_data['arr_data']        = $arr_data;
    	$this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
    	$this->arr_view_data['module_title']    = str_plural($this->module_title);
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        
    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

    
}
