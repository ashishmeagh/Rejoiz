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
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use DB;
use Datatables;
use Excel, Validator;
use Sentinel;


class CommissionReportsController extends Controller
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
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel
                                )
    {
        $this->arr_view_data      = [];
        $this->project_name       = get_site_settings(['site_name','website_url']);
        $this->module_title       = "Payment Intermediation (Through ".$this->project_name['site_name'].")";
        $this->module_view_folder = "admin.commission_reports";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/commission_reports");    
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
        $this->CommissionService          = $CommissionService;
    }


    public function index()
    {   //dd("ok");
        $this->arr_view_data['arr_data'] = array();
        
        $arrMakers          = $this->get_user('maker');
        $arrRepresentative  = $this->get_user('representative');
        $arrRetailer        = $this->get_retailer('retailer');
        $arrSalesManger     = $this->get_user('sales_manager');
        
        $this->arr_view_data['page_title']          = $this->module_title;
        $this->arr_view_data['module_title']        = $this->module_title;
        $this->arr_view_data['module_url_path']     = $this->module_url_path;
        $this->arr_view_data['arr_data']            = isset($arr_data) ? $arr_data: [];
        $this->arr_view_data['arrMakers']           = isset($arrMakers) ? $arrMakers: [];
        $this->arr_view_data['arrRetailer']         = isset($arrRetailer) ? $arrRetailer: [];
        $this->arr_view_data['arrSalesManager']     = isset($arrSalesManger) ? $arrSalesManger: [];
        $this->arr_view_data['arrRepresentative']   = isset($arrRepresentative) ? $arrRepresentative: [];

        return view($this->module_view_folder.'.index', $this->arr_view_data);
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
        $formData['repId']            = isset($arr_search_column['representative'])?$arr_search_column['representative']:false;        
        $formData['salesId']          = isset($arr_search_column['sales_manager'])?$arr_search_column['sales_manager']:false;        
        $formData['orderStatus']      = isset($arr_search_column['order_payment_status'])?$arr_search_column['order_payment_status']:false;
        $formData['vendorPaymentStatus'] = isset($arr_search_column['vendor_payment_status'])?$arr_search_column['vendor_payment_status']:false;
        $formData['repPaymentStatus']    = isset($arr_search_column['rep_payment_status'])?$arr_search_column['rep_payment_status']:false;
        $formData['is_direct_payment'] = 0;      

        $objprefixStripeTransaction = $this->get_retailer_orders($formData,$arr_search_column);
        
        return $objprefixStripeTransaction;

                     
    }

    public function get_all_rep_orders($formData,$arr_search_column)
    {
      
        $total_commission_amount = $this->CommissionReportService->calculate_rep_total_commission('all',$formData); 
                // dd($total_commission_amount);
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

            $adminCommission  = isset($data->admin_commission)?num_format($data->admin_commission):$this->CommissionService->get_admin_commission();

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

    public function get_reps_orders($formData,$arr_search_column)
    {
       
        $total_commission_amount = $this->CommissionReportService->calculate_rep_total_commission('reps',$formData);
        // dd($total_commission_amount);          
        $objprefixStripeTransaction = $this->CommissionReportService->rep_order_query($formData,$arr_search_column);
        $current_context = $this;

        $json_result  = Datatables::of($objprefixStripeTransaction);
        
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

            if(isset($arr_search_column['order_status']) && $arr_search_column['order_status']!="" && $arr_search_column['order_status'] == 2)
            {
               $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
            }
            else
            {
               $href = url('/').'/admin/commission_reports/report_details/'.base64_encode($data->quote_id).'/'.base64_encode('lead');
            }
            
            
            $build_view_action = '<a class="btn btn-circle btn-success btn-outline show-tooltip href="'.$href.'" title="View">View</a>';

            return $build_view_action;
        })  
        
        ->editColumn('total_wholesale_price',function($data) use ($current_context)
        {
            return $total_wholesale_price = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;
        });
                    
                                   
        $build_result = $json_result->make(true)->getData();

        return response()->json($build_result);
    }


    public function get_retailer_orders($formData,$arr_search_column)
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
              

                        $amount_excluding_shipping_charge = $totalPrice - $shipping_charge;

                    
                        return num_format($amount_excluding_shipping_charge);
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

                
                        $adminCommissionAmount  = ($amount_excluding_shipping_charge * $adminCommission) / 100;

                       // dump($adminCommissionAmount,$representative_commission);
                        
                        $rep_commission_amount = ($adminCommissionAmount * $representative_commission) / 100;

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

                        $adminCommissionAmount  = ($amount_excluding_shipping_charge *$adminCommission)/100;

                        $rep_commission_amount =0;

                      if(isset($data->representative_id)||isset($data->sales_manager_id))
                      {
                        if(isset($data->representative_id))
                        {
                            $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                        }
                        if(isset($data->sales_manager_id))
                        {
                            $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                        } 
 
                        $rep_commission_amount = ($adminCommissionAmount * $representative_commission)/100 ;
                      }

                        //$makerCommissionAmount = $adminCommissionAmount+$rep_commission_amount;
                        

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

                    
                        // $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        // if($adminCommissionPercent==0)
                        // {
                        // $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        // }

                        $adminCommissionPercent  = isset($data->admin_commission)?num_format($data->admin_commission):0.00;
                        if($adminCommissionPercent==0)
                        {
                        $adminCommissionPercent  = isset($data->maker_admin_commission)?num_format($data->maker_admin_commission):0.00;
                        }




                       // dump($data->order_no,$data->admin_commission,$data->maker_admin_commission);

                        $totalPrice       = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):0.00;

                       /* $shipping_charge = $this->get_retailer_order_shipping_charges($data->order_id);


                        if(isset($data->representative_id)||isset($data->sales_manager_id))
                        {
                           $shipping_charge = $this->CommissionReportService->get_representative_order_shipping_charges($data->order_id);   
                        }  */  

                        //if applied free shipping promocode
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

    
                        if($shipping_charge > 0)
                        {
                            $totalPrice = $totalPrice - $shipping_charge;
                        }

                        
                        //$amount_excluding_shipping_charge = $totalPrice - $shipping_charge;
                        $amount_excluding_shipping_charge = $totalPrice;
                        
                        $adminCommissionAmount  = $totalPrice * ($adminCommissionPercent / 100);

                        $vendorPaybleAmount = $totalPrice - $adminCommissionAmount;

                        $representative_pay_amount = $adminCommissionAmount * ($representative_commission / 100);




                        $order_flag = '';

                        $is_rep_sales_orders = $this->RepresentativeLeadsModel->where('id',$data->order_id)->where('order_no',$data->order_no)->count();

                        if($is_rep_sales_orders > 0)
                        {
                            $order_flag = 'rep_sales_order';
                        }
                        else
                        {
                            $order_flag = "retailer_orders";
                        }

                            
                        if($data->ship_status == 1 && $data->is_direct_payment == 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && ($data->maker_commission_status == null || $data->maker_commission_status == 0))
                        {
                             
                           
                           $build_pay_vendor_btn = '<button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$adminCommissionPercent.','.$totalPrice.','.num_format($vendorPaybleAmount).','.num_format($adminCommissionAmount).','.$data->maker_id.','.$data->order_id.','."'".$data->order_no."'".','."'".$order_flag."'".')" style="'.$is_disabled.'" >Pay Vendor</button>';

                        }


                        /* Pay representative commission if order payment type is direct payment */
                        if($data->ship_status == 1 && $data->is_direct_payment === 0 && $data->payment_term != 'Net30' && $data->is_split_order == 0 && ($data->rep_commission_status == 0 || $data->sales_manager_commission_status == 0))
                        {
                        $chkRepCommissionStatus  = isset($data->rep_commission_status) ? $data->rep_commission_status:0;
                        if($chkRepCommissionStatus!=1)
                        {
                           $pay_rep_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip aaa"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->representative_id.','.$data->order_id.','."'".$order_flag."'".')" >Pay Representative</button>';
                       }
                       $chkSalesManagerCommissionStatus  = isset($data->sales_manager_commission_status) ? $data->sales_manager_commission_status:0;
                        if($chkSalesManagerCommissionStatus==0)
                        {
                           $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$amount_excluding_shipping_charge.','.num_format($adminCommissionAmount).','.$representative_commission.','.num_format($representative_pay_amount).','.$data->sales_manager_id.','.$data->order_id.','."'".$order_flag."'".')" >Pay Sales Manager</button>';
                         }
                     }

                           
                    

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


                        if($data->representative_id == 0)
                        {
                            $bulk_sale_manager_pay_class = "checkbox_bulk_sales_manager";
                        }
                        else
                        { 
                        $chkRepCommissionStatus  = isset($data->rep_commission_status) ? $data->rep_commission_status:0;
                        if($chkRepCommissionStatus!=1)
                        {
                           $bulk_rep_pay_class = "checkbox_bulk_representative";
                        } 
                        }
                        
                        if($data->representative_id == 0 && $data->sales_manager_id == 0)
                        {
                           $bulk_rep_pay_class = $bulk_sale_manager_pay_class = "";
                        }
                        
                        $chkSalesManagerCommissionStatus  = isset($data->sales_manager_commission_status) ? $data->sales_manager_commission_status:0;
                        if ($data->sales_manager_id != 0 && $chkSalesManagerCommissionStatus == 0) 
                        {
                           $representative_commission = $this->CommissionService->get_sales_manager_commission($data->sales_manager_id);
                           $bulk_sale_manager_pay_class = "checkbox_bulk_sales_manager";
                        }   
                        else
                        {
                           $representative_commission = $this->CommissionService->get_representative_commission($data->representative_id);
                           $bulk_sale_manager_pay_class = "";
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
                        $vendorCommision = 0;
                        $vendorCommision = $amount_excluding_shipping_charge - $adminCommissionAmount;
   
                        $data_attr = 'data-amount_excluding_shipping_charge="'.$amount_excluding_shipping_charge.'"';
                        $data_attr .= ' data-representative_commission="'.$representative_commission.'"';
                        $data_attr .= ' data-order_no="'.$data->order_no.'"';
                        $data_attr .= ' data-representative_pay_amount="'.num_format($representative_pay_amount).'"';
                        $data_attr .= ' data-sales_manager="'.$data->sales_manager_id.'"';
                        $data_attr .= ' data-representative="'.$data->representative_id.'"';
                        $data_attr .= ' data-adminCommissionAmount="'.num_format($adminCommissionAmount).'"';
                        $data_attr .= ' data-vendorCommissionAmount="'.num_format($vendorCommision).'"';
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
                            $rep_sales_commission = $data->rep_commission_status;
                        }
                        elseif(isset($data->sales_manager_id) && $data->sales_manager_id!=0)
                        {
                           $rep_sales_commission = $data->sales_manager_commission_status;
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
    }

   
    public function load_bulkPaymentModelData(Request $request)
    {
        $post = $request->input(); 
        
        //echo "<pre> ==>"; print_r($post); exit;
       $order_ids = $posted_data = $order_nos = $users = array();
       // dd($post['checkOrderItems']);
       foreach($post['checkOrderItems'] as $key => $row)
       {
            $posted_data[$key][]  = $row;
            $order_ids[]  = $row['order_id'];
            $order_nos[]  = $row['orderNo'];

       }
// dd($posted_data);
        $user_details = DB::table('representative_leads')->selectRaw( 
            '( CASE WHEN
                (   
                    representative_leads.maker_id = users.id AND representative_leads.maker_id != 0 
                ) THEN CONCAT( users.first_name, " ", users.last_name ) ELSE "--" END ) AS vendor_name,

            (CASE WHEN (
                         representative_leads.sales_manager_id = users.id AND representative_leads.sales_manager_id != 0 
                        ) 
                    THEN CONCAT(users.first_name, " ", users.last_name) ELSE "--" 
                    END
            ) AS sales_manager_name,

            (CASE WHEN (
                         representative_leads.representative_id = users.id AND representative_leads.representative_id != 0 
                        ) 
                    THEN CONCAT(users.first_name, " ", users.last_name) ELSE "--" 
                    END
            ) AS representative_name,

            (CASE WHEN (
                         representative_leads.retailer_id = users.id AND
                         representative_leads.retailer_id != 0 
                        ) 
                    THEN CONCAT(users.first_name, " ", users.last_name) ELSE  "--"
                    END
            ) AS retailer_name,

            representative_leads.id, representative_leads.order_no')

         ->leftjoin('users',function($join) {
                                $join->on('users.id','=','representative_leads.maker_id')
                                        ->orOn('users.id','=','representative_leads.representative_id')
                                        ->orOn('users.id','=','representative_leads.retailer_id')
                                        ->orOn('users.id','=','representative_leads.sales_manager_id');
                            })


                       ->whereIn('representative_leads.id', $order_ids)
                       ->whereIn('representative_leads.order_no', $order_nos);
                       if(Sentinel::findUserById($post['user_id'])->inRole('maker'))
                       {
                        $user_details= $user_details->where('representative_leads.maker_id', $post['user_id']);
                       }
                       
                       // ->where('representative_leads.maker_id', $post['user_id'])
                       $user_details= $user_details->get();

        $resultArr1 = $user_details->toArray();
        // dd($post['user_id'],$resultArr1, $order_nos);
        /*if(count($resultArr) == 0)
        {*/
             $user_details = DB::table('retailer_transaction')->selectRaw( 
            '( CASE WHEN
                (   
                    retailer_transaction.maker_id = users.id AND retailer_transaction.maker_id != 0 
                ) THEN CONCAT( users.first_name, " ", users.last_name ) ELSE "--" END ) AS vendor_name,

            (CASE WHEN (
                         retailer_transaction.retailer_id = users.id AND
                         retailer_transaction.retailer_id != 0 
                        ) 
                    THEN CONCAT(users.first_name, " ", users.last_name) ELSE  "--"
                    END
            ) AS retailer_name,

            retailer_transaction.id, retailer_transaction.order_no')

         ->leftjoin('users',function($join) {
                                $join->on('users.id','=','retailer_transaction.maker_id')
                                        ->orOn('users.id','=','retailer_transaction.retailer_id');
                            })


                       ->whereIn('retailer_transaction.id', $order_ids)
                       ->whereIn('retailer_transaction.order_no', $order_nos);
                       // ->where('retailer_transaction.maker_id', $post['user_id'])
                       if(Sentinel::findUserById($post['user_id'])->inRole('maker'))
                       {
                        $user_details= $user_details->where('retailer_transaction.maker_id', $post['user_id']);
                       }
                       
                       // ->where('representative_leads.maker_id', $post['user_id'])
                       $user_details= $user_details->get();

        $resultArr2 = $user_details->toArray();
        // }

        $resultArr = array_merge($resultArr1,$resultArr2);
// dd($resultArr);

        foreach ($resultArr1 as $key => $row) 
        {
             if(isset($row->vendor_name) && $row->vendor_name != "--")
                $users[$row->id]['vendor_name'] = isset($row->vendor_name)?$row->vendor_name:'--';

            if(isset($row->sales_manager_name) && $row->sales_manager_name != "--")
                $users[$row->id]['sales_manager_name'] = isset($row->sales_manager_name)?$row->sales_manager_name:'--';

            if(isset($row->representative_name) && $row->representative_name != "--")
                $users[$row->id]['representative_name'] = isset($row->representative_name)?$row->representative_name:'--';

            if(isset($row->retailer_name) && $row->retailer_name != "--")
                $users[$row->id]['retailer_name'] = isset($row->retailer_name)?$row->retailer_name:'--';

            $users[$row->id]['order_no'] = $row->order_no;
        }

        $usersData = [];
        
        foreach ($resultArr2 as $key => $value) 
        {
             if(isset($value->vendor_name) && $value->vendor_name != "--")
                $usersData[$value->id]['vendor_name'] = isset($value->vendor_name)?$value->vendor_name:'--';

            if(isset($value->sales_manager_name) && $value->sales_manager_name != "--")
                $usersData[$value->id]['sales_manager_name'] = isset($value->sales_manager_name)?$value->sales_manager_name:'--';

            if(isset($value->representative_name) && $value->representative_name != "--")
                $usersData[$value->id]['representative_name'] = isset($value->representative_name)?$value->representative_name:'--';

            if(isset($value->retailer_name) && $value->retailer_name != "--")
                $usersData[$value->id]['retailer_name'] = isset($value->retailer_name)?$value->retailer_name:'--';

            $usersData[$value->id]['order_no'] = $value->order_no;
        }

        // dd($usersData,$users);
         $users = array_merge($usersData,$users);
        // dd($users);

       // echo "<pre> ===>"; print_r($resultArr); exit;
       /* $users = array();
        foreach ($resultArr as $key => $row) {
            
            if(isset($row->vendor_name) && $row->vendor_name != "--")
                $users[$row->id]['vendor_name'] = isset($row->vendor_name)?$row->vendor_name:'--';

            if(isset($row->sales_manager_name) && $row->sales_manager_name != "--")
                $users[$row->id]['sales_manager_name'] = isset($row->sales_manager_name)?$row->sales_manager_name:'--';

            if(isset($row->representative_name) && $row->representative_name != "--")
                $users[$row->id]['representative_name'] = isset($row->representative_name)?$row->representative_name:'--';

            if(isset($row->retailer_name) && $row->retailer_name != "--")
                $users[$row->id]['retailer_name'] = isset($row->retailer_name)?$row->retailer_name:'--';

            $users[$row->id]['order_no'] = $row->order_no;

        }*/

        $dataArr['posted_data'] = $posted_data;
        $dataArr['user_details'] = $users;
        $dataArr['user_type'] = $post['user_type'];
        $dataArr['user_id'] = $post['user_id'];

       return view($this->module_view_folder.'.bulkPaymentModelForm', $dataArr);
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
        if ($role=='customer') {

          $objUserData = $this->MakerModel->with('user_details')->whereHas('user_details',function ($query)
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

            
                $userData = $usersDetailes;
            
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
         $arrResponse = [];  

         $adminCommission = $this->CommissionService->get_admin_commission();

         $objOrderData = $this->RetailerQuotesModel->with(['user_details','maker_data','stripe_transaction_detail'])
                                                   ->where('id',$id)
                                                   ->first();

         if($objOrderData)
         {
           $orderData = $objOrderData->toArray();
            
           $companyName = isset($orderData['maker_data']['company_name'])?$orderData['maker_data']['company_name']:'N/A';

           $retailer_first_name = isset($orderData['user_details']['first_name'])?$orderData['user_details']['first_name']:'N/A';

           $retailer_last_name = isset($orderData['user_details']['last_name'])?$orderData['user_details']['last_name']:'';
           
           $adminCommissionAmount = $orderData['total_wholesale_price']*((float)$adminCommission / 100);



           $vendorAmount = $orderData['total_wholesale_price'] - $adminCommissionAmount;
           $arrResponse['order_no']           = $orderData['order_no'];
           $arrResponse['commission_amount']  = $vendorAmount;
           $arrResponse['order_amount']       = $orderData['total_wholesale_price'];
           $arrResponse['vendor_name']        = $companyName;
           $arrResponse['retailer_name']      = $retailer_first_name.' '.$retailer_last_name;
           $arrResponse['status'] = isset($orderData['stripe_transaction_detail']['status'])?$orderData['stripe_transaction_detail']['status']:'';
           $arrResponse['transfer_id']        = isset($orderData['stripe_transaction_detail']['transfer_id'])?$orderData['stripe_transaction_detail']['transfer_id']:'N/A';

         }
         
         return $arrResponse;

   }

    public function report_generator(Request $request)
{   
     
    $type  = 'csv';    
    $formData = $request->all();
    //dd($formData);
    $filterData = [];

    $filterData['fromDate']   = isset($formData['from_date'])?$formData['from_date']:null;
    $filterData['toDate']     = isset($formData['to_date'])?$formData['to_date']:null;

    $filterData['vendor']     = isset($formData['makerId'])?$formData['makerId']:null;
    $filterData['retailerId'] = isset($formData['retailer'])?$formData['retailer']:null;
    $filterData['repId']      = isset($formData['representative'])?$formData['representative']:null;
    $filterData['salesId']    = isset($formData['sales_manager'])?$formData['sales_manager']:null;
        
    $filterData['orderStatus']         = isset($formData['order_payment_status'])?$formData['order_payment_status']:null;
    $filterData['vendorPaymentStatus'] = isset($formData['vendor_payment_status'])?$formData['vendor_payment_status']:null;
    $filterData['commissionStatus'] = isset($formData['order_status'])?$formData['order_status']:null;
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

        /*-------------------------------------------------------*/
        if($value->maker_commission_status == 1)
          {
            $maker_commission_status = 'Paid';
          }
          if($value->maker_commission_status == 0 || $value->maker_commission_status==null)
          {
            $maker_commission_status = 'Pending';
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
        $amount = isset($value->total_wholesale_price)?num_format($value->total_wholesale_price):0;

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

         $maker_commission_amount = $admin_commission+$rep_commission_amount;

         $maker_commission_amount = $amount_excluding_shipping_charge - $maker_commission_amount;

        $maker_commission_amount = isset($maker_commission_amount)?num_format($maker_commission_amount):0.00;
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
        $arrayResponseData['Vendor Payment Amount'] = $maker_commission_amount;
        $arrayResponseData['Vendor Payment Status']   = $maker_commission_status;        
        $arrayResponseData['Rep/Sales Commission
                Amount'] = $rep_commission_amount;
        $arrayResponseData['Rep/Sales Commission Status'] = $rep_sales_commission_status;
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

        if($shipCharge!=0)
        {
            return $shippingCharges = $shipCharge-$shipChargeDisount;
        }
        return $shipCharge;
    }

    public function get_representative_order_shipping_charges($orderId)
    {
        $shippingCharges = 0;

        $shipCharge = $this->RepresentativeProductLeadsModel->where('representative_leads_id',$orderId)->sum('shipping_charges');

        $shipChargeDisount = $this->RepresentativeProductLeadsModel->where('representative_leads_id',$orderId)->sum('shipping_charges_discount');
        
        return $shippingCharges = $shipCharge-$shipChargeDisount;
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
