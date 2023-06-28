<?php

namespace App\Common\Services\Api\Representative;

use App\Models\UserModel;
use App\Models\NotificationsModel;

use \paginate;

class NotificationsService {

	public function __construct(NotificationsModel $NotificationsModel)  {

			$this->NotificationsModel 	=	 $NotificationsModel;
	}

    public function get_list($user_id = 0,$perpage,$type='representative') {

    	try {


    		 $str_created_date = $str_timestamp = $str_created_at = "";

    		$paginate = isset($perpage) ? $perpage : 10;

	    	$notifications_obj = $this->NotificationsModel
			    						->select( 'id', 'type', 'title', 'description', 'is_read',  'status' , 'created_at' , 'notification_url' )
			    						->orderBy('id','DESC')
			                            ->where('type',$type)
			                            ->where('to_user_id',$user_id)
			                            ->paginate($paginate);

        	$arr_response['data'] = [];

	        if ($notifications_obj) {

	        	$notifications_arr = $notifications_obj->toArray();
	        	$order_number      = "";

	        	foreach ($notifications_arr['data'] as $key => $value) {

					$arr_response['data'][$key]["id"]	=  $value["id"]; 

					$pos = strpos($value["description"], 'J2');

					if($pos!=false)
					{
						$order_number =  substr($value["description"],$pos,10);
					}

				/*	$pieces 		= explode(' ', $value["description"]);

					$order_number 	= array_pop($pieces);*/

        			$arr_response['data'][$key]["order_number"]	= $order_number;	// order number

        			$str_arr = explode ("/", $value["notification_url"]);   
        		
        			$arr_response['data'][$key]["order_id"]	= base64_decode($str_arr[6]);	// order id
        			$arr_response['data'][$key]["notification_url"]	= $value["notification_url"];	// order id

				/*-------------------------------------- Notification Type ---------------------------------------*/
					 
					$arr_response['data'][$key]["title"]	 	= $value["title"];  
					$arr_response['data'][$key]["description"] 	= $value["description"];
					$arr_response['data'][$key]["is_read"]	 	= $value["is_read"]; 
					$arr_response['data'][$key]["status"]	 	= $value["status"]; 
					

					if(isset($value['created_at']) && !empty($value['created_at']))
					{	
						$str_created_date 	= date('m-d-Y',strtotime($value['created_at'])) ;

						$str_timestamp      = date("g:i A",strtotime($value['created_at']));

	                    $str_created_at     = $str_created_date." | ".$str_timestamp;

	                    $arr_response['data'][$key]["created_at"]   = $str_created_at;
                    }

                    else
                    {
                    	$arr_response['data'][$key]["created_at"]   = ""  ;
                    }
	        	}
	        }

			$arr_response['pagination']["first_page_url"] 	= $notifications_arr["first_page_url"]; 
			$arr_response['pagination']["from"] 			= $notifications_arr["from"]; 
			$arr_response['pagination']["last_page"] 		= $notifications_arr["last_page"]; 
			$arr_response['pagination']["last_page_url"] 	= $notifications_arr["last_page_url"]; 
			$arr_response['pagination']["next_page_url"]	= $notifications_arr["next_page_url"]; 
			$arr_response['pagination']["path"] 			= $notifications_arr["path"]; 
			$arr_response['pagination']["per_page"] 		= $notifications_arr["per_page"]; 
			$arr_response['pagination']["prev_page_url"] 	= $notifications_arr["prev_page_url"]; 
			$arr_response['pagination']["to"] 				= $notifications_arr["to"]; 
			$arr_response['pagination']["total"] 			= $notifications_arr["total"]; 

	  		$response['status']		= 'success';
			$response['message']	= 'Notifications list get successfully.';
			$response['data']		= $arr_response;
			
			return $response;
    		
    	} catch (Exception $e) {
    		
    		$response['status']		= 'failure';
			$response['message']	= $e->getMessage();
			$response['data']		= [];
			
			return $response;
    	}
    }

    public function delete($user_id,$notifications_id) {

    	try {
 
    		$delete = $this->NotificationsModel
                            ->where('id',$notifications_id)
                            ->delete();
                            
    		$response['status']		= 'success';
			$response['message']	= 'Notifications deleted successfully.';
			$response['data']		= [];

			return $response;

    	} catch (Exception $e) {

    		$response['status']		= 'failure';
			$response['message']	= $e->getMessage();
			$response['data']		= [];
			
			return $response;
    	}
    }

    public function change_view_status($user_id,$notification_id) {

    	try {

    		$update = $this->NotificationsModel
		                    ->where('id',$notification_id)
		                    ->update(['is_read' => "1"]);
		                    
	        $response['status']		= 'success';
			$response['message']	= 'Notifications view status changed successfully.';
			$response['data']		= [];

			return $response;
    		
    	} catch (Exception $e) {

    		$response['status']		= 'failure';
			$response['message']	= $e->getMessage();
			$response['data']		= [];
			
			return $response;
    	}
    }

    public function count($user_id = 0,$type='representative') 
    {
    	try {

    		$notifications_count = $this->NotificationsModel
			                            ->where([ 	'type'			=> $type,
													'to_user_id'	=> $user_id,
				                            		'is_read' 		=> "0" ])
			                            ->count();

            $data['notifications_count'] = $notifications_count;

        	$response['status']		= 'success';
			$response['message']	= 'Notifications count get successfully.';
			$response['data']		= $data;

			return $response;

    	} catch (Exception $e) {

    		$response['status']		= 'failure';
			$response['message']	= $e->getMessage();
			$response['data']		= [];
			
			return $response;	
    	}
	}
}

?>