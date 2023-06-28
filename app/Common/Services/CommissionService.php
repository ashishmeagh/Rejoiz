<?php
namespace App\Common\Services;

use App\Models\MakerModel;
use App\Models\UserModel;

use Mail;
use Request;
use Stripe;
use Carbon\Carbon;
use Flash;


class CommissionService
{

	public function __construct(MakerModel $MakerModel,
								UserModel $UserModel
								)
	{
		$this->MakerModel 					= $MakerModel;
		$this->UserModel                  	= $UserModel;
	}


   	/*function for get admin commission*/
   	public function get_admin_commission($maker_id=false)
   	{
   		$admin_commission      = 0;
	
   		/*Get site setting data from helper*/
        $arr_site_setting = get_site_settings(['site_name','commission']);

		$admin_commission      = isset($arr_site_setting['commission'])?num_format($arr_site_setting['commission']):0;  

		if($maker_id)
		{
		    $obj_admin_commission_data = $this->MakerModel->where('user_id',$maker_id)->first();
		                                        //->first('admin_commission');
		                                        //->toArray();
		    if($obj_admin_commission_data)
		    {
		      $arr_admin_commission_data = $obj_admin_commission_data->toArray();
		      
		      $admin_commission = isset($arr_admin_commission_data['admin_commission'])?num_format($arr_admin_commission_data['admin_commission']):0;
		    }


		    if($admin_commission == 0)
		    {
		      $admin_commission = isset($arr_site_setting['commission'])?num_format($arr_site_setting['commission']):0;   
		    }
		}
		 
		return $admin_commission;
   	}

   	/*function for get representative commission*/
   	function get_representative_commission($user_id=false)
	{
	  	$representative_commission = 0;

	  	if($user_id)
	  	{
		    $representative_commission =  $this->UserModel->where('id',$user_id)->select(['commission'])->first();

		    if(isset($representative_commission))
		    {
		       $representative_commission =  $representative_commission->toArray();
		    }
		    
		    $representative_commission = isset($representative_commission['commission'])?$representative_commission['commission']:0;  

		    if($representative_commission == 0)  
		    {
		    	/*Get site setting data from helper*/
	        	$representative_commission = get_site_settings(['site_name','representative_commission']);
		      
		        $representative_commission = isset($representative_commission['representative_commission'])?$representative_commission['representative_commission']:0;
		    }
	  	}
	  	else
	  	{
		  	/*Get site setting data from helper*/
	        $representative_commission = get_site_settings(['site_name','representative_commission']);
		  
		    $representative_commission = isset($representative_commission['representative_commission'])?$representative_commission['representative_commission']:0;
	  	}

	  	return $representative_commission;
	}

	/*function for get sales manager commission*/
	function get_sales_manager_commission($user_id=false)
	{
	  $salesmanager_commision = 0;

	  if($user_id)
	  {
	    $obj_salesmanager_commision =  $this->UserModel->where('id',$user_id)->first(['commission']);

	    if($obj_salesmanager_commision)
	    {
	    	$salesmanager_commision = $obj_salesmanager_commision->toArray();
	    }
	    
	    $salesmanager_commision = isset($salesmanager_commision['commission'])?$salesmanager_commision['commission']:0;  

	   if($salesmanager_commision == 0)  
	    {
	    	/*Get site setting data from helper*/
	        $salesmanager_commision = get_site_settings(['site_name','salesmanager_commission']);
	  
	      	$salesmanager_commision = isset($salesmanager_commision['salesmanager_commission'])?$salesmanager_commision['salesmanager_commission']:0;
	    }  
	  }
	  else
	  {
	    /*Get site setting data from helper*/
	    $salesmanager_commision = get_site_settings(['site_name','salesmanager_commission']);
	  
	    $salesmanager_commision = isset($salesmanager_commision['salesmanager_commission'])?$salesmanager_commision['salesmanager_commission']:0;
	  }

	  return $salesmanager_commision;
	}
}

?>