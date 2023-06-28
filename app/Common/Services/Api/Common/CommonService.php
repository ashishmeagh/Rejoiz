<?php

namespace App\Common\Services\Api\Common;

use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\CountryModel;
use App\Models\ProductDetailsModel;
use App\Common\Services\GeneralService;
use App\Models\VisitorsEnquiryModel;
use DB;
use DateTime;

class CommonService {

	public function __construct(
                  UserModel $UserModel,
                  VisitorsEnquiryModel $VisitorsEnquiryModel,
									CountryModel $CountryModel,
                  ProductDetailsModel $ProductDetailsModel,
									GeneralService $GeneralService
								) 
	{
    $this->UserModel 			    = $UserModel;
    $this->VisitorsEnquiryModel 			    = $VisitorsEnquiryModel;
		$this->CountryModel 	    = $CountryModel;
    $this->ProductDetailsModel= $ProductDetailsModel;
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

             if(isset($val['transaction_status']))
             {
                
                   if($val['transaction_status']=="1"){$new_val['transaction_status'] = "Pending";} 
                   if($val['transaction_status']=="2"){$new_val['transaction_status'] = "Paid";}
                   if($val['transaction_status']=="3"){$new_val['transaction_status'] = "Failed";}
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

              if(isset($arr_data['rep_sales_retailer_payment_status']))
             {

                 if($arr_data['rep_sales_retailer_payment_status']==0)
                 {
                   $arr_data['rep_sales_retailer_payment_status'] = "Pending";
                 } 
                 elseif($arr_data['rep_sales_retailer_payment_status']==1)
                 {
                  $arr_data['rep_sales_retailer_payment_status'] = "Paid";
                 } 
                 else
                 {
                  $arr_data['rep_sales_retailer_payment_status'] = "Failed";
                 }
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
  
  public function get_visitors_enquiry_list($form_data){

    $arr_search_column = isset($form_data['column_filter'])?$form_data['column_filter']:[];
    $visitors_enquiry_tbl        = $this->VisitorsEnquiryModel->getTable();
    $prefix_visitors_enquiry_tbl = DB::getTablePrefix().$visitors_enquiry_tbl;
      
    $vistrs_data = DB::table('visitors_enquiry')
                  ->select(DB::raw($prefix_visitors_enquiry_tbl.'.*'))
                  ->orderBy($visitors_enquiry_tbl.'.created_at','DESC');

    if(isset($arr_search_column['q_name']) && $arr_search_column['q_name']!="")
        {
            $search_term   = $arr_search_column['q_name'];
            $vistrs_data     = $vistrs_data->where($visitors_enquiry_tbl.'.name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_mobile_no']) && $arr_search_column['q_mobile_no']!="")
        {
            $search_term  = $arr_search_column['q_mobile_no'];
            $vistrs_data     = $vistrs_data->where($visitors_enquiry_tbl.'.mobile_no','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_type']) && $arr_search_column['q_type']!="")
        {
            $search_term  = $arr_search_column['q_type'];
            $vistrs_data     = $vistrs_data->where($visitors_enquiry_tbl.'.type','LIKE', '%'.$search_term.'%');
        }
       
        return $vistrs_data;
  }

  public function submit_visitors_enquiry($form_data){

        $visitors_enquiry = new VisitorsEnquiryModel();

        
        

        $visitors_enquiry->name = $form_data['name'];
        $visitors_enquiry->mobile_no = $form_data['country_code'].$form_data['mobile_no'];
        $visitors_enquiry->type = $form_data['user_type'];
    
        $visitors_enquiry->save();
        $arr_response['status'] = 'Success';
        $arr_response['msg']    = 'Visitors enquiry data saved successfully';
        return $arr_response;
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

    public function get_sku_image($sku_no)
    {
      $sku_no = (int)$sku_no;

      $image_path = '';
      
      $image = $this->ProductDetailsModel->where('sku',$sku_no)->first(['image']);

      if($image)
      {
        $image_arr = $image->toArray();

        $image_name = isset($image_arr['image'])?$image_arr['image']:'';

        $image_path = $this->imagePathProduct($image_name, 'product', 0);
      }

      return $image_path;
    
    }

}

?>