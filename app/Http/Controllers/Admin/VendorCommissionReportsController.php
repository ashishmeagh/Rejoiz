<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\CommissionReportService; 
use App\Common\Services\CommissionService;
use App\Models\StripeTransactionModel;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RetailerModel;
use App\Models\RetailerQuotesModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\CustomerQuotesProductModel;
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeModel;
use App\Models\SalesManagerModel;
use App\Models\CustomerQuotesModel;
use App\Models\RepresentativeProductLeadsModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use DB;
use Datatables;
use Excel, Validator;


class VendorCommissionReportsController extends Controller
{
    public function __construct(StripeTransactionModel $StripeTransactionModel,
                                CommissionReportService $CommissionReportService,
                                CommissionService $CommissionService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                RetailerModel $RetailerModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel,
                                UserModel $UserModel,
                                RepresentativeModel $RepresentativeModel,
                                SalesManagerModel $SalesManagerModel,
                                MakerModel $MakerModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel
                                )
    {
        $this->arr_view_data      = [];
        $this->module_title       = "Direct Payment Reports";
        $this->module_view_folder = "admin.vendor_commission_reports";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/direct_payment_to_vendor");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
        $this->role                       = 'retailer';
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->MakerModel                 = $MakerModel;
        $this->RetailerModel              = $RetailerModel;
        $this->CommissionReportService    = $CommissionReportService;
        $this->RetailerQuotesModel        = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->CustomerQuotesProductModel = $CustomerQuotesProductModel;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeModel        = $RepresentativeModel;
        $this->SalesManagerModel          = $SalesManagerModel;
        $this->UserModel                  = $UserModel;
        $this->CustomerQuotesModel        = $CustomerQuotesModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->CommissionService              = $CommissionService;
    }


    public function index()
    {  
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrRepresentative  = $this->get_user('representative');
        $arrRetailer        = $this->get_retailer('retailer');
        $arrSalesManger     = $this->get_user('sales_manager');
        //echo "<pre>";print_r( $arrMakers);die;
        
        $this->arr_view_data['page_title']          = $this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrRetailer']         = isset($arrRetailer) ? $arrRetailer: [];
        $this->arr_view_data['arrSalesManager']      = isset($arrSalesManger) ? $arrSalesManger: [];
        $this->arr_view_data['arrRepresentative']   = isset($arrRepresentative) ? $arrRepresentative: [];


        return view($this->module_view_folder.'.index', $this->arr_view_data);
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
       

        $objprefixStripeTransaction = $this->get_retailer_orders($formData,$arr_search_column); 
        //dd($objprefixStripeTransaction);

        /*if ($formData['toDate'] == false && $formData['fromDate'] == false && $formData['commissionStatus'] == false && $formData['makerId'] == false && $formData['retailerId'] == false && $formData['repId'] == false && $formData['salesId'] == false) 
        {

            $objprefixStripeTransaction = $this->get_all_rep_orders($formData,$arr_search_column);


            return $objprefixStripeTransaction;
        }*/

        $arr_search_column = $request->input('column_filter');
     

        if ($formData['repId'] != false || $formData['salesId'] != false) {


            //$objprefixStripeTransaction = $this->get_reps_orders($formData,$arr_search_column);

            /*if (isset($arr_search_column['order_status']) && $arr_search_column['order_status'] != '1') 
            {

                //Reps order commission report for admin pay commition to reps
            }

            elseif(isset($arr_search_column['order_status']) && $arr_search_column['order_status'] == '1')
            {
                //Reps order commission report for admin not pay any commition to reps
                $objprefixStripeTransaction = $this->get_reps_pending_orders($formData,$arr_search_column);
            } 
            else{

                //Reps order commission report for all orders
                $objprefixStripeTransaction = $this->get_reps_orders($formData,$arr_search_column);                

            } */
            return $objprefixStripeTransaction;       
           
        }
        /*if ($formData['salesId'] != false) {

            dd(123);

            $objprefixStripeTransaction = $this->get_reps_orders($formData,$arr_search_column);
        }*/
       // dd($arr_search_column);

        // if (isset($arr_search_column['retailer']) && $arr_search_column['representative'] == false) {

        if (isset($arr_search_column['retailer'])) {

           $objprefixStripeTransaction = $this->get_retailer_orders($formData,$arr_search_column); 
            /*if (isset($arr_search_column['order_status']) && $arr_search_column['order_status'] != '1') 
            {
                //Retailer order commission report for admin pay commition to reps
                $objprefixStripeTransaction = $this->get_retailer_paid_orders($formData,$arr_search_column);
            }
            else
            {
                //Retailer order commission report for admin not pay any commition to reps
                $objprefixStripeTransaction = $this->get_retailer_pending_orders($formData,$arr_search_column);

            } */
            return $objprefixStripeTransaction;                    
        }
        
            return  $objprefixStripeTransaction;    
    }

    public function get_all_rep_orders($formData,$arr_search_column)
    {
       
        $total_commission_amount = $this->CommissionReportService->calculate_rep_total_commission('all',$formData); 
               
        $objprefixStripeTransaction = $this->CommissionReportService->all_rep_order_query();
        
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        

        $json_result  = $json_result->editColumn('sales_manager_name',function($data) use ($current_context)
        {
            return $sales_manager_name = isset($data->sales_manager_name)?$data->sales_manager_name:'N/A';
        })

        ->editColumn('amount',function($data) use ($current_context)
        {
          if (isset($data->amount)) {

            $amount =  isset($data->amount)?num_format($data->amount):'0.00';
          }
          else{

            if ($data->sales_manager_id != 0) {

                $repCommission    = $this->CommissionService->get_sales_manager_commission();
            }
            else{

                $repCommission    = $this->CommissionService->get_representative_commission();
            }

            $adminCommission  = $this->CommissionService->get_admin_commission();

            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

            $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

            $amount                 = $adminCommissionAmount * ($repCommission / 100);
          }
            return num_format($amount);
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
            return num_format($total_commission_amount['vendorCommissionPending']);
        })
        ->editColumn('vendor_commission_paid',function($data) use ($total_commission_amount)
        {
            return num_format($total_commission_amount['vendorCommissionPaid']);
        })

        ->editColumn('rep_name',function($data) use ($current_context,$formData)
        {
            if($data->sales_manager_id != 0)
            {
                return $data->sales_manager_name;
            }
            else
            {
                return $data->rep_name;
            }
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
                    $href='/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                    $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                    return $build_view_action;
                                         
                } 
                $arr_rep_order_details = $this->RepresentativeLeadsModel
                                             ->where('order_no',$data->order_no)
                                             ->where('id',$data->order_id)
                                             ->first();

                if ($arr_rep_order_details) 
                {
                    $href='/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);
                    $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                    return $build_view_action;                          
                } 
            }
        })
       
        ->editColumn('build_action_btn',function($data) use ($current_context,$arr_search_column,$formData)
        { 
            /*if($data->quote_id != "")
            {
               $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
            }
            else
            {
               $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->order_id).'/'.base64_encode('lead');
            }
            
            $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$href.'" title="View">View</a>';

            return $build_view_action;*/

           /* if($data->quote_id != "")
            {
               $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
            }*/
            
            if(isset($data->order_id) && $data->order_id != "")
            {
              $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->order_id).'/'.base64_encode('lead');

            }

            $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$href.'" title="View">View</a>';
            return $build_view_action;
        })  
        
        ->editColumn('total_wholesale_price',function($data) use ($current_context)
        {
            return $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
        });
                    
                                
        $build_result = $json_result->make(true)->getData();
        
        return response()->json($build_result);
    }

    // public function get_reps_orders($formData,$arr_search_column)
    // {

       
    //     $total_commission_amount = $this->CommissionReportService->calculate_rep_total_commission('reps',$formData);
    //     // dd($total_commission_amount);          
    //     $objprefixStripeTransaction = $this->CommissionReportService->rep_order_query($formData,$arr_search_column);
    //     $current_context = $this;

    //     $json_result  = Datatables::of($objprefixStripeTransaction);
        
    //     $json_result  = $json_result->editColumn('sales_manager_name',function($data) use ($current_context)
    //     {
    //         return $sales_manager_name = isset($data->sales_manager_name)?$data->sales_manager_name:'N/A';
    //     })

    //     ->editColumn('amount',function($data) use ($current_context)
    //     {
    //       if (isset($data->amount)) {

    //         $amount =  isset($data->amount)?num_format($data->amount):0.00;
    //       }
    //       else{

    //         if ($data->sales_manager_id != 0) {

    //             $repCommission    = get_sales_manager_commission();
    //         }
    //         else{

    //             $repCommission    = $this->CommissionService->get_representative_commission();

    //         }

    //         $adminCommission  = $this->CommissionService->get_admin_commission();

    //         $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

    //         $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

    //         $amount                 = $adminCommissionAmount * ($repCommission / 100);
    //       }
    //         return num_format($amount);
    //     })

    //     ->editColumn('created_at',function($data) use ($current_context)
    //     {
    //         return us_date_format($data->order_date);
    //     })

    //    ->editColumn('rep_commission_pending',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['totalCommissionPending']);
    //     })
    //     ->editColumn('rep_commission_paid',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['totalCommissionPaid']);
    //     })
    //     ->editColumn('order_amount_pending',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['totalOrderAmountPending']);
    //     })
    //     ->editColumn('order_amount_paid',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['totalOrderAmountPaid']);
    //     })
    //     ->editColumn('admin_commission',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['adminCommissionEarned']);
    //     })
    //     ->editColumn('vendor_commission_pending',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['vendorCommissionPending']);
    //     })
    //     ->editColumn('vendor_commission_paid',function($data) use ($total_commission_amount)
    //     {
    //         return num_format($total_commission_amount['vendorCommissionPaid']);
    //     })


    //     ->editColumn('rep_name',function($data) use ($current_context,$formData)
    //     {
    //         if($data->sales_manager_id != 0)
    //         {
    //             return $data->sales_manager_name;
    //         }
    //         else
    //         {
    //             return $data->rep_name;
    //         }
    //     })

    //     ->editColumn('order_link',function($data) use ($current_context)
    //     {
    //         if ($data->order_no != "") {

    //             $arr_retailer_order_details = $this->RetailerQuotesModel
    //                                      ->where('order_no',$data->order_no)
    //                                      ->where('id',$data->order_id)
    //                                      ->first();
    //             if ($arr_retailer_order_details) 
    //             {
    //                 $href='/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
    //                 $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
    //                 return $build_view_action;
                                         
    //             } 
    //             $arr_rep_order_details = $this->RepresentativeLeadsModel
    //                                          ->where('order_no',$data->order_no)
    //                                          ->where('id',$data->order_id)
    //                                          ->first();

    //             if ($arr_rep_order_details) 
    //             {
    //                 $href='/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);
    //                 $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
    //                 return $build_view_action;                          
    //             } 
    //         }
    //     })
       
    //     ->editColumn('build_action_btn',function($data) use ($current_context,$arr_search_column,$formData)
    //     {       

    //         if(isset($arr_search_column['order_status']) && $arr_search_column['order_status']!="" && $arr_search_column['order_status'] == 2)
    //         {
    //            $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
    //         }
    //         else
    //         {
    //            $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
    //         }
            
            
    //         $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip href="'.$href.'" title="View">View</a>';

    //         return $build_view_action;
    //     })  
        
    //     ->editColumn('total_wholesale_price',function($data) use ($current_context)
    //     {
    //         return $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
    //     });
                    
                                   
    //     $build_result = $json_result->make(true)->getData();

    //     return response()->json($build_result);
    // }


    public function get_retailer_orders($formData,$arr_search_column)
    { 
        //dd("ok");
        $total_commission_amount = $this->CommissionReportService->calculate_vendor_total_commission('retailer',$formData);


        $objprefixStripeTransaction = $this->CommissionReportService->retailer_order_query($formData,$arr_search_column);


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
                                $href='/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;
                                                     
                            } 
                            $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                          ->where('order_no',$data->order_no)
                                                          ->where('id',$data->order_id)
                                                          ->first();

                            if ($arr_rep_order_details) 
                            {
                                $href='/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);
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

                        $adminCommission = isset($data->admin_commission)?num_format($data->admin_commission):0;
                        if($adminCommission==0)
                        {
                        $adminCommission  = $this->CommissionService->get_admin_commission();
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
                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      }
                        return num_format($amount);
                    })

                     ->editColumn('amount_excluding_shipping_charge',function($data) use ($current_context)
                    { 
                        
                       $adminCommission = isset($data->admin_commission)?num_format($data->admin_commission):0;
                        if($adminCommission==0)
                        {
                        $adminCommission  = $this->CommissionService->get_admin_commission();
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

                        $adminCommission = isset($data->admin_commission)?num_format($data->admin_commission):0;
                        if($adminCommission==0)
                        {
                        $adminCommission  = $this->CommissionService->get_admin_commission();
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
                        //$adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
                        $adminCommissionAmount  = ($totalPrice * $adminCommission) / 100;

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

                  
                    ->editColumn('rep_name',function($data) use ($current_context)
                    {
                      $rep_name = "-";

                      if(isset($data->representative_id))
                      {
                        $rep_name = get_user_name($data->representative_id);
                      }
                      if(isset($data->sales_manager_id) && $data->representative_id==0 && $data->sales_manager_id!=0)
                      { 
                        $rep_name = get_user_name($data->sales_manager_id);
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



                        $adminCommission = isset($data->admin_commission)?num_format($data->admin_commission):0;
                        if($adminCommission==0)
                        {
                        $adminCommission  = $this->CommissionService->get_admin_commission();
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

                        //$amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommission) / 100;

                        $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommission) / 100;
                        
                        $rep_commission_amount = ($adminCommissionAmount * $representative_commission )/ 100;

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
                                $href='/admin/retailer_orders/view/'.base64_encode($arr_retailer_order_details['id']);
                                $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                                return $build_view_action;
                                                     
                            } 
                            $arr_rep_order_details = $this->RepresentativeLeadsModel
                                                         ->where('order_no',$data->order_no)
                                                         ->where('id',$data->order_id)
                                                         ->first();

                            if ($arr_rep_order_details) 
                            {
                                $href='/admin/leads/view/'.base64_encode($arr_rep_order_details['id']);
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

                    $build_pay_vendor_btn  = $pay_rep_commission = $pay_sales_man_commission = '';
                    $sale_manager_pay_btn = $generate_invoice = '';

                    if($data->sales_manager_id != 0 || $data->representative_id != 0) 
                    {
                        /* Pay vendor commission */

                        $is_disabled = 'display:none';

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

                        if ($data->sales_manager_id != 0) 
                        {

                           $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        }
                        else
                        {
                           $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }

                    
                        $adminCommissionPercent = isset($data->admin_commission)?num_format($data->admin_commission):0;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = $this->CommissionService->get_admin_commission();
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                        $shipping_charge = isset($data->total_shipping_charges)?num_format($data->total_shipping_charges):0; 

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

                        $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommissionPercent) / 100;

                        $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                        $representative_pay_amount = ($adminCommissionAmount * $representative_commission) / 100;


                        if($data->ship_status == 1 && $data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                        {
                           $build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.$adminCommissionPercent.','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';

                        }
                        else
                        {
                            $build_pay_vendor_btn = '-';
                        }


                        /* Pay representative commission if order payment type is direct payment */
                        if($data->is_direct_payment == 1 && $data->admin_commission_status == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0 )
                        {
                            if($data->rep_commission_status == 0){


                           $pay_rep_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->order_id.')" >Pay Representative</button>';
                            }

                           if($data->sales_manager_commission_status == 0) 
                           {
                            $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->order_id.')" >Pay Sales Manager</button>';
                           }
                           
                        }
                    }

                    if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                    { 
                        //check the order whether it is rep/sales order or retailer order

                        if(isset($data->order_no) && $data->order_no!='')
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
                        }
                    
                        $generate_invoice = "<button type='button' class='btn btn-circle btn-success btn-outline show-tooltip' id='generate_inoice' onclick='generate_invoice($data->order_id , \"$order_type\")' title='Generate Invoice' >Generate Invoice</button>";
                    }
                    else
                    {
                        $generate_invoice = '--';
                    }

                    return $build_pay_vendor_btn. $pay_rep_commission . $pay_sales_man_commission. $generate_invoice;

                    })
                    
                    ->editColumn('showCheckboxes',function($data) use ($current_context)
                    {   

                        $bulk_pay_vendor_class = $bulk_rep_pay_class = $bulk_sale_manager_pay_class = $bulk_invoice_class = "";
                    
                        $sale_manager_pay_btn = $is_disabled = ''; $order_type = "";
                        $data_attr = "";

                        if($data->sales_manager_id != 0 || $data->representative_id != 0) 
                        {
                            /* Pay vendor commission */

                            $is_disabled = 'display:none';

                            if($data->maker_commission_status == 1)
                            {
                              $is_disabled = 'display:none';
                            }


                            if($data->is_direct_payment == 1 && $data->admin_commission_status == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0)
                            {

                                if($data->sales_manager_commission_status == 0 && $data->representative_id == 0) 
                                 {
                                    $bulk_sale_manager_pay_class = "checkbox_bulk_sales_manager";    
                                 }
                                 else
                                 {
                                    $chkRepCommissionStatus  = isset($data->rep_commission_status) ? $data->rep_commission_status:0;
                                    if($data->rep_commission_status == 0 && $data->representative_id != 0)
                                     {
                                        $bulk_rep_pay_class = "checkbox_bulk_representative";
                                     }
                                 }


                            }


                            if ($data->sales_manager_id != 0) 
                            {

                               $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                            }
                            else
                            {
                               $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                            }

                           
                            $adminCommissionPercent = isset($data->admin_commission)?num_format($data->admin_commission):0;

                            if($adminCommissionPercent==0)
                            {
                            $adminCommissionPercent  = $this->CommissionService->get_admin_commission();
                            }



                            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                            $shipping_charge = isset($data->total_shipping_charges)?num_format($data->total_shipping_charges):0; 

                             if(isset($data->representative_id)||isset($data->sales_manager_id))
                            {
                             $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);
                            }   

                            //$amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

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

                            $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommissionPercent) / 100;

                            $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                            $representative_pay_amount = ($adminCommissionAmount * $representative_commission) / 100;


                          



                            if($data->ship_status == 1 && $data->is_direct_payment == 1 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                            {
                               //$build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.$adminCommissionPercent.','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';


                               if($is_disabled == ''){

                                    $bulk_pay_vendor_class = "checkbox_bulk_vendor";
                                }

                            }


                           $data_attr .= 'data-amount_excluding_shipping_charge="'.$amount_excluding_shipping_charge.'"';
                            $data_attr .= ' data-representative_commission="'.$representative_commission.'"';
                            $data_attr .= ' data-order_no="'.$data->order_no.'"';
                            $data_attr .= ' data-representative_pay_amount="'.num_format($representative_pay_amount).'"';
                            $data_attr .= ' data-sales_manager="'.$data->sales_manager_id.'"';
                            $data_attr .= ' data-representative="'.$data->representative_id.'"';
                            $data_attr .= ' data-adminCommissionAmount="'.num_format($adminCommissionAmount).'"';
                            $data_attr .= ' data-maker_id="'.$data->maker_id.'"';
                            $data_attr .= ' data-totalPrice="'.$totalPrice.'"';
                            $data_attr .= ' data-adminCommissionPercent="'.$adminCommissionPercent.'"';
                            $data_attr .= ' data-vendorPaybleAmount="'.num_format($vendorPaybleAmount).'"';
                        }


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
                            $bulk_invoice_class = "checkbox_bulkInvoice_vendor";
                            $data_attr .= ' data-ordertype="'.$order_type.'"';
                           
                            $checkbox_itemVal = array();

                        } 
                       


                        if($bulk_pay_vendor_class != "" || $bulk_rep_pay_class != "" || $bulk_sale_manager_pay_class != "" || $bulk_invoice_class != "")
                        {
                            $checkbox_extra_class = $bulk_pay_vendor_class." ".$bulk_rep_pay_class." ".$bulk_sale_manager_pay_class." ".$bulk_invoice_class; 

                            $orderNo_checkbox = '<div class="checkbox checkbox-success"><input type="checkbox" id="order_numbers_'.$data->order_id.'" name="order_numbers[]" value="'. $data->order_id .'" class="checkItem case checkOrderItems '.$checkbox_extra_class .'" '.$data_attr .'"><label for="order_numbers_'.$data->order_id.'"></label></div>';

                        }
                        else
                            $orderNo_checkbox = '<div class="checkbox checkbox-success">--</div>';


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

   
    public function get_retailer_paid_orders($formData,$arr_search_column)
    {
       
        $total_commission_amount = $this->CommissionReportService->calculate_vendor_total_commission('paid',$formData);  
      
        $objprefixStripeTransaction = $this->CommissionReportService->retailer_paid_order_query($formData);

        /*if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
            {
                $search_term      = $arr_search_column['q_order_no'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.order_no','LIKE', '%'.$search_term.'%');
            }
            if(isset($arr_search_column['q_order_date']) && $arr_search_column['q_order_date']!="")
            {
                $search_term      = $arr_search_column['q_order_date'];
                $search_term  = date('Y-m-d',strtotime($search_term));
                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.created_at','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_order_amount']) && $arr_search_column['q_order_amount']!="")
            {
                $search_term      = $arr_search_column['q_order_amount'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->where($RetailerQuotesTable.'.total_wholesale_price','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_commission_amount']) && $arr_search_column['q_commission_amount']!="")
            {
                $search_term      = $arr_search_column['q_commission_amount'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->where($prefixStripeTransactionTable.'.amount','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_rep_name']) && $arr_search_column['q_rep_name']!="")
            {
                $search_term      = $arr_search_column['q_rep_name'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->having('rep_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_rep_name']) && $arr_search_column['q_rep_name']!="")
            {
                $search_term      = $arr_search_column['q_sales_name'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->having('rep_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_vendor_name']) && $arr_search_column['q_vendor_name']!="")
            {
                $search_term      = $arr_search_column['q_vendor_name'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->having('vendor_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_retaier_name']) && $arr_search_column['q_retaier_name']!="")
            {
                $search_term      = $arr_search_column['q_retaier_name'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->having('retailer_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
            {
                $search_term      = $arr_search_column['q_payment_status'];
                $objprefixStripeTransaction = $objprefixStripeTransaction->where($prefixStripeTransactionTable.'.status','LIKE', '%'.$search_term.'%');
            }


            if((isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="") && (isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!=""))
            {

                $toDate    = date('Y-m-d',strtotime($arr_search_column['q_to_date']));
                $fromDate  = date('Y-m-d',strtotime($arr_search_column['q_from_date']));

                $objprefixStripeTransaction   = $objprefixStripeTransaction->whereBetween($RetailerQuotesTable.'.created_at',[$fromDate,$toDate]);
            }
        */  

        $current_context = $this;

    

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $json_result  = $json_result->editColumn('sales_manager_name',function($data) use ($current_context)
                    {
                        return $sales_manager_name = isset($data->sales_manager_name)?$data->sales_manager_name:'N/A';
                    })

                    ->editColumn('amount',function($data) use ($current_context)
                    {
                      if (isset($data->amount)) {

                        $amount =  isset($data->amount)?num_format($data->amount):0.00;
                      }
                      else{

                        $adminCommission  = $this->CommissionService->get_admin_commission();

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

                        $amount                 = $totalPrice - $adminCommissionAmount;
                      }
                        return num_format($amount);
                    })
                    ->editColumn('rep_name',function($data) use ($current_context)
                    {
                      
                        return '-';
                    })
                    ->editColumn('created_at',function($data) use ($current_context)
                    {
                        return us_date_format($data->order_date);
                    })
                    ->editColumn('total_commission',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalCommission']);
                    })

                    ->editColumn('total_amount',function($data) use ($total_commission_amount)
                    {
                        return num_format($total_commission_amount['totalOrderAmount']);
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

                    ->editColumn('build_action_btn',function($data) use ($current_context,$arr_search_column)
                    {       

                        if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="" && $arr_search_column['q_payment_status'] == 2)
                        {
                           $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode($data->maker_id);
                        }
                        else
                        {
                            // dd($data->lead_id);
                          /* $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode($data->maker_id);*/

                           $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->lead_id).'/'.base64_encode('quotes');
                        }
                        
                        
                        $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$href.'" title="View">View</a>';

                        return $build_view_action;
                    }) 
                    
                    ->editColumn('total_wholesale_price',function($data) use ($current_context)
                    {
                        return $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
                    });
            
                           
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
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
           // ->withCount(['user_details as username' => function($query) {
           //      $query->select(DB::raw('first_name'));
           //  }])
           // ->orderBy('username','ASC')
          ->orderBy('company_name','ASC')
          ->get();
          //dd($objUserData);
          
          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            
                $userData = $usersDetailes;
            
          }
        }

        elseif ($role=='representative') {

          $objUserData = $this->RepresentativeModel->with('get_user_details')->whereHas('get_user_details',function ($query)
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

          $objUserData = $this->SalesManagerModel->with('get_user_data')->whereHas('get_user_data',function ($query)
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

          $objUserData = $this->RetailerModel->with('user_details')->whereHas('user_details',function ($query)
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

    public function get_order_data($id,$orderType=false)
    {
        $id = isset($id)?base64_decode($id):false;

        $orderType = isset($orderType)?base64_decode($orderType):false;
        
        $arrResponse = [];

        if($id)
        {
            
            $adminCommission = $this->CommissionService->get_admin_commission();

            if($orderType == 'lead')
            {
                //retailer
                $orderDetails = $this->leadsDetails($id,$orderType);
           

                $arr_view_data['transaction_details'] = $orderDetails;

            }
            elseif($orderType == 'quotes')
            {
                //Reps
                $orderDetails = $this->quotesDetails($id,$orderType);

                $arr_view_data['transaction_details'] = $orderDetails;

            }
    
            /*else
            {

                $objTransactionData = $this->StripeTransactionModel->where('id',$id)
                                                                   ->first();

                if($objTransactionData)
                {
                    $transactionData = $objTransactionData->toArray();

                    if($transactionData['lead_id'] != 0)
                    {  
                        $orderDetails = $this->quotesDetails($transactionData['lead_id'],$orderType);
                    }
                    else
                    {   
                    
                        $orderDetails =  $this->leadsDetails($transactionData['quote_id'],$orderType);         
                    }
      
               
                    if($transactionData['status'] == '2')
                    {
                        $status = 'Paid';
                    }
                    if($transactionData['status'] == '3')
                    {
                        $status = 'Failed';
                    }
                    if($transactionData['status'] == '1')
                    {
                        $status = 'Pending';
                    }

                    $arr_view_data['transaction_details']['transaction_id']     = $transactionData['transfer_id'];
                    $arr_view_data['transaction_details']['transaction_status'] = $status;
                    $arr_view_data['transaction_details'] = $orderDetails;
                    $arr_view_data['transaction_details']['status'] = $transactionData['status'];
                    // dd($arr_view_data,$orderDetails);
                }
            }*/
        }
        else
        {
            // dd('Invalid request');
        }

        $this->arr_view_data['page_title']      = "Commission Reports Details";
        $this->arr_view_data['module_title']    = $this->module_title;;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['transaction_details'] = isset($arr_view_data['transaction_details'])?$arr_view_data['transaction_details']:'';

        return view($this->module_view_folder.'.report_details', $this->arr_view_data);
    }

   public function leadsDetails($id,$orderType=false)
   {    
        $orderType=false;
    
         $adminCommission = $this->CommissionService->get_admin_commission();
         $arrResponse = [];

        /* if ($orderType == 'lead') {
            $objOrderData = $this->RepresentativeLeadsModel->with(['retailer_user_details',
                                                                'maker_details',
                                                                'sales_manager_details',
                                                                'representative_user_details','stripe_transaction_detail'
                                                            ])
                                                        ->where('id',$id)
                                                        ->first();
         }
         else{
            $objOrderData = $this->RepresentativeLeadsModel->with(['retailer_user_details',
                                                                'maker_details',
                                                                'sales_manager_details',
                                                                'representative_user_details','stripe_transaction_detail'
                                                            ])->whereHas('stripe_transaction_detail',function($q) use($orderType){
                                                                 $q->where('received_by',$orderType);

                                                            })
                                                        ->where('id',$id)
                                                        ->first();
         }*/

         
          $objOrderData = $this->RepresentativeLeadsModel->with(['retailer_user_details',
                                                                'maker_details',
                                                                'sales_manager_details',
                                                                'representative_user_details','stripe_transaction_detail'
                                                            ])
                                                        ->where('id',$id)
                                                        ->first();
         if($objOrderData)
         {
           $orderData = $objOrderData->toArray();
           // dd($orderData);
           $companyName = isset($orderData['maker_details']['company_name'])?$orderData['maker_details']['company_name']:'N/A';              

           $retailer_first_name = isset($orderData['retailer_user_details']['first_name'])?$orderData['retailer_user_details']['first_name']:'N/A';

           $retailer_last_name = isset($orderData['retailer_user_details']['last_name'])?$orderData['retailer_user_details']['last_name']:'';

           $rep_first_name = isset($orderData['representative_user_details']['first_name'])?$orderData['representative_user_details']['first_name']:'';

           $rep_last_name = isset($orderData['representative_user_details']['last_name'])?$orderData['representative_user_details']['last_name']:'';

           $sales_man_first_name = isset($orderData['sales_manager_details']['first_name'])?$orderData['sales_manager_details']['first_name']:'';

           $sales_man_last_name = isset($orderData['sales_manager_details']['last_name'])?$orderData['sales_manager_details']['last_name']:'';


           if ($orderData['sales_manager_id'] != 0) {

             $commission = $this->CommissionService->get_sales_manager_commission();
           }
           else
           {
             $commission = $this->CommissionService->get_representative_commission();
           }

           $adminCommissionAmount = $orderData['total_wholesale_price']*((float)$adminCommission / 100);

           $AdminPayAmount = $orderData['total_wholesale_price'] - $adminCommissionAmount;

            $repCommissionAmount = $adminCommissionAmount *((float)$commission / 100);

           $arrResponse['order_no']           = $orderData['order_no'];
           $arrResponse['commission_amount']  = $repCommissionAmount;
           $arrResponse['order_amount']       = $orderData['total_wholesale_price'];
           $arrResponse['vendor_name']        = $companyName;
           $arrResponse['retailer_name']      = $retailer_first_name.' '.$retailer_last_name;
           $arrResponse['rep_name']           = $rep_first_name.' '.$rep_last_name;
           $arrResponse['sales_man_name']     = $sales_man_first_name.' '.$sales_man_last_name;
           $arrResponse['status'] = isset($orderData['stripe_transaction_detail'])?$orderData['stripe_transaction_detail']['status']:'';
           $arrResponse['transfer_id'] = isset($orderData['stripe_transaction_detail']['transfer_id'])?$orderData['stripe_transaction_detail']['transfer_id']:'N/A';
         }

         return $arrResponse;
   }

   public function quotesDetails($id,$orderType=false)
   {    
         $arrResponse     = [];  
         $adminCommission = 0;
         $retailer_store_name = "";

         if($orderType == 'retailer')
         {
             $objOrderData = $this->RetailerQuotesModel->where('id',$id)
                                                       ->with(['user_details','maker_data','stripe_transaction_detail','user_details.retailer_details'])
                                                       ->first();
         }

         if($orderType == 'customer')
         {
             $objOrderData = $this->CustomerQuotesModel->where('id',$id)
                                                       ->with(['user_details','maker_data','stripe_transaction_detail'])
                                                       ->first();
         }

         if($orderType == 'rep-sales')
         {
             $objOrderData = $this->RepresentativeLeadsModel->with(['user_details','maker_details','stripe_transaction_detail'])
                                                       ->where('id',$id)
                                                       ->first();
         }

           
         if($objOrderData)
         {
           $orderData = $objOrderData->toArray();
           // /echo "<pre>";print_r($orderData);die;
          
           $adminCommission = $this->CommissionService->get_admin_commission(isset($orderData['maker_id'])?$orderData['maker_id']:false);

           if($orderType == 'retailer' || $orderType == 'customer')
           {
              $companyName = isset($orderData['maker_data']['company_name'])?$orderData['maker_data']['company_name']:'N/A';
           }

           if($orderType == 'rep-sales')
           {
              $companyName = isset($orderData['maker_details']['company_name'])?$orderData['maker_details']['company_name']:'N/A';
           }           

           $retailer_first_name = isset($orderData['user_details']['first_name'])?$orderData['user_details']['first_name']:'N/A';

           $retailer_last_name = isset($orderData['user_details']['last_name'])?$orderData['user_details']['last_name']:'';

           $retailer_store_name = isset($orderData['user_details']['retailer_details']['store_name'])?$orderData['user_details']['retailer_details']['store_name']:'';

           /* Get order shipping charges */

           if($orderType == 'retailer')
           {
              $shippingCharges = $this->get_retailer_order_shipping_charges($orderData['id']);
           }

           if($orderType == 'customer')
           {
              $shippingCharges = $this->get_customer_order_shipping_charges($orderData['id']);
           }

           if($orderType == 'rep-sales')
           {
              $shippingCharges = isset($orderData['total_shipping_charges'])?num_format($orderData['total_shipping_charges']):0;
           }

           /* excluding shipping charges calculation */

           $is_freeshipping = is_promocode_freeshipping($orderData['promo_code']);

           if($is_freeshipping == false)
           {
             $orderData['total_wholesale_price'] = $orderData['total_wholesale_price'] - $shippingCharges;

             if($orderType == 'customer')
             {
                $orderData['total_retail_price'] = $orderData['total_retail_price'] - $shippingCharges;
             }
           }
           
           if($orderType == 'customer')
           {
              $arrResponse['order_amount']= isset($orderData['total_retail_price'])?num_format($orderData['total_retail_price']):0;
           }
           else
           {
                $arrResponse['order_amount']       = $orderData['total_wholesale_price'];
           }
           
           $adminCommissionAmount = $arrResponse['order_amount']*((float)$adminCommission / 100);

           $vendorAmount = $arrResponse['order_amount'] - $adminCommissionAmount;

           $arrResponse['order_id']           = $orderData['id'];           
           $arrResponse['order_no']           = $orderData['order_no'];
           $arrResponse['retail_store_name']  = $retailer_store_name;
           $arrResponse['commission_amount']  = $vendorAmount;


           $arrResponse['vendor_name']        = $companyName;
           $arrResponse['order_date']         = isset($orderData['created_at'])?$orderData['created_at']:'N/A';
           $arrResponse['vendor_id']          = isset($orderData['maker_id'])?$orderData['maker_id']:'0';  
           $arrResponse['retailer_name']      = $retailer_first_name.' '.$retailer_last_name;
           $arrResponse['status'] = isset($orderData['stripe_transaction_detail']['status'])?$orderData['stripe_transaction_detail']['status']:'';
           
           $arrResponse['transfer_id']        = isset($orderData['stripe_transaction_detail']['transfer_id'])?$orderData['stripe_transaction_detail']['transfer_id']:'N/A';

         }
         
         return $arrResponse;

   }

   public function allQuoteDetails($arrId = [])
   {    
         $arrResponse = [];  

         $adminCommission = $this->CommissionService->get_admin_commission();

         $arrOrderData = $this->RetailerQuotesModel->with(['user_details','maker_data','stripe_transaction_detail'])
                                                   ->whereIn('id',$arrId)
                                                   ->get()
                                                   ->toArray();      
        $arrBuiltDataById = [];   

         if(sizeof($arrOrderData) > 0)
         {
            foreach($arrOrderData as $orderData)
            {
                $companyName = isset($orderData['maker_data']['company_name'])?$orderData['maker_data']['company_name']:'N/A';

               $retailer_first_name = isset($orderData['user_details']['first_name'])?$orderData['user_details']['first_name']:'N/A';

               $retailer_last_name = isset($orderData['user_details']['last_name'])?$orderData['user_details']['last_name']:'';
               
               $adminCommissionAmount = $orderData['total_wholesale_price']*((float)$adminCommission / 100);

               $vendorAmount = $orderData['total_wholesale_price'] - $adminCommissionAmount;

               $arrBuiltDataById[$orderData['id']]['order_id']           = $orderData['id'];           
               $arrBuiltDataById[$orderData['id']]['order_no']           = $orderData['order_no'];
               $arrBuiltDataById[$orderData['id']]['commission_amount']  = $vendorAmount;
               $arrBuiltDataById[$orderData['id']]['order_amount']       = $orderData['total_wholesale_price'];
               $arrBuiltDataById[$orderData['id']]['vendor_name']        = $companyName;
               $arrBuiltDataById[$orderData['id']]['retailer_name']      = $retailer_first_name.' '.$retailer_last_name;
               $arrBuiltDataById[$orderData['id']]['status'] = isset($orderData['stripe_transaction_detail']['status'])?$orderData['stripe_transaction_detail']['status']:'';
               $arrBuiltDataById[$orderData['id']]['transfer_id']        = isset($orderData['stripe_transaction_detail']['transfer_id'])?$orderData['stripe_transaction_detail']['transfer_id']:'N/A';
            }
        
           

        }
         
         return $arrBuiltDataById;

   }


    public function vendor_commission_report_generator(Request $request)
    {  
        $type  = 'csv'; 

        $q_order_no          = $request->get('q_order_no');
        $q_from_date         = $request->get('from_date');
        $q_to_date           = $request->get('to_date');
        $q_status            = $request->get('vendor_payment_status');
        $q_ordered_by        = $request->get('ordered_by');
        $q_vendor_name       = $request->get('vendor');
        
        $arr_search_column['q_order_no']    = isset($order_no)?$order_no:null;
        $arr_search_column['q_from_date']   = isset($from_date)?$from_date:null;
        $arr_search_column['q_to_date']     = isset($to_date)?$to_date:null;
        $arr_search_column['q_status']      = isset($q_status)?$q_status:null;
        $arr_search_column['q_ordered_by']  = isset($q_ordered_by)?$q_ordered_by:null;
        $arr_search_column['q_vendor_name'] = isset($q_vendor_name)?$q_vendor_name:null;


        $objTransactionData = $this->CommissionReportService->vendor_order_query($arr_search_column);
        $stripe_transaction_tbl = $this->StripeTransactionModel->getTable();
        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();
      
       
        if(isset($objTransactionData))
        {
            $objTransactionData = $objTransactionData->get()->toArray();
        }
        $objprefixStripeTransaction = $this->CommissionReportService->all_rep_order_query();

        $objprefixStripeTransaction = $objprefixStripeTransaction->get()->toArray();


        $data = $arrayResponseData = [];
        $adminCommission  = $this->CommissionService->get_admin_commission();
        $i =1;
        $role="N/A";
        
        foreach($objTransactionData as $key => $value)
        {   
          if($value->status == null)
          {
            $status = 'Pending';
          }
          else if($value->status == '2')
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

        
                

        $totalPrice       = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):0.00;
        
        $totalPrice = $totalPrice - $shipping_charge;

        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);
        $adminCommissionAmount = num_format($adminCommissionAmount);               
                                
          

          $array = (array)$value;
          $arrayResponseData['Sr No']          = $i;
          $arrayResponseData['Order Date']     = us_date_format($value->order_date);
          $arrayResponseData['Order No']       = $value->order_no;
          $arrayResponseData['Ordered By']     = $role;
          $arrayResponseData['Vendor Name']    = $value->vendor_name;
          
          $arrayResponseData['Total Order Amount']            = '$'.num_format($value->total_wholesale_price);
          $arrayResponseData['Amount Paid To Vendor']   = '$'.$amount;
          
          $arrayResponseData['Admin Commission Amount'] = '$'.$adminCommissionAmount;
          $arrayResponseData['Status'] = $status;

          array_push($data,$arrayResponseData); 
          $i++;

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


public function report_generator(Request $request)
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
    $filterData['repPaymentStatus'] = false;
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

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):0.00;
        $amount = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):0.00;

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
        
        $admin_commission = isset($adminCommissionAmount)?num_format($adminCommissionAmount):0.00;
    
        $rep_commission_amount = isset($rep_commission_amount)?num_format($rep_commission_amount):0.00;

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
        $arrayResponseData['Rep/Sales Commission
                Amount'] = $rep_commission_amount;
        $arrayResponseData['Rep/Sales Commission'] = $rep_sales_commission_status;
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


    public function vendor_commission_reports(Request $request)
    {
        
        $objTransactionData = $this->StripeTransactionModel->where('paid_by',1 &&  'user_role',2);
        $stripe_transaction_tbl = $this->StripeTransactionModel->getTable();
        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();

        $arr_search_column = $request->input('column_filter');
        $objTransactionData = $this->CommissionReportService->vendor_order_query($arr_search_column);

   
        $current_context = $this;

        $objTransactionData = $objTransactionData->get();
        $json_result  = Datatables::of($objTransactionData);
        
        $arrTransactionData = $objTransactionData->toArray();
        $arrLeadId = array_column($arrTransactionData,'lead_id');

        $arrLeadMapById = $this->allQuoteDetails($arrLeadId);
        $adminCommission  = $this->CommissionService->get_admin_commission();
        
        $vendor_payable_amount = [];
        $admin_commission_amt =[];
        $total_commission_pending = [];

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
          $sum_admin_commission_amt  = array_sum($admin_commission_amt);
          $sum_total_commission_pending = array_sum($total_commission_pending);
        
       //dd($sum_vendor_payable_amount,$sum_admin_commission_amt,$sum_total_commission_pending);

        /* Modifying Columns */
        $json_result =  $json_result->editColumn('lead_id',function($data) use ($current_context,$arrLeadMapById)
                        {   
                            if($data->order_id=='null')
                            {
                                return 'N/A';
                            }
                            
                            if($data->role_id==4)
                            {
                             $href=url('/').'/admin/retailer_orders/view/'.base64_encode($data->order_id);
                            }
                            if($data->role_id==6)
                            {
                             $href=url('/').'/admin/customer_orders/view/'.base64_encode($data->order_id);
                            }

                            $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                            return $build_view_action;
                        })

                        ->editColumn('ordered_by',function($data) use ($current_context)
                          {  
                            $ordered_by = "N/A";
                            if($data->role_id==4)
                            {
                                $ordered_by = "Retailer";
                            }
                            if($data->role_id ==6)
                            {
                                $ordered_by = "Customer";
                            }

                            return  $ordered_by;
                          })

                        ->editColumn('received_by',function($data) use ($current_context)
                          {  
                            return  $received_by = isset($data->vendor_name) && $data->vendor_name!=''? $data->vendor_name:'N/A';
                          })


                         ->editColumn('order_amount',function($data) use ($current_context,$arrLeadMapById)
                          { 
                      
                            $order_amount = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'N/A';
                            return  $order_amount;  
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

                            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

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
                           

                            })

                         ->editColumn('status',function($data) use ($current_context)
                         {
                            //dd($data->status);
                            if($data->status==null)
                            {
                                $status = 1;
                                return  $status;
                            }
                            
                            return  $status = isset($data->status) && $data->status!=''?$data->status:'N/A';
                         })

                         ->editColumn('created_at',function($data) use ($current_context)
                         {   //dd($data);
                            
                            return  us_date_format($data->order_date);

                         })
                         ->editColumn('sum_vendor_payable_amount',function($data) use ($current_context,$sum_vendor_payable_amount)
                         {
                            return $sum_vendor_payable_amount;
                         })
                         ->editColumn('sum_admin_commission_amt',function($data) use ($current_context,$sum_admin_commission_amt)
                         {
                            return $sum_admin_commission_amt;
                         })
                         ->editColumn('sum_total_commission_pending',function($data) use ($current_context,$sum_total_commission_pending)
                         {
                            return $sum_total_commission_pending;
                         })
                        ->make(true);


        $build_result = $json_result->getData();
        return response()->json($build_result);

       
    }


    public function admin_commission_reports(Request $request)
    {
        $type = 1;
        $objTransactionData = $this->StripeTransactionModel->where('paid_by',1 &&  'user_role',2);
        $stripe_transaction_tbl = $this->StripeTransactionModel->getTable();
        $RetailerQuotesTable =  $this->RetailerQuotesModel->getTable();

        $arr_search_column = $request->input('column_filter');
        $objTransactionData = $this->CommissionReportService->vendor_order_query($arr_search_column,$type);
       
        $current_context = $this;

        $objTransactionData = $objTransactionData->get();
        $json_result  = Datatables::of($objTransactionData);
        
        $arrTransactionData = $objTransactionData->toArray();
       
        $arrLeadId = array_column($arrTransactionData,'lead_id');

        $arrLeadMapById = $this->allQuoteDetails($arrLeadId);
        $adminCommission  = $this->CommissionService->get_admin_commission();
        
        $vendor_payable_amount = [];
        $admin_commission_amt =[];
        $total_commission_pending = [];

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
          $sum_admin_commission_amt  = array_sum($admin_commission_amt);
          $sum_total_commission_pending = array_sum($total_commission_pending);
        
       //dd($sum_vendor_payable_amount,$sum_admin_commission_amt,$sum_total_commission_pending);

        /* Modifying Columns */
        $json_result =  $json_result->editColumn('lead_id',function($data) use ($current_context,$arrLeadMapById)
                        {   
                            if($data->order_id=='null')
                            {
                                return 'N/A';
                            }
                            
                            if($data->role_id==4)
                            {
                             $href=url('/').'/admin/retailer_orders/view/'.base64_encode($data->order_id);
                            }
                            if($data->role_id==6)
                            {
                             $href=url('/').'/admin/customer_orders/view/'.base64_encode($data->order_id);
                            }

                            $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                            return $build_view_action;

                        })

                       ->editColumn('ordered_by',function($data) use ($current_context)
                          {  
                            $ordered_by = "N/A";
                            if($data->role_id==4)
                            {
                                $ordered_by = "Retailer";
                            }
                            if($data->role_id ==6)
                            {
                                $ordered_by = "Customer";
                            }

                            return  $ordered_by;
                          })


                        ->editColumn('received_by',function($data) use ($current_context)
                          {  
                            return  $received_by = isset($data->vendor_name) && $data->vendor_name!=''? $data->vendor_name:'N/A';
                          })


                         ->editColumn('order_amount',function($data) use ($current_context,$arrLeadMapById)
                          { 
                            $order_amount = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'N/A';
                            return  $order_amount;  
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
                                
                            
                               
                                // return  $amount = isset($data->amount) && $data->amount!=''? num_format($data->amount-$shipping_charge):'N/A';

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
                       

                        })

                         ->editColumn('status',function($data) use ($current_context)
                         {
                            //dd($data->status);
                            if($data->status==null)
                            {
                                $status = 1;
                                return  $status;
                            }
                            
                            return  $status = isset($data->status) && $data->status!=''?$data->status:'N/A';
                         })

                         ->editColumn('created_at',function($data) use ($current_context)
                         {   //dd($data);
                            
                            return  us_date_format($data->order_date);

                         })
                         ->editColumn('sum_vendor_payable_amount',function($data) use ($current_context,$sum_vendor_payable_amount)
                         {
                            return $sum_vendor_payable_amount;
                         })
                         ->editColumn('sum_admin_commission_amt',function($data) use ($current_context,$sum_admin_commission_amt)
                         {
                            return $sum_admin_commission_amt;
                         })
                         ->editColumn('sum_total_commission_pending',function($data) use ($current_context,$sum_total_commission_pending)
                         {
                            return $sum_total_commission_pending;
                         })
                        
                        // 

                        ->make(true);


        $build_result = $json_result->getData();
        return response()->json($build_result);

       
    }



    public function admin_commission_invoice_generator(Request $request, $order_id = false,$orderType = false)
    { 
       //dd($request);

        if($order_id!=false)
        {   
            
            $order_id = base64_decode($order_id);


            $quotes_details = $this->quotesDetails($order_id,$orderType);
            //dd($quotes_details);
            $is_success = $this->CommissionReportService->commission_invoice_generator($quotes_details,'',$order_id);
            if($is_success=="true")
            {  
                $response['status']      ="success";
                $response['description'] = "Invoice sent successfully to vendor!";
            }
            else
            {
                $response['status']      ="failure";
                $response['description'] = "An Error has occurred while sending invoice,please try again";
            }
        }
        else
            {
                $response['status']      ="failure";
                $response['description'] = "An Error has occurred while sending invoice,please try again";
            }

        return $response;
    }
/* -----------------Start: Generate Bulk invoices -------------------- */
    public function admin_commission_invoice_generator_bulk(Request $request)
    { 
       //dd($request);
       $post = $request->input(); 
        
       
       $orders = array();
       foreach($post['checkOrderItems'] as $row)
       {
            $orders[$row['orderType']][]  = $row['orderNumber'];
       }
        
        $quotes_detailsArr = $this->get_bulkQuotesDetails($orders);
        //  echo "<pre> quotes_details ===> "; print_r($quotes_details); exit;     
        $is_success = $this->CommissionReportService->commission_bulk_invoice_generator($quotes_detailsArr);
            

      
         if($is_success==true)
            {
                $response['status']      ="success";
                $response['description'] = "Invoice sent successfully to vendor!";
            }
        else
        {
            $response['status']      ="failure";
            $response['description'] = "An Error has occurred while sending invoice,please try again";
        }

        return $response;
    }

    public function get_bulkQuotesDetails($orders)
    {    
         $arrResponse     = [];  
         $adminCommission = 0;
 

        foreach ($orders as $orderType => $orderIds) {
            switch($orderType)
            {
                case "retailer": $objOrderData['retailer'] = 
                                                        $this->RetailerQuotesModel->whereIn('id',$orderIds)
                                                       ->with(['user_details','maker_data','stripe_transaction_detail'])
                                                       ->get();

                                                     $orderData['retailer'] = $objOrderData['retailer']->toArray();   
                                                break;       
                case "customer": $objOrderData['customer'] = 
                                                    $this->CustomerQuotesModel->whereIn('id',$orderIds)
                                                       ->with(['user_details','maker_data','stripe_transaction_detail'])
                                                       ->get();

                                                     $orderData['customer'] = $objOrderData['customer']->toArray();   
                                                break;       
                case "rep-sales": $objOrderData['rep-sales'] = 
                                                    $this->RepresentativeLeadsModel->whereIn('id',$orderIds)
                                                       ->with(['user_details','maker_details','stripe_transaction_detail'])
                                                       ->get();
                                                       $orderData['rep-sales'] = $objOrderData['rep-sales']->toArray();
                                                break;       
            }

        }
         
        //echo "input ===> "; print_r($orderData); exit;   
    foreach ($orderData as $orderType => $ordersArr) {
        foreach ($ordersArr as $orderRow) {

          // echo "<pre> orderRow ===> "; print_r($orderRow); exit;   
           $order_no = $orderRow['order_no'];
            $adminCommission = $this->CommissionService->get_admin_commission(isset($orderRow['maker_id'])?$orderRow['maker_id']:false);

           if($orderType == 'retailer' || $orderType == 'customer')
           {
              $companyName = isset($orderRow['maker_data']['company_name'])?$orderRow['maker_data']['company_name']:'N/A';
           }

           if($orderType == 'rep-sales')
           {
              $companyName = isset($orderRow['maker_details']['company_name'])?$orderRow['maker_details']['company_name']:'N/A';
           }           

           $retailer_first_name = isset($orderRow['user_details']['first_name'])?$orderRow['user_details']['first_name']:'N/A';

           $retailer_last_name = isset($orderRow['user_details']['last_name'])?$orderRow['user_details']['last_name']:'';

           /* Get order shipping charges */

           if($orderType == 'retailer')
           {
              $shippingCharges = $this->get_retailer_order_shipping_charges($orderRow['id']);
           }

           if($orderType == 'customer')
           {
              $shippingCharges = $this->get_customer_order_shipping_charges($orderRow['id']);
           }

           if($orderType == 'rep-sales')
           {
              $shippingCharges = isset($orderRow['total_shipping_charges'])?num_format($orderRow['total_shipping_charges']):0;
           }

           /* excluding shipping charges calculation */

           $is_freeshipping = is_promocode_freeshipping($orderRow['promo_code']);

           if($is_freeshipping == false)
           {
             $orderRow['total_wholesale_price'] = $orderRow['total_wholesale_price'] - $shippingCharges;

             if($orderType == 'customer')
             {
                $orderRow['total_retail_price'] = $orderRow['total_retail_price'] - $shippingCharges;
             }
           }
           
           if($orderType == 'customer')
           {
              $arrResponse['order_amount']= isset($orderRow['total_retail_price'])?num_format($orderRow['total_retail_price']):0;
           }
           else
           {
                $arrResponse['order_amount']       = $orderRow['total_wholesale_price'];
           }
           
           $adminCommissionAmount = $arrResponse['order_amount']*((float)$adminCommission / 100);

           $vendorAmount = $arrResponse['order_amount'] - $adminCommissionAmount;

           $arrResponse['order_id']           = $orderRow['id'];           
           $arrResponse['order_no']           = $orderRow['order_no'];
           $arrResponse['commission_amount']  = $vendorAmount;


           $arrResponse['vendor_name']        = $companyName;
           $arrResponse['order_date']         = isset($orderRow['created_at'])?$orderRow['created_at']:'N/A';
           $arrResponse['vendor_id']          = isset($orderRow['maker_id'])?$orderRow['maker_id']:'0';  
           $arrResponse['retailer_name']      = $retailer_first_name.' '.$retailer_last_name;
           $arrResponse['status'] = isset($orderRow['stripe_transaction_detail']['status'])?$orderRow['stripe_transaction_detail']['status']:'';
           
           $arrResponse['transfer_id']        = isset($orderRow['stripe_transaction_detail']['transfer_id'])?$orderRow['stripe_transaction_detail']['transfer_id']:'N/A';

           $resultant_arrResponse[$order_no] = $arrResponse;
        }
    }
         
       
         
         return $resultant_arrResponse;

    }
/* -----------------End: Generate Bulk invoices -------------------- */
    public function show_vendor_commission()
    {       
      $arrMakers          = $this->get_user('maker');
      $arrRepresentative  = $this->get_user('representative');
      $arrRetailer        = $this->get_user('retailer');
      $arrSalesManger     = $this->get_user('sales_manager');

      $this->arr_view_data['arr_data']            = array();
      $this->arr_view_data['module_url_path']     = url(config('app.project.admin_panel_slug')."/vendor_commission_reports"); 
        
      $this->arr_view_data['exprot_csv_url']      = url(config('app.project.admin_panel_slug')."/direct_payment_to_vendor")."/vendor_commission_report_generator"; 

      $this->arr_view_data['generate_invoice_url'] = url(config('app.project.admin_panel_slug')."/direct_payment_to_vendor")."/vendor_commission_invoice_generator"; 
      $this->arr_view_data['module_title']         = "Vendor Payments";
      $this->module_title                          = "Vendor Payments";
      $this->arr_view_data['page_title']           = $this->module_title;
      $this->arr_view_data['arr_data']             = isset($arr_data) ? $arr_data: [];
      $this->arr_view_data['arrMakers']            = isset($arrMakers) ? $arrMakers: [];
      $this->arr_view_data['arrRetailer']          = isset($arrRetailer) ? $arrRetailer: [];
      $this->arr_view_data['arrSalesManager']      = isset($arrSalesManger) ? $arrSalesManger: [];
      $this->arr_view_data['arrRepresentative']    = isset($arrRepresentative) ? $arrRepresentative: [];
      return view($this->module_view_folder.'.vendor_commission', $this->arr_view_data);
    }

    public function show_admin_commission()
    {      
      $arrMakers          = $this->get_user('maker');
      $arrRepresentative  = $this->get_user('representative');
      $arrRetailer        = $this->get_user('retailer');
      $arrSalesManger     = $this->get_user('sales_manager');

      $this->arr_view_data['arr_data']             = array();
      $this->arr_view_data['module_url_path']      = url(config('app.project.admin_panel_slug')."/admin_commission_reports"); 
      $this->arr_view_data['exprot_csv_url']       = url(config('app.project.admin_panel_slug')."/admin_commission_reports")."/vendor_commission_report_generator"; 
      $this->arr_view_data['generate_invoice_url'] = url(config('app.project.admin_panel_slug')."/admin_commission_reports")."/admin_commission_invoice_generator"; 
      $this->arr_view_data['module_title']         = "Admin Commission";
      $this->module_title                          = "Admin Commission";
      $this->arr_view_data['page_title']           = $this->module_title;
      $this->arr_view_data['arr_data']             = isset($arr_data) ? $arr_data: [];
      $this->arr_view_data['arrMakers']            = isset($arrMakers) ? $arrMakers: [];
      $this->arr_view_data['arrRetailer']          = isset($arrRetailer) ? $arrRetailer: [];
      $this->arr_view_data['arrSalesManager']      = isset($arrSalesManger) ? $arrSalesManger: [];
      $this->arr_view_data['arrRepresentative']    = isset($arrRepresentative) ? $arrRepresentative: [];
       
      return view($this->module_view_folder.'.admin_commission', $this->arr_view_data);
    }

    public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

    public function get_customer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }


    // Function added by Harshada.k on date 15 Oct 2020   
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
