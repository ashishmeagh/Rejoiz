<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
	/* customer authentication */ 
    public function authentication($oauth_code)
    {
    	
    	\Stripe\Stripe::setApiKey('sk_test_GUfV9rgvyFI2wTiZsUVvjmvC00DZDfGz25');

		$response = \Stripe\OAuth::token([
		  'grant_type' => 'authorization_code',
		  'code' => $oauth_code,
		]);

		$connected_account_id = $response->stripe_user_id;
		dd($connected_account_id);
		
    }

   /* create new customer */
    public function create_customer($connected_account_id)
    {
    	\Stripe\Stripe::setApiKey('sk_test_GUfV9rgvyFI2wTiZsUVvjmvC00DZDfGz25');

    	 $customer_account = \Stripe\Customer::create(
											          ["email" => 'Test@gmail.com' ],
											          ["stripe_account" => $connected_account_id]
											        );

    	 dd($customer_account);
    }

    /* create charge */
    public function create_charge($connected_account_id)
    {
    	\Stripe\Stripe::setApiKey('sk_test_GUfV9rgvyFI2wTiZsUVvjmvC00DZDfGz25');
    	$charge = \Stripe\Charge::create([
				  "amount" => 1000,
				  "currency" => "usd",
				  "source" => "tok_visa",
				], ["stripe_account" => $connected_account_id]);
		
		dd($charge);
    	$create_transfer = $this->StripePaymentService->create_transfer($data);
    }

    /* create transfer */
    public function create_transfer(Request $request, $connected_account_id = false)
    {
    	\Stripe\Stripe::setApiKey('sk_test_GUfV9rgvyFI2wTiZsUVvjmvC00DZDfGz25');


    	/* Account Creation */
    	$account = \Stripe\Account::create([
						  'type' => 'express',
						  'country' => 'US',
						  'email' => 'bob_justgottohaveit_2@yopmail.com',
						  'business_type' => 'individual',
						  'tos_acceptance' => [
						  	'date' => strtotime('now'),
						  	'ip' => $request->ip(),
						  	'user_agent' => $request->header('User-Agent')
						  ],
						  'requested_capabilities' => [
						    'card_payments',
						    'transfers',
						  ],
						]);

    	dd($account);

    	/* Create Login Link for Account */

    	$link = \Stripe\Account::createLoginLink(
		  'acct_1FrNJkKJvHpT2LSC'
		);



    	/* Link External Account Card */
    	$external_account = \Stripe\Account::createExternalAccount(
		  'acct_1FrNJkKJvHpT2LSC',
		  [
		    'external_account' => 'tok_1FrNhoJCrQ4wzmU5eL2elf0T',
		  ]
		);




    	/* Link External Account Bank */
    	$external_account = \Stripe\Account::createExternalAccount(
		  'acct_1FrNJkKJvHpT2LSC',
		  [
		    'external_account' => [
		    	'object' => 'bank_account',
		    	'country' => 'US',
		    	'currency' => 'USD',
		    	'account_number' => '000123456789',
		    	'routing_number' => '110000000'
		    ],
		  ]
		);




    	/* Account Verfication Link */
		$account_link = \Stripe\AccountLink::create([
		  'account' => 'acct_1FrNJkKJvHpT2LSC',
		  'failure_url' => 'https://example.com/failure',
		  'success_url' => 'https://example.com/success',
		  'type' => 'custom_account_verification',
		]);



    	
    	/* Transfer Creation */
    	$create_transfer = \Stripe\Transfer::create([
									  "amount" => 400,
									  "currency" => "usd",
									  "destination" => $account->id,
									]);
		
									
    }
}
