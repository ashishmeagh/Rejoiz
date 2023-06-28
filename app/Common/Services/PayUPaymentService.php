<?php 
namespace App\Common\Services;

use App\Models\OrderModel;

use Session;
use Sentinel;
use DB;

class PayUPaymentService
{	
	public function __construct(OrderModel $order_model)
	{
		//$this->SchoolAccountBalanceService = $SchoolAccountBalanceService;
		$this->MERCHANT_KEY   = 'UrgxozHX';
		$this->SALT           = '8ao28qdcl9';
		$this->PAYU_BASE_URL  = 'https://sandboxsecure.payu.in'; /* "https://secure.payu.in"; For Production Mode */
		$this->OrderModel     = $order_model;
	}

	public function process_payment($data_arr = false)
	{  

		$MERCHANT_KEY  = $this->MERCHANT_KEY;
		$SALT          = $this->SALT;
		$PAYU_BASE_URL = $this->PAYU_BASE_URL;		// For Sandbox Mode

		$action = '';
		$hash   = '';
		$txnid  = $data_arr['order_id'];

		// Hash Sequence
		$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
		
		//$posted['productinfo'] = json_encode(json_decode('[{"name":"tutionfee","description":"","value":"500","isRequired":"false"},{"name":"developmentfee","description":"monthly tution fee","value":"1500","isRequired":"false"}]'));

		$tx_data = [];
		
		$tx_data['key'] 		= $MERCHANT_KEY;
	    $tx_data['txnid']   	= $txnid;
	    $tx_data['amount'] 	   	= $data_arr['total_amount'];
	    $tx_data['productinfo'] = $data_arr['product_desc'];
	    $tx_data['firstname'] 	= $data_arr['name'];
	    $tx_data['email'] 	    = $data_arr['email'];
	    $tx_data['phone'] 	    = $data_arr['phone_no'];
	    $tx_data['surl'] 	    = $data_arr['surl'];
	    $tx_data['furl'] 	    = $data_arr['furl'];
	
	    $hashVarsSeq = explode('|', $hashSequence);
	    $hash_string = '';  
	    foreach($hashVarsSeq as $hash_var) 
	    {	
	        $hash_string .= isset($tx_data[$hash_var]) ? $tx_data[$hash_var] : '';
	        $hash_string .= '|';
	    }

	    $hash_string .= $SALT;
	  	
	    $hash   = strtolower(hash('sha512', $hash_string));
	    $action = $PAYU_BASE_URL . '/_payment';

	    echo view('vendor.payuform',[
			'action'      => $action,
			'key'         => $MERCHANT_KEY,
			'hash'        => $hash,
			'txnid'       => $txnid,
			'amount'      => $tx_data['amount'],
			'firstname'   => $tx_data['firstname'],
			'email'       => $tx_data['email'],
			'phone'       => $tx_data['phone'],
			'productinfo' => $tx_data['productinfo'],
			'surl'        => $tx_data['surl'],
			'furl'        => $tx_data['furl'],

		])->render();
		exit();
	}

	public function process_transaction_response($arr_data = [])
	{
        
		//compairing hash key
      /*  $SALT     = $this->SALT;
	    $resphash = $arr_data['hash'];
	    $status   = $arr_data['status'];

	    // Hash Sequence
		$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

	    $hashVarsSeq = explode('|', $hashSequence);
	    $hash_string = '';  
	    foreach($hashVarsSeq as $hash_var) 
	    {	
	        $hash_string .= isset($tx_data[$hash_var]) ? $tx_data[$hash_var] : '';
	        $hash_string .= '|';
	    }

	    $hash_string .= $SALT;
	  	
	    $CalcHashString = strtolower(hash('sha512', $hash_string));
   
        //compairing hash key existing and new response

		if ($status == 'success'  && $resphash == $CalcHashString) 
		{
			//$msg = "Transaction Successful and Hash Verified...";
			return  [
				     'status' => 'success',
				     'description' => 'Transaction Successful and Hash Verified...'
			        ];
			//Do success order processing here...
		}
		else 
		{
			
			return [
				      'status' => 'failure',
				      'description' => 'Payment failed for Hasn not verified...'
			       ];
		} */

		DB::beginTransaction();
		try
		{

			if(isset($arr_data['txnid']) == false)
			{   
				return [
						 'status'      => 'failure',
						 'description' => 'TXID Missing , Invalid Transaction Response'
					   ];
			}

			if(isset($arr_data['status']) == false)
			{ 
				return  [
						  'status'      => 'failure',
						  'description' => 'Payment Gateway Status Missing , Invalid Transaction Response'
					    ];
			}

			/* Get Transaction from DB */
			$obj_db_tx = $this->OrderModel->where('order_id',$arr_data['txnid'])->first();


			if($obj_db_tx == false)
			{
				return [
						 'status'      => 'failure',
						 'description' => 'Unable to Get Transaction  , Invalid Transaction Response'
					   ];
			}	

			if($obj_db_tx->transaction_status != 'PENDING')
			{ 
				return  [
						  'status'      => 'success',
						  'description' => 'Already Transaction is processed.'
					    ];
			}

			if($arr_data['status'] == 'success')
			{   
				$obj_db_tx->transaction_status = 'COMPLETED';
			}
			else
			{   
				$obj_db_tx->transaction_status = 'FAILED';
			}
			
			$obj_db_tx->transaction_data         = json_encode($arr_data);
			$obj_db_tx->transaction_date_time    = $arr_data['addedon'];
			$obj_db_tx->transaction_id           = $arr_data['payuMoneyId'];
			$obj_db_tx->order_status             = 'PENDING';
			$obj_db_tx->payment_mode             = 'online';

			$transaction_data['transaction_data']         = $obj_db_tx->transaction_data;
			$transaction_data['transaction_id']           = $obj_db_tx->transaction_id;
			$transaction_data['order_status']             = $obj_db_tx->order_status;
			$transaction_data['transaction_date_time']    = $obj_db_tx->transaction_date_time;
			$transaction_data['transaction_status']       = $obj_db_tx->transaction_status;
			$transaction_data['payment_mode']             = $obj_db_tx->payment_mode;
    
	        $order_result = $obj_db_tx->where('order_id',$arr_data['txnid'])->update($transaction_data);

			//if($obj_db_tx->where('order_id',$arr_data['txnid'])->update($transaction_data))
			if($order_result)
			{ 	
				if($obj_db_tx->transaction_status == 'COMPLETED')
				{  
					DB::commit();
					
					return [
							 'status'      => 'success',
							 'description' => 'Your Payment Done Successfully.'
						   ];
				}
				else
				{
					return [
							 'status'      => 'failure',
							 'description' => 'Your Payment Failed.'
						   ];	
				}
					
			}
			else
			{
				return  [
							'status' => 'failure',
							'description' => 'Something went wrong Please try again!'
						];
			}  
        }
        catch(Exception $e)
        {
           DB::rollback();
           return false;
        }

	}
	
}

?>