<?php

namespace App\Common\Services\Api\Common;

use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\CountryModel;
use App\Common\Services\GeneralService;


class CommonService {

	public function __construct(
									UserModel $UserModel,
									CountryModel $CountryModel,
									GeneralService $GeneralService
								) 
	{
		$this->UserModel 			= $UserModel;
		$this->CountryModel 	    = $CountryModel;
		$this->GeneralService 		= $GeneralService;
	}

	public function get_all_countries() {

		try {

             $arr_data           = "";  
		         $obj_data           = $this->CountryModel->select('id','name','phonecode','zipcode_length')->where('is_active',1)->orderBy('id','ASC')->get(); 

		         if($obj_data!=null)
		         {
		         	$arr_data        = $obj_data->toArray();   
		         } 


                    if(isset($arr_data) && !empty($arr_data))
                    {	
    		           		$response['status']                = 'success';
          						$response['message']               = 'Countries get successfully.';
          						$response['data'] 		             =  isset($arr_data)?$arr_data:[];

			      		      return $response;
		      	        }

    		      	    else
    		      	    {
    		      	    	$response['status']                = 'failure';
          						$response['message']               = 'Something went wrong ,please try again.';
          						$response['data'] 		             = '';

          						return $response;
    		      	    }
	            } 
			
          catch (Exception $e) {
			$response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      		return $response;
    	}
	}


	public function get_phone_code($country_id=null)
	{
		try
		{
           if($country_id!=null)
           {
           	  $obj_data = $this->CountryModel->select('phonecode')->where('id',$country_id)->first(); 
           	  if($obj_data!=null)
           	  {
           	  	 $arr_data = $obj_data->toArray();
           	  }

           	  if(isset($arr_data) && !empty($arr_data))
	          {	
	           		$response['status']              = 'success';
      					$response['message']             = 'Country code get successfully.';
      					$response['data'] 		           =  isset($arr_data)?$arr_data:[];

		      		return $response;
	      	  }

	      	  else
	      	  {
	      	    	$response['status']               = 'failure';
      					$response['message']              = 'Something went wrong ,please try again.';
      					$response['data'] 		            = '';

					return $response;
	      	  } 
           } 
		}

		catch(Exception $e)
		{
		   $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong.',
				'data'    => ''
			];

      	   return $response;	 

		}
	}


	public function get_status_display_names($arr_data=null,$type=null)
	{
       try
       {
       	  $suffix  = "_display"; 
       	  $val_new = array();

        if($type!=null && $type=='listing')
        {  
          if(isset($arr_data) && !empty($arr_data))
          {
          	$new_val = [];

          	 foreach($arr_data as $key => $val)
          	{ 

             $arr_data[$key]['status_display'] = [];

             if(isset($val['status']))
             {
             	  if($val['status']==0) {$new_val['status'.$suffix]   = "Block"; }	

                  if($val['status']==1) {$new_val['status'.$suffix]   = "Active";} 

             }

             if(isset($val['shipping_status']))
             {
                   if($val['shipping_status']==0){$new_val['shipping_status'.$suffix] = "Pending";} 

                   if($val['shipping_status']==1){$new_val['shipping_status'.$suffix] = "Shipped";}

                   if($val['shipping_status']==2){$new_val['shipping_status'.$suffix] = "Failed";} 
             }

             if(isset($val['retailer_approval']))
             {
                   if($val['retailer_approval']==1){$new_val['retailer_approval'.$suffix] = "Approved";}

                   if($val['retailer_approval']==2){$new_val['retailer_approval'.$suffix] = "Pending";}

                   if($val['retailer_approval']==3){$new_val['retailer_approval'.$suffix] = "Rejected";}

                   if($val['retailer_approval']==4){$new_val['retailer_approval'.$suffix] = "Pending";} 
             }

             if(isset($val['retailer_payment_status']))
             {

             	   if($val['retailer_payment_status']==0){$new_val['retailer_payment_status'.$suffix] = "Pending";} 
                   if($val['retailer_payment_status']==1){$new_val['retailer_payment_status'.$suffix] = "Pending";} 
                   if($val['retailer_payment_status']==2){$new_val['retailer_payment_status'.$suffix] = "Paid";}

                   if($val['retailer_payment_status']==3){$new_val['retailer_payment_status'.$suffix] = "Failed";}
             }

             if(isset($val['representative_commision']))
             {
             	  
                   if($val['representative_commision']=="1"){$new_val['representative_commision'.$suffix] = "Pending";} 
                   if($val['representative_commision']=="2"){$new_val['representative_commision'.$suffix] = "Paid";}
                   if($val['representative_commision']=="3"){$new_val['representative_commision'.$suffix] = "Failed";}
              }

              array_push($arr_data[$key]['status_display'],$new_val);
            }

            return $arr_data;
          }  
        }

        else if($type!=null && $type=='details')
        {
           if(isset($arr_data) && !empty($arr_data))
           {
           	   if(isset($arr_data['status']))
             {
             	  if($arr_data['status']==0) {$arr_data['status'.$suffix]   = "Block"; }	

                  if($arr_data['status']==1) {$arr_data['status'.$suffix]   = "Active";} 
             }

              if(isset($arr_data['shipping_status']))
             {
                   if($arr_data['shipping_status']==0){$arr_data['shipping_status'.$suffix] = "Pending";} 

                   if($arr_data['shipping_status']==1){$arr_data['shipping_status'.$suffix] = "Shipped";}

                   if($arr_data['shipping_status']==2){$arr_data['shipping_status'.$suffix] = "Failed";} 
             }

             if(isset($arr_data['retailer_approval']))
             {

                   if($arr_data['retailer_approval']==1){$arr_data['retailer_approval'.$suffix] = "Approved";}

                   if($arr_data['retailer_approval']==2){$arr_data['retailer_approval'.$suffix] = "Pending";}

                   if($arr_data['retailer_approval']==3){$arr_data['retailer_approval'.$suffix] = "Rejected";}

                   if($arr_data['retailer_approval']==4){$arr_data['retailer_approval'.$suffix] = "Pending";} 
             }

             if(isset($arr_data['retailer_payment_status']))
             {
             	   if($arr_data['retailer_payment_status']==0){$arr_data['retailer_payment_status'.$suffix] = "Pending";} 
                   if($arr_data['retailer_payment_status']==1){$arr_data['retailer_payment_status'.$suffix] = "Pending";} 
                   if($arr_data['retailer_payment_status']==2){$arr_data['retailer_payment_status'.$suffix] = "Paid";}

                   if($arr_data['retailer_payment_status']==3){$arr_data['retailer_payment_status'.$suffix] = "Failed";}
             }

             if(isset($arr_data['representative_commision']))
             {
                   if($arr_data['representative_commision']=="0"){$arr_data['representative_commision'.$suffix] = "Pending";} 
                   if($arr_data['representative_commision']=="1"){$arr_data['representative_commision'.$suffix] = "Paid";}
                   if($arr_data['representative_commision']=="2"){$arr_data['representative_commision'.$suffix] = "Failed";}
             }

           }	

            return $arr_data;
         }  

          return $arr_data;
       }

       catch(Exception $e)
       {
       	   $response = [
				'status'  => 'failure',
				'message' => 'Something went wrong, please try again.',
				'data'    => ''
			];

      	   return $response;
       }
	}


     // Show image 
  public function imagePathProduct($image_name, $image_type, $is_resize)
  {
      $imagePath = "";
      $URL = url('/');
      $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

      if($image_type == "product")
      {
        $isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
        
        if($isFileExixst && $is_resize == 0 && $image_name != "")
        {
          $imagePath = url('/storage/app/'.$image_name);
        }
        elseif($isFileExixst && $is_resize == 1 && $image_name != "")
        {
          $imagePath = image_resize(url('/storage/app/'.$image_name));
        }
        else
          $imagePath = $URL.config('app.project.img_path.product_default_images_app');
      }
      return $imagePath;
    }

}

?>