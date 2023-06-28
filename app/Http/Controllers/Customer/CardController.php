<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\StripePaymentService;
use App\Models\CardModel;
use App\Models\TransactionsModel;
use Validator;
use Stripe;
use Flash;

class CardController extends Controller
{
    function __construct()
    {
    	$this->module_title         = 'Card Details';
    	$this->module_view_folder   = 'customer.card'; 
    	$this->retailer_panel_slug  = config('app.project.customer_panel_slug');
    	$this->module_url_path      = url($this->retailer_panel_slug.'/card');
    	$this->module_view_folder   = 'customer/card';
        $this->BaseModel            = new CardModel();
        $this->TransactionsModel    = new TransactionsModel();
        $this->StripePaymentService = new StripePaymentService();
    	$this->arr_view_data        = [];
        //$this->stripe_api_key       = 'sk_test_UQE8wx6WNY7Ogj1A5Uy1ZMWA00Cjg1fs3r';
        $this->stripe_api_key       = get_admin_stripe_key();
        $this->admin_user_id       = get_admin_id();


    }

    public function index()
    {
        $stripApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

        $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';

        $user_id = \Sentinel::getUser()->id;

        $arr_card_details = [];
        
    	$arr_card_details = $this->BaseModel->where('user_id',$user_id)
                                            ->where('stripe_key_id',$stripeKeyId)
                                            ->groupBy('fingerprint')
                                            ->get()
                                            ->toArray();
        $arr_cards = [];

        if($arr_card_details && count($arr_card_details))
        {
          $arr_cards = $this->StripePaymentService->get_card_data($arr_card_details);
        }
        
        if(isset($arr_cards['status']) && $arr_cards['status'] == 'Error'){
       
          $arr_cards = [];
        }
        
        $this->arr_view_data['module_title']     = 'Manage Cards';
    	$this->arr_view_data['page_title']       = 'Manage Cards';
        $this->arr_view_data['module_url_path']  = $this->module_url_path;
    	$this->arr_view_data['arr_card_details'] = $arr_cards;
        // $this->arr_view_data['arr_card_details'] = $new_card_details;
    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function add()
    {
        
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title'] = 'Add '.$this->module_title;
    	$this->arr_view_data['page_title'] = 'Add '.$this->module_title;
    	return view($this->module_view_folder.'.add',$this->arr_view_data);
    }

    public function store(Request $request)
    {
        $user = \Sentinel::check();
        $form_data = $request->all();
        
        if($user)
        {
          $user_id = $user->id;
        }

        $stripApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

        $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';

        $get_user = $this->BaseModel->where('user_id',$user_id)
                                    ->where('stripe_key_id',$stripeKeyId)
                                    ->groupBy('fingerprint')
                                    ->first();

        if(!isset($request->card_id))
        {
            if(count($get_user) >= 6)
            { 
               $response['status']      = 'warning';
               $response['description'] = ' You can add only six cards.';
               return response()->json($response); 
            }
        }
    
        $arr_rule=[
                    'number' => 'required',
                    'expiry' => 'required',
                    'cvc'    => 'required',
                  ];

        $validator = Validator::make($request->all(),$arr_rule);
        if($validator->fails())
        {
           $response['status'] = 'warning';
           $response['description'] = 'Form validation failed, please check all fields.';

          return response()->json($response);
        }

        $this->stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

        $this->stripe_api_key = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';

        $data['stripe_key_id'] = isset($this->stripeApiKeyData['id'])?$this->stripeApiKeyData['id']:'';

        Stripe\Stripe::setApiKey($this->stripe_api_key);

        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $request->input('expiry');
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        $cvv              = trim($request->cvc);
        $admin_user_id    = get_admin_id();
        $admin_user_id    = isset($admin_user_id)?$admin_user_id:0;
        // Create a token
        $token = $this->StripePaymentService->create_card_token($request->number,$expire_month,$expire_year,$cvv);

        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($admin_user_id);
        
        $data['stripe_key_id'] = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

        if(isset($token['status']) && $token['status'] == 'Error')
        {
            $response['status']      = 'warning';
            $response['description'] = isset($token['description'])?$token['description']:'Something went wrong,please try again.';
            return response()->json($response);
        }

        if($token)
        {
            if(count($get_user) > 0)
            {
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

                $data['user_id']            = $user_id;
                $data['stripe_customer_id'] = $cust_list['stripe_customer_id'];
                $data['stripe_card_id']     = $card->id;
                $data['fingerprint']        = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';
                //$action = $this->BaseModel->create($data);
            }
            else
            {
                try{
                    // Create a Customer
                    $customer = \Stripe\Customer::create(array(
                        "email" => $user->email
                    ));

                    if($customer)
                    {
                        $retrive_customer = \Stripe\Customer::retrieve($customer->id);
                       
                        $card = $retrive_customer->sources->create(array(
                            "source" => $token,
                             "metadata" => [
                                            "CardNo"   => base64_encode($request->number),
                                            "ExpMonth" => base64_encode($expire_month),
                                            "ExpYear"  => base64_encode($expire_year),
                                            "Cvv"      => base64_encode($cvv)
                                           ]
                        ));  

                        if($card)
                        {
                            $data['user_id']            = $user_id;
                            $data['stripe_customer_id'] = $customer->id;
                            $data['stripe_card_id']     = $card->id;
                            $data['fingerprint']        = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';
                        }
                        else
                        {
                            $response['status']          = 'warning';
                            $response['description']     = 'Something went wrong, please try again.';
                            return response()->json($response);
                        }

                    }
                    else
                    {
                        $response['status']             = 'warning';
                        $response['description']        = 'Something went wrong, please try again.';
                        return response()->json($response);
                    }
                }
                catch(Exception $e)
                {
                    $response['status']          = 'warning';
                    $response['description']     = 'Something went wrong, please try again.';
                    return response()->json($response);
                    
                }
            } 

            $action                     = $this->BaseModel->create($data);

            if($action)
            {
                $response['status']      = 'success';
                $response['description'] = 'Your card details has been saved.';
                $response['link'] = url(config('app.project.customer_panel_slug').'/card');
                return response()->json($response);
            }
        }
        else
        {
            $response['status']      = 'warning';
            $response['description'] = $token['description'] or '';
            return response()->json($response);
        }
    }

    public function edit($enc_id,$customer_id)
    {
        $card_id     = isset($enc_id)?base64_decode($enc_id):'';
        $customer_id = isset($customer_id)?base64_decode($customer_id):'';

        $card_data = [];

        if($card_id && $customer_id){

             $card_data = $this->StripePaymentService->get_single_card_details($card_id,$customer_id);
             // dd(base64_decode($card_data->metadata->ExpMonth));
             if(empty($card_data))
             {
                flash::error('something went wrong, please try again.');
                return redirect()->back();
             }
        }

        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['module_title']    = 'Edit '.$this->module_title;
        $this->arr_view_data['page_title']      = 'Edit '.$this->module_title;
        $this->arr_view_data['card']            = $card_data;
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function delete_card($enc_id,$customer_id,$encCardFingerPrint)
    {

        $card_id     = isset($enc_id)?base64_decode($enc_id):'';

        $encCardFingerPrint     = isset($encCardFingerPrint)?base64_decode($encCardFingerPrint):'';

        $customer_id = isset($customer_id)?base64_decode($customer_id):'';
        
         /* Check card is valid for deletion or not */
        $pendingPaymentCount = $this->TransactionsModel->where('card_id',$card_id)
                                      ->with(['get_customer_orders'])
                                      ->whereHas('get_customer_orders',function($q){
                                                 $q->where('is_payment_status',0)
                                                 ->where('order_cancel_status','!=','2');
                                              })
                                      ->count();

        if($pendingPaymentCount > 0)
        {
           $response['status']      = 'warning';
           $response['description'] = 'Card can not be deleted for in-progress order.';
           return response()->json($response); 
        }

        if($card_id)
        {
            $card_data = $this->StripePaymentService->delete_card_details($card_id,$customer_id);

            $delete = $this->BaseModel->where('fingerprint',$encCardFingerPrint)
                                      ->delete();

            if($delete){
                $response['status']      = 'success';
                $response['description'] = 'Your card has been removed.';
                return response()->json($response);
            }
            else
            {
                $response['status']      = 'error';
                $response['description'] = 'Something went wrong, please try again.';
                return response()->json($response);
            }
        }
    }

    public function update(Request $request)
    {
        $user = \Sentinel::check();
        $form_data = $request->all();
        
        if($user)
        {
          $user_id = $user->id;
        }

        $get_user = $this->BaseModel->where('user_id',$user_id)->first();
    
        $arr_rule=['expiry' => 'required'];

        $validator = Validator::make($request->all(),$arr_rule);
        if($validator->fails())
        {
           $response['status'] = 'warning';
           $response['description'] =' Form validation failed, please check all fields.';

          return response()->json($response);
        }

        $card_id     = $request->card_id;
        $customer_id = $request->customer_id;

        Stripe\Stripe::setApiKey($this->stripe_api_key);

        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $request->input('expiry');
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        $cvv              = trim($request->cvv);

        $update_card_data = $this->StripePaymentService->update_card_details($card_id,$customer_id,$expire_month,$expire_year);
        
        if($update_card_data)
        {
            $response['status']      = 'success';
            $response['description'] = 'Your card details has been saved.';
            $response['link']        = url(config('app.project.customer_panel_slug').'/card');
           
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }
}


