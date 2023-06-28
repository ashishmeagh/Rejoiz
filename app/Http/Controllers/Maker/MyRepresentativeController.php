<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\RetailerQuotesModel;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\TransactionsModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\TransactionMappingModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\RepresentativeModel;
use App\Models\RepAreaModel;
use App\Common\Services\EmailService;
use App\Common\Services\GeneralService;
use App\Common\Services\UserService;
use App\Common\Services\orderDataService;


use Sentinel;
use DB;
use Validator;
use Flash;
use DataTable;

class MyRepresentativeController extends Controller
{
    /* 
    |  Show Retailer orders with status   
	|  Author : Shital Vijay More
	|  Date   : 29 Aug 2019
	*/
	public function __construct(RetailerQuotesModel $retailer_quote,UserModel $user_model,
                                ProductsModel $product_model,RetailerQuotesProductModel $retailer_quotes,
                                MakerModel $MakerModel,
                                TransactionsModel $TransactionsModel,
                                TransactionMappingModel $TransactionMappingModel,
                                EmailService $EmailService,
                                GeneralService $GeneralService,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeModel $RepresentativeModel,
                                RepAreaModel $RepAreaModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                UserService $UserService,
                                orderDataService $orderDataService
                               )
    {  
    	  $this->arr_view_data              = [];
    	  $this->module_title               = "My Representatives";
    	  $this->module_view_folder         = 'maker.my_representative'; 
    	  $this->maker_panel_slug        = config('app.project.maker_panel_slug');
    	  $this->module_url_path            = url($this->maker_panel_slug.'/my_representative');
        $this->RetailerQuotesModel        = $retailer_quote;
        $this->UserModel                  = $user_model;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->ProductsModel              = $product_model;
        $this->MakerModel                 = $MakerModel;
        $this->TransactionsModel          = $TransactionsModel;
        $this->TransactionMappingModel    = $TransactionMappingModel;
        $this->EmailService               = $EmailService;
        $this->GeneralService             = $GeneralService;
        $this->UserService                =$UserService;
        $this->RetailerQuotesProductModel = $retailer_quotes;
        $this->RoleModel                  = $RoleModel;
        $this->RepresentativeModel        =$RepresentativeModel;
        $this->RepAreaModel               =$RepAreaModel;
         $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
        $this->orderDataService           = $orderDataService;
        $this->RepresentativeProductLeadsModel  = $RepresentativeProductLeadsModel;
    }

    public function index()
    {
        $this->arr_view_data['page_title']      =  $this->module_title;
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_listing_data(Request $request)
    {

        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
            //dd($loggedIn_userId);
        }    

        $arr_search_column = $request->input('column_filter');
        // dd($arr_search_column);

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $vendor_representative_mapping_table = $this->VendorRepresentativeMappingModel->getTable();
        $prefix_vendor_representative_mapping_table = DB::getTablePrefix().$vendor_representative_mapping_table;

        $representative_table = $this->RepresentativeModel->getTable();
        $prefix_representative_table = DB::getTablePrefix().$representative_table;

        $rep_area_table = $this->RepAreaModel->getTable();
        $prefix_rep_area_table = DB::getTablePrefix().$rep_area_table;

        // dd($representative_table,$rep_area_table);

        $obj_user = DB::table($vendor_representative_mapping_table)
                   ->select(DB::raw($prefix_user_table.".id as uid,".
                                     $prefix_user_table.".email as email, ".
                                    $vendor_representative_mapping_table.".representative_id, ".
                                    $representative_table.".id as rid,".
                                    $representative_table.".area_id,".
                                    $rep_area_table.".id as aid,".
                                    $rep_area_table.".area_name,".
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
                       ->leftJoin($prefix_user_table,$vendor_representative_mapping_table.'.representative_id','=',$user_table.'.id')

                       ->leftJoin($representative_table,$vendor_representative_mapping_table.'.representative_id','=',$representative_table.'.user_id')

                       ->leftJoin($prefix_rep_area_table,$representative_table.'.area_id','=',$rep_area_table.'.id')
                       ->where('vendor_id',$loggedIn_userId);
                      /* ->get();*/

   
       
        if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
        {
            // dd(123);
            $search_term      = $arr_search_column['q_representative_name'];
          
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
  
           
        }   
        if(isset($arr_search_column['q_representative_email']) && $arr_search_column['q_representative_email']!="")
        {
            $search_term      = $arr_search_column['q_representative_email'];
            $obj_user = $obj_user->where($prefix_user_table.'.email','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_representative_area']) && $arr_search_column['q_representative_area']!="")
        {
            $search_term      = $arr_search_column['q_representative_area'];
            $obj_user = $obj_user->where('area_name','LIKE', '%'.$search_term.'%');
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

                            ->make(true);

        $build_result = $json_result->getData();
        // dd($build_result);
        
        return response()->json($build_result);

    }

     public function view(Request $request,$uid = 0)
    {

        // $loggedInUserId = 0;
        // $user = Sentinel::check();

        // if($user)
        // {
        //     $loggedInUserId = $user->id;
        // }

        // $enquiry_id = base64_decode($enquiry_id);
        // $enquiry_arr = [];


       //  $enquiry_obj = $this->RepresentativeLeadsModel->with(['leads_details.product_details',
       //    'leads_details','order_details','retailer_user_details','transaction_mapping','transaction_details','address_details'])
       //                 ->where('order_no',$enquiry_id)->first();

       // if($enquiry_obj)
       //  {
       //      $enquiry_arr = $enquiry_obj->toArray(); 
                 
       //  }
        $uid = base64_decode($uid);
        // dd($uid);
         // $obj_rep = $this->UserModel->where('id',$uid)->with(['representative_details','sales_manager_details.area_details'])->first();

        $obj_rep = $this->RepresentativeModel->where('user_id',$uid)->with(['get_user_details','get_area_details','sales_manager_details.get_user_data'])->first();
         // dd($obj_rep);
        if($obj_rep)
        {
           $arr_data = $obj_rep->toArray(); 
           // dd($arr_data);   
        }
        // $this->arr_view_data['enquiry_arr']     = $enquiry_arr;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['arr_data']        = $arr_data;    
        $this->arr_view_data['page_title']      = 'Representative Details';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        // dd($this->arr_view_data);
        return view($this->module_view_folder.'.view',$this->arr_view_data);
    }
}
