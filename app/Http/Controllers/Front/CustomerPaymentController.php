<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GeneralSettingModel;
use App\Models\TransactionsDetailsModel;
use App\Models\TempBagModel;
use App\Models\UserModel;
use App\Models\CardModel;
use App\Models\CountryModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RetailerModel;
use App\Models\CustomerModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Common\Services\StripePaymentService;
use App\Common\Services\orderDataService;
use App\Common\Services\GeneralService;
use App\Common\Services\CustomerOrderService;
use App\Common\Services\EmailService;
use App\Common\Services\ProductService;
use App\Common\Services\HelperService;

use Validator;
use URL;
use Session;
use Redirect;
use Input;
use Flash;
use Sentinel;
use DB;
use App\User;
use Stripe\Error\Card;
use Stripe;

class CustomerPaymentController extends Controller
{
  public function __construct(
    GeneralSettingModel $GeneralSettingModel,
    TransactionsDetailsModel $TransactionsDetailsModel,
    TempBagModel $TempBagModel,
    StripePaymentService $StripePaymentService,
    CardModel $CardModel,
    UserModel $UserModel,
    RetailerModel $RetailerModel,
    CustomerModel $CustomerModel,
    GeneralService $GeneralService,
    CustomerOrderService $CustomerOrderService,
    CountryModel $CountryModel,
    EmailService $mail_service,
    RetailerQuotesModel $RetailerQuotesModel,
    CustomerQuotesModel $CustomerQuotesModel,
    RepresentativeLeadsModel $RepresentativeLeadsModel,
    ProductService $ProductService,
    HelperService $HelperService,
    orderDataService $orderDataService
  ) {
    $this->arr_view_data            = [];
    $this->module_title             = "Payment Transactions";
    $this->page_title               = "Payment Transactions";
    $this->retailer_panel_slug      = config('app.project.retailer_panel_slug');
    $this->module_url_path          = url('/');
    $this->GeneralSettingModel      = $GeneralSettingModel;
    $this->TransactionsDetailsModel = $TransactionsDetailsModel;
    $this->TempBagModel             = $TempBagModel;
    $this->CardModel                = $CardModel;
    $this->UserModel                = $UserModel;
    $this->RetailerModel            = $RetailerModel;
    $this->CustomerModel            = $CustomerModel;
    $this->GeneralService           = $GeneralService;
    $this->CustomerOrderService     = $CustomerOrderService;
    $this->EmailService             = $mail_service;
    $this->CountryModel             = $CountryModel;
    $this->RetailerQuotesModel      = $RetailerQuotesModel;
    $this->CustomerQuotesModel      = $CustomerQuotesModel;
    $this->RepresentativeLeadsModel = $RepresentativeLeadsModel;
    $this->StripePaymentService     = $StripePaymentService;
    $this->ProductService           = $ProductService;
    $this->HelperService            = $HelperService;
    $this->orderDataService         = $orderDataService;
    $this->admin_user_id            = get_admin_id();

  }

  public function checkout(Request $request, $role = false, $order_no = false, $maker_id = false)
  { 
    $loggedInUserId = 0;
    $user = Sentinel::check();

    if ($user) {
      $loggedInUserId = $user->id;
    }

    if ($loggedInUserId == 0) {
      return redirect('/');
    }
    $form_data    = $request->all();

    $arr_final_data = [];
    $user_arr = [];

    /*---get logged in user id--*/

    // $user = Sentinel::check();
    $user_id = $promotional_discount = 0;

    if ($user) {
      $user_id = $user->id;
    }

    /*-----------------------*/

    $role = isset($role) ? $role : false;
    $order_no = isset($order_no) ? base64_decode($order_no) : false;
    $maker_id = isset($maker_id) ? base64_decode($maker_id) : false;


    $arr_final_data = $this->orderDataService->get_customer_bag_details($user_id);
   
    $this->arr_view_data['arr_final_data'] = isset($arr_final_data) ? $arr_final_data : [];

    /* calculation  */
    $ship_discount = $prod_discount = $sub_total = $shipping_charg = $total_amt = $temp_ship_discount = 0;

    if (isset($arr_final_data) && count($arr_final_data) > 0) {

      foreach ($arr_final_data as $product_details) {

        if (isset($product_details['product_details']) && count($product_details['product_details']) > 0) {

          foreach ($product_details['product_details'] as $arr_product_data) {

            $product_shipping_charg = isset($arr_product_data['shipping_charges']) ? num_format($arr_product_data['shipping_charges']) : 0.00;

            $temp_ship_discount += isset($arr_product_data['shipping_discount']) ? num_format($arr_product_data['shipping_discount']) : 0.00;

            $ship_discount = isset($arr_product_data['shipping_discount']) ? num_format($arr_product_data['shipping_discount']) : 0.00;

           
            $shipping_charg += $product_shipping_charg + $ship_discount;
            

            $prod_discount += isset($arr_product_data['product_dis amount']) ? num_format($arr_product_data['product_dis amount']) : 0.00;

            $item_qty = isset($arr_product_data['item_qty']) ? $arr_product_data['item_qty'] : 0;
            
            $login_user  = Sentinel::check();

            if ($login_user->inRole('customer')) {
              $unit_price = isset($arr_product_data['unit_retail_price']) ? $arr_product_data['unit_retail_price'] : 0;
            }


            $sub_total += $item_qty * $unit_price;

            $total_amt = ($sub_total) + ($shipping_charg) - ($prod_discount) - ($temp_ship_discount);
          }
        }
      }
    }

    /*Get Session Customer Promo Code Data (If customer has applied promo code then give discount)*/

    $session_promotion_data = Session::get('customer_promotion_data');

    if (isset($session_promotion_data)) {
      $promo_discount_amt = isset($session_promotion_data['discount_amt']) ? $session_promotion_data['discount_amt'] : 0;
      $total_amt = $total_amt - $promo_discount_amt;
    }

    $total_amount      = $sub_total;
    $shipping_charges  = $shipping_charg;
    $shipping_discount = $temp_ship_discount;
    $product_discount  = $prod_discount;
    $amount            = $total_amt;
    // dd($shipping_charges,$shipping_discount);
    /*************************************************/


    if ($user) {
      $user_id = $user->id;

      $user_obj      = $this->UserModel->where('id', $user_id)->first();
      $status_net_30 = $user_obj->status_net_30;
    }

    $stripApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

    $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';

    $card_data = $this->CardModel->where('user_id', $user_id)
                                 ->where('stripe_key_id',$stripeKeyId)
                                 ->groupBy('fingerprint')
                                 ->get()
                                 ->toArray();

    $arr_cards = [];

    if ($card_data && count($card_data)) {
      $arr_cards = $this->StripePaymentService->get_card_data($card_data);

      if (isset($arr_cards['status']) && $arr_cards['status'] == 'Error') {
        $arr_cards = [];
      }
    }


    if ($user && $user->inRole('customer')) {

      /*get customer details */
      $customer_details = $this->UserModel->with(['customer_details'])->where('id', $user_id)->first();

      $customer_addr_data = $this->CustomerModel->where('user_id', $user_id)->first();
    }

    if (isset($customer_addr_data)) {
      $customer_addr_data =  $customer_addr_data->toArray();
    }


    $customer_addr_data_arr = [];

    $shipping_addr    = isset($customer_addr_data['shipping_addr']) && $customer_addr_data['shipping_addr'] != "" ? $customer_addr_data['shipping_addr'] : '';


    $shipping_suit_apt = isset($customer_addr_data['shipping_suit_apt']) && $customer_addr_data['shipping_suit_apt'] != '' ? $customer_addr_data['shipping_suit_apt'] : '';

    $shipping_city    = isset($customer_addr_data['shipping_city']) && $customer_addr_data['shipping_city'] != '' ? $customer_addr_data['shipping_city'] : '';
    $shipping_state   = isset($customer_addr_data['shipping_state']) && $customer_addr_data['shipping_state'] != '' ? $customer_addr_data['shipping_state'] : '';

    $ship_contact_no = isset($customer_addr_data['ship_contact_no']) && $customer_addr_data['shipping_state'] != '' ? $customer_addr_data['ship_contact_no'] : '';



    if (isset($customer_addr_data['shipping_country']) && $customer_addr_data['shipping_country'] != '') {
      $shipping_country = get_country($customer_addr_data['shipping_country']);
      $shipping_country = isset($shipping_country) && $shipping_country != '' ? $shipping_country : '';
    } else {
      $shipping_country = '';
    }

    $billing_addr    = isset($customer_addr_data['billing_address']) && $customer_addr_data['billing_address'] != '' ? $customer_addr_data['billing_address'] : '';

    $billing_suit_apt = isset($customer_addr_data['billing_suit_apt']) && $customer_addr_data['billing_suit_apt'] != '' ? $customer_addr_data['billing_suit_apt'] : '';


    $billing_city    = isset($customer_addr_data['billing_city']) && $customer_addr_data['billing_city'] != '' ? $customer_addr_data['billing_city'] : '';
    $billing_state   = isset($customer_addr_data['billing_state']) && $customer_addr_data['billing_state'] != '' ? $customer_addr_data['billing_state'] : '';


    $bill_contact_no = isset($customer_addr_data['bill_contact_no']) && $customer_addr_data['bill_contact_no'] != '' ? $customer_addr_data['bill_contact_no'] : '';


    if (isset($customer_addr_data['billing_country']) && $customer_addr_data['billing_country'] != '') {
      $billing_country = get_country($customer_addr_data['billing_country']);

      $billing_country = isset($billing_country) && $billing_country != '' ? $billing_country : '';
    } else {
      $billing_country = '';
    }

    $customer_shippping_post_code = isset($customer_addr_data['shipping_zip_postal_code']) ? $customer_addr_data['shipping_zip_postal_code'] : '';

    $customer_billing_post_code = isset($customer_addr_data['billing_zip_postal_code']) ? $customer_addr_data['billing_zip_postal_code'] : '';

    $country_data  = $this->CountryModel->where('is_active','1')->orderBy('name','ASC')->get()->toArray();

    $customer_shipping_addr['shipping_addr']     =  $shipping_addr;
    $customer_shipping_addr['shipping_suit_apt'] =  $shipping_suit_apt;
    $customer_shipping_addr['shipping_city']     =  $shipping_city;
    $customer_shipping_addr['shipping_state']    =  $shipping_state;
    $customer_shipping_addr['shipping_country']  = $customer_addr_data['shipping_country'];
    $customer_shipping_addr['shipping_zip_postal_code'] = $customer_shippping_post_code;
    $customer_shipping_addr['ship_contact_no']   = $ship_contact_no;



    $customer_billing_addr['billing_addr']            =  $billing_addr;
    $customer_billing_addr['billing_suit_apt']        =  $billing_suit_apt;
    $customer_billing_addr['billing_city']            =  $billing_city;
    $customer_billing_addr['billing_state']           =  $billing_state;
    $customer_billing_addr['billing_country']         =  $customer_addr_data['billing_country'];
    $customer_billing_addr['billing_zip_postal_code'] =  $customer_billing_post_code;
    $customer_billing_addr['bill_contact_no']         =  $bill_contact_no;

    $this->arr_view_data['page_title']             = 'Payment Transaction';
    $this->arr_view_data['module_url_path']        = $this->module_url_path;
    $this->arr_view_data['status_net_30']          = $status_net_30;
    $this->arr_view_data['customer_data']          = $user_arr;
    $this->arr_view_data['customer_addr_data']     = $customer_addr_data_arr;
    $this->arr_view_data['arr_card']               = $arr_cards;
    $this->arr_view_data['customer_shipping_addr'] = $customer_shipping_addr;
    $this->arr_view_data['customer_billing_addr']  = $customer_billing_addr;
    $this->arr_view_data['country_data']           = $country_data;

    /*this id is put into session when order placed by net 30*/
    $order_id = Session::get('order_id');

    /*this id is set into session when order placed by representative.and retailer going to do payment that time this id is will used*/
    // $representative_order_id = Session::get('representative_order_id');


    if (isset($order_id) && $order_id != '') {
      $order_data_arr = [];

      $obj_order_data = $this->CustomerQuotesModel->where('id', $order_id)->first();

      if (isset($obj_order_data)) {
        $order_data_arr = $obj_order_data->toArray();

        $this->arr_view_data['amount']            = isset($amount) ? $amount : '';
        $this->arr_view_data['product_discount']  = isset($product_discount) ? $product_discount : '';
        $this->arr_view_data['total_amount']      = isset($total_amount) ? $total_amount : '';
        $this->arr_view_data['shipping_charges']  = isset($shipping_charges) ? $shipping_charges : '';
        $this->arr_view_data['shipping_discount'] = isset($shipping_discount) ? $shipping_discount : '';

        /*get payment terms*/

        $payment_term = '';

        $payment_term = $this->HelperService->get_payment_term($order_data_arr['order_no'], $order_id);

        $this->arr_view_data['payment_term'] = isset($payment_term) ? $payment_term : '';


        /*--------------------*/
      }
    }
    
    $this->arr_view_data['role']              = isset($role) ? $role : false;
    $this->arr_view_data['amount']            = isset($amount) ? $amount : 0;
    $this->arr_view_data['product_discount']  = isset($product_discount) ? $product_discount : 0;
    $this->arr_view_data['total_amount']      = isset($total_amount) ? $total_amount : 0;
    $this->arr_view_data['shipping_charges']  = isset($shipping_charges) ? $shipping_charges : 0;
    $this->arr_view_data['shipping_discount'] = isset($shipping_discount) ? $shipping_discount : 0;
    $this->arr_view_data['promotional_discount'] = isset($promotional_discount) ? $promotional_discount : 0;
    
    return view('front.customer_checkout', $this->arr_view_data);
  }

  public function postPaymentWithStripe($arr_data)
  {
    $loggedInUserId = 0;
    $user = Sentinel::check();

    if ($user && $user->inRole('customer')) {
      $loggedInUserId = $user->id;
    } else {
      Sentinel::logout();
      Flash::error('Please login as retailer or customer for buying product.');
      return redirect('/login');
    }

    $process_payment = $this->StripePaymentService->process_payment($arr_data);

    return $process_payment;
  }

  public function buy_cart_items(Request $request)
  {

    $request_data = [];
    $form_data    = $request->all();

    $user         = \Sentinel::check();

    if ($request->payment_type == '2') {
      $response = $this->net_30_payment($form_data);

      //dd($response);
    }

    if ($user) {
      $user_id = $user->id;
    }
    
    $adminId = get_admin_id();
    $adminStripeKeyData = $this->StripePaymentService->get_active_stripe_key($adminId);

    $stripe_key_id = isset($adminStripeKeyData['id'])?$adminStripeKeyData['id']:false;

    $get_user = $this->CardModel->where('user_id',$user_id)
                                ->where('stripe_key_id',$stripe_key_id)
                                ->groupBy('fingerprint')
                                ->get();
        

    $is_exists = $request->input('is_exists');

    $is_exists = isset($is_exists) ? $is_exists : false;

    if ($is_exists == false) {
      if (count($get_user) >= 6) {
        $response['status'] = 'warning';
        $response['description'] = ' You can add only six cards.';
        return response()->json($response);
      }
    }
    if ($is_exists) {

      $stripe_payment = $this->buy_items($form_data);


      return $stripe_payment;
    }

    $admin_user_id       = get_admin_id();

    $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($admin_user_id);

    $stripe_api_key = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';

    $stripe_key_id = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

    $arr_rule = [
      'number' => 'required',
      'expiry' => 'required',

      'shipping_addr'    => 'required',
      'shipping_city'    => 'required',
      'shipping_state'   => 'required',
      'shipping_country' => 'required',
      'shipping_zip_postal_code' => 'required',

      'billing_addr'     => 'required',
      'billing_city'     => 'required',
      'billing_state'    => 'required',
      'billing_country'  => 'required',
      'billing_zip_postal_code' => 'required',
      'bill_contact_no'  => 'required',
      'ship_contact_no'  => 'required'

    ];

    $validator = Validator::make($form_data, $arr_rule);

    if ($validator->fails()) {

      $response['status']      = 'error';
      $response['description'] = 'Form validation failed, please check form fields.';
      return response()->json($response);
    }



    Stripe\Stripe::setApiKey($stripe_api_key);

    /* Seprate Card Expiry Month And Year */

    $card_expiry_date = $request->input('expiry');
    $arr_expire       = explode('/', $card_expiry_date);
    $expire_month     = trim($arr_expire[0]);
    $expire_year      = trim($arr_expire[1]);
    $cvv              = trim($request->cvc);

    // Create a token
    $token = $this->StripePaymentService->create_card_token($request->number, $expire_month, $expire_year, $cvv);

    // dd($token);



    if ($token['status'] != 'Error') {
      $request_data = [];
      $form_data = $request->all();

      $request_data['number'] = $form_data['number'] or '';
      $request_data['expiry'] = $form_data['expiry'] or '';
      $request_data['cvc']    = $form_data['cvc'] or '';
      $request_data['is_exists'] = $form_data['is_exists'] or '';
      $request_data['payment_type'] = $form_data['payment_type'] or '';

      $request_data['shipping_addr'] = $form_data['shipping_addr'];
      $request_data['shipping_city'] = $form_data['shipping_city'];
      $request_data['shipping_state'] = $form_data['shipping_state'];
      $request_data['shipping_country'] = $form_data['shipping_country'];
      $request_data['shipping_zip_postal_code'] = $form_data['shipping_zip_postal_code'];
      $request_data['ship_contact_no'] = $form_data['ship_contact_no'];

      $request_data['billing_addr'] = $form_data['billing_addr'];
      $request_data['billing_city'] = $form_data['billing_city'];
      $request_data['billing_state'] = $form_data['billing_state'];
      $request_data['billing_country'] = $form_data['billing_country'];
      $request_data['billing_zip_postal_code'] = $form_data['billing_zip_postal_code'];
      $request_data['bill_contact_no'] = $form_data['bill_contact_no'];



      if (count($get_user) > 0) {
        $get_user = $this->CardModel->where('user_id', $user_id)->first();
        $cust_list = $get_user->toArray();

        $customer = \Stripe\Customer::retrieve($cust_list['stripe_customer_id']);
        $card = $customer->sources->create(array(
          "source" => $token,
          "metadata" => [
            "CardNo"   => base64_encode($request->number),
            "ExpMonth" => base64_encode($expire_month),
            "ExpYear"  => base64_encode($expire_year),
            "Cvv"      => base64_encode($cvv)
          ]
        ));

        $data['user_id']     = $user_id;
        $data['stripe_customer_id'] = $cust_list['stripe_customer_id'];
        $data['stripe_card_id']     = $card->id;
        $data['fingerprint']        = $token->card['fingerprint'] or '';
        $data['stripe_key_id']      = $stripe_key_id;

        $action = $this->CardModel->create($data);

        $request_data['customer_id'] = isset($cust_list['stripe_customer_id']) ? $cust_list['stripe_customer_id'] : '';
        $request_data['is_exists']   = isset($card->id) ? $card->id : '';


        $stripe_payment = $this->buy_items($request_data);

        return $stripe_payment;
      } else {

        // Create a Customer
        $customer = \Stripe\Customer::create(array(
          "email" => $user->email
        ));
        $customer = \Stripe\Customer::retrieve($customer->id);
        $card = $customer->sources->create(array(
          "source" => $token,
          "metadata" => [
            "CardNo"   => base64_encode($request->number),
            "ExpMonth" => base64_encode($expire_month),
            "ExpYear"  => base64_encode($expire_year),
            "Cvv"      => base64_encode($cvv)
          ]
        ));

        $data['user_id']            = $user_id;
        $data['stripe_customer_id'] = $customer->id;
        $data['stripe_card_id']     = $card->id;
        $data['fingerprint']        = $token->card['fingerprint'] or '';
        $data['stripe_key_id']      = $stripe_key_id;
        
        $action                     = $this->CardModel->create($data);

        $request_data['customer_id'] = isset($customer->id) ? $customer->id : '';
        $request_data['is_exists']   = isset($card->id) ? $card->id : '';


        $stripe_payment = $this->buy_items($request_data);

        return $stripe_payment;
      }
    } else {
      $response['status'] = 'error';
      $response['description'] = $token['description'] or '';
      return response()->json($response);
    }
  }

  public function buy_items($arr_data)
  {

    /* card verification */

    if (isset($arr_data['is_exists']) && $arr_data['is_exists'] != '' && $arr_data['is_exists'] != false) {
      $is_valid = $this->StripePaymentService->is_card_valid($arr_data['is_exists']);

      if (isset($is_valid['status']) && $is_valid['status'] == 'error') {
        $response['status']      = 'error';
        $response['description'] = isset($is_valid['message']) ? $is_valid['message'] : '';
        return response()->json($response);
      }
    }

    $user         = \Sentinel::check();


    if ($user) {
      $user_id = $user->id;
    }

    $shipping_country_name = $billing_country_name = "";

    $shipping_addr    = $arr_data['shipping_addr'];
    $shipping_city    = $arr_data['shipping_city'];
    $shipping_state   = $arr_data['shipping_state'];
    $shipping_country = $arr_data['shipping_country'];
    $shipping_zip_postal_code = $arr_data['shipping_zip_postal_code'];

    $shipping_suit_apt        = isset($arr_data['shipping_suit_apt']) ? $arr_data['shipping_suit_apt'] : '';
    $ship_contact_no          = isset($arr_data['ship_contact_no']) ? $arr_data['ship_contact_no'] : '';




    if (isset($shipping_country) && $shipping_country != "") {
      $shipping_country_name = get_country($shipping_country);
    }

    $arr_data['shipping'] = $shipping_addr . ' ' . $shipping_suit_apt . ' ' . $shipping_city . ' ' . $shipping_state . ' ' . $shipping_country_name . ' ' . $shipping_zip_postal_code.' '.', Mobile.No: '.$ship_contact_no;


    $billing_addr    = $arr_data['billing_addr'];
    $billing_city    = $arr_data['billing_city'];
    $billing_state   = $arr_data['billing_state'];
    $billing_country = $arr_data['billing_country'];
    $billing_zip_postal_code = $arr_data['billing_zip_postal_code'];

    $billing_suit_apt        = isset($arr_data['billing_suit_apt']) ? $arr_data['billing_suit_apt'] : '';
    $billing_contact_no      = isset($arr_data['bill_contact_no']) ? $arr_data['bill_contact_no'] : '';



    $addressing_details = $this->CustomerModel::firstOrNew(['user_id' => $user_id]);

    if($addressing_details['shipping_addr'] == null)
    {


        $addressing_details->user_id                  = isset($user_id) ? $user_id : 0;

        $addressing_details->shipping_addr            = isset($shipping_addr) ? $shipping_addr : '';

        $addressing_details->shipping_city            = isset($shipping_city) ? $shipping_city : '';

        $addressing_details->shipping_state           = isset($shipping_state) ? $shipping_state : '';

        $addressing_details->shipping_country         = isset($shipping_country) ? $shipping_country : '';

        $addressing_details->shipping_zip_postal_code = isset($shipping_zip_postal_code) ? $shipping_zip_postal_code : '';

        $addressing_details->shipping_suit_apt        = isset($shipping_suit_apt) ? $shipping_suit_apt : '';

        $addressing_details->ship_contact_no          = isset($ship_contact_no) ? $ship_contact_no : '';



        $addressing_details->billing_address          = isset($billing_addr) ? $billing_addr : '';

        $addressing_details->billing_city             = isset($billing_city) ? $billing_city : '';

        $addressing_details->billing_state            = isset($billing_state) ? $billing_state : '';

        $addressing_details->billing_country          = isset($billing_country) ? $billing_country : '';

        $addressing_details->billing_zip_postal_code  = isset($billing_zip_postal_code) ? $billing_zip_postal_code : '';

        $addressing_details->billing_suit_apt         = isset($billing_suit_apt) ? $billing_suit_apt : '';

        $addressing_details->bill_contact_no          = isset($billing_contact_no) ? $billing_contact_no : '';



        $save_addresses = $addressing_details->save();
    }




    if (isset($billing_country) && $billing_country != "") {
      $billing_country_name = get_country($billing_country);
    }

    $arr_data['billing'] = $billing_addr . ' ' . $billing_suit_apt . ' ' . $billing_city . ' ' . $billing_state . ' ' . $billing_country_name . ' ' . $billing_zip_postal_code.' '.', Mobile.No: '.$billing_contact_no;


    $stripe_payment = $this->postPaymentWithStripe($arr_data);


    if ($stripe_payment['status'] == 'success') {
      $transaction_id = isset($stripe_payment['transaction_ref_id']) ? $stripe_payment['transaction_ref_id'] : '';

      /*update transaction id into retailer transaction table*/

      $order_id = Session::get('order_id');

      $representative_order_id = Session::get('representative_order_id');

      $data['transaction_id'] = $transaction_id;


      // $result = $this->RetailerQuotesModel->where('id',$order_id)->update($data);


      /*also update transaction id in representative leads*/

      // $updated_result = $this->RepresentativeLeadsModel->where('id',$representative_order_id)->update($data);

      $order_addr_data = [];
      $order_addr_data['shipping'] = isset($arr_data['shipping']) ? $arr_data['shipping'] : '';


      $order_addr_data['billing'] = isset($arr_data['billing']) ? $arr_data['billing'] : '';
      // dd($order_addr_data);

      $service_response = $this->CustomerOrderService->store_customer_cart_items($transaction_id, $order_addr_data);

      if ($service_response['status'] == 'success') {
        $response['status']      = 'success';

        if (Session::has('payment_type')) {
          $response['description'] = 'Payment has been done.';
          Session::forget('payment_type');
        } else {
          $response['description'] = 'Order has been generated.';

          /*clear session data of promotion*/
          Session::forget('promo_shipping_charges');
          Session::forget('promotion_discount_amt');
          Session::forget('total_order_amout');
          Session::forget('customer_promotion_data');
          Session::forget('amount');
        }


        /*release session data*/

        Session::forget('payment_type');
        Session::forget('order_id');
        Session::forget('representative_order_id');
        Session::forget('amount');
        Session::forget('shipping_discount');
        Session::forget('product_discount');
        Session::forget('total_amount');
        Session::forget('shipping_charges');
        Session::forget('representative_order_total');
        Session::forget('Wholsale_sub_total');

        /*clear session data of promotion*/
        // Session::forget('promo_shipping_charges');
        Session::forget('promotion_discount_amt');
        Session::forget('total_order_amout');
        Session::forget('customer_promotion_data');


        return response()->json($response);
      } else {
        $response['status']      = 'error';
        if (isset($service_response['description'])) {

          $response['description'] = $service_response['description'];
        } else {

          $response['description'] = 'Something went wrong, please try again.';
        }
        return response()->json($response);
      }
    } else {
      $response['status']      = 'error';
      $response['description'] = 'Something went wrong, please try again.';
      return response()->json($response);
    }
  }

  public function email_view()
  {
    return view('front.email_template.purchase_order');
  }
}
