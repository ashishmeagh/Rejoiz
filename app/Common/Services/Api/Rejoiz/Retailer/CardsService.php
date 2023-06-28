<?php
namespace App\Common\Services\Api\Rejoiz\Retailer;
   
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\StripePaymentService;
use App\Models\CardModel;
use App\Models\TransactionsModel;

use Stripe;

  
class CardsService {

  public function __construct()
  {
  	    $this->BaseModel            = new CardModel();
        $this->TransactionsModel    = new TransactionsModel();
        $this->StripePaymentService = new StripePaymentService();
    	$this->arr_view_data        = [];

        $this->admin_user_id       = get_admin_id();
  }

    public function get_user_cards($user_id)
    {
    	$card['data'] = [];

    	try {
		      $stripApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

		      $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';
		      
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

		          foreach ($arr_cards as $key => $cardData) {
		          		
			          $card['data'][$key]['card_no'] = isset($cardData['card_no'])?str_pad($cardData['card_no'], 16, "X", STR_PAD_LEFT):'';

		              $card['data'][$key]['card_expiry_month'] = isset($cardData['exp_month'])?$cardData['exp_month']:'';

		              $card['data'][$key]['card_expiry_year']  = isset($cardData['exp_year'])?$cardData['exp_year']:'';

		              $card['data'][$key]['card_expiry'] = $card['data'][$key]['card_expiry_month'].' / '.$card['data'][$key]['card_expiry_year'];

		              $card['data'][$key]['customer_id'] = isset($cardData['customer_id'])?$cardData['customer_id']:'';

		              $card['data'][$key]['card_id'] = isset($cardData['stripe_card_id'])?$cardData['stripe_card_id']:'';
		          }

		        }
		        
		        if(isset($arr_cards['status']) && $arr_cards['status'] == 'Error')
		        {
		          $arr_cards = [];
		        }
		        $response = [];

		        $response['status']  = 'success';
		        $response['message'] = 'Card list get successfully';
		        $response['data']    = $arr_cards;

		        return $response;
    		
    	} catch (Exception $e) 
    	{
    		return $response = [
             'status'  => 'failure',
             'message' => 'Something went wrong.',
             'data'    => ''
           ];
    	}
    }

    public function store($user,$form_data)
    {
    	$response = [];

        if($user)
        {
          $user_id = $user->id;
        }

	    $adminStripeKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

	    $stripe_key_id = isset($adminStripeKeyData['id'])?$adminStripeKeyData['id']:false;

	    $stripe_key = isset($adminStripeKeyData['stripeKey'])?$adminStripeKeyData['stripeKey']:false;

	    $get_user = $this->BaseModel->where('user_id',$user_id)
	                                ->where('stripe_key_id',$stripe_key_id)
	                                ->groupBy('fingerprint')
	                                ->get();
	    if(count($get_user) >= 6)
	    { 
	       $response['status']      = 'failure';
	       $response['message'] = ' You can add only six cards.';
	       return $response; 
	    }

        Stripe\Stripe::setApiKey($stripe_key);
        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $form_data['expiry'];
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        $cvv              = trim($form_data['cvc']);


        // Create a token
        $token = $this->StripePaymentService->create_card_token($form_data['number'],$expire_month,$expire_year,$cvv,$stripe_key,$stripe_key_id,$user);
        	
        if(isset($token['status']) && $token['status'] == 'Error')
        {
            $response['status']  = 'failure';
            $response['message'] = isset($token['description'])?$token['description']:'Something went wrong,please try again.';
            return $response;
        }

        if($token)
        {
            if(count($get_user) > 0)
            {
                $cust_list = $get_user->toArray();

                $customer = \Stripe\Customer::retrieve($cust_list[0]['stripe_customer_id']);
                
                $card = $customer->sources->create(array(
                    "source" => $token,
                    "metadata" => [
                                    "CardNo"   => base64_encode($form_data['number']),
                                    "ExpMonth" => base64_encode($expire_month),
                                    "ExpYear"  => base64_encode($expire_year),
                                    "Cvv"      => base64_encode($cvv)
                                   ]
                ));

                $data['user_id']            = $user_id;
                $data['stripe_customer_id'] = $cust_list[0]['stripe_customer_id'];
                $data['stripe_card_id']     = $card->id;
                $data['fingerprint']        = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';
                // $action = $this->BaseModel->create($data);
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
                                            "CardNo"   => base64_encode($form_data['number']),
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
                            $response['status']          = 'failure';
                            $response['message']         = 'Something went wrong, please try again.';
                            $response['description']     = 'Something went wrong, while create card token.';
                            return $response;
                        }

                    }
                    else
                    {
                        $response['status']             = 'failure';
                        $response['description']        = 'Something went wrong, while create customer.';
                        return $respons;
                    }
                }
                catch(Exception $e)
                {
                    $response['status']          = 'failure';
                    $response['description']     = 'Something went wrong, please try again.';
                    return $response;
                    
                }
            } 

            $data['stripe_key_id'] = isset($stripe_key_id)?$stripe_key_id:'';
            $action  = $this->BaseModel->create($data);

            if($action)
            {
                $response['status']      = 'success';
                $response['message']     = 'Your card details has been saved.';

                return $response;
            }
        }
        else
        {
            $response['status']  = 'failure';
            $response['message'] = $token['description'] or 'Something went wrong while adding card.';
            return $response;
        }
    }

    public function edit($card_id,$customer_id)
    {
        $card = [];

        if($card_id && $customer_id)
        {
             $card_data = $this->StripePaymentService->get_single_card_details($card_id,$customer_id);

           
             if(empty($card_data))
             {
                  $response['status']          = 'failure';
                  $response['description']     = 'Something went wrong, please try again.';
                  return $response;
             }
             else
             {
             	 $card['card_no'] = isset($card_data->last4)?str_pad($card_data->last4, 16, "X", STR_PAD_LEFT):'';

	             $card['card_expiry_month'] = isset($card_data->exp_month)?$card_data->exp_month:'';

	             $card['card_expiry_year']  = isset($card_data->exp_year)?$card_data->exp_year:'';

	             $card['card_expiry'] = $card['card_expiry_month'].' / '.$card['card_expiry_year'];

	             $card['customer_id'] = isset($card_data->customer)?$card_data->customer:'';

	             $card['card_id'] = isset($card_data->id)?$card_data->id:'';
	              	
	             $response['status']      = 'success';
	             
	             $response['message']     = 'Card details get successfully.';
	             
	             $response['data']        = $card;

	             return $response;
             }


        }
    }

    public function update($form_data)
    {
        $user = isset($form_data['auth_user'])?$form_data['auth_user']:false;

    	$card_id = isset($form_data['card_id'])?$form_data['card_id']:false;

    	$customer_id = isset($form_data['customer_id'])?$form_data['customer_id']:false;
        
        if($user)
        {
          $user_id = $user->id;
        }

        $card_id     = $form_data['card_id'];

        $customer_id = $form_data['customer_id'];

        $this->stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($this->admin_user_id);

         $this->stripe_api_key = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';

        Stripe\Stripe::setApiKey($this->stripe_api_key);

        /* Seprate Card Expiry Month And Year */

        $card_expiry_date = $form_data['expiry'];
        $arr_expire       = explode('/', $card_expiry_date);
        $expire_month     = trim($arr_expire[0]);
        $expire_year      = trim($arr_expire[1]);
        // $cvv              = trim($form_data['cvv']);

        $update_card_data = $this->StripePaymentService->update_card_details($card_id,$customer_id,$expire_month,$expire_year,$user);

        if($update_card_data)
        {
            $response['status']      = 'success';
            $response['message'] = 'Your card details has been saved.';
           
        }
        else
        {
            $response['status']      = 'error';
            $response['message'] = 'Something went wrong, please try again.';
        }
        
        return $response;
    }

    public function delete($form_data)
    {
    	$user = isset($form_data['auth_user'])?$form_data['auth_user']:false;

    	$card_id = isset($form_data['card_id'])?$form_data['card_id']:false;

    	$customer_id = isset($form_data['customer_id'])?$form_data['customer_id']:false;

    	$finger_print = isset($form_data['finger_print'])?$form_data['finger_print']:false;

        $user_id = 0;

        if($user)
        {
          $user_id = $user->id;
        }


        /* Check card is valid for deletion or not */
        $pendingPaymentCount = $this->TransactionsModel->where('card_id',$card_id)
                                      ->with(['get_retailer_orders',
                                              'get_rep_sales_orders'])

                                      ->where(function($query)
                                      {
                                            $query->orwhereHas('get_rep_sales_orders',function($q){
                                                         $q->where('is_payment_status',0)
                                                        ->where('order_cancel_status','!=','2');
                                                      })
                                             ->orwhereHas('get_retailer_orders',function($q){
                                                 $q->where('is_payment_status',0)
                                                 ->where('order_cancel_status','!=','2');
                                              });
                                      })
                                      ->count();
                                      
        if($pendingPaymentCount > 0)
        {
           $response['status']  = 'failure';
           $response['message'] = 'Card can not be deleted for in-progress order.';
           return $response; 
        }

        
        if($card_id)
        {
            $card_data = $this->StripePaymentService->delete_card_details($card_id,$customer_id);
            
            $delete = $this->BaseModel->where('fingerprint',$finger_print)
                                      ->where('user_id',$user_id)
                                      ->delete();
                                     
            if($delete){
                $response['status']  = 'success';
                $response['message'] = 'Your card has been removed.';
                return $response;
            }
            else
            {
                $response['status']      = 'error';
                $response['message'] = 'Something went wrong, please try again.';
                return $response;
            }
        }
    }
}