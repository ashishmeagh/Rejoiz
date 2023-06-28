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
use App\Models\RoleUsersModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RepresentativeModel;
use App\Models\SalesManagerModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\CustomerModel;
use App\Models\CustomerQuotesProductModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use DB;
use Datatables;
use Excel, Validator;


class CustomerCommissionReportsController extends Controller
{
    public function __construct(StripeTransactionModel $StripeTransactionModel,
                                CommissionReportService $CommissionReportService,
                                CommissionService $CommissionService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                RetailerModel $RetailerModel,
                                RoleUsersModel $RoleUsersModel,
                                RepresentativeLeadsModel $RepresentativeLeadsModel,
                                UserModel $UserModel,
                                RepresentativeModel $RepresentativeModel,
                                SalesManagerModel $SalesManagerModel,
                                MakerModel $MakerModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                                CustomerModel $CustomerModel,
                                CustomerQuotesProductModel $CustomerQuotesProductModel
                                )
    {
        $this->arr_view_data      = [];
        $this->module_title       = "Customer Commission Reports";
        $this->module_view_folder = "admin.commission_reports";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/customer_commission_reports");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
        $this->role                       = 'retailer';
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->MakerModel                 = $MakerModel;
        $this->RetailerModel              = $RetailerModel;
        $this->CommissionReportService    = $CommissionReportService;
        $this->RetailerQuotesModel        = $RetailerQuotesModel;
        $this->RepresentativeLeadsModel   = $RepresentativeLeadsModel;
        $this->RoleUsersModel             = $RoleUsersModel;
        $this->RepresentativeModel        = $RepresentativeModel;
        $this->SalesManagerModel          = $SalesManagerModel;
        $this->UserModel                  = $UserModel;
        $this->RetailerQuotesProductModel = $RetailerQuotesProductModel;
        $this->RepresentativeProductLeadsModel = $RepresentativeProductLeadsModel;
        $this->CustomerModel                   = $CustomerModel;
        $this->CustomerQuotesProductModel      = $CustomerQuotesProductModel;
        $this->CommissionService          = $CommissionService; 
    }


    public function direct_payment()
    {   
        
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrCustomer        = $this->get_user('customer');
        //dd($arrCustomer);
       
        $this->arr_view_data['page_title']          = "Direct Payment Reports";
        $this->arr_view_data['module_title']        = "Direct Payment Reports";
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrCustomer']         = isset($arrCustomer) ? $arrCustomer: [];
     
        return view($this->module_view_folder.'.direct_payment_customer_report', $this->arr_view_data);
    }

    public function payment_intermediation()
    {
        //dd("it's working");
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrCustomer        = $this->get_user('customer');
        
        $arr_site_setting  = get_site_settings(['site_name','website_url']);

        $this->arr_view_data['page_title']          = "Payment Intermediation (Through ".$arr_site_setting['site_name'].")";
        $this->arr_view_data['module_title']        = "Payment Intermediation (Through ".$arr_site_setting['site_name'].")";
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrCustomer']         = isset($arrCustomer) ? $arrCustomer: [];
     
        return view($this->module_view_folder.'.payment_intermediation_customer_report', $this->arr_view_data);
    }

    public function get_commission_reports(Request $request,$role=false)
    {
       
        $formData = [];

        $arr_search_column = $response = [];
        $arr_search_column = $request->input('column_filter');

        $formData['toDate']           = isset($arr_search_column['to_date'])?$arr_search_column['to_date']:false;
        $formData['fromDate']         = isset($arr_search_column['from_date'])?$arr_search_column['from_date']:false;
        $formData['commissionStatus'] = isset($arr_search_column['order_status'])?$arr_search_column['order_status']:false;
        $formData['makerId']          = isset($arr_search_column['vendor'])?$arr_search_column['vendor']:false;
        $formData['retailerId']       = isset($arr_search_column['retailer'])?$arr_search_column['retailer']:false;

        /* kadoe commission status */
        $formData['orderStatus']              = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        
        /* vendor commission status */
        $formData['vendorPaymentStatus']      = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;

        $formData['is_direct_payment'] = 0;



        // dd($arr_search_column); 
       

        $objprefixStripeTransaction = $this->get_customer_orders($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;

                     
    }

    public function get_indirect_payment_commission_reports(Request $request,$role=false)
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
       

        $objprefixStripeTransaction = $this->get_indirect_payment_customer_orders($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;

                     
    }

    public function get_customer_orders($formData,$arr_search_column)
    {
       
        $objprefixStripeTransaction = $this->CommissionReportService->customer_order_query($formData,$arr_search_column);

        $total_commission_amount = $this->CommissionReportService->direct_payment_customer_total_commission($formData,$arr_search_column);

        //dd($objprefixStripeTransaction->get());
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
        $amount = 0;

        $adminCommissionAmount = 0;

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

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

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

                        $href='/admin/customer_orders/view/'.base64_encode($data->order_id);
                        $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                        return $build_view_action;
                        //return $data->order_no;
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

                   ->editColumn('action',function($data) use ($current_context,$formData){

                    $build_pay_vendor_btn  = $pay_rep_commission = $pay_sales_man_commission = '';
                    $sale_manager_pay_btn = $generate_invoice = '';


                    if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                    { 
                        //check the order whether it is rep/sales order or retailer order

                        if(isset($data->order_no) && $data->order_no!='')
                        {
                            $order_type = "customer";                                                                                          
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

                        if($data->customer_id != 0) 
                        {
                            

                            $adminCommissionPercent = isset($data->admin_commission)?$data->admin_commission:0;

                            $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;


                            $shipping_charge = isset($data->total_shipping_charges)?num_format($data->total_shipping_charges):0; 

                            $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                            $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommissionPercent / 100);

                            $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                            //$representative_pay_amount = $adminCommissionAmount * ($representative_commission / 100);                         



                             if($data->is_direct_payment == 1 && $data->admin_commission_status == 0 && $data->ship_status == 1)
                             { 
                               //$build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.$adminCommissionPercent.','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';


                               if($is_disabled == ''){

                                    $bulk_pay_vendor_class = "checkbox_bulk_vendor";
                                }

                            }
                            if(isset($data->order_no) && $data->order_no!='')
                            {
                                $order_type = "customer";                                                                                          
                            }
                    


                           $data_attr .= 'data-amount_excluding_shipping_charge="'.$amount_excluding_shipping_charge.'"';
                           // $data_attr .= ' data-representative_commission="'.$representative_commission.'"';
                            //$data_attr .= ' data-representative_pay_amount="'.num_format($representative_pay_amount).'"';
                           // $data_attr .= ' data-sales_manager="'.$data->sales_manager_id.'"';
                           $data_attr .= ' data-ordertype="'.$order_type.'"';
                            $data_attr .= ' data-customer="'.$data->customer_id.'"';
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
                    });

                   

                   
                  
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
    }

    public function get_indirect_payment_customer_orders($formData,$arr_search_column)
    {
        //dd("ok");
        $objprefixStripeTransaction = $this->CommissionReportService->indirect_customer_order_query($formData,$arr_search_column);

        $total_commission_amount = $this->CommissionReportService->intermidiate_payment_customer_total_commission($formData,$arr_search_column);
        //echo "<pre>";print_r($total_commission_amount);die;
       
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
                         $href='/admin/customer_orders/view/'.base64_encode($data->order_id);
                        $build_view_action = '<a href="'.$href.'" title="View">'.$data->order_no.'</a>';
                        return $build_view_action;
                        //return $data->order_no;
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
                    
                    ->editColumn('customer_payment_status',function($data) use ($current_context)
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

                     ->editColumn('vendor_payment_status',function($data) use ($current_context)
                    {   
                        // $vendor_payment_status = "";
                        // if($data->stripe_trxn_status == '2' && $data->maker_confirmation =='1')
                        // {
                        //    $vendor_payment_status = '<span class="label label-success">Paid</span>';
                        // }
                        // else if($data->stripe_trxn_status == '3')
                        // {
                        //     $vendor_payment_status = '<span class="label label-warning">Failed</span>';
                        // }
                        // elseif($data->stripe_trxn_status == '1') 
                        // {
                        //    $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                        // }
                        // else
                        // {
                        //     $vendor_payment_status = '<span class="label label-warning">Pending</span>';
                        // }

                        // return $vendor_payment_status;

                        $vendor_payment_status = "";
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

                        $adminCommissionAmount = ($amount_excluding_shipping_charge * $adminCommission)/100;

                        $totalPrice = $totalPrice - ($shipping_charge + $adminCommissionAmount);

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
                    
                     /*Kadoe Commissions start*/
                        ->editColumn('total_admin_commission_pending',function($data) use ($total_commission_amount)
                        {
                            //$total_commission_amount['adminCommissionPending'] =  10;
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

                     /*Kadoe Commissions end*/
                   
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
                  ->editColumn('action',function($data) use ($current_context,$formData){

                    $build_pay_vendor_btn = $pay_rep_commission = $pay_sales_man_commission = '-';
                    
                    $sale_manager_pay_btn = $is_disabled = '';
                     
                        /* Pay vendor commission */

                        if($data->maker_commission_status == 1)
                        {
                          $is_disabled = 'display:none';
                        }


                        if($data->customer_id == 0)
                        {
                            $rep_pay_btn = 'display:none';
                            $sale_manager_pay_btn = 'display:block';
                        }
                        else
                        { 
                            $rep_pay_btn = 'display:block';
                            $sale_manager_pay_btn = 'display:none';
                        }
                        
                        if($data->customer_id == 0 )
                        {
                            $rep_pay_btn = 'display:none';
                            $sale_manager_pay_btn = 'display:none';

                        }
                        // temporary solution
                        $representative_commission = 0;

                    
                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                       

                       

                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        //$amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount  = $amount_excluding_shipping_charge * ($adminCommissionPercent / 100);

                        $vendorPaybleAmount = $amount_excluding_shipping_charge - $adminCommissionAmount;

                        $adminCommissionAmount = $amount_excluding_shipping_charge * $adminCommission/100;

                        

                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        } 


                        /* ---------------------- calculate excluding order amount --------------------*/

                        $adminCommission_new  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission_new==0)
                        {
                        $adminCommission_new  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice_new       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge_new = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge_new = $totalPrice_new - $shipping_charge_new;

                        $total_excluding_amount_new = $amount_excluding_shipping_charge_new * $adminCommission_new/100;
                        /* ----------------------- Ends ------------------------------------------------*/




                        if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                        {
                           $build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$data->customer_id.','.$amount_excluding_shipping_charge_new.','.num_format($vendorPaybleAmount).','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.','.$adminCommission.','."'".$data->order_no."'".')" style="'.$is_disabled.'" >Pay Vendor</button>';

                        }


                        /* Pay representative commission if order payment type is direct payment */
                        /*if($data->is_direct_payment === 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && ($data->rep_commission_status == 0 || $data->sales_manager_commission_status == 0))
                        {*/
                     

                           
                    

                        return $build_pay_vendor_btn. $pay_rep_commission . $pay_sales_man_commission;
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

                       

                        $adminCommission  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommission==0)
                        {
                        $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        
                        $shipping_charge = $this->get_customer_order_shipping_charges($data->order_id);  


                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                        $adminCommissionAmount = $amount_excluding_shipping_charge * $adminCommission/100;

                        

                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        }

                        $totalPrice       = isset($data->total_retail_price)?num_format($data->total_retail_price):0.00;

                        $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);

                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        }   
 


                       if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && $data->maker_commission_status == 0)
                        {
                           //$build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$totalPrice.','.num_format($vendorPaybleAmount).','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.')" style="'.$is_disabled.'" >Pay Vendor</button>';

                           if($is_disabled == ''){

                                $bulk_pay_vendor_class = "checkbox_bulk_vendor";

                           }

                           
                        }

                        $vendorCommision = 0;
                        $vendorCommision = $amount_excluding_shipping_charge - $adminCommissionAmount;
                        $data_attr = 'data-amount_excluding_shipping_charge="'.$amount_excluding_shipping_charge.'"';
                        // $data_attr .= ' data-representative_commission="'.$representative_commission.'"';
                        //$data_attr .= ' data-vendor_commission="'.$adminCommissionAmount.'"';
                        // $data_attr .= ' data-representative_pay_amount="'.num_format($representative_pay_amount).'"';
                        $data_attr .= ' data-sales_manager="'.$data->customer_id.'"';
                        $data_attr .= ' data-representative="'.$data->customer_id.'"';
                        $data_attr .= ' data-adminCommissionAmount="'.num_format($adminCommissionAmount).'"';
                        $data_attr .= ' data-vendorCommissionAmount="'.num_format($vendorCommision).'"';
                        $data_attr .= ' data-maker_id="'.$data->maker_id.'"';
                        $data_attr .= ' data-totalPrice="'.$amount_excluding_shipping_charge.'"';
                        //$data_attr .= ' data-vendorPaybleAmount="'.num_format($vendorPaybleAmount).'"';

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
                    });

                   

                   
                  
            $build_result = $json_result->make(true)->getData();

            return response()->json($build_result);
    }

     public function get_customer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->CustomerQuotesProductModel->where('customer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }


    public function get_user($role=false)
    {

        $userData = [];
        if ($role=='maker') {

          $objUserData = $this->MakerModel->with('user_details')->whereHas('user_details',function ($query)
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

          $objUserData = $this->RepresentativeModel->with('get_user_details')->whereHas('get_user_details',function ($query)
          {
              $query->where('status',1);
          })->get();

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
          })->get();
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
          })->get();

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
          ->withCount(['user_details as username' => function($query) {
                            $query->select(DB::raw('first_name'));
                        }])
          ->orderBy('username','ASC')
          ->get();
          if ($objUserData) {

            $usersDetailes = $objUserData->toArray();

            foreach ($usersDetailes as $id => $customer) {

                $userData[] = $customer['user_details'];
            }
          }

        }

        return $userData;
    }

public function report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];

    

    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;

    $filterData['makerId']    =isset($formData['vendor'])?$formData['vendor']:null;

    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    $filterData['is_direct_payment'] = 1;

    //dd($filterData);

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
        $arrayResponseData['Retailer Payment Status']      = $retailer_payment_status;
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

public function intermidiate_report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    $filterData = [];

    // dd($formData);

    $filterData['fromDate']  = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']    = isset($formData['to_date'])?$formData['to_date']:null;
    
    $filterData['vendor']    =isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['makerId']    =isset($formData['vendor'])?$formData['vendor']:null;
    $filterData['retailerId']          = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
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

        $adminCommissionAmount  = $totalPrice * ($adminCommission / 100);

        $shipping_charge = isset($shipping_charge)?num_format($shipping_charge):0.00;
        $amount = isset($value->total_retail_price)?num_format($value->total_retail_price):0.00;

        $amount_excluding_shipping_charge = $amount - $shipping_charge;

        $vendor_outbound_amount = $totalPrice - ($shipping_charge + $adminCommissionAmount); 

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

 public function get_retailer_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_charge');

        $shipChargeDisount = $this->RetailerQuotesProductModel->where('retailer_quotes_id',$orderId)->sum('shipping_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
    }

public function load_bulkPaymentModelData(Request $request)
    {

       $post = $request->input();  
       //echo "<pre>";print_r($post);die;
       $order_ids = $posted_data = array();
       foreach($post['checkOrderItems'] as $row)
       {
            $posted_data[$row['order_id']][]  = $row;
            $order_ids[]  = $row['order_id'];
       }
 
       if(isset($post['order_type']) && $post['order_type'] == 'customer'){
                $user_details = DB::table('customer_transaction')->selectRaw( 
                    '
                    makers.company_name as vendor_name,
                    CONCAT( users.first_name, " ", users.last_name ) AS customer_name,
                    customer_transaction.id, customer_transaction.order_no,customer_transaction.admin_commission')

                 ->leftjoin('users',function($join) {
                                        $join->on('users.id','=','customer_transaction.customer_id');                                       
                                    })
                 ->leftjoin('makers',function($join) {
                                        $join->on('makers.user_id','=','customer_transaction.maker_id');                                       
                                    })
                  ->whereIn('customer_transaction.id', $order_ids)->get();

                $resultArr = $user_details->toArray();
        } else  if(isset($post['order_type']) && $post['order_type'] == 'retailer'){
                $user_details = DB::table('retailer_transaction')->selectRaw( 
                    '
                    makers.company_name as vendor_name,
                    CONCAT( users.first_name, " ", users.last_name ) AS customer_name,
                    retailer_transaction.id, retailer_transaction.order_no,retailer_transaction.admin_commission')

                 ->leftjoin('users',function($join) {
                                        $join->on('users.id','=','retailer_transaction.retailer_id');                                       
                                    })
                 ->leftjoin('makers',function($join) {
                                        $join->on('makers.user_id','=','retailer_transaction.maker_id');                                       
                                    })
                  ->whereIn('retailer_transaction.id', $order_ids)->get();

                $resultArr = $user_details->toArray();
        } else {
                $user_details = DB::table('representative_leads')->selectRaw( 
                    '
                    makers.company_name as vendor_name,
                    CONCAT( users.first_name, " ", users.last_name ) AS customer_name,
                     representative_leads.id,  representative_leads.order_no, representative_leads.admin_commission')

                 ->leftjoin('users',function($join) {
                                        $join->on('users.id','=',' representative_leads.representative_id');                                       
                                    })
                 ->leftjoin('makers',function($join) {
                                        $join->on('makers.user_id','=',' representative_leads.maker_id');                                       
                                    })
                  ->whereIn(' representative_leads.id', $order_ids)->get();

                $resultArr = $user_details->toArray();
        }  
       // echo "resultArr <pre>";print_r($resultArr);die;  
        $users = array();
        foreach ($resultArr as $key => $row) {
            
            if($row->vendor_name != "--")
                $users[$row->id]['vendor_name'] = $row->vendor_name;    
                $users[$row->id]['customer_name'] = $row->customer_name;    
                $users[$row->id]['order_no'] = $row->order_no;
                $users[$row->id]['admin_commission'] = $row->admin_commission;
            }

        $dataArr['posted_data'] = $posted_data;
        $dataArr['user_details'] = $users;
        $dataArr['user_type'] = $post['user_type'];
        $dataArr['order_type'] = $post['order_type'];
        $dataArr['user_id'] = $post['user_id'];

       return view($this->module_view_folder.'.bulkPaymentModelForm_customer', $dataArr);
    }

}