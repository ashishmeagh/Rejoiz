<?php
namespace App\Common\Services;

use App\Models\MakerModel;
use App\Models\UserModel;
use App\Models\RetailerQuotesModel;
use App\Models\CustomerQuotesModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\OrderTrackDetailsModel;
use App\Models\CategoryDivisionModel;
use App\Models\BrandsModel;
use App\Models\PromoCodeModel;
use App\Models\PromotionsModel;
use App\Models\PromotionsOffersModel;
use App\Models\SubCategoryModel;
use App\Models\RepresentativeMakersModel;

use Mail;
use Request;
use Stripe;
use Carbon\Carbon;
use Flash;
use DB;

class HelperService
{	
	public function __construct(MakerModel $MakerModel,
								UserModel $UserModel,
								RetailerQuotesModel $RetailerQuotesModel,
								RepresentativeLeadsModel $RepresentativeLeadsModel,
								CustomerQuotesModel $CustomerQuotesModel,
								OrderTrackDetailsModel $OrderTrackDetailsModel,
								CategoryDivisionModel $CategoryDivisionModel,
								BrandsModel $BrandsModel,
								PromoCodeModel $PromoCodeModel,
								PromotionsModel $PromotionsModel,
								PromotionsOffersModel $PromotionsOffersModels,
								SubCategoryModel $SubCategoryModel,
								RepresentativeMakersModel $RepresentativeMakersModel
								)
	{
		$this->MakerModel 					= $MakerModel;
		$this->UserModel                  	= $UserModel;
		$this->RetailerQuotesModel      	= $RetailerQuotesModel;
		$this->CustomerQuotesModel 			= $CustomerQuotesModel;
		$this->OrderTrackDetailsModel 		= $OrderTrackDetailsModel;
		$this->CategoryDivisionModel 		= $CategoryDivisionModel;
		$this->BrandsModel					= $BrandsModel;
		$this->SubCategoryModel 			= $SubCategoryModel;
		$this->RepresentativeMakersModel 	= $RepresentativeMakersModel;
	}


	public function get_payment_term($order_no,$order_id)
	{   
	    /*check whether it is rep/sales order or retailer order*/
	    $count =RetailerQuotesModel::where('order_no',$order_no)->count();

	    if($count >0)
	    {
	      //retailer order
	      $payment_term = RetailerQuotesModel::where('order_no',$order_no)->where('id',$order_id)->pluck('payment_term')->first();
	    }
	    else
	    {
	      //rep/sales order
	      $payment_term = RepresentativeLeadsModel::where('order_no',$order_no)->where('id',$order_id)->pluck('payment_term')->first();
	    }

	    return $payment_term; 
	}

	/*get user mail using id*/
	public function get_user_mail($user_id=false)
	{
	  $email = "";
	   $obj_user = UserModel::where('id',$user_id)->first();
	   if(isset($obj_user))
	   {
	    $email = $obj_user['email'];
	   }
	  return $email;
	}
	/*end*/


	public function calculate_product_discount($product_discount_type,$product_discount_min_amt,$product_discount,$total_price)
	{
	  $product_discount_type = isset($product_discount_type)?(float)num_format($product_discount_type):0.00;

	  $product_discount_min_amt = isset($product_discount_min_amt)?(float)num_format($product_discount_min_amt):0.00;

	  $product_discount = isset($product_discount)?(float)num_format($product_discount):0.00;

	  $total_price=isset($total_price)?(float)num_format($total_price):0.00;


	  $pro_discount = 0;

	  if($product_discount_min_amt <= $total_price)
	  {
	    if($product_discount_type==1)
	    { 
	       /*Discount = Original Price x Discount % / 100*/
	        $pro_discount =  $total_price * $product_discount/ 100;
	        
	    }
	    elseif($product_discount_type==2)
	    {
	      $pro_discount = $product_discount;
	    }
	     return $pro_discount;
	  }
	  else
	  {
	     $pro_discount = 0;
	     return $pro_discount;
	  }

	}


	public function calculate_shipping_discount($shipping_type,$wholesale_price,$minimum_amount_off,$off_type_amount,$shipping_charge)
	{
	    if($shipping_type==1)
	    {
	      $shipping_discount = 0;
	      if($wholesale_price < $minimum_amount_off)
	      {
	        $shipping_charge = $shipping_charge;

	        $product_ship_charge = $shipping_charge;
	      }
	      else
	      {
	        $shipping_charge = 0;
	        $product_ship_charge = 0;
	      }
	    }
	    elseif($shipping_type==2)
	    {
	      if($wholesale_price>=$minimum_amount_off)
	      {
	        /*Discount = Original Price x Discount % / 100*/
	       
	        if(is_numeric($shipping_charge) && is_numeric($off_type_amount))
	         { 
	          $shipping_discount   =  $shipping_charge * $off_type_amount/ 100;
	          $product_ship_charge = $shipping_charge;
	         }
	        else
	         {
	           $shipping_discount   =  0;
	           $product_ship_charge = 0;
	         }

	    
	      }
	      else
	      {
	        $shipping_charge   = $shipping_charge;
	        $shipping_discount = 0;

	        $product_ship_charge = $shipping_charge;
	      }
	    }
	    elseif($shipping_type==3)
	    {
	      if($wholesale_price>=$minimum_amount_off)
	      {
	        $shipping_discount   =  $off_type_amount;
	        $product_ship_charge = $shipping_charge;
	      }
	    else
	      {
	        $shipping_charge     = $shipping_charge;
	        $shipping_discount   = 0;
	        $product_ship_charge = $shipping_charge;
	      }
	    }
	    else
	    {
	        $shipping_charge     = 0;
	        $shipping_discount   = 0;
	        $product_ship_charge = 0;
	    }

	    $shipping_arr = [];
	    $shipping_arr['shipping_charge']     = $shipping_charge;
	    $shipping_arr['shipping_discount']   = $shipping_discount;
	    
	    $shipping_arr['product_ship_charge'] = $product_ship_charge;

	    return $shipping_arr;
	}


	public function get_order_calculation_data($orderNo,$vendorId,$userSegment)
	{
	    $response = '';
	    $orderNo = base64_decode($orderNo);
	    $vendorId = base64_decode($vendorId);

	   /* return $orderID = RetailerQuotesModel::where('order_no',$orderNo)
	                                    ->where('maker_id',$vendorId)
	                                    ->pluck('id');*/
	    if($userSegment == 'representative' || $userSegment == 'sales_manager')
	    {
	      $orderData = RepresentativeLeadsModel::where('order_no',$orderNo)
	                                            ->where('maker_id',$vendorId)
	                                            ->first();
	      if($orderData)                                  
	      {
	        $response = $this->get_reps_sales_order_summary_data($orderData->promo_code,$orderData->id);
	      }
	    }

	    if($userSegment == 'retailer')
	    {
	       $orderData = RetailerQuotesModel::where('order_no',$orderNo)
	                                    ->where('maker_id',$vendorId)
	                                    ->first();

	      if($orderData)                                  
	      {
	        $response = $this->get_retailer_order_summary_data($orderData->promo_code,$orderData->id);
	      }
	    }    

	    if($userSegment == 'customer')
	    {  
	       $orderData = CustomerQuotesModel::where('order_no',$orderNo)
	                                    ->where('maker_id',$vendorId)
	                                    ->first();

	      if($orderData)                                  
	      {
	        $response = $this->get_customer_order_summary_data($orderData->promo_code,$orderData->id);
	      }
	    }
	  
	    return $response;
	}

	public function getTrackingDetails($order_id,$order_no,$is_tracking_no=false)
	{
	   
	    $track_details = [];
	    $tracking_no ='';
	    $tracking_details = OrderTrackDetailsModel::where('order_id',$order_id)
	                                               ->where('order_no',$order_no)
	                                               ->first();

	    
	    if(isset($tracking_details) && $tracking_details!='')
	    {  
	        $track_details = $tracking_details->toArray();
	    }                                     
	    if($is_tracking_no)
	    {
	    	$tracking_no = $track_details['tracking_no'];
	    	return $tracking_no;
	    }
	    return $track_details;
	}
	public function get_cat_division($cat_div_id)
	{
	    if(isset($cat_div_id) && $cat_div_id!=false)
	    {
		    $obj_cat_division = CategoryDivisionModel::where('id',$cat_div_id)->first();

		    if(isset($obj_cat_division))
		    {
		        $category_name = $obj_cat_division['cat_division_name'];
		        return $category_name;
		    }
		}
	    return false;
	}
	public function get_all_brands($country_id = false)
	{ 
		
	  $active_users = $active_users_ids = $brand_names = [];

	  ini_set('max_execution_time', 0);
	  
	  $obj_active_users =   UserModel::select(DB::raw('users'.'.id as id'))
	                                 ->join('makers','makers'.'.user_id','=','users'.'.id')
	                                 ->where('users'.'.status',1)
	                                 ->where('users'.'.is_approved',1);

	                                 if(isset($country_id) && $country_id!=false)
	                                 {
	                                 	$obj_active_users->where('country_id',$country_id);
	                                 }

	$obj_active_users = $obj_active_users->get();


	  if(isset($obj_active_users))
	  {
	    $active_users = $obj_active_users->toArray();
	    
	  }

	  if(count($active_users)>0)
	  {
	    foreach ($active_users as $key => $value) {
	     if(isset($value['id']))
	     {  
	      array_push($active_users_ids,$value['id']);
	     } 
	    }
	  }

	  $brands_arr = BrandsModel::select(['brand_name','id'])
	                            ->where('is_active',1)
	                            ->whereIn('user_id',$active_users_ids)
	                            ->orderBy('brand_name','ASC')
	                            ->get()
	                            ->toArray();
	  
	  if(isset($brands_arr) && count($brands_arr) > 0)
	  {
	    foreach ($brands_arr as $key => $value) {
	      array_push($brand_names, $value['brand_name']);
	    }
	  }
	  //dd($brand_names);
	  return  $brand_names;
	}
	public function get_all_vendor_brands($user_id=false)
	{
	  $brand_names= $brands_arr = [];
	  if(isset($user_id) && !empty($user_id) && $user_id != false)
	  {
	    $obj_brands = BrandsModel::select(['brand_name'])->where('user_id',$user_id)->get();
	    if(isset($obj_brands))
	    {
	      $brands_arr = $obj_brands->toArray();
	      if(sizeof($brands_arr)>0)
	      {
	        foreach ($brands_arr as $key => $value){
	          array_push($brand_names,$value['brand_name']);
	        }
	      }
	    }
	  }
	   return  $brand_names;
	}
	public function get_maker_brands($maker_id=false)
	{
	  $arr_brands = [];
	  if(isset($maker_id) && !empty($maker_id) && $maker_id != false)
	  {
	    $obj_brands = BrandsModel::where('user_id',$maker_id)
	                            ->where('is_active','1')
	                            ->get();
	                            
	    if(isset($obj_brands))
	    {
	    	$arr_brands = $obj_brands->toArray();
	    }

	  }
	  return $arr_brands;
	}

	function get_retailer_order_summary_data($promoCode=false,$orderId)
	{
	    $final_total = [];

	    $quoteData = RetailerQuotesModel::with(['quotes_details'])->where('id',$orderId)->first();

	    if($quoteData)
	    {

	      $quoteData = $quoteData->toArray();

	      $orderShippingCharge = 0;

	      $total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout = 0;

	      foreach ($quoteData['quotes_details'] as $order) 
	      {
	        $orderShippingCharge += $order['shipping_charge'] - $order['shipping_discount'];

	        $subTotal = $order['qty'] * $order['unit_wholsale_price'];
	        $total_amount += $subTotal + $order['shipping_charge'] - $order['shipping_discount'] - $order['product_discount'];
	        $ordSubTotal += $subTotal;
	        $ordProductDiscout += $order['product_discount'];
	        $ordShipCharge += $order['shipping_charge'];
	        $ordShipDiscout += $order['shipping_discount'];
	      }

	        $final_total['sub_total']         = $ordSubTotal;
	        $final_total['product_discount']  = $ordProductDiscout;
	        $final_total['ship_discount']     = $ordShipDiscout;
	        $final_total['ship_charges']      = $ordShipCharge;
	        $final_total['sub_grand_total']   = $total_amount;
	        

	    }


	    /******************* get promo code data  ***********************/

	    $promo_code_details = PromoCodeModel::where('promo_code_name',$promoCode)->first();

	    if($promo_code_details)
	    {
	      $promo_code_id = $promo_code_details->id;

	      $promotion_details = PromotionsModel::where('promo_code',$promo_code_id)->first();

	      if($promotion_details)
	      {
	       $promotion_id =  $promotion_details->id;
	      }
	      //get all promotion type of that promotion

	      $promotion_offers_arr = PromotionsOffersModel::with(['get_prmotion_type'])
	                                        ->where('promotion_id',$promotion_id)
	                                        ->get()
	                                        ->toArray();
	      
	      if(isset($promotion_offers_arr) && count($promotion_offers_arr)>0)
	      {
	         foreach($promotion_offers_arr as $key => $offers_types) 
	         {
	           if($total_amount >=$offers_types['minimum_ammount'])
	           {
	               $promotion_type = '';
	               $promotion_type = $offers_types['get_prmotion_type']['promotion_type_name'];
	          
	               
	                if($promotion_type == '% Off')
	                {
	                   
	                    /* for sequense purposr this section use */
	                    $discount = $offers_types['discount'];
	                    $final_total['discount_per'] = $discount;

	                    if(isset($final_total['grand_total']))
	                    {
	                       $get_discount_amt = ($discount*$total_amount/100);  
	                       $get_discount_amt = num_format($get_discount_amt);
	                       $final_total['grand_total'] = $final_total['grand_total']-$get_discount_amt;
	                    }
	                    else
	                    {
	                       $get_discount_amt = ($discount*$total_amount/100);   
	                       $get_discount_amt = num_format($get_discount_amt);                   
	                       $final_total['grand_total'] = $total_amount-$get_discount_amt;
	                    }                    

	                    $final_total['discount_amt'] = $get_discount_amt;
	                   
	                }

	                if($promotion_type == 'Free Shipping')
	                {
	                    
	                    $final_total['promotion_shipping_charges'] = $final_total['ship_charges'] - $final_total['ship_discount'];

	                    /* for sequense purposr this section use */

	                    if(isset($final_total['grand_total']))
	                    {
	                      $final_total['grand_total'] = $final_total['grand_total'] - $final_total['promotion_shipping_charges'];  
	                    }
	                    else
	                    {
	                      $final_total['grand_total'] = $total_amount - $final_total['promotion_shipping_charges'];  
	                    }
	                }
	            }            
	         }          
	      }
	    }
	    else
	    {
	      /* if promo code not used */

	      $final_total['grand_total'] = $total_amount;

	    }
	   
	    return $final_total;         
	}  

	function get_customer_order_summary_data($promoCode=false,$orderId)
	{
	    $final_total = [];

	    $quoteData = CustomerQuotesModel::with(['quotes_details'])->where('id',$orderId)->first();

	    if($quoteData)
	    {

	      $quoteData = $quoteData->toArray();
	      // dd($quoteData);
	      
	      $orderShippingCharge = 0;

	      $total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout = 0;
	      // dd($quoteData);
	      foreach ($quoteData['quotes_details'] as $order) 
	      {
	        $orderShippingCharge += $order['shipping_charge'] - $order['shipping_discount'];

	        $subTotal = $order['qty'] * $order['unit_retail_price'];
	        $total_amount += $subTotal + $order['shipping_charge'] - $order['shipping_discount'] - $order['product_discount'];
	        $ordSubTotal += $subTotal;
	        $ordProductDiscout += $order['product_discount'];
	        $ordShipCharge += $order['shipping_charge'];
	        $ordShipDiscout += $order['shipping_discount'];
	      }

	        $final_total['sub_total']         = $ordSubTotal;
	        $final_total['product_discount']  = $ordProductDiscout;
	        $final_total['ship_discount']     = $ordShipDiscout;
	        $final_total['ship_charges']      = $ordShipCharge;
	        $final_total['sub_grand_total']   = $total_amount;
	        

	    }

	    /******************* get promo code data  ***********************/

	    if(isset($quoteData['promo_code']) && $quoteData['promo_code'] != '')
	    {
	      $final_total['discount_amt'] = isset($quoteData['promotion_discount'])?$quoteData['promotion_discount']:0;
	      $final_total['grand_total'] = $total_amount - $final_total['discount_amt'];
	    }
	    else
	    {
	      $final_total['grand_total'] = $total_amount;
	    }

	   
	    return $final_total;         
	}

	function get_reps_sales_order_summary_data($promoCode=false,$orderId)
	{

	    $final_total = [];

	    $quoteData = RepresentativeLeadsModel::with(['leads_details'])->where('id',$orderId)->first();
	    if($quoteData)
	    {

	      $quoteData = $quoteData->toArray();

	      $orderShippingCharge = 0;

	      $total_amount = $ordSubTotal = $ordProductDiscout = $ordShipCharge = $ordShipDiscout = 0;

	      foreach ($quoteData['leads_details'] as $order) 
	      {
	    
	        $orderShippingCharge += $order['product_shipping_charge'] - $order['shipping_charges_discount'];

	        $subTotal = $order['qty'] * $order['unit_wholsale_price'];
	        $total_amount += $subTotal + $order['product_shipping_charge'] - $order['shipping_charges_discount'] - $order['product_discount'];
	        $ordSubTotal += $subTotal;
	        $ordProductDiscout += $order['product_discount'];
	        $ordShipCharge += $order['product_shipping_charge'];
	        $ordShipDiscout += $order['shipping_charges_discount'];
	      }

	        $final_total['sub_total']         = $ordSubTotal;
	        $final_total['product_discount']  = $ordProductDiscout;
	        $final_total['ship_discount']     = $ordShipDiscout;
	        $final_total['ship_charges']      = $ordShipCharge;
	        $final_total['sub_grand_total']   = $total_amount;

	    }

	    /******************* get promo code data  ***********************/

	    $promo_code_details = PromoCodeModel::where('promo_code_name',$promoCode)->first();

	    if($promo_code_details)
	    {
	      $promo_code_id = $promo_code_details->id;

	      $promotion_details = PromotionsModel::where('promo_code',$promo_code_id)->first();

	      if($promotion_details)
	      {
	       $promotion_id =  $promotion_details->id;
	      }
	      //get all promotion type of that promotion

	      $promotion_offers_arr = PromotionsOffersModel::with(['get_prmotion_type'])
	                                        ->where('promotion_id',$promotion_id)
	                                        ->get()
	                                        ->toArray();
	      
	      if(isset($promotion_offers_arr) && count($promotion_offers_arr)>0)
	      {
	         foreach($promotion_offers_arr as $key => $offers_types) 
	         {
	           if($total_amount >=$offers_types['minimum_ammount'])
	           {
	               $promotion_type = '';
	               $promotion_type = $offers_types['get_prmotion_type']['promotion_type_name'];
	          
	               
	                if($promotion_type == '% Off')
	                {
	                   
	                    /* for sequense purposr this section use */
	                    $discount = $offers_types['discount'];
	                    $final_total['discount_per'] = $discount;

	                    if(isset($final_total['grand_total']))
	                    {
	                       $get_discount_amt = ($discount*$total_amount/100); 

	                       $get_discount_amt = isset($get_discount_amt)?num_format($get_discount_amt):'';

	                       $final_total['grand_total'] = $final_total['grand_total']-$get_discount_amt;
	                    }
	                    else
	                    {
	                       $get_discount_amt = ($discount*$total_amount/100);  

	                       $get_discount_amt = isset($get_discount_amt)?num_format($get_discount_amt):'';
	                                           
	                       $final_total['grand_total'] = $total_amount-$get_discount_amt;
	                    }                    

	                    $final_total['discount_amt'] = $get_discount_amt;
	                   
	                }

	                if($promotion_type == 'Free Shipping')
	                {
	                    
	                    $final_total['promotion_shipping_charges'] = $final_total['ship_charges'] - $final_total['ship_discount'];

	                    /* for sequense purposr this section use */

	                    if(isset($final_total['grand_total']))
	                    {
	                      $final_total['grand_total'] = $final_total['grand_total'] - $final_total['promotion_shipping_charges'];  
	                    }
	                    else
	                    {
	                      $final_total['grand_total'] = $total_amount - $final_total['promotion_shipping_charges'];  
	                    }
	                }
	            }            
	         }          
	      }
	    }
	    else
	    {
	      /* if promo code not used */

	      $final_total['grand_total'] = $total_amount;

	    }
	   
	    return $final_total;         
	}
	public function get_subcategories($category_id = null)
	{
	    $arr_sub_category = [];
	    if(isset($category_id) && $category_id!="")
	    {
	        $obj_sub_category = SubCategoryModel::where('category_id',$category_id)
	                            ->where('is_active',1)
	                            /*->select('id','subcategory_name')*/
	                            ->get();
	    }
	    else
	    {
	        $obj_sub_category = SubCategoryModel::where('is_active',1)
	                            /*->select('id','subcategory_name')*/
	                            ->get();
	    }

	    if($obj_sub_category)
	    {
	      $arr_sub_category = $obj_sub_category->toArray();
	    }

	    return $arr_sub_category;
	}

	public function get_maker_representative($maker_id,$table_data=null)
	{
	  $arr_data = [];
	  $obj_data = RepresentativeMakersModel::where('maker_id',$maker_id)
	                ->with(['representative_details'=>function($query)
	                {
	                    $query->select('id','first_name','last_name');
	                }])
	                ->get();

	  if($table_data!="true")
	  {
	    if($obj_data)
	    {
	      $arr_data = $obj_data->toArray();
	    }
	    
	    return $arr_data;
	  }
	  else
	  {
	    return $obj_data;
	  }
	}
	public function get_maker_details($maker_id = false)
	{	
	  $maker_data = [];
	  if($maker_id != false)
	  {
	 
	    $maker_company_name = MakerModel::where('user_id',$maker_id)
	                              ->select('company_name')
	                              ->get()->toArray();
	    $maker_data['company_name'] = $maker_company_name;

	    $maker_brands = BrandsModel::where('user_id',$maker_id)
	                             ->select(['brand_name','id'])
	                             ->get()->toArray();

	    $maker_data['maker_brands'] = $maker_brands;
	  }

	 return $maker_data;
	}

// 	use App\Models\RetailerQuotesModel;
// use App\Models\CustomerQuotesModel;
// use App\Models\RepresentativeLeadsModel;

	public function get_order_company_name($order_no,$order_id,$order_type)
	{
		//dump($order_no,$order_id,$role);
		$maker_id = 0;
		if($order_type=="Retailer-Order")
		{
			$order_data_obj = RetailerQuotesModel::where('order_no',$order_no)
											  ->where('id',$order_id)
											  ->first();
			if(isset($order_data_obj))
			{
				$order_data_arr =  $order_data_obj->toArray();
				
				$maker_id = isset($order_data_arr['maker_id'])?$order_data_arr['maker_id']:0;
			} else {

				/* Get maker_id from representative_leads */
				$order_data_obj = RepresentativeLeadsModel::where('order_no',$order_no)
											  ->where('id',$order_id)
											  ->first();
				if(isset($order_data_obj))
				{
					$order_data_arr =  $order_data_obj->toArray();
					
					$maker_id = isset($order_data_arr['maker_id'])?$order_data_arr['maker_id']:0;
				}
			}
		}

		if($order_type=="Rep-Sales-Order")
		{
			$order_data_obj = RepresentativeLeadsModel::where('order_no',$order_no)
											  ->where('id',$order_id)
											  ->first();
			
			if(isset($order_data_obj))
			{
				$order_data_arr =  $order_data_obj->toArray();
				
				$maker_id = isset($order_data_arr['maker_id'])?$order_data_arr['maker_id']:0;
			}
		}

		if($order_type=="Customer-Order")
		{
			$order_data_obj = CustomerQuotesModel::where('order_no',$order_no)
											  ->where('id',$order_id)
											  ->first();
			
			if(isset($order_data_obj))
			{
				$order_data_arr =  $order_data_obj->toArray();
				
				$maker_id = isset($order_data_arr['maker_id'])?$order_data_arr['maker_id']:0;
			}
		}

		$maker_data = MakerModel::where('user_id',$maker_id)
									->first();
		
		$company_name = isset($maker_data->company_name)?$maker_data->company_name:'-';						

		return $company_name;
	}
	
}
?>