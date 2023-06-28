<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesModel;
use App\Models\RetailerQuotesProductModel;


use Sentinel;
use Validator;
use DB;

class CustomerController extends Controller
{
    
	public function __construct(UserModel      $UserModel,
                                RoleUsersModel $RoleUsersModel,
                                RoleModel      $RoleModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                RetailerQuotesModel $RetailerQuotesModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel


                                )

    {   
    	$this->arr_view_data                    = [];
        $this->RoleUsersModel                    = $RoleUsersModel;
        $this->UserModel                        = $UserModel;
        $this->RoleModel                        = $RoleModel;
        $this->RepresentativeLeadsModel         = $RepresentativeLeadsModel;
        $this->RepresentativeProductLeadsModel  = $RepresentativeProductLeadsModel;
        $this->RetailerQuotesModel              = $RetailerQuotesModel;
        $this->RetailerQuotesProductModel       = $RetailerQuotesProductModel;
        $this->module_title                     = "Customers listing";
        $this->module_view_folder               = 'maker.customers';
        $this->maker_panel_slug                 = config('
                                                    app.project.maker_panel_slug');
        $this->module_url_path                  = url($this->maker_panel_slug.'/maker');
       //dd($this->module_url_path); 
    }

    
    public function customers_listing()
    {
    // dd("inside makers customers");

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Customers Listing';
        $this->arr_view_data['module_url_path'] = url($this->module_url_path.'/customers_listing');

        return view($this->module_view_folder.'.customer_listing',$this->arr_view_data);

    }
   

    

    public function get_listing_quote_data(Request $request)
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Customers Listing';
        $this->arr_view_data['module_url_path'] = url($this->module_url_path.'/customers_listing');
        
        return view($this->module_view_folder.'.customer_listing',$this->arr_view_data);
       /* return view('maker/shop/customer_listing',$this->arr_view_data);*/
        
    }


    public function get_quote_listing_data(Request $request)
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

        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_quotes =  $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_tbl = DB::getTablePrefix().$retailer_quotes;

       /* $retailer_product_quotes =  $this->RetailerQuotesProductModel->getTable();
        $prefix_retailer_product_quotes_tbl = DB::getTablePrefix().$retailer_product_quotes;*/

       /* $representative_leads =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;*/

       /* $obj_user = DB::table($retailer_quotes)
                        ->select(DB::raw($prefix_retailer_quotes_tbl.".*,".
                                        $prefix_user_table.".email,".
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                         .$prefix_user_table.".last_name) as user_name"),
                        DB::raw("(SELECT count(id) FROM ".$retailer_product_quotes." as RL WHERE RL.retailer_quotes_id = ".$prefix_retailer_quotes_tbl.".id) as total_quotes"))
                       ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefix_retailer_quotes_tbl.'.retailer_id')
                       ->leftjoin($retailer_product_quotes,$prefix_retailer_product_quotes_tbl.'.retailer_quotes_id','=',$prefix_retailer_quotes_tbl.'.id')
                       ->where($retailer_quotes.'.maker_id',$loggedIn_userId)
                       ->groupBy($retailer_quotes.'.retailer_id')
                       ->get();*/
        $obj_user = DB::table($retailer_quotes)                        
                         ->select(DB::raw($prefix_retailer_quotes_tbl.".*,".
                                         $prefix_user_table.".email,".
                                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"),
                         /*DB::raw("(SELECT count(id) FROM ".$retailer_quotes." as RL WHERE RL.retailer_id = ".$prefix_user_table.".id) as total_quotes"),*/
                         DB::raw("(SELECT created_at FROM ".$retailer_quotes." as RL_2 WHERE RL_2.retailer_id = ".$prefix_user_table.".id AND maker_id = ".$loggedIn_userId." ORDER BY RL_2.id DESC LIMIT 0,1  ) as last_quote_date"))
                        ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefix_retailer_quotes_tbl.'.retailer_id')
                        ->where($retailer_quotes.'.maker_id',$loggedIn_userId)            
                        ->groupBy($retailer_quotes.'.retailer_id');
                        /*->get();*/   
       /* ---------------- Filtering Logic ----------------------------------*/  

        if(isset($arr_search_column['q_retailer_id']) && $arr_search_column['q_retailer_id']!="")
        {
            $search_term      = $arr_search_column['q_retailer_id'];
            $obj_user = $obj_user->where($retailer_quotes.'.retailer_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_last_quote_date']) && $arr_search_column['q_last_quote_date']!="")
        {
            $search_term  = $arr_search_column['q_last_quote_date'];
            $search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user     = $obj_user->where($retailer_quotes.'.created_at','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_total_quote']) && $arr_search_column['q_total_quote']!="")
        {
            $search_term      = $arr_search_column['q_total_quote'];
            $obj_user = $obj_user->having('total_quote','LIKE', '%'.$search_term.'%');
        }    
        
        $json_result     = \Datatables::of($obj_user);


        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = 
        $json_result->editColumn('last_quote_date',function($data)
                    {
                        return date('d-M-Y',strtotime($data->last_quote_date));
                    })   
                    ->editColumn('total_quotes',function($data) use($loggedIn_userId)
                    {
                        $retailer_id = $data->retailer_id;
                        //dd($retailer_id);
                        $retailer_quotes_count = get_retailer_quote_count($retailer_id,$loggedIn_userId);
                        return $retailer_quotes_count;
                        //dd($retailer_quotes_count);
                        //return date('d-M-Y',strtotime($data->last_quote_date));
                    })                          
                    ->make(true);

        $build_result = $json_result->getData();
        // dd($build_result);
      
        return response()->json($build_result);
    }
   






    public function get_listing_data(Request $request)
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

        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_quotes =  $this->RetailerQuotesModel->getTable();
        $prefix_retailer_quotes_tbl = DB::getTablePrefix().$retailer_quotes;

        $representative_leads =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

        /*$obj_user = DB::table($user_table." as U")
                        ->select(DB::raw("U.id AS user_id,
                                        (SELECT id FROM ".$retailer_quotes." as RQ_2 WHERE RQ_2.maker_id = U.id ORDER BY RQ_2.id DESC LIMIT 0,1  ) as last_quote_id,
                                        (SELECT id FROM ".$representative_leads." as RL_2 WHERE RL_2.maker_id = U.id  AND RL_2.is_confirm = 1 ORDER BY RL_2.id DESC LIMIT 0,1  ) as last_lead_id,
                                        (SELECT count(id) FROM ".$retailer_quotes." as RQ WHERE RQ.maker_id = U.id ) as total_quotes,
                                        (SELECT count(id) FROM ".$representative_leads." as RL WHERE RL.maker_id = U.id AND RL.is_confirm = 1) as total_leads

                                        "
                                      ))
                        ->where('U.id',$loggedIn_userId)
                        ->get();
                        */
        dd($obj_user);                

                        /*->where($user_table.'.id','!=',1)*/
                        /*->orderBy($user_table.'.created_at','DESC')*/;

        /* ---------------- Filtering Logic ----------------------------------*/  

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }   

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
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

        if(isset($arr_search_column['q_is_approved']) && $arr_search_column['q_is_approved']!="")
        {
            $search_term      = $arr_search_column['q_is_approved'];
            $obj_user = $obj_user->where($user_table.'.is_approved','=', $search_term);
        }
        
        $json_result     = \Datatables::of($obj_user);

        dd($json_result->make(true)->getData());


        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = $json_result->editColumn('enc_id',function($data)
                            {
                                return base64_encode($data->id);
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
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                                $build_view_action = '<a class="btn btn-outline btn-info btn-circle show-tooltip" href="'.$view_href.'" title="View">View</a>';

                                return $build_action = $build_view_action;
                            })
                            ->make(true);

        $build_result = $json_result->getData();
        dd($build_result);
      
        return response()->json($build_result);

    }

    public function index_vie_lead()
    {
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Customers Listing Via Lead';
        $this->arr_view_data['module_url_path'] = url($this->module_url_path.'/customers_listing');

        return view($this->module_view_folder.'.index-vialead',$this->arr_view_data);
    }

    public function  get_listing_data_vie_lead(Request $request)
    {
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }    

        // $obj_user = DB::table($user_table." as U")
        //                 ->select(DB::raw("U.id AS user_id,
        //                                 (SELECT id FROM ".$retailer_quotes." as RQ_2 WHERE RQ_2.maker_id = U.id ORDER BY RQ_2.id DESC LIMIT 0,1  ) as last_quote_id,
        //                                 (SELECT id FROM ".$representative_leads." as RL_2 WHERE RL_2.maker_id = U.id  AND RL_2.is_confirm = 1 ORDER BY RL_2.id DESC LIMIT 0,1  ) as last_lead_id,
        //                                 (SELECT count(id) FROM ".$retailer_quotes." as RQ WHERE RQ.maker_id = U.id ) as total_quotes,
        //                                 (SELECT count(id) FROM ".$representative_leads." as RL WHERE RL.maker_id = U.id AND RL.is_confirm = 1) as total_leads"
        //                               ))
        //                 ->where('U.id',$loggedIn_userId)
        //                 ->get();

// ->select(DB::raw("(SELECT count(id) FROM ".$representative_leads." as RL WHERE RL.maker_id = U.id AND RL.is_confirm = 1) as total_leads"))

        $arr_search_column = $request->input('column_filter');

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $representative_leads =  $this->RepresentativeLeadsModel->getTable();
        $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

        
        $obj_user = DB::table($representative_leads)                        
                         ->select(DB::raw($prefix_representative_leads_tbl.".*,".
                                         $prefix_user_table.".email,".
                                                  "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"),
                         DB::raw("(SELECT count(id) FROM ".$representative_leads." as RL WHERE RL.retailer_id = ".$prefix_user_table.".id AND RL.is_confirm = 1) as total_leads"),
                         DB::raw("(SELECT created_at FROM ".$representative_leads." as RL_2 WHERE RL_2.retailer_id = ".$prefix_user_table.".id  AND RL_2.is_confirm = 1 AND maker_id = ".$loggedIn_userId." ORDER BY RL_2.id DESC LIMIT 0,1  ) as last_lead_date"))
                        ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.retailer_id')
                        ->where($representative_leads.'.maker_id',$loggedIn_userId)        
                        ->where($representative_leads.'.is_confirm',1)        
                        ->groupBy($representative_leads.'.retailer_id');
                        // ->get(); 
                        
                // dd($obj_user);      

        /* ---------------- Filtering Logic ----------------------------------*/  

        
        if(isset($arr_search_column['q_retailer_id']) && $arr_search_column['q_retailer_id']!="")
        {
            $search_term      = $arr_search_column['q_retailer_id'];
            $obj_user = $obj_user->where($representative_leads.'.retailer_id','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_username']) && $arr_search_column['q_username']!="")
        {
            $search_term      = $arr_search_column['q_username'];
            $obj_user = $obj_user->having('user_name','LIKE', '%'.$search_term.'%');
        }  

        if(isset($arr_search_column['q_email']) && $arr_search_column['q_email']!="")
        {
            $search_term      = $arr_search_column['q_email'];
            $obj_user = $obj_user->where($user_table.'.email','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_last_lead_date']) && $arr_search_column['q_last_lead_date']!="")
        {
            $search_term  = $arr_search_column['q_last_lead_date'];
            $search_term  = date('Y-m-d',strtotime($search_term));
            $obj_user     = $obj_user->where($representative_leads.'.created_at','LIKE', '%'.$search_term.'%');
        } 

        if(isset($arr_search_column['q_total_lead']) && $arr_search_column['q_total_lead']!="")
        {
            $search_term      = $arr_search_column['q_total_lead'];
            $obj_user = $obj_user->having('total_leads','LIKE', '%'.$search_term.'%');
        }    
        
        $json_result     = \Datatables::of($obj_user);


        $json_result     = $json_result->blacklist(['id']);
        
        $json_result     = 
        $json_result->editColumn('last_lead_date',function($data)
                            {
                                return date('d-M-Y',strtotime($data->last_lead_date));
                            })                          
                            ->make(true);

        $build_result = $json_result->getData();
        // dd($build_result);
      
        return response()->json($build_result);
    }
   
}

