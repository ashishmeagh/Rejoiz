<?php

namespace App\Http\Controllers\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GeneralSettingModel;
use App\Models\TransactionsModel;
use App\Models\TransactionsDetailsModel;
use App\Models\TempBagModel;
use App\Common\Services\StripePaymentService;

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
use Cartalyst\Stripe\Stripe;

class PaymentController extends Controller
{
    /*
	| Author : Sagar B. Jadhav
	| Date   : 20 Aug 2019
    */

     public function __construct(GeneralSettingModel $GeneralSettingModel,
     						     TransactionsModel $TransactionsModel,
     						     TransactionsDetailsModel $TransactionsDetailsModel,
     						     TempBagModel $TempBagModel,
     						     StripePaymentService $StripePaymentService
     							)
    {  		
    	$this->arr_view_data      	 	= [];
    	$this->module_title          	= "Payment Transaction";    
    	$this->retailer_panel_slug   	= config('app.project.retailer_panel_slug');
        $this->module_url_path       	= url($this->retailer_panel_slug);  
        $this->GeneralSettingModel   	= $GeneralSettingModel;  
        $this->TransactionsModel     	= $TransactionsModel;
        $this->TransactionsDetailsModel = $TransactionsDetailsModel;
        $this->TempBagModel 			= $TempBagModel;
        $this->StripePaymentService     = $StripePaymentService;

    }


    public function set_session(Request $request)
    {
    	$wholesale_total = $request->input('wholesale_total');

    	$bag_id = $request->input('bag_id');

    	$wholesale_total = (float)$wholesale_total;

    	session::put('amount',$wholesale_total);
    	session::put('bag_id',$bag_id);

    	if(session::has('amount'))
    	{
    		$response['status'] = 'SUCCESS';	
    	}
    	else
    	{
    		$response['status'] = 'FAILURE';
    	}

    	return response()->json($response);
    }

    public function checkout()
    {
    	if(!session::has('amount'))
    	{
    		return redirect()->back();
    	}

    	$amount = session::get('amount');

    	if(!session::has('bag_id'))
		{
			return redirect()->back();
		}
		

        $this->arr_view_data['amount']          = $amount;
        $this->arr_view_data['page_title']      = 'Payment Transactions';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
     
    	return view('front.checkout',$this->arr_view_data);
    }

    public function postPaymentWithStripe(Request $request)
 	{	
        $stripe_secret_obj = $this->GeneralSettingModel->where('data_id','STRIPE_SECRET_KEY')->where('type','admin')->first();

        $stripe_secret_key = isset($stripe_secret_obj->data_value)?$stripe_secret_obj->data_value:'';
         
        $stripe = Stripe::make($stripe_secret_key);

        $token = $stripe->tokens()->create([
                            'card' => [
                            'number'    => $input['card_no'],
                            'exp_month' => $input['ccExpiryMonth'],
                            'exp_year'  => $input['ccExpiryYear'],
                            'cvc'       => $input['cvvNumber'],
                        ],
        ]);

 		$loggedInUserId = 0;
 		$user = Sentinel::check();

        if($user && $user->inRole('retailer'))
        {
            $loggedInUserId = $user->id;
        }
        else
        {
        	Sentinel::logout();
        	Flash::error('Please login with retailer for buying product.');
        	return redirect('/login');
        }  
        
        $process_payment = $this->StripePaymentService->process_payment($request->all());

		dd($process_payment);
	}
}
