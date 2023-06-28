<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Common\Services\Api\Rejoiz\Retailer\CardsService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;
use Validator;

class CardController extends Controller
{
	public function __construct(
	                                CardsService $CardsService,
	                                ResponseService $ResponseService
	                           )
	{
	    $this->CardsService       = $CardsService;
	    $this->ResponseService    = $ResponseService;
	}

	public function index(Request $request)
    {
       $form_data  = $request->all();
       $user       = $request->input('auth_user');
       $user_id    = $user->id;

       $response   = $this->CardsService->get_user_cards($user_id);

       return $this->ResponseService->send($response);  
    }

    public function add(Request $request)
    {
       $form_data  = $request->all();
       $user       = $request->input('auth_user');

       $arr_rule=[
	                'number' => 'required',
	                'expiry' => 'required',
	                'cvc'    => 'required',
	             ];

        $validator = Validator::make($request->all(),$arr_rule);

        if($validator->fails())
        {
           $response['status']      = 'failure';
           $response['message'] = 'Form validation failed, please check all fields.';

          return $response;
        }

       $response   = $this->CardsService->store($user,$form_data);

       return $this->ResponseService->send($response);  
    }

    public function edit(Request $request)
    {
 		$card_id     = $request->input('card_id');
 		
 		$customer_id = $request->input('customer_id');

 		$response   = $this->CardsService->edit($card_id,$customer_id);
     
        return $this->ResponseService->send($response);
    }

    public function update(Request $request)
    {
    	$form_data = $request->all();

        $arr_rule=['expiry' => 'required'];

        $validator = Validator::make($request->all(),$arr_rule);

        if($validator->fails())
        {
           $response['status'] = 'warning';
           $response['description'] =' Form validation failed, please check all fields.';

          return $response;
        }

    	$card_id     = $request->input('card_id');
 		
 		$customer_id = $request->input('customer_id');

 		$response   = $this->CardsService->update($form_data);
     
        return $this->ResponseService->send($response);
    }

    public function delete(Request $request)
    {
 		$form_data = $request->all();

 		$response   = $this->CardsService->delete($form_data);
   
        return $this->ResponseService->send($response);
    }

}