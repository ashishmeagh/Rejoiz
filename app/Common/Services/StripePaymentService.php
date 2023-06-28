<?php 
namespace App\Common\Services;

use App\Models\GeneralSettingModel;
use App\Models\TransactionsModel;
use App\Models\TransactionsDetailsModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\UserStripeCardMappingModel;
use App\Models\TempBagModel;
use App\Models\CardModel;
use App\Models\UserModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\UserStripeAccountDetailsModel;
use App\Models\StripeVendorRetailerMappingModel;
use App\Models\StripeVendorCustomerMappingModel;
use App\Models\StripeCardMappingModel;

use Validator;
use URL;
use Session;
use Redirect;
use Input;
use Flash;
use Sentinel;
use DB;
use Mail;
use App\User;
use Stripe;
use Stripe\Error\Card;
// use Cartalyst\Stripe\Stripe;

class StripePaymentService
{	
	public function __construct()
	{
		$this->GeneralSettingModel   		= new GeneralSettingModel();  
        $this->TransactionsModel     		= new TransactionsModel();
        $this->TransactionsDetailsModel 	= new TransactionsDetailsModel();
        $this->TempBagModel 				= new TempBagModel();
        $this->CardModel                    = new CardModel();	
        $this->RepresentativeLeadsModel     = new RepresentativeLeadsModel();
        $this->StripeAccountDetailsModel    = new StripeAccountDetailsModel();
        $this->UserModel                    = new UserModel();
        $this->StripeCardMappingModel       = new StripeCardMappingModel();
        $this->UserStripeCardMappingModel   = new UserStripeCardMappingModel();
        $this->UserStripeAccountDetailsModel = new UserStripeAccountDetailsModel();
        $this->StripeVendorRetailerMappingModel = new StripeVendorRetailerMappingModel();
        $this->StripeVendorCustomerMappingModel = new StripeVendorCustomerMappingModel();
       
        $this->strip_api_key               = get_admin_stripe_key();
        $this->admin_user_id               = get_admin_id();
	}

	public function get_active_stripe_key($userId)
	{ 
		$arrData = [];
		$stripeKey = UserStripeAccountDetailsModel::where('user_id',$userId)
		                                           ->where('is_active','1')
		                                           ->first(['secret_key','id']);

		if(isset($stripeKey) && $stripeKey != null)
		{
			$arrData['stripeKey'] = $stripeKey->secret_key;
			$arrData['id']        = $stripeKey->id;
		}
		return $arrData;
	}

	public function process_payment($data_arr = false)
	{	
		$user_data = Sentinel::getUser();

		$validator = Validator::make($data_arr, [
		 'number' 		 => 'required',
		 'expiry'        => 'required',
		 ]);

 		$input = $data_arr;

		if(!$validator->passes()) 
		{ 
			return [
					'status'      => 'failure',
					'description' => 'Please enter valid card details'
					];
		}

		/* Get Card Details Using Card ID if card card is present */

		$card_id = isset($input['is_exists'])?$input['is_exists']:false;
		$payment_type = isset($input['payment_type'])?$input['payment_type']:false;
		
        if($card_id)
        {
            $card = $this->CardModel->where('stripe_card_id',$card_id)->first();

            if($card)
            {
                $card_data = $card->toArray();
            }            
        }
        
		DB::beginTransaction();

		$amount = session::get('amount');
		$flagRepOrder = false;
		

		if($amount == null)
		{
			$amount = session::get('representative_order_total');

			$amount = intval($amount);

			$flagRepOrder = true;

		}
		

		$input = array_except($input,array('_token'));

    	/*$stripe_secret_obj = $this->GeneralSettingModel->where('data_id','STRIPE_SECRET_KEY')->where('type','admin')->first();

    	$stripe_secret_key = isset($stripe_secret_obj->data_value)?$stripe_secret_obj->data_value:'';*/

    	$admin_user_id       = get_admin_id();

    	$stripeApiKeyData = $this->get_active_stripe_key($admin_user_id);

    	$stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

	 
	 	/*Check stripe secret key not null*/
	 	if(isset($stripeApiKeyData) && count($stripeApiKeyData) > 0)
	 	{
			
			/* Explode Card Expiry Date */
			
			try 
			{
				/* Generate random transaction id*/
                $transaction_id = str_random(10);
                /* redirect transaction details page*/
				/*$admin_role = Sentinel::findRoleBySlug('admin');        
                $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();
                $admin_id   = 0;
                
                if($admin_obj)
                {
                    $admin_id = $admin_obj->user_id;            
                }*/

                $admin_id = $admin_user_id;

                $user_id = 0;

		        if(Sentinel::check())
		        {
		            $user_id = Sentinel::check()->id;           
		        }
               
                
                $trans_arr['customer_token']     = isset($data_arr['customer_id'])?$data_arr['customer_id']:'';

		  		if(isset($card_data) && count($card_data) > 0)
		  		{
                  $trans_arr['customer_token']     = $card_data['stripe_customer_id'];
		  		}


		  		/* if session amount is empty means order place from sales manager and  representative*/

                /*$total_order_amount = $this->RepresentativeLeadsModel->where('transaction_id',$transaction_id)->pluck('total_wholesale_price');

                dd($total_order_amount);*/

                  		/*-------------------------------------------------------------------------------------*/
		  	
			    $trans_arr['paid_by']            = $user_id or '';
			    $trans_arr['transaction_id']     = $transaction_id or '' ;
			    $trans_arr['card_id']            = $card_id or '' ;
			    $trans_arr['received_by']        = $admin_id or '';
			    $trans_arr['amount']             = $amount or '';
			    $trans_arr['payment_type']       = $payment_type or '';
    	        $trans_arr['stripe_key_id']       = $stripKeyId;

    	        if($payment_type == 1 && $flagRepOrder == true)
    	        {
    	          Session::put('payment_type','1');
    	        }

			    $login_user = Sentinel::Check();
			    if($login_user == true && $login_user->inRole('customer'))
			    {
			    	$trans_arr['retailer_type']       = '3';
			    }

			    $transactions = $this->TransactionsModel->create($trans_arr);
			    
				if($transactions)
				{
				   DB::commit();

				   return [ 'status'      => 'success',
				   			'transaction_ref_id' => isset($transactions->transaction_id)?$transactions->transaction_id:''
				   		  ];
				}
				else
				{
					return ['status'      => 'failure'];
				}

			} 
			catch (Exception $e) 
			{	
				/* TODO update transaction to failed*/ 
				Flash::error($e->getMessage());

				DB::rollback();

				return redirect()->back();

			} catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
			 	
			 	/* TODO update transaction to failed*/ 
			 	Flash::error($e->getMessage());

			 	DB::rollback();

			 	return redirect()->back();

			} catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
				/* TODO update transaction to failed*/ 
			 	Flash::error($e->getMessage());
			 	DB::rollback();
			 	return redirect()->back();
			} 
		}
		else
		{
			return ['status'      => 'failure'];
		}	
	}

	public function create_card_token($card_no,$expire_month,$expire_year,$cvv,$stripeKey=false,$stripeKeyId=false,$auth_user = false)
	{

		/* get admin details */
	    $user = \Sentinel::check();

	    if($user)
	    {
	    	$user_id = isset($user->id)?$user->id:false;
	    }

	    if($auth_user != false)
	    {
	    	$user = $auth_user;
	    	$user_id = isset($user->id)?$user->id:false;
	    }

	    if($stripeKey == false)
	    {
		    $stripApiKeyData = $this->get_active_stripe_key($this->admin_user_id);

	        $this->strip_api_key = isset($stripApiKeyData['stripeKey'])?$stripApiKeyData['stripeKey']:'';
	    }


		try
		{
			if($card_no && $expire_month && $expire_year && $cvv)
			{
				if($stripeKey != false)
				{
					Stripe\Stripe::setApiKey($stripeKey);
				}
				else
				{
				  Stripe\Stripe::setApiKey($this->strip_api_key);
				   
      			  $stripeKeyId = isset($stripApiKeyData['id'])?$stripApiKeyData['id']:'';
				}

		        $token = \Stripe\Token::create(array(
		            "card" => array(
		                "number"    => trim($card_no),
		                "exp_month" => $expire_month,
		                "exp_year"  => $expire_year,
		                "cvc"       => $cvv,
		            )
		        ));	
		
		        $cart_count = $this->CardModel->where('user_id',$user_id)
		        							  ->where('fingerprint',$token->card['fingerprint'])
		        							  ->where('stripe_key_id',$stripeKeyId)
		        							  ->count();
		        
		        if($cart_count > 0)
		        {
		        	$response['status'] = 'Error';
					$response['description'] = 'Card is already exists.';
					return $response;
		        }

		        return $token;
			}
			else
			{
				$response['status']      = 'Error';
				$response['description'] = 'Form validation failed,please check all fileds.';
				return $response;
			}
		}
		catch (\Stripe\Error\RateLimit $e) 
		{
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\InvalidRequest $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\Authentication $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\ApiConnection $e) {
			 $msg = $e->getMessage();
             // $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\Base $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (Exception $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		}
		
	}

	public function get_single_card_details($card_id,$customer_id,$stripeSecretKey=false)
	{
		$card_data = [];

		if($stripeSecretKey == false)
		{
			$stripApiKeyData = $this->get_active_stripe_key($this->admin_user_id);

	        $this->strip_api_key = isset($stripApiKeyData['stripeKey'])?$stripApiKeyData['stripeKey']:'';
		}


		if($card_id && $customer_id)
		{
			try
			{

				if($stripeSecretKey != false)
				{
					\Stripe\Stripe::setApiKey($stripeSecretKey);		
				}
				else
				{
			  		\Stripe\Stripe::setApiKey($this->strip_api_key);
				}

	            $card_data = \Stripe\Customer::retrieveSource(
	               $customer_id,$card_id
	             );

	          return $card_data;
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

	
		
	}

	public function delete_card_details($card_id,$customer_id)
	{
		if($card_id && $customer_id)
		{
		  \Stripe\Stripe::setApiKey($this->strip_api_key);

		  try{

		  	    $get_card_id = $this->CardModel->where('stripe_card_id',$card_id)
			                                    ->where('stripe_customer_id',$customer_id)
			                                    ->pluck('id')
                                                ->first();
		        if($get_card_id)   
		        {
		           $delete_card =  \Stripe\Customer::deleteSource(
		                              $customer_id,$card_id
		                            );

		        	/* delete card details on vendors stripe account */
		        	$vendorCardUpdates = $this->build_card_data_for_delete($get_card_id);
		        	
		        }

          	  return true;
		  }
		  catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}

		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

		return false;
		
	}

	public function update_card_details($card_id,$customer_id,$exp_month,$exp_year,$auth_user=false)
	{
		if($card_id && $customer_id)
		{

			try{

			       
                $get_card_id = $this->CardModel->where('stripe_card_id',$card_id)
			                                    ->where('stripe_customer_id',$customer_id)
			                                    ->pluck('id')
                                                ->first();
		        if($get_card_id)   
		        {
		        	/* update card details on vendors stripe account */
		        	$vendorCardUpdates = $this->build_card_data_for_updation($get_card_id,$exp_month,$exp_year,$auth_user);
		        	
		        }

				Stripe\Stripe::setApiKey($this->strip_api_key);
			    $update = \Stripe\Customer::updateSource(
			         $customer_id,$card_id,
			         [
			           'exp_year' => trim($exp_year),
			           'exp_month' => trim($exp_month),
			            "metadata" => [
			            				// "CardNo"  => $request->number,
                                         "ExpMonth" => base64_encode($exp_month),
                                         "ExpYear"  => base64_encode($exp_year)
                                         // "Cvv"      => $cvv
                                               ]
			         ]
			    );
				return $update;
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}
		
	}

	public function get_card_data($arr_data,$stripe_secret_key=false)
	{	

		$stripApiKeyData = $this->get_active_stripe_key($this->admin_user_id);

        $this->strip_api_key = isset($stripApiKeyData['stripeKey'])?$stripApiKeyData['stripeKey']:'';

        $stripeSecretKey = isset($stripe_secret_key)?$stripe_secret_key:false;

        if($stripeSecretKey != '' && $stripeSecretKey != false)
        {
        	$this->strip_api_key = $stripeSecretKey;
        }

		$new_card_details = $card_details= [];

		if(isset($arr_data) && sizeof($arr_data)>0)
        {
        	try
        	{

	           foreach($arr_data as $list)
	           {	
	           	    Stripe\Stripe::setApiKey($this->strip_api_key);
	                $customer = \Stripe\Customer::retrieve($list['stripe_customer_id']);

	                if($customer)
	                {
	                	$card = $customer->sources->retrieve($list['stripe_card_id']);


		                $card_details['id']         = $list['id'];
		                $card_details['customer_id']= $customer->id;
		                $card_details['card_type']  = $card->brand;
		                $card_details['card_no']    = $card->last4;
		                $card_details['exp_month']  = $card->exp_month;
		                $card_details['exp_year']   = $card->exp_year;
		                $card_details['stripe_card_id'] = $card->id;
		                $card_details['fingerprint'] = $list['fingerprint'];
		                
		                $new_card_details[]         = $card_details;
	                }
	                
	           } 

	           return $new_card_details;
	       }
	       catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
        }
        else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

	}

	public function create_charge($arr_data)
	{
		$this->strip_api_key = isset($arr_data['stripe_key'])?$arr_data['stripe_key']:false;

		if($this->strip_api_key == false || $this->strip_api_key == '')
		{
			$response['status'] = 'Error';
			$response['description'] = 'Please verify strip secret key details.';
			return $response;
		}

		$charge = false;

		if(isset($arr_data) && count($arr_data) > 0)
		{
			try
			{
				// $amt = isset($arr_data['order_amount'])?$arr_data['order_amount']:0;
				$amt = isset($arr_data['order_amount'])?num_format($arr_data['order_amount']):0;

				Stripe\Stripe::setApiKey($this->strip_api_key);
	          
	            $charge = \Stripe\Charge::create(array(
	             "amount"     => (float)$amt*100, 
	             "currency"   => "USD",
	             "customer"   => $arr_data['customer_id'],
	             "source"     => $arr_data['card_id'],
	             "description"=> 'Payment For Order No :'.$arr_data['order_no']
	            ));

	            return $charge;

            } 
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $msg = $e->getMessage();
	             // $msg = $error['error']['message'] . '.';
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}
		
	}

	public function retrieve_refund($charge_id,$amount,$stripe_key = false)
	{

		if($stripe_key != false )
		{
			if(isset($stripe_key[0]) && !empty($stripe_key[0]))
            {
                $this->strip_api_key = $stripe_key[0];
            }
		}

		$refund = false;

		if($charge_id)
		{
			try
			{
				Stripe\Stripe::setApiKey($this->strip_api_key);
	            $refund = \Stripe\Refund::create([
				  	'charge' => $charge_id,
				  	/*'amount' => $amount,*/
				]);

	            return $refund;
            } 
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}
	}
	
	public function vendor_retrieve_refund($vendor_id,$charge_id,$amount,$stripe_key=false)
	{
		$refund = false;
		
 		/*if($vendor_id)
		{
			$userData = $this->get_stripe_account_details($vendor_id);

 			$stripeSecretKey = isset($userData['secret_key'])?$userData['secret_key']:false;
 			
	 		if($stripeSecretKey)
	 		{*/

	 			if($stripe_key != false )
				{
					if(isset($stripe_key[0]) && !empty($stripe_key[0]))
		            {
		                $this->strip_api_key = $stripe_key[0];
		            }
				}

	 			Stripe\Stripe::setApiKey($this->strip_api_key);
	 			
				if($charge_id)
				{
					try
					{
						
			            $refund = \Stripe\Refund::create([
						  	'charge' => $charge_id,
						  	/*'amount' => $amount,*/
						]);

			            return $refund;
		            } 
					catch (\Stripe\Error\RateLimit $e) 
					{
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;

						 return $response;
					} catch (\Stripe\Error\InvalidRequest $e) {
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;
						 return $response;
					} catch (\Stripe\Error\Authentication $e) {
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;
						 return $response;
					} catch (\Stripe\Error\ApiConnection $e) {
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;
						 return $response;
					} catch (\Stripe\Error\Base $e) {
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;
						 return $response;
					} catch (Exception $e) {
						 $error = $e->getJsonBody();
			             $msg = $error['error']['message'];
						 $response['status'] = 'Error';
						 $response['description'] = $msg;
						 return $response;
					}
				}
				else
				{
					$response['status'] = 'Error';
					$response['description'] = 'Something went wrong,please try again.';
					return $response;
				}
			/*}
			else
			{
				$response['status'] = 'Error';
				$response['description'] = 'No any stripe secret key available for vendor, Please update your stripe secret key from account settings.';
				
				return $response;
			}*/
		/*}
		else
			{
				$response['status'] = 'Error';
				$response['description'] = 'Something went wrong, Unable to get your account details.';
				
				return $response;
			}*/
	}

	public function create_stripe_account($auth_code,$customer_data,$adminStripeKeyId =false,$vendorStripeKeyId=false)
	{
		$response = [];

		if(isset($auth_code) && count($customer_data) > 0)
		{
			try
			{
				\Stripe\Stripe::setApiKey($this->strip_api_key);

				if(isset($customer_data['vendor_id']))
				{
					$stripeKey = $this->UserStripeAccountDetailsModel->where('user_id',$customer_data['vendor_id'])
					                                    ->where('is_active','1')
					 									->pluck('secret_key')
					                                    ->first();

					if($stripeKey)
					{
						\Stripe\Stripe::setApiKey($stripeKey);
					}
				}
		// dd($auth_code,$customer_data,$adminStripeKeyId,$vendorStripeKeyId);

				$response = \Stripe\OAuth::token([
					          'grant_type' => 'authorization_code',
					          'code'       => $auth_code,
					        ]);

				 // Access the connected account id in the response

				if($response)
				{
			        $connected_account_id = $response->stripe_user_id;
			 		
			 		/*  Create Customer using account ID */

			        $customer_account = \Stripe\Customer::create(
											          ["email" => $customer_data['email'] ],
											          ["stripe_account" => $connected_account_id]
											        );

			       
			         if($customer_account)
			         {
			         	$data['user_id']            = $customer_data['id'];
			         	$data['admin_stripe_key_id']  = $adminStripeKeyId;
			         	$data['vendor_stripe_key_id'] = $vendorStripeKeyId;
			         	$data['stripe_acc_id']      = $connected_account_id;
			         	$data['stripe_customer_id'] = $customer_account->id;
			         	$data['user_stripe_email_id'] = $customer_data['email'];
			         	$data['vendor_id']          = isset($customer_data['vendor_id'])?$customer_data['vendor_id']:0;
			         	
			         	/*  Create Entry in Stripe Account Details Table  */

			         	$create_strip_acc_data = $this->StripeAccountDetailsModel->create($data);

			         	if($create_strip_acc_data)
			         	{
				         	$update_data['is_stripe_connected'] = '1';

				         	if(isset($customer_data['vendor_id']))
				         	{
				         		$this->UserStripeAccountDetailsModel->where('user_id',$customer_data['vendor_id'])
				         		                                    ->update(['is_admin_authorize' => 1]);
				         	}
				         	
				         	/*  Update stripe connected field in user Table  */

				         	$update_data = $this->UserModel->where('id',$customer_data['id'])->update($update_data);
			         	}
			         }

					 $response['status']      = 'success';
					 $response['description'] = $customer_account;

					 return $response;					
				}
				else
				{
					$response['status'] = 'Error';
					$response['description'] = 'Something went wrong,please try again.';
					return $response;
				}
					      
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error_description'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}
	}

	public function create_transfer($arr_data=[])
	{
		$response = [];
		
		if($arr_data && count($arr_data) > 0)
		{
			try
			{
				\Stripe\Stripe::setApiKey($this->strip_api_key);
				
				if(isset($arr_data['StripeKey']))
				{
				   \Stripe\Stripe::setApiKey($arr_data['StripeKey']);
				}
				/* Create Transfer */

				$arr_data['amount'] = isset($arr_data['amount'])?num_format($arr_data['amount']):0;
				
				$create_transfer = \Stripe\Transfer::create([
									  "amount" => $arr_data['amount'] * 100,
									  "currency" => "usd",
									  "destination" => $arr_data['account_id'],
									  "transfer_group" => "ORDER_95"
									]);

				return $create_transfer;
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}
		
	}

	// Fetching an account just needs the ID as a parameter

	public function retrive_account_details($connected_account_id)
	{
		$data = []; 

		// \Stripe\Stripe::setApiKey($this->strip_api_key));

		if($connected_account_id)
		{
	        $data = \Stripe\Account::retrieve($connected_account_id);
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

		return $data;
	}

	/*
		Auth : Jaydip
		Date : 20 Dec 2019
		Desc : send stripe account creation request to end user
	*/
	 public function connection_request($user_id=false,$clientId=false,$vendor_id=false)
	 {
	 	$user =  [];

	 	/* get end user details using userId*/

	 	$end_user_details = \Sentinel::findById($user_id);

 		$end_user_name  = isset($end_user_details->first_name)?$end_user_details->first_name:'N/A';
	 	$end_user_email = isset($end_user_details->email)?$end_user_details->email:'N/A';

	 	$user_data['end_user_name']  = $end_user_name;
	 	$user_data['end_user_email'] = $end_user_email;

        $isRoleMaker = Sentinel::findUserById($user_id)->inRole('maker');

        $isRoleAdmin = Sentinel::findUserById($user_id)->inRole('admin');

        if($isRoleMaker || $isRoleAdmin)
        {
        	$stripeApiKeyData = $this->get_active_stripe_key($user_id);
            $userStripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';  

            $getAccountDetails = get_stripe_account_details($userStripKeyId);
                
            if($getAccountDetails)
            {
              $stripeAccountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';

              $fistDigits     = isset($getAccountDetails['secret_key'])?substr($getAccountDetails['secret_key'], 0,14):'';

              $lastDigits     = isset($getAccountDetails['secret_key'])?substr($getAccountDetails['secret_key'],-7):'';

              $stripeSecretKey = $fistDigits.'XXXXXXXXXXX'.$lastDigits;
             
            } 
        	
        }
        
       
	 	/* get admin details */
	 
	    $admin_email = get_admin_email();

	    if($admin_email)
	    {
	        //$user_data['email']          = $user->email;
	        $user_data['email']         = $admin_email;
	 		$arr_view_data['user_name']  = $end_user_name;
	 		$arr_view_data['user_id']    = $user_id;
	 		$arr_view_data['client_id']  = $clientId;
	 		$arr_view_data['vendor_id']  = $vendor_id;
	 		$arr_view_data['stripe_account_holder']  = isset($stripeAccountHolder)?$stripeAccountHolder:'';
	 		$arr_view_data['stripe_secret_key']      = isset($stripeSecretKey)?$stripeSecretKey:'';
		  	
		    $content = view('email.stripe_connection',$arr_view_data)->render();

		    $content = html_entity_decode($content);
		   
		    /* Send Mail */ 
		    $send_mail = Mail::send(array(),array(), function($message) use($user_data,$content)
		    {
		      $message->from($user_data['email']);
		      $message->to($user_data['end_user_email'])
		              ->subject('Stripe Connection Request')
		              ->setBody($content, 'text/html');
		    });

		    if($send_mail == null)
		    {
		    	$response = 'success';
		    }

		    return $response;
	    }
	    else
	    {
	    	$response = 'failed' ;
	    }
	 }
 

	 public function create_direct_charge($arrData)
	 {
	 	
	 	$this->strip_api_key = isset($arrData['stripe_key'])?$arrData['stripe_key']:false;

		if($this->strip_api_key == false || $this->strip_api_key == '')
		{
			$response['status'] = 'Error';
			$response['description'] = 'Please verify strip secret key details.';
			return $response;
		}


 		$vendorId   = isset($arrData['vendorId'])?$arrData['vendorId']:false;
 		// $amount     = isset($arrData['order_amount'])?$arrData['order_amount']:false;
 		$amount     = isset($arrData['order_amount'])?num_format($arrData['order_amount']):false;
 		$retailerId = isset($arrData['retailerId'])?$arrData['retailerId']:false;
 		$adminCustomerId = isset($arrData['customerId'])?$arrData['customerId']:false;
 		$cardId     = isset($arrData['cardId'])?$arrData['cardId']:false;
 		$order_no   = isset($arrData['order_no'])?$arrData['order_no']:'';

	 	if($amount  && $vendorId && $retailerId && $adminCustomerId)
	 	{
	 		/* get vendor account stripe secret key   */
	 		$userData = $this->get_active_stripe_key($vendorId);

	 		$stripeSecretKey = isset($userData['stripeKey'])?$userData['stripeKey']:false;
	 		$stripeSecretKeyId = isset($userData['id'])?$userData['id']:false;

	 		if($stripeSecretKey)
	 		{

	 			try
		 		{ 			
				 	\Stripe\Stripe::setApiKey($stripeSecretKey);
                   
                   /* check user is exists or not in vendor stripe account */

				 	$isCustomerExsts = $this->StripeVendorRetailerMappingModel->where('vendor_id',$vendorId)
				 	                                                          ->where('user_id',$retailerId)
				 	                                                          ->where('stripe_key_id',$stripeSecretKeyId)
				 	                                                          ->first();

				 	if($isCustomerExsts)
				 	{
				 		$isCustomerExsts = $isCustomerExsts->toArray();


				 		$customerId = isset($isCustomerExsts['stripe_customer_id'])?$isCustomerExsts['stripe_customer_id']:false;

				 		$cardDetails = $this->create_card_from_existing_data($stripeSecretKey,$stripeSecretKeyId,$customerId,$cardId,$vendorId,$adminCustomerId);
				 	}
				 	else
				 	{
				 		/* create new customer */
				 		$createCustomer = $this->create_customer($stripeSecretKey,$retailerId);

				 		if($createCustomer)
				 		{
				 			$customerId = isset($createCustomer->id)?$createCustomer->id:false;

					 		$customerData = [];

					 		$customerData['stripe_key_id']   = $stripeSecretKeyId;
					 		$customerData['vendor_id']      = $vendorId;
					 		$customerData['user_id']        = $retailerId;
					 		$customerData['stripe_customer_id'] = $customerId;

					 		$addCustomerdata = $this->StripeVendorRetailerMappingModel->create($customerData);

					 		$cardDetails = $this->create_card_from_existing_data($stripeSecretKey,$stripeSecretKeyId,$customerId,$cardId,$vendorId,$adminCustomerId);
				 		}
				 		else
				 		{
				 			return false;
				 		}
				 	}

				 	if(isset($cardDetails['status']) && $cardDetails['status'] == 'Error')
				 	{
				 		$response['status'] = 'Error';
						$response['description'] = isset($cardDetails['description'])?$cardDetails['description']:'Something went wrong.';
						
						return $response;
				 	}
				 	else
				 	{

				 		/* Create charge */
					 	$charge = \Stripe\Charge::create(array(
						             "amount"     => (float)$amount*100, 
						             "currency"   => "USD",
						             "customer"   => $customerId,
						             "source"     => $cardDetails,
						             "description"=> 'Payment For Order No :'.$order_no
						            ));

						return $charge;
				 	}


										
		 		}
		 		catch (\Stripe\Error\RateLimit $e) 
				{
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;

					 return $response;
				} catch (\Stripe\Error\InvalidRequest $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\Authentication $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\ApiConnection $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\Base $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (Exception $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				}
	 		}
	 		else
			{
				$response['status'] = 'Error';
				$response['description'] = 'No any stripe scret key available for vendor, Please add stripe scret key.';
				
				return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

 	}
	

	public function create_customer_direct_charge($arrData)
	 {

	 	/* 
	 	   $arrData['vendorId']     = Maker user id (primary key)
	 	   $arrData['stripe_key']   = Admin stripe key , which is used while create order
	 	   $arrData['order_amount'] = order amount
	 	   $arrData['endUserId']    = End user id (Primary key)
	 	   $arrData['customerId']   =  Stripe customer id of which is used while generate order
	 	   $arrData['cardId']       = Stripe card id of which is used while generate order
	 	   $arrData['order_no']     = order number

	 	*/
	 	$this->strip_api_key = isset($arrData['stripe_key'])?$arrData['stripe_key']:false;

		if($this->strip_api_key == false || $this->strip_api_key == '')
		{
			$response['status'] = 'Error';
			$response['description'] = 'Please verify strip secret key details.';
			return $response;
		}


 		$vendorId   = isset($arrData['vendorId'])?$arrData['vendorId']:false;
 		// $amount     = isset($arrData['order_amount'])?$arrData['order_amount']:false;
 		$amount     = isset($arrData['order_amount'])?num_format($arrData['order_amount']):false;
 		$endUserId = isset($arrData['endUserId'])?$arrData['endUserId']:false;
 		$adminCustomerId = isset($arrData['customerId'])?$arrData['customerId']:false;
 		$cardId     = isset($arrData['cardId'])?$arrData['cardId']:false;
 		$order_no   = isset($arrData['order_no'])?$arrData['order_no']:'';

	 	if($amount  && $vendorId && $endUserId && $adminCustomerId)
	 	{
	 		/* get vendor account stripe secret key   */
	 		$userData = $this->get_active_stripe_key($vendorId);

	 		$stripeSecretKey = isset($userData['stripeKey'])?$userData['stripeKey']:false;
	 		$stripeSecretKeyId = isset($userData['id'])?$userData['id']:false;

	 		if($stripeSecretKey)
	 		{

	 			try
		 		{ 			
				 	\Stripe\Stripe::setApiKey($stripeSecretKey);
                   
                   /* check user is exists or not in vendor stripe account */

				 	$isCustomerExsts = $this->StripeVendorCustomerMappingModel->where('vendor_id',$vendorId)
				 	                                                          ->where('user_id',$endUserId)
				 	                                                           ->where('stripe_key_id',$stripeSecretKeyId)
				 	                                                          ->first();

				 	if($isCustomerExsts)
				 	{
				 		$isCustomerExsts = $isCustomerExsts->toArray();

				 		$customerId = isset($isCustomerExsts['stripe_customer_id'])?$isCustomerExsts['stripe_customer_id']:false;

				 		$cardDetails = $this->create_card_from_existing_data($stripeSecretKey,$stripeSecretKeyId,$customerId,$cardId,$vendorId,$adminCustomerId);
				 	}
				 	else
				 	{
				 		/* create new customer */

				 		$createCustomer = $this->create_customer($stripeSecretKey,$endUserId);

				 		if($createCustomer)
				 		{
				 			$customerId = isset($createCustomer->id)?$createCustomer->id:false;

					 		$customerData = [];

					 		$customerData['stripe_key_id']   = $stripeSecretKeyId;
					 		$customerData['vendor_id']      = $vendorId;
					 		$customerData['user_id']        = $endUserId;
					 		$customerData['stripe_customer_id'] = $customerId;

					 		$addCustomerdata = $this->StripeVendorCustomerMappingModel->create($customerData);

					 		$cardDetails = $this->create_card_from_existing_data($stripeSecretKey,$stripeSecretKeyId,$customerId,$cardId,$vendorId,$adminCustomerId);
				 		}
				 		else
				 		{
				 			return false;
				 		}
				 	}

				 	if(isset($cardDetails['status']) && $cardDetails['status'] == 'Error')
				 	{
				 		$response['status'] = 'Error';
						$response['description'] = isset($cardDetails['description'])?$cardDetails['description']:'Something went wrong.';
						
						return $response;
				 	}
				 	else
				 	{

				 		/* Create charge */
					 	$charge = \Stripe\Charge::create(array(
						             "amount"     => (float)$amount*100, 
						             "currency"   => "USD",
						             "customer"   => $customerId,
						             "source"     => $cardDetails,
						             "description"=> 'Payment For Order No :'.$order_no
						            ));

						return $charge;
				 	}


										
		 		}
		 		catch (\Stripe\Error\RateLimit $e) 
				{
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;

					 return $response;
				} catch (\Stripe\Error\InvalidRequest $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\Authentication $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\ApiConnection $e) {
					 $msg = $e->getMessage();
		             // $msg = $error['error']['message'] . '.';
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (\Stripe\Error\Base $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				} catch (Exception $e) {
					 $error = $e->getJsonBody();
				     $msg = $error['error']['message'];
					 $response['status'] = 'Error';
					 $response['description'] = $msg;
					 return $response;
				}
	 		}
	 		else
			{
				$response['status'] = 'Error';
				$response['description'] = 'Please add stripe account secret key..';
				
				return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}

 	}

 	public function get_stripe_account_details($user_id)
 	{
 		$userData = [];

 		$getUserAccountData = $this->UserStripeAccountDetailsModel->where('user_id',$user_id)
 		                                                          ->where('is_active','1')
 																  ->first();

 	    if($getUserAccountData)
 	    {
 	    	$userData = $getUserAccountData->toArray();
 	    }

 		return $userData;
 	}

 	public function create_customer($stripeKey,$userId)
 	{
		\Stripe\Stripe::setApiKey($stripeKey);

		$customer = '';

		$userData = Sentinel::findById($userId);

		if($userData)
		{
			$userEmail = isset($userData->email)?$userData->email:false;
	 		
	 		// Create a Customer
	        $customer = \Stripe\Customer::create(array(
	            "email" => $userEmail
	        ));

	        if($customer)
	        {
	        	return $customer;
	        }
		}

        return $customer;
 	}

 	public function create_card_from_existing_data($stripeKey,$stripeSecretKeyId=false,$customerId,$cardId,$vendorId,$adminCustomerId)
 	{
 		$response = false;

 		if($customerId && $cardId)
 		{
 			/* check card is exist on current vendor account */

 		  $adminAccCardId = $this->CardModel->where('stripe_card_id',$cardId)->pluck('id')->first();

 		  if($adminAccCardId)
 		  {
 		  	/* get current card details on vendor account details */
 		  	$isCardExist = $this->StripeCardMappingModel->where('card_id',$adminAccCardId)
 		  	                                            ->where('vendor_id',$vendorId)
 		  	                                            ->where('stripe_key_id',$stripeSecretKeyId)
 		  	                                            ->first();

 		  	if($isCardExist)
 		  	{
 		  		$cardId = isset($isCardExist->stripe_card_id)?$isCardExist->stripe_card_id:false;
 		  		
 		  		return $cardId;
 		  	}
 		  	else
 		  	{
 		  		// dd($cardId,$adminCustomerId);
 		  		/* get card details from admin stripe account */
 		  		  $existingCardData = $this->get_single_card_details($cardId,$adminCustomerId,$this->strip_api_key);

 		  		// DD($existingCardData);

		 		  if($existingCardData)
		 		  {
		 		  	  $cardNumber  = base64_decode($existingCardData->metadata->CardNo);
			 		  $expireMonth = base64_decode($existingCardData->metadata->ExpMonth);
			 		  $expireYear  = base64_decode($existingCardData->metadata->ExpYear);
			 		  $cvv         = base64_decode($existingCardData->metadata->Cvv);
		 		  }
		 		  
		 		  /* create card token on vendor stripe account */
		 		  $token = $this->create_card_token($cardNumber,$expireMonth,$expireYear,$cvv,$stripeKey,$stripeSecretKeyId);

		 		  if(isset($token['status']) && $token['status'] == 'Error')
		 		  {
		 		  	$response['status']      =  isset($token['status'])?$token['status']:'warning';
			        $response['description'] = isset($token['description'])?$token['description']:'Something went wrong,please try again.';
		 		  	
			        return $response;
		 		  }
		 		  else
		 		  {	

		 		  	  Stripe\Stripe::setApiKey($stripeKey);

			 		  /* retrive customer from vendor stripe account */
			 		  $customer = \Stripe\Customer::retrieve($customerId);

			 		  if($customer)
			 		  {
		 		  	     $checkSource = $customer->sources;

			 		  	 if($checkSource == null)
			 		  	 {
			 		  	 	$response['status']      =  'Error';
					        $response['description'] =  'Something went wrong while creating source for vendor.';
				 		  	
					        return $response;
		 		         }

					     /* create source on vendor stripe account */
		                  $card = $customer->sources->create(array(
		                    "source" => $token
		                  ));

				 		  $cardId = isset($card->id)?$card->id:false;

				 		  $cardData = [];

				 		  $cardData['card_id']        = $adminAccCardId;
				 		  $cardData['stripe_card_id'] = $cardId;
				 		  $cardData['vendor_id']      = $vendorId;
				 		  $cardData['stripe_key_id']   = $stripeSecretKeyId;

				 		  $addCard = $this->StripeCardMappingModel->create($cardData);

				 		  return $cardId;
			 		  }					
		 		  }  
		 		  
 		  	}

 		  }

 		}

 		return $response;
 	}

 	public function build_card_data_for_updation($cardId,$exp_month,$exp_year,$auth_user=false)
 	{
        $loggedinUserId = 0;
        
 		if($auth_user != false)
 		{
 			$user = $auth_user;

 			$user = Sentinel::findUserById($user->id);
 		}
 		else
 		{
          $user = Sentinel::check();
 		}
        //get loggedin user

        if($user)
        {
          $loggedinUserId = $user->id;
        }



	    $getVendorCustomerCardId = $this->StripeCardMappingModel->where('card_id',$cardId)
	                                                              ->get(['vendor_id','stripe_card_id'])
	                                                              ->toArray();

	    if($getVendorCustomerCardId && count($getVendorCustomerCardId) > 0)
	    {      	
		    foreach ($getVendorCustomerCardId as $cardData)
		    {
		      	$stripeCardId = isset($cardData['stripe_card_id'])?$cardData['stripe_card_id']:false;

		      	$getStripeKey = $this->UserStripeAccountDetailsModel->where('user_id',$cardData['vendor_id'])
		                                                             ->pluck('secret_key')
		                                                             ->first();

		        $stripeKey = isset($getStripeKey)?$getStripeKey:false;

		        if($user->inRole('customer') == true)
		        {
                   	$getCustomerId = $this->StripeVendorCustomerMappingModel->where('vendor_id',$cardData['vendor_id'])
		      	                                                         ->pluck('stripe_customer_id')
		      	                                                         ->first();
		        }
		        elseif ($user->inRole('retailer') == true)
		        {
		        	$getCustomerId = $this->StripeVendorRetailerMappingModel->where('vendor_id',$cardData['vendor_id'])
		      	                                                         ->pluck('stripe_customer_id')
		      	                                                         ->first();
		        }
		        else
		        {
		        	$getCustomerId = '';
		        }
		      	


		        $CustomerId = isset($getCustomerId)?$getCustomerId:false;


		      	$this->update_vendor_card_details($stripeKey,$stripeCardId,$CustomerId,$exp_month,$exp_year);
		    }

	    }

 	}

 	public function update_vendor_card_details($stripeKey,$stripeCardId,$CustomerId,$exp_month,$exp_year)
	{
		if($stripeKey && $stripeCardId && $CustomerId && $exp_month && $exp_year)
		{
			try{

				Stripe\Stripe::setApiKey($stripeKey);
			    $update = \Stripe\Customer::updateSource(
			         $CustomerId,$stripeCardId,
			         [
			           'exp_year' => trim($exp_year),
			           'exp_month' => trim($exp_month)
			         ]
			    );
				return $update;
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;

				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}		
	}

	public function build_card_data_for_delete($cardId)
 	{
      $getVendorCustomerCardId = $this->StripeCardMappingModel->where('card_id',$cardId)
                                                              ->get(['vendor_id','stripe_card_id'])
                                                              ->toArray();

      if($getVendorCustomerCardId && count($getVendorCustomerCardId) > 0)
      {      	
	      foreach ($getVendorCustomerCardId as $cardData)
	      {
	      	$stripeCardId = isset($cardData['stripe_card_id'])?$cardData['stripe_card_id']:false;

	      	$getStripeKey = $this->UserStripeAccountDetailsModel->where('user_id',$cardData['vendor_id'])
	                                                             ->pluck('secret_key')
	                                                             ->first();

	         $stripeKey = isset($getStripeKey)?$getStripeKey:false;
	      	
	      	 $getCustomerId = $this->StripeVendorRetailerMappingModel->where('vendor_id',$cardData['vendor_id'])
	      	                                                         ->pluck('stripe_customer_id')
	      	                                                         ->first();
	         $CustomerId = isset($getCustomerId)?$getCustomerId:false;


	      	 $this->delete_vendor_card_details($stripeKey,$stripeCardId,$CustomerId);
	      }
      }

 	}

 	public function delete_vendor_card_details($stripeKey,$stripeCardId,$CustomerId)
    {
    	
		if($stripeKey && $stripeCardId && $CustomerId)
		{
			try{

		        DB::beginTransaction();

				$getVendorCustomerCardId = $this->StripeCardMappingModel->where('stripe_card_id',$stripeCardId)
                                                                        ->delete();

				Stripe\Stripe::setApiKey($stripeKey);

	            $delete_card =  \Stripe\Customer::deleteSource(
	                              $CustomerId,$stripeCardId
	                            );

	            DB::commit();

          	  return true;
			}
			catch (\Stripe\Error\RateLimit $e) 
			{
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			} catch (\Stripe\Error\InvalidRequest $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			} catch (\Stripe\Error\Authentication $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			} catch (\Stripe\Error\ApiConnection $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			} catch (\Stripe\Error\Base $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			} catch (Exception $e) {
				 $error = $e->getJsonBody();
	             $msg = $error['error']['message'];
				 $response['status'] = 'Error';
				 $response['description'] = $msg;
				 DB::rollback();
				 return $response;
			}
		}
		else
		{
			$response['status'] = 'Error';
			$response['description'] = 'Something went wrong,please try again.';
			return $response;
		}		
	}

	public function is_card_valid($card_id)
	{
		$response = [];

		$getCardData = $this->CardModel->where('stripe_card_id',$card_id)->first();

		if($getCardData)
		{
			$cardId     = $getCardData->stripe_card_id;
			$customerId = $getCardData->stripe_customer_id;

			$cardData = $this->get_single_card_details($cardId,$customerId);

			if($cardData)
			{
				$cardYear = isset($cardData->exp_year)?$cardData->exp_year:'';
				$cardMonth = isset($cardData->exp_month)?$cardData->exp_month:'';
			}

			$currentDate = date('yy-m-d');
			$CardDate    = $cardYear.'-'.$cardMonth.'-'.'01';

			$ts1 = strtotime($currentDate);
            $ts2 = strtotime($CardDate);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $currentYear = date('Y', $ts1);

            $monthDiff = $month2 - $month1;

            if($currentYear >= $cardYear)
            {
				if($monthDiff < 0)
				{
					$response['status'] = 'error';
					$response['message'] = 'Your card is expired, Please update the card';
				}
            }
			else
			{
				$response['status'] = 'success';
			}

			return $response;

		}
	}

	/* Description : Clone existing card details of current stripe account into new stripe account */
	public function clone_card_data_into_new_account($fromAccSecretKey,$fromAccKeyId,$toAccSecretKey,$toAccKeyId,$arrCardData)
	{
		if($arrCardData && count($arrCardData))
        {
	        foreach ($arrCardData as $card) 
	        {
	            $stripeCardId = $card['stripe_card_id'];
	            $stripeCustomerId = $card['stripe_customer_id'];
	            
			    /* check if retailer have already mapped with existing card */

			    $isRetailerAlreadyMappedWithCard = $this->UserStripeCardMappingModel->where('user_id',$card['user_id'])
				                                                             ->where('to_stripe_key_id',$toAccKeyId)
				                                                             ->where('card_id',$card['id'])
				                                                             ->count();
				
				if($isRetailerAlreadyMappedWithCard == 0)
				{
					$existingCardData =  $this->get_single_card_details($stripeCardId,$stripeCustomerId,$fromAccSecretKey);

		            if($existingCardData)
		            {
		                  $cardNumber  = base64_decode($existingCardData->metadata->CardNo);
		                  $expireMonth = base64_decode($existingCardData->metadata->ExpMonth);
		                  $expireYear  = base64_decode($existingCardData->metadata->ExpYear);
		                  $cvv         = base64_decode($existingCardData->metadata->Cvv);
		            }
		              
		            /* create card token on vendor stripe account */
		            $token = $this->create_card_token($cardNumber,$expireMonth,$expireYear,$cvv,$toAccSecretKey,$toAccKeyId);

		            if(isset($token['status']) && $token['status'] == 'Error')
		            {
		                $response['status']      =  isset($token['status'])?$token['status']:'warning';
		                $response['description'] = isset($token['description'])?$token['description']:'Something went wrong,please try again.';
		                
		                return $response;
		            }
		            else
		            {

		              $isCardMappedwithUser = $this->UserStripeCardMappingModel->where('user_id',$card['user_id'])
					                                                           ->where('to_stripe_key_id',$toAccKeyId)
					                                                           ->where('fingerprint',$token->card['fingerprint'])
					                                                           ->count();

					  if($isCardMappedwithUser == 0)
					  {
					  	  Stripe\Stripe::setApiKey($toAccSecretKey);

						  /* check admin and retailer mapping */
	                      $objRetailerMappingDetails = $this->CardModel->where('user_id',$card['user_id'])
						                                               ->where('stripe_key_id',$toAccKeyId)
	                                                                   ->first();

						  if(isset($objRetailerMappingDetails) && $objRetailerMappingDetails != null)
						  {
						  	$isRetailerMapped = $objRetailerMappingDetails->toArray();

						  	if(count($isRetailerMapped) > 0)
						  	{
						  		$customerId = $isRetailerMapped['stripe_customer_id'];

						  		$customer = \Stripe\Customer::retrieve($customerId);
						  	}
						  	
						  }
						  else
					  	  {
					  		$customer = $this->create_customer($toAccSecretKey,$card['user_id']);

					  		$customerId = isset($customer->id)?$customer->id:0;
					  	  }


				 		  if($customer)
				 		  {
			 		  	     $checkSource = $customer->sources;

				 		  	 if($checkSource == null)
				 		  	 {
				 		  	 	$response['status']      =  'Error';
						        $response['description'] =  'Something went wrong while creating source for vendor.';
					 		  	
						        return $response;
			 		         }

			 		         /* check if card token is already added for new key then no need to add card again (This is use for remove only card duplication issue) */

			 		        /* $checkCardTokenIsExists = $this->CardModel->where('stripe_key_id',$toAccKeyId)
			 		                                                   ->where('fingerprint',$token->card['fingerprint'])
			 		                                                   ->count();

			 		          if($checkCardTokenIsExists > 0)
			 		          {*/
			 		          	   $stripeCard = $customer->sources->create(array(
				                            "source" => $token,
				                            "metadata" => [
		                                    "CardNo"   => base64_encode($cardNumber),
		                                    "ExpMonth" => base64_encode($expireMonth),
		                                    "ExpYear"  => base64_encode($expireYear),
		                                    "Cvv"      => base64_encode($cvv)
		                                   ]
				                          ));

					              $cardData = [];

					              $cardId = isset($stripeCard->id)?$stripeCard->id:false;

					              $cardData['user_id'] = $card['user_id'];
					              $cardData['stripe_customer_id'] = $customerId;
					              $cardData['stripe_card_id'] = $cardId;
					              $cardData['stripe_key_id'] = $toAccKeyId;
					              $cardData['fingerprint'] = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';

					              $storeCard = $this->CardModel->create($cardData);

					              $cardData = [];

					              $cardData['from_stripe_key_id']  = $fromAccKeyId;
					              $cardData['to_stripe_key_id']    = $toAccKeyId;
					              $cardData['card_id']        = $card['id'];
					              $cardData['user_id']    = $card['user_id'];
					              $cardData['admin_id']       = $this->admin_user_id;
					              $cardData['stripe_card_id'] = $cardId;
					              $cardData['stripe_customer_id']  = $customerId;
					              $cardData['fingerprint']  = isset($token->card['fingerprint'])?$token->card['fingerprint']:'';

					              $addCard = $this->UserStripeCardMappingModel->create($cardData);
			 		          // }
			              }
					  }
				    }			
	            }
            }
	
		}

		return true;
	}

	/* Verify provided stripe secret key is acive stripe secret key or not for the user */

	public function build_stripe_card_and_key_data($stripeCardId,$stripeCustomerId,$secretKey,$secretKeyId,$userId,$CardUserId)
	{
		$arrResponse = [];

		$stripeApiKeyData = $this->get_active_stripe_key($userId);

		$stripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';

		$stripSecretKey = isset($stripeApiKeyData['stripeKey'])?$stripeApiKeyData['stripeKey']:'';

		if($secretKeyId != $stripKeyId)
		{
			$arrResponse['secret_key']  = $stripSecretKey;

			$getCardId = $this->CardModel->where('stripe_card_id',$stripeCardId)->pluck('id');

			$objCardData = $this->UserStripeCardMappingModel->where('from_stripe_key_id',$secretKeyId)
			 												->where('to_stripe_key_id',$stripKeyId)
			 												->where('card_id',$getCardId)
			 												->where('user_id',$CardUserId)
			 												->orderBy('id','DESC')
			 												->first();

			$arrResponse['customer_id'] = isset($objCardData->stripe_customer_id)?$objCardData->stripe_customer_id:'';
			$arrResponse['card_id']     = isset($objCardData->stripe_card_id)?$objCardData->stripe_card_id:'';
		}
		else
		{
			$arrResponse['card_id']     = $stripeCardId;
			$arrResponse['customer_id'] = $stripeCustomerId;
			$arrResponse['secret_key']  = $secretKey;
		}
		
		return $arrResponse;
	}


	public function clone_user_stripe_account_details($stripeSecretKey=false,$userEmailId)
	{
		$response = [];

		\Stripe\Stripe::setApiKey($this->strip_api_key);

		if($stripeSecretKey != false)
		{
			\Stripe\Stripe::setApiKey($stripeSecretKey);
		}

		try
		{
			$account = \Stripe\Account::create([
			  'country' => 'US',
			  'type' => 'custom',
			  'email' => isset($userEmailId)?$userEmailId:'',
			  // 'business_type' => 'individual',
			  /*'industry' => 'test',
			  'business_website' => 'https://kadoeb2b.ml/',
			  'representative' => 'test@gmail.com',*/
			  'capabilities' => [
			    'card_payments' => [
			      'requested' => true,
			    ],
			    'transfers' => [
			      'requested' => true,
			    ],
			  ],
			]);

			
			/*$account = \Stripe\Account::create([
			  'country' => 'US',
			  'type' => 'custom',
			  'email' => isset($userEmailId)?$userEmailId:'',
			  'business_type' => 'individual',
			  'Industry Group' => 'Sub-industry group',
			  'business website' => 'https://kadoeb2b.ml/',
			  'representative' => 'test@gmail.com',
			  'ssn' => '1234',
			  'address' => '1234asdsadasdsad',
			  'capabilities' => [
			    'card_payments' => [
			      'requested' => true,
			    ],
			    'transfers' => [
			      'requested' => true,
			    ],
			  ],
			]);*/

			if($account)
			{
				/*  Create Customer using account ID */

			        $customer_account = \Stripe\Customer::create(
											          ["email" => $userEmailId ],
											          ["stripe_account" => $account->id]
											        );
			}

			$response['customer_id'] = $customer_account->id;
			$response['account_id'] = $account->id;

			return $response;
		}
		catch (\Stripe\Error\RateLimit $e) 
		{
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\InvalidRequest $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\Authentication $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\ApiConnection $e) {
			 $msg = $e->getMessage();
             // $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (\Stripe\Error\Base $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		} catch (Exception $e) {
			 $error = $e->getJsonBody();
             $msg = $error['error']['message'] . '.';
			 $response['status'] = 'Error';
			 $response['description'] = $msg;
			 return $response;
		}
	
	}


	// Retrive transaction details using tx_id
	public function retrive_transaction_details($secretKey,$txId)
	{
		$data = []; 

		try 
		{
			if(isset($secretKey) && !empty($secretKey) && isset($txId) && !empty($txId))
			{
		        // $data = \Stripe\Account::retrieve($connected_account_id);
		  //       $stripe = new \Stripe\StripeClient(
				//   'sk_test_51GqFmFCqIWM3nYzljYWfNJ54a16vyGA2B2FfkHi5E58NX3yw3PDQliQHKJVdhWsQt1CXT3MZpC5uKu5xVGvEVJ38007zTlXkiQ'
				// );
				// $stripe->charges->retrieve(
				//   'ch_1HzEgICqIWM3nYzlAbdqdXmr',
				//   []
				// );
				\Stripe\Stripe::setApiKey($secretKey);
				$data = \Stripe\Charge::retrieve($txId);

			}
			else
			{
				$response['status'] = 'Error';
				$response['description'] = 'Something went wrong,please try again.';
				return $response;
			}

			return $data;
		}
		catch (Exception $e) 
		{	
			/* TODO update transaction to failed*/ 
			Flash::error($e->getMessage());

			return redirect()->back();

		}
	}

 }
?>