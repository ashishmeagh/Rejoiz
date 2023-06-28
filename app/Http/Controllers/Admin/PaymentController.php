<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TransactionMappingModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Common\Services\EmailService;
use App\Common\Services\HelperService;
use App\Common\Services\orderDataService;
use App\Common\Services\CommissionService;
use App\Common\Services\InventoryService;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\ProductInventoryModel;
use App\Models\RetailerQuotesProductModel;
use App\Models\UserModel;
use App\Models\RetailerQuotesModel;
use App\Models\ShopSettings;
use App\Models\StripeTransactionModel;
use App\Models\SiteSettingModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use Sentinel;
use Validator;


class PaymentController extends Controller
{
    public function __construct(StripePaymentService $StripePaymentService,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                StripeTransactionModel $StripeTransactionModel,
                                GeneralService $GeneralService,
                                RetailerQuotesModel $RetailerQuotesModel,
                                EmailService $EmailService,
                                HelperService $HelperService,
                                SiteSettingModel $SiteSettingModel,
                                ShopSettings $ShopSettings,
                                orderDataService $orderDataService,
                                InventoryService $InventoryService,
                                CommissionService $CommissionService,
                                ProductInventoryModel $ProductInventoryModel,
                                RetailerQuotesProductModel $RetailerQuotesProductModel,
                                UserModel $UserModel,
                                TransactionMappingModel $TransactionMappingModel,
                                RepresentativeProductLeadsModel $RepresentativeProductLeadsModel
                                )
    {
        $this->arr_view_data      = [];
        $this->module_title       = "Payment";
        $this->payment_proof = base_path() . '/storage/app/';
        $this->module_view_folder = "admin.vendor_payment";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/vendor");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
        $this->role = 'retailer';
        $this->StripeAccountDetailsModel  = $StripeAccountDetailsModel;
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->TransactionMappingModel     = $TransactionMappingModel;
        $this->RepresentativeProductLeadsModel     = $RepresentativeProductLeadsModel;
        $this->UserModel     = $UserModel;
        $this->RetailerQuotesProductModel     = $RetailerQuotesProductModel;
        $this->StripePaymentService       = $StripePaymentService;
        $this->GeneralService             = $GeneralService;
        $this->CommissionService             = $CommissionService;
        $this->ShopSettings             = $ShopSettings;
        $this->HelperService              = $HelperService;
        $this->orderDataService              = $orderDataService;
        $this->ProductInventoryModel              = $ProductInventoryModel;
        $this->RetailerQuotesModel        = $RetailerQuotesModel;
        $this->InventoryService           = $InventoryService; 
        $this->EmailService               = $EmailService;    
        $this->SiteSettingModel        = $SiteSettingModel;

        $this->site_setting_obj  = $this->SiteSettingModel->first();
       
        if(isset($this->site_setting_obj))
        {
           $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
        
    }

    public function pay_to_vendor(Request $request)
    {
        
        $form_data = $request->all();
        
        $maker_id = isset($form_data['maker_id'])?$form_data['maker_id']:false;
        $order_id = isset($form_data['order_id'])?$form_data['order_id']:false;

        /*  Get admin stripe secret key id  */
        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        /*  Get maker stripe secret key id  */
        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($maker_id);

        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$maker_id)
                                                                      ->where('admin_stripe_key_id',$stripKeyId)
                                                                      ->where('vendor_stripe_key_id',$vendorStripKeyId)
                                                                      ->first();        
             
        if($obj_stripe_account_details)
        {
            $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;
            
            if($stripe_acc_id)
            {
                $order_data = get_order_data($order_id);

                if(isset($order_data['maker_commission_status']) && $order_data['maker_commission_status'] == '1')
                {
                    $response['status']  = 'pay-warning';
                    $response['message'] = "Payment already completed for this order.";
                    
                   return response()->json($response);
                }
                
                $arr_transfer_data['amount']      = $form_data['amount'];
                $arr_transfer_data['account_id']  = $stripe_acc_id;
                $arr_transfer_data['description'] = 'Payment for Order No:'.isset($order_data['order_no'])?$order_data['order_no']:false;

                $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

                if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }

                if($transfer_response)
                {   

                    $arr_data['lead_id']         = $form_data['order_id'];
                    $arr_data['amount']          = $form_data['amount'];
                    $arr_data['transaction_id']  = $transfer_response->balance_transaction;
                    $arr_data['transfer_id']     = $transfer_response->id;
                    $arr_data['destination_payment'] = $transfer_response->destination_payment;
                    $arr_data['status']          = '2';
                    $arr_data['received_by']     = $maker_id;
                    $arr_data['paid_by']         = get_admin_id();

                     /* Create Entry in StripTransaction table */
                    $create_stripe_transaction = $this->StripeTransactionModel->create($arr_data);

                    if($create_stripe_transaction)
                    {
                        $bulk_transaction_key = rand(1000,9999).date("s");
                        /* update maker commission status in retailer transaction table */

                        $this->RetailerQuotesModel->where('id',$form_data['order_id'])
                                                  ->update(['maker_commission_status' => '1',
                                                            'received_commission_stripe_key_id' => $vendorStripKeyId,
                                                            'transfer_commission_stripe_key_id' => $stripKeyId]);



                        $login_user = \Sentinel::check();

                        if($login_user)
                        {
                            $loggedInUserId = $login_user->id;
                        }
                        else
                        {
                            $loggedInUserId = 1;
                        }
                        /*   Notification Sending   */
                        $notification_arr                 = [];
                        $notification_arr['from_user_id'] = $loggedInUserId;
                        $notification_arr['to_user_id']   = $maker_id;

                       /* $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of Order No: <a href='.url('vendor/retailer_orders/view/'.base64_encode($order_id)).">".$order_data['order_no'].'</a>';*/

                        /*check order from rep or sales*/
                        $view_href = '';

                       /* $count = $this->RetailerQuotesModel->where('order_no',$order_data['order_no'])->count();

                        if(isset($count) && $count>0)
                        {
                            $order_obj = $this->RetailerQuotesModel->where('order_no',$order_data['order_no'])->where('maker_id',$maker_id)->first();

                            if(isset($order_obj))
                            {
                              $order_arr = $order_obj->toArray(); 
                            }*/

                            $view_href = url('/').'/vendor/retailer_orders/view/'.base64_encode($order_id);

                     /*   }
                        else
                        {
                            $order_obj = $this->RepresentativeLeadsModel->where('order_no',$order_data['order_no'])->where('maker_id',$maker_id)->first();

                            if(isset($order_obj))
                            {
                                $order_arr = $order_obj->toArray();
                            } 
                            
                            $view_href = url('/').'vendor/representative_orders/view/'.base64_encode($order_data['order_no']);

                        }*/

                        $notification_arr['description']  = 'Payment ($'.$form_data['amount'].') is received successfully of Order No: '.$order_data['order_no'];

                        $notification_arr['title']        = 'Payment Received';
                        $notification_arr['type']         = 'maker'; 
                        $notification_arr['link']         = $view_href; 
                        
                        $this->GeneralService->save_notification($notification_arr);




                        /* send payment received email to vendor */

                        $maker_email = $this->HelperService->get_user_mail($maker_id);

                        $credentials = ['email' => $maker_email];
      
                        $arr_user = get_user_by_credentials($credentials);

                        /*Get site setting data from helper*/
                        $arr_site_setting = get_site_settings(['site_name','website_url']);

                        $arr_built_content = ['commission_amount'   => num_format($form_data['amount']),
                                                'order_no'          => $order_data['order_no'],
                                                'PROJECT_NAME'      => $arr_site_setting['site_name']
                                            ];


                        $arr_mail_data                      = [];
                        $arr_mail_data['email_template_id'] = '61';
                        $arr_mail_data['arr_built_content'] = $arr_built_content;
                        $arr_mail_data['arr_user']          = $arr_user;

                        $arrMailData['retailer_order_data'][] = $order_data;
                        $arrUserData['business_details'] = $arr_user;
                        $arrUserData['personla_details'] = $arr_user;


                        $arrOrderAmount = [];

                        //$email_status  = $this->EmailService->send_mail($arr_mail_data);

                        /*  ------ Send payment Invoice to user -----------------  */
                          $invoice_data = $this->GeneralService->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount);
                          /*  ----------- END ------------  */

                        //$is_mail_send = $this->EmailService->commission_paid_mail(num_format($form_data['amount']),$order_data['order_no'],$maker_email);


                        $response['status']  = 'success';
                        $response['message'] = 'Commission has been transferred.';
                    }
                    else
                    {
                        $response['status']  = 'error';
                        $response['message'] = 'Something went wrong, please try again.';
                    }
                    return response()->json($response);
                }
                else
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }
            }
            else
            {
                $response['status'] = 'error';
                $response['message'] = 'Please verify this users stripe account details.';
                return response()->json($response);
            }

        }
        else
        {
            $response['status']  = 'warning';
            $response['message'] = "This user are not connected to ".$this->site_setting_arr['site_name']." stripe account";
            $response['user_id'] = $maker_id;
            
            return response()->json($response);
        }
    }

    public function payment($order_id,$slug_chk_qty_available_or_not=null)
    {      

        
        $order_data = [];

        $order_id = isset($order_id)?base64_decode($order_id):'';

        $payment_term = $this->RetailerQuotesModel->where('id',$order_id)->pluck('payment_term')->first();

      
        if($order_id)
        {

            $loggedInUserId = 0;
            $user = \Sentinel::check();


            if($user && $user->inRole('maker'))
            {
                $loggedInUserId = $user->id;
            }
      
            $order_details = $out_of_stock_details = [];
            
            $order_details = $this->RetailerQuotesModel->where('id',$order_id)
                                            ->with(['quotes_details','quotes_details.product_details'])
                                            ->first();
                                           

            $arr_data = $arr_charge_data = [];
                    
            if($order_details)
            {



                $order_details = $order_details->toArray();
                
                $split_order_free_shipping = $this->ShopSettings->where('maker_id',$order_details['maker_id'])
                                                                ->pluck('split_order_free_shipping')
                                                                ->first();
                
                /*  check Product Availability  */

                $ord_no = isset($order_details['order_no'])?$order_details['order_no']:false;
                $order_data = $this->orderDataService->get_order_details('retailer',$ord_no,$order_details['maker_id']);

                $arr_available_qty_by_skno = [];
                $arr_requested_qty_by_skno = [];
                $arr_sku_no = [];

                /* If True then Direct Inventory Deduction Applicable
                      False then Order Split Applicable with Email Sent to Retailer for Confirmation 

                */
                $is_completely_fulfilled = true;

                $arr_product = array_column($order_data, 'quotes_details');

                /* Extract SKU */
                if($order_data)

                {                     
                    if($arr_product && count($arr_product) > 0)
                    {
                        foreach($arr_product as $product_data)
                        {
                            if($product_data && sizeof($product_data) > 0)
                            {
                                foreach ($product_data as $key => $product)
                                {
                                    $arr_sku_no[] = $product['sku_no'];
                                    $arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);
                                }
                            }
                        }       
                    }    
                }    

                /* Get Available SKU Quantity */
                
                $arr_available_qty_by_skno = $this->InventoryService->get_available_qty($arr_sku_no);

                $arr_available_qty_by_skno = array_column($arr_available_qty_by_skno, 'quantity','sku_no');
                // dd($arr_available_qty_by_skno,$arr_requested_qty_by_skno);
                
                if(sizeof($arr_requested_qty_by_skno) > 0)
                {
                    foreach ($arr_requested_qty_by_skno as $sku_no => $requested_quantity) {

                        if($arr_available_qty_by_skno[$sku_no] < $requested_quantity)
                        {
                            $is_completely_fulfilled = false;
                        }
                    }
                }

                /*When click on confirm order and insufficient inventory then return view and tell to vender that click on split order...*/
                if(isset($slug_chk_qty_available_or_not) && $slug_chk_qty_available_or_not!=null)
                {
                    if($is_completely_fulfilled == false)
                    {
                        $response['status']      = 'split_warning';
                        $response['description'] = 'Sorry, Product inventory is insufficient, for order processing please fulfill the product inventory.';
                        return $response;   
                    }
                    else
                    {
                        $response['status']      = 'success';
                        $response['description'] = 'Inventory available, you can procced.';
                        return $response;       
                    }
                    
                }
                

                if($is_completely_fulfilled == true)
                {  
                    
                    
                        if($payment_term == 'Offline')
                        {
                            $isDirectPayment = isset($order_details['is_direct_payment'])?$order_details['is_direct_payment']:0;
                            
                            $retailerId = isset($order_details['retailer_id'])?$order_details['retailer_id']:0;

                            $customerId = isset($order_details['transaction_details']['customer_token'])?$order_details['transaction_details']['customer_token']:'';
                
                         /* Update payment status in order table by Harshada on date 21 Oct 2020 */
                    
                        $order_status_update = $this->RetailerQuotesModel->where('id',$order_id)
                                                                 ->where('maker_id',$order_details['maker_id'])

                                                                 ->update(['maker_confirmation' => '1','is_direct_payment'=>$isDirectPayment]);



                        /* send retailer notification*/

                        $view_order_href=  url('/').'/retailer/my_orders/view/'.base64_encode($order_details['id']);


                        $vendorStoreName = get_maker_company_name($order_details['maker_id']);
                        $arr_notify_data                 = [];
                        $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
                        $arr_notify_data['to_user_id']   = $order_details['retailer_id'] or '';

                        $arr_notify_data['description']  = 'Your order('.$order_details['order_no'].') is confirmed from vendor : '.$vendorStoreName;

                        $arr_notify_data['title']        = 'Order Confirmed';
                        $arr_notify_data['type']         = 'retailer';  
                        $arr_notify_data['link']         = $view_order_href;  

                        $this->GeneralService->save_notification($arr_notify_data);

                        $this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);
                    

                        /* get admin id */

                        $admin_id  = get_admin_id();
                        $view_href =  url('/').'/admin/retailer_orders/view/'.base64_encode($order_details['id']);

                        $maker_details = \Sentinel::findById($order_details['maker_id']);

                        /* send admin notification*/
                        $arr_notify_data                 = [];
                        $arr_notify_data['from_user_id'] = $order_details['maker_id'] or '';
                        $arr_notify_data['to_user_id']   = $admin_id or '';

                        $arr_notify_data['description']  = 
                        'Order('.$order_details['order_no'].') is confirmed from vendor : '.$maker_details->first_name.' '.$maker_details->last_name;

                        $arr_notify_data['title']        = 'Order Confirmed';
                        $arr_notify_data['type']         = 'admin';  
                        $arr_notify_data['link']         = $view_href;  

                        $this->GeneralService->save_notification($arr_notify_data);


                                

                                        
                        /* get user mail details */
                            $user_details = \Sentinel::findById($order_details['maker_id']);

                            $user_email = isset($user_details->email)?$user_details->email:false;

                            if($user_email != false)
                            {

                                $credentials = ['email' => $user_email];
                            $arr_user = get_user_by_credentials($credentials);

                        
                                /*call to dynamic function send_mail*/
                                $arr_site_setting = get_site_settings(['site_name','website_url']);

                                $arr_built_content = 
                                    [
                                        'credit_amount'     => num_format($order_details['total_wholesale_price']),
                                    'order_no'          => $order_details['order_no'],
                                    'PROJECT_NAME'      => $arr_site_setting['site_name'],                                              
                                ];

                            $arr_mail_data                      = [];
                            $arr_mail_data['email_template_id'] = '84';
                            $arr_mail_data['arr_built_content'] = $arr_built_content;
                            $arr_mail_data['arr_user']          = $arr_user;
                                /*end*/



                            /* send mail to vendor for order amount is credited on your acount */

                            $email_status  = $this->EmailService->send_mail($arr_mail_data);

                      

                            // $is_mail_sent = $this->EmailService->amount_credited_email(num_format($order_details['total_wholesale_price']),$order_details['order_no'],$user_email,false);

                            }                                   


                            /* Send mail to retailer */
                            $hide_vendor_address_from_retailer = 1;
                            $retailer_mail = $this->HelperService->get_user_mail($order_details['retailer_id']);
                            
                            $this->send_mail($retailer_mail,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment,$hide_vendor_address_from_retailer);
                    

                            /*send the mail to admin*/
                            
                            $admin_email = 0;

                            $admin_details = $this->UserModel->where('id',1)->first();

                            if(isset($admin_details))
                            {
                               $admin_email = $admin_details->email;

                            }

                            $this->send_mail($admin_email,$order_details['quotes_details'],$order_details['order_no'],$order_details['retailer_id'],isset($charge_status)?$charge_status:'Pending',$loggedInUserId,$order_details['id'],$isDirectPayment);



                          
                            /* send notification and mail to the vendor and admin
                             for insufficient inventory after confirm the order*/  
                             
                             
                            /* get all product list from order id*/
                            $product_inventory_arr = [];

                            $order_product_details = $this->RetailerQuotesProductModel
                                                        ->with(['product_details'])
                                                        ->where('retailer_quotes_id',$order_id)
                                                        ->get()
                                                        ->toArray();
                          

                            if(isset($order_product_details) && count($order_product_details)>0)
                            {
                                foreach($order_product_details as $key => $product) 
                                {  
                                    // check inventory
                                    $inventory = $this->ProductInventoryModel
                                                      ->where('product_id',$product['product_id'])
                                                      ->where('sku_no',$product['sku_no'])
                                                      ->pluck('quantity')
                                                      ->first();

                                    if($inventory <=200)
                                    {
                                        $product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                                        $product_inventory_arr[$key]['sku_no']= $product['sku_no'];

                                        $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                                    }                 
                                

                                }
                            }
                             
                           
                            if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                            {
                                 $key1 = '';
                                //send vendor notification
                                $admin_id  = get_admin_id();
                                $arr_notify_data                 = [];
                                $arr_notify_data['from_user_id'] = $admin_id or '';

                                $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
                                $html = '';
                                $html= "Following products having insufficient inventory: ";

                                foreach ($product_inventory_arr as $key => $value) {

                                if($key == 0)
                                {
                                   $key1 = 1;
                                }
                                else
                                {
                                  $key1 = $key+1;
                                }

                                $html.= '  '.$key1.') Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
                                       ' and Available Stock : '.$value['inventory_stock'].'  ';
                                }

                                $arr_notify_data['description']  = $html;
                                $arr_notify_data['title']        = 'Insufficient Inventory';
                                $arr_notify_data['type']         = 'maker';  
                                $arr_notify_data['link']         = '';  

                                $this->GeneralService->save_notification($arr_notify_data);
    

                            }

                            /*send email to the vendor*/
                            $maker_id = '';

                            $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                            $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                            $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'retailer_order'); 

                            $email_status  = $this->EmailService->send_mail($arr_mail_data);
                          
                            /*---------------------------------------------------*/ 
     
                                    $response['status']      = 'success';
                                    $response['description'] = 'Order has been confirmed successfully.';
                                    return $response;
                                }
                                else
                                {
                                    $response['status']      = 'warning';
                                    $response['description'] = 'Something went wrong, please try again.';
                                    return $response;
                                }
                            }
                            else{
                                 
                                    $response['status']      = 'warning';
                                    $response['description'] = 'Something went wrong, please try again.';
                                    return $response;
                                }   
                        
                        
                            
                    }
                    
                else
                {
                    
                    /* Split Order */
                    // dd($arr_available_qty_by_skno,$arr_requested_qty_by_skno);

                    $is_quantity_available = array_filter($arr_available_qty_by_skno);
                    

                    if (empty($is_quantity_available)) 
                    {
                        /*Product stock is not available*/
                        $response['status']      = 'warning';
                        $response['description'] = 'Oops..! Stock is not available currently, please update quantity of product.';
                        return $response;
                        
                    }
                    

                    $order_arr = $order_arr['fulfill'] = [];

                    // $partial_order_no = str_pad('J2',  10, rand('1234567890',10)); 
                    // $fulfill_order_no = str_pad('J2',  10, rand('1234567890',10)); 

                    $partial_order_no = $this->orderDataService->generate_order_no($order_details['order_no']);
                    $fulfill_order_no = $this->orderDataService->generate_order_no($partial_order_no);

                    
                    /* Split the quantity of product*/

                    
                    foreach ($order_data as $key => $product_arr) 
                    {
                        $order_arr = $product_arr;
            
                        if (sizeof($product_arr['quotes_details']) > 0) 
                        {
                            foreach ($product_arr['quotes_details'] as $key => $product) 
                            {
                                foreach ($arr_available_qty_by_skno as $availble_qty_key => $available_qty_by_skno) 
                                {
                                    if ($product['sku_no'] == $availble_qty_key) 
                                    {
                                        if ($product['qty'] > $available_qty_by_skno) 
                                        {
                                            
                                                /*
                                                #If product have maximum quantity than available quantity
                                                    # split product 1) Fulfill the quantity requirement
                                                2) Send remaining quantity in partial order     
                                               */

                                                if($available_qty_by_skno == 0)
                                                {
                                                    /*Product have 0 stock */

                                                    $remainig_product_qty = $product['qty'] - $available_qty_by_skno;
                                                    $total_price = $remainig_product_qty * $product['unit_wholsale_price'];

                                                    
                                                    if(count($remainig_product_qty) <= 0)
                                                    {
                                                        $remainig_product_qty = 0;
                                                    }

                                                    $order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

                                                    $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

                                                    $order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


                                                    $order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
                                                    $order_arr['partial'][$key]['qty']  = $remainig_product_qty or '';
                                                    $order_arr['partial'][$key]['unit_price']   = $product['unit_wholsale_price'];
                                                    $order_arr['partial'][$key]['total_price']  = isset($total_price)?$total_price:0;

                                                    $product_discount = $this->product_discount($product['product_id'], $order_arr['partial'][$key]['qty']);


                                                        /* if maker has set split_order_free_shipping */
                                                
                                                    if(isset($split_order_free_shipping) && $split_order_free_shipping == 1)
                                                    {
                                                        $order_arr['partial'][$key]['shipping_charges']  = 0.00;
                                                        $order_arr['partial'][$key]['shipping_discount'] = 0.00;
                                                    }
                                                    else
                                                    {                                                   
                                                        $order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

                                                        $order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;
                                                    }
                                                    
                                                    $order_arr['partial'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0; 
                                                }
                                                else
                                                {

                                                    $remainig_product_qty = $product['qty'] - $available_qty_by_skno;

                                                    $total_price = $remainig_product_qty * $product['unit_wholsale_price'];


                                                    if(count($remainig_product_qty) <= 0)
                                                    {
                                                        $remainig_product_qty = 0;
                                                    }
                                                    
                                                    $order_arr['partial'][$key]['product_id'] = $product['product_id'] or '';

                                                    $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

                                                    $order_arr['partial'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


                                                    $order_arr['partial'][$key]['sku_no'] = $product['sku_no'] or '';
                                                    $order_arr['partial'][$key]['qty']  = $remainig_product_qty or '';
                                                    $order_arr['partial'][$key]['unit_price']   = $product['unit_wholsale_price'];
                                                    $order_arr['partial'][$key]['total_price']  = isset($total_price)?$total_price:0;

                                                    $product_discount = $this->product_discount($product['product_id'], $order_arr['partial'][$key]['qty']);


                                                    /* if maker has set split_order_free_shipping */
                                                
                                                    if(isset($split_order_free_shipping) && $split_order_free_shipping == 1)
                                                    {
                                                        $order_arr['partial'][$key]['shipping_charges']  = 0.00;
                                                        $order_arr['partial'][$key]['shipping_discount'] = 0.00;
                                                    }
                                                    else
                                                    {   
                                                        $order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
                                                        
                                                        $order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;
                                                    }

                                                    /*$order_arr['partial'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;
                                                    $order_arr['partial'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?$product_discount['shipping_discount']:0;*/

                                                    $order_arr['partial'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0.00;      



                                                    
                                                    $order_arr['fulfill'][$key]['product_id']   = $product['product_id'] or '';

                                                    $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

                                                    $order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


                                                    $order_arr['fulfill'][$key]['sku_no']       = $product['sku_no'] or '';
                                                    $order_arr['fulfill'][$key]['qty']      = $available_qty_by_skno or '';
                                                    $order_arr['fulfill'][$key]['unit_price']   = $product['unit_wholsale_price'];


                                                    $product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);


                                                    $order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0.00;

                                                    $order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

                                                    $order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0.00;

                                                    $arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);

                                                    // $total_price = $available_qty_by_skno * $product['unit_wholsale_price'];

                                                    $total_price = $available_qty_by_skno * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['product_discount']-$order_arr['fulfill'][$key]['shipping_discount'];

                                                
                                                    $order_arr['fulfill'][$key]['total_price']  = isset($total_price)?$total_price:0;
                                                }
                                        }                                       
                                        else
                                        {




                                            /***********************************************************/
                                            $order_arr['fulfill'][$key]['product_id']   = $product['product_id'] or '';

                                            $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

                                            $order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';


                                            $order_arr['fulfill'][$key]['sku_no']       = $product['sku_no'] or '';
                                            //$order_arr['fulfill'][$key]['qty']        = $available_qty_by_skno or '';

                                            $order_arr['fulfill'][$key]['qty']          = $product['qty'] or '';
                                            $order_arr['fulfill'][$key]['unit_price']   = $product['unit_wholsale_price'];


                                            $product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

                                            $order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

                                            $order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

                                            $order_arr['fulfill'][$key]['product_discount'] = isset($product_discount['product_discount'])?$product_discount['product_discount']:0;

                                            $arr_requested_qty_by_skno[$product['sku_no']] = intval($product['qty']);

                                            // $total_price = $available_qty_by_skno * $product['unit_wholsale_price'];

                                            $total_price = $available_qty_by_skno * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['product_discount']- $order_arr['fulfill'][$key]['shipping_discount'];

                                            
                                            $order_arr['fulfill'][$key]['total_price']  = isset($total_price)?$total_price:0;

                                            /******************************* End ***********************************************/







                                            $product_discount = $this->product_discount($product['product_id'], $order_arr['fulfill'][$key]['qty']);

                                            $order_arr['fulfill'][$key]['shipping_charges'] = isset($product_discount['shipping_charges'])?$product_discount['shipping_charges']:0;

                                            $order_arr['fulfill'][$key]['shipping_discount'] = isset($product_discount['shipping_discount'])?num_format($product_discount['shipping_discount']):0.00;

                                            $order_arr['fulfill'][$key]['product_discount'] = (isset($product_discount['product_discount']))?$product_discount['product_discount']:0;
                                            /*
                                                If product have full quantity
                                                */
                                            /*$total_price = $product['qty'] * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['shipping_discount']-$order_arr['fulfill'][$key]['product_discount'];  */

                                            $total_price = $product['qty'] * $product['unit_wholsale_price'] + $order_arr['fulfill'][$key]['shipping_charges']-$order_arr['fulfill'][$key]['shipping_discount'] -$order_arr['fulfill'][$key]['product_discount'];

                                            
                                            //$total_price = $product['qty'] * $product['unit_wholsale_price']; 

                                            
                                            $order_arr['fulfill'][$key]['product_id']   = $product['product_id'] or '';

                                            $sku_no = isset($product['sku_no'])?$product['sku_no']:"-";

                                            $order_arr['fulfill'][$key]['product_name'] = isset($product['product_details']['product_name'])?$product['product_details']['product_name'].' (SKU: '.$sku_no.')':'';

                                            $order_arr['fulfill'][$key]['sku_no']   = $product['sku_no'] or '';
                                            $order_arr['fulfill'][$key]['qty']      = $product['qty'] or '';

                                            $order_arr['fulfill'][$key]['unit_price']  = $product['unit_wholsale_price'];

                                            $order_arr['fulfill'][$key]['total_price'] = isset($total_price)?$total_price:0;
                                        }   
                                    }                                   
                                }
                            }
                        }   
                    }



                    /*Store split order record */

                    $fulfill_final_total_arr = $partial_final_total_arr = [];

                    $promotion_free_shipping = $get_discount_amt = 0;

                    if($order_arr) {

                        /* get parent order payment type */
                        $ordPaymentMethod = $this->RetailerQuotesModel->where('id',$order_details['id'])
                                                                      ->first(['is_direct_payment','admin_commission']);
                        

                        /*Update Order status to split order*/

                        $this->RetailerQuotesModel->where('id',$order_details['id'])->update(['is_split_order'=>'1']);



                        //get parent order details
                        $retailer_order_arr = $promotion_arr = [];

                        $order_details_obj = $this->RetailerQuotesModel->with(['quotes_details'])->where('id',$order_details['id'])->first();

                        if(isset($order_details_obj))
                        {
                           $retailer_order_arr = $order_details_obj->toArray();
                        }




                        /*Store fulfill order record*/

                        if (isset($order_arr['fulfill']) && sizeof($order_arr['fulfill']) > 0)
                        {
                            $order_arr['fulfill'] = array_values($order_arr['fulfill']);


                            $total_wholesale_price = $total_retail_price = 0;
                            $pro_ship_charge = 0;
                            $pro_ship_dis = 0;
                            $pro_dis = 0;

                            $total_retail_price = array_sum(array_column($order_arr['fulfill'],'total_price'));
                            $total_wholesale_price = array_sum(array_column($order_arr['fulfill'],'total_price'));

                            $pro_ship_charge = array_sum(array_column($order_arr['fulfill'],'shipping_charges'));
                            $pro_ship_dis = array_sum(array_column($order_arr['fulfill'],'shipping_discount'));
                            $pro_dis =array_sum(array_column($order_arr['fulfill'],'product_discount'));


                            
                            $total_retail_price = $total_retail_price+$pro_ship_charge-$pro_dis;

                            $arr_order['order_no'] = $fulfill_order_no;
                            $arr_order['is_direct_payment'] = isset($ordPaymentMethod->is_direct_payment)?$ordPaymentMethod->is_direct_payment:0;
                            $arr_order['maker_id'] = $order_arr['maker_id'];
                            $arr_order['retailer_id'] = $order_arr['retailer_id'];
                            $arr_order['transaction_id'] = $order_arr['transaction_id'];
                            $arr_order['total_retail_price'] = $total_retail_price;
                            $arr_order['total_wholesale_price'] = $total_wholesale_price;
                            $arr_order['shipping_addr'] = $order_arr['shipping_addr'];
                            $arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
                            $arr_order['billing_addr'] = $order_arr['billing_addr'];
                            $arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
                            $arr_order['payment_term'] = $order_arr['payment_term'];
                            $arr_order['split_order_id'] = $order_arr['id'];
                            
                            $arr_order['ship_contact_no'] = $order_arr['user_details']['retailer_details']['ship_contact_no'];
                            $arr_order['bill_contact_no'] = $order_arr['user_details']['retailer_details']['bill_contact_no'];

                            // $arr_order['admin_commission']      = $this->CommissionService->get_admin_commission($order_arr['maker_id']);

                            $arr_order['admin_commission']    = isset($ordPaymentMethod['admin_commission'])?$ordPaymentMethod['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);



                            
                            $create_quotes = $this->RetailerQuotesModel->create($arr_order);

                            foreach ($order_arr['fulfill'] as $key => $product) {

                            $quote_product_arr = [];
                            

                            $quote_product_arr['retailer_quotes_id'] = $create_quotes->id;
                            $quote_product_arr['product_id']         = $product['product_id'];
                            $quote_product_arr['sku_no']             = $product['sku_no'];
                            $quote_product_arr['qty']                = $product['qty'];
                            $quote_product_arr['retail_price']      = $product['unit_price'];
                            $quote_product_arr['unit_retail_price'] = $product['unit_price'];
                            $quote_product_arr['unit_wholsale_price'] = $product['unit_price'];
                            $quote_product_arr['wholesale_price']       = $product['qty']*$product['unit_price'];
                            $quote_product_arr['description']        = '';


                            $quote_product_arr['shipping_discount']  = $product['shipping_discount'];


                            //$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$quote_product_arr['shipping_discount'];

                            $quote_product_arr['shipping_charge']   = $product['shipping_charges'];

                            $quote_product_arr['product_discount']  = $product['product_discount'];
                            
                            $grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 

                            $quote_product_arr['wholesale_price']    = $grand_total;

                            $create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
                            }

                            $payment_status = $this->payment_process($arr_order);
                                               
                                                            
                            if ($payment_status['status'] == 'success') {
     
                                /*reduce the quantity of sku*/
                                $this->InventoryService->batch_sku_update_quantity($arr_requested_qty_by_skno);

                            }
                        }
            

                        /* Store partial order record */
                        if (isset($order_arr['partial']) && sizeof($order_arr['partial']) > 0) 
                        {

                            $order_arr['partial'] = array_values($order_arr['partial']);

                            $total_wholesale_price = $total_retail_price = 0;
                            $pro_ship_charge = 0;
                            $pro_ship_dis    = 0;
                            $pro_dis         = 0;

                            $total_retail_price = array_sum(array_column($order_arr['partial'],'total_price'));
                            $total_wholesale_price = array_sum(array_column($order_arr['partial'],'total_price'));

                            $pro_ship_charge = array_sum(array_column($order_arr['partial'],'shipping_charges'));
                            $pro_ship_dis = array_sum(array_column($order_arr['partial'],'shipping_discount'));
                            $pro_dis =array_sum(array_column($order_arr['partial'],'product_discount'));

                            $total_wholesale_price = $total_wholesale_price+$pro_ship_charge-$pro_dis-$pro_ship_dis;
                            
                            $arr_order['order_no'] = $partial_order_no;
                            $arr_order['is_direct_payment'] = isset($ordPaymentMethod->is_direct_payment)?$ordPaymentMethod->is_direct_payment:0;
                            $arr_order['maker_id'] = $order_arr['maker_id'];
                            $arr_order['retailer_id'] = $order_arr['retailer_id'];
                            $arr_order['transaction_id'] = $order_arr['transaction_id'];
                            $arr_order['total_retail_price'] = $total_wholesale_price;
                            $arr_order['total_wholesale_price'] = $total_wholesale_price;
                            $arr_order['shipping_addr'] = $order_arr['shipping_addr'];
                            $arr_order['shipping_addr_zip_code'] = $order_arr['shipping_addr_zip_code'];
                            $arr_order['billing_addr'] = $order_arr['billing_addr'];
                            $arr_order['billing_addr_zip_code'] = $order_arr['billing_addr_zip_code'];
                            $arr_order['payment_term'] = $order_arr['payment_term'];
                            $arr_order['split_order_id'] = $order_arr['id'];

                            $arr_order['ship_contact_no'] = $order_arr['user_details']['retailer_details']['ship_contact_no'];
                            $arr_order['bill_contact_no'] = $order_arr['user_details']['retailer_details']['bill_contact_no'];

                            // $arr_order['admin_commission']      = $this->CommissionService->get_admin_commission($order_arr['maker_id']);                
                            $arr_order['admin_commission']      = isset($ordPaymentMethod['admin_commission'])?$ordPaymentMethod['admin_commission']:$this->CommissionService->get_admin_commission($order_arr['maker_id']);  






                            $create_quote = $this->RetailerQuotesModel->create($arr_order);

                            foreach ($order_arr['partial'] as $key => $product) {
                            
                            $quote_product_arr = [];
                            

                            $quote_product_arr['retailer_quotes_id'] = $create_quote->id;
                            $quote_product_arr['product_id']         = $product['product_id'];
                            $quote_product_arr['sku_no']             = $product['sku_no'];
                            $quote_product_arr['qty']                = $product['qty'];
                            $quote_product_arr['retail_price']       = $product['unit_price'];
                            $quote_product_arr['unit_wholsale_price']= $product['unit_price'];
                            $quote_product_arr['wholesale_price']    = $product['qty']*$product['unit_price'];
                            $quote_product_arr['description']          = '';


                            /*$quote_product_arr['shipping_charge']    = $product['shipping_charges']+$product['shipping_discount'];
*/
                            $quote_product_arr['shipping_charge'] = $product['shipping_charges'];

                            $quote_product_arr['shipping_discount']  = $product['shipping_discount'];

                            $quote_product_arr['product_discount']  = $product['product_discount'];
                            
                            $grand_total = $product['total_price'] + $product['shipping_charges']-$quote_product_arr['shipping_discount']-$product['product_discount']; 
                        
                            $quote_product_arr['wholesale_price']    = $grand_total;

                            
                            
                            $create_quote_product = $this->RetailerQuotesProductModel->create($quote_product_arr);
                            }

                            if(empty($order_arr['fulfill']))
                            {
                                $main_order_status_update = $this->RetailerQuotesModel->where('id',$order_arr['id'])
                                                                     ->where('maker_id',$order_arr['maker_id'])

                                                                     ->update(['maker_confirmation' => '1']);
                            }
                        }

                    /*send the mail to retailer*/

                    $retailer_email_id = $this->HelperService->get_user_mail($order_arr['retailer_id']);
                      
                    $email_status      = $this->send_split_order_mail($order_arr,$retailer_email_id,$partial_order_no,$fulfill_order_no);

                    /*-------------------------------------------------------------*/

                    /*send the mail to admin*/
                    $admin_id  = get_admin_id();
                    $admin_email = 0;

                    $admin_details = $this->UserModel->where('id',1)->first();

                    if(isset($admin_details))
                    {
                       $admin_email = $admin_details->email;
                    }


                    $email_status = $this->send_split_order_mail($order_arr,$admin_email,$partial_order_no,$fulfill_order_no);

                    
                    /* send notification and mail to the vendor and admin
                    for insufficient inventory after confirm the order*/  
                                     
                                     
                    /* get all product list from order id*/
                    $product_inventory_arr = [];

                    $order_product_details = $this->RetailerQuotesProductModel
                                                ->with(['product_details'])
                                                ->where('retailer_quotes_id',$order_id)
                                                ->get()
                                                ->toArray();
                  

                    if(isset($order_product_details) && count($order_product_details)>0)
                    {
                        foreach($order_product_details as $key => $product) 
                        {  
                            // check inventory
                            $inventory = $this->ProductInventoryModel
                                              ->where('product_id',$product['product_id'])
                                              ->where('sku_no',$product['sku_no'])
                                              ->pluck('quantity')
                                              ->first();

                            if($inventory <=200)
                            {
                                $product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                                $product_inventory_arr[$key]['sku_no']= $product['sku_no'];

                                $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                            }                 
                        

                        }
                    }
                     
                   
                    if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                    {
                        $key1 = '';
                        //send vendor notification
                        $admin_id  = get_admin_id();
                       
                        $arr_notify_data                 = [];
                        $arr_notify_data['from_user_id'] = $admin_id or '';

                        $arr_notify_data['to_user_id']   = $order_details['maker_id'] or '';
                        $html = '';
                        $html= "Following products having insufficient inventory: ";

                        foreach ($product_inventory_arr as $key => $value) {

                        if($key == 0)
                        {
                           $key1 = 1;
                        }
                        else
                        {
                          $key1 = $key+1;
                        }

                        $html.= '  '.$key1.') Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
                               ' and Available Stock : '.$value['inventory_stock'].'  ';
                        }

                        $arr_notify_data['description']  = $html;
                        $arr_notify_data['title']        = 'Insufficient Inventory';
                        $arr_notify_data['type']         = 'maker';  
                        $arr_notify_data['link']         = '';  

                        $this->GeneralService->save_notification($arr_notify_data);
                    }

                    /*send email to the vendor*/
                    $maker_id = '';

                    $maker_id = isset($order_details['maker_id'])?$order_details['maker_id']:'';

                    $vendor_email = $this->UserModel->where('id',$maker_id)->pluck('email')->first();

                    $arr_mail_data = $this->mailForInsufficientInventory($vendor_email,$order_id,'retailer_order'); 

                    $email_status  = $this->EmailService->send_mail($arr_mail_data);
                      

                   /*------------------------------------------------------*/



                    $response['status']      = 'success';
                    $response['description'] = 'Order is split because of inadequate inventory to fulfill.';
                    return $response;
                        
                    }
                    $response['status']      = 'warning';
                    $response['description'] = 'Something went wrong, please try again.';
                    return $response;
                }
                
                if($out_of_stock_details && count($out_of_stock_details)>0)             
                {

                    $response['status']      = 'warning';
                    $response['description'] = '';
                    return $response;
                }


            }
            else
            {
                $response['status']      = 'warning';
                $response['description'] = 'Something went wrong, please try again.';
                return $response;
            
            }
            
        
    }


    public function payment_proof($order_id,Request $request)
    {
       
        $order_id = base64_decode($order_id);
       
        $arr_rules = [
            'payment_proof' => 'required'

        ];

        $validator = Validator::make($request->all(), $arr_rules);

        if ($validator->fails()) {
            $response['status'] = 'warning';
            $response['description'] = 'Form validations failed, please check form fields.';

            return response()->json($response);
        }


        if($request->hasfile('payment_proof')) 
        {
   
              $file = $request->file('payment_proof')->getClientOriginalName();
              $fileName = pathinfo($file,PATHINFO_FILENAME);
              $file_extension =strtolower($request->file('payment_proof')->getClientOriginalExtension()); 
              if(in_array($file_extension,['jpg','jpeg','png','JPG','PNG','JPEG']))
              {                           
                  $file = date('mdYHis') . uniqid() .'.'.$file_extension;
                  $request->file('payment_proof')->move($this->payment_proof.'payments_proof/', $file);
              }
        } 

        $payment_proof = $this->RetailerQuotesModel->firstOrNew(['id'=>$order_id]);
        $payment_proof->payment_proof = $file;
        
        if($payment_proof->save())
        {
            
           $response = $this->payment(base64_encode($order_id));
           $data['status'] = $response['status'];
           $data['description'] = $response['description']; 
           return $data;
         
        }

            
    }


   public function send_mail($to_mail_id=false,$arr_product,$order_no,$user_id,$charge_status=false,$loggedInUserId,$order_id=false,$isDirectPayment=false,$hide_vendor_address_from_retailer = false)
    {

        $temp_data = isset($arr_product['leads_details'])?$arr_product['leads_details']:$arr_product;

        /* Build array of mail data */
            foreach($temp_data as $key => $product)
            {
                $mail_data[$user_id][$key]['unit_price']       = isset($product['unit_wholsale_price'])?$product['unit_wholsale_price']:0.00;

                
                $mail_data[$user_id][$key]['product_id']       = isset($product['product_id'])?$product['product_id']:0;

                $mail_data[$user_id][$key]['item_qty']         = isset($product['qty'])?$product['qty']:0.00;

                $mail_data[$user_id][$key]['color']         = isset($product['color'])?$product['color']:'';

                $mail_data[$user_id][$key]['size_id']         = isset($product['size_id'])?$product['size_id']:'';
                
                $mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

                $prod_whole_price = $mail_data[$user_id][$key]['unit_price'] * $mail_data[$user_id][$key]['item_qty'];

                $mail_data[$user_id][$key]['total_wholesale_price'] = isset($product['wholesale_price'])?$product['wholesale_price']:$product['wholesale_price'];

                $mail_data[$user_id][$key]['wholesale_price']  = isset($prod_whole_price)?$prod_whole_price:0.00;

                
                $prod_ship_charge = isset($product['shipping_charge'])?$product['shipping_charge']:0.00;

                $prod_ship_disc = isset($product['shipping_discount'])?$product['shipping_discount']:0.00;

                $mail_data[$user_id][$key]['product_discount'] = isset($product['product_discount'])?$product['product_discount']:0.00;

                $mail_data[$user_id][$key]['shipping_charges'] = isset($product['shipping_charges'])?$product['shipping_charges']:$prod_ship_charge;

                $mail_data[$user_id][$key]['shipping_discount'] = isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:$prod_ship_disc;

                $mail_data[$user_id][$key]['sku_no'] = isset($product['sku_no'])?$product['sku_no']:$product['sku'];
            }
        
            /* sending mail */

            if($charge_status == 'succeeded')
            {
                $charge_status = 'Paid';
            }
            
            $email_status = $this->GeneralService->send_mail($to_mail_id,$mail_data,$order_no,$charge_status,$loggedInUserId,$order_id,$isDirectPayment,$hide_vendor_address_from_retailer);
            
        return $email_status;               
    }

    public function mailForInsufficientInventory($email,$order_id,$status)
    {   

        $product_inventory_arr = [];

        $user = $this->get_user_details($email);
        
        if(isset($user) && $user)
        {
            $arr_user = $user->toArray();  
            $html     = '';
           
            /* get all product list from order id*/
            
            if(isset($status) && $status == 'retailer_order')
            {
                $order_product_details =  $this->RetailerQuotesProductModel
                                               ->with(['product_details'])
                                               ->where('retailer_quotes_id',$order_id)
                                               ->get()
                                               ->toArray();


                if(isset($order_product_details) && count($order_product_details)>0)
                {
                    foreach($order_product_details as $key => $product) 
                    {  
                        // check inventory
                        $inventory = $this->ProductInventoryModel
                                          ->where('product_id',$product['product_id'])
                                          ->where('sku_no',$product['sku_no'])
                                          ->pluck('quantity')
                                          ->first();

                        if($inventory <=200)
                        {
                            $product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];

                            $product_inventory_arr[$key]['sku_no']= $product['sku_no'];

                            $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                        }                 
                    

                    }
                }
                                     
                if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                {
                   
                    $html = '';
                    $html = "Following products having insufficient inventory:<br> ";
                    $key1 = '';

                    foreach ($product_inventory_arr as $key => $value)
                    {

                        if($key == 0)
                        {
                           $key1 = 1;
                        }
                        else
                        {
                            $key1 = $key+1;
                        }

                        $html.= $key1.') Product Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
                               '  and Available Stock : '.$value['inventory_stock'].'</br>';
                    }
                    
                    
                    $arr_site_setting = get_site_settings(['site_name','website_url']);

                    $arr_built_content = ['USER_NAME'       => $arr_user['first_name'],
                                          'APP_NAME'        => $arr_site_setting['site_name'],
                                          'HTML'            => $html,
                                          'SITE_URL'        => $arr_site_setting['website_url']
                                        ];

                    $arr_mail_data                      = [];
                    $arr_mail_data['email_template_id'] = '51';
                    $arr_mail_data['arr_built_content'] = $arr_built_content;
                    $arr_mail_data['arr_user']          = $arr_user;

                    return $arr_mail_data;
                }
                else
                {
                    $arr_mail_data = [];
                    return $arr_mail_data;
                }
            }  
            else if(isset($status) && $status == 'rep_sales_order')
            {

                $order_product_details = $this->RepresentativeProductLeadsModel->with(['product_details'])
                                                ->where('representative_leads_id',$order_id)
                                                ->get()
                                                ->toArray();


                if(isset($order_product_details) && count($order_product_details)>0)
                {
                    foreach($order_product_details as $key => $product) 
                    {  
                        // check inventory
                        $inventory = $this->ProductInventoryModel
                                          ->where('product_id',$product['product_id'])
                                          ->where('sku_no',$product['sku'])
                                          ->pluck('quantity')
                                          ->first();

                        if($inventory <=200)
                        {
                            $product_inventory_arr[$key]['product_name']= $product['product_details']['product_name'];
                            $product_inventory_arr[$key]['sku_no']= $product['sku'];
                            $product_inventory_arr[$key]['inventory_stock'] = $inventory;
                        }                 
                    

                    }
                }
                                 
                if(isset($product_inventory_arr) && count($product_inventory_arr)>0)
                {
                    $html = '';
                    $html = "Following products having insufficient inventory:<br> ";
                    $key1 = '';
                    foreach ($product_inventory_arr as $key => $value)
                    {

                        if($key == 0)
                        {
                           $key1 = 1;
                        }
                        else
                        {
                           $key1 = $key+1;
                        }

                         $html.= $key1.') Product Name : '.$value['product_name'].' - SKU:('.$value['sku_no'].')'.
                               '  and Available Stock : '.$value['inventory_stock'].'</br>';
                    }

                    $arr_site_setting = get_site_settings(['site_name','website_url']);


                    $arr_built_content = ['USER_NAME'       => $arr_user['first_name'],
                                          'APP_NAME'        => $arr_site_setting['site_name'],
                                          'HTML'            => $html];

                    $arr_mail_data                      = [];
                    $arr_mail_data['email_template_id'] = '51';
                    $arr_mail_data['arr_built_content'] = $arr_built_content;
                    $arr_mail_data['user']              = $arr_user;
                    $arr_mail_data['arr_user']          = $arr_user;

                    return $arr_mail_data;
                
                } 
                else
                {
                    $arr_mail_data = [];
                    return $arr_mail_data;
                }                 
            }
        }    

        return false;
    }

     public function  get_user_details($email)
    {
        $credentials = ['email' => $email];
        $user = Sentinel::findByCredentials($credentials); // check if user exists

        if($user)
        {
          return $user;
        }
        return false;
    }

   
}
