<?php

namespace App\Http\Controllers\Front;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\MailChimpService;
 
class MailchimpController extends Controller
{
	public function __construct(MailChimpService $MailChimpService)
	{
		$this->MailChimpService = $MailChimpService;	
	}

	public function subscribe(Request $request) 
	{     
		$email = $request->input('email_address'); 
		$sentmail = $this->MailChimpService->subscribe($email);


		$response = [];
		if($sentmail==true){
			$response['status']      = 'SUCCESS';
			$response['description'] = 'Subscription has been done.';
		}
		else{
			$response['status']      = 'ERROR';
			$response['description'] = 'Something went wrong, please try again.';	
		}
		
		return response()->json($response);
	}
}	