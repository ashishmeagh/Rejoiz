<?php

namespace App\Http\Controllers\sales_manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\ProductsModel;
use App\Models\AddressModel;
use App\Models\RepresentativeLeadsModel;
use App\Models\RoleModel;
use App\Models\TempBagModel;
use App\Models\MakerModel;
use App\Models\RoleUsersModel;
use App\Models\ProductDetailsModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerRepresentativeMappingModel;
use App\Models\RepresentativeMakersModel;  
use App\Models\TransactionMappingModel;
use App\Models\RetailerModel;
use App\Models\CountryModel;
use App\Models\SubCategoryModel;  
use App\Models\VendorSalesmanagerMappingModel;  
use App\Common\Services\RepsEmailService;
use App\Models\CategoryModel;
use App\Models\RepresentativeModel;
use App\Models\StripeTransactionModel;
use App\Models\ProductsSubCategoriesModel;
use App\Common\Services\GeneralService;
use App\Events\NotificationEvent;
// use App\Common\Services\UserService;
use App\Common\Services\orderDataService;
use App\Common\Services\CommissionService;
use App\Common\Services\HelperService;
use App\Common\Services\ProductService;

use Sentinel;
use Validator;
use DB;
use Datatables;
use Flash;
use Session;
use DateTime;

class LeadsController extends Controller
{

    /*
      | Author : Sagar B. Jadhav
      | Date   : 04 July 2019
      */
public function __construct(ProductsModel $ProductsModel,
                            UserModel $UserModel,
                            TempBagModel $TempBagModel,
                            MakerModel $MakerModel,
                            RoleModel $RoleModel,
                            ProductDetailsModel $ProductDetailsModel,
                            RoleUsersModel $RoleUsersModel,
                            RepsEmailService $RepsEmailService,
                            AddressModel $AddressModel,
                            RepresentativeLeadsModel $RepresentativeLeadsModel,
                            RepresentativeProductLeadsModel $RepresentativeProductLeadsModel,
                            GeneralService $GeneralService,
                            RetailerRepresentativeMappingModel $RetailerRepresentativeMappingModel,
                            RepresentativeMakersModel $RepresentativeMakersModel,
                            TransactionMappingModel $TransactionMappingModel,
                            orderDataService $orderDataService,
                            RetailerModel $RetailerModel,
                            CategoryModel $CategoryModel,
                            SubCategoryModel $SubCategoryModel,
                            ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                            CountryModel $CountryModel,
                            RepresentativeModel $RepresentativeModel,
                            StripeTransactionModel $StripeTransactionModel,
                            VendorSalesmanagerMappingModel $VendorSalesmanagerMappingModel,
                            HelperService $HelperService,
                            CommissionService $CommissionService,
                            ProductService $ProductService

                           )
  {
    $this->AddressModel                      = $AddressModel;
    $this->UserModel                         = $UserModel;
    $this->TempBagModel                      = $TempBagModel;
    $this->RoleModel                         = $RoleModel;
    $this->MakerModel                        = $MakerModel;
    $this->RoleUsersModel                    = $RoleUsersModel;
    $this->RepsEmailService                  = $RepsEmailService;
    $this->HelperService                     = $HelperService;
    $this->ProductDetailsModel               = $ProductDetailsModel;
    $this->RepresentativeLeadsModel          = $RepresentativeLeadsModel;
    $this->RepresentativeProductLeadsModel   = $RepresentativeProductLeadsModel;
    $this->ProductsModel                     = $ProductsModel;
    $this->RetailerRepresentativeMappingModel= $RetailerRepresentativeMappingModel;
    $this->TransactionMappingModel           = $TransactionMappingModel;
    $this->GeneralService                    = $GeneralService;
    $this->RetailerModel                     = $RetailerModel;
    $this->CountryModel                      = $CountryModel;
    $this->CategoryModel                     = $CategoryModel;
    $this->SubCategoryModel                  = $SubCategoryModel;
    $this->ProductsSubCategoriesModel        = $ProductsSubCategoriesModel;
    $this->orderDataService                  = $orderDataService;
    $this->RepresentativeMakersModel         = $RepresentativeMakersModel;
    $this->RepresentativeModel               = $RepresentativeModel;
    $this->StripeTransactionModel            = $StripeTransactionModel;
    $this->VendorSalesmanagerMappingModel    = $VendorSalesmanagerMappingModel;
    $this->ProductService                    = $ProductService;

    $this->arr_view_data      = [];
    $this->module_title       = "My Orders";
    $this->module_view_folder = 'sales_manager.leads';
    $this->sales_manager_panel_slug   = config('app.project.sales_manager_panel_slug');
    $this->module_url_path    = url($this->sales_manager_panel_slug.'/leads');

    $this->CommissionService       = $CommissionService;
  }

  public function index()
  {
    $this->arr_view_data['module_title']    = $this->module_title;
    $this->arr_view_data['page_title']      = 'My Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['confirmed_flag']  = 0;


    return view($this->module_view_folder.'.index',$this->arr_view_data);
  }

  public function confirmed_orders()
  {
    $this->arr_view_data['module_title']    = 'My Confirmed Orders';
    $this->arr_view_data['page_title']      = 'My Confirmed Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['confirmed_flag']  = 1;

    return view($this->module_view_folder.'.index',$this->arr_view_data);
  }

   public function pending_orders()
  {
    
    $this->arr_view_data['module_title']    = 'My Pending Orders';
    $this->arr_view_data['page_title']      = 'My Pending Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['pending_flag']    = 1;

     return view($this->module_view_folder.'.index',$this->arr_view_data);

  }

  public function completed_orders()
  {
    
    $this->arr_view_data['module_title']    = 'My Completed Orders';
    $this->arr_view_data['page_title']      = 'My Completed Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['completed_flag']  = 1;

    return view($this->module_view_folder.'.index',$this->arr_view_data);

  }

  public function net_30_completed_orders()
  {
    
    $this->arr_view_data['module_title']    = 'My Net30 Completed Orders';
    $this->arr_view_data['page_title']      = 'My Net30 Completed Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['net_30_completed_orders']  = 1;

    return view($this->module_view_folder.'.index',$this->arr_view_data);

  }

  public function net_30_pending_orders()
  {
    
    $this->arr_view_data['module_title']    = 'My Net30 Pending Orders';
    $this->arr_view_data['page_title']      = 'My Net30 Pending Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['net_30_pending_orders']  = 1;

    return view($this->module_view_folder.'.index',$this->arr_view_data);

  }

  public function approved_orders()
  {

    $this->arr_view_data['module_title']    = 'My Approved Orders';
    $this->arr_view_data['page_title']      = 'My Approved Orders';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['approved_flag']   = 1;

    return view($this->module_view_folder.'.index',$this->arr_view_data);
  }


  public function rep_lead_listing()
  {
    $this->arr_view_data['module_title']    = 'Orders by Reps';
    $this->arr_view_data['page_title']      = 'Orders by Reps';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;

    return view($this->module_view_folder.'.rep_leads',$this->arr_view_data);
  }


  public function create($enc_cust_id = null)
  {

      $user = Sentinel::check();
      $loggedIn_userId = 0;
      $data = [];
      $order_no = false;

      if($user)
      {
          $loggedIn_userId = $user->id;
         
      }    

      //Check maker add in list or 
      $is_exists = [];
      
      $is_exists = $this->UserModel->where('id',$loggedIn_userId)
                       ->where('status',1)->first();
                       

      if(is_array($enc_cust_id) == false)
      {
       $order_no = isset($enc_cust_id)?base64_decode($enc_cust_id):'';
      }
     
      $arr_address = [];

      if ($order_no)
      {

        $address = $this->AddressModel->where('order_no',$order_no)
                                       ->first();
        if($address)
        {
          $arr_address = $address->toArray();
        }

      }

       /*get all active retailers*/

       $retailer_arr = $country_arr = [];

   /*    $retailer_arr = $this->RetailerModel->with(['user_details'])
                                           ->whereHas('user_details',function($q)
                                            {
                                                $q->where('status',1);                
                                            })
                                           ->where('store_name','!=','')
                                           ->orderBy('store_name','ASC')
                                          ->get()
                                          ->toArray();*/


        //get only those retailer who has added by sales manager

        $retailer_arr = $this->RetailerRepresentativeMappingModel
                             ->with(['retailer_details','getRetailerDetails'])
                             ->whereHas('retailer_details',function($q){
                                $q->select('store_name');
                                $q->where('store_name','!=','');
                                $q->orderBy('store_name','ASC');
                             })
                             ->whereHas('getRetailerDetails',function($q){
                                $q->where('status',1);
                                $q->where('is_approved',1);
                             })

                             ->where('sales_manager_id',$loggedIn_userId)
                             
                            ->get()
                            ->toArray();


    
      /*get country */

      $country_arr = $this->CountryModel->where('is_active',1)->orderBy('name','ASC')->get()->toArray();
     
      if(count($is_exists)<=0)
      { 
        Flash::error('Commission is not confirmed by admin, please wait for confirmation.');
              return redirect()->back();
      }
         
              
       
      $this->arr_view_data['arr_address']     = $arr_address;
      $this->arr_view_data['customer_data']   = $data;
      $this->arr_view_data['module_title']    = $this->module_title;
      
      $this->arr_view_data['retailer_arr']    = $retailer_arr;
      $this->arr_view_data['country_arr']     = $country_arr;

      $this->arr_view_data['page_title']      = 'Customer Details';
      $this->arr_view_data['module_url_path'] = $this->module_url_path;

    return view($this->module_view_folder.'.create',$this->arr_view_data);
  }

  public function reorder(Request $request)
  {
    $arr_order_details = $arr_address = $arr_order = $product_details = $arr_sku_no = [];

    $order_no = str_pad('J2',  10, rand('1234567890',10)); 

    $form_data = $request->all();
    $order_details = $this->RepresentativeLeadsModel->with('leads_details','address_details')->where('id',$form_data['order_no'])->first();

    if ($order_details)
    {

      $arr_order_details = $order_details->toArray();

      /* get sku nos of order for getting product status*/
      if(isset($arr_order_details) && count($arr_order_details) >0 )
      {
        if(isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0)
        {
          $arr_sku_no = array_column($arr_order_details['leads_details'], 'sku');

          $active_products = $this->orderDataService->get_active_product($arr_sku_no);

          //here check product related all data is active or not then reorder will perform
          $data['order_no']     = $arr_order_details['order_no'];
          $data['maker_id']     = $arr_order_details['maker_id'];
          $data['order_from']   = 'rep';
          $product_availability = $this->ProductService->product_availability($data);

          /*---------------------------------------------------------------------------*/



          $sku_count = isset($arr_sku_no)?count($arr_sku_no):0;

          $arr_active_product_count = isset($active_products)?count($active_products):0;

          $deactive_products_count = $sku_count - $arr_active_product_count;

          if($deactive_products_count > 0 && $deactive_products_count !== $sku_count)
          {
             $response['status'] = 'warning';
             $response['msg']    = 'The order you are trying to place, has '.$deactive_products_count.' product(s) unavailable at the moment, would you still like to proceed ?.';
             return $response;
          }
/*
          if(isset($active_products) && count($active_products) == 0)
          {
            $response['status'] = 'Apologies';
            $response['msg']    = 'None of the product(s) are available at the moment in this order.';
             return $response;
          }*/

          if($product_availability == false)
          {
            $response['status'] = 'Apologies';
            $response['msg']    = 'None of the product(s) are available at the moment in this order.';
             return $response;
          }

        }
      }

    }

    // Store address of order

    $arr_address['order_no']              = $order_no;
    $arr_address['user_id']               = $arr_order_details['address_details']['user_id'];
    $arr_address['bill_first_name']       = $arr_order_details['address_details']['bill_first_name'];
    $arr_address['bill_last_name']        = $arr_order_details['address_details']['bill_last_name'];
    $arr_address['bill_email']            = $arr_order_details['address_details']['bill_email'];
    $arr_address['bill_mobile_no']        = $arr_order_details['address_details']['bill_mobile_no'];
    $arr_address['bill_complete_address'] = $arr_order_details['address_details']['bill_complete_address'];
    $arr_address['bill_city']             = $arr_order_details['address_details']['bill_city'];
    $arr_address['bill_state']            = $arr_order_details['address_details']['bill_state'];
    $arr_address['bill_zip_code']         = $arr_order_details['address_details']['bill_zip_code'];
    $arr_address['ship_first_name']       = $arr_order_details['address_details']['ship_first_name'];
    $arr_address['ship_last_name']        = $arr_order_details['address_details']['ship_last_name'];
    $arr_address['ship_email']            = $arr_order_details['address_details']['ship_email'];
    $arr_address['ship_mobile_no']        = $arr_order_details['address_details']['ship_mobile_no'];
    $arr_address['ship_complete_address'] = $arr_order_details['address_details']['ship_complete_address'];
    $arr_address['ship_city']             = $arr_order_details['address_details']['ship_city'];
    $arr_address['ship_state']            = $arr_order_details['address_details']['ship_state'];
    $arr_address['bill_country']          = $arr_order_details['address_details']['bill_country'];
    $arr_address['ship_country']          = $arr_order_details['address_details']['ship_country'];
    $arr_address['ship_zip_code']         = $arr_order_details['address_details']['ship_zip_code'];
    $arr_address['is_as_below']           = $arr_order_details['address_details']['is_as_below'];
    $arr_address['bill_street_address']   = $arr_order_details['address_details']['bill_street_address'];
    $arr_address['bill_suit_apt']         = $arr_order_details['address_details']['bill_suit_apt'];
    $arr_address['ship_street_address']   = $arr_order_details['address_details']['ship_street_address'];
    $arr_address['ship_suit_apt']         = $arr_order_details['address_details']['ship_suit_apt'];
    
  
    $store_address = $this->AddressModel->create($arr_address);

    $shippingCharges = $totalShippingDiscount = $totalProductDiscount = $totalShippingCharges = $totalWholesalePrice = $totalRetailerPrice = 0;

    foreach ($arr_order_details['leads_details'] as $key => $product)
    {
      $shippingCharg = $shippingDiscnt = $productDiscnt = 0;

      $productData = $this->product_discount($product['product_id'], $product['qty']);

      $productDetails = get_product_details($product['product_id']);

      $shippingCharg  = isset($productData['shipping_charges'])?(float)$productData['shipping_charges']:0;

      $shippingDiscnt = isset($productData['shipping_discount'])?(float)$productData['shipping_discount']:0;
      
      $productDiscnt  = isset($productData['product_discount'])?$productData['product_discount']:0;
      // dd($productDiscnt,$shippingDiscnt,$shippingCharg);
      $shippingCharges       += isset($productData['shipping_charges'])?(float)$productData['shipping_charges']:0;

      $totalShippingDiscount += isset($productData['shipping_discount'])?(float)$productData['shipping_discount']:0;
     
      $totalShippingCharges  += $shippingCharg - $shippingDiscnt;

      $totalProductDiscount  += isset($productData['product_discount'])?$productData['product_discount']:0;
      
      $wholesalePrice         = $product['qty'] * $productDetails['unit_wholsale_price']; 

      $retailerPrice         = $product['qty'] * $productDetails['retail_price']; 

      $totalWholesalePrice   += $wholesalePrice + ($shippingCharg - $shippingDiscnt) - $productDiscnt;

      $totalRetailerPrice   += $retailerPrice + ($shippingCharg - $shippingDiscnt) - $productDiscnt;
    }
    //store main order

    $arr_order['order_no']                          = $order_no;
    $arr_order['admin_commission']                  = $this->CommissionService->get_admin_commission($arr_order_details['maker_id']);
    $arr_order['representative_id']                 = $arr_order_details['representative_id'];
    $arr_order['sales_manager_id']                  = $arr_order_details['sales_manager_id'];
    $arr_order['maker_id']                          = $arr_order_details['maker_id'];
    $arr_order['retailer_id']                       = $arr_order_details['retailer_id'];
    $arr_order['ship_status']                       = 0;
    $arr_order['is_confirm']                        = 0;
    $arr_order['sales_manager_commission_status']   = 0;
    $arr_order['total_retail_price']                = $totalRetailerPrice;
    $arr_order['total_wholesale_price']             = $totalWholesalePrice;
    $arr_order['total_product_discount']            = $totalProductDiscount;
    $arr_order['total_shipping_charges']            = $totalShippingCharges;
    $arr_order['total_shipping_discount']           = $totalShippingDiscount;
    $arr_order['total_product_shipping_charges']    = $shippingCharges;
    $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);

    $store_order = $this->RepresentativeLeadsModel->create($arr_order);

    if (isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0) {
      
      $unsatisfied_product_arr = [];


      foreach ($arr_order_details['leads_details'] as $key => $product) {

        $shippingCharg = $shippingDiscnt = $productDiscnt = 0;

        $productDetails = get_product_details($product['product_id']);

        $productData = $this->product_discount($product['product_id'], $product['qty']);

        $shippingCharg  = isset($productData['shipping_charges'])?(float)$productData['shipping_charges']:0;

        $shippingDiscnt = isset($productData['shipping_discount'])?(float)$productData['shipping_discount']:0;

        $productDiscnt  = isset($productData['product_discount'])?$productData['product_discount']:0;

        $product_details['order_no']                = $order_no;
        $product_details['representative_leads_id'] = $store_order->id;
        $product_details['maker_id']                = $product['maker_id'];
        $product_details['product_id']              = $product['product_id'];
        $product_details['sku']                     = $product['sku'];
        $product_details['retail_price']            = $productDetails['retail_price'];
        $product_details['unit_wholsale_price']     = $productDetails['unit_wholsale_price'];
        $product_details['wholesale_price']         = $productDetails['unit_wholsale_price'] * $product['qty'];
        $product_details['qty']                     = $product['qty'];
        $product_details['description']             = $productDetails['description'];
        $product_details['product_discount']        = $productDiscnt;
        $product_details['shipping_charges']        = $shippingCharg - $shippingDiscnt;
        $product_details['shipping_charges_discount'] = $shippingDiscnt;
        $product_details['product_shipping_charge']   = $shippingCharg;

        
        $store_order_details = $this->RepresentativeProductLeadsModel->create($product_details);
      }
    }


    if ($order_details) {
      
      $response['status']   = 'success';
      $response['msg']      = 'Order has been created.';
      $response['order_no'] = base64_encode($order_no);
      return $response;
    }

    else{

      $response['status'] = 'failure';
      $response['msg']    = 'Something went wrong, please try again.';

      return $response;

    }   
  }

  public function splice_reorder_data(Request $request)
  {
    $arr_order_details = $arr_address = $arr_order = $product_details = $arr_sku_no = [];

    $order_no = str_pad('J2',  10, rand('1234567890',10)); 

    $form_data = $request->all();
    $order_details = $this->RepresentativeLeadsModel->with('leads_details','address_details')->where('id',$form_data['order_no'])->first();

    if ($order_details) {

      $arr_order_details = $order_details->toArray();

      /* get sku nos of order for getting product status*/
      if(isset($arr_order_details) && count($arr_order_details) >0 )
      {
        if(isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0)
        {
          $arr_sku_no = array_column($arr_order_details['leads_details'], 'sku');

          $active_products = $this->orderDataService->get_active_product($arr_sku_no);

          $arr_active_sku_no = array_column($active_products, 'sku');

          $deactive_product = array_diff($arr_sku_no, $arr_active_sku_no);
        
        }
      }

    }

    // dd($deactive_product,$arr_order_details['leads_details']);
    $total_retail_price = $total_wholesale_price = $total_product_discount =$total_shipping_charges = $total_shipping_discount = $total_product_shipping_charges = 0;




    foreach($arr_order_details['leads_details'] as $key => $product)
    {
      if (in_array($product['sku'], $deactive_product))
      {
        unset($arr_order_details['leads_details'][$key]);

        $total_retail_price               += $product['retail_price'];
        $total_wholesale_price            += $product['wholesale_price'];
        $total_product_discount           += $product['product_discount'];
        $total_shipping_charges           += $product['shipping_charges'];
        $total_shipping_discount          += $product['shipping_charges_discount'];
        $total_product_shipping_charges   += $product['product_shipping_charge'];
      }
     
    }
    // dd($arr_order_details['leads_details'],$total_retail_price,$total_wholesale_price,$total_product_discount,$total_shipping_charges,$total_shipping_discount,$total_product_shipping_charges);

    // Store address of order

    $arr_address['order_no']              = $order_no;
    $arr_address['user_id']               = $arr_order_details['address_details']['user_id'];
    $arr_address['bill_first_name']       = $arr_order_details['address_details']['bill_first_name'];
    $arr_address['bill_last_name']        = $arr_order_details['address_details']['bill_last_name'];
    $arr_address['bill_email']            = $arr_order_details['address_details']['bill_email'];
    $arr_address['bill_mobile_no']        = $arr_order_details['address_details']['bill_mobile_no'];
    $arr_address['bill_complete_address'] = $arr_order_details['address_details']['bill_complete_address'];
    $arr_address['bill_city']             = $arr_order_details['address_details']['bill_city'];
    $arr_address['bill_state']            = $arr_order_details['address_details']['bill_state'];
    $arr_address['bill_zip_code']         = $arr_order_details['address_details']['bill_zip_code'];
    $arr_address['ship_first_name']       = $arr_order_details['address_details']['ship_first_name'];
    $arr_address['ship_last_name']        = $arr_order_details['address_details']['ship_last_name'];
    $arr_address['ship_email']            = $arr_order_details['address_details']['ship_email'];
    $arr_address['ship_mobile_no']        = $arr_order_details['address_details']['ship_mobile_no'];
    $arr_address['ship_complete_address'] = $arr_order_details['address_details']['ship_complete_address'];
    $arr_address['ship_city']             = $arr_order_details['address_details']['ship_city'];
    $arr_address['ship_state']            = $arr_order_details['address_details']['ship_state'];
    $arr_address['bill_country']          = $arr_order_details['address_details']['bill_country'];
    $arr_address['ship_country']          = $arr_order_details['address_details']['ship_country'];
    $arr_address['ship_zip_code']         = $arr_order_details['address_details']['ship_zip_code'];
    $arr_address['is_as_below']           = $arr_order_details['address_details']['is_as_below'];
  
    $store_address = $this->AddressModel->create($arr_address);

    //store main order

    $arr_order['order_no']                          = $order_no;
    $arr_order['representative_id']                 = $arr_order_details['representative_id'];
    $arr_order['sales_manager_id']                  = $arr_order_details['sales_manager_id'];
    $arr_order['maker_id']                          = $arr_order_details['maker_id'];
    $arr_order['retailer_id']                       = $arr_order_details['retailer_id'];
    $arr_order['ship_status']                       = 0;
    $arr_order['is_confirm']                        = 0;
    $arr_order['total_retail_price']                = $total_retail_price;
    $arr_order['total_wholesale_price']             = $total_wholesale_price;
    $arr_order['sales_manager_commission_status']   = 0;
    $arr_order['total_product_discount']            = $total_product_discount;
    $arr_order['total_shipping_charges']            = $total_shipping_charges;
    $arr_order['total_shipping_discount']           = $total_shipping_discount;
    $arr_order['total_product_shipping_charges']    = $total_product_shipping_charges;
    $arr_order['admin_commission']                  = $this->CommissionService->get_admin_commission($arr_order_details['maker_id']);
    $arr_order['is_direct_payment']                 = get_maker_payment_term($arr_order_details['maker_id']);

    $store_order = $this->RepresentativeLeadsModel->create($arr_order);

    if (isset($arr_order_details['leads_details']) && count($arr_order_details['leads_details']) > 0) {
      
      foreach ($arr_order_details['leads_details'] as $key => $product) {

        $product_details['order_no']                = $order_no;
        $product_details['representative_leads_id'] = $store_order->id;
        $product_details['maker_id']                = $product['maker_id'];
        $product_details['product_id']              = $product['product_id'];
        $product_details['sku']                     = $product['sku'];
        $product_details['retail_price']            = $product['retail_price'];
        $product_details['unit_wholsale_price']     = $product['unit_wholsale_price'];
        $product_details['wholesale_price']         = $product['wholesale_price'];
        $product_details['qty']                     = $product['qty'];
        $product_details['description']             = $product['description'];
        $product_details['product_discount']        = $product['product_discount'];
        $product_details['shipping_charges']        = $product['shipping_charges'];
        $product_details['shipping_charges_discount'] = $product['shipping_charges_discount'];
        $product_details['product_shipping_charge']  = $product['product_shipping_charge'];

        $store_order_details = $this->RepresentativeProductLeadsModel->create($product_details);
      }
    }


    if ($order_details) {
      
      $response['status']   = 'success';
      $response['msg']      = 'Order has been created.';
      $response['order_no'] = base64_encode($order_no);
      return $response;
    }

    else{

      $response['status'] = 'failure';
      $response['msg']    = 'Something went wrong, please try again.';

      return $response;

    }   
  }

  
  public function search_customer(Request $request)
  {
    $context = $request->input('term');
    $data    = [];

    if(isset($context) && $context!='')
    {
      $role = Sentinel::findRoleBySlug('retailer');
     
      $role_id = 0;

      if($role)
      {
        $role_id = $role->id;
      }

    
      
      $customer_obj = $this->UserModel->where('email', 'LIKE', '%'.$context.'%')                    
                           ->whereHas('role_details',function($q) use($role_id)
                          {
                            $q->where('role_id',$role_id);                
                          })
                   
                          ->where('status',1)
                          ->get(); 

                            

      if(isset($customer_obj) && count($customer_obj))
      {

        $customer_arr = $customer_obj->toArray();
        
        foreach ($customer_arr as $key => $value)
        {
          $data[] = $value['first_name'].' '.$value['last_name'].' ['.$value['email'].']';            
        }
      }

      else
      {
        $data[]  = 'Customer not found';
      }
    }
    else
    {
      $data[] = 'Customer not found';
    }
       
  
    return response()->json($data);
  }



  public function is_customer_exists(Request $request)
  {
    $context = $request->input('term');
    $data    = [];

    if(isset($context) && $context!='')
    {
      $role = Sentinel::findRoleBySlug('retailer');
      
      $role_id = 0;

      if($role)
      {
        $role_id = $role->id;
      }

      
      $customer_obj = $this->UserModel->where('email', 'LIKE', '%'.$context.'%')                    
                           ->whereHas('role_details',function($q) use($role_id)
                          {
                            $q->where('role_id',$role_id);                
                          })
                          ->where('status',1)
                          ->count(); 
                           

      if($customer_obj>0)
      {
        $response['status'] = 'SUCCESS';
      }

      else
      {
        $response['status'] = 'ERROR';
      }
    }
    else
    {
      $response['status'] = 'ERROR';
    }
       
   
    return response()->json($response);
  }



    public function delete_product_from_bucket($enc_order_no,$sku_no)
    {
        $order_no =base64_decode($enc_order_no);

        $response = $this->orderDataService->delete_product_from_bucket($enc_order_no,$sku_no);

        if($response['status'] == 'FAILURE')
        {

          Flash::error($response['description']);
              
        }else
        {
           /*--update total wholesale price as per total product dis ,total shipping charges ,tot shiping dis---*/

              $order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();

              if(isset($order_details))
              {
                  $order_arr = $order_details->toArray();
                
                  $total_wholesale_price = $order_details['total_wholesale_price']+$order_details['total_product_shipping_charges']-$order_details['total_product_discount']-$order_details['total_shipping_discount'];

                  $this->RepresentativeLeadsModel->where('order_no',$order_no)->update(['total_wholesale_price'=>$total_wholesale_price]);
              }
          /*--------------------------------------------------------------------------------*/
          
          Flash::success($response['description']);
        }
        
        return redirect()->back();
    }


     public function delete_product_from_bucket_no($enc_order_no,$sku_no)
    {
        $order_no =base64_decode($enc_order_no);

        $response = $this->orderDataService->delete_product_from_bucket_no($enc_order_no,$sku_no);

        if($response['status'] == 'FAILURE')
        {

          Flash::error($response['description']);
              
        }else
        {
           /*--update total wholesale price as per total product dis ,total shipping charges ,tot shiping dis---*/

              $order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();

              if(isset($order_details))
              {
                  $order_arr = $order_details->toArray();
                
                  $total_wholesale_price = $order_details['total_wholesale_price']+$order_details['total_product_shipping_charges']-$order_details['total_product_discount']-$order_details['total_shipping_discount'];

                  $this->RepresentativeLeadsModel->where('order_no',$order_no)->update(['total_wholesale_price'=>$total_wholesale_price]);
              }
          /*--------------------------------------------------------------------------------*/
          
          Flash::success($response['description']);
        }
        
        return redirect()->back();
    }

  public function get_customer_detail(Request $request)
  {
      $country_arr = [];
      $customer    = $request->input('customer');

      if($customer)
      {
        $response = $this->orderDataService->get_customer_detail($customer);
        
        /*get country */

        $country_arr = $this->CountryModel->where('is_active',1)->orderBy('name','ASC')->get()->toArray();


        $response['country_arr'] = $country_arr;

      }
      else
      {
        $response['status'] = 'FAILURE';
      }

     return response()->json($response); 
  }


  public function save_customer_address(Request $request)
  {
    $is_update      = false;
    $arr_rules      = [];        
    $loggedInUserId = 0;
    $is_addr_exist  = 0;
    $form_data      = $request->all();    
    $user           = Sentinel::check();
    if($user)
    {
       $loggedInUserId = $user->id;
    } 


    $arr_rules = [
                    'bill_first_name'         => 'required',
                    'bill_mobile_no'          => 'required',
                    //'bill_complete_addr' => 'required',
                    'billing_street_address'  => 'required',
                    'bill_state'              => 'required',
                    'bill_last_name'          => 'required',
                    'bill_email'              => 'required',
                    'bill_city'               => 'required',
                    'ship_first_name'         => 'required',
                    'ship_mobile_no'          => 'required',
                    //'ship_complete_addr'    => 'required',
                    'ship_state'              => 'required',
                    'ship_last_name'          => 'required',
                    'ship_email'              => 'required',
                    'ship_city'               => 'required',
                    'shipping_street_address' => 'required'                            
                ];

    $validator = Validator::make($request->all(),$arr_rules); 

    if($validator->fails())
    {
       $response['status']      = 'warning';
       $response['description'] = 'Something went wrong, please check all fields.';
       return response()->json($response);
    }  

    $response = $this->orderDataService->save_customer_address($form_data);

    if($response['status'] == 'SUCCESS')
    {
        $arr_event                  = [];                 
        $arr_event['ACTION']        = 'ADD';
        $arr_event['MODULE_ID']     = $response['module_id'];
        $arr_event['MODULE_TITLE']  = $this->module_title;     
        $arr_event['USER_ID']       = $loggedInUserId;          

        $this->save_activity($arr_event);

        DB::commit();
        $response['status']      = 'SUCCESS';               
        $response['description'] = str_singular('Customer details has saved.');
        $response['lead_id']     = $response['lead_id'];
        $response['next_url']    = $this->module_url_path.'/find_products/'.base64_encode($response['order_no']);                

        return response()->json($response);
    }
    else
    {
      $response['status']      = 'FAILURE';
      $response['description'] = $response['description'];

      return response()->json($response);
    }
  }



  public function find_products(Request $request,$order_no = 0,$slug=null)
  {   
      $response = $this->orderDataService->find_product($order_no);

      if(isset($response) && count($response) > 0)
      {
        foreach($response as $company_name => $final_arr)
        {
          if(isset($final_arr) && count($final_arr)>0)
          {
            foreach($final_arr as $key_sku => $product_data)
            {
              $unit_price = isset($product_data['unit_wholsale_price'])?(float)num_format($product_data['unit_wholsale_price']):0;

              $item_qty = isset($product_data['qty'])?$product_data['qty']:0;
           
              $sub_total = $unit_price * $item_qty;
              $prod_discount = $this->HelperService->calculate_product_discount($product_data['prodduct_dis_type'],$product_data['product_dis_min_amt'],$product_data['product_discount'],$sub_total);

              /*call Calculate shipping discount*/
              $ship_amount_arr = $this->HelperService->calculate_shipping_discount($product_data['shipping_type'],$product_data['wholesale_price'],$product_data['minimum_amount_off'],$product_data['off_type_amount'],$product_data['shipping_charges']);
              

              $response[$company_name][$key_sku]['prod_discount'] = $prod_discount;
              $response[$company_name][$key_sku]['ship_amount_arr'] = $ship_amount_arr;
              
            }
          }          
        }
      }
      
      $this->arr_view_data['page_slug']         = $slug;
      $this->arr_view_data['arr_result']        = $response;  
      $this->arr_view_data['order_no']           = $order_no;  
      $this->arr_view_data['module_title']      = $this->module_title;
      $this->arr_view_data['page_title']        = 'Add Products';
      $this->arr_view_data['module_url_path']   = $this->module_url_path;

      return view($this->module_view_folder.'.find_products',$this->arr_view_data); 
  }


public function get_product_list(Request $request)
  { 
        $lead_arr = [];
        $lead_id  = "";
       
        $user = Sentinel::check();
        $loggedIn_userId = 0;

        if($user)
        {
            $loggedIn_userId = $user->id;
        }    


        $arr_search_column = $request->input('column_filter');
        $lead_id = $arr_search_column['lead_id'];

        //get maker details
        $lead_obj = $this->RepresentativeLeadsModel->with(['order_details'])
                                                   /* ->whereHas('leads_details',function($q) use($lead_id){
                                                        $q->where('representative_leads_id',$lead_id);
                                                    })*/
                                                    ->where('id',$lead_id)
                                                    ->first();

        if($lead_obj)
        {
          $lead_arr = $lead_obj->toArray();       
        }
        
        $arr_makers = []; 
        $arr_makers = $this->get_all_makers();

      
        $product_tbl_name   = $this->ProductsModel->getTable();        
        $prefixed_product_tbl = DB::getTablePrefix().$this->ProductsModel->getTable();

        $product_category_tbl_name   = $this->CategoryModel->getTable();        
        $prefixed_product_category_tbl = DB::getTablePrefix().$this->CategoryModel->getTable();

        $subcategory_tbl_name   = $this->SubCategoryModel->getTable();        
        $prefixed_subcategory_tbl = DB::getTablePrefix().$this->SubCategoryModel->getTable();

        $product_subcategory_tbl_name   = $this->ProductsSubCategoriesModel->getTable();        
        $prefixed_product_subcategory_tbl = DB::getTablePrefix().$this->ProductsSubCategoriesModel->getTable();

        $maker_tbl                    = $this->MakerModel->getTable();        
        $prefixed_maker_tbl           = DB::getTablePrefix().$this->MakerModel->getTable();

        $vendor_sales_mapping_table  = $this->VendorSalesmanagerMappingModel->getTable();
        $prefix_vendor_sales_mapping_table = DB::getTablePrefix().$this->VendorSalesmanagerMappingModel->getTable();

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $obj_products = DB::table($vendor_sales_mapping_table)
                            ->select(DB::raw($prefixed_product_tbl.".id,". 
                                                     $prefixed_product_tbl.".user_id,". 
                                                     $prefixed_maker_tbl.".company_name,".   
                                                     $prefixed_product_tbl.'.is_active,'.
                                                     
                                                     $prefixed_product_tbl.'.category_id,'.
                                                     $prefixed_product_tbl.'.product_name,'.
                                                      "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                              .$prefix_user_table.".last_name) as user_name,".
                                                     $prefixed_product_tbl.".created_at,".
                                                     $prefixed_product_tbl.".unit_wholsale_price,".
                                                      $prefixed_product_tbl.".product_image,".
                                                     $prefixed_product_tbl.".retail_price"  

                                                   ))
                            ->leftjoin($maker_tbl,$maker_tbl.'.user_id','=',$vendor_sales_mapping_table.'.vendor_id')
                            ->leftjoin($user_table,$user_table.'.id','=',$vendor_sales_mapping_table.'.vendor_id')
                            ->leftjoin($product_tbl_name,$product_tbl_name.'.user_id','=',$vendor_sales_mapping_table.'.vendor_id')
                            ->where($vendor_sales_mapping_table.'.salesmanager_id',$loggedIn_userId)
                            ->where($user_table.'.status','1')
                            ->where($user_table.'.is_approved','1')
                            ->where($product_tbl_name.'.product_complete_status','4')
                            ->where($product_tbl_name.'.is_active','1')
                            ->where($product_tbl_name.'.product_status','1')
                            ->where($product_tbl_name.'.is_deleted','0')
                            ->groupBy($product_tbl_name.'.id')
                            ->orderBy($product_tbl_name.'.updated_at',"DESC");

        // $obj_products = DB::table($product_tbl_name)
        //                             ->select(DB::raw($prefixed_product_tbl.".id,". 
        //                                              $prefixed_product_tbl.".user_id,". 
        //                                              $prefixed_maker_tbl.".company_name,".   
        //                                              $prefixed_product_tbl.'.is_active,'.
                                                     
        //                                              $prefixed_product_tbl.'.category_id,'.
        //                                              $prefixed_product_tbl.'.product_name,'.
        //                                               "CONCAT(".$prefix_user_table.".first_name,' ',"
        //                                                       .$prefix_user_table.".last_name) as user_name,".
        //                                              $prefixed_product_tbl.".created_at,".
        //                                              $prefixed_product_tbl.".unit_wholsale_price,".
        //                                              $prefixed_product_tbl.".retail_price"  

        //                                            ))
        //                             ->leftjoin($prefixed_maker_tbl,$maker_tbl.'.user_id','=',$prefixed_product_tbl.'.user_id')
                                   
        //                             ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_product_tbl.'.user_id')

        //                             ->join($vendor_sales_mapping_table,$vendor_sales_mapping_table.'.vendor_id','=',$prefixed_product_tbl.'.user_id')

        //                             ->where($user_table.'.status',1)
        //                             ->where($user_table.'.is_approved',1)

        //                             /*->leftjoin($product_category_tbl_name,$product_category_tbl_name.'.id','=',$prefixed_product_tbl.'.category_id')

        //                             ->where($product_category_tbl_name.'.is_active',1)
                                   
        //                             ->join($product_subcategory_tbl_name,$product_subcategory_tbl_name.'.product_id','=',$prefixed_product_tbl.'.id')  

        //                             ->join($prefixed_subcategory_tbl,$product_subcategory_tbl_name.'.sub_category_id','=',$prefixed_subcategory_tbl.'.id')

        //                             ->groupBy($product_subcategory_tbl_name.'.product_id')  
                                    
        //                             ->where($subcategory_tbl_name.'.is_active',1)*/

        //                             ->where($product_tbl_name.'.product_complete_status',4)
        //                             ->where($product_tbl_name.'.is_active',1)
        //                             ->where($product_tbl_name.'.product_status',1)
        //                             ->where($product_tbl_name.'.is_deleted',0)
        //                             ->orderBy($product_tbl_name.'.updated_at',"DESC");
                                  
                                    /*->get()*/
                     
            /*if(isset($arr_makers) && sizeof($arr_makers)>0)                     
            {*/
              //$obj_products = $obj_products->whereIn($prefix_user_table.'.id', $arr_makers);
            /*}*/ 
           
            /* ---------------- Filtering Logic ----------------------------------*/                              
            if(isset($arr_search_column['q_product_name']) && $arr_search_column['q_product_name']!="")
            {
                $search_term      = $arr_search_column['q_product_name'];
                $obj_products = $obj_products->where($prefixed_product_tbl.'.product_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_company_name']) && $arr_search_column['q_company_name']!="")
            {
                $search_term      = $arr_search_column['q_company_name'];
                $obj_products = $obj_products->where($prefixed_maker_tbl.'.company_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
            {
                $search_term      = $arr_search_column['q_maker_name'];
                $obj_products = $obj_products->having('user_name','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_wholesale_price']) && $arr_search_column['q_wholesale_price']!="")
            {
                $search_term      = $arr_search_column['q_wholesale_price'];
                $obj_products = $obj_products->where($prefixed_product_tbl.'.unit_wholsale_price','LIKE', '%'.$search_term.'%');
            }

            if(isset($arr_search_column['q_retail_price']) && $arr_search_column['q_retail_price']!="")
            {
                $search_term  = $arr_search_column['q_retail_price'];
                $obj_products = $obj_products->where($prefixed_product_tbl.'.retail_price','LIKE', '%'.$search_term.'%');
            }

           /* if(isset($lead_arr) && count($lead_arr)>0)
            { 
                $maker_id  = $lead_arr['maker_id'];
                $obj_products = $obj_products->where($prefixed_product_tbl.'.user_id', $maker_id);
            } */

            
          //dd($obj_products);
          $current_context = $this;

          $json_result  = Datatables::of($obj_products);

          /* Modifying Columns */
          $json_result =  $json_result->make(true);
          $build_result = $json_result->getData();
          
          return response()->json($build_result);
  }


    public function get_all_makers()
  {
      $user           = Sentinel::check();
      $role_id        = Sentinel::findRoleBySlug('Maker');
      $arr_maker_list = $arr_maker = [];
      $post_code      = '';

      if($user)
      {
          $post_code  = $user->post_code;
      } 
      else{
        return false;
      }

      $user_table             =  $this->UserModel->getTable();
      $prefix_user_table      = DB::getTablePrefix().$user_table;

      $maker_table            =  $this->MakerModel->getTable();
      $prefix_maker_table     = DB::getTablePrefix().$maker_table;

      $role_user_table        =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $role_table             =  $this->RoleModel->getTable();
      $prefix_role_table      = DB::getTablePrefix().$role_table;

      $arr_maker = DB::table($user_table)
                          ->select(DB::raw($prefix_user_table.".id as id"
                                          ))
                          ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                          ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
                          ->where($role_table.'.slug','=','Maker')
                          ->whereNull($user_table.'.deleted_at')
                          ->where('status','1')
                          ->where($user_table.'.id','!=','1')
                          // ->where($user_table.'.post_code',$post_code)
                          ->orderBy($user_table.'.created_at','DESC')
                          ->get()
                          ->toArray();

      if(isset($arr_maker) &&count($arr_maker) > 0)
      {
          foreach ($arr_maker as $key => $maker) {
          
          $arr_maker_list[]['maker_id'] = $maker->id;
        }
      }

      return $arr_maker_list;
  }


 /* public function get_product_details($enc_id)
  {
      $response = $this->orderDataService->get_product_details($enc_id);

      return response()->json($response);      
  }*/

  public function get_product_details(Request $request)
  { 
      $product_id = '';
      $maker_id = '';
      $product_arr = $maker_arr = [];
      $form_data = $request->all();

      if(isset($form_data) && !empty($form_data) && isset($form_data['product_id']) && !empty($form_data['product_id']))
      {

        $product_id = base64_decode($form_data['product_id']);

      }
      else
      {
        return redirect()->back();
      }

       /*check this product is active or not*/

        if(isset($product_id) && $product_id!='')
        {
            $isProductActive = $this->ProductsModel->where('id',$product_id)->pluck('is_active')->first();

            if($isProductActive !=1)
            {
               $message                      = "This product is currently unavailable.";
               $this->arr_view_data['message']          = $message;
               
            }
           
            $this->arr_view_data['isProductActive'] = $isProductActive;
        }  
        $first_pro_details_mul_images=[];
        if($product_id)
        {

            $arr_data = [];
            $obj_data = $this->ProductsModel->with(['productDetails.inventory_details','categoryDetails','brand_details'])
                             ->where('id',$product_id)
                             ->first();


            if($obj_data)
            {
                $product_arr = $obj_data->toArray();
                $new_product_id = base64_encode($product_id);
                $first_pro_details_mul_images = get_multiple_images($new_product_id);
              $this->arr_view_data['product_arr'] = $product_arr;
            }
            
            // $html = view($this->module_view_folder.'._image_gallery',$this->arr_view_data)->render();
            
            if(isset($product_arr) && sizeof($product_arr)>0)
            {
              $sku_id = isset($product_arr['product_details'][0]['sku'])?$product_arr['product_details'][0]['sku']:"";
              $pro_details = get_style_dimension($sku_id);



              /*Meta details start*/

              $arr_meta_details = [];
              $product_name = isset($product_arr['product_name'])?$product_arr['product_name']:"";
              $product_image = isset($product_arr['product_image'])?$product_arr['product_image']:"";  
              $brand_name = isset($product_arr['brand_details']['brand_name'])?$product_arr['brand_details']['brand_name']:"";
              $brand_id = isset($product_arr['brand_details']['id'])?$product_arr['brand_details']['id']:"";
             
              //dd($product_arr,$product_name,$product_image,$brand_name);

              $meta_image = ""; 
              if($product_image!="")
              {

                $meta_image = url('/storage/app/'.$product_image); 
                
              }
              else
              {
                $meta_image = url('/assets/images/no-product-img-found.jpg');
              }

              $arr_meta_details['meta_title']  = $brand_name.'/'.$product_name;
              $arr_meta_details['meta_large_image_content']  = 'product_large_image';
              $arr_meta_details['meta_image']  = $meta_image; 

              /*Meta details stop*/

              $this->arr_view_data['pro_details'] = isset($pro_details)?$pro_details:"";
              $this->arr_view_data['sku_id']     = $sku_id;                  
              // $this->arr_view_data['html']       = $html;                
              
              
              // $category_arr = $this->ElasticSearchService->activate_category_product('25');

              // dd($category_arr);


              

              /*get maker details*/
              if(isset($form_data['vendor_id']) && !empty($form_data['vendor_id']))
              {
                $maker_id = base64_decode($form_data['vendor_id']);
                
                $maker_obj = $this->UserModel->with(['store_details','maker_details'])
                                     ->where('id',$maker_id)
                                     ->first();
                if($maker_obj)
                {
                    $maker_arr = $maker_obj->toArray();
                }


              }                
              /*end*/



              /*get first product detail*/
              $first_product_id = $request->input('product_id');
              if(isset($first_product_id))
              { 
                $first_product_id = intval(base64_decode($first_product_id));
                $arr_data         = [];
                $obj_data         = $this->ProductsModel->with(['productDetails.inventory_details',
                                                                'categoryDetails']) 
                                                        ->where('id',$first_product_id)
                                                        ->first();
                if($obj_data)
                {
                  $first_product_arr = $obj_data->toArray();
                  if (isset($first_product_arr['product_details'][0]['sku'])) {
                     $first_prod_sku    = $first_product_arr['product_details'][0]['sku']; 
                   } 

                   /*get related category product*/

                    $category_id = isset($first_product_arr['category_id'])?$first_product_arr['category_id']:"";

                    $obj_subcategory =$this->ProductsSubCategoriesModel
                    ->where('product_id',$product_id)
                    ->first();

                    if($obj_subcategory)
                    {
                      $arr_subcategory = $obj_subcategory->toArray();


                      $related_product_arr =$this->ProductsSubCategoriesModel
                                        ->where('sub_category_id',$arr_subcategory['sub_category_id'])
                                        ->with('productDetails')
                                        ->whereHas('productDetails',function($q) use($product_id){
                                                          $q->where('id','<>',$product_id);
                                                          $q->where('product_complete_status','4');
                                                          $q->where('is_active','1');
                                                          $q->where('is_deleted','0');
                                                          $q->orderBy('updated_at','DESC');
                                                         })
                                        ->limit(10)
                                        ->get()
                                        ->toArray();


                      // dd($related_product_arr);

                    }


                     // $related_product_arr = $this->ProductsModel->where('is_active','1')
                     //                      ->where('is_deleted','0')                    
                     //                      ->where('product_complete_status',4)    
                     //                      ->where('id','<>',$product_id)    
                     //                      ->where('category_id',$category_id)    
                     //                      ->orderBy('updated_at','DESC')
                     //                      ->take(8)
                     //                      ->get()
                     //                      ->toArray();  

                    /*end related category*/                    
                }
              
                if(isset($first_prod_sku))
                {                                    
                  $first_pro_details = get_style_dimension($first_prod_sku);
                  $first_pro_qty     = get_product_quantity($first_prod_sku);
                }
              }

            }
            else
            {
              $this->arr_view_data['arr_data']   = $product_arr;
              $this->arr_view_data['status']     = "FAILURE"; 
            }
        }
        else
        {
          $this->arr_view_data['status']     = "FAILURE"; 

        }

      $this->arr_view_data['first_prod_arr']     = isset($first_product_arr)?$first_product_arr:[]; 
      $this->arr_view_data['first_prod_details'] = isset($first_pro_details)?$first_pro_details:[];
      $this->arr_view_data['first_pro_qty']      = isset($first_pro_qty)?$first_pro_qty:'';
      $this->arr_view_data['arr_data']           = isset($product_arr)?$product_arr:'';
      $this->arr_view_data['meta_details']       = isset($arr_meta_details)?$arr_meta_details:[];
      $this->arr_view_data['related_product_arr']       = isset($related_product_arr)?$related_product_arr:[];
      $this->arr_view_data['first_prod_detail_mul_images'] = isset($first_pro_details_mul_images)?$first_pro_details_mul_images:[];
      $this->arr_view_data['maker_arr']         = isset($maker_arr)?$maker_arr:[];
      $this->arr_view_data['request_values']    = $request->all();
      $this->arr_view_data['search_value']      = $request->all();
      $this->arr_view_data['module_url_path']   = $this->module_url_path;
      $this->arr_view_data['order_no']          = isset($form_data['order_no'])?$form_data['order_no']:"";
      $this->arr_view_data['page_title']        = 'Product Detail';
      $this->arr_view_data['module_base_path'] = url($this->sales_manager_panel_slug);
      
      return view($this->module_view_folder.'.product_detail',$this->arr_view_data);
  }

  public function store_lead(Request $request)
  {
    
    $order_arr = $role_arr = $form_data = [];
    $loggedInUserId = 0;
    $total_wholesale_price = 0.0;


    $user = Sentinel::check();

    $order_no = $request->input('order_no');
    $product_id = $request->input('product_id',null);
    $order_no = base64_decode($order_no);
    $product_id = base64_decode($product_id);

    $form_data['maker_id'] = $request->input('maker_id',null);
    $form_data['product_id'] = $product_id;
    $form_data['sku_num'] = $request->input('sku_no',null);
    $form_data['item_qty'] = $request->input('item_qty',null);
    $form_data['retail_price'] = $request->input('retail_price',null);
    $form_data['wholesale_price'] = $request->input('wholesale_price',null);
    $form_data['total_wholesale_price'] = $request->input('total_wholesale_price',null);
    $form_data['total_retail_price'] = '';
    $form_data['order_no'] = $request->input('order_no',null);
    
    if($user)
    {
        $loggedInUserId = $user->id;
        
        /*get role of logedin user*/

        $role_details = $this->RoleUsersModel->where('user_id',$user->id)->with(['role_name'])->first();
        
        if(isset($role_details))
        {
            $role_arr = $role_details->toArray(); 
        }
        
    }

    $data = $this->orderDataService->store_lead($form_data);
    
    //$data = $this->orderDataService->store_lead($request);
   
    if($data['status'] == 'SUCCESS')
    { 
       /*----update total wholesale price as per total product dis ,total shipping charges ,tot shiping dis------*/

       $order_details = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();
      
       if(isset($order_details))
       {
          $order_arr = $order_details->toArray();
        
        
          $total_wholesale_price = $order_details['total_wholesale_price']+$order_details['total_product_shipping_charges']-$order_details['total_product_discount']- num_format($order_details['total_shipping_discount']);

          $this->RepresentativeLeadsModel->where('order_no',$order_no)->update(['total_wholesale_price'=>$total_wholesale_price]);
       }
       


       $response['status']      = 'SUCCESS';
       $response['description'] = 'Product added to bag.';
       $response['next_url']    = $this->module_url_path.'/find_products/'.base64_encode($data['order_no']); 
       

    }
    else
    {
       $response['status']      = 'FAILURE';
       $response['description'] = 'Something went wrong, please try again.';
       $response['next_url']    = $this->module_url_path.'/find_products/'.base64_encode($order_no);   
    }
    return response()->json($response);
  }

  public function order_summary($order_no = 0)
  { 
     
      $result = $data = [];
      $total_product_shipping_charges = $total_ship_charges =0;

     /*---update whole calculation vendor wise---*/

     $order_num = base64_decode($order_no);

     $order_product_details_arr = $this->RepresentativeProductLeadsModel->where('order_no',$order_num)
                                                                             ->get()
                                                                             ->toArray();
      if(isset($order_product_details_arr) && count($order_product_details_arr)>0)
      {
          foreach ($order_product_details_arr as $key => $value) 
          {
            $result[$value['maker_id']][] = $value;
          }

          foreach($result as $key => $res) 
          { 
              $i=0;
              
              $total_product_discount = array_sum((array_column($res,'product_discount')));
              $total_shipping_charges = array_sum((array_column($res,'shipping_charges')));
              $total_shipping_charges_discount = array_sum((array_column($res,'shipping_charges_discount')));
              
              $total_product_shipping_charges = array_sum((array_column($res,'product_shipping_charge')));

              $total_wholesale_price = array_sum((array_column($res,'wholesale_price')));

              $data['total_product_discount']          = $total_product_discount;
              $data['total_product_shipping_charges']  = $total_product_shipping_charges;
              $data['total_shipping_charges']          = $total_shipping_charges;
              $data['total_shipping_discount']         = $total_shipping_charges_discount;

              $data['total_wholesale_price'] = $total_wholesale_price+$total_product_shipping_charges-$total_product_discount-$total_shipping_charges_discount;

              $this->RepresentativeLeadsModel->where('order_no',$order_num)->where('maker_id',$key)->update($data);
            
              $total_ship_charges += $res[$i]['product_shipping_charge'];

              $data['total_product_shipping_charges'] = $total_ship_charges;

              $i++;

          }
        
     }


    /*---------------------------------------*/

  
    $arr_data = $updated_arr = $leads_updated_arr = [];

    $order_data = $this->orderDataService->order_summary($order_no);
    
    $total_wholesale_price = $total_product_discount = $total_shipping_charges = 
    $total_shipping_charge = $total_shipping_discount = $total_wholsale_price = 0; 

      
      /*if vendor update the product details then update calculation according to new changes in representative leads and representative leads table table*/
      $arrVendors = $arr_sku = [];


      if(isset($order_data) && count($order_data)>0)
      { 
         if (isset($order_data['order_details']) && count($order_data['order_details'])>0)
         {
              /* Workaround to brind arrays into contigious indexes Starts*/
              $arr_tmp_holding = [];
              $arr_final_holding = [];

              foreach ($order_data['order_details'] as $key => $tmpArr) {
                 
                if(isset($arr_tmp_holding[$tmpArr['maker_id']]) == false)
                {
                  $arr_tmp_holding[$tmpArr['maker_id']] = [$tmpArr];
                }
                else
                {
                  array_push($arr_tmp_holding[$tmpArr['maker_id']], $tmpArr);
                }
              }

              foreach ($arr_tmp_holding as $key => $tmpArr) {
                $arr_final_holding = array_merge($arr_final_holding,$tmpArr); 
              }

              $order_data['order_details'] = $arr_final_holding;

              /* Workaround to brind arrays into contigious indexes Ends*/

              foreach($order_data['order_details'] as $key => $orders)
              {
                $updated_arr['wholesale_price'] = $updated_arr['unit_wholsale_price'] = $updated_arr['product_shipping_charge'] = $updated_arr['shipping_charges_discount'] = $updated_arr['shipping_charges'] = $updated_arr['product_discount'] = 0;

                  $tmp_tag = $orders['maker_id'];
                  if(count($arrVendors) == 0)
                  {
                    array_push($arrVendors,$orders['maker_id']);

                  }

                  if(!in_array($orders['maker_id'],$arrVendors))
                  {
                      array_push($arrVendors,$orders['maker_id']);

                      $total_wholesale_price = $total_product_discount = $total_shipping_charges = 
                      $total_shipping_charge = $total_shipping_discount = $total_wholsale_price = 0;
  
                  }
                
               
                  $updated_arr['unit_wholsale_price'] = $orders['product_details']['unit_wholsale_price'];
                  $updated_arr['wholesale_price'] = $orders['product_details']['unit_wholsale_price']*$orders['qty'];
                  // dump($orders['product_details']['product_discount']);
                  // dump($orders['product_details']);
                  //calculate product discount calculation
                  if(isset($orders['product_details']['prodduct_dis_type']) && $orders['product_details']['prodduct_dis_type'] == 1)
                  {
                      
                    $updated_arr['product_discount'] = 0;

                    if($updated_arr['wholesale_price'] >= $orders['product_details']['product_dis_min_amt'])
                    {
                      $updated_arr['product_discount'] = $updated_arr['wholesale_price']*$orders['product_details']['product_discount']/100;
                    }
                      // dump($updated_arr);


                  }
                  elseif(isset($orders['product_details']['prodduct_dis_type']) && $orders['product_details']['prodduct_dis_type'] == 2)
                  {

                      $updated_arr['product_discount'] = 0;

                      if($updated_arr['wholesale_price'] >= $orders['product_details']['product_dis_min_amt'])
                      {
                        $updated_arr['product_discount'] = $orders['product_details']['product_discount'];
                      }

                      // dump($updated_arr);

                  }
                  else
                  {
                     $updated_arr['product_discount'] = 0.00;
                  }

                  $updated_arr['product_shipping_charge'] = $orders['product_details']['shipping_charges'];
                  $updated_arr['shipping_charges'] = $orders['product_shipping_charge']-$orders['shipping_charges_discount'];

                  //calculate shipping charges and discount


                  if(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 1)
                  { 
                    
                      if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                      {   
                          $updated_arr['shipping_charges_discount'] = 0.00;
                          $updated_arr['shipping_charges']          = 0.00;
                          $updated_arr['product_shipping_charge']   = 0.00;
                      }
                      else{


                        $updated_arr['shipping_charges_discount'] = 0.00;
                        $updated_arr['shipping_charges'] = $orders['product_shipping_charge']-$orders['shipping_charges_discount'];
                        $updated_arr['product_shipping_charge'] = $orders['product_details']['shipping_charges'];

                       
                      }
                     

                  }
                  elseif(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 2)
                  {
                    
                      if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                      {
                         $updated_arr['shipping_charges_discount'] = 0;
                         $updated_arr['shipping_charges'] = 0;

                          $updated_arr['shipping_charges_discount'] = $updated_arr['product_shipping_charge']*$orders['product_details']['off_type_amount']/100;

                          $updated_arr['shipping_charges'] = $updated_arr['product_shipping_charge'] - 
                          $updated_arr['shipping_charges_discount'];

                      } 

                  }
                  elseif(isset($orders['product_details']['shipping_type']) && $orders['product_details']['shipping_type'] == 3)
                  {
                     $updated_arr['wholesale_price'] = $orders['product_details']['unit_wholsale_price']*$orders['qty'];
                 /*   dump($orders);
                    dump($updated_arr['wholesale_price']);
                    dump($orders['product_details']['minimum_amount_off']);*/
                      if($updated_arr['wholesale_price'] >= $orders['product_details']['minimum_amount_off'])
                      { 
                         $updated_arr['shipping_charges_discount'] = 0;

                         $updated_arr['shipping_charges'] = 0;
                        
                        $updated_arr['shipping_charges_discount'] = $orders['product_details']['off_type_amount'];

                        $updated_arr['shipping_charges'] = $updated_arr['product_shipping_charge'] - 
                        $updated_arr['shipping_charges_discount'];
                        // dump($orders['product_details']['off_type_amount']);
                        // dump($orders['product_details']);
                      } 

                  }
                  else{
                      
                      $updated_arr['shipping_charges_discount'] = 0.00;
                      $updated_arr['shipping_charges']          = 0.00;
                      $updated_arr['product_shipping_charge']   = 0.00;
                     
                  }

                  // dd($updated_arr);
                  // dump($updated_arr);
                  $this->RepresentativeProductLeadsModel
                        ->where('representative_leads_id',$orders['representative_leads_id'])
                        ->where('order_no',$orders['order_no'])
                        ->where('maker_id',$orders['maker_id'])
                        ->where('product_id',$orders['product_id'])
                        ->update($updated_arr);


                  $total_product_discount  += isset($updated_arr['product_discount'])?$updated_arr['product_discount']:0.00;

                  $total_shipping_charges  += isset($updated_arr['shipping_charges'])?$updated_arr['shipping_charges']:0.00; 

                  $total_shipping_charge   += isset($updated_arr['product_shipping_charge'])?$updated_arr['product_shipping_charge']:0.00;

                  $total_shipping_discount += isset($updated_arr['shipping_charges_discount'])?$updated_arr['shipping_charges_discount']:0.00;

                  $total_wholsale_price    += isset($updated_arr['wholesale_price'])?$updated_arr['wholesale_price']:0.00;

                
                //update all total calculation in representative leads table
                 
                $leads_updated_arr['total_wholesale_price'] = $total_wholsale_price + $total_shipping_charge - $total_product_discount - $total_shipping_discount;
                  
                $leads_updated_arr['total_product_discount'] = $total_product_discount;
                $leads_updated_arr['total_shipping_charges'] = $total_shipping_charges;
                $leads_updated_arr['total_shipping_discount'] = $total_shipping_discount;
                $leads_updated_arr['total_product_shipping_charges'] = $total_shipping_charge;

           
                $this->RepresentativeLeadsModel
                      ->where('order_no',$orders['order_no'])
                      ->where('maker_id',$orders['maker_id'])
                      ->update($leads_updated_arr); 


                
            }

         

         }
// dd(4444);
        
      }


      /*-----------------------------------------------------------------------------------*/


    /*get country */

    $country_arr = $this->CountryModel->where('is_active',1)->orderBy('name','ASC')->get()->toArray();

    if(!isset($order_data) && count($order_data) <= 0)
    { 
      Flash::error('Something went wrong,please try again.');

      return redirect()->back();
    }

    $this->arr_view_data['order_no']        = $order_no;
    $this->arr_view_data['data']            = $data;
    $this->arr_view_data['arr_data']        = $order_data;
    $this->arr_view_data['country_arr']     = $country_arr;
    $this->arr_view_data['module_title']    = $this->module_title;
    $this->arr_view_data['page_title']      = 'Order Summary';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['product_data']    = $result;
    
    return view($this->module_view_folder.'.order_summary',$this->arr_view_data); 
  }


  public function finalize_lead(Request $request,$enc_order_no = false)
  { 
    $form_data = $request->all();
    $loggedInUserId = 0;

    //check login user

     $user = Sentinel::check(); 
     if($user)
     {
       $loggedInUserId = $user->id;
     }

    $current_date = date('Y-m-d h:i:s');
    $order_no = isset($enc_order_no)?base64_decode($enc_order_no):'';
    
    $order_no = isset($order_no)?base64_decode($order_no):'';

    $arr_data    = [];
    $msg = '';
    
    if(isset($order_no))
    {
      
        $type = $request->input('type');
        
        $update_lead_arr = [];
        if($type=='confirm_requested')
        {
          $update_lead_arr['is_confirm'] = 2;
          $update_lead_arr['created_at'] = $current_date;
          $msg = "Order has been confirmed and sent to Customer for approval.";
          
        }
        elseif($type=='quote')
        {
           $update_lead_arr['is_confirm'] = 4;
           $msg = "Order has been saved.";
        }
        elseif($type=='reject')
        {
          $update_lead_arr['is_confirm'] = 3;
          $msg = "Order has been rejected.";

        }
        elseif($type=='confirm')
        {
           $update_lead_arr['is_confirm'] = 1;
           $msg = "Order has been confirmed.";
        }

        $orderData = $this->RepresentativeLeadsModel->where('order_no',$order_no)->first();

        if($orderData)
        {
          $orderData = $orderData->toArray();
        }

        if(isset($orderData['sales_manager_id']) && $orderData['sales_manager_id'] != 0)
        {
           $update_lead_arr['rep_sales_commission'] = $this->CommissionService->get_sales_manager_commission($orderData['sales_manager_id']);
        }

        if(isset($orderData['representative_id']) && $orderData['representative_id'] != 0)
        {
          $update_lead_arr['rep_sales_commission'] = $this->CommissionService->get_representative_commission($orderData['representative_id']);
        }   

        $is_update = $this->RepresentativeLeadsModel->where('order_no',$order_no)
                                                    ->update($update_lead_arr);
        if($is_update)
        {
          if ($type=='confirm_requested') {

            $objOrder = $this->RepresentativeLeadsModel->with(['leads_details','address_details'])->where('order_no',$order_no)->get();

            if ($objOrder) {
              
              $orderData = $objOrder->toArray();
            }
            $sendEmailToRetailer  = $this->RepsEmailService->send_retailer_mail($orderData,$order_no,$user);

           // $send_notification = $this->GeneralService->representative_send_notification($order_no,$user);

          }

                  

        /*after save or confirm order if product is deactivated then skip that product
          developer -> priyanka
        */
     
        $lead_product_data = $this->RepresentativeProductLeadsModel
                                   ->with([ 
                                            'product_details',
                                            'maker_details',
                                            'get_product_min_qty',
                                            'product_details.shop_settings'
                                          ])
                                   ->where('order_no',$order_no)
                                   ->get()
                                   ->toArray();

          
        $product_count   = 0;
        $new_product_arr = [];
         

        if(isset($lead_product_data) && count($lead_product_data)>0)
        {

            foreach($lead_product_data as $key => $leads)
            {
                if( $leads['product_details']['product_status'] == 0  ||
                    $leads['product_details']['is_active'] == 0 ||
                    $leads['product_details']['product_complete_status'] != 4
                  )

                { 
                   $is_delete = $this->RepresentativeProductLeadsModel
                                     ->where('product_id',$leads['product_id'])
                                     ->where('order_no',$leads['order_no'])
                                     ->delete();
                }
                else
                {  
                   $new_product_arr[] = $leads;
                }

     
                //and if all product is deleted from order then that order should be deleted

                $product_count =  $this->RepresentativeProductLeadsModel
                                       ->where('maker_id',$leads['maker_id'])
                                       ->where('order_no',$leads['order_no'])
                                       ->count();
        
                if($product_count == 0)
                {
                   $this->RepresentativeLeadsModel->where('maker_id',$leads['maker_id'])
                                                  ->where('order_no',$leads['order_no'])
                                                  ->delete();
                }

            }
        

          }

          $lead_product_data = $new_product_arr;

          $arr_p_data = $arr = $arr_p_data = $company_names =[];

          $total_wholesale_price = $total_shipping_discount = $total_shipping_charges = $total_product_discount= $total_product_ship_charges = $final_total = 0;

          if(count($lead_product_data) > 0)
          {
              foreach ($lead_product_data as $key => $product) 
              {
                $arr_p_data['company_name'][] = $product['maker_details']['company_name'];
              }
          }
            
          if(count($arr_p_data) > 0)
          {
            $company_names = array_unique($arr_p_data['company_name']);
          }

          if(count($company_names) > 0 && isset($company_names))
          {
              foreach($company_names as $company)
              {
                  foreach ($lead_product_data as $key => $product)
                  {

                      if($product['maker_details']['company_name'] == $company)
                      {

                          $total_wholesale_price += $product['wholesale_price'];
                          $total_shipping_discount += $product['shipping_charges_discount'];
                          $total_shipping_charges += $product['shipping_charges'];
                          $total_product_ship_charges += $product['product_shipping_charge'];
                          $total_product_discount  += $product['product_discount'];
                          
                          $final_total  = $total_wholesale_price+$total_product_ship_charges-$total_shipping_discount-$total_product_discount;

                          /*update into representative leads table*/

                          $data = [];
                          $data['total_wholesale_price']              =  $final_total;
                          $data['total_product_discount']             =  $total_product_discount;
                          $data['total_shipping_charges']             =  $total_shipping_charges;
                          $data['total_product_shipping_charges']     =  $total_product_ship_charges;
                          $data['total_shipping_discount']            =  $total_shipping_discount;

                          $this->RepresentativeLeadsModel->where('maker_id',$product['maker_id'])
                                                         ->where('order_no',$product['order_no'])
                                                         ->update($data);

                          /*---------------------------------------*/
                      }
                      else
                      {
                         $total_wholesale_price = $total_shipping_discount = $total_shipping_charges = $total_product_discount= $total_product_ship_charges = $final_total = 0;
                      }

                  }
              }

          }

        /*-----------------------------------------------------------------*/


        $data = $this->create($form_data);
        $arr_event                   = [];                 
        $arr_event['ACTION']         =  'EDIT';
        $arr_event['MODULE_ID']      = $order_no;
        $arr_event['MODULE_TITLE']   = $this->module_title;   
        $arr_event['USER_ID']        = $loggedInUserId;          
        

        $this->save_activity($arr_event);

 
        $count = $this->RepresentativeLeadsModel->where('order_no',$order_no)->count();
          
        if($count == 0)
        {
            if($type == 'quote')
            {
               Flash::error("Order has not saved beacause none of the product(s) are available.");
            }

            if($type == 'confirm_requested')
            {
              Flash::error("Order has not confirmed beacause none of the product(s) are available.");
            }

        }else
        {

          if($type == 'confirm_requested')
          {
              $send_notification = $this->GeneralService->representative_send_notification($order_no,$user);
          }
            
          Flash::success($msg);
       
        }                                           




        }
        else
        {
          Flash::error('Something went wrong, please try again.');
        }
     }
    
    else
    {
      Flash::error('Something went wrong, please try again.');
    }   

    return redirect($this->module_url_path);
  }


  public function update_lead_listing($lead_id)
  {

    $leads_id = base64_decode($lead_id);

    $entity = $this->RepresentativeLeadsModel->where('id',$leads_id)->first();

    $leads_arr = [];
    $obj_data = $this->RepresentativeLeadsModel
                     ->with(['address_details','leads_details.product_details','retailer_user_details'=>function($q1)
                     {
                        $q1->select('id','email','first_name','last_name');
                     },'representative_user_details'=>function($q2)
                     {
                        $q2->select('id','email','first_name','last_name');
                     }])
                     ->where('id',$leads_id)
                     ->first();
    if($obj_data)
    {
        $leads_arr = $obj_data->toArray();
    }                                           
  
    $this->arr_view_data['leads_arr']       = $leads_arr;
    $this->arr_view_data['module_title']    = $this->module_title;
    $this->arr_view_data['page_title']      = $this->module_title;
    $this->arr_view_data['module_url_path'] = $this->module_url_path;


    if($entity)
    {   
        //Activate the user
      $is_update = $this->RepresentativeLeadsModel->where('id',$leads_id)->update(['is_confirm'=>2]);

      if($is_update)
      {

          /******************Notification to maker START*******************************/
              $loggedInUserId = 0;
              $user = Sentinel::check();

              if($user)
              {
                  $loggedInUserId = $user->id;
              }

              $this->RepsEmailService->vendor_order_retailer_mail($leads_arr,$leads_arr['order_no']);

              $first_name = isset($user->first_name)?$user->first_name:"";
              $last_name  = isset($user->last_name)?$user->last_name:"";  

              $get_lead_maker_id = $this->RepresentativeLeadsModel->where('id',$lead_id)->first();

              $arr_event                 = [];
              $arr_event['from_user_id'] = $loggedInUserId;
              $arr_event['to_user_id']   = isset($get_lead_maker_id->maker_id)?$get_lead_maker_id->maker_id:"";
              $arr_event['description']  = 'Order confirmed by a'.$first_name.' '.$last_name.' .';
              $arr_event['title']        = 'Order confirmed by a sales manager';
              $arr_event['type']         = 'maker';   
              
              $this->GeneralService->save_notification($arr_event);
          /**********************Notification to admin END*********************************/

              $response['status']      = "success";
              $response['description'] = "Order has been confirmed.";
              $response['url'] = $this->module_url_path;
              return response()->json($response); 
      }
      else
      {
        $response['status']      = "failure";
        $response['description'] = "Error occurred while confirming order.";
        $response['url']         = $this->module_url_path;
        return response()->json($response); 
      }

    }

     return view($this->module_view_folder.'.leads_listing_view',$this->arr_view_data);
  }


  public function update_product_qty(Request $request)
  {
      $update_product = $this->orderDataService->update_product_qty($request);

      $order_num = $update_product['arr_responce']['order_no'];
      
      $order_product_details_arr = $this->RepresentativeProductLeadsModel->where('order_no',$order_num)
                                                                             ->get()
                                                                             ->toArray();
     
     if(isset($order_product_details_arr) && count($order_product_details_arr)>0)
     {
        foreach ($order_product_details_arr as $key => $value) 
        {
          $result[$value['maker_id']][] = $value;
        }


        foreach($result as $key => $res) 
        {
            $total_product_discount = array_sum((array_column($res,'product_discount')));
            $total_shipping_charges = array_sum((array_column($res,'shipping_charges')));
            $total_shipping_charges_discount = array_sum((array_column($res,'shipping_charges_discount')));

            $total_product_shipping_charges = array_sum((array_column($res,'product_shipping_charge')));
             
            $total_wholesale_price = array_sum((array_column($res,'wholesale_price')));

            
            $data['total_product_discount']  = $total_product_discount;
            $data['total_product_shipping_charges']  = $total_product_shipping_charges;
            $data['total_shipping_charges']  = $total_shipping_charges;
            $data['total_shipping_discount'] = $total_shipping_charges_discount;

            $data['total_wholesale_price'] =  $total_wholesale_price+$total_product_shipping_charges-$total_product_discount-$total_shipping_charges_discount;

            $this->RepresentativeLeadsModel->where('order_no',$order_num)->where('maker_id',$key)->update($data);

        }
        
     }
   
    
  return response()->json($update_product);
  }

    public function delete_all_products($enc_order_no = 0)
  {
    $response = $this->orderDataService->delete_all_products($enc_order_no);

    if($response['status'] == 'FAILURE')
    {

      Flash::error($response['description']);
          
    }else
    {
        Flash::success($response['description']);
    }
    
    return redirect()->back();
  }

  public function lead_listing(Request $request)
  {



    $search_data                 = $request->input('column_filter');
    $confirmed_flag              = $request->input('confirmed_flag');
    $pending_flag                = $request->input('pending_flag');
    $completed_flag              = $request->input('completed_flag');
    $approved_flag               = $request->input('approved_flag');
    $net_30_completed_orders     = $request->input('net_30_completed_orders');
    $net_30_pending_orders       = $request->input('net_30_pending_orders');


    $module_data['module_url'] = $this->module_url_path;

    $user = Sentinel::check();
    $sales_manager_id = 0;
    $total_amt = 0; 

    if($user)
    {
      
      $sales_manager_id = $user->id;
            
    }    


   /* $data = $this->orderDataService->get_order_list($search_data,$module_data,$sales_manager_id,false,$confirmed_flag,$pending_flag,$completed_flag,$approved_flag);
*/
    $data = $this->get_order_list($search_data,$module_data,$sales_manager_id,false,$confirmed_flag,$pending_flag,$completed_flag,$approved_flag,$net_30_completed_orders,$net_30_pending_orders);

     //Calculate total by Harshada on date 09 Sep 2020
   
      /* $total_amt =array_reduce($data->get()->toArray(), function(&$res, $item) {
          return $res + $item->total_wholesale_price;
      }, 0);*/


    $order_arr = $data->get()->toArray();

    if(isset($order_arr))
    {
      foreach($order_arr as $key => $order)
      {
        
          //make a query if order is only save
          if($order->is_confirm == 4)
          { 
              $total_sum = $this->RepresentativeLeadsModel
                                ->where('order_no',$order->order_no)
                                ->where('maker_id','!=',0)                
                                ->sum('total_wholesale_price');  

              $total_sum = num_format($total_sum); 
             

          }
          else
          { 
             $total_sum = isset($order->total_wholesale_price)?num_format($order->total_wholesale_price):0;
          }

          $total_amt += $total_sum;

      }

    }  


     
    $current_context = $this;
    
    $json_result     = \Datatables::of($data);
    
    $json_result     = $json_result->editColumn('enc_id',function($data)
                        {
                            return base64_encode($data->id);
                        })
                       ->editColumn('total_retail_price', function($data){

                        return isset($data->total_retail_price)?num_format($data->total_retail_price):'';
                        
                       })
                       ->editColumn('total_wholesale_price', function($data){

                        /*return isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'';*/

                        $total_sum = 0;
                        //make a query if order is only save
                        if($data->is_confirm == 4)
                        {
                           $total_sum = $this->RepresentativeLeadsModel
                                             ->where('order_no',$data->order_no)
                                             ->where('maker_id','!=',0)                
                                             ->sum('total_wholesale_price');    


                          $total_sum = num_format($total_sum);                
                        }
                        else
                        {
                          $total_sum = isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'';
                        }
                        
                        return $total_sum;

                       })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   
                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];

                            if(isset($data->is_confirm) && $data->is_confirm == 4)
                            {
                               
                               $products_arr = get_rep_sales_leads_products($order_no);
                               return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);
                            }
                            else
                            {
                                $products_arr = get_lead_products($id,$order_no);

                               return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);
                            }
                        



                      /*
                            $products_arr = [];
                            $products_arr = get_lead_products($data->id,$data->order_no);

                            if(isset($products_arr) && count($products_arr)>0)
                            {
                                $products = '';

                                foreach ($products_arr as $key => $product) 
                                {
                                  
                                    $products .= '<tr>
                                                    <td>'.$product['product_details']['product_name'].'</td>
                                                    <td>'.$product['qty'].'</td>
                                                  </tr>';
                                  
                                }
                            }
                            else
                            {
                                $products = '<tr>
                                                    <td colspan=2>No Record Found</td>
                                                  </tr>';
                            }

                            return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
            
                                <td colspan="5">
                                    <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                        <thead>
                                          <tr>
                                            <th>Product Title</th>
                                            <th>Quantity</th>                                
                                          </tr>
                                        </thead>
                                        <tbody>'.$products.'</tbody>
                                      </table>
                                </td>';*/

                        })
                        ->editColumn('build_action_btn',function($data) use ($module_data)
                        {
                            //get unread messages count
                            $unread_message_count = get_lead_unread_messages_count($data->id,'representative');
                            
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            //check if user is online or not
                            $is_online = check_is_user_online($data->maker_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            $build_edit_action = $build_view_action = $build_chat_action = $build_reorder_action = '';

                            $view_href   =  $module_data['module_url'].'/view_lead_listing/'.base64_encode($data->id).'/'.base64_encode($data->order_no);
                            
                            $chat_href   = $module_data['module_url'].'/conversation/'.base64_encode($data->id);

                           $build_reorder_action = '';

                            if($data->is_confirm == 4 && $data->is_confirm != 3)
                            {
                              $build_edit_action = '<a href="'.$module_data['module_url'].'/find_products/'.base64_encode($data->order_no).'/edit"  data-size="small" title="Edit Product Details" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</i></a>';
                            }

                            if($data->is_confirm != 4)
                            {
                              $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';
                            }  

                            if($data->is_split_order != 1)
                            {
                              if($data->is_confirm == '1' || $data->is_confirm == 3)
                              {
                               /* $build_reorder_action = '<a href="javascript:void(0)" data-size="small" title="Reorder" class="btn btn-circle btn-success btn-outline show-tooltip"  onclick="reorder('.$data->id.');">Reorder</i></a>';*/
                              }
                            }
                            

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.' </a>';                                      

                            //return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_chat_action;

                            return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_reorder_action;
                        })
                        ->editColumn('created_at',function($data)
                          {
                            //return  format_date($data->created_at);
                            return  us_date_format($data->created_at);
                          })
                        ->editColumn('comission_status',function($data)
                          {
                            $comission_status = $data->sales_manager_commission_status;

                            $status = '-';

                            // if($comission_status == "1")
                            // {
                            //    $status = '<span class="label label-success">Pending</span>';
                            // }
                            // elseif($comission_status == "2")
                            // {
                            //    $status = '<span class="label label-success">Paid</span>';
                            // }
                            // elseif($comission_status == "3")
                            // {
                            //     $status = '<span class="label label-success">Failed</span>';
                            // }
                            // else
                            // {
                            //    $status = '<span class="label label-success">Pending</span>';
                            // }


                           /* if($comission_status == "1")
                            {
                               $status = '<span class="label label-success">Paid</span>';
                            }
                            else if($comission_status == "0")
                            {
                                $status = '<span class="label label-success">Pending</span>';
                            }
                            else
                            {
                               $status = '<span class="label label-success">Pending</span>';
                            }*/

                            return $status;

                          })->make(true);

    $build_result = $json_result->getData();
    $build_result->total_amt = $total_amt;
    return response()->json($build_result);

  }


  //new function

 public function get_order_list($arr_search_column=false,$module_data=false,$sales_manager_id=false,$orderBy=false,$confirmed_flag=false,$pending_flag=false,$completed_flag=false,$approved_flag=false,$net_30_completed_orders=false,$net_30_pending_orders=false)
  {
    
      $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $role_table        =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table        =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $representative_leads            =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $maker_table           = $this->MakerModel->getTable();
      $prefix_maker_table    = DB::getTablePrefix().$maker_table;

      $transaction_mapping        = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $stripe_transaction        = $this->StripeTransactionModel->getTable();
      $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

   
      $retailer_rep_mapping_tbl        = $this->RetailerRepresentativeMappingModel->getTable();
      $prefix_retailer_rep_mapping_tnl = DB::getTablePrefix().$retailer_rep_mapping_tbl;
      

      $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_maker_table.'.company_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                              "CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name"
                               // "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name"
                              ))
             

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_user_table." AS RL","RL.id",'=',$prefix_representative_leads_tbl.'.retailer_id')
                     
                           ->join($retailer_rep_mapping_tbl." AS REP_MAP1",'REP_MAP1.sales_manager_id','=',$prefix_representative_leads_tbl.".sales_manager_id")

                           ->join($retailer_rep_mapping_tbl." AS REP_MAP2","REP_MAP2.retailer_id","=",$prefix_representative_leads_tbl.'.retailer_id')
                      
                           ->join($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')
                        
                           // ->orderBy($prefix_representative_leads_tbl.'.created_at',"DESC")
                           
                           //->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.is_confirm','!=',0)

                           ->where($representative_leads.'.is_confirm','!=',4)

                           ->where($representative_leads.'.maker_id','!=',0)

                           ->where($representative_leads.'.sales_manager_id','!=',0)
                       
                           ->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)

                           ->where('REP_MAP2.sales_manager_id','!=',0);

          
                          if(isset($confirmed_flag) && $confirmed_flag==1)
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                          }

                          if(isset($pending_flag) && $pending_flag==1)
                          {  
                             
                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           ->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3')
                                           /*->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');*/

                                          ->where(function($q) use($prefix_representative_leads_tbl){
                                                return $q->orwhere($prefix_representative_leads_tbl.'.payment_term','!=','Net30')
                                                         ->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30 - Online/Credit')
                                                         ->orwhereNULL('payment_term');
                                            });
                                          
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);

                            });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                                         
                              });


                            $lead_obj = $lead_obj->where(function($q)use($prefix_representative_leads_tbl){

                                return $q->where($prefix_representative_leads_tbl.'.is_confirm','!=','3');
                                         
                                         
                              });
                          
                            
                          }  

                          if(isset($completed_flag) && $completed_flag==1)
                          {
                              $lead_obj = $lead_obj
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)      
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)
                                ->where($prefix_representative_leads_tbl.'.payment_term','!=','Net30');
                          }

                          if(isset($net_30_pending_orders) && $net_30_pending_orders==1)
                          {


                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           ->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3')
                                           ->where($prefix_representative_leads_tbl.'.payment_term','=','Net30');;
                            });

                             $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);
                            });

                            $lead_obj = $lead_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              });                             
                               
                          }

                          if(isset($net_30_completed_orders) && $net_30_completed_orders==1)
                          {
                              $lead_obj = $lead_obj
                                ->where($prefix_representative_leads_tbl.'.payment_term','=','Net30')
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)      
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1);
                          }

                          if(isset($approved_flag) && $approved_flag==1)
                          {
                             $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                                            
                                  ->where($prefix_representative_leads_tbl.'.order_cancel_status','!=',2);
                          }



       $rep_sales_order_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_maker_table.'.company_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                              "CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name"
                               
                              ))
             

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_leads_tbl.'.maker_id')

                           ->leftjoin($prefix_user_table." AS RL","RL.id",'=',$prefix_representative_leads_tbl.'.retailer_id')
                     
                           ->join($retailer_rep_mapping_tbl." AS REP_MAP1",'REP_MAP1.sales_manager_id','=',$prefix_representative_leads_tbl.".sales_manager_id")

                           ->join($retailer_rep_mapping_tbl." AS REP_MAP2","REP_MAP2.retailer_id","=",$prefix_representative_leads_tbl.'.retailer_id')
                      
                           ->join($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')
                        
                          // ->orderBy($prefix_representative_leads_tbl.'.created_at',"DESC")
                           
                           ->groupBy($prefix_representative_leads_tbl.'.order_no')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)
 
                           ->where($representative_leads.'.is_confirm','=',4)

                           ->where($representative_leads.'.maker_id','!=',0)

                           ->where($representative_leads.'.sales_manager_id','!=',0)
                       
                           ->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)

                           ->where('REP_MAP2.sales_manager_id','!=',0);

          
                          if(isset($confirmed_flag) && $confirmed_flag==1)
                          {
                              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                          }

                          if(isset($pending_flag) && $pending_flag==1)
                          {

                                $rep_sales_order_obj = $rep_sales_order_obj->where($representative_leads.'.is_split_order','=','0')
                                  ->where($representative_leads.'.is_payment_status','=','0')
                                  ->where($representative_leads.'.is_confirm','!=',0)
                                  ->where($representative_leads.'.is_confirm','!=',4)
                                  
                                  ->where(function($q) use($representative_leads){
                                    return $q->orwhere($representative_leads.'.ship_status','=','0')
                                             ->orwhere($representative_leads.'.ship_status','=','1');
                                  })

                                  ->where(function($q) use($representative_leads){
                                    return $q->where($representative_leads.'.payment_term','!=','Net30')
                                             ->orwhere($representative_leads.'.payment_term','!=','Net30 - Online/Credit')
                                             ->orwhereNULL($representative_leads.'.payment_term');
                                  });


                                $rep_sales_order_obj = $rep_sales_order_obj->where(function($q)use($prefix_representative_leads_tbl){
                              
                                return $q->where($prefix_representative_leads_tbl.'.is_confirm','!=','3');
                                         
                                         
                              });   
                          }  

                          if(isset($completed_flag) && $completed_flag==1)
                          {
                              $rep_sales_order_obj = $rep_sales_order_obj
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)      
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1)
                                ->where(function($q){
                                    return $q->where('payment_term','!=','Net30')
                                             ->orwhere('payment_term','!=','Net30 - Online/Credit');
                                  });
                          }

                          if(isset($net_30_pending_orders) && $net_30_pending_orders==1)
                          {


                            $rep_sales_order_obj = $rep_sales_order_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                               // return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                         //   ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                              return $query->where('is_split_order','=','0')
                                           ->where('ship_status','=',0)
                                           ->where('order_cancel_status','!=',2)
                                           ->where($prefix_representative_leads_tbl.'.is_confirm','!=','3')
                                           ->where($prefix_representative_leads_tbl.'.payment_term','=','Net30');;
                            });

                             $rep_sales_order_obj = $rep_sales_order_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                             ->orWhere($prefix_representative_leads_tbl.'.is_confirm','!=',0);
                            });

                            $rep_sales_order_obj = $rep_sales_order_obj->where(function($q){
                                return $q->orwhere('is_payment_status','=','0')
                                         ->orwhere('is_payment_status','=','1');
                              });                             
                               
                          }

                          if(isset($net_30_completed_orders) && $net_30_completed_orders==1)
                          {
                              $rep_sales_order_obj = $rep_sales_order_obj
                                ->where($prefix_representative_leads_tbl.'.payment_term','=','Net30')
                                ->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                //->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                ->where($prefix_representative_leads_tbl.'.is_payment_status','=',1)      
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1);
                          }

                          if(isset($approved_flag) && $approved_flag==1)
                          {
                             $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                                                            
                                  ->where($prefix_representative_leads_tbl.'.order_cancel_status','!=',2);
                          }
                   

    
                          
      // ---------------- Filtering Logic ----------------------------------

      if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {
          $search_term         = $arr_search_column['q_order_no'];
          $lead_obj            = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');

          $rep_sales_order_obj = $rep_sales_order_obj->having('order_no','LIKE', '%'.$search_term.'%');

      } 
      if(isset($arr_search_column['q_lead_date']) && $arr_search_column['q_lead_date']!="")
      {
          $search_term       = $arr_search_column['q_lead_date'];
          $date              = DateTime::createFromFormat('m-d-Y',$search_term);
          $date              = $date->format('Y-m-d');
          // $search_term      = date('Y-m-d',strtotime($search_term));
          $lead_obj = $lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');

          $rep_sales_order_obj = $rep_sales_order_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
      } 

      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term         = $arr_search_column['q_customer_name'];
          $lead_obj            =  $lead_obj->having('store_name','LIKE', '%'.$search_term.'%');

          $rep_sales_order_obj = $rep_sales_order_obj->having('store_name','LIKE', '%'.$search_term.'%');
      }

      
      if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
      {
          $search_term          = $arr_search_column['q_representative_name'];

          $lead_obj             =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');

          $rep_sales_order_obj  = $rep_sales_order_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
      }


      if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
      {

          $search_term      = $arr_search_column['q_maker_name'];
          $lead_obj         = $lead_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%')
                                       ->where($representative_leads.'.is_confirm','!=',4);

          $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%')
                                ->where($representative_leads.'.is_confirm','!=',4);


      }

      if(isset($arr_search_column['q_lead_status']) && $arr_search_column['q_lead_status']!="")
      {  

          $search_term = $arr_search_column['q_lead_status'];

          if ($search_term == "2")
          {

          
                $lead_obj = $lead_obj->where(function($query)use($search_term,$prefix_representative_leads_tbl)
                {
                    return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',$search_term)
                                ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                });


                $rep_sales_order_obj = $rep_sales_order_obj->where(function($query)use($search_term,$prefix_representative_leads_tbl)
                {
                    return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',$search_term)
                                ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                });


          }
          else
          {
           
            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

            $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

          }   

      }


      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {  
          $search_term = $arr_search_column['q_payment_status'];

          if($search_term == 1)
          {
              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',0);

              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',0);
          }

          elseif ($search_term == 2) 
          {
              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',1);

              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',1);
          }
          else
          {
              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',2);

              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_payment_status','=',2);
          } 
     
      }  



/*
      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {  
        $search_term = $arr_search_column['q_payment_status'];

        if($search_term == 1)
        {
          $lead_obj = $lead_obj->whereNotExists(function($query) use ($prefix_transaction_mapping,$representative_leads)
          {
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
          });


          $rep_sales_order_obj = $rep_sales_order_obj->whereNotExists(function($query) use ($prefix_transaction_mapping,$representative_leads)
          {
              $query->select(\DB::raw("
                      transaction_mapping.order_id,
                      transaction_mapping.order_no
                  FROM
                      `transaction_mapping`
                  WHERE
                      `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
              "));
          });


           
        }
        else
        {
           
            $lead_obj = $lead_obj->whereExists(function($query) use ($prefix_transaction_mapping,$representative_leads,$search_term)
            {
                $query->select(\DB::raw("
                        transaction_mapping.order_id,
                        transaction_mapping.order_no
                    FROM
                        `transaction_mapping`
                    WHERE
                        `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                          AND transaction_mapping.transaction_status = ".$search_term."
                "));
            });

           
            $rep_sales_order_obj = $rep_sales_order_obj->whereExists(function($query) use ($prefix_transaction_mapping,$representative_leads,$search_term)
            {
                $query->select(\DB::raw("
                        transaction_mapping.order_id,
                        transaction_mapping.order_no
                    FROM
                        `transaction_mapping`
                    WHERE
                        `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                          AND transaction_mapping.transaction_status = ".$search_term."
                "));
            });



        }


      }*/

      if(isset($arr_search_column['q_shipping_status']) && $arr_search_column['q_shipping_status']!="")
      {  
        $search_term  = $arr_search_column['q_shipping_status'];

        $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term)
                                 ->where($representative_leads.'.is_confirm','!=',4);

        $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term)
                              ->where($representative_leads.'.is_confirm','!=',4);
      }

     

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {

         $search_term             = $arr_search_column['q_total_costing_wholesale'];

         $search_term             = intval($search_term);

         $lead_obj                =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%')
                                     ->where($representative_leads.'.is_confirm','!=',4);

         $rep_sales_order_obj     =  $rep_sales_order_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%')
                                     ->where($representative_leads.'.is_confirm','!=',4);
      }
 
      if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="" && isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
      {
          $search_term_from_date  = $arr_search_column['q_from_date'];
          $search_term_to_date    = $arr_search_column['q_to_date'];
          $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
          $from_date              = $from_date->format('Y-m-d');
          $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
          $to_date                = $to_date->format('Y-m-d');
      
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);

          $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);

          $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);


      }

      //old

       /*   if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']!="" && $arr_search_column['q_comission_status']!=1)
      {  
         $search_term  = $arr_search_column['q_comission_status'];
         
         $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=', $search_term);

         $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.rep_commission_status','=', $search_term);
          
      }*/



     
      if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']!="")
      {  
          $search_term  = $arr_search_column['q_comission_status'];
        
          if($search_term == "1")
          {  
              $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','0');


              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','0');

          }
          else if($search_term == "2")
          { 
              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','1');


              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','1');

          }
          else
          { 
              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','2');

              $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.sales_manager_commission_status','=','2');

          }
         


      }








    // Condition added by Harshada on date 28 Aug 2020 
      // For showing pending records after search filter
   /*   if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']!="" && $arr_search_column['q_comission_status']!=1)
      {  
        $search_term  = $arr_search_column['q_comission_status'];
        //$rep_sales_order_obj     = $rep_sales_order_obj->where($prefix_stripe_transaction.'.status','=', $search_term);
      }*/
      

      $lead_obj = $rep_sales_order_obj->union($lead_obj);
     
      $lead_obj = $lead_obj->orderBy('id','DESC');
    
      return $lead_obj;

  }


  //old function
    
  /*public function get_order_list($arr_search_column=false,$module_data=false,$sales_manager_id=false,$orderBy=false,$confirmed_flag=false,$pending_flag=false,$completed_flag=false,$approved_flag=false)
  {
      $user = \Sentinel::check();
      if($user)
      {
        $loggedInUserId = $user->id;
      }

      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $user_maker_table =  $this->UserModel->getTable();
      $prefix_user_maker_table = DB::getTablePrefix().$user_maker_table;        

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $representative_leads =  $this->RepresentativeLeadsModel->getTable();
      $prefix_representative_leads_tbl = DB::getTablePrefix().$representative_leads;

      $transaction_mapping = $this->TransactionMappingModel->getTable();
      $prefix_transaction_mapping = DB::getTablePrefix().$transaction_mapping;

      $retailer_table        = $this->RetailerModel->getTable();
      $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $maker_table           = $this->MakerModel->getTable();
      $prefix_maker_table    = DB::getTablePrefix().$maker_table;

      $rep_table = $this->RepresentativeModel->getTable();
      $prefix_rep_table = DB::getTablePrefix().$rep_table;

      $stripe_transaction        = $this->StripeTransactionModel->getTable();
      $prefix_stripe_transaction = DB::getTablePrefix().$stripe_transaction;

      $representative_product_leads            =  $this->RepresentativeProductLeadsModel->getTable();
      $prefix_representative_product_leads_tbl = DB::getTablePrefix().$representative_product_leads;

      $lead_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.
                              $prefix_maker_table.'.company_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name,".
                            
                               $prefix_stripe_transaction.'.status as comission_status'
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')
                       

                          ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')



                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })


                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           //Show stripe status...
                           ->leftjoin($prefix_stripe_transaction,$prefix_stripe_transaction.'.quote_id','=',$representative_leads.'.id')

                           //->groupBy($prefix_representative_leads_tbl.'.id')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.is_confirm','!=',0)
                           ->where($representative_leads.'.is_confirm','!=',4)

                           ->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)

                           ->where($representative_leads.'.maker_id','!=',0);

                           if(isset($confirmed_flag) && $confirmed_flag==1)
                           {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                           } 

                           if(isset($pending_flag) && $pending_flag==1)
                          {  
                             
                            $lead_obj = $lead_obj->where(function($query)use($prefix_representative_leads_tbl)
                            {
                                return $query->where($prefix_representative_leads_tbl.'.is_confirm','=',2)
                                            ->orWhere($prefix_representative_leads_tbl.'.is_confirm','=',0);

                            })
                            ->whereNotExists(function($query) use ($prefix_transaction_mapping,$representative_leads)
                              {
                                  $query->select(\DB::raw("
                                          transaction_mapping.order_id,
                                          transaction_mapping.order_no
                                      FROM
                                          `transaction_mapping`
                                      WHERE
                                          `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                                  "));
                              });

                            
                          }  

                          if(isset($completed_flag) && $completed_flag==1)
                          {
                              $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1)
                               
                                ->where($prefix_transaction_mapping.'.transaction_status','=',2)
                                                    
                                ->where($prefix_representative_leads_tbl.'.ship_status','=',1)
                                                    
                                ->where($prefix_representative_leads_tbl.'.maker_confirmation','=',1);
                          }
                           


    $rep_sales_order_obj = DB::table($representative_leads)
                            ->select(DB::raw($prefix_representative_leads_tbl.".*,"."RL.id as retailer_user_id,".
                              $prefix_transaction_mapping.'.transaction_status,'.
                              $prefix_transaction_mapping.'.order_id,'.
                              $prefix_maker_table.'.company_name,'.
                              $prefix_retailer_table.'.store_name,'.

                              "CONCAT(RL.first_name,' ',RL.last_name) as retailer_user_name,".
                               " CONCAT(RM.first_name,' ',RM.last_name) as maker_user_name,".
                               "CONCAT(RR.first_name,' ',RR.last_name) as representative_user_name,".
                            
                               $prefix_stripe_transaction.'.status as comission_status'
                            )) 

                           ->leftJoin($prefix_user_table." AS RL", 'RL.id','=',$prefix_representative_leads_tbl.'.retailer_id')
                            
                         

                           ->leftJoin($prefix_representative_product_leads_tbl,$prefix_representative_product_leads_tbl.'.maker_id','=',$representative_leads.'.maker_id')

                           ->leftJoin($prefix_user_table, $prefix_user_table.'.id','=',$prefix_representative_leads_tbl.'.maker_id')
                            
                           ->leftJoin($prefix_user_table." AS RR", 'RR.id','=',$prefix_representative_leads_tbl.'.representative_id')

                           ->leftJoin($prefix_user_table." AS RM", 'RM.id','=',$prefix_representative_product_leads_tbl.'.maker_id')

                          
                            ->leftjoin($prefix_transaction_mapping,function($join) use($prefix_representative_leads_tbl,$prefix_transaction_mapping){

                                $join->on($prefix_representative_leads_tbl.'.id','=',$prefix_transaction_mapping.'.order_id')
                                ->on($prefix_representative_leads_tbl.'.order_no','=',$prefix_transaction_mapping.'.order_no');

                            })


                           ->leftjoin($prefix_retailer_table,$prefix_retailer_table.'.user_id','=',$representative_leads.'.retailer_id')

                           ->leftjoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$representative_leads.'.maker_id')

                           //Show stripe status...
                           ->leftjoin($prefix_stripe_transaction,$prefix_stripe_transaction.'.quote_id','=',$representative_leads.'.id')

                           ->groupBy($representative_leads.'.order_no')

                           ->where($representative_leads.'.total_wholesale_price','>','0')

                           ->where($representative_leads.'.order_cancel_status','!=',2)

                           ->where($representative_leads.'.is_confirm','=',4)

                           ->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)

                           ->where($representative_leads.'.maker_id','!=',0);  

                          if(isset($confirmed_flag) && $confirmed_flag==1)
                          {
                            $rep_sales_order_obj = $rep_sales_order_obj->where($prefix_representative_leads_tbl.'.is_confirm','=',1);
                          } 
                          // Condition added by Harshada on date 28 Aug 2020 
                          // For showing pending records after search filter
                          if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']==1)
                          {  
                            $search_term  = $arr_search_column['q_comission_status'];
                            $rep_sales_order_obj     = $rep_sales_order_obj->whereNull($prefix_stripe_transaction.'.status');
                            $rep_sales_order_obj     = $rep_sales_order_obj->whereNotIn($prefix_stripe_transaction.'.status', [2,3]);
                          }
                  

                           
                          
       //---------------- Filtering Logic ----------------------------------  

     if(isset($arr_search_column['q_order_no']) && $arr_search_column['q_order_no']!="")
      {
          $search_term      = $arr_search_column['q_order_no'];
          $lead_obj = $lead_obj->having('order_no','LIKE', '%'.$search_term.'%');
      } 
      if(isset($arr_search_column['q_lead_date']) && $arr_search_column['q_lead_date']!="")
      {
          $search_term      = $arr_search_column['q_lead_date'];
          $date             = DateTime::createFromFormat('m-d-Y',$search_term);
          $date             = $date->format('Y-m-d');
          //$search_term    = date('Y-m-d',strtotime($search_term));

          $lead_obj = $lead_obj->where($representative_leads.'.created_at','LIKE', '%'.$date.'%');
      } 

      if(isset($arr_search_column['q_customer_name']) && $arr_search_column['q_customer_name']!="")
      {
          $search_term      = $arr_search_column['q_customer_name'];
          $lead_obj =  $lead_obj->having('store_name','LIKE', '%'.$search_term.'%');
      }

      
      if(isset($arr_search_column['q_representative_name']) && $arr_search_column['q_representative_name']!="")
      {
          $search_term      = $arr_search_column['q_representative_name'];
          $lead_obj =  $lead_obj->having('representative_user_name','LIKE', '%'.$search_term.'%');
      }


      if(isset($arr_search_column['q_maker_name']) && $arr_search_column['q_maker_name']!="")
      {

          $search_term      = $arr_search_column['q_maker_name'];
          $lead_obj =  $lead_obj->where($prefix_maker_table.'.company_name','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_lead_status']) && $arr_search_column['q_lead_status']!="")
      {
        
          $search_term      = $arr_search_column['q_lead_status'];
          // if ($search_term == "2") {
       

          //   $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term)->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id)->orwhere($prefix_representative_leads_tbl.'.is_confirm','=', 0)->where($prefix_representative_leads_tbl.'.sales_manager_id','=',$sales_manager_id);
          // }
          // else{
           
            $lead_obj = $lead_obj->where($prefix_representative_leads_tbl.'.is_confirm','=', $search_term);

          // }
         

      }

      if(isset($arr_search_column['q_shipping_status']) && $arr_search_column['q_shipping_status']!="")
      {  
          $search_term  = $arr_search_column['q_shipping_status'];
          $lead_obj     = $lead_obj->where($prefix_representative_leads_tbl.'.ship_status','=', $search_term);
      }



      if(isset($arr_search_column['q_payment_status']) && $arr_search_column['q_payment_status']!="")
      {
          $search_term  = $arr_search_column['q_payment_status'];
          // $lead_obj     = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=', $search_term);

          if($search_term == 1)
          {
             
              $lead_obj = $lead_obj->whereNotExists(function($query) use ($prefix_transaction_mapping,$prefix_representative_leads_tbl)
                    {

                        $query->select(\DB::raw("
                                transaction_mapping.order_id,
                                transaction_mapping.order_no
                            FROM
                                `transaction_mapping`
                            WHERE
                                `transaction_mapping`.`order_no` = representative_leads.order_no AND `transaction_mapping`.`order_id` = representative_leads.id
                        "));
                    });                         
                             
          }
          else
          {
             $lead_obj = $lead_obj->where($prefix_transaction_mapping.'.transaction_status','=',$search_term);
          }
          
      }


      if(isset($arr_search_column['q_total_costing_retail']) && $arr_search_column['q_total_costing_retail']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_retail'];
          $lead_obj =  $lead_obj->having('total_retail_price','LIKE', '%'.$search_term.'%');
      }

      if(isset($arr_search_column['q_total_costing_wholesale']) && $arr_search_column['q_total_costing_wholesale']!="")
      {
          $search_term      = $arr_search_column['q_total_costing_wholesale'];
          $lead_obj =  $lead_obj->having('total_wholesale_price','LIKE', '%'.$search_term.'%');
      }



      if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="" && isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
      {
          $search_term_from_date  = $arr_search_column['q_from_date'];
          $search_term_to_date    = $arr_search_column['q_to_date'];
          $from_date              = DateTime::createFromFormat('m-d-Y',$search_term_from_date);
          $from_date              = $from_date->format('Y-m-d');
          $to_date                = DateTime::createFromFormat('m-d-Y',$search_term_to_date);
          $to_date                = $to_date->format('Y-m-d');
      
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
          $lead_obj   = $lead_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);

          $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '<=', $to_date);
          $rep_sales_order_obj   = $rep_sales_order_obj->whereDate($prefix_representative_leads_tbl.'.created_at', '>=', $from_date);


      }

      if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']!="" && $arr_search_column['q_comission_status']!=1)
      {  
        $search_term  = $arr_search_column['q_comission_status'];
        $lead_obj     = $lead_obj->where($prefix_stripe_transaction.'.status','=', $search_term);
      } else {

        // Condition added by Harshada on date 28 Aug 2020 
        // For showing pending records after search filter
        if(isset($arr_search_column['q_comission_status']) && $arr_search_column['q_comission_status']==1)
        {  
          $search_term  = $arr_search_column['q_comission_status'];
          $lead_obj     = $lead_obj->whereNull($prefix_stripe_transaction.'.status');
         // $lead_obj     = $lead_obj->whereNotIn($prefix_stripe_transaction.'.status', [2,3]);
        }
                  
      }


      $lead_obj = $rep_sales_order_obj->union($lead_obj);
      $lead_obj = $lead_obj->orderBy('id','DESC');
      //echo $lead_obj->toSql();die;
      return $lead_obj;
  }*/

  public function reps_leads(Request $request)
  { 
    $search_data = $request->input('column_filter');

    $module_data['module_url'] = $this->module_url_path;

    $user = Sentinel::check();
    $sales_manager_id = 0;

    if($user)
    {
      
      $sales_manager_id = $user->id;
            
    }    


    $data = $this->orderDataService->get_order_list($search_data,$module_data,$sales_manager_id,'reps');

    //Calculate total by Harshada on date 09 Sep 2020
     $total_amt = 0;        
     $total_amt =array_reduce($data->get()->toArray(), function(&$res, $item) {
          return $res + $item->total_wholesale_price;
      }, 0);


    $current_context = $this;
    
    $json_result     = \Datatables::of($data);
    
    $json_result     = $json_result->editColumn('enc_id',function($data)
                        {
                            return base64_encode($data->id);
                        })
                       ->editColumn('total_retail_price', function($data){

                        return isset($data->total_retail_price)?num_format($data->total_retail_price):'';
                        
                       })
                       ->editColumn('total_wholesale_price', function($data){

                        return isset($data->total_wholesale_price)?num_format($data->total_wholesale_price):'';

                       })
                       ->editColumn('representative_user_name', function($data){

                        return isset($data->representative_user_name)?$data->representative_user_name:'N/A';

                       })
                        ->editColumn('product_html',function($data) use ($current_context)
                        {   

                            $id       = isset($data->id)?$data->id:"";
                            $order_no = isset($data->order_no)?$data->order_no:"";

                            $products_arr = [];
                            $products_arr = get_lead_products($id,$order_no);

                            return $product_html = $this->GeneralService->order_rep_sles_products_for_list($id,$order_no,$products_arr);


                            /*$products_arr = [];
                            $products_arr = get_lead_products($data->id,$data->order_no);

                            if(isset($products_arr) && count($products_arr)>0)
                            {
                                $products = '';

                                foreach ($products_arr as $key => $product) 
                                {
                                  
                                    $products .= '<tr>
                                                    <td>'.$product['product_details']['product_name'].'</td>
                                                    <td>'.$product['qty'].'</td>
                                                  </tr>';
                                  
                                }
                            }
                            else
                            {
                                $products = '<tr>
                                                    <td colspan=2>No Record Found</td>
                                                  </tr>';
                            }

                            return '<a href="javascript:void(0)" class="pro-list-bg" data-tbl-id="tbl_'.$data->id.'" onclick="show_product_list($(this))">View Products<span> '.count($products_arr).'</span></a>
            
                                <td colspan="5">
                                    <table style="display:none;" id="tbl_'.$data->id.'" class="table table-bordered product-list">
                                        <thead>
                                          <tr>
                                            <th>Product Title</th>
                                            <th>Quantity</th>                                
                                          </tr>
                                        </thead>
                                        <tbody>'.$products.'</tbody>
                                      </table>
                                </td>';*/

                        })
                        ->editColumn('build_action_btn',function($data) use ($module_data)
                        {
                            //get unread messages count
                            $unread_message_count = get_lead_unread_messages_count($data->id,'representative');
                            
                            if($unread_message_count>0)
                            {
                                $unread_msg_count = '<span class="counts-ldt">'.$unread_message_count.'</span>';    
                            }
                            else
                            {
                                $unread_msg_count = '';
                            }

                            //check if user is online or not
                            $is_online = check_is_user_online($data->maker_id);

                            if($is_online ==true)
                            {
                              $online_status = '<span class="act-online"></span>';
                            }
                            else
                            {
                              $online_status = '<span class="act-offline"></span>';
                            }

                            $build_edit_action = $build_view_action = $build_chat_action = $build_reorder_action = '';

                            $view_href   =  $module_data['module_url'].'/view_details/'.base64_encode($data->id).'/'.base64_encode($data->order_no).'/1';
                            
                            $chat_href   = $module_data['module_url'].'/conversation/'.base64_encode($data->id);

                            $build_reorder_action = '';

                            if($data->is_confirm == '0')
                            {
                              $build_edit_action = '<a href="'.$module_data['module_url'].'/find_products/'.base64_encode($data->order_no).'/edit"  data-size="small" title="Edit Product Details" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</i></a>';
                            }

                            $build_view_action = '<a data-toggle="tooltip"  data-size="small" title="View Order Details" class="btn btn-circle btn-success btn-outline show-tooltip" href="'.$view_href.'">View</a>';


                         //for while we have hide this reorder button

                         /* if($data->is_split_order !=1)
                          {
                            if($data->is_confirm == '1')
                            {
                              $build_reorder_action = '<a href="javascript:void(0)" data-size="small" title="Reorder" class="btn btn-circle btn-success btn-outline show-tooltip"  onclick="reorder('.$data->id.');">Reorder</i></a>';
                            }
                          }  */

                            $build_chat_action = '<a data-toggle="tooltip"  data-size="small" title="View Chat" class="btn btn-outline btn-info btn-circle show-tooltip lead-chat-btn" href="'.$chat_href.'">Chat'.$unread_msg_count.' '.$online_status.' </a>';                                      

                            //return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_chat_action;

                            //return $build_action = $build_edit_action.' '.$build_view_action.' '.$build_reorder_action;

                            return $build_action = $build_edit_action.' '.$build_view_action;
                        })
                        ->editColumn('created_at',function($data)
                          {
                            //return  format_date($data->created_at);
                            return  us_date_format($data->created_at);
                          })->make(true);

    $build_result = $json_result->getData();
    $build_result->total_amt = $total_amt;
    return response()->json($build_result);

  }



  public function view_lead_listing($enc_lead_id,$order_no,$is_rep_sales_order = false)
  {
    
    $loggedInUserId = 0;
    $user = Sentinel::check();

    if($user)
    {
        $loggedInUserId = $user->id;
    }

    $leads_id = base64_decode($enc_lead_id);
    $order_no = base64_decode($order_no);
    
    $leads_arr = $split_order_arr = $main_split_order_no = [];
    $obj_data = $this->RepresentativeLeadsModel
                     ->with([ 'order_details.product_details.brand_details',
                              'transaction_mapping',
                              'retailer_user_details.retailer_details',
                              'representative_user_details'=>function($q2)
                             {
                                $q2->select('id','email','first_name','last_name');
                             },'order_details'=>function($q3) use ($leads_id)
                             {
                               $q3->where('representative_leads_id',$leads_id);
                             },'maker_details'=>function($q4)
                             {
                               $q4->select('company_name');
                             },'maker_data'=>function($q5)
                             {
                                $q5->select('id','email','first_name','last_name');
                             },'address_details','maker_details',
                             'stripe_transaction_detail','stripe_transaction_data'

                        ])
                        ->where('id',$leads_id)
                        ->where('order_no',$order_no)
                        ->first();

    // Get transaction mapping details
    $leads_arr["transaction_mapping_details"] = array();                    
    $transaction_mapping_details = $this->TransactionMappingModel->where('order_no',$order_no)->where('order_id',$leads_id)->first();                     
                        
    if($obj_data)
    {
        $leads_arr = $obj_data->toArray();
        if(!empty($transaction_mapping_details))
        {
          $transaction_mapping_details = $transaction_mapping_details->toArray();      
          $leads_arr["transaction_mapping_details"] = $transaction_mapping_details;  
        }
        
        if (isset($leads_arr['split_order_id']) && $leads_arr['split_order_id'] != '') {

              $main_split_order_no = $this->RepresentativeLeadsModel->where('id',$leads_arr['split_order_id'])->first();

            }
            elseif (isset($leads_arr['is_split_order']) && $leads_arr['is_split_order'] == '1') {

              $split_order_arr = $this->RepresentativeLeadsModel->where('split_order_id',$leads_arr['id'])->get()->toArray();

            }
    }                                           

    /*check count whether in a payment done or fail*/

    $count = $this->TransactionMappingModel->where('order_no',$order_no)->count();

    $leads_arr_id         = isset($leads_arr['id'])?$leads_arr['id']:0;
    $leads_arr_order_no   = isset($leads_arr['order_no'])?$leads_arr['order_no']:'';

    $tracking_details = [];
    $tracking_no = 0;

    if($leads_arr_id!=0 && $leads_arr_order_no!='')
    {
      $tracking_details = $this->HelperService->getTrackingDetails($leads_arr_id,$leads_arr_order_no);
      $tracking_no = isset($tracking_details['tracking_no'])?$tracking_details['tracking_no']:0;
    }


    /*get order calculation data from helper*/
    if(isset($leads_arr['order_no']) && !empty($leads_arr['order_no']) &&
              isset($leads_arr['maker_id']) && !empty($leads_arr['maker_id']))
    {
        $ordNo = base64_encode($leads_arr['order_no']);
        $vendorId = base64_encode($leads_arr['maker_id']);

        $orderCalculationData = $this->HelperService->get_order_calculation_data($ordNo,$vendorId,$userSegment='sales_manager');
    }   

    $this->arr_view_data['leads_arr']       = $leads_arr;
    $this->arr_view_data['count']           = $count;
    $this->arr_view_data['module_title']    = $this->module_title;
    $this->arr_view_data['page_title']      = 'Order Details';
    $this->arr_view_data['module_url_path'] = $this->module_url_path;
    $this->arr_view_data['split_order_arr'] = $split_order_arr;
    $this->arr_view_data['main_split_order_no']     = $main_split_order_no;
    $this->arr_view_data['tracking_details'] = $tracking_details;
    $this->arr_view_data['tracking_no']      = $tracking_no;  
    $this->arr_view_data['is_rep_sales_order']     = $is_rep_sales_order;  

    $this->arr_view_data['orderCalculationData']      = $orderCalculationData;  

    return view($this->module_view_folder.'.leads_listing_view',$this->arr_view_data);
  }


  public function save_address(Request $request)
  {  
      $form_data = $request->all();
   
      $arr_rules = [
                   
                    'bill_mobile_no'      => 'required',
                    'bill_state'          => 'required',
                    'bill_email'          => 'required',
                    'bill_city'           => 'required',
                    'bill_zip'            => 'required',
                    'ship_mobile_no'      => 'required',
                    'ship_state'          => 'required',
                    'ship_email'          => 'required',
                    'ship_city'           => 'required',
                    'ship_zip_code'       => 'required',
                    'bill_street_address' => 'required',
                    'ship_street_address' => 'required'                                
                  ];

      $validator = Validator::make($request->all(),$arr_rules); 

      if($validator->fails())
      {
         $response['status']      = 'warning';
         $response['description'] = 'Something went wrong, please check all fields.';
         return response()->json($response);
      }

      $save_address = $this->orderDataService->store_order_address($request);
  
      $response['status']      = "success";
      $response['description'] = "Order has been confirmed.";
      $response['url']         = $this->module_url_path;

      return response()->json($response); 
  } 



  public function net_30_payment($order_id)
  {  
      $data     = [];
      $order_id = base64_decode($order_id);
     
      $next_due_date = $payment_term = '';
      $next_due_date = date('Y-m-d H:i:s', strtotime("+30 days"));
 
      $data['payment_term']     = 'Net30';
      $data['payment_due_date'] = $next_due_date;

      $result = $this->RepresentativeLeadsModel->where('id',$order_id)->update($data);

      if($result)
      {
         Flash::success('Net30 payment has been done.');
         return redirect()->back();
      }
      else
      {
        Flash::error('Error occurred while doing Net30 payment.');
      }
  }



 /************************Notification Event START**************************/

    public function save_notification($ARR_DATA = [])
    {  
        if(isset($ARR_DATA) && sizeof($ARR_DATA)>0)
        {
            $ARR_EVENT_DATA                 = [];
            $ARR_EVENT_DATA['from_user_id'] = $ARR_DATA['from_user_id'];
            $ARR_EVENT_DATA['to_user_id']   = $ARR_DATA['to_user_id'];
            $ARR_EVENT_DATA['description']  = $ARR_DATA['description'];
            $ARR_EVENT_DATA['title']        = $ARR_DATA['title'];
            $ARR_EVENT_DATA['type']         = $ARR_DATA['type'];
            $ARR_EVENT_DATA['status']         = isset($ARR_DATA['status'])?$ARR_DATA['status']:'0'; 

            event(new NotificationEvent($ARR_EVENT_DATA));

            return true;
        }
        return false;
    }

    /************************Notification Event END  **************************/

    public function product_discount($product_id, $quantity)
    {
      $arr_product = $arr_discount = [];
      $total_wholesale_price = 0;
      $obj_product = $this->ProductsModel->where('id',$product_id)->first();
      if ($obj_product) {
        
        $arr_product_details = $obj_product->toArray();
      }
      
      $total_price = $quantity * $arr_product_details['unit_wholsale_price'];

      $total_wholesale_price = isset($total_price)?$total_price:0;


      if($arr_product_details['shipping_type']==2) 
      {
          if($total_wholesale_price>=$arr_product_details['minimum_amount_off'])
            {
               
                $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

                if(is_numeric($shipping_charges) && is_numeric($arr_product_details['off_type_amount']))
                {

                    $discount_amount =  $shipping_charges * $arr_product_details['off_type_amount']/ 100;

                    //$shipping_charges = $shipping_charges-$discount_amount;

                    $arr_discount['shipping_discount'] = $discount_amount;
                    $arr_discount['shipping_charges'] = $shipping_charges;
                }
                else
                {
                    $arr_discount['shipping_discount'] = 0;
                    $arr_discount['shipping_charges'] = 0;
                }
                
            }
            else
            {

              $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
              $arr_discount['shipping_charges'] = $shipping_charges;
              $arr_discount['shipping_discount'] = 0;

            }
        }

        if($arr_product_details['shipping_type']==1) 
        { 
            if($total_wholesale_price<$arr_product_details['minimum_amount_off'])
            {
                $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
                   
                $arr_discount['shipping_charges'] = isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';
                $arr_discount['shipping_discount'] =  0;

            }
            else
            {
                   
                $arr_discount['shipping_discount'] = 0;
                $arr_discount['shipping_charges'] = 0;
                //dd($cart_product_arr[$key]['shipping_charges']);

            }
        }

        if($arr_product_details['shipping_type']==3) 
        {
            if($total_wholesale_price>=$arr_product_details['minimum_amount_off'])
            {
          
            
              $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

             // $shipping_charges = $shipping_charges;

              $arr_discount['shipping_discount'] = $arr_product_details['off_type_amount'];

             // $arr_discount['shipping_charges'] = $shipping_charges-$arr_discount['shipping_discount'];

              $arr_discount['shipping_charges'] = $shipping_charges;

            }
            elseif($total_wholesale_price<$arr_product_details['minimum_amount_off'])
            {
          

              $shipping_charges =  isset($arr_product_details['shipping_charges'])?$arr_product_details['shipping_charges']:'';

              $arr_discount['shipping_charges']  = $shipping_charges;

              $arr_discount['shipping_discount'] = 0;
            }
          

        }
        if($arr_product_details['prodduct_dis_type']==1)
    { 

        if($total_wholesale_price>=$arr_product_details['product_dis_min_amt'])
            {
                $pro_discount =  $total_wholesale_price * $arr_product_details['product_discount']/ 100;
            $arr_discount['product_discount'] = isset($pro_discount)?$pro_discount:0;

            }
            else
            {                                 
                $arr_discount['product_discount'] = 0;
            }

    }    
      if($arr_product_details['prodduct_dis_type']==2)
      {
        if($total_wholesale_price>=$arr_product_details['product_dis_min_amt'])
            {
                $pro_discount = $arr_product_details['product_discount'];
            $arr_discount['product_discount'] = isset($pro_discount)?$pro_discount:0;

            }
            else
            {                                 
                $arr_discount['product_discount'] = 0;
            }
        
    
      }

        return $arr_discount;
    } 

}