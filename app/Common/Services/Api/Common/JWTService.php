<?php 

namespace App\Common\Services\Api\Common;

//use \Firebase\JWT\JWT;

use Ahc\Jwt\JWT;
use Sentinel;

class JWTService
{
	public $key = false;
	public $algo = false;

	public function __construct(){

		$this->key = config('app.JWT_SECRET');
		$this->JWT_TTL = config('app.ttl');
		$this->algo = config('jwt.algo');


	}

	public function encode($data = []) {


		$response = [

			'status' 	=> 'failure',
			'message' 	=> '',
			'data' 		=> []
		];

		try {


			$token   = (new JWT($this->key, 'HS512', $this->JWT_TTL))->encode(['user_id' => $data->id]);

			//dd($token);

			$response = [

				'status'	=> 'success',
				'message' 	=> '',
				'data' 		=> $token
			];

		} catch (Exception $e) {

			$response = [

				'status' 	=> 'success',
				'message'	=> $e->getMessage(),
				'data' 		=> []
			];	
		}

		//dd($response);

		return $response;
	}

	public function decode_token($token = false) {

		$response = [

			'status' 	=> 'failure',
			'message' 	=> '',
			'data' 		=> []
		];

		try {

			$decoded = (new JWT($this->key, 'HS512', $this->JWT_TTL))->decode($token);

			$response = [

				'status' 	=> 'success',
				'message' 	=> '',
				'data' 		=> $decoded
			];
		}
		catch(\Exception $e) {

			$response = [

				'status' 	=> 'failure',
				'message' 	=> $e->getMessage(),
				'data' 		=> []
			];
		}

		return $response;
	}	

	public function getUser($token = false){


		$response = [

			'status' 	=> 'success',
			'message' 	=> '',
			'data' 		=> []
		];

		$decoded = $this->decode_token($token); 		// Token decode Function

		if($decoded['status'] == 'failure') {

			$response['status']  = 'failure';
			$response['message'] = $decoded['message'];

			return $response;
		}

		if(isset($decoded['data']['user_id']) == false)
		{
			$response['status'] = 'failure';
			$response['message'] = 'Invalid Auth Payload';	

			return $response;
		}

		$user = Sentinel::findById($decoded['data']['user_id']);

		if($user == null) {

			$response['status']  = 'failure';
			$response['message'] = 'Invalid User';

			return $response;
		}

		$response['status'] 	= 'success';
		$response['message'] 	= 'User retrieved';	
		$response['data'] 		= $user;	

		return $response;	
	}
}