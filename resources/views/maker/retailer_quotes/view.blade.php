@extends('maker.layout.master')                
@section('main_content')

<style>
.moretext{
   overflow: hidden;
}
a.readmore-toggle {
    color: #3a72e2;
    font-weight: 600;
    text-decoration: underline;
}
.main-nm-retailer-right {
    margin-left: 110px;
}
.main-nm-retailer {    margin-top: 0px;
    position: relative; float: right;
}
.main-nm-retailer-left {
    position: absolute; font-weight: 600;
    left: 0;
}
.orderstatus-td{display: inline-block;     color: #404040;
    font-weight: 500; font-size: 14px;}

/*.modal{position:absolute !important;}*/
.order-title-tracking{    font-size: 20px;
    font-weight: 600; margin-bottom: 20px;
    color: #333;}
/*#orderTrackingModal .modal-body{ padding: 135px 25px 25px; }*/

.row{
  padding-bottom: 20px;
}

  #mceu_15{
    display: none !important;
 }
 #reject_reason {
  display: block;
 }
</style>

<?php
  $ordNo = base64_encode($enquiry_arr['order_no']);
  $vendorId = base64_encode($enquiry_arr['maker_id']);

?>

<div id="page-wrapper">
  <div class="container-fluid retailer_orders_view_page">
    <div class="row bg-title">
      <div class="col-sm-12 top-bg-title">
        <h4 class="page-title">{{$page_title or ''}}</h4>
        <div class="right">
        <ol class="breadcrumb">
          <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
          <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
          <li class="active">{{$page_title or ''}}</li>
        </ol>
      </div>
      </div>
    </div>
 
    <div class="row">
      <div class="col-md-12">
        @include('admin.layout._operation_status')
        <div class="white-box small-space-nw">
          @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)

          <div class="row">
            <div class="col-sm-12">
              <a href="{{url('/')}}/vendor/retailer_orders/view/{{base64_encode($main_split_order_no['id'])}}"><button class="btn btn-inverse pull-right">This order is split from : {{isset($main_split_order_no['order_no'])?$main_split_order_no['order_no']:''}}</button></a>
            </div>
          </div>

          @endif

          @php
                      $confirm_button = $ship_button = $payment_button = '';
                      $maker_confirmation = isset($enquiry_arr['maker_confirmation'])?$enquiry_arr['maker_confirmation']:'';
                      $ship_status = isset($enquiry_arr['ship_status'])?$enquiry_arr['ship_status']:'';
                      $is_payment_status = isset($enquiry_arr['is_payment_status'])?$enquiry_arr['is_payment_status']:'';
                     
                     /* if($maker_confirmation == 1)
                      {
                        $confirm_button = 'hidden';
                        $ship_button = '';
                        $payment_button = "";
                      }
                      else
                      {
                        $ship_button = 'disabled';
                        $payment_button = 'disabled';
                      }
                      if($ship_status == 1)
                      {
                        $confirm_button = 'hidden';
                        $ship_button = 'hidden';
                        $payment_button = 'hidden';
                      }
                      if($is_payment_status == 0)
                      {
                        $confirm_button = 'hidden';
                        $ship_button = 'disabled';
                        $payment_button = '';
                      } else {
                        $confirm_button = 'hidden';
                        $ship_button = '';
                        $payment_button = 'hidden';
                      }*/

                    $confirm_button = 'hidden';
                    $ship_button_new = 'hidden';
                    $payment_button = "hidden";


                    /* For order confirm or not */
                    if($maker_confirmation == 0)
                    {
                      $confirm_button = '';
                      $ship_button_new = 'disabled';
                      $payment_button = "disabled";
                    }
                    if($maker_confirmation == 1 && $is_payment_status == 0 && $ship_status == 0)
                    {
                      $confirm_button = 'hidden';
                      $ship_button_new = 'disabled';
                      $payment_button = "";
                    }       

                    /*For order payment received or not  */                     
                    if($maker_confirmation == 1 && $is_payment_status == 0)
                    {
                      $confirm_button = 'hidden';
                      $ship_button_new = 'disabled';
                      $payment_button = '';
                    }

                    if($maker_confirmation == 1 && $is_payment_status == 1)
                    {
                      $confirm_button = 'hidden';
                      $ship_button_new = '';
                      $payment_button = 'hidden';
                    }

                    /* For order shipped or not */

                    if($maker_confirmation == 1 && $is_payment_status == 1 && $ship_status == 0)
                    {
                      $confirm_button = 'hidden';
                      $ship_button_new = '';
                      $payment_button = 'hidden';
                    } 

                    if($maker_confirmation == 1 && $is_payment_status == 1 && $ship_status == 1)
                    {
                      $confirm_button = 'hidden';
                      $ship_button_new = 'hidden';
                      $payment_button = 'hidden';
                    } 
                      
                    @endphp
            <div class="row"> 
              <div class="col-sm-12 col-md-12 col-lg-6">
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Order No.</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                  </div>
                </div>
                 
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Order Date</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</span>
                  </div>
                </div>
                
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Term</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
                      <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                    @else
                       <span class="label label-success">{{isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] != "" ?$enquiry_arr['payment_term']:'N/A' }}</span>
                    @endif
    
                  </div>
                </div>

                
              
                @php 
                  $shipping_addr = $billing_addr = ""; 

                  if(isset($enquiry_arr['shipping_addr']))
                  { 
                      $shipping_addr = $enquiry_arr['shipping_addr'];

                      if(isset($enquiry_arr['shipping_addr_zip_code'])) 
                      {
                        $shipping_addr = $shipping_addr . " ".$enquiry_arr['shipping_addr_zip_code'];
                      }

                        
                  }   

                  if(isset($enquiry_arr['billing_addr']))
                  { 
                     $billing_addr = $enquiry_arr['billing_addr'];

                    if(isset($enquiry_arr['billing_addr_zip_code'])) 
                    {
                      $billing_addr = $billing_addr . " ".$enquiry_arr['billing_addr_zip_code'];
                    }

                      
                  } 

                @endphp

               

                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Status</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    @php

                    // $payment_status = isset($enquiry_arr['transaction_status'])?$enquiry_arr['transaction_status'] : 'N/A';
                    $payment_status = isset($enquiry_arr['is_payment_status'])?$enquiry_arr['is_payment_status'] : 'N/A';

                    @endphp

                    @if($payment_status == 1) 
                
                    <td><label class="label label-success">Paid</label></td>
                    @elseif($payment_status == 0)

                    <td ><label class="label label-warning">Pending</label></td>

                    @else
                    <td><label class="label label-warning">Pending</label></td>
         
                    @endif

                  </div>
                </div>

                 <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Type</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    @if(isset($enquiry_arr['is_direct_payment']) && $enquiry_arr['is_direct_payment'] == 1)
                      <span class="label label-success">Direct</span>
                    @else
                       <span class="label label-success">In-Direct</span>
                    @endif
    
                  </div>
                </div>

            

                @if($ship_button=='hidden')
                          
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Shipping Status:</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                    <label class="label label-success">Shipped</label>
                    </div>
                  </div>
                @endif

                @if($enquiry_arr['order_cancel_status'] == "2")
                    <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Order Status</span></label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                      <label class="label label-success">Canceled</label>
                      </div>
                    </div>
                       
                @endif


                @if($confirm_button=='hidden')
                  <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Order Status</span></label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                      <label class="label label-success">Order Confirmed</label>
                      </div>
                  </div>
                @endif

              {{--   @if(isset($enquiry_arr['stripe_transaction_detail']))
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Commission Status</span></label>
                    <div class="col-sm-8">
                      <span class="label label-success">{{isset($status)?$status:'N/A'}}</span>
                    </div> 

                  </div>
                @endif  --}} 

            
                @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Vendor Rejection Reason</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
                  </div>
                </div>

                @endif




                @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '') 
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Promo Code</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                      <span>{{isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:'N/A'}}</span>
                    </div>
                  </div>

                @endif  

                @if(isset($enquiry_arr['payment_proof']) && $enquiry_arr['payment_proof'] != null)
                <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Proof</span></label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                      <a href="{{url('/')}}/storage/app/payments_proof/{{$enquiry_arr['payment_proof']}}" target="_blank"><img src="{{url('/')}}/storage/app/payments_proof/{{$enquiry_arr['payment_proof']}}" height="100px" width="100px"></img></a>
                      {{-- <label class="label label-success">Order Confirmed</label> --}}
                      </div>
                  </div>
                @endif
               

                

              </div>

              <div class="col-sm-12 col-md-12 col-lg-6">
                     
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="semibold-mkr">Customer</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <!--<span>{{isset($enquiry_arr['user_details']['retailer_details']['store_name'])?ucfirst($enquiry_arr['user_details']['retailer_details']['store_name']):"N/A"}}</span> -->
                    <span>{{isset($enquiry_arr['user_details']['retailer_details']['dummy_store_name']) && $enquiry_arr['user_details']['retailer_details']['dummy_store_name'] != "" ?ucfirst($enquiry_arr['user_details']['retailer_details']['dummy_store_name']):"N/A"}}</span>
                  </div>
                </div>

                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Vendor</span></label>
            
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{{isset($enquiry_arr['maker_data']['company_name'])?ucfirst($enquiry_arr['maker_data']['company_name']):'N/A'}}</span>
                  </div>
                </div>
                     
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Total Amount</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                  <span>${{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</span>
                  </div>
                </div>

                 <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Shipping Address</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{{$shipping_addr}}</span>
                  </div>
                </div>
         
                <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Billing Address</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <span>{{$billing_addr}}</span>
                  </div>
                </div>

                @php
                  if(isset($tracking_details['company_id']) && $tracking_details['company_id']==1)
                  {
                    $url = 'https://www.fedex.com/en-in/home.html';

                    //$url = "http://www.fedex.com/apps/fedextrack/?action=track&tracknumbers='".$tracking_no."'";
                  } 
                  elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==2)
                  {
                    $url = "https://www.ups.com/in/en/Home.page";
                  }
                  elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==3)
                  {
                     $url = "https://www.usps.com/";
                  }
                  elseif(isset($tracking_details['company_id']) && $tracking_details['company_id']==4)
                  {
                     $url = "https://www.dhl.com/en.html";
                  }
                  else
                  {
                     $url = '';
                  }
                @endphp

                @if(isset($tracking_no) && $tracking_no!='')
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Tracking Number</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                      <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                    </div>
                  </div>

                @endif

               
                @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')

                  <!-- <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Due Date</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                      <span>{{isset($enquiry_arr['payment_due_date'])?us_date_format($enquiry_arr['payment_due_date']):'N/A' }}</span>
                    </div>
                  </div> --> 
                @endif

                <?php
                  $accountHolder = '';
                  if(isset($enquiry_arr['stripe_key_id']))
                  {
                    $getAccountDetails = get_stripe_account_details($enquiry_arr['stripe_key_id']);

                    if($getAccountDetails)
                    {
                      $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
                    }
                  }
                ?>

                @if((isset($enquiry_arr['transaction_mapping']['transaction_id'])) && (isset($accountHolder) && $accountHolder != ''))
                    <div class="row">
                   <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                   <div class="col-sm-12 col-md-12 col-lg-8">
                  
                   <span>{{$enquiry_arr['transaction_mapping']['transaction_id']}}</span>
                  
                  </div>
                </div>
                @endif

                

            @if(isset($accountHolder) && $accountHolder != '')

             <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Account Holder</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$accountHolder}}</span>
                </div> 
            </div>
            @endif
               
          <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                      @if($enquiry_arr['ship_status'] == 1 && $enquiry_arr['is_direct_payment'] == 1 && $enquiry_arr['payment_term'] != 'Net30' && $enquiry_arr['is_split_order'] == 0)
                      @php
                        $ord_retail_price = 0;
                        $admin_commission = 0;
                        $admin_commission_amount = 0;
                        $ord_retail_price = isset($enquiry_arr['total_wholesale_price'])?$enquiry_arr['total_wholesale_price']:0;
                        $shippingCharges = $order_shipping_charge;

   
                        $is_freeshipping = is_promocode_freeshipping(isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:false);

                        if($is_freeshipping == true)
                        {
                            $shippingCharges = 0.00;
                        }
                        else
                        {
                            $shippingCharges = isset($shippingCharges)?$shippingCharges:0.00;
                        }


                        $ord_retail_price = $ord_retail_price - $shippingCharges;
                        $admin_commission = $enquiry_arr['admin_commission'];
                        $admin_commission_amount = $ord_retail_price*($admin_commission / 100);
                        if($enquiry_arr['admin_commission_status'] == '1')
                        {
                          $is_disabled = 'display:none';
                        }
                        else
                        {
                          $is_disabled='display:block';
                        }


                      @endphp     
                      {{-- <button data-toggle="tooltip"  data-size="small" title="Confirm Order" class="btn blueclr" id="btn_pay_btn"  onclick="fillData({{ $ord_retail_price }},{{$admin_commission }},{{ $admin_commission_amount }},{{ $enquiry_arr['id'] }},{{ $enquiry_arr['maker_id'] }})" style="{{ $is_disabled }}">Pay {{$project_name}}</button>   --}}

                      <button data-toggle="tooltip"  data-size="small" title="View Commission" class="btn blueclr" id="btn_pay_btn"  onclick="fillData({{ $ord_retail_price }},{{$admin_commission }},{{ $admin_commission_amount }},{{ $enquiry_arr['id'] }},{{ $enquiry_arr['maker_id'] }})" style="{{ $is_disabled }}">View Commission</button>  


                      @endif
                  </div>
                </div>

         

                  @if($enquiry_arr['is_split_order'] != '1' && $enquiry_arr['order_cancel_status'] != "2")
                         
                    
                    <div class="row">
                      
                      <div class="col-sm-12 text-right vendor-orders-view3btns">
                           <button data-toggle="tooltip"  data-size="small" title="Confirm Order" class="btn blueclr" id="btn_confirm_order" {{$confirm_button}}>Confirm Order</button>  
                        
                            {{-- <button data-toggle="tooltip"  data-size="small" title="Split Order" class="btn blueclr" id="btn_split_order" disabled="false" {{$confirm_button}}>Split Order</button>   --}}

                           @if($payment_button != 'hidden')
                              <button data-toggle="tooltip"  data-size="small" title="Payment Received" class="btn blueclr" id="btn_received_payment" @if($payment_button != 'disabled')  @endif {{$payment_button}} >Payment Received</button> 
                            @endif     

                            @if($ship_button != 'hidden')
                              <button data-toggle="tooltip"  data-size="small" title="Ship order" class="btn blueclr" id="btn_ship_order" @if($ship_button_new != 'disabled') onclick="return shipOrder()" @endif {{$ship_button_new}} >Ship Order</button> 
                            @endif        

                      </div>  
                    </div>        
                  @endif 

             
              
             
                  @if(isset($split_order_arr) && sizeof($split_order_arr) > 0)
                    <div class="row">
                    <label class="col-sm-4">Split Orders</label>
        
                    @foreach($split_order_arr as $key => $split_order)
                      <div class="col-sm-3">
                      
                        <a href="{{url('/')}}/vendor/retailer_orders/view/{{isset($split_order['id'])?base64_encode($split_order['id']):'0'}}"><span class="label label-success">{{$split_order['order_no'] or 'N/A'}}</span> </a>
                      </div>
                    @endforeach
                  </div>
                  @endif
                
                </div>
              </div>


        </div>
        

       </div>

        @php
          $total_amount = 0;
          @endphp

       <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="60">
       <div class="col-md-12">
          <div class="white-box">
             <label>
                <h3>Summary</h3>
             </label>
             <div class="table-responsive">
                <table class="table table-striped table-bordered view-porifile-table">
                   <tr>
                      <th>Order No</th>
                      <th>Product</th>
                      <th>Brand</th>
                      <th>Description</th>
                      <th>SKU No</th>
                      <th>QTY</th>
                      <th>Price </th>
                      <th>Sub Total</th>
                     {{--  <th>Shipping Charges</th>
                      <th>Shipping Discount</th>
                      <th>Product Discount</th> --}}
                      <th>Total Amount</th>

                   </tr>
                   <?php 
                      $tot_qty = 0;
                      
                      $sub_tot_wholesale = 0.00;       
                      $tot_unit_price_wholesale = 0.00;
                      $tot_sub_tot_wholesale = 0.00;
                      $tot_shipping_charges = 0.00;
                      $tot_shipping_discount = 0.00;
                      $tot_pro_dis = 0.00;
                      $tot_amt_column = 0.00;
                ?> 
                   @if(isset($enquiry_arr['quotes_details']) && count($enquiry_arr['quotes_details'])>0)
                   @foreach($enquiry_arr['quotes_details'] as $quote)
              
                   <tr>
                      <td>{{isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:"N/A"}}</a></td>
                      <td>{{isset($quote['product_details']['product_name'])?$quote['product_details']['product_name']:"N/A"}}@if($quote['color'])|  Color:&nbsp;{{$quote['color'] or '-'}}@endif| @if(get_size_from_id($quote['size_id']))Size:&nbsp;{{get_size_from_id($quote['size_id']) }}@endif</td>
                      <td>
                        
                        {{isset($quote['product_details']['brand_details']['brand_name'])?$quote['product_details']['brand_details']['brand_name']:"N/A"}}
                        
                      </td>
                     {{--  <td>

                        @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                           <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                           <div class="truncate">{!!$quote['product_details']['description']!!}</div>
                         
                         @else

                           {!!$quote['product_details']['description']!!}
                         
                         @endif
              
                    
                      </td> --}}
                      <td>
                    @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70 && $quote['product_details']['description']!='' )   
                        @php
                       
                        $desc_html = $desc = "";
                        $desc_html = ($quote['product_details']['description']);
                        $desc = substr(strip_tags( $desc_html), 0, 70);
                       
                        @endphp               
                       <p class="prod-desc"> {!! $desc !!}
                        <br>
                          <a class="readmorebtn" message="{{$quote['product_details']['description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else                      
                       {!!$quote['product_details']['description']!!}
                    @endif
                  </td>
                      
                       <td class="summmarytdsprice">{{isset($quote['sku_no'])?$quote['sku_no']:""}}</td>
                      <td class="summmarytdsprice">{{ $quote['qty'] or 'N/A' }}</td>
                       
                      <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($quote['unit_wholsale_price'])?num_format($quote['unit_wholsale_price']) : 'N/A' }}</td>
                    
                      <td class="summmarytdsprice">
                        @php 
                     
                         $wholesale_price = $quote['qty'] * $quote['unit_wholsale_price'];
                         $sub_wholesale_price = $wholesale_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];
                         $total_amount += $sub_wholesale_price;
                        @endphp
                         <span class="fa fa-dollar"></span>{{isset($wholesale_price)?num_format($wholesale_price) :0.00}}
                      </td>
                      {{-- <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']) :0.00}}</td>

                      <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']) :0.00}}</td>

                      <td class="summmarytdsprice">@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']) :0.00}}</td> --}}

                      <td class="summmarytdsprice">
                        <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : 0.00}}
                      </td>
                   </tr>
                    <?php
                                        
                    $tot_qty+= (float)$quote['qty'];

                    $tot_unit_price_wholesale+=(float)$quote['unit_wholsale_price'];
                    $tot_sub_tot_wholesale+=(float)$wholesale_price;
                    $tot_shipping_charges+=(float)$quote['shipping_charge'];
                    $tot_shipping_discount+=(float)$quote['shipping_discount'];
                    $tot_pro_dis+=(float)$quote['product_discount'];
                    $tot_amt_column+=(float)$sub_wholesale_price;
                    
                   ?>
                   @endforeach
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="summmarytdsprice">Total :</td>
                    <td class="summmarytdsprice">{{$tot_qty}}</td> 
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_unit_price_wholesale)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_sub_tot_wholesale)}}</td>
                    {{-- <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td> --}}
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_amt_column)}}</td>
                  </tr>
                   @else
                   <td colspan="7">No Record Found</td>
                   @endif
                </table>
             </div>
             

              <div class="col-sm-6 pull-right viewsummaryamtbox">

                @if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges'])))   
                  {{-- <div class="row">
                    <div class="left">
                      <h3>Total Amount :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div> --}}
                      @if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0)
                          {{-- <div class="row">
                            <div class="left">
                              <h3>Promotion Discount ({{ isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 }}%):</h3>
                            </div>
                            <div class="right">
                              <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00 }}</span>
                            </div>
                          </div> --}}
                        @endif
                
                      @if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)
                        {{-- <div class="row">
                            <div class="left">
                              <h3>Promotion(Free shipping) :</h3>
                            </div>
                            <div class="right">
                              <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}</span>
                            </div>
                        </div> --}}
                      @endif 

                  {{-- <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div> --}}
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @else
                  {{-- <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div> --}}
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                 @endif    
              </div>

              <div class="clearfix"></div>
        </div>


        
       </div>




  @php
   

   if(isset($enquiry_arr['stripe_transaction_data']) && count($enquiry_arr['stripe_transaction_data']) > 0)
   {

    foreach ($enquiry_arr['stripe_transaction_data'] as $stripeData) 
    {      
        $userRole = Sentinel::findById($stripeData['received_by'])->roles;
        $paiduserRole = Sentinel::findById($stripeData['paid_by'])->roles;

        if(($paiduserRole[0]->slug != 'retailer') && ($userRole[0]->slug == 'admin' || $userRole[0]->slug == 'maker'))
        {

        $trans_status = isset($stripeData['status'])?$stripeData['status']:'';

        $status = '';

        if($trans_status == 1)
        {
          $status = 'Pending';
        }
        elseif($trans_status == 2)
        {
          $status = 'Paid';
        }
        elseif($trans_status == 3)
        {
           $status = 'Failed';
        }
        else
        {
          $status = '';
        }

       
        if(isset($userRole[0]->slug) && $userRole[0]->slug != '')
        {
          if($userRole[0]->slug == 'maker')
          {
            $label = 'Vendor';
          }
          elseif($userRole[0]->slug == 'admin')
          {
            $label = 'Admin';
          }
          elseif($userRole[0]->slug == 'representative' || $userRole[0]->slug == 'sales_manager')
          {
            $label = 'Rep/Sales';
          }
        }

                    

   if(isset($trans_status) && $trans_status != '')
   {
    @endphp
        <div class="col-md-12">
          <div class="white-box">
             <label>
                <h3>{{$label}} Commission Details</h3>
             </label>
             <div class="table-responsive">
                
                <table class="table table-striped table-bordered view-porifile-table">
                   <tr>
                      <th>Order No</th>
                      <th>Order Amount</th>
                      <th>Admin Commission Percent</th>

                      @if($label == 'Vendor')
                        <th>Admin Commission Amount</th>
                        <th>Amount Paid By Admin</th>
                        @else
                      <th>Amount Paid to Admin</th>
                      <th>Vendor Amount</th>

                      @endif   

                      <th>Status</th>
                      <th>Transfer From Account</th>
                      <th>Transaction Id</th>

                   </tr>
                  
                    @php
   
                      $orderAmount = isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 0.00;


                      $is_freeshipping = is_promocode_freeshipping(isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:false);

                      if($is_freeshipping == true)
                      {
                          $orderShippingCharges = 0.00;
                      }
                      else
                      {
                          $orderShippingCharges = isset($order_shipping_charge)?$order_shipping_charge:0.00;
                      }

                      
                      $excludingAmount = $orderAmount - $orderShippingCharges;

                      
                      if($enquiry_arr['is_direct_payment']==1)
                      {
                        $adminCommissionAmount = isset($stripeData['amount'])?num_format($stripeData['amount']):'0.00';

                        $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 

                        $amountPaidToVendor = $excludingAmount - $adminCommissionAmount;
                      }
                      else
                      {
                        $amountPaidToVendor = isset($stripeData['amount'])?num_format($stripeData['amount']):'0.00';

                        $adminCommissionAmount = $excludingAmount - $amountPaidToVendor;
                        
                        $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 

                      } 
                     
                    @endphp

                  <tr>
                    <td>{{isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:"N/A"}}</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($excludingAmount)?num_format($excludingAmount):0.00}}
                      <label class="shippingLabel">Excluded shipping costs</label>
                    </td>
                    
                    <td><i class="label" aria-hidden="true"></i>{{isset($enquiry_arr['admin_commission'])?num_format($enquiry_arr['admin_commission']):'N/A'}} %</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($adminCommissionAmount)?num_format($adminCommissionAmount):0.00}}</td>

                     

                    {{-- @if($label == 'Vendor') --}}

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($amountPaidToVendor)?num_format($amountPaidToVendor):0.00}}</td>

                    {{-- @endif --}}

                    <td><span class="label label-success">{{isset($status)?$status:'Pending'}}</span></td>

                      <?php

                        $accountHolder = '';

                        if(isset($enquiry_arr['transfer_commission_stripe_key_id']))
                        {
                          $getAccountDetails = get_stripe_account_details($enquiry_arr['transfer_commission_stripe_key_id']);

                          if($getAccountDetails)
                          {
                            $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
                          }
                        }
                      ?>

                    <td><span class="label label-success">{{isset($accountHolder)?$accountHolder:'N/A'}}</span></td>


                    <td>{{isset($stripeData['transaction_id'])?$stripeData['transaction_id']:'N/A'}}</td>
                  </tr>
                </table>
             </div>
           </div>
         </div>
         @php
               
    }
    }
  }
   }

    @endphp

      
          <div class="form-group row">
             <div class="col-md-12 text-right p-0">
                   <a class="btn btn-inverse waves-effect waves-light backbtnonly" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
             </div>
          </div>
    </div>
  </div>
</div>


<div id="orderTrackingModal" class="modal fade" data-replace="true" style="display: none;">
    <div class="modal-dialog">

       
        <div class="modal-content sign-up-popup">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

            <div class="modal-body">
              <form id="order_tracking_form"> 
                  <div class="login-form-block">
                      <div class="login-content-block">

                        <div class="order-title-tracking">Order Tracking Details </div>

                          <div class="form-group">
                              <label for="shipping_company" class="col-form-label">Shipping Company<i class="red">*</i></label>
                               <select class="form-control" id="shipping_company" name="shipping_company" data-parsley-required="true" data-parsley-required-message="Please select shipping company.">
                                <option value="">Select Company</option>
                               <option value="1">Fedex</option>
                               <option value="2">UPS</option>
                               <option value="3">US Portal Service</option>
                               <option value="4">DHL</option>
                             </select>   
                          </div>
                         
                         
                          <div class="form-group">
                              <label for="tracking_no" class="col-form-label">Tracking No<i class="red">*</i></label>
                              <div class="">
                                <input type="text" name="tracking_no" id="tracking_no" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter tracking number." data-parsley-maxlength="12" placeholder="Tracking Number">
                              </div>
                              <span class='red'>{{ $errors->first('tracking_no') }}</span>
                          </div>

                          <div class="form-group ship-company-icon">
                            <a href="https://www.fedex.com/en-in/home.html" target="_blank"><img src="{{url('/assets/images/fedex.svg')}}"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="https://www.ups.com/in/en/Home.page" target="_blank"><img src="{{url('/assets/images/UPS_logo.svg')}}"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="https://www.usps.com/" target="_blank"><img src="{{url('/assets/images/usps.svg')}}"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="https://www.dhl.com/en.html" target="_blank"><img src="{{url('/assets/images/dhl_logo.png')}}"></a>
                          </div>
                          
                           <div class="categorynote-main">
                              
                            <div class="pull-right">
                           
                             <a class="btn logi-link-block btn-primary" data-toggle="modal" id="btn_submit">Submit</a>
                            </div>

                           <div class="clearfix"></div>
                           </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
              </form>
            </div>
        </div>
    </div>
</div>




<!-- Hidden Fields -->
<input type="hidden" id="order_id" name="" value="{{isset($enquiry_arr['id'])?$enquiry_arr['id']:0}}">
<input type="hidden" id="maker_id" name="" value="{{isset($enquiry_arr['maker_id'])?$enquiry_arr['maker_id']:0}}">
<input type="hidden" id="retailer_id" name="" value="{{isset($enquiry_arr['retailer_id'])?$enquiry_arr['retailer_id']:0}}">

<input type="hidden" name="order_no" id="order_no" value="{{isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:''}}">


<!-- END Main Content -->


<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Admin Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="adminPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="order_id"   id="orderId_pay" >
          <input type="hidden" name="amount"     id="amount_pay">
          <input type="hidden" name="maker_id"   id="maker_id_pay">
          <input type="hidden" name="order_from" value="retailer"> 


          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission(%) :</div>
              <div class="admin-commission-lnk-right"><span id="admin_commission"></span>%</div>
              </div>
            </div>
             <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission($) :</div>
              <div class="admin-commission-lnk-right">$<span class="pay_amount"></span>
              </div>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Total Order Amount :</div>
              <div class="admin-commission-lnk-right">$<span id="order_amount"></span>
              </div>
              <label class="shippingLabel">Excluded shipping costs</label>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Amount Payable to Admin :</div>
              <div class="admin-commission-lnk-right">$<span class="pay_amount"></span>
              </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        {{-- <button type="button" class="btn btn-primary" onclick="payAdmin()" >Pay</button> --}}
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Stripe Connection Modal -->
<div class="modal fade " id="sendStripeLinkModel" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="">Stripe Connection Request</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {{--  This user is not connected to {{$site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Admin'}} stripe account , send account creation link . --}}

      Currently this user is not associated with us on stripe, do you want to send email for stripe account association.

      </div>
      <div class="modal-footer">

        <input type="hidden" name="user_id" id="user_id" value="">
       
        <input type="hidden" name="vendor_id" id="vendor_id" value="">

       
        <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>

         <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
         
      </div>
    </div>
  </div>
</div>

<div id="paymentProof" data-controls-modal="ModalRepresentative" data-backdrop="static" data-keyboard="false" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog md-size-mdl">
        <!-- Modal content-->
        <div class="modal-content sign-up-popup">
            <div class="modal-body">
                <form method="post" id="payment_proof_form"> 
                 {{csrf_field()}}
                    <div class="login-form-block">
                        <div class="login-content-block">
                          <div class="categorymodalslst">       
                            <div class="row">
                              <div class="col-md-12">
                                <label>Attachment</label>
                                {{-- <textarea class="form-control" id="reject_reason"  ></textarea> --}}
                                <input type="file" name="payment_proof" class="form-control dropify" id="reject_reason" data-parsley-required="true" data-allowed-file-extensions="jpg png jpeg JPEG PNG JPG" data-parsley-errors-container="#error_container" data-parsley-required-message="Please attach a payment proof">
                                <span id="error_container" class="red"></span>
                              </div>
                            </div>
                          </div>
                           <div class="modal-footer">
                          <a class="logi-link-block btn-primary" data-toggle="modal" id="btn_submit_proof" is_button="submit">Submit</a>

                          <button id="close_submit" type="button" class="btn logi-link-block btn-primary" data-dismiss="modal" is_button="cancel">Close</button>
                      </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                  </form>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>


@include('product_description_popup')
<script>

  $(document).ready(function(){

    $('#btn_confirm_order').click(function(){
      
      $.ajax({
           url:'{{url(config('app.project.maker_panel_slug'))}}/payment/{{isset($enquiry_arr['id'])?base64_encode($enquiry_arr['id']) : ''}}/slug_chk_qty_available_or_not',
           method:'GET',
           dataType:'JSON',
           beforeSend : function()
           {
             showProcessingOverlay();
             
           },
           success:function(response)
           {
              hideProcessingOverlay();
             
             if(typeof response =='object')
             {
               if(response.status && response.status=="split_warning")
               {   
                swal({
                     title: "Insufficient Inventory",
                     text: response.description,
                     type: "info",
                     showCancelButton: false,
                     confirmButtonClass: "btn-success",
                     confirmButtonText: "OK",
                     closeOnConfirm: true,
                     closeOnCancel: false
                   },
                   function(isConfirm) {
                     if (isConfirm) 
                     {
                       //location.reload(true);
                       $('#btn_split_order').prop('disabled', false);
                     } 
                   });
                 
               }
               else
               {
                 var order_confirm_text = "Are you sure? Do you want to confirm this order."; 
                 confirm_order(order_confirm_text);
               }
               
             }
           },
           error: function(XMLHttpRequest, textStatus, errorThrown) 
           {
              
           }
        });
      
    });


    
  $('#btn_received_payment').click(function(){
      

      swal({
        title: "Need Confirmation",
        text: 'Are you sure ? Do you want to mark this order as payment received',
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        closeOnConfirm: false
      },
      function(){

         $.ajax({
           url:'{{url(config('app.project.maker_panel_slug'))}}/payment/payment_received/{{isset($enquiry_arr['id'])?base64_encode($enquiry_arr['id']) : ''}}',
           method:'GET',
           dataType:'JSON',
           beforeSend : function()
           {
             showProcessingOverlay();
             
           },
           success:function(response)
           {
              hideProcessingOverlay();
             
             if(typeof response =='object')
             {
               if(response.status && response.status=="success")
               {   
                swal({
                     title: "Success",
                     text: response.description,
                     type: "success",
                     showCancelButton: false,
                     confirmButtonClass: "btn-success",
                     confirmButtonText: "OK",
                     closeOnConfirm: false,
                     closeOnCancel: false
                   },
                   function(isConfirm) {
                     if (isConfirm) {
                       location.reload(true);
                     } 
                   });
                 
               }
               else
               {
                 swal('Error',response.description,response.status);
               }
               
             }
           },
           error: function(XMLHttpRequest, textStatus, errorThrown) 
           {
              
           }
        });

      });
      
    });

    $('#btn_split_order').click(function(){
        
        var order_confirm_text = "Are you sure? Do you want to split this order."; 
        confirm_order(order_confirm_text);
    });

  });

  $('#btn_submit_proof').click(function(){
    
   
     if($('#payment_proof_form').parsley().validate()==false) return;

     var formdata = new FormData($("#payment_proof_form")[0]);

    $.ajax({
         url:'{{url(config('app.project.maker_panel_slug'))}}/payment/payment_proof/{{isset($enquiry_arr['id'])?base64_encode($enquiry_arr['id']) : ''}}',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend: function() 
          {
            showProcessingOverlay();                 
          },
          success:function(data)
          {

              hideProcessingOverlay(); 
             if(typeof data =='object')
             {
               if(data.status && data.status=="success")
               { 
                 
                 $('#payment_proof_form')[0].reset();

                 swal({
                     title: "Success",
                     text: data.description,
                     type: "success",
                     showCancelButton: false,
                     confirmButtonClass: "btn-success",
                     confirmButtonText: "OK",
                     closeOnConfirm: false,
                     closeOnCancel: false
                   },
                   function(isConfirm) {
                     if (isConfirm) {
                        $('#paymentProof').modal('hide');
                       location.reload(true);
                     } 
                   });

                
              }
              else
              {
                
                 swal("Error",data.description,data.status);
              }  
            }
          }
          
        });   
    });



  function confirm_order(order_confirm_text)
  {
    swal({
        title: "Need Confirmation",
        text: order_confirm_text,
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        closeOnConfirm: true
      },
      function(){

        $('#paymentProof').modal('show');

        //  $.ajax({
        //    url:'{{url(config('app.project.maker_panel_slug'))}}/payment/{{isset($enquiry_arr['id'])?base64_encode($enquiry_arr['id']) : ''}}',
        //    method:'GET',
        //    dataType:'JSON',
        //    beforeSend : function()
        //    {
        //      showProcessingOverlay();
             
        //    },
        //    success:function(response)
        //    {
        //       hideProcessingOverlay();
             
        //      if(typeof response =='object')
        //      {
        //        if(response.status && response.status=="success")
        //        {   
        //         swal({
        //              title: "Success",
        //              text: response.description,
        //              type: "success",
        //              showCancelButton: false,
        //              confirmButtonClass: "btn-success",
        //              confirmButtonText: "OK",
        //              closeOnConfirm: false,
        //              closeOnCancel: false
        //            },
        //            function(isConfirm) {
        //              if (isConfirm) {
        //                location.reload(true);
        //              } 
        //            });
                 
        //        }
        //        else
        //        {
        //          swal('Error',response.description,response.status);
        //        }
               
        //      }
        //    },
        //    error: function(XMLHttpRequest, textStatus, errorThrown) 
        //    {
              
        //    }
        // });

      });
  }

  function shipOrder()
  {
    $('#orderTrackingModal').modal('show');
  }


  $('#btn_submit').click(function(){
  
     if($('#order_tracking_form').parsley().validate()==false) return;
     
      var order_id = $('#order_id').val();
      var order_no = $('#order_no').val();
      var tracking_no = $('#tracking_no').val();
      var shipping_company = $('#shipping_company').val();
      var maker_id    = $('#maker_id').val();
      var retailer_id = $('#retailer_id').val();  
      var _token      = "{{ csrf_token() }}";


      $.ajax({
           url:'{{url(config('app.project.maker_panel_slug'))}}/retailer_orders/save_track_details',
           method:'POST',
           data:{"order_id":order_id,"order_no":order_no,"tracking_no":tracking_no,"shipping_company":shipping_company,"maker_id":maker_id,"retailer_id":retailer_id,"_token":_token},
           dataType:'JSON',
           beforeSend : function()
           {
              showProcessingOverlay();
           },
           success:function(response)
           {
              hideProcessingOverlay();
             
             if(typeof response =='object')
             {
                if(response.status && response.status=="success")
                {   
                    
                  swal({
                       title: "Success",
                       text: response.description,
                       type: "success",
                       showCancelButton: false,
                       confirmButtonClass: "btn-success",
                       confirmButtonText: "OK",
                       closeOnConfirm: false,
                       closeOnCancel: false
                     },
                     function(isConfirm) {
                       if (isConfirm) {
                         location.reload(true);
                         
                       } 
                     });
                   
                }
                else
                {
                  swal('Error',response.description,response.status);
                }
               
             }
           }
        });


  });

 function fillData(orderPrice,adminCommissionPer,adminCommissionAmount,orderId,vendorId)
  {

    console.log(orderPrice,adminCommissionAmount,orderId,vendorId);
    $('.vendor-Modal').modal('show');
   
    $('#order_amount').html(orderPrice.toFixed(2));     
    $('.pay_amount').html(adminCommissionAmount.toFixed(2)); 
    $('#admin_commission').html(adminCommissionPer.toFixed(2));    
    $('#orderId_pay').val(orderId);    
    $('#amount_pay').val(adminCommissionAmount);     
    $('#maker_id_pay').val(vendorId);     
  }

  $('#close_submit').click(function(){
    $('#paymentProof').modal('hide');
  });

  $('#reject_reason').keyup(function(){
        $("#error_container").html("");
   });

  function payAdmin()
  {
    var paymentFormData = $('form').serialize();
    
    $.ajax({
            url: '{{url('/vendor')}}'+'/payment/admin',
            type:"POST",
            data: paymentFormData,  
            dataType:'json',
            beforeSend : function()
            {
              showProcessingOverlay();
             
            },
            success:function(data)
            { 
               hideProcessingOverlay();

               if('success' == data.status)
               {
                    swal({title: "Success", 
                          text: data.message, 
                          type: data.status,
                          allowEscapeKey : false,
                          allowOutsideClick: false
                        },
                          function(){ 
                             location.reload();
                          }
                      );               
               }
               else if('error' == data.status)
               {
                swal('Error',data.message,'error');
               }
               else if('warning' == data.status)
               {

                  // $('#client_id').val(data.client_id);
                  $('#user_id').val(data.user_id);
                  $('#vendor_id').val(data.vendor_id);
                   
                  $('.modal').modal('hide');
                  
                  $('#sendStripeLinkModel').modal('show');

               }
               else if('pay-warning' == data.status)
               {
                swal({
                           title:"Warning", 
                           text: data.message, 
                           type: "warning",
                           allowEscapeKey : false,
                           allowOutsideClick: false
                         },
                         function(){ 
                             location.reload();
                         }
                      ); 
               }
               else
               {
                 swal("Error",data.message,data.status);
               }  
            }
          }); 
  }

  function sendStripeAccountLink()
  {
    let user_id   = $('#user_id').val();
    // let client_id  = $('#client_id').val();
    let vendor_id = $('#vendor_id').val();
    let token     = "{{csrf_token()}}";

     $.ajax({
            url: '{{url('/vendor')}}'+'/payment/send_stripe_acc_creation_link',
            type:"POST",
            // data: {"_token":token,"user_id":user_id,"client_id":client_id,'vendor_id':vendor_id},
            data: {"_token":token,"user_id":user_id,'vendor_id':vendor_id},
            beforeSend : function()
            {
              showProcessingOverlay();
             
            },
            success:function(data)
            { 
               hideProcessingOverlay();
               if('success' == data.status)
               {
                    swal({title:"Success", 
                          text: data.message, 
                          type: data.status},
                         
                          function(){ 
                            location.reload();
                         }
                      );               
               }
               else
               {
                 swal("Error",data.message,data.status);
               }  
            }
          }); 
  }
</script>



@stop