<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\CustomerModel;
use App\Models\GetQuoteModel;
use App\Models\ProductsModel;
use App\Models\BrandsModel;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;

use Sentinel;
use DB;
use Datatables;
use Flash;
use DateTime;
use Excel;

class QuoteRequestsController extends Controller
{
     public function __construct(UserModel $UserModel,
                                MakerModel $MakerModel,
                                GeneralService $GeneralService,
                                CustomerModel $CustomerModel,
                                GetQuoteModel $GetQuoteModel,
                                ProductsModel $ProductsModel,
                                BrandsModel $BrandsModel,
                                EmailService $EmailService
    							)
    {
    	                      	
    	$this->UserModel               = $UserModel;
        $this->MakerModel              = $MakerModel;
        $this->CustomerModel           = $CustomerModel;
        $this->GeneralService          = $GeneralService;
    	$this->arr_view_data           = [];
    	$this->module_title            = "Quote Requests";
    	$this->module_view_folder      = 'admin.quotes';
        $this->admin_panel_slug        = config('app.project.admin_panel_slug');
        $this->module_url_path         = url($this->admin_panel_slug.'/quote_requests');
        $this->GetQuoteModel           = $GetQuoteModel;
        $this->ProductsModel           = $ProductsModel;
        $this->BrandsModel             = $BrandsModel;
        $this->EmailService            = $EmailService;
    }

    // Get all get a quote requests
    public function index(Request $request)
    {  

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Quote Requests';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->admin_panel_slug;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_all_get_quote_requests(Request $request)
    { 
      $quote_request_tbl_name = $this->GetQuoteModel->getTable();      
      $user_tbl_name              = $this->UserModel->getTable();
      $products_tbl_name          = $this->ProductsModel->getTable();
      $maker_tbl_name             = $this->MakerModel->getTable();
      $brands_tbl_name             = $this->BrandsModel->getTable();
            
      $obj_qutoes = DB::table($quote_request_tbl_name)
                                ->select(DB::raw($quote_request_tbl_name.".*,".
                                                $products_tbl_name.".id as productId,".
                                                $products_tbl_name.".product_name,".
                                                $products_tbl_name.".description,".
                                                $products_tbl_name.".unit_wholsale_price,".
                                                $products_tbl_name.".retail_price,".
                                                $products_tbl_name.".available_qty,".
                                                $products_tbl_name.".product_image,".
                                                $user_tbl_name.".email as vendor_email,".
                                                $user_tbl_name.".first_name,".
                                                $user_tbl_name.".last_name,".
                                                $user_tbl_name.".contact_no,".
                                                $brands_tbl_name.".brand_name,".
                                                $maker_tbl_name.".company_name,".
                                                $maker_tbl_name.".brand_name as productbrand,".
                                                "CONCAT(".$user_tbl_name.".first_name,' ',"
                                                          .$user_tbl_name.".last_name) as vendor_name")) 
                                ->leftjoin($products_tbl_name,$products_tbl_name.'.id','=',$quote_request_tbl_name.'.product_id')                                                                                 
                                ->leftjoin($user_tbl_name,$user_tbl_name.'.id','=',$quote_request_tbl_name.'.vendor_id')                                                                 
                                ->leftjoin($maker_tbl_name,$user_tbl_name.'.id','=',$maker_tbl_name.'.user_id')                                                                 
                                ->leftjoin($brands_tbl_name,$brands_tbl_name.'.id','=',$products_tbl_name.'.brand')                                                                 
                                ->orderBy($quote_request_tbl_name.".id",'DESC');
                                
        
        /* ---------------- Filtering Logic ----------------------------------*/                    
        $arr_search_column = $request->input('column_filter');
              
       
        /*---Search by using company name 2020/12/17--*/
        if(isset($arr_search_column['q_vendorname']) && $arr_search_column['q_vendorname']!="")
        {
            $search_term      = $arr_search_column['q_vendorname'];
            $obj_qutoes = $obj_qutoes->where($maker_tbl_name.'.company_name','LIKE', '%'.$search_term.'%');
        }

        /*Updated code for searching by date and status , 2020/12/17*/
        if(isset($arr_search_column['q_generate_date']) && $arr_search_column['q_generate_date']!="")
        {
            $search_term  = $arr_search_column['q_generate_date'];
            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_qutoes   = $obj_qutoes->where($quote_request_tbl_name.'.created_at','LIKE', '%'.$date.'%');
        }
        if(isset($arr_search_column['q_delivery_date']) && $arr_search_column['q_delivery_date']!="")
        {
            $search_term  = $arr_search_column['q_delivery_date'];
            $date         = DateTime::createFromFormat('m/d/Y',$search_term);
            $date         = $date->format('Y-m-d');

            $obj_qutoes   = $obj_qutoes->where($quote_request_tbl_name.'.expected_delivery_date','LIKE', '%'.$date.'%');
        }
        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {
            $search_term = $arr_search_column['q_status'];
            $obj_qutoes  = $obj_qutoes->where($quote_request_tbl_name.'.status','LIKE', '%'.$search_term.'%');
        }
        /*----end---*/      
        $current_context = $this;

        $json_result  = Datatables::of($obj_qutoes);
		
		$json_result  = $json_result->editColumn('generate_date',function($data) use ($current_context)
                        {
                            $created_at = $data->created_at;
                            $created_at = date("m-d-Y", strtotime($created_at));
                            return $created_at;
                        })
                        ->editColumn('expected_delivery_date',function($data) use ($current_context)
                        {
                            $expected_delivery_date = $data->expected_delivery_date;
                            $expected_delivery_date = date("m-d-Y", strtotime($expected_delivery_date));
                            return $expected_delivery_date;
                        })
                        
                        ->editColumn('status',function($data) use ($current_context)
                        {
                            $status = ""; 
                            if($data->status == 0)
                            {
                              $status = '<span class="label label-warning">Pending</span>';
                            }
                            else if($data->status == 1)
                            {
                              $status = '<span class="label label-success">Email Sent</span>'; 
                            }
                            else if($data->status == 2)
                            {
                              $status = '<span class="label label-warning">Rejected</span>';
                            }
                            else
                            {
                              $status = '<span class="label label-warning">Cancelled</span>';
                            } 
                                                        
                            return $status;
                        })
                        ->editColumn('action',function($data) use ($current_context)
                        {
                           
                            $actionButton = ""; 
                            if($data->status == 1)
                            {
                              // action button
                              $actionButton .= '<a href="javascript:void(0)" id="buttonSendEmail_'.$data->id.'" onclick="sendVendorEmail('.$data->id.')" data-quoteid="'.$data->id.'" data-vendoremail="'.$data->vendor_email.'" data-vendorname="'.$data->vendor_name.'" data-newbrandname="'.$data->brand_name.'" data-productname="'.$data->product_name.'" data-companyname="'.$data->company_name.'" data-quotequantity="'.$data->quantity.'" data-expecteddeliverydays="'.$data->no_of_days_to_expected_delivery.'" data-toggle="tooltip"  data-size="small" title="Send email to vendor" class="btn btn-circle btn-outline btn-success show-tooltip buttonSendEmail">Send Email Again</a>';  
                            }
                            else
                            {
                              // action button
                              $actionButton .= '<a href="javascript:void(0)" id="buttonSendEmail_'.$data->id.'" onclick="sendVendorEmail('.$data->id.')" data-quoteid="'.$data->id.'" data-vendoremail="'.$data->vendor_email.'" data-vendorname="'.$data->vendor_name.'" data-newbrandname="'.$data->brand_name.'" data-productname="'.$data->product_name.'" data-companyname="'.$data->company_name.'" data-quotequantity="'.$data->quantity.'" data-expecteddeliverydays="'.$data->no_of_days_to_expected_delivery.'" data-toggle="tooltip"  data-size="small" title="Send email to vendor" class="btn btn-circle btn-outline btn-success show-tooltip buttonSendEmail">Send Email</a>'; 
                            }                           
                            
                            $actionButton .= '<a href="'.url('admin/quote_requests/view').'/'.base64_encode($data->id).'" data-toggle="tooltip"  data-size="small" title="View quote details" class="btn btn-circle btn-outline btn-success show-tooltip">View</a>'; 
                            
                            return $actionButton;
                        });

        $build_result = $json_result->make(true)->getData();
          
        return response()->json($build_result);
    }
    
    // Send email to vendor
  public function send_quote_email_to_vendor(Request $request)
  {
      $form_data = $request->all();   
                            
      $site_setting_arr = $request->attributes->get('site_setting_arr');   
      
      // Get admin details
      $admin_details_obj = $this->UserModel->where('id',1)
                                           ->where('status','1')
                                           ->where('is_approved','1')
                                           ->first();
      
     
      if(isset($admin_details_obj))
      {
        $admin_details = $admin_details_obj->toArray();
      }
      

      $admin_name =  $admin_details['first_name'];                            
      $admin_email =  $admin_details['email'];      
      
      
      //check all email variables are set
      $form_data['vendor_email'] = isset($form_data['vendor_email'])?$form_data['vendor_email']:'-';
      $form_data['vendor_name'] = isset($form_data['vendor_name'])?$form_data['vendor_name']:'-';
      $form_data['company_name'] = isset($form_data['company_name'])?$form_data['company_name']:'-';
      $form_data['brand_name'] = isset($form_data['brand_name'])?$form_data['brand_name']:'-';
      $form_data['quote_quantity'] = isset($form_data['quote_quantity'])?$form_data['quote_quantity']:'-';
      $form_data['expected_delivery_days'] = isset($form_data['expected_delivery_days'])?$form_data['expected_delivery_days']:'-';



      /* send mail to vendor */

      $credentials = ['email' => $form_data['vendor_email']];
      
      $arr_user = get_user_by_credentials($credentials);

      /*Get site setting data from helper*/
      $arr_site_setting = get_site_settings(['site_name','website_url']);


      if($admin_details)
      {
             
        $arr_built_content = [
          'VENDOR_EMAIL'                => $form_data['vendor_email'],          
          'SITE_URL'                    => $arr_site_setting['website_url'],
          'ADMIN_NAME'                  => $admin_name,
          'ADMIN_EMAIL'                 => $admin_email,
          'VENDOR_NAME'                 => $form_data['vendor_name'],
          'COMPANY_NAME'                => $form_data['company_name'],
          'PRODUCT_NAME'                => $form_data['product_name'],
          'BRAND_NAME'                  => $form_data['brand_name'],
          'QUOTE_QUANTITY'              => $form_data['quote_quantity'],
          'EXPECTED_DELIVERY_DAYS'      => $form_data['expected_delivery_days'],
          'PROJECT_NAME'                => $arr_site_setting['site_name'],
          'EMAIL_BODY'                  => $form_data['email_body']
        ];

        // $arr_vendor_mail_data['email_template_id'] = '71';
        //$arr_vendor_mail_data['arr_built_content'] = $arr_built_content_1;

        $arr_mail_data                      = [];
        $arr_mail_data['email_template_id'] = '71';
        $arr_mail_data['arr_built_content'] = $arr_built_content;
        $arr_mail_data['arr_user']          = $arr_user;

        $email_status  = $this->EmailService->send_mail($arr_mail_data);


        // Update quote status
        $this->GetQuoteModel->where('id',$form_data['quote_id'])->update(['status' => 1]);

        /*    try
        {
          
          $is_mail_sent = $this->EmailService->send_get_a_quote_mail_to_vendor($arr_vendor_mail_data);

        } catch (\Exception $e) {
          $response['status']   = 'ERROR';
          $response['msg']      = $e->getMessage();

          return response()->json($response);
        }*/

        $response['status']      = 'SUCCESS';
        $response['description'] = 'Quote request email has been sent to '.$form_data['company_name'].' vendor.'; 
          
        return response()->json($response);

      }
      else
      {
          $response['status']      = 'ERROR';
          $response['description'] = 'Error occurred while submit get a quote.';             
             
          return response()->json($response);
      }
    
  }

  // view quote details
  public function view_quote_request_details($quote_id)
  {
    //dd("ok");
    $quote_id = base64_decode($quote_id);

    // get quote details
    $quote_details_obj = $this->GetQuoteModel->where('id',$quote_id)
                                           ->first();
    $quote_details = [];
    if(isset($quote_details_obj))
    {
      $quote_details = $quote_details_obj->toArray();
    }
    // Get product details
    $product_details = $this->ProductsModel->where('id',$quote_details['product_id'])
                                        ->with(['brand_details'=>function($q){
                                          $q->select('id','brand_name');
                                        }])
                                        ->select('id','brand','product_name','product_image','retail_price')
                                        ->first();                                       
    
    
    


    if(isset($product_details))
    {
      $product_details = $product_details->toArray();
    } 
  
    $this->arr_view_data['quote_details']   = $quote_details;
    $this->arr_view_data['product_details'] = $product_details;
    $this->arr_view_data['module_title']    = $this->module_title;
    $this->arr_view_data['page_title']      = 'Quote Details';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['curr_panel_slug'] = $this->admin_panel_slug;                                       

    return view($this->module_view_folder.'.view', $this->arr_view_data);
  }
}
