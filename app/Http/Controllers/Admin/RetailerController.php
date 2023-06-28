<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Common\Services\UserService;
use App\Common\Services\EmailService;
use App\Common\Services\ReportService;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RetailerModel;
use App\Models\ActivationModel;

use App\Common\Traits\MultiActionTrait;
use App\Common\Services\PdfReportService;
use App\Common\Services\GeneralService;

use PDF;
use DB;
use DateTime;
class RetailerController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 22 June 2019
    */
    use MultiActionTrait;
    public function __construct(UserModel $UserModel,
    							UserService $UserService,
    							EmailService $EmailService,
                                ReportService $ReportService,
                                PdfReportService $PdfReportService,
                                GeneralService $GeneralService,
    							RoleModel $RoleModel,
    							RoleUsersModel $RoleUsersModel,
                                RetailerModel $RetailerModel,
                                ActivationModel $ActivationModel
    						)
    {
    	$this->UserModel    	  = $UserModel;
    	$this->BaseModel    	  = $UserModel;
    	$this->UserService  	  = $UserService;
        $this->PdfReportService   = $PdfReportService;
    	$this->EmailService 	  = $EmailService;
        $this->ReportService      = $ReportService;
        $this->GeneralService     = $GeneralService;
    	$this->RoleModel          = $RoleModel;
    	$this->RoleUsersModel     = $RoleUsersModel;
        $this->RetailerModel      = $RetailerModel;
        $this->ActivationModel    = $ActivationModel;
    	$this->arr_view_data 	  = [];
    	$this->module_title       = "Customer";
        $this->module_view_folder = "admin.retailer";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/retailer");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');

    	$this->role = 'retailer';
    }

    public function index(){
    	$this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_retailer(Request $request)
    {
    	$arr_search_column = $request->input('column_filter');

    	$user_table =  $this->UserModel->getTable();
		$prefix_user_table = DB::getTablePrefix().$user_table;

		$role_table =  $this->RoleModel->getTable();
		$prefix_role_table = DB::getTablePrefix().$role_table;

		$role_user_table =  $this->RoleUsersModel->getTable();
		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $retailer_table = $this->RetailerModel->getTable();
        // dd($retailer_table);
        $prefix_retailer_table = DB::getTablePrefix().$retailer_table;
        // dd($prefix_retailer_table);

		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as id,".
                                     $prefix_user_table.".email as email, ".
                                     $prefix_user_table.".status, ".
                                     $prefix_user_table.".is_approved, ".
                                     $prefix_user_table.".wallet_address as wallet_address,".
                                     $prefix_user_table.".status_net_30,".
                                     $prefix_user_table.".contact_no as contact_no, ".
                                     $prefix_user_table.".country_code, ".
                                     $prefix_user_table.".created_at, ".
                                     $role_table.".slug as slug, ".
                                     $role_table.".name as name, ".
                                     // $retailer_table.".store_name as store_name"
                                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                                     ))
						->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
						->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
                        // ->leftJoin($retailer_table,$role_user_table.'.role_id','=',$role_table.'.id')
						->where($role_table.'.slug','=',$this->role)
                        ->where($user_table.'.id','!=',1)
						->whereNull($user_table.'.deleted_at')
                        ->orderBy($user_table.'.created_at','DESC');


                        // dd($obj_user->get()->toArray());


        /* ---------------- Filtering Logic ----------------------------------*/  

        
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

         if(isset($arr_search_column['q_status_net_30']) && $arr_search_column['q_status_net_30']!="")
        {
            $search_term      = $arr_search_column['q_status_net_30'];
            $obj_user = $obj_user->where($user_table.'.status_net_30','=', $search_term);
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
                           
                            ->editColumn('build_status_btn',function($data)
                            {
                                $build_status_btn ='';
                                if($data->status == '0')
                                {   
                                    $build_status_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                elseif($data->status == '1')
                                {
                                    $build_status_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" readonly/>';
                                }
                                return $build_status_btn;
                            }) 

                             ->editColumn('build_status_net_30',function($data)
                                {
                                $build_status_net_30_btn ='';
                                if($data->status_net_30 == '0')
                                {   
                                    $build_status_net_30_btn = '<input type="checkbox" data-size="small" data-enc_id="'.base64_encode($data->id).'"  class="js-switch toggleSwitch net_30" data-type="net_30_activate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
                                elseif($data->status_net_30 == '1')
                                {
                                    $build_status_net_30_btn = '<input type="checkbox" checked data-size="small"  data-enc_id="'.base64_encode($data->id).'"  id="status_'.$data->id.'" class="js-switch toggleSwitch net_30" data-type="net_30_deactivate" data-color="#99d683" data-secondary-color="#f96262" />';
                                }
/*                                dd($build_status_net_30_btn);
*/                                return $build_status_net_30_btn;
                            })      
                           
                            ->editColumn('build_action_btn',function($data) 
                            {   
                                $view_href =  $this->module_url_path.'/view/'.base64_encode($data->id);
                                $delete_href =  $this->module_url_path.'/delete/'.base64_encode($data->id);

                                $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'" title="View">View</a>';

                                

                                return $build_action = $build_view_action.' ';
                            })

                            ->editColumn('contact_no',function($data)
                            {

                                $contactNo = $data->contact_no;
                                if($data->country_code == "")
                                {
                                    $contactNo = str_replace($data->country_code,"",$contactNo);
                                    $contactNo = get_contact_no($contactNo);
                                }
                                else
                                {
                                    $contactNo = str_replace($data->country_code,"",$contactNo);
                                    $contactNo = $data->country_code.'-'.get_contact_no($contactNo);
                                }
                            
                                return $contactNo;
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



    public function save(Request $request)
    {
        $is_update = false;

        $form_data = $request->all();
        $user_id = $request->input('user_id');

        if($request->has('user_id'))
        {
           $is_update = true;

        }

      
        $arr_rules = [
                        /*'tax_id'=>'required',*/
                        'first_name'=>'required',
                        'last_name'=>'required',
                        'email'=>'required|email',
                    
                       
                        'contact_no'=>'required|numeric',
                        'primary_category_id'=>'required',
                        'no_of_store'=>'required',
                        'nationality'=>'required',
                        'instagram_handle'=>'required',
                        
                        
                     ];

       
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            return redirect()->back()
                            ->withInput($request->all())
                            ->withErrors($validator);
        }

       
        /* Check for email duplication */
        $is_duplicate =  $this->UserModel->where('email','=',$request->input('email'));  
        
        if($is_update)
        {
            $is_duplicate = $is_duplicate->where('id','<>',$user_id);
        }

        $does_exists = $is_duplicate->count();

        if($does_exists)
        {
           $response['status']      = "error";
           $response['description'] = "Email id already exists.";
           return response()->json($response);
        }   
         
      

     $profile_img_file_path = '';
            
            $profile_image = $form_data['profile_image'];

            if($profile_image!=null)
            {
                //Validation for product image
                $file_extension = strtolower( $profile_image->getClientOriginalExtension());

                if(!in_array($file_extension,['jpg','png','jpeg']))
                {                           
                    $arr_response['status']       = 'FAILURE';
                    $arr_response['description']  = 'Invalid profile image, please try again.';

                    return response().json($response);
                }

                $profile_img_file_path = $profile_image->store('profile_image');
            }

            
        $arr_user_data['first_name'] = $request->input('first_name');
        $arr_user_data['last_name'] = $request->input('last_name');
        $arr_user_data['email']      = $request->input('email');
        $arr_user_data['password']   = $request->input('password');
        $arr_user_data['contact_no'] = $request->input('contact_no');
        $arr_user_data['nationality'] = $request->input('nationality');
        $arr_user_data['profile_image'] = $profile_img_file_path;

        $arr_maker_data['tax_id'] = $request->input('tax_id');
        $arr_maker_data['brand_name'] = $request->input('brand_name');
        $arr_maker_data['primary_category_id'] = $request->input('primary_category_id');
        $arr_maker_data['no_of_stores'] = $request->input('no_of_store');
        $arr_maker_data['instagram_handle'] = $request->input('instagram_handle');
        $arr_maker_data['website_url'] = $request->input('website_url');

        /* File Upload */
        $user = Sentinel::createModel()->where(['id' => $user_id])->first();

        if($user == false)
        {
            $user = Sentinel::registerAndActivate([
                'email' => $arr_user_data['email'],
                'password' => $arr_user_data['password'],

            ]);

            if($user){

                $role = Sentinel::findRoleBySlug('maker');

                $role->users()->attach($user);
            }
        }
        else
        {
            Sentinel::update($user, [
                'email' => $arr_user_data['email'],
                'password' => $arr_user_data['password'],

            ]);
        }

        $user->first_name = $arr_user_data['first_name'];
        $user->last_name = $arr_user_data['last_name'];
        $user->email = $arr_user_data['email'];
        // $user->password = $arr_user_data['password'];
        $user->contact_no = $arr_user_data['contact_no'];
        $user->nationality = $arr_user_data['nationality'];
        $user->profile_image = $arr_user_data['profile_image'];
        $user->save();

        $maker = MakerModel::firstOrNew(['user_id' => $user->id]);

        $maker->brand_name = $arr_maker_data['brand_name'];
        $maker->tax_id = $arr_maker_data['tax_id'];
        $maker->primary_category_id = $arr_maker_data['primary_category_id'];
        $maker->no_of_stores = $arr_maker_data['no_of_stores'];
        $maker->insta_url = $arr_maker_data['instagram_handle'];
        $maker->website_url =  $arr_maker_data['website_url'];

        $maker->save();
        
       

        $response['status']      = "success";
  
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
             $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'ACTIVE';
        return response()->json($arr_response);
    }

    public function deactivate(Request $request)
    {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_deactivate(base64_decode($enc_id)))
        {
            $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'DEACTIVE';

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
            $this->UserModel->where('id',$id)->update(['status'=>'0', 'is_approved' => 0]);

            return TRUE;
        }
        return FALSE;
    }


    public function net_30_activate(Request $request)
    { 
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_net_30_activate(base64_decode($enc_id)))
        {
             $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'ACTIVE';
        return response()->json($arr_response);
    }

    public function net_30_deactivate(Request $request)
    {  
        $enc_id = $request->input('id');
      
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_net_30_deactivate(base64_decode($enc_id)))
        {
            $arr_response['status'] = 'SUCCESS';
        }
        else
        {
            $arr_response['status'] = 'ERROR';
        }

        $arr_response['data'] = 'DEACTIVE';

        return response()->json($arr_response);
    }

    public function perform_net_30_activate($id)
    {
        $entity = $this->UserModel->where('id',$id)->first();
        
        if($entity)
        {   
            //Activate the user
            $this->UserModel->where('id',$id)->update(['status_net_30'=>1]);
              $arr_notify_data                 = [];
              $arr_notify_data['from_user_id'] = get_admin_id();
              $arr_notify_data['to_user_id']   = $id or '';

              $arr_notify_data['description']  = "Your Net30 Payment status has been activated by admin, you can start the payment with the net30 payment method.";
              $arr_notify_data['title']        = 'Net30 Payment Status';
              $arr_notify_data['type']         = 'retailer';  
              $arr_notify_data['link']         = '';  

              $this->GeneralService->save_notification($arr_notify_data);

            return TRUE;
        }

        return FALSE;
    }

    public function perform_net_30_deactivate($id)
    {   
        $entity = $this->UserModel->where('id',$id)->first();
        
        if($entity)
        {   
            //deactivate the user
            $this->UserModel->where('id',$id)->update(['status_net_30'=>0]);

            return TRUE;
        }
        return FALSE;
    }

    public function create()
    { 
        $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']      = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function view($enc_id)
    {	
    	$arr_user = [];

    	$user_id = base64_decode($enc_id);

    	$arr_user = $this->UserService->get_user_information($user_id,$this->role);
        
    	$this->arr_view_data['arr_user']        = $arr_user;

    	$this->arr_view_data['page_title']      = str_singular( $this->module_title).' Details';
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
    	return view($this->module_view_folder.'.view',$this->arr_view_data);
    }



    function changeAprovalStatus(Request $request)
    {
      $retailer_status      = $request->input('retailerAprovalStatus');
      $retailer_id          = $request->input('retailer_id');
      $type                 = $request->input('type');

      // dd($request->all());
      if($retailer_status=='1')
      {
        $this->UserModel->where('id',$retailer_id)->update(['is_approved'=>1]);
        
        // $response['status']  = 'SUCCESS';
        // $response['message'] = $this->module_title.' has been approved.';
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
      elseif($retailer_status=='0')
      {
        $this->UserModel->where('id',$retailer_id)->update(['is_approved'=>0]);

        // $response['status']  = 'SUCCESS';
        // $response['message'] = $this->module_title.' has been disapproved.';
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
      else
      {
        $response['status']  = 'ERROR';
        $response['message'] = 'Something went wrong,  try again.';
      }
      
    return response()->json($response); 
    }

     public function report_generator(Request $request,$type=false)
    {         $type  = \Request::segment(4);

       
        if($type=="pdf")
        {
         $inventory_pdf =  $this->PdfReportService->downloadPdfRetailer($type);
         
          return $inventory_pdf->download('retailer report.pdf');
        }
        else
        {
            $this->ReportService->downloadExcelRetailer($type);
        }
    }

}
