<?php
namespace App\Http\Controllers\Maker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\CommissionReportService;
use App\Common\Services\CommissionService;
use App\Models\StripeTransactionModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\RetailerQuotesModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeModel;
use App\Models\SalesManagerModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\CustomerModel;
use App\Models\SiteSettingModel;
use App\Models\CustomerQuotesProductModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use DB;
use Datatables;
use Excel, Validator;
class CommissionController extends Controller
{
    public function __construct(StripeTransactionModel $StripeTransactionModel, CommissionReportService $CommissionReportService, RetailerQuotesModel $RetailerQuotesModel, RetailerModel $RetailerModel, RoleUsersModel $RoleUsersModel, RepresentativeLeadsModel $RepresentativeLeadsModel, UserModel $UserModel, RepresentativeModel $RepresentativeModel, SalesManagerModel $SalesManagerModel, MakerModel $MakerModel,RetailerQuotesProductModel $RetailerQuotesProductModel,CustomerQuotesProductModel $CustomerQuotesProductModel,
        CustomerModel $CustomerModel,SiteSettingModel $SiteSettingModel,
        CommissionService $CommissionService
    )
    {
        $this->arr_view_data = [];
        $this->module_title = "Commissions";
        $this->module_view_folder = "maker.commission_reports";
        $this->module_url_path = url(config('app.project.maker_panel_slug'));
        $this->curr_panel_slug = config('app.project.maker_panel_slug');
        $this->StripeTransactionModel = $StripeTransactionModel;
        $this->MakerModel = $MakerModel;
        $this->RetailerModel = $RetailerModel;
        $this->CommissionReportService = $CommissionReportService;
        $this->CommissionService              = $CommissionService;
        $this->RetailerQuotesModel = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
        $this->RoleUsersModel = $RoleUsersModel;
        $this->RepresentativeModel = $RepresentativeModel;
        $this->SalesManagerModel = $SalesManagerModel;
        $this->UserModel = $UserModel;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->CustomerModel              = $CustomerModel;  
        $this->SiteSettingModel           = $SiteSettingModel;

        $this->site_setting_obj  = $this->SiteSettingModel->first();
       
        if(isset($this->site_setting_obj))
        {
          $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
        

    }
    public function index(Request $request)
    {   
        
        $this->arr_view_data['arr_data'] = array();
        $this->arr_view_data['module_url_path'] = url(config('app.project.maker_panel_slug') . "/commissions");
        $this->arr_view_data['exprot_csv_url'] = url(config('app.project.maker_panel_slug') . "/commissions") . "/commission_report_generator";
        $this->arr_view_data['generate_invoice_url'] = url(config('app.project.maker_panel_slug') . "/commissions") . "/commission_invoice_generator";
        $this->arr_view_data['page_title'] = $this->module_title;
        $this->arr_view_data['module_title'] = "Commissions";
        $this->arr_view_data['arr_data'] = isset($arr_data) ? $arr_data : [];
        return view($this->module_view_folder . '.commission', $this->arr_view_data);
    }

    public function get_user($role=false)
    {

        $userData = [];
        if ($role=='maker') {

          $objUserData = $this->MakerModel
          ->with('user_details')
          ->whereHas('user_details',function ($query)
          {
              $query->where('status',1);
          })
          ->orderBy('company_name','ASC')
          ->get();

          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            
                $userData = $usersDetailes;
            
          }
        }

        elseif ($role=='representative') {

          $objUserData = $this->RepresentativeModel
                        ->with('get_user_details')
                        ->whereHas('get_user_details',function ($query)
                          {
                              $query->where('status',1);
                          })
                        ->withCount(['get_user_details as username' => function($query) {
                            $query->select(DB::raw('first_name'));
                        }])
                       ->orderBy('username','ASC')
                        ->get();

          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            foreach ($usersDetailes as $id => $maker) {

                $userData[] = $maker['get_user_details'];
            }
            
          }

        }

        elseif ($role=='sales_manager') {

          $objUserData = $this->SalesManagerModel
                                    ->with('get_user_data')
                                    ->whereHas('get_user_data',function ($query)
                                      {
                                          $query->where('status',1);
                                      })
                                    ->withCount(['get_user_data as username' => function($query) {
                                        $query->select(DB::raw('first_name'));
                                    }])
                                   ->orderBy('username','ASC')
                                    ->get();
          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            foreach ($usersDetailes as $id => $maker) {

                $userData[] = $maker['get_user_data'];
            }
            
          }

        }

        elseif ($role=='retailer') {

          $objUserData = $this->RetailerModel
                                ->with('user_details')
                                ->whereHas('user_details',function ($query)
                                  {
                                      $query->where('status',1);
                                  })
                                ->withCount(['user_details as username' => function($query) {
                                        $query->select(DB::raw('first_name'));
                                    }])
                                ->orderBy('username','ASC')
                                ->get();

          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            foreach ($usersDetailes as $id => $maker) {

                $userData[] = $maker['user_details'];
            }
          }

        }

        elseif ($role=='customer') {

          $objUserData = $this->CustomerModel
                            ->with('user_details')
                            ->whereHas('user_details',function ($query)
                                  {
                                      $query->where('status',1);
                                  })
                            ->withCount(['user_details as username' => function($query) {
                                        $query->select(DB::raw('first_name'));
                                    }])
                             ->orderBy('username','ASC')
                            ->get();

          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            foreach ($usersDetailes as $id => $maker) {

                $userData[] = $maker['user_details'];
            }
          }

        }

        return $userData;
    }

    public function get_commissions(Request $request)
    {
        $type = 0;
        $stripe_transaction_tbl = $this->StripeTransactionModel->getTable();
        $RetailerQuotesTable = $this->RetailerQuotesModel->getTable();
        $RoleUserTable = $this->RoleUsersModel->getTable();
        $arr_search_column = $request->input('column_filter');
        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        $objTransactionData = $this->CommissionReportService->vendor_order_query($arr_search_column, $type, $loggedInUserId);
        $current_context = $this;
        $objTransactionData = $objTransactionData->get();
        $json_result = Datatables::of($objTransactionData);
        $arrTransactionData = collect($objTransactionData)->toArray();
        //dd($arrTransactionData);
        $arrLeadId = array_column($arrTransactionData, 'lead_id');
        $arrLeadMapById = $this->allQuoteDetails($arrLeadId);
        $adminCommission = $this->CommissionService->get_admin_commission();
        $vendor_payable_amount = [];
        $admin_commission_amt = [];
        $total_commission_pending = [];
        //calculations of total transactions
        /*foreach ($arrTransactionData as $tempArrTransactionData)
        {
            if ($tempArrTransactionData->status != 1)
            {
                $totalPrice = isset($tempArrTransactionData->total_wholesale_price) ? num_format($tempArrTransactionData->total_wholesale_price) : 0.00;
                $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
                $amount = $totalPrice - $adminCommissionAmount;
                array_push($admin_commission_amt, $adminCommissionAmount);
                array_push($vendor_payable_amount, $amount);
            }
            if ($tempArrTransactionData->status == null)
            {
                $totalPrice = isset($tempArrTransactionData->total_wholesale_price) ? num_format($tempArrTransactionData->total_wholesale_price) : 0.00;
                $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
                $amount = $totalPrice - $adminCommissionAmount;
                array_push($total_commission_pending, $amount);
            }
        }*/


        foreach ($arrTransactionData as $tempArrTransactionData)
        {
           
            if($tempArrTransactionData->status!=1)
             { 
                $totalPrice       = isset($tempArrTransactionData->total_wholesale_price)?num_format($tempArrTransactionData->total_wholesale_price):0.00;

                $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                

                $amount = $totalPrice - $adminCommissionAmount;
                $shipping_charge = 0;
                if($tempArrTransactionData->role_id==4)
                {
                    $shipping_charge = $this->get_retailer_order_shipping_charges($tempArrTransactionData->order_id);
                }
                if($tempArrTransactionData->role_id==6)
                {
                    $shipping_charge = $this->get_retailer_order_shipping_charges($tempArrTransactionData->order_id);
                }
                $amount = $amount-$shipping_charge;

                array_push($admin_commission_amt,$adminCommissionAmount);
                
                array_push($vendor_payable_amount,$amount);

             }

             if($tempArrTransactionData->status==null)
             {
                $totalPrice       = isset($tempArrTransactionData->total_wholesale_price)?num_format($tempArrTransactionData->total_wholesale_price):0.00;

                $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                
                $amount = $totalPrice - $adminCommissionAmount;
                array_push($total_commission_pending,$amount);
             }
        }


        $sum_vendor_payable_amount = array_sum($vendor_payable_amount);
        $sum_admin_commission_amt = array_sum($admin_commission_amt);
        $sum_total_commission_pending = array_sum($total_commission_pending);
        /* Modifying Columns */
        $json_result = $json_result->editColumn('lead_id', function ($data) use ($current_context, $arrLeadMapById)
        {
            if ($data->order_id == 'null')
            {
                return 'N/A';
            }
            if ($data->role_id == 4)
            {
                $href = url('/') . '/vendor/retailer_orders/view/' . base64_encode($data->order_id);
            }
            if ($data->role_id == 6)
            {
                $href = url('/') . '/vendor/customer_orders/view/' . base64_encode($data->order_id);
            }
            $build_view_action = '<a href="' . $href . '" title="View">' . $data->order_no . '</a>';
            return $build_view_action;
        })->editColumn('received_by', function ($data) use ($current_context)
        {
            return $received_by = isset($data->vendor_name) && $data->vendor_name != '' ? $data->vendor_name : 'N/A';
        })->editColumn('ordered_by', function ($data) use ($current_context)
        {
            $ordered_by = "N/A";
            if ($data->role_id == 4)
            {
                $ordered_by = "Retailer";
            }
            if ($data->role_id == 6)
            {
                $ordered_by = "Customer";
            }
            return $ordered_by;
        })->editColumn('order_amount', function ($data) use ($current_context, $arrLeadMapById)
        {
            $order_amount = isset($data->total_wholesale_price) ? num_format($data->total_wholesale_price) : 'N/A';
            return $order_amount;
        })

    ->editColumn('amount', function ($data) use ($current_context, $adminCommission)
        {
            if (!isset($data->amount))
            {
                $totalPrice = isset($data->total_wholesale_price) ? num_format($data->total_wholesale_price) : 0.00;
                $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
                $adminCommissionAmount = isset($adminCommissionAmount) ? $adminCommissionAmount : 0.00;
                $amount = $totalPrice - $adminCommissionAmount;
                return num_format($amount);
            }
            return $amount = isset($data->amount) && $data->amount != '' ? num_format($data->amount) : 'N/A';
        })


         ->editColumn('shipping_charge',function($data) use ($current_context,$arrLeadMapById)
                          { 
                            $shipping_charge=0;
                            if($data->role_id==4)
                            {
                            $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);
                            
                                
                            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                                
                              
                            }
                            
                            if($data->role_id==6)
                            {
                            $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);

                               
                            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                               
                            }

                           
                            $total_amount = $totalPrice - $shipping_charge;

                            return  num_format($total_amount);  
                          })



                         ->editColumn('amount',function($data) use ($current_context,$adminCommission)
                         {
                            
                            $shipping_charge=0;
                            if($data->role_id==4)
                            {
                            $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);
                            }
                            if($data->role_id==6)
                            {
                            $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);
                            }

                          
                                if($data->role_id=4)
                                {
                                $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                                }
                                if($data->role_id==6)
                                {
                                $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;
                                }
                               

                                $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                                $adminCommissionAmount = isset($adminCommissionAmount)?$adminCommissionAmount:0.00;

                                $amount =  $totalPrice - $adminCommissionAmount;
                               
                                $amount =  $amount - $shipping_charge;

                                return num_format($amount);
                                
                           

                         })

    ->editColumn('commission_amount', function ($data) use ($current_context, $arrLeadMapById, $adminCommission)
        {
            $order_data = isset($arrLeadMapById[$data->lead_id]) ? $arrLeadMapById[$data->lead_id] : [];
            $totalPrice = isset($data->total_wholesale_price) ? num_format($data->total_wholesale_price) : 0.00;

            $shipping_charge=0;
            if($data->role_id==4)
            {
                $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);
            }
            if($data->role_id==6)
            {
                $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);
            }

            $totalPrice = $totalPrice - $shipping_charge;

            $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
            return num_format($adminCommissionAmount);
       

        })->editColumn('status', function ($data) use ($current_context)
        {
            if ($data->status == null)
            {
                $status = 1;
                return $status;
            }
            return $status = isset($data->status) && $data->status != '' ? $data->status : 'N/A';
        })->editColumn('created_at', function ($data) use ($current_context)
        {
            return us_date_format($data->order_date);
        })->editColumn('sum_vendor_payable_amount', function ($data) use ($current_context, $sum_vendor_payable_amount)
        {
            return $sum_vendor_payable_amount;
        })->editColumn('sum_admin_commission_amt', function ($data) use ($current_context, $sum_admin_commission_amt)
        {
            return $sum_admin_commission_amt;
        })->editColumn('sum_total_commission_pending', function ($data) use ($current_context, $sum_total_commission_pending)
        {
            return $sum_total_commission_pending;
        })->make(true);
        $build_result = $json_result->getData();
        return response()->json($build_result);
    }
    public function quotesDetails($id, $orderType = false)
    {
        $arrResponse = [];
        $adminCommission = $this->CommissionService->get_admin_commission();
        $objOrderData = $this->RetailerQuotesModel->with(['user_details', 'maker_data', 'stripe_transaction_detail'])->where('id', $id)->first();
        if ($objOrderData)
        {
            $orderData = $objOrderData->toArray();
            $companyName = isset($orderData['maker_data']['company_name']) ? $orderData['maker_data']['company_name'] : 'N/A';
            $retailer_first_name = isset($orderData['user_details']['first_name']) ? $orderData['user_details']['first_name'] : 'N/A';
            $retailer_last_name = isset($orderData['user_details']['last_name']) ? $orderData['user_details']['last_name'] : '';
            $adminCommissionAmount = $orderData['total_wholesale_price'] * ((float)$adminCommission / 100);
            $vendorAmount = $orderData['total_wholesale_price'] - $adminCommissionAmount;
            $arrResponse['order_id'] = $orderData['id'];
            $arrResponse['order_no'] = $orderData['order_no'];
            $arrResponse['commission_amount'] = $vendorAmount;
            $arrResponse['order_amount'] = $orderData['total_wholesale_price'];
            $arrResponse['vendor_name'] = $companyName;
            $arrResponse['order_date'] = isset($orderData['created_at']) ? $orderData['created_at'] : 'N/A';
            $arrResponse['vendor_id'] = isset($orderData['maker_id']) ? $orderData['maker_id'] : '0';
            $arrResponse['retailer_name'] = $retailer_first_name . ' ' . $retailer_last_name;
            $arrResponse['status'] = isset($orderData['stripe_transaction_detail']['status']) ? $orderData['stripe_transaction_detail']['status'] : '';
            $arrResponse['transfer_id'] = isset($orderData['stripe_transaction_detail']['transfer_id']) ? $orderData['stripe_transaction_detail']['transfer_id'] : 'N/A';
        }
        return $arrResponse;
    }
    public function allQuoteDetails($arrId = [])
    {
        $arrResponse = [];
        $adminCommission = $this->CommissionService->get_admin_commission();
        $arrOrderData = $this->RetailerQuotesModel->with(['user_details', 'maker_data', 'stripe_transaction_detail'])->whereIn('id', $arrId)->get()->toArray();
        $arrBuiltDataById = [];
        if (sizeof($arrOrderData) > 0)
        {
            foreach ($arrOrderData as $orderData)
            {
                $companyName = isset($orderData['maker_data']['company_name']) ? $orderData['maker_data']['company_name'] : 'N/A';
                $retailer_first_name = isset($orderData['user_details']['first_name']) ? $orderData['user_details']['first_name'] : 'N/A';
                $retailer_last_name = isset($orderData['user_details']['last_name']) ? $orderData['user_details']['last_name'] : '';
                $adminCommissionAmount = $orderData['total_wholesale_price'] * ((float)$adminCommission / 100);
                $vendorAmount = $orderData['total_wholesale_price'] - $adminCommissionAmount;
                $arrBuiltDataById[$orderData['id']]['order_id'] = $orderData['id'];
                $arrBuiltDataById[$orderData['id']]['order_no'] = $orderData['order_no'];
                $arrBuiltDataById[$orderData['id']]['commission_amount'] = $vendorAmount;
                $arrBuiltDataById[$orderData['id']]['order_amount'] = $orderData['total_wholesale_price'];
                $arrBuiltDataById[$orderData['id']]['vendor_name'] = $companyName;
                $arrBuiltDataById[$orderData['id']]['retailer_name'] = $retailer_first_name . ' ' . $retailer_last_name;
                $arrBuiltDataById[$orderData['id']]['status'] = isset($orderData['stripe_transaction_detail']['status']) ? $orderData['stripe_transaction_detail']['status'] : '';
                $arrBuiltDataById[$orderData['id']]['transfer_id'] = isset($orderData['stripe_transaction_detail']['transfer_id']) ? $orderData['stripe_transaction_detail']['transfer_id'] : 'N/A';
            }
        }
        return $arrBuiltDataById;
    }
    /*public function commission_report_generator(Request $request)
    {
        $type = 'csv';
        $q_order_no = $request->get('q_order_no');
        $q_from_date = $request->get('from_date');
        $q_to_date = $request->get('to_date');
        $q_status = $request->get('vendor_payment_status');
        $q_ordered_by = $request->get('ordered_by');
        
        $arr_search_column['q_order_no'] = isset($order_no) ? $order_no : null;
        $arr_search_column['q_from_date'] = isset($from_date) ? $from_date : null;
        $arr_search_column['q_to_date'] = isset($to_date) ? $to_date : null;
        $arr_search_column['q_status'] = isset($q_status) ? $q_status : null;
        $arr_search_column['q_ordered_by'] = isset($q_ordered_by) ? $q_ordered_by : null;
        
        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        $objTransactionData = $this->CommissionReportService->vendor_order_query($arr_search_column, $type, $loggedInUserId);
        $stripe_transaction_tbl = $this->StripeTransactionModel->getTable();
        $RetailerQuotesTable = $this->RetailerQuotesModel->getTable();
        
        if (isset($objTransactionData))
        {
            $objTransactionData = $objTransactionData->get()->toArray();
        }
        // $objprefixStripeTransaction = $this->get_order_data($request);
        $objprefixStripeTransaction = $this->CommissionReportService->all_rep_order_query();
        $objprefixStripeTransaction = $objprefixStripeTransaction->get()->toArray();
        $data = $arrayResponseData = [];
        $adminCommission = $this->CommissionService->get_admin_commission();
        $i = 1;
        foreach ($objTransactionData as $key => $value)
        {
            if ($value->status == null)
            {
                $status = 'Pending';
            }
            else if ($value->status == '2')
            {
                $status = 'Paid';
            }
            else
            {
                $status = 'Failed';
            }
            if ($value->role_id == 4)
            {
                $role = "Retailer";
                $shipping_charge = $this->get_retailer_order_shipping_charges($value->order_id);
            }
            if ($value->role_id == 6)
            {
                $role = "Customer";
                $shipping_charge = $this->get_retailer_order_shipping_charges($value->order_id);
            }
            
                
                $totalPrice = isset($value->total_wholesale_price) ? num_format($value->total_wholesale_price) : 0.00;
                $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
                $adminCommissionAmount = isset($adminCommissionAmount) ? $adminCommissionAmount : 0.00;
                $totalPrice = $totalPrice - $shipping_charge;
                $amount = $totalPrice - $adminCommissionAmount;
                $amount = num_format($amount);
            
            


            $totalPrice = isset($value->total_wholesale_price) ? num_format($value->total_wholesale_price) : 0.00;

            $totalPrice = $totalPrice - $shipping_charge;
            $adminCommissionAmount = $totalPrice * ($adminCommission / 100);
            $adminCommissionAmount = num_format($adminCommissionAmount);
            $array = (array)$value;
            $arrayResponseData['Sr No'] = $i;
            $arrayResponseData['Order Date'] = us_date_format($value->order_date);
            $arrayResponseData['Order No'] = $value->order_no;
            $arrayResponseData['Ordered By'] = $role;
            $arrayResponseData['Total Order Amount'] = '$' . num_format($value->total_wholesale_price);
            $arrayResponseData['Total Order Amount Excluding Shipping Charge'] = '$'.num_format($totalPrice); 
            $arrayResponseData['Amount Received'] = '$' . $amount;
            $arrayResponseData['Admin Commission Amount'] = '$' . $adminCommissionAmount;
            $arrayResponseData['Status'] = $status;
            array_push($data, $arrayResponseData);
            $i++;
        }
        return Excel::create('Commission Report', function ($excel) use ($data)
        {
            $excel->sheet('Commission Reports', function ($sheet) use ($data)
            {
                $sheet->fromArray($data);
                $sheet->freezeFirstRow();
                $sheet->cells("A1:M1", function ($cells)
                {
                    $cells->setFont(array(
                        'bold' => true
                    ));
                });
            });
        })->download($type);
    }*/

    public function commission_report_generator(Request $request)
{   
    
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];


    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['repId']          = isset($formData['representative'])?$formData['representative']:null;
    $filterData['salesId']          = isset($formData['sales_manager'])?$formData['sales_manager']:null;
    
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    $filterData['commissionStatus'] = isset($formData['order_status'])?$formData['order_status']:null;
    $filterData['vendorId']          = isset($formData['loggedInUserId'])?$formData['loggedInUserId']:0;
    $filterData['repPaymentStatus'] = isset($formData['rep_payment_status'])?$formData['rep_payment_status']:null;    
    $filterData['is_direct_payment'] = 1;


        
    $objprefixStripeTransaction = $this->CommissionReportService->retailer_order_query($filterData);
  

    $objprefixStripeTransaction = $objprefixStripeTransaction->get();

    if(isset($objprefixStripeTransaction))
    {
        $objprefixStripeTransaction = $objprefixStripeTransaction->toArray();
    }
    $data =  $arrayResponseData = [];

    foreach($objprefixStripeTransaction as $key => $value)
    { 
               
    
        /*-------------------------------------------------------*/
          $admin_commission_status = 'Pending';
          if($value->admin_commission_status == 0 || $value->admin_commission_status==null)
          {

            $admin_commission_status =  'Pending';
          }
          //---------------For retailer orders-----------
          if($value->admin_commission_status == 1)
          {

            $admin_commission_status = 'Paid';
          }
        /*-------------------------------------------------------*/
        

        /*-------------------------------------------------------*/
          //return status when order from rep or sales
          if($value->representative_id!=null || $value->sales_manager_id!=null)
          {
            //filter for rep/sales commission is pending
            if($value->rep_commission_status == 0 || $value->sales_manager_commission_status!=1)
            {
                $rep_sales_commission_status = 'Pending';
            }

            //filter for rep/sales commission is paid
            if($value->rep_commission_status == 1 || $value->sales_manager_commission_status==1)
            {
                $rep_sales_commission_status = 'Paid';
            }
          }
          else
          {
            //return status when order is not from sales and rep
            if($value->status == '-')
            {
                $rep_sales_commission_status = '-';
            }
            else
            {
                $rep_sales_commission_status = 'Pending';
            }
          }
        /*-------------------------------------------------------*/
        
        /*-------------------------------------------------------*/  
          if($value->transaction_status == 2)
          {
            
            $retailer_payment_status =  'Paid';
          }
          if($value->transaction_status == 3)
          {
            
            $retailer_payment_status = 'Failed';
          }
          if($value->transaction_status == 1 || $value->transaction_status ==null)
          {

            $retailer_payment_status = 'Pending';
          }          
        /*-------------------------------------------------------*/

        $shipping_charge = $this->get_retailer_order_shipping_charges($value->order_id);

        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($value->order_id);
        }

        $adminCommission  = isset($value->maker_admin_commission)?num_format($value->maker_admin_commission):0.00;
        
        if($adminCommission==0)
        {
            $adminCommission  = isset($value->admin_commission)?num_format($value->admin_commission):0.00;
        }

        $totalPrice       = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):0.00;
                        
        $shipping_charge = $shipping_charge;

        $totalPrice = $totalPrice - $shipping_charge;

        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):'-';
        $amount = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):'-';

        $amount_excluding_shipping_charge = $amount - $shipping_charge;

        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            if(isset($value->representative_id))
            {
                $representative_commission = $this->CommissionService->get_representative_commission($value->representative_id);
            }
            if(isset($value->sales_manager_id))
            {
                $representative_commission = $this->CommissionService->get_sales_manager_commission($value->sales_manager_id);
            } 
 
         $rep_commission_amount = ($adminCommissionAmount * $representative_commission)/100 ;

         //$rep_commission_amount = $adminCommissionAmount;
         $rep_commission_amount = $rep_commission_amount;

        }

        $amount_excluding_shipping_charge = isset($amount_excluding_shipping_charge)?num_format($amount_excluding_shipping_charge):0.00;
        
        $admin_commission = isset($adminCommissionAmount)?num_format($adminCommissionAmount):'0.00';
    
        $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):'-';

        $array = (array)$value;

        $arrayResponseData['order_date']       = us_date_format($value->order_date);
        $arrayResponseData['order no']         = $value->order_no;
        $arrayResponseData['Retailer']         = $value->orderer_name;
        $arrayResponseData['vendor']           = $value->vendor_name;
          
        $arrayResponseData['Order amount']     = $amount;
        $arrayResponseData['Shipping Charges'] = $shipping_charge;
        $arrayResponseData['Order Amount
          (Excluding Shipping Charge)'] = $amount_excluding_shipping_charge;
        $arrayResponseData['Retailer Payment Status']      = $retailer_payment_status;
        $arrayResponseData['Admin Commission']   = $admin_commission;
        $arrayResponseData['Admin Commission Status'] = $admin_commission_status;
        
        // $arrayResponseData['Rep/Sales Commission
        //         Amount'] = $rep_commission_amount;
        // $arrayResponseData['Rep/Sales Commission'] = $rep_sales_commission_status;
        
        array_push($data,$arrayResponseData); 

    }

    return Excel::create('Commission Report', function($excel) use ($data) {
        
      $excel->sheet('Commission Reports', function($sheet) use ($data)
      {
        $sheet->fromArray($data);
        $sheet->freezeFirstRow();  
        $sheet->cells("A1:M1", function($cells) {            
          $cells->setFont(array(              
            'bold'       =>  true
          ));

        });
      });

    })->download($type);
}

public function intermediate_commission_report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];

    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['repId']          = isset($formData['representative'])?$formData['representative']:null;
    $filterData['salesId']          = isset($formData['sales_manager'])?$formData['sales_manager']:null;
    
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    $filterData['commissionStatus'] = isset($formData['order_status'])?$formData['order_status']:null;
    $filterData['repPaymentStatus'] = isset($formData['rep_payment_status'])?$formData['rep_payment_status']:null;
    $filterData['vendorId']          = isset($formData['loggedInUserId'])?$formData['loggedInUserId']:0;
    $filterData['repPaymentStatus'] = isset($formData['rep_payment_status'])?$formData['rep_payment_status']:null; 
    $filterData['is_direct_payment'] = 0;


        
    $objprefixStripeTransaction = $this->CommissionReportService->retailer_indirect_order_query($filterData);
  

    $objprefixStripeTransaction = $objprefixStripeTransaction->get();

    if(isset($objprefixStripeTransaction))
    {
        $objprefixStripeTransaction = $objprefixStripeTransaction->toArray();
    }
    $data =  $arrayResponseData = [];

    foreach($objprefixStripeTransaction as $key => $value)
    { 
               
    
        /*-------------------------------------------------------*/
          $admin_commission_status = 'Pending';
          if($value->admin_commission_status == 0 || $value->admin_commission_status==null)
          {

            $admin_commission_status =  'Pending';
          }
          //---------------For retailer orders-----------
          if($value->admin_commission_status == 1)
          {

            $admin_commission_status = 'Paid';
          }
        /*-------------------------------------------------------*/
        

        /*-------------------------------------------------------*/
          //return status when order from rep or sales
          if($value->representative_id!=null || $value->sales_manager_id!=null)
          {
            //filter for rep/sales commission is pending
            if($value->rep_commission_status == 0 || $value->sales_manager_commission_status!=1)
            {
                $rep_sales_commission_status = 'Pending';
            }

            //filter for rep/sales commission is paid
            if($value->rep_commission_status == 1 || $value->sales_manager_commission_status==1)
            {
                $rep_sales_commission_status = 'Paid';
            }
          }
          else
          {
            //return status when order is not from sales and rep
            if($value->status == '-')
            {
                $rep_sales_commission_status = '-';
            }
            else
            {
                $rep_sales_commission_status = 'Pending';
            }
          }
        /*-------------------------------------------------------*/
        
        /*-------------------------------------------------------*/  
          if($value->transaction_status == 2)
          {
            
            $retailer_payment_status =  'Paid';
          }
          if($value->transaction_status == 3)
          {
            
            $retailer_payment_status = 'Failed';
          }
          if($value->transaction_status == 1 || $value->transaction_status ==null)
          {

            $retailer_payment_status = 'Pending';
          }          
        /*-------------------------------------------------------*/

        $shipping_charge = $this->get_retailer_order_shipping_charges($value->order_id);

        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($value->order_id);
        }

        $adminCommission  = isset($value->maker_admin_commission)?num_format($value->maker_admin_commission):0.00;
        
        if($adminCommission==0)
        {
            $adminCommission  = isset($value->admin_commission)?num_format($value->admin_commission):0.00;
        }

        $totalPrice       = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):0.00;
                        
        $shipping_charge = $shipping_charge;

        $totalPrice = $totalPrice - $shipping_charge;

        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):'-';
        $amount = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):'-';

        $amount_excluding_shipping_charge = $amount - $shipping_charge;

        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            if(isset($value->representative_id))
            {
                $representative_commission = $this->CommissionService->get_representative_commission($value->representative_id);
            }
            if(isset($value->sales_manager_id))
            {
                $representative_commission = $this->CommissionService->get_sales_manager_commission($value->sales_manager_id);
            } 
 
         $rep_commission_amount = ($adminCommissionAmount * $representative_commission)/100 ;

         //$rep_commission_amount = $adminCommissionAmount;
         $rep_commission_amount = $rep_commission_amount;

        }

        $amount_excluding_shipping_charge = isset($amount_excluding_shipping_charge)?num_format($amount_excluding_shipping_charge):0.00;
        
        $admin_commission = isset($adminCommissionAmount)?num_format($adminCommissionAmount):'0.00';
    
        $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):'-';

        $array = (array)$value;

        $arrayResponseData['order_date']       = us_date_format($value->order_date);
        $arrayResponseData['order no']         = $value->order_no;
        $arrayResponseData['Retailer']         = $value->orderer_name;
        $arrayResponseData['vendor']           = $value->vendor_name;
          
        $arrayResponseData['Order amount']     = $amount;
        $arrayResponseData['Shipping Charges'] = $shipping_charge;
        $arrayResponseData['Order Amount
          (Excluding Shipping Charge)'] = $amount_excluding_shipping_charge;
        $arrayResponseData['Retailer Payment Status']      = $retailer_payment_status;
        $arrayResponseData['Admin Commission']   = $admin_commission;
        $arrayResponseData['Admin Commission Status'] = $admin_commission_status;
        
        // $arrayResponseData['Rep/Sales Commission
        //         Amount'] = $rep_commission_amount;
        // $arrayResponseData['Rep/Sales Commission'] = $rep_sales_commission_status;
        
        
        $filterData['vendorId']          = isset($formData['loggedInUserId'])?$formData['loggedInUserId']:0;
        $filterData['repPaymentStatus'] = false;
        $filterData['is_direct_payment'] = 0;
        array_push($data,$arrayResponseData); 

    }

    return Excel::create('Commission Report', function($excel) use ($data) {
        
      $excel->sheet('Commission Reports', function($sheet) use ($data)
      {
        $sheet->fromArray($data);
        $sheet->freezeFirstRow();  
        $sheet->cells("A1:M1", function($cells) {            
          $cells->setFont(array(              
            'bold'       =>  true
          ));

        });
      });

    })->download($type);
}






    public function commission_invoice_generator($order_id = false)
    {
        if ($order_id != false)
        {
            $order_id = base64_decode($order_id);
            $quotes_details = $this->quotesDetails($order_id);
            $is_vendor = 1;
            $is_success = $this->CommissionReportService->commission_invoice_generator($quotes_details, $is_vendor);
            if ($is_success == "true")
            {
                $response['status'] = "success";
                $response['description'] = "Invoice sent successfully to vendor!";
            }
            else
            {
                $response['status'] = "failure";
                $response['description'] = "An Error has occurred while sending invoice,please try again";
            }
        }
        else
        {
            $response['status'] = "failure";
            $response['description'] = "An Error has occurred while sending invoice,please try again";
        }
        return $response;
    }

     public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0.00;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        // if($orderId == '43')
        // {
        //     dd("shipping details",$shipCharge,$shipChargeDisount);
        // }
        if($shipCharge!=0)
        {
        return $shippingCharges = $shipCharge-$shipChargeDisount;
        }
        return $shipCharge;


    }


    public function get_rep_sales_order_shipping_charges($orderId)
    {
        $shippingCharges = 0.00;

        $shipCharge = $this->RepresentativeLeadsModel->where('id',$orderId)->pluck('total_shipping_charges')->first();

        $shipChargeDisount = $this->RepresentativeLeadsModel->where('id',$orderId)->pluck('total_shipping_discount')->first();
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

    public function get_customer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

    //this function for commission for customer 
    public function view_customer_direct_payment_to_vendor($vendor_id)
    {
        
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrCustomer        = $this->get_user('customer');
        //dd($arrCustomer);

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
       
        $this->arr_view_data['page_title']          = "Direct Payment Reports";
        $this->arr_view_data['module_title']        = "Direct Payment Reports";
        $this->arr_view_data['module_url_path']     = $this->module_url_path.'/customer_commission_reports/customer_direct_payment_commission_reports';

        $this->arr_view_data['csv_url_path']     = $this->module_url_path.'/customer_commission_reports/';
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrCustomer']         = isset($arrCustomer) ? $arrCustomer: [];
        $this->arr_view_data['vendorId'] = $loggedInUserId;
     
        return view($this->module_view_folder.'.direct_payment_customer_report', $this->arr_view_data);
    }

     public function view_customer_payment_intermidiation_report($vendor_id)
    {
        //dd("it's working");
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrCustomer        = $this->get_user('customer');
        //dd($arrCustomer);

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }

        $arr_site_setting = get_site_settings(['site_name','website_url']);

        $this->arr_view_data['page_title']          = "Payment Intermediation (Through ".$arr_site_setting['site_name'].")";
        $this->arr_view_data['module_title']        = "Payment Intermediation (Through ".$arr_site_setting['site_name'].")";
        $this->arr_view_data['module_url_path']     = $this->module_url_path.'/customer_commission_reports/customer_indirect_payment_commission_reports';
        $this->arr_view_data['csv_url_path']     = $this->module_url_path.'/customer_commission_reports/customer_intermidiate_report_generator';
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrCustomer']         = isset($arrCustomer) ? $arrCustomer: [];
        $this->arr_view_data['vendorId'] = $loggedInUserId;
     
        return view($this->module_view_folder.'.payment_intermediation_customer_report', $this->arr_view_data);
    }
    

    public function view_direct_payment_to_vendor($vendor_id)
    {  
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrRepresentative  = $this->get_user('representative');
        $arrRetailer        = $this->get_retailer('retailer');
        $arrSalesManger     = $this->get_user('sales_manager');

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        $this->arr_view_data['page_title']          = $this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path.'/commissions/get_commission_reports';
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrRetailer']         = isset($arrRetailer) ? $arrRetailer: [];
        $this->arr_view_data['arrSalesManager']      = isset($arrSalesManger) ? $arrSalesManger: [];
        $this->arr_view_data['arrRepresentative']   = isset($arrRepresentative) ? $arrRepresentative: [];
        $this->arr_view_data['exprot_csv_url'] = url(config('app.project.maker_panel_slug') . "/commissions") . "/commission_report_generator";

        $this->arr_view_data['loggedInUserId'] = $loggedInUserId;

        //dd($this->arr_view_data['module_url_path']);
        return view($this->module_view_folder.'.direct_payment_report', $this->arr_view_data);
    }

     public function view_payment_intermidiation_report()
    {  
        //dd("ok");
        $this->arr_view_data['arr_data'] = array();

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        
        $arrMakers          = $this->get_user('maker');
        $arrRepresentative  = $this->get_user('representative');
        $arrRetailer        = $this->get_retailer('retailer');
        $arrSalesManger     = $this->get_user('sales_manager');
        
        $this->arr_view_data['page_title']          = $this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path.'/commissions/payment_intermediation_commission_reports';
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrRetailer']         = isset($arrRetailer) ? $arrRetailer: [];
        $this->arr_view_data['arrSalesManager']      = isset($arrSalesManger) ? $arrSalesManger: [];
        $this->arr_view_data['arrRepresentative']   = isset($arrRepresentative) ? $arrRepresentative: [];
        $this->arr_view_data['exprot_csv_url'] = url(config('app.project.maker_panel_slug') . "/commissions") . "/intermediate_commission_report_generator";
        $this->arr_view_data['loggedInUserId'] = $loggedInUserId;


        return view($this->module_view_folder.'.payment_intermidiation_report', $this->arr_view_data);
    }

    //this function for customer direct payment commission report
    public function customer_direct_payment_commission_reports(Request $request,$role=false)
    {
       
        $formData = [];

        $arr_search_column = $response = [];
        $arr_search_column = $request->input('column_filter');

        $formData['toDate']           = isset($arr_search_column['to_date'])?$arr_search_column['to_date']:false;
        $formData['fromDate']         = isset($arr_search_column['from_date'])?$arr_search_column['from_date']:false;
        $formData['commissionStatus'] = isset($arr_search_column['order_status'])?$arr_search_column['order_status']:false;
        $formData['makerId']          = isset($arr_search_column['vendor'])?$arr_search_column['vendor']:false;
        $formData['retailerId']       = isset($arr_search_column['retailer'])?$arr_search_column['retailer']:false;

      
        $formData['orderStatus']              = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        
        $formData['vendorPaymentStatus']      = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;

        $formData['is_direct_payment'] = 0;

         $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        $formData['vendorId'] = $loggedInUserId;
       

        $objprefixStripeTransaction = $this->get_direct_payment_customer_orders($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;

                     
    }


      public function customer_indirect_payment_commission_reports(Request $request,$role=false)
    {
       //dd("12ok");
        $formData = [];

        $arr_search_column = $response = [];
        $arr_search_column = $request->input('column_filter');

        $formData['toDate']           = isset($arr_search_column['to_date'])?$arr_search_column['to_date']:false;
        $formData['fromDate']         = isset($arr_search_column['from_date'])?$arr_search_column['from_date']:false;
        $formData['commissionStatus'] = isset($arr_search_column['order_status'])?$arr_search_column['order_status']:false;
        $formData['makerId']          = isset($arr_search_column['vendor'])?$arr_search_column['vendor']:false;
        $formData['retailerId']       = isset($arr_search_column['retailer'])?$arr_search_column['retailer']:false;

      
        $formData['orderStatus']              = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        
        $formData['vendorPaymentStatus']      = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;

        $formData['is_direct_payment'] = 0;

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        $formData['vendorId'] = $loggedInUserId;
       

        $objprefixStripeTransaction = $this->get_indirect_payment_customer_orders($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;

                     
    }
    public function get_commission_reports(Request $request,$role=false)
    {
        //dd(22);
        $formData = [];

        $arr_search_column = $response = [];
        $arr_search_column = $request->input('column_filter');

        $formData['toDate']           = isset($arr_search_column['to_date'])?$arr_search_column['to_date']:false;
        $formData['fromDate']         = isset($arr_search_column['from_date'])?$arr_search_column['from_date']:false;
        $formData['commissionStatus'] = isset($arr_search_column['order_status'])?$arr_search_column['order_status']:false;
        $formData['makerId']          = isset($arr_search_column['vendor'])?$arr_search_column['vendor']:false;
        $formData['retailerId']       = isset($arr_search_column['retailer'])?$arr_search_column['retailer']:false;

        $formData['repId']            = isset($arr_search_column['representative'])?$arr_search_column['representative']:false;
        
        $formData['salesId']          = isset($arr_search_column['sales_manager'])?$arr_search_column['sales_manager']:false;
        
        $formData['orderStatus']              = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        
        $formData['vendorPaymentStatus']      = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;

        $formData['repPaymentStatus']      = isset($arr_search_column['rep_payment_status'])?$arr_search_column['rep_payment_status']:false;

        $formData['is_direct_payment'] = 1;

        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        $formData['vendorId'] = $loggedInUserId;
       

        $objprefixStripeTransaction = $this->direct_payment_to_vendor($formData,$arr_search_column); 
      
        
            return  $objprefixStripeTransaction;    
    }

     public function payment_intermediation_commission_reports(Request $request,$role=false)
    {
      
        $formData = [];

        $arr_search_column = $response = [];
        $arr_search_column = $request->input('column_filter');

        $formData['toDate']           = isset($arr_search_column['to_date'])?$arr_search_column['to_date']:false;
        $formData['fromDate']         = isset($arr_search_column['from_date'])?$arr_search_column['from_date']:false;
        $formData['commissionStatus'] = isset($arr_search_column['order_status'])?$arr_search_column['order_status']:false;
        $formData['makerId']          = isset($arr_search_column['vendor'])?$arr_search_column['vendor']:false;
        $formData['retailerId']       = isset($arr_search_column['retailer'])?$arr_search_column['retailer']:false;

        $formData['repId']            = isset($arr_search_column['representative'])?$arr_search_column['representative']:false;
        
        $formData['salesId']          = isset($arr_search_column['sales_manager'])?$arr_search_column['sales_manager']:false;
        
        $formData['orderStatus']              = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        
        $formData['vendorPaymentStatus']      = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;

        $formData['repPaymentStatus']      = isset($arr_search_column['rep_payment_status'])?$arr_search_column['rep_payment_status']:false;

        

        $formData['is_direct_payment'] = 0;

        
        $loggedInUserId = 0;
        $user = \Sentinel::check();
        if ($user && $user->inRole('maker'))
        {
            $loggedInUserId = $user->id;
        }
        
        $formData['vendorId'] = $loggedInUserId;
       

        $objprefixStripeTransaction = $this->payment_intermediation($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;
    }
 
    /******/
    public function get_direct_payment_customer_orders($formData,$arr_search_column)
    {
        
        $objprefixStripeTransaction = $this->CommissionReportService->customer_order_query($formData,$arr_search_column);

        $total_commission_amount = $this->CommissionReportService->direct_payment_customer_total_commission($formData,$arr_search_column);

        //dd($objprefixStripeTransaction->get()->toArray());
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $json_result  = $json_result->editColumn('amount',function($data) use ($current_context)
                    {   
                      if (isset($data->total_retail_price)) {

                        $amount =  isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;
                      }
                     
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      
                        return num_format($amount);
                    })

                     ->editColumn('total_retail_price',function($data) use ($current_context)
                    {
                        return num_format($data->total_retail_price);
                    })

                     ->editColumn('order_no',function($data) use ($current_context)
                    {
                        return $data->order_no;
                    })


                     ->editColumn('created_at',function($data) use ($current_context)
                    {
                        return us_date_format($data->order_date);
                    })

                      ->editColumn('shipping_charge',function($data) use ($current_context)
                    {
                        return num_format($this->get_customer_order_shipping_charges($data->order_id));
                    })


                     ->editColumn('amount_excluding_shipping_charge',function($data) use ($current_context)
                    {

                       $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  
                     
                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                    
                        return num_format($amount_excluding_shipping_charge);
                    })

                    ->editColumn('admin_commission_status',function($data) use ($current_context)
                    {
                        if($data->admin_commission_status !=1)
                        {
                          return "Pending";  
                        }
                        else
                        {
                          return "Paid";     
                        }
                    })

                    ->editColumn('vendor_payment_status',function($data) use ($current_context)
                    {   
                        if($data->maker_confirmation !=1)
                        {
                          return "Pending";  
                        }
                        else
                        {
                          return "Paid";     
                        }
                    })

                    ->editColumn('vendor_payment_amount',function($data) use ($current_context)
                    {
                       
                      
                       $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount = $amount_excluding_shipping_charge * $adminCommission/100;


                        return '$'.num_format($adminCommissionAmount);

                    })
                      ->editColumn('total_admin_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionPending']);
                    })
                    ->editColumn('total_admin_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionRecived']);
                    })
                    ->editColumn('total_admin_commission',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalAdminCommission']);
                    })
                   
                    ->editColumn('order_amount_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPending']);
                    })
        
                    ->editColumn('order_amount_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPaid']);
                    })
        
                    ->editColumn('total_vendor_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPendingPayVendors']);
                    })
                    ->editColumn('total_vendor_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPaidPayVendors']);
                    })

                    ->editColumn('total_pay_vendors',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPayVendors']);
                    })

                    ->editColumn('action',function($data) use ($current_context,$formData)
                    {
                        $build_view_action  =  $pay_admin_button = '';

                        if($data->admin_commission_status == '1')
                        {
                            $is_disabled = 'display:none';
                        }
                        else
                        {
                            $is_disabled='display:block';
                        }


                        $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->order_no);

                        /*************************************************************************/
                
                        $totalPrice = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;


                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);   
                

                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;


                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;

                        if($adminCommission==0)
                        {
                          $admin_commission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }
                        else
                        {
                            $admin_commission = $adminCommission;
                        }
                       

                        //calculate admin commission amount
                        $admin_commission_amount = $amount_excluding_shipping_charge*($admin_commission / 100);

                        if($data->ship_status == '1')
                        {
                            $order_from = '';

                            if($data->is_direct_payment == 1)
                            { 
                                $pay_admin_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="Pay '.$this->site_setting_arr['site_name'].'"  onclick="fillData('.num_format($amount_excluding_shipping_charge).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->order_id.','.$data->maker_id.')"" style="'.$is_disabled.'" >Pay '.$this->site_setting_arr['site_name'].'</button>';

                            }

                            $build_view_action = $pay_admin_button; 
                        }
                        else
                        {
                            return '--';
                        }

                        if($data->admin_commission_status == 1)
                        {
                            return '--';
                        }

                        return $build_action = $build_view_action;

                        /*************************************************************************/

                    });

                   
                  
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
    }

      public function get_indirect_payment_customer_orders($formData,$arr_search_column)
    {
     
        $objprefixStripeTransaction = $this->CommissionReportService->indirect_customer_order_query($formData,$arr_search_column);

        $total_commission_amount = $this->CommissionReportService->intermidiate_payment_customer_total_commission($formData,$arr_search_column);
      
        //dd($total_commission_amount);
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $json_result  = $json_result->editColumn('amount',function($data) use ($current_context)
                    {   
                      if (isset($data->total_retail_price)) {

                        $amount =  isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;
                      }
                     
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      
                        return num_format($amount);
                    })

                     ->editColumn('total_retail_price',function($data) use ($current_context)
                    {
                        return num_format($data->total_retail_price);
                    })

                     ->editColumn('order_no',function($data) use ($current_context)
                    {
                        return $data->order_no;
                    })


                     ->editColumn('created_at',function($data) use ($current_context)
                    {
                        return us_date_format($data->order_date);
                    })

                    /*->editColumn('admin_commission_status',function($data) use ($current_context)
                    {   dd($data);
                        if($data->admin_commission_status !=1)
                        {
                          return "Pending";  
                        }
                        else
                        {
                          return "Paid";     
                        }
                    })*/
                    
                    ->editColumn('vendor_payment_status',function($data) use ($current_context)
                    {    //dump($data->order_no , $data->maker_commission_status);
                        
                        if($data->maker_commission_status == '1')
                        {  
                           $vendor_payment_status = '<span class="label label-success">Paid</span>';
                        }
                        else
                        {
                            $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                        }
                        
                        return $vendor_payment_status;
                     
                    })

                    ->editColumn('vendor confirmation',function($data) use ($current_context)
                    {
                        if($data->maker_confirmation ==1)
                        {
                          return '<span class="label label-success">Paid</span>';  
                        }
                        else
                        {
                          return '<span class="label label-warning">Pending</span>';     
                        }
                    })

                    ->editColumn('vendor_payment_amount',function($data) use ($current_context)
                    {
                       
                      
                       $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount = $amount_excluding_shipping_charge * $adminCommission/100;

                        $totalPrice = $totalPrice - ($shipping_charge+$adminCommissionAmount);

                        return '$'.num_format($totalPrice);

                    })

                    ->editColumn('shipping_charge',function($data) use ($current_context)
                    {
                        return num_format($this->get_customer_order_shipping_charges($data->order_id));
                    })


                     ->editColumn('amount_excluding_shipping_charge',function($data) use ($current_context)
                    {

                       $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount = $amount_excluding_shipping_charge * $adminCommission/100;

                    
                        return num_format($amount_excluding_shipping_charge);
                    })
                      ->editColumn('total_admin_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionPending']);
                    })
                    ->editColumn('total_admin_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionRecived']);
                    })
                    ->editColumn('total_admin_commission',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalAdminCommission']);
                    })
                   
                    ->editColumn('order_amount_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPending']);
                    })
        
                    ->editColumn('order_amount_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPaid']);
                    })
        
                    ->editColumn('total_vendor_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPendingPayVendors']);
                    })
                    ->editColumn('total_vendor_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPaidPayVendors']);
                    })

                  ->editColumn('total_pay_vendors',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPayVendors']);
                    });

                   

                   
                  
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
    }
  
   


    public function direct_payment_to_vendor($formData,$arr_search_column=false)
    {       
        
       
       $objprefixStripeTransaction = $this->CommissionReportService->retailer_order_query($formData,$arr_search_column);


        $total_commission_amount = $this->CommissionReportService->calculate_vendor_total_commission('retailer',$formData);
       
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $json_result  = $json_result->editColumn('sales_manager_name',function($data) use ($current_context)
                    {
                        return $sales_manager_name = isset($data->sales_manager_name)?$data->sales_manager_name:'N/A';
                    })

                    ->editColumn('order_link',function($data) use ($current_context)
                    {
                        if ($data->order_no != "")
                        {

                            $arr_retailer_order_details = $this->RetailerQuotesModel
                                ->where('order_no',$data->order_no)
                                ->where('id',$data->order_id)
                                ->first();
                            if ($arr_retailer_order_details) 
                            {
                                $href='/vendor/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;
                                                     
                            } 
                            $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                          ->where('order_no',$data->order_no)
                                                          ->where('id',$data->order_id)
                                                          ->first();

                            if ($arr_rep_order_details) 
                            {
                                $href='/vendor/leads/view/'.base64_encode($arr_rep_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;                          
                            } 
                        }
                    })

                    ->editColumn('amount',function($data) use ($current_context)
                    {
                      if (isset($data->amount)) {

                        $amount =  isset($data->amount)?num_format($data->amount):0.00;
                      }
                      else{

                        $adminCommission  = $data->admin_commission;
                        if($adminCommission == null)
                        {
                             $adminCommission =  $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                        $totalPrice = $totalPrice - $shipping_charge;
                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      }
                        return num_format($amount);
                    })

                     ->editColumn('amount_excluding_shipping_charge',function($data) use ($current_context)
                    { 
                        $adminCommission  = $data->admin_commission;
                        if($adminCommission == null)
                        {
                             $adminCommission =  $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id); 
                      
                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {   
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);
                        }                      

                         $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';
                        if($promo_code == true)
                        {
                           $shipping_charge = 0;
                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        else
                        {
                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }


                    
                        return num_format($amount_excluding_shipping_charge);
                    })

                    //admin_commission_amount
                     ->editColumn('admin_commission_amount',function($data) use ($current_context)
                    {
                      
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission == 0)
                        {
                             $adminCommission =  $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                        
                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);
                    
                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                            $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);
                        }

                         $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';
                        if($promo_code == true)
                        {
                           $shipping_charge = 0;
                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        else
                        {
                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        
                        $totalPrice = $totalPrice - $shipping_charge;
                        $adminCommissionAmount  = ($totalPrice * $adminCommission )/100;

                    
                       return num_format($adminCommissionAmount);
                    })

                    ->editColumn('orderer_role',function($data) use ($current_context)
                    {
                      $role = "-";
                      if($data->role_id==4)
                      {
                        $role ="Retailer";
                      }
                      if($data->role_id==6)
                      {
                        $role =  "Customer";
                      }
                        return $role;
                    })

                    ->editColumn('orderer_name',function($data) use ($current_context)
                    {
                      
                        return $data->orderer_name;
                    })

                    //reppresentative and sales manager name
                    ->editColumn('rep_name',function($data) use ($current_context)
                    {
                      $rep_name = "-";

                      if(isset($data->representative_id))
                      {
                         //$rep_name = get_user_name($data->representative_id);
                         $rep_name = "Representative";
                      }
                      if(isset($data->sales_manager_id) && $data->representative_id==0 && $data->sales_manager_id!=0)
                      { 
                        //$rep_name = get_user_name($data->sales_manager_id);
                        $rep_name = "Sales Manager";
                      }
                      return $rep_name;
                    })

                    ->editColumn('rep_commission_amount',function($data) use ($current_context)
                    {
                      $rep_commission_amount = "-";
                      if(isset($data->representative_id) ||isset($data->sales_manager_id))
                      {
                        if(isset($data->representative_id) && $data->representative_id!=0)
                        {
                           $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }

                        if(isset($data->sales_manager_id) && $data->sales_manager_id!=0)
                        {
                           $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        }


                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission == 0)
                        {
                             $adminCommission =  $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);   

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                            $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);
                        }                    

                         $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';
                        if($promo_code == true)
                        {
                           $shipping_charge = 0;
                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        else
                        {
                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }

                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommission / 100);

                        $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommission) / 100;
                        
                        $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);

                        return '$'.num_format($rep_commission_amount);
                      }
                      return $rep_commission_amount;
                        
                    })

                      

                    ->editColumn('created_at',function($data) use ($current_context)
                    {
                        return us_date_format($data->order_date);
                    })
                    ->editColumn('rep_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalCommissionPending']);
                    })
                    ->editColumn('rep_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalCommissionPaid']);
                    })
                    ->editColumn('order_amount_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPending']);
                    })
                    ->editColumn('order_amount_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPaid']);
                    })
                    
                    //rep/sales commission
                    ->editColumn('admin_commission',function($data) use ($total_commission_amount)
                    {
                        
                        return num_format($total_commission_amount['adminCommissionEarned']);
                        
                    })
                    ->editColumn('vendor_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionPending']);
                    })
                    ->editColumn('vendor_commission_paid',function($data) use ($total_commission_amount)
                    {   
                        return num_format($total_commission_amount['adminCommissionRecived']);

                        //return num_format($total_commission_amount['vendorCommissionPaid']);
                    })

                    ->editColumn('total_vendors_amount',function($data) use ($total_commission_amount)
                    {   
                        return num_format($total_commission_amount['totalCommission']);

                        //return num_format($total_commission_amount['vendorCommissionPaid']);
                    })

                    ->editColumn('total_retailers_payment',function($data) use ($total_commission_amount)
                    {   
                        return num_format($total_commission_amount['totalRetailersPayment']);

                        //return num_format($total_commission_amount['vendorCommissionPaid']);
                    })

                    ->editColumn('status',function($data)
                    {
                        return '-';
                    })

                    ->editColumn('maker_commission_status',function($data) use ($current_context)
                    {
                        // dump($data->status);
                        return $data->status;
                    })
                    ->editColumn('order_link',function($data) use ($current_context)
                    {
                        if ($data->order_no != "") {

                            $arr_retailer_order_details = $this->RetailerQuotesModel
                                                     ->where('order_no',$data->order_no)
                                                     ->where('id',$data->order_id)
                                                     ->first();
                            if ($arr_retailer_order_details) 
                            {
                                $href='/vendor/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;
                                                     
                            } 
                            $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();

                            if ($arr_rep_order_details) 
                            {
                                $href='/vendor/leads/view/'.base64_encode($arr_rep_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;                          
                            } 
                        }
                    })
                
                    ->editColumn('commission_status',function($data) use ($current_context,$formData)
                    {
                        if($formData['commissionStatus'] == '3')
                        {
                            return 'Failed';
                        }
                        elseif($formData['commissionStatus'] == '2')
                        {
                            return 'Paid';
                        }
                        else{
                            return 'Pending';
                        }
                    })
                    ->editColumn('action',function($data) use ($current_context,$formData){

                        if($data->admin_commission_status == '1')
                        {
                            $is_disabled = 'display:none';
                        }
                        else
                        {
                            $is_disabled='display:block';
                        }

                        
                        $build_view_action  = '';

                        $view_href   =  $this->module_url_path.'/view/'.base64_encode($data->order_no);

                        /*************************************************************************/
                        $pay_admin_button = '';


                        $totalPrice = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);   

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                            $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);

                        }                    

                         $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';
                        if($promo_code == true)
                        {
                           $shipping_charge = 0;
                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        else
                        {
                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }


                       $admin_commission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($admin_commission == 0)
                        {
                             $admin_commission =  $this->CommissionService->get_admin_commission();
                        }
                       

                        //calculate admin commission amount
                        $admin_commission_amount = $amount_excluding_shipping_charge*($admin_commission / 100);


                        if($data->ship_status == '1')
                        {
                            $order_from = '';

                            if($data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                            { 
                                $order_from_retailer = $this->RetailerQuotesModel
                                                            ->where('id',$data->order_id)
                                                            ->where('order_no',$data->order_no)
                                                            ->count();

                                $order_from_rep  = $this->RepresentativeLeadsModel
                                                        ->where('id',$data->order_id)
                                                        ->where('order_no',$data->order_no)
                                                        ->count();                    

                                if($order_from_retailer >0)
                                {
                                   $order_from = 1;
                                } 

                                if($order_from_rep >0)
                                {
                                   $order_from = 0;
                                }

                                $pay_admin_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="Pay '.$this->site_setting_arr['site_name'].'"  onclick="fillData('.num_format($amount_excluding_shipping_charge).','.$admin_commission.','.num_format($admin_commission_amount).','.$data->order_id.','.$data->maker_id.','.$order_from.')"" style="'.$is_disabled.'" >Pay '.$this->site_setting_arr['site_name'].'</button>';

                            }


                            $build_view_action = $pay_admin_button; 
                        }
                        else
                        {
                            return '--';
                        }

                        if($data->admin_commission_status == 1)
                        {
                            return '--';
                        }

                        return $build_action = $build_view_action;

                        /*************************************************************************/

                    })
                    
                    ->editColumn('showCheckboxes',function($data) use ($current_context)
                    {   
                         if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                        { 
                           
                           $retailer_details = $this->RetailerQuotesModel->where('id',$data->order_id)
                                                                          ->where('order_no',$data->order_no)
                                                                          ->first();

                            if(isset($retailer_details))
                            {
                                $order_type = "retailer";
                            } 


                            $leadsDetails = $this->RepresentativeLeadsModel->where('id',$data->order_id)
                                                                           ->where('order_no',$data->order_no)
                                                                           ->first();

                            if(isset($leadsDetails))
                            {
                                $order_type = "rep-sales";
                            }     

                           // $checkbox_itemVal[$order_type] = $data->order_id;

                            $orderNo_checkbox = '<div class="checkbox checkbox-success"><input type="checkbox" id="order_numbers_'.$data->order_id.'" name="order_numbers[]" value="'. $data->order_id .'" class="checkItem case checkOrderItems" data-ordertype="'. $order_type .'"><label for="order_numbers_'.$data->order_id.'"></label></div>';

                            $checkbox_itemVal = array();

                        } 
                        else
                        {
                            $orderNo_checkbox = '<div class="checkbox checkbox-success">--</div>';
                        }

                        return $orderNo_checkbox;
                    })
                    ->editColumn('total_wholesale_price',function($data) use ($current_context)
                    {   //dd("total_wholesale_price",$data);
                        $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                        
                        return num_format($total_wholesale_price);
                    })
                    ->editColumn('total_rep_commission',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission = isset($total_commission_amount['totalRepCommission'])?num_format($total_commission_amount['totalRepCommission']):0.00;
                    })
                    ->editColumn('total_rep_commission_paid',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission_paid = isset($total_commission_amount['totalRepCommissionPaid'])?num_format($total_commission_amount['totalRepCommissionPaid']):0.00;
                    })
                    ->editColumn('total_rep_commission_pending',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission_pending = isset($total_commission_amount['totalRepCommissionPending'])?num_format($total_commission_amount['totalRepCommissionPending']):0.00;
                    });
            
            
                           
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
    }

    public function payment_intermediation($formData,$arr_search_column=false)
    {
        $total_commission_amount = $this->CommissionReportService->indirect_calculate_vendor_total_commission('retailer',$formData);


        $objprefixStripeTransaction = $this->CommissionReportService->retailer_indirect_order_query($formData,$arr_search_column);

        //dd($objprefixStripeTransaction->get()->toArray());
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $json_result  = $json_result->editColumn('sales_manager_name',function($data) use ($current_context)
                    {
                        //dd($data);
                        return $sales_manager_name = isset($data->sales_manager_name)?$data->sales_manager_name:'N/A';
                    })

                    ->editColumn('amount',function($data) use ($current_context)
                    {   
                      if (isset($data->amount)) {

                        $amount =  isset($data->amount)?num_format($data->amount):0.00;
                      }
                     
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      
                        return num_format($amount);
                    })

                    ->editColumn('shipping_charges',function($data) use ($current_context)
                    { 
                        $shipping_charges = 0.00;

                        $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';
                        
                        if($promo_code == true)
                        {  
                           $shipping_charges = 0.00;
                        }
                        else
                        {
                             
                            $totalPrice = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                            $shipping_charges = $this->get_retailer_order_shipping_charges($data->order_id);  

                            if(isset($data->representative_id)||isset($data->sales_manager_id))
                            {
                               $shipping_charges = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                            }
    
                            
                        }

                        return '$'.num_format($shipping_charges);
                       
                    })    

                     ->editColumn('amount_excluding_shipping_charge',function($data) use ($current_context)
                    {

                       $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        
                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);  

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        }                     


                        $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';

                        if($promo_code == true)
                        {
                           $shipping_charge = 0;
                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }
                        else
                        {
                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        }

                       
                    
                        return num_format($amount_excluding_shipping_charge);
                    })

                    ->editColumn('rep_name',function($data) use ($current_context)
                    {
                      $rep_name = "-";

                      if(isset($data->representative_id))
                      {
                        //$rep_name = get_user_name($data->representative_id);
                         $rep_name = "Representative";
                      }
                      if(isset($data->sales_manager_id) && $data->representative_id==0 && $data->sales_manager_id!=0)
                      { 
                        //$rep_name = get_user_name($data->sales_manager_id);
                        $rep_name = "Sales Manager";
                      }
                      return $rep_name;
                    })

                    ->editColumn('rep_commission_amount',function($data) use ($current_context)
                    {
                      $rep_commission_amount = "-";
                      $representative_commission = 0;
                      /*if(isset($data->representative_id))
                      {*/
                       // $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);

                        if(isset($data->representative_id) && $data->representative_id!=0)
                        {
                          $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }

                        if(isset($data->sales_manager_id) && $data->sales_manager_id!=0)
                        {
                          $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        }

                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                        $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';

                        if($promo_code == true)
                        {
                           $shipping_charge = 0.00;
                        }
                        else
                        {
                           $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);  

                            if(isset($data->representative_id)||isset($data->sales_manager_id))
                            {
                               $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                            }
                        }

                          

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                          /* $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);*/

                           $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        
                           $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommission / 100);
                        
                           $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);

                          //dd("rep order",$adminCommissionAmount,$rep_commission_amount);
                        }                     
                    

                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        
                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommission / 100);
                        
                        $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);

                        //dd($adminCommissionAmount);

                        return "$".num_format($rep_commission_amount);
                   
                      //return $rep_commission_amount;
                        
                    })

                    ->editColumn('created_at',function($data) use ($current_context)
                    {
                        return us_date_format($data->order_date);
                    })
                    ->editColumn('rep_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalCommissionPending']);
                    })
                    ->editColumn('rep_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalCommissionPaid']);
                    })
                    ->editColumn('order_amount_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPending']);
                    })
                    ->editColumn('order_amount_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmountPaid']);
                    })
                    ->editColumn('admin_commission',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['adminCommissionEarned']);
                    })
                    ->editColumn('vendor_commission_pending',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPendingPayVendors']);
                    })
                    ->editColumn('vendor_commission_paid',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPaymentsPaidPayVendors']);
                    })

                  ->editColumn('total_pay_vendors',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalPayVendors']);
                    })

                    ->editColumn('status',function($data)
                    {   
                        return $data->status;
                    })

                    ->editColumn('vendor_commission_amount',function($data) use ($current_context)
                    {
                        
                       $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                        $promo_code = isset($data->promo_code)?is_promocode_freeshipping($data->promo_code):'';

                        if($promo_code == true)
                        {
                           $shipping_charge = 0.00;
                        }
                        else
                        {
                            
                            $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id); 

                            if(isset($data->representative_id)||isset($data->sales_manager_id))
                            {
                               $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                            }    
                        }
                 
         

                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommission / 100);

                        $rep_commission_amount =0;

                        if(isset($data->representative_id)  || isset($data->sales_manager_id))
                        {
                           
                            if(isset($data->representative_id) && $data->representative_id!=0)
                            {
                              $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                            }

                            if(isset($data->sales_manager_id) && $data->sales_manager_id!=0)
                            {
                              $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                            }


                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                           /* $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommission / 100);*/

                            $rep_commission_amount = $adminCommissionAmount * ($representative_commission / 100);

                        }
            

                       /* $makerCommissionAmount = $totalPrice-$shipping_charge-$adminCommissionAmount-$rep_commission_amount;*/

                       $makerCommissionAmount = $amount_excluding_shipping_charge - $adminCommissionAmount; 
                     
                        return num_format($makerCommissionAmount);
                    })

                    ->editColumn('maker_commission_status',function($data) use ($current_context)
                    {
                        return $data->maker_commission_status;
                    })
                     ->editColumn('maker_confirmation',function($data) use ($current_context)
                    {
                        return $data->maker_confirmation;
                    })


                ->editColumn('order_link',function($data) use ($current_context)
                {
                    if ($data->order_no != "") {

                        $arr_retailer_order_details = $this->RetailerQuotesModel
                                                 ->where('order_no',$data->order_no)
                                                 ->where('id',$data->order_id)
                                                 ->first();
                        if ($arr_retailer_order_details) 
                        {
                            $href='/vendor/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                            $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                            return $build_view_action;
                                                 
                        } 
                        $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                     ->where('order_no',$data->order_no)
                                                     ->where('id',$data->order_id)
                                                     ->first();

                        if ($arr_rep_order_details) 
                        {
                            $href='/vendor/leads/view/'.base64_encode($arr_rep_order_details['id']);
                            $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                            return $build_view_action;                          
                        } 
                    }
                })

                ->editColumn('action',function($data) use ($current_context,$formData){

                    $build_pay_vendor_btn = $pay_rep_commission = $pay_sales_man_commission = '-';
                    
                    $sale_manager_pay_btn = $is_disabled = '';
                     
                        /* Pay vendor commission */

                        if($data->maker_commission_status == 1)
                        {
                          $is_disabled = 'display:none';
                        }


                        if($data->representative_id == 0)
                        {
                            $rep_pay_btn = 'display:none';
                            $sale_manager_pay_btn = 'display:block';
                        }
                        else
                        { 
                            $rep_pay_btn = 'display:block';
                            $sale_manager_pay_btn = 'display:none';
                        }
                        
                        if($data->representative_id == 0 && $data->sales_manager_id == 0)
                        {
                            $rep_pay_btn = 'display:none';
                            $sale_manager_pay_btn = 'display:none';

                        }

                        if ($data->sales_manager_id != 0) 
                        {
                           $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        }
                        else
                        {
                           $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }

                    
                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        }                     
    
                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommissionPercent / 100);

                        $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                        $representative_pay_amount = $adminCommissionAmount * ($representative_commission / 100);
                          /*

                                if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                                {
                                   $build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';

                                }*/


                                /* Pay representative commission if order payment type is direct payment */
                                /*if($data->is_direct_payment === 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && ($data->rep_commission_status == 0 || $data->sales_manager_commission_status == 0))
                                {*/
                           /*        $pay_rep_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->order_id.')" >Pay Representative</button>';

                                   $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->order_id.')" >Pay Sales Manager</button>';*/
                                // }

                                   
                            

                                //return $build_pay_vendor_btn. $pay_rep_commission . $pay_sales_man_commission;
                                //return $build_pay_vendor_btn;
                    })
                
                ->editColumn('showCheckboxes',function($data) use ($current_context)
                    {   
                      
                        $bulk_pay_vendor_class = $bulk_rep_pay_class = $bulk_sale_manager_pay_class = "";
                    
                        $sale_manager_pay_btn = $is_disabled = '';
                     
                        /* Pay vendor commission */

                        if($data->maker_commission_status == 1)
                        {
                          $is_disabled = 'display:none';
                        }


                        if($data->representative_id == 0)
                        {
                            $bulk_sale_manager_pay_class = "checkbox_bulk_sales_manager";
                        }
                        else
                        { 
                           $bulk_rep_pay_class = "checkbox_bulk_representative";
                        }
                        
                        if($data->representative_id == 0 && $data->sales_manager_id == 0)
                        {
                           $bulk_rep_pay_class = $bulk_sale_manager_pay_class = "";
                        }

                        if ($data->sales_manager_id != 0) 
                        {
                           $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        }
                        else
                        {
                           $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }

                    
                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        }                     
    
                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommissionPercent / 100);

                        $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                        $representative_pay_amount = $adminCommissionAmount * ($representative_commission / 100);


                        if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                        {
                           //$build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';

                           if($is_disabled == ''){

                                $bulk_pay_vendor_class = "checkbox_bulk_vendor";

                           }

                           
                        }

   
                        $data_attr = 'data-amount_excluding_shipping_charge="'.$amount_excluding_shipping_charge.'"';
                        $data_attr .= ' data-representative_commission="'.$representative_commission.'"';
                        $data_attr .= ' data-representative_pay_amount="'.num_format($representative_pay_amount).'"';
                        $data_attr .= ' data-sales_manager="'.$data->sales_manager_id.'"';
                        $data_attr .= ' data-representative="'.$data->representative_id.'"';
                        $data_attr .= ' data-adminCommissionAmount="'.num_format($adminCommissionAmount).'"';
                        $data_attr .= ' data-maker_id="'.$data->maker_id.'"';
                        $data_attr .= ' data-totalPrice="'.$totalPrice.'"';
                        $data_attr .= ' data-vendorPaybleAmount="'.num_format($vendorPaybleAmount).'"';

                        $action_checkboxs = '<div class="checkbox checkbox-success">--</div>';

                        if($bulk_pay_vendor_class != "" || $bulk_rep_pay_class != "" || $bulk_sale_manager_pay_class != "" )
                        {
                            $checkbox_extra_class = $bulk_pay_vendor_class." ".$bulk_rep_pay_class." ".$bulk_sale_manager_pay_class; 
                            $action_checkboxs = '
                                <div class="checkbox checkbox-success">
                                    <input type="checkbox" id="order_numbers_'.$data->order_id.'" name="order_numbers[]" value="'. $data->order_id .'" class="checkItem case checkOrderItems '.$checkbox_extra_class .'" '.$data_attr.'>
                                    <label for="order_numbers_'.$data->order_id.'"></label>
                                </div>';  
                        }
                    

                        return $action_checkboxs;
                    })
                ->editColumn('build_action_btn',function($data) use ($current_context)
                    {   
                        
                        $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->order_id).'/'.base64_encode('quotes');
                        
                        $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$href.'" title="View">View</a>';

                        return $build_view_action;
                    }) 

                    ->editColumn('commission_status',function($data) use ($current_context,$formData)
                    {
                        $rep_sales_commission = '';

                        if(isset($data->representative_id) && $data->representative_id!=0)
                        {
                           $rep_sales_commission =  $data->rep_commission_status;
                        }
                        else if(isset($data->sales_manager_id) && $data->sales_manager_id!=0)
                        {
                            $rep_sales_commission =  $data->sales_manager_commission_status;
                        }


                        if($rep_sales_commission == '1')
                        {
                            $rep_payment_status = '<span class="label label-warning">Paid</span>';
                        }
                        elseif($rep_sales_commission == '0')
                        {
                            $rep_payment_status = '<span class="label label-warning">Pending</span>';
                        }   
                        else
                        {
                            $rep_payment_status = "-";
                        }


                        return $rep_payment_status;
                    })
                    
                    ->editColumn('total_wholesale_price',function($data) use ($current_context)
                    {
                        return $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                    })
                    ->editColumn('total_rep_commission',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission = isset($total_commission_amount['totalRepCommission'])?num_format($total_commission_amount['totalRepCommission']):0.00;
                    })
                    ->editColumn('total_rep_commission_paid',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission_paid = isset($total_commission_amount['totalRepCommissionPaid'])?num_format($total_commission_amount['totalRepCommissionPaid']):0.00;
                    })
                    ->editColumn('admin_commission_status',function($data) use ($current_context)
                    {
                        return $data->admin_commission_status;
                    })
                    ->editColumn('total_rep_commission_pending',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_rep_commission_pending = isset($total_commission_amount['totalRepCommissionPending'])?num_format($total_commission_amount['totalRepCommissionPending']):0.00;
                    })
                    ->editColumn('total_vendor_commission_pending',function($data) use ($current_context,$total_commission_amount)
                    {//dd($total_commission_amount);
                        return $total_vendor_commission_pending = isset($total_commission_amount['adminCommissionPending'])?num_format($total_commission_amount['adminCommissionPending']):0.00;
                    })
                    ->editColumn('total_vendor_commission_paid',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_vendor_commission_paid = isset($total_commission_amount['adminCommissionRecived'])?num_format($total_commission_amount['adminCommissionRecived']):0.00;
                    })
                    ->editColumn('total_vendor_commission',function($data) use ($current_context,$total_commission_amount)
                    {
                        return $total_vendor_commission = isset($total_commission_amount['totalCommission'])?num_format($total_commission_amount['totalCommission']):0.00;
                    });

                    
            
                         
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
   // }
}

public function customer_report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];

    
    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    $filterData['vendorId']    = isset($formData['vendorId'])?$formData['vendorId']:null;
    $filterData['is_direct_payment'] = 1;


    $objprefixStripeTransaction = $this->CommissionReportService->customer_order_query($filterData);
    
    $objprefixStripeTransaction = $objprefixStripeTransaction->get();

    if(isset($objprefixStripeTransaction))
    {
        $objprefixStripeTransaction = $objprefixStripeTransaction->toArray();
    }
    $data =  $arrayResponseData = [];

    foreach($objprefixStripeTransaction as $key => $value)
    { 
               
        //dd($value);
        /*-------------------------------------------------------*/
          $admin_commission_status = 'Pending';
          if($value->admin_commission_status == 0 || $value->admin_commission_status==null)
          {

            $admin_commission_status =  'Pending';
          }
          //---------------For retailer orders-----------
          if($value->admin_commission_status == 1)
          {

            $admin_commission_status = 'Paid';
          }
        /*-------------------------------------------------------*/
        


        /*-------------------------------------------------------*/  
          if($value->transaction_status == 2)
          {
            
            $retailer_payment_status =  'Paid';
          }
          if($value->transaction_status == 3)
          {
            
            $retailer_payment_status = 'Failed';
          }
          if($value->transaction_status == 1 || $value->transaction_status ==null)
          {

            $retailer_payment_status = 'Pending';
          }          
        /*-------------------------------------------------------*/

        $shipping_charge = $this->get_customer_order_shipping_charges($value->order_id);

        //dd($shipping_charge);

      
        $adminCommission  = isset($value->maker_admin_commission)?num_format($value->maker_admin_commission):0.00;
        
        if($adminCommission==0)
        {
            $adminCommission  = isset($value->admin_commission)?num_format($value->admin_commission):0.00;
        }

        $totalPrice       = isset($value->total_retail_price)?num_format($value->total_retail_price):0.00;
                        

        $totalPrice = $totalPrice - $shipping_charge;

        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):0.00;
        $amount = isset($value->total_retail_price)?num_format($value->total_retail_price):0.00;

        $amount_excluding_shipping_charge = $amount - $shipping_charge;

        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            if(isset($value->representative_id))
            {
                $representative_commission = $this->CommissionService->get_representative_commission($value->representative_id);
            }
            if(isset($value->sales_manager_id))
            {
                $representative_commission = $this->CommissionService->get_sales_manager_commission($value->sales_manager_id);
            } 
 
         $rep_commission_amount = ($adminCommissionAmount * $representative_commission)/100 ;

         //$rep_commission_amount = $adminCommissionAmount;
         $rep_commission_amount = $rep_commission_amount;

        }

        $amount_excluding_shipping_charge = isset($amount_excluding_shipping_charge)?num_format($amount_excluding_shipping_charge):0.00;
        
        $admin_commission = isset($adminCommissionAmount)?num_format($adminCommissionAmount):'0.00';
    
        $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):'-';

        $array = (array)$value;

        $arrayResponseData['order_date']       = us_date_format($value->order_date);
        $arrayResponseData['order no']         = $value->order_no;
        $arrayResponseData['Customer']         = $value->orderer_name;
        $arrayResponseData['vendor']           = $value->vendor_name;
          
        $arrayResponseData['Order amount']     = $amount;
        $arrayResponseData['Shipping Charges'] = $shipping_charge;
        $arrayResponseData['Order Amount
          (Excluding Shipping Charge)'] = $amount_excluding_shipping_charge;
        $arrayResponseData['Customer Payment Status']      = $retailer_payment_status;
        $arrayResponseData['Admin Commission']   = $admin_commission;
        $arrayResponseData['Admin Commission Status'] = $admin_commission_status;
      
        array_push($data,$arrayResponseData); 

    }

    return Excel::create('Commission Report', function($excel) use ($data) {
        
      $excel->sheet('Commission Reports', function($sheet) use ($data)
      {
        $sheet->fromArray($data);
        $sheet->freezeFirstRow();  
        $sheet->cells("A1:M1", function($cells) {            
          $cells->setFont(array(              
            'bold'       =>  true
          ));

        });
      });

    })->download($type);
}

public function customer_intermidiate_report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];

    //dd($formData);

    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    
    $filterData['vendorId']    = isset($formData['vendorId'])?$formData['vendorId']:null;
    $filterData['is_direct_payment'] = 0;


    //ssdd($filterData);

    $objprefixStripeTransaction = $this->CommissionReportService->indirect_customer_order_query($filterData);
    
    $objprefixStripeTransaction = $objprefixStripeTransaction->get();

    if(isset($objprefixStripeTransaction))
    {
        $objprefixStripeTransaction = $objprefixStripeTransaction->toArray();
    }
    $data =  $arrayResponseData = [];

    foreach($objprefixStripeTransaction as $key => $value)
    { 
               
        
        if($value->maker_confirmation ==1)
        {
          $customer_payment_status = "Paid";  
        }
        else
        {
           $customer_payment_status = "Pending";     
        }
                                        
        if($value->stripe_trxn_status == '2' && $value->maker_confirmation =='1')
        {
           $vendor_payment_status = "Paid";
        }
        else if($value->stripe_trxn_status == '3')
        {
            $vendor_payment_status = "Failed";
        }
        elseif($value->stripe_trxn_status == '1') 
        {
           $vendor_payment_status = "Pending";
        }
        else
        {
            $vendor_payment_status = "Pending";
        }

                     
        $shipping_charge = $this->get_customer_order_shipping_charges($value->order_id);


      
        $adminCommission  = isset($value->maker_admin_commission)?num_format($value->maker_admin_commission):0.00;
        
        if($adminCommission==0)
        {
            $adminCommission  = isset($value->admin_commission)?num_format($value->admin_commission):0.00;
        }

        $totalPrice       = isset($value->total_retail_price)?num_format($value->total_retail_price):0.00;
        
        $totalPrice = $totalPrice - $shipping_charge;

        $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):0.00;
        $amount = isset($value->total_retail_price)?num_format($value->total_retail_price):0.00;

        $amount_excluding_shipping_charge = $amount - $shipping_charge;

        $vendor_outbound_amount = $totalPrice - $adminCommissionAmount;    


        if(isset($value->representative_id)||isset($value->sales_manager_id))
        {
            if(isset($value->representative_id))
            {
                $representative_commission = $this->CommissionService->get_representative_commission($value->representative_id);
            }
            if(isset($value->sales_manager_id))
            {
                $representative_commission = $this->CommissionService->get_sales_manager_commission($value->sales_manager_id);
            } 
 
         $rep_commission_amount = ($adminCommissionAmount * $representative_commission)/100 ;

         //$rep_commission_amount = $adminCommissionAmount;
         $rep_commission_amount = $rep_commission_amount;

        }

        $amount_excluding_shipping_charge = isset($amount_excluding_shipping_charge)?num_format($amount_excluding_shipping_charge):0.00;
        
        $admin_commission = isset($adminCommissionAmount)?num_format($adminCommissionAmount):'0.00';
    
        $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):'-';

        $array = (array)$value;

        $arrayResponseData['order_date']       = us_date_format($value->order_date);
        $arrayResponseData['order no']         = $value->order_no;
        $arrayResponseData['Customer']         = $value->orderer_name;
        $arrayResponseData['vendor']           = $value->vendor_name;
          
        $arrayResponseData['Order amount']     = $amount;
        $arrayResponseData['Shipping Charges'] = $shipping_charge;
        $arrayResponseData['Order Amount
          (Excluding Shipping Charge)'] = $amount_excluding_shipping_charge;
        $arrayResponseData['Customer Payment Status']      = $customer_payment_status;
        $arrayResponseData['Vendor Payment (Outbound)']   = $vendor_outbound_amount;
        $arrayResponseData['Vendor Payment Status (Outbound)'] = $vendor_payment_status;
      
        array_push($data,$arrayResponseData); 

    }

    return Excel::create('Commission Report', function($excel) use ($data) {
        
      $excel->sheet('Commission Reports', function($sheet) use ($data)
      {
        $sheet->fromArray($data);
        $sheet->freezeFirstRow();  
        $sheet->cells("A1:M1", function($cells) {            
          $cells->setFont(array(              
            'bold'       =>  true
          ));

        });
      });

    })->download($type);
}

// Function added by Harshada.k on date 16 Oct 2020    
    public function get_retailer($role=false)
    {

         $userData = array();
         $objUserData = $this->RetailerModel
            ->withCount(['user_details as username' => function($query) {
                $query->select(DB::raw('id as userid'));
            }])
           ->where('store_name','!=','')     
           ->orderBy('store_name','ASC')
           ->get();
          //echo "<pre>";print_r($objUserData->toArray());die;
          if ($objUserData) {
            $usersDetailes = $objUserData->toArray();
            $userData      = $usersDetailes;
          }
        
        return $userData;
    }
}
