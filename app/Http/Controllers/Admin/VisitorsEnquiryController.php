<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Common\CommonService;

use App\Models\VisitorsEnquiryModel;

use Datatables;
use DB;
use Excel;

class VisitorsEnquiryController extends Controller
{
    //

    public function __construct(
        VisitorsEnquiryModel $VisitorsEnquiryModel,
        CommonService $CommonService
       )
{
$this->VisitorsEnquiryModel = $VisitorsEnquiryModel;
$this->CommonService = $CommonService;

$this->arr_view_data 	  = [];
$this->module_title       = "Visitors Enquiry";
$this->module_view_folder = "admin.visitors_enquiry";
$this->module_url_path    = url(config('app.project.admin_panel_slug')."/visitors_enquiry");
}


public function index()
    {
        
    	$this->arr_view_data['page_title']      = "Manage ".str_singular( $this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_data(Request $request){

    	$obj_data     = $this->get_visitors_enquiry_data($request);

    	$module_url_path = $this->module_url_path;

        $json_result     = \Datatables::of($obj_data);

        
        
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

    public function get_visitors_enquiry_data(Request $request){
    	$visitors_enquiry_table           = $this->VisitorsEnquiryModel->getTable();
        $prefixed_visitors_enquiry_table  = DB::getTablePrefix().$this->VisitorsEnquiryModel->getTable();
        $obj_visitors_enquiry = DB::table($visitors_enquiry_table)
                                ->select(DB::raw($prefixed_visitors_enquiry_table.".*"
                                                 ))
                                
                                ->orderBy($visitors_enquiry_table.'.id','DESC');

        $arr_search_column = $request->input('column_filter');

        
        if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term      = $arr_search_column['q_name'];
            $obj_visitors_enquiry = $obj_visitors_enquiry->where($visitors_enquiry_table.'.name','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_mobile_no']) && $arr_search_column['q_mobile_no']!="")
        {
            $search_term      = $arr_search_column['q_mobile_no'];
            $obj_visitors_enquiry = $obj_visitors_enquiry->where($visitors_enquiry_table.'.mobile_no','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_type']) && $arr_search_column['q_type']!="")
        {
            $search_term      = $arr_search_column['q_type'];
            $obj_visitors_enquiry = $obj_visitors_enquiry->where($visitors_enquiry_table.'.type','LIKE', '%'.$search_term.'%');
        }
        //dd($obj_visitors_enquiry);
        return $obj_visitors_enquiry;                            
    }

    


    public function view($enc_id)
    {	
    	$arr_user = [];

    	$id = base64_decode($enc_id);

    	$arr_data = [];
    	$obj_data = $this->VisitorsEnquiryModel->where('id',$id)->first();
    	if($obj_data){
    		$arr_data = $obj_data->toArray();
    	}


    	$this->arr_view_data['arr_data']        = $arr_data;
    	$this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
    	$this->arr_view_data['module_title']    = str_plural($this->module_title);
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        
    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }

    public function get_export_visitors_enquiry(Request $request)
    {
        
      $type  = 'csv'; 
      $data = $arr_visitors_enquiry = $arrayResponseData = [];

      $form_data['column_filter'] = $request->all();
      $arr_visitors_enquiry     = $this->CommonService->get_visitors_enquiry_list($form_data)->get();
      

      if(count($arr_visitors_enquiry) <= 0)
      {
          $response['status']      = 'error';
          $response['message']      = 'No data available for export';
          
          return response()->json($response);
      }
      
      foreach($arr_visitors_enquiry as $key => $value)
      { 
        $arrayResponseData['Name']          =           $value->name;
        $arrayResponseData['MobileNo']              = $value->mobile_no;
        $arrayResponseData['Type']          =         $value->type;
        $arrayResponseData['Created On']          =   $value->created_at;
        array_push($data,$arrayResponseData);
      }
      
      return Excel::create('Visitors Enquiry', function($excel) use ($data) {
        
        $excel->sheet('Visitors Enquiry', function($sheet) use ($data)
        {
          $sheet->fromArray($data);
          $sheet->freezeFirstRow();  
          $sheet->cells("A1:D1", function($cells) {            
            $cells->setFont(array(              
              'bold'       =>  true
            ));

          });
        });
      })->download($type);

    }
}
