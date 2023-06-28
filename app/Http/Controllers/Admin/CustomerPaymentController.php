<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\StripePaymentService;
use App\Common\Services\GeneralService;
use App\Common\Services\HelperService;
use App\Models\StripeAccountDetailsModel;
use App\Models\StripeTransactionModel;
use App\Models\CustomerQuotesModel;
use Cartalyst\Stripe\Stripe;
use Stripe\Error\Card;
use Session;
use Sentinel;

 
class CustomerPaymentController extends Controller
{
    public function __construct(StripePaymentService $StripePaymentService,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                StripeTransactionModel $StripeTransactionModel,
                                CustomerQuotesModel $CustomerQuotesModel,
                                GeneralService $GeneralService,
                                HelperService $HelperService
                                )
    {
    	$this->arr_view_data 	  = [];
    	$this->module_title       = "Payment";
        $this->module_view_folder = "admin.vendor_payment";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/vendor");    
        $this->curr_panel_slug    =  config('app.project.admin_panel_slug');
    	$this->role = 'retailer';
        $this->StripeAccountDetailsModel  = $StripeAccountDetailsModel;
        $this->StripeTransactionModel     = $StripeTransactionModel;
        $this->CustomerQuotesModel        = $CustomerQuotesModel;
        $this->StripePaymentService       = $StripePaymentService;
        $this->HelperService              = $HelperService;
        $this->GeneralService             = $GeneralService;
    }

    public function pay_to_vendor(Request $request)
    {
        $form_data = $request->all();
        //dd($form_data);
        $maker_id = isset($form_data['maker_id'])?$form_data['maker_id']:false;
        $order_id = isset($form_data['order_id'])?$form_data['order_id']:false;

         /*  Get admin stripe secret key id  */
        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key(get_admin_id());

        $stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        /*  Get maker stripe secret key id  */
        $vendorStripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($maker_id);

        $vendorStripKeyId = isset($vendorStripeApiKeyData['id'])?$vendorStripeApiKeyData['id']:'';

        // $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$maker_id)->first();

        $obj_stripe_account_details = $this->StripeAccountDetailsModel->where('user_id',$maker_id)
                                                              ->where('admin_stripe_key_id',$stripKeyId)
                                                              ->where('vendor_stripe_key_id',$vendorStripKeyId)
                                                              ->first(); 
        
              
        if($obj_stripe_account_details)
        {
            $stripe_acc_id = isset($obj_stripe_account_details->stripe_acc_id)?$obj_stripe_account_details->stripe_acc_id:false;
            
            if($stripe_acc_id)
            {
                $order_data = get_customer_order_data($order_id);

                 if(isset($order_data['maker_commission_status']) && $order_data['maker_commission_status'] == '1')
                {
                    $response['status']  = 'pay-warning';
                    $response['message'] = "Payment already completed for this order.";
                    
                   return response()->json($response);
                }
                
                $arr_transfer_data['amount']      = $form_data['amount'];
                $arr_transfer_data['account_id']  = $stripe_acc_id;
                $arr_transfer_data['description'] = 'Payment for Order No:'.isset($order_data['order_no'])?$order_data['order_no']:false;
                // dd($arr_transfer_data);
                $transfer_response = $this->StripePaymentService->create_transfer($arr_transfer_data);

                if(isset($transfer_response['status']) && $transfer_response['status'] == 'Error')
                {
                    $response['status'] = 'error';
                    $response['message'] = isset($transfer_response['description'])?$transfer_response['description']:'Something went wrong, please try again.';
                    return response()->json($response);
                }

                if($transfer_response)
                {   

                    $arr_data['customer_order_id']         = $form_data['order_id'];
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
                        // $this->CustomerQuotesModel->where('id',$order_id)
                        //                           ->update(['maker_commission_status'           => '1',
                        //                                     'received_commission_stripe_key_id' => $vendorStripKeyId,
                        //                                     'transfer_commission_stripe_key_id' => $stripKeyId]);

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

                            $view_href = url('/').'/vendor/customer_orders/view/'.base64_encode($order_id);

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

                        $notification_arr['description']  = 'Commission ($'.$form_data['amount'].') is received successfully of Order No: '.$order_data['order_no'];

                        $notification_arr['title']        = 'Commission Received';
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

                        $arrMailData['customer_order_data'][] = $order_data;
                        $arrUserData['business_details'] = $arr_user;
                        $arrUserData['personla_details'] = $arr_user;


                        $arrOrderAmount = [];

                        //$email_status  = $this->EmailService->send_mail($arr_mail_data);

                        /*  ------ Send payment Invoice to user -----------------  */
                          $invoice_data = $this->GeneralService->send_payment_invoice($arr_mail_data,$arrMailData,$arrUserData,$bulk_transaction_key,$arrOrderAmount);
                          /*  ----------- END ------------  */




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
            $arr_site_setting = get_site_settings(['site_name','website_url']);
            $response['status']  = 'warning'; 
            $response['message'] = "This user are not connected to ".$arr_site_setting['site_name']."'s stripe account";
            $response['user_id'] = $maker_id;
            
            return response()->json($response);
        }
    }
}
