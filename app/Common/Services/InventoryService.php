<?php
namespace App\Common\Services;
use App\Models\ProductInventoryModel;
use App\Models\ProductSizesModel;
use App\Models\TempBagModel;
use DB;
class InventoryService
{
	public function __construct(ProductInventoryModel $ProductInventoryModel
								,TempBagModel $TempBagModel,ProductSizesModel $ProductSizesModel)
	{
		$this->ProductInventoryModel = $ProductInventoryModel;
		$this->TempBagModel 		 = $TempBagModel; 
		$this->ProductSizesModel     = $ProductSizesModel;
      
	}

	public function update_count($user_ip_addr)
	{
		$ip_address = \Request::ip();
		$session_id = session()->getId();
		
		$product_data = $this->TempBagModel->where('ip_address',$user_ip_addr)->where('user_session_id',$session_id)->first();
		
		if($product_data)
		{
			$product_data =$product_data->toArray();
			
			$product_details = $product_data['product_data'];
		
		$product_details = json_decode($product_details, true);
		foreach ($product_details['sku'] as $key => $value) {
		
		ProductInventoryModel::where('sku_no',$key)->decrement('quantity',$value['item_qty']);

		}
	}
		
	}


	/*
		sku_no => quantity 
	*/
	public function batch_sku_update_quantity($arr_sku_no)
	{
		if(count($arr_sku_no) <= 0 ) return false;

		DB::beginTransaction();

		try
		{
			foreach ($arr_sku_no as $sku_no => $quantity) 
			{
				$objProductqty = ProductInventoryModel::where('sku_no',$sku_no);

				if($objProductqty)
				{
					$productQty = $objProductqty->pluck('quantity')
					                            ->first();

					if($productQty > 0)
					{
						$quantity = $productQty - $quantity;

						if($quantity < 0)
						{
							$quantity = 0;
						}

						$objProductqty = $objProductqty->update(['quantity'=>$quantity]);
					}
				}				
			}	

			DB::commit();
			return true;
		}
		catch(\Exception $e)
		{
        	DB::rollback();
        	return false;
		}	

	}

	/*
		sku_no => quantity 
	*/
	public function batch_sku_update_quantity_size($arr_sku_no)
	{
		// dd($arr_sku_no);
		if(count($arr_sku_no) <= 0 ) return false;

		DB::beginTransaction();

		try
		{
			foreach ($arr_sku_no as $key => $sku_no) 
			{
				
				// dd($sku_no[]);
				$objProductqty = ProductSizesModel::where('product_id',$sku_no['product_id'])->where('sku_no',$sku_no['sku_no'])->where('size_id',$sku_no['size_id']);
                            
				
						if($objProductqty)
						{
							// dd($objProductqty);
							$productQty = $objProductqty->pluck('size_inventory')
					                            ->first();
					           // dd($productQty);                 
					           if($productQty > 0)
								{                 
									$quantity = $productQty - $sku_no['qty'];

									if($quantity < 0)
									{
										$quantity = 0;
									}
								$objProductqty = $objProductqty->update(['size_inventory'=>$quantity]);
								}
								
						}
					
								
			}	

			DB::commit();
			return true;
		}
		catch(\Exception $e)
		{
        	DB::rollback();
        	return false;
		}	

	}

	public function sku_update_quantity($sku_no,$quantity)
	{
		if( isset($sku_no) && isset($quantity))
		{
			return ProductInventoryModel::where('sku_no',$sku_no)->decrement('quantity',$quantity);
		}
	}

	public function check_count($user_id)
	{
		$instock_products = ProductInventoryModel::where('user_id',$user_id)
													->where('quantity','<=','25')
													->where('quantity','<>','0')
													->where('is_deleted','=',0)		
													->get();

		$outstock_products = ProductInventoryModel::where('user_id',$user_id)
													->where('quantity','=',0)
													->where('is_deleted','=',0)
													->get();
							

		if($instock_products && $instock_products->count()!=0)
		{
			$instock_products = $instock_products->toArray();
			$product_notification_arr =[];
			$product_details = [];

			foreach($instock_products as $key => $value) 
			{
				$product_details['product_id'] = $value['product_id'];
				$product_details['sku_no'] = $value['sku_no'];
				$product_details['quantity'] = $value['quantity'];
				$product_details['user_id'] = $value['user_id'];

				
				array_push($product_notification_arr,$product_details);
			}
			
		}
		else
		{
			$product_notification_arr =0;
		}

		if($outstock_products && $outstock_products->count()!=0)
		{
			$outstock_products = $outstock_products->toArray();
			$product_out_notification_arr =[];
			$product_out_details = [];

			foreach($outstock_products as $key => $value) 
			{
				$product_out_details['product_id'] = $value['product_id'];
				$product_out_details['sku_no'] = $value['sku_no'];
				$product_out_details['quantity'] = $value['quantity'];
				$product_out_details['user_id'] = $value['user_id'];
				
				array_push($product_out_notification_arr,$product_out_details);
			}
			
			
		}
		else
		{
			$product_out_notification_arr =0;
		}

		$pro_notification_arr = [$product_notification_arr,$product_out_notification_arr];

		return $pro_notification_arr;


	}

	public function get_available_qty($arr_sku = []){

		$arr_sku = is_array($arr_sku) ? $arr_sku : [$arr_sku];
		$arr_product = ProductInventoryModel::whereIn('sku_no',$arr_sku)->get(['sku_no','quantity'])->toArray();

		return $arr_product;
  			
	}

	public function get_available_qty_size($arr_sku = [],$arr_product_id = [],$arr_size = [])
	{

		$arr_product = [];
		$arr_sku = is_array($arr_sku) ? $arr_sku : [$arr_sku];

		$arr_product_id = is_array($arr_product_id) ? $arr_product_id : [$arr_product_id];

		$arr_size = is_array($arr_size) ? $arr_size : [$arr_size];
		
		foreach($arr_sku as $sku => $value)
		{
			
			$arr_product[] = ProductSizesModel::where('sku_no',$value)->where('product_id',$arr_product_id[$sku])->where('size_id',$arr_size[$sku])->get(['sku_no','size_id','size_inventory'])->toArray();
		}

		return $arr_product;
  			
	}

}