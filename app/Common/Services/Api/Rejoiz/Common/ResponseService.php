<?php 

namespace App\Common\Services\Api\Rejoiz\Common;

use \Exception;

class ResponseService {

	public function send($arr_response = [],$http_code = false) {

		try {


			if(isset($arr_response['status']) == false) {

				throw new Exception("Response Status missing");
			}

			if(isset($arr_response['message']) == false) {

				throw new Exception("Response Message missing");
			}

			$arr_response['status'] = strtolower($arr_response['status']);

			if(in_array($arr_response['status'], ['success','failure']) == false) {

				throw new Exception("Response status can be success or failure only");	
			}


			if($http_code == false && $arr_response['status'] == 'failure') {

				$http_code = 500;
			}
			elseif($http_code == false && $arr_response['status'] == 'success') {

				$http_code = 200;
			}

			return response()->json($arr_response,$http_code);
			
		} catch (Exception $e) {

			$response['status']		= 'failure';
			$response['message']	= $e->getMessage();
			$response['data']		= [];
			
			return $response;
		}
	}
}
