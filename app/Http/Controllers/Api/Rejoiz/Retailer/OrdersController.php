<?php

namespace App\Http\Controllers\Api\Rejoiz\Retailer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Services\Api\Rejoiz\Retailer\OrdersService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;
use App\Common\Services\Api\Representative\OrdersService as RepresentativeOrdersService;

use App\Events\ActivityLogEvent;
use App\Events\NotificationEvent;

use Validator;

class OrdersController extends Controller
{
    public function __construct(
                                    OrdersService $OrdersService,
                                    ResponseService $ResponseService,
                                    RepresentativeOrdersService $RepresentativeOrdersService
                               )
    {
        $this->OrdersService      = $OrdersService;
        $this->ResponseService    = $ResponseService;
        $this->RepresentativeOrdersService    = $RepresentativeOrdersService;

    }

    public function index(Request $request) 
    {
        $user       = $request->input('auth_user');
        $user_id    = $user->id;
        $form_data  = $request->all();

        $response   =  $this->OrdersService->my_orders($user_id, $form_data);

        return $this->ResponseService->send($response); 

    }

    public function rep_sales_orders(Request $request) {

        $user       = $request->input('auth_user');
        $user_id    = $user->id;
        $form_data  = $request->all();

        $response   =  $this->OrdersService->rep_sales_orders($user_id, $form_data);

        return $this->ResponseService->send($response); 

    }

    public function rep_sales_product_listing(Request $request) {

        $order_no  = $request->input('order_no');
        $order_id  = $request->input('order_id');
        $per_page  = $request->input('per_page');
        $page      = $request->input('page');
        
        $response  = $this->OrdersService->rep_sales_product_listing($order_no,$order_id,$page,$per_page);

        return $this->ResponseService->send($response); 
    }


    public function details(Request $request) {

        $user       = $request->input('auth_user');
        $user_id    = $user->id;
        $form_data  = $request->all();

        $order_id  = $request->input('order_id');
        $order_no  = $request->input('order_no');

        $response  = $this->OrdersService->details($user_id,$order_no ,$order_id);

        return $this->ResponseService->send($response); 
    }

    public function rep_sale_order_details(Request $request) {

        $order_no  = $request->input('order_id');
        $order_id  = $request->input('order_no');

        $response  = $this->OrdersService->rep_sale_order_details($order_no ,$order_id);

        return $this->ResponseService->send($response); 
    }
    
    public function rep_sale_cancel_order(Request $request) 
    {
        $user       = $request->input('auth_user');
        $order_id  = $request->input('order_id');
        
        $response  = $this->OrdersService->rep_sales_order_cancel($order_id,$user);
        
        return $this->ResponseService->send($response); 
    }

    public function retailer_cancel_order(Request $request) 
    {
        $user       = $request->input('auth_user');
        $order_id  = $request->input('order_id');
        
        $response  = $this->OrdersService->retailer_order_cancel($order_id,$user);
        
        return $this->ResponseService->send($response); 
    }



    public function reorder(Request $request) {

       $user       = $request->input('auth_user');   
       $order_id  = $request->input('order_id'); 
       $response  = $this->OrdersService->reorder($order_id,$user);

       return $this->ResponseService->send($response); 
    }

    public function product_listing(Request $request) 
    {

        $order_no  = $request->input('order_no');
        $order_id  = $request->input('order_id');
        $per_page  = $request->input('per_page');
        $page      = $request->input('page');

        $response  = $this->OrdersService->product_listing($order_no,$order_id,$page,$per_page);

        return $this->ResponseService->send($response); 
    }


    public function rep_sales_reorder(request $request) 
    {
       $user       = $request->input('auth_user');       
       
       $order_id  = $request->input('order_id'); 

       $response  = $this->OrdersService->rep_sales_reorder($order_id,$user);

       return $this->ResponseService->send($response); 
    }


    public function save_address(Request $request)
    {
        $arr_rules = [
                        'ship_first_name'    =>'required',
                        'ship_last_name'     =>'required',
                        'ship_email'         =>'required|email',  
                        'ship_country'       =>'required',
                        'ship_state'         =>'required',
                        'ship_zip_code'      =>'required',
                        'ship_mobile_no'     =>'required',
                        'ship_street_address'=>'required', 
                        'bill_first_name'    =>'required',
                        'bill_last_name'     =>'required',
                        'bill_email'         =>'required|email',  
                        'bill_country'       =>'required',
                        'bill_state'         =>'required',
                        'bill_zip_code'      =>'required',
                        'bill_mobile_no'     =>'required',
                        'bill_street_address'=>'required'
                    ];
           
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails()) {

            $response['status']     = 'failure';
            $response['message']    = 'Please enter valid data.';
            $response['data']       = '';

            return $this->ResponseService->send($response , 500);
        }
         
       $form_data = $request->all(); 
       $response  = $this->RepresentativeOrdersService->save_address($form_data);

       return $this->ResponseService->send($response);
    }

    public function product_details(Request $request)
    {
        $form_data = $request->all();
        $response  = $this->OrdersService->product_details($form_data);

        return $this->ResponseService->send($response);
    }
    public function apply_promocode(Request $request)
    {
        $form_data = $request->all();

        $response  = $this->OrdersService->apply_promo_code($form_data);
        
        return $this->ResponseService->send($response);
    }

    public function clear_promocode(Request $request)
    {
        $form_data = $request->all();

        $response  = $this->OrdersService->clear_promo_code($form_data);
        
        return $this->ResponseService->send($response);
    } 

    public function get_order_calculation_data(Request $request)
    {
        $form_data = $request->all();

        $response  = $this->OrdersService->get_order_calculation_data($form_data);
        
        return $this->ResponseService->send($response);
    }

    public function add_to_bag(Request $request)
    {
        $form_data = $request->all();
        $response  = $this->OrdersService->add_to_bag($form_data);

        return $this->ResponseService->send($response);
    }

    public function remove_from_bag(Request $request)
    {
        $form_data = $request->all();
        $order_no  = $form_data['order_no']; 
        $sku_no    = $form_data['sku_no']; 
        $response  = $this->OrdersService->remove_from_bag($order_no,$sku_no);

        return $this->ResponseService->send($response);
    }

    public function summary(Request $request)
    {
        $form_data = $request->all();
        $order_no  = $form_data['order_no']; 
       // $per_page  = $form_data['per_page'];
       /* $page    = $form_data['page'];*/
        $response  = $this->OrdersService->summary($order_no);

        return $this->ResponseService->send($response);
    }

    public function save(Request $request)
    {
        $form_data = $request->all();
        $user      = $request->input('auth_user');
        $user_id   = $user->id;
        $order_no  = $form_data['order_no']; 
        $type      = $form_data['type'];

        $response  = $this->OrdersService->save($order_no,$type,$user);


        if($response['status']=='success')
        {   
            $arr_event                  = [];                 
            $arr_event['ACTION']        = 'EDIT';
            $arr_event['MODULE_ID']     = $order_no;

            $arr_event['USER_ID']       = $user_id;
            $arr_event['MODULE_TITLE']  = 'My Orders';    


            $this->save_activity($arr_event);

        }

        return $this->ResponseService->send($response);
    } 

    public function find_products(Request $request)
    {
        $form_data = $request->all();
        $user      = $request->input('auth_user');
        $user_id   = $user->id;
        $order_no  = $form_data['order_no'];
        $response  = $this->OrdersService->find_products($user_id,$order_no);

        return $this->ResponseService->send($response);   
    }

    public function update_product_qty(Request $request)
    {
       $form_data  = $request->all();
       $response   = $this->OrdersService->update_product_qty($form_data);

       return $this->ResponseService->send($response);     
    }

    public function retailer_details(Request $request)
    {
       $form_data  = $request->all();
       $order_no   = $form_data['order_no'];
       $response   = $this->RepresentativeOrdersService->retailer_details($order_no);
       
       return $this->ResponseService->send($response);     
    }

    public function sku_details(Request $request)
    {
       $form_data  = $request->all();
       $product_id = $form_data['product_id'];
       $sku_no     = $form_data['sku_no'];
       $response   = $this->OrdersService->sku_details($product_id,$sku_no);

       return $this->ResponseService->send($response);     
    }


}
