@extends('retailer.layout.master')                
@section('main_content')
<!-- Page Content -->
<style type="text/css">
  .row{
  padding-bottom: 20px;
}
</style>

<?php

     $net30Status = 0;

     $currentUserDetails = Sentinel::getUser();

     if($currentUserDetails != false && $currentUserDetails != null)
     {
        $net30Status = $currentUserDetails->status_net_30;
     }
?>
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
      
         <li><a href="{{url('/')}}/retailer/my_orders/order_from_representative">Orders By Reps / Sales</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- .row -->
<div class="row">
  <div class="col-md-12">
    @include('retailer.layout._operation_status')  

        <div class="white-box small-space-nw">
            <!-- split order button will show -->
            @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
       
              <a class="btn btn-inverse mb-4" href="{{url('/')}}/retailer/my_orders/order_summary/{{base64_encode($main_split_order_no['order_no'])}}/{{base64_encode($main_split_order_no['maker_id'])}}">This order is split from : {{$main_split_order_no['order_no']}}</a>

            @endif

            <div class="row">
              <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Order No.</span></label>
                    <div class="col-sm-8">
                      <span>{{ $arr_data['order_no'] or 'N/A' }}</span>
                    </div>
                  </div>
                 
                  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Order Date</span></label>
                    <div class="col-sm-8">
                      <span>{{ isset($arr_data['created_at'])?us_date_format($arr_data['created_at']):'N/A' }}</span>
                    </div>
                  </div>


                  @if(isset($arr_data['representative_user_details']) && $arr_data['representative_user_details']!='')

                    <div class="row">
                      <label class="col-sm-4"><span class="col-left-vw">Representative</span></label>
                      <div class="col-sm-8">
                        <span>{{isset($arr_data['representative_user_details']['first_name'])?ucfirst($arr_data['representative_user_details']['first_name']." ".$arr_data['representative_user_details']['last_name']):""}}</span>
                      </div>
                    </div>

                  @elseif(isset($arr_data['sales_manager_details']) && $arr_data['sales_manager_details']!='')
                    <div class="row">
                      <label class="col-sm-4"><span class="col-left-vw">Sales Manager</span></label>
                      <div class="col-sm-8">
                        <span>{{isset($arr_data['sales_manager_details']['first_name'])?ucfirst($arr_data['sales_manager_details']['first_name']." ".$arr_data['sales_manager_details']['last_name']):""}}</span>
                      </div>
                    </div>
                  
                  @endif 

                  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Shipping Status</span></label>
                    <div class="col-sm-8">
                        <?php $status = isset($arr_data['ship_status'])?get_order_status($arr_data['ship_status']):'N/A'?>

                        @if($status=="Pending")
                         <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                        @elseif($status=="Shipped")
                         <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>

                        @elseif($status=="Failed")
                        <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>

                        @else
                        
                         <span>{{$status or 'N/A'}}</span>

                        @endif

                    </div>
                  </div>
     <div class="row">
                <label class="col-sm-4"><span class="col-left-vw">Payment Status</span></label>
                <div class="col-sm-8">
                    @php 
                      $status = "";

                     /* if(isset($arr_data['transaction_mapping_details']) && isset($arr_data['transaction_mapping']))
                      { 
                        $status = isset($arr_data['transaction_mapping_details']['transaction_status'])?get_payment_status($arr_data['transaction_mapping_details']['transaction_status']):'';
                      }*/


                     if($arr_data['is_payment_status'] == 1)
                     {
                      $status = 'Paid';
                     }
                     else
                     {
                      $status = 'Pending';
                     } 

                    @endphp
                    
                    @if($status=="Pending")
                     <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                    @elseif($status=="Paid")
                     <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>

                    @elseif($status=="Failed")
                    <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>
                    
                    @elseif($status =="")
                    <span class="label label-warning">Pending</span>

                    @else
                     <span>{{$status or ''}}</span>
                    @endif
                </div>
              </div>

                  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Payment Term</span></label>
                    <div class="col-sm-8">
                      @if(isset($arr_data['payment_term']) && $arr_data['payment_term'] == 'Net30')

                      <span class="label label-success">{{isset($arr_data['payment_term']) && $arr_data['payment_term'] != "" ?$arr_data['payment_term']:'N/A' }}</span>

                      @else

                         <span class="label label-success">{{isset($arr_data['payment_term']) && $arr_data['payment_term'] != ""?$arr_data['payment_term']:'N/A' }}</span>

                      @endif
                    </div>
                  </div>

                @if(isset($arr_data['order_rejected_reason']) && $arr_data['order_rejected_reason']!='')
              
                  <div class="row-main-vw">
                    <label class="col-left-vw"><span class="col-left-vw">Vendor Rejection Reason</span></label>
                    <div class="col-right-vw">
                      <span>{!!$arr_data['order_rejected_reason'] or ''!!}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>

                @endif
          
                  @php 
                      if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']==null) 
                      $shipping_address = $enquiry_arr['shipping_addr']; 
                      else if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']!=null) 
                      $shipping_address = $enquiry_arr['shipping_addr'].','.$enquiry_arr['shipping_addr_zip_code']; 
                      else
                       $shipping_address = 'N/A';
                       
                      if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']==null) 
                      $billing_address = $enquiry_arr['billing_addr']; 
                      else if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']!=null) 
                      $billing_address = $enquiry_arr['billing_addr'].','.$enquiry_arr['billing_addr_zip_code']; 
                      else
                       $billing_address = 'N/A'; 
                  @endphp



                @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != '0' && $arr_data['promo_code'] != '') 
                  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Promo Code</span></label>
                    <div class="col-sm-8">
                      <span>{{isset($arr_data['promo_code'])?$arr_data['promo_code']:'N/A'}}</span>
                    </div>
                  </div>

                @endif  


                <div class="row">
                   
                    <div class="col-sm-8">
                          <?php $status = isset($arr_data['transaction_mapping']['transaction_status'])?get_payment_status($arr_data['transaction_mapping']['transaction_status']):'';?>
                    <?php $shipping_status = isset($arr_data['ship_status'])?get_order_status($arr_data['ship_status']):'N/A'?>
                    
                       @php

                          $Wholsale_sub_total = 0.00;

                          if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)
                          {
                            foreach($arr_data['order_details'] as $key => $product)
                            {
                                if($product['maker_id'] == base64_decode($enc_maker_id))
                                {
                                  $Wholsale_sub_total += $product['unit_wholsale_price']*$product['qty'] - $product['product_discount'];
                                }
                               
                            }
                          }
                      
                      @endphp

                  @if($arr_data['is_split_order']!=1)  

                      @if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 1)
                          @php  
                            Session::put('representative_order_id',$arr_data['id']);

                            Session::put('representative_order_total',$arr_data['total_wholesale_price']);
                             
                            Session::put('Wholsale_sub_total',$Wholsale_sub_total);
                           
                            if($arr_data['payment_term'] == 'Online/Credit')
                            {
                               Session::put('payment_type','Online/Credit');
                            }
                            elseif($arr_data['payment_term'] == 'Net30')
                            {
                              Session::put('payment_type','Net30');
                            }
                            else
                            {
                               Session::put('payment_type','Online/Credit');
                            }


                          @endphp

                          @php

                          if($arr_data['payment_term'] == 'Net30')
                          {
                            if(isset($arr_data['maker_confirmation'])  && $arr_data['maker_confirmation'] == 1)
                              {
                               
                                $eventCall = "return checkoutRedirect($(this))";
                              }
                              else
                              {
                                  $eventCall = 'return notAllowedPayment()';
                              } 
                          }
                          else
                          {
                            $eventCall = "return checkoutRedirect($(this))";
                          }

                                  
                          

                          @endphp
                            
                            @if($count == 0)  

                            @if($arr_data['order_cancel_status'] != 1)
                                <div class="button-left-right"> 
                                    @if($arr_data['payment_term'] != 'Offline')
                                    
                                      {{-- <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="{{$eventCall}}">Pay Now</a> --}}

                                      <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="{{$eventCall}}">Order Now</a>
                                    @endif

                                    
                                </div>
                            @endif
                            
                            @endif

                        @endif

                    @endif  
                      
                        <input type="hidden" name="payment_type" id="payment_type" value="{{$arr_data['payment_term'] or ''}}">
                    </div>                    
                  </div> 

            </div>



            <div class="col-sm-12 col-md-12 col-lg-6">
              <div class="row">
                <label class="col-sm-4"><span class="col-left-vw">Total Amount</span></label>
                <div class="col-sm-8">
                <span>${{ isset($arr_data['total_wholesale_price'])?num_format($arr_data['total_wholesale_price']) : 'N/A' }}</span>
                </div>
              </div>

              <div class="row">
                <label class="col-sm-4"><span class="col-left-vw">Vendor</span></label>
                <div class="col-sm-8">{{isset($arr_data['maker_data']['maker_details']['company_name'])?ucfirst($arr_data['maker_data']['maker_details']['company_name']):''}}</div>
              </div>
                 
              
                 

              @if(isset($arr_data['payment_due_date']) && $arr_data['payment_due_date'] == 'Net30')

               <!--  <div class="row">
                    <label class="col-sm-4"><span class="col-left-vw">Payment Due Date</span></label>
                    <div class="col-sm-8">
                      <span>{{isset($arr_data['payment_due_date'])?us_date_format($arr_data['payment_due_date']):'N/A' }}</span>
                    </div>
        
                </div> -->
              @endif

              <div class="row">
                <label class="col-sm-4"><span class="col-left-vw">Shipping Address</span></label>
                <div class="col-sm-8">
                  <span>
                    

                    {{isset($arr_data['address_details']['ship_street_address'])?$arr_data['address_details']['ship_street_address'].',':''}} 

                    {{isset($arr_data['address_details']['ship_suit_apt'])?$arr_data['address_details']['ship_suit_apt'].',':''}} 

                    {{isset($arr_data['address_details']['ship_city'])?$arr_data['address_details']['ship_city'].',':''}} 

                    {{isset($arr_data['address_details']['ship_state'])?$arr_data['address_details']['ship_state'].',':''}}

                    {{isset($arr_data['address_details']['ship_country'])?get_country($arr_data['address_details']['ship_country']).',':''}} 
                                
                    {{isset($arr_data['address_details']['ship_zip_code'])?$arr_data['address_details']['ship_zip_code'].',':''}}

                    Mobile.No: {{isset($arr_data['address_details']['ship_mobile_no'])?$arr_data['address_details']['ship_mobile_no']:''}}

                  </span>
                </div>
              </div>
              <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Billing Address</span></label>
                  <div class="col-sm-8">
                    <span>
           

                      {{isset($arr_data['address_details']['bill_street_address'])?$arr_data['address_details']['bill_street_address'].',':''}} 

                      {{isset($arr_data['address_details']['bill_suit_apt'])?$arr_data['address_details']['bill_suit_apt'].',':''}} 

                      {{isset($arr_data['address_details']['bill_city'])?$arr_data['address_details']['bill_city'].',':''}} 

                      {{isset($arr_data['address_details']['bill_state'])?$arr_data['address_details']['bill_state'].',':''}}

                      {{isset($arr_data['address_details']['bill_country'])?get_country($arr_data['address_details']['bill_country']).',':''}}

                      {{isset($arr_data['address_details']['bill_zip_code'])?$arr_data['address_details']['bill_zip_code'].',':''}}

                      Mobile.No: {{isset($arr_data['address_details']['bill_mobile_no'])?$arr_data['address_details']['bill_mobile_no']:''}}

                    </span>
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
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="col-left-vw">Tracking Number</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                    </div>
                  </div>

                @endif   

                 <?php
                    $accountHolder = '';
                    if(isset($arr_data['stripe_key_id']))
                    {
                      $getAccountDetails = get_stripe_account_details($arr_data['stripe_key_id']);

                      if($getAccountDetails)
                      {
                        $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
                      }
                    }
                 ?>

                
                  @if((isset($arr_data['transaction_mapping']['transaction_id']) && $arr_data['transaction_mapping']['transaction_id'] != '') && (isset($accountHolder) && $accountHolder != ''))
                
                    <div class="row">
                   <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                   <div class="col-sm-12 col-md-12 col-lg-8">
                  
                   <span>{{$arr_data['transaction_mapping']['transaction_id']}}</span>
                  
                  </div>
                </div>
                @endif

                

           {{--  @if(isset($accountHolder) && $accountHolder != '')

             <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Account Holder</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$accountHolder}}</span>
                </div> 
            </div>
            @endif  --}}

             <!--  @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != "" && $arr_data['promo_code'] != '0')

                <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Promo Code</span></label>
                  <div class="col-sm-8">
                    <span class="label label-success">{{$arr_data['promo_code'] or ''}}</span>
                  </div>
                </div> 

              @endif -->
            
            </div>
          </div>
      </div>
    

     
  </div> 
   
   <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>Summary</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Product Name</th>
                  <th>Brand Name</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th>Unit Price (Wholesale)</th>
                  <th>Sub Total (Wholesale)</th>
                  {{-- <th>Shipping Charges</th>
                  <th>Shipping Charges Discount</th>
                  <th>Product Discount</th> --}}
                  <th>Total Amount </th>

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
               <?php
               $grand_total_amount = $subWholesalePrice = $freeShipAmount = 0;
               ?>
               @if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)

               @foreach($arr_data['order_details'] as $quote)

                @if($quote['maker_id'] == base64_decode($enc_maker_id))
          
                  <tr>
                      <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):"N/A"}}</td>

                      <td>
                         {{isset($quote['product_details']['brand_details']['brand_name'])?ucfirst($quote['product_details']['brand_details']['brand_name']):"N/A"}}
                      </td>

                      {{-- @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                        <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                        <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A' !!}</div></td>
                      
                      @else

                        <td>{!! $quote['product_details']['description'] or 'N/A' !!}</td>
                      
                      @endif
                      <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="20"> --}}

                      <td>
                           @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70 && $quote['product_details']['description']!='' )   
                              @php
                              $desc_html = $desc = "";
                              $desc_html = ($quote['product_details']['description']);
                              $desc =  substr(html_entity_decode($desc_html), 0, 70);
                              @endphp               
                             <p class="prod-desc"> {!!html_entity_decode($desc)!!}
                              <br>
                                <a class="readmorebtn" message="{{$quote['product_details']['description']}}" style="cursor:pointer">
                                    <b>Read more</b>
                                </a>
                            </p>                  
                          @else                      
                             {!!$quote['product_details']['description']!!}
                          @endif
                     </td>

                       <td class="summmarytdsprice"><div class="truncate">{!! $quote['sku']  or 'N/A' !!}</div></td>
                      <td class="summmarytdsprice">{{ $quote['qty'] or 'N/A' }}</td>
                     
                   
                      <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($quote['unit_wholsale_price'])?num_format($quote['unit_wholsale_price']) : 'N/A' }}</td>
                     
                            <!-- $total_amount = $sub_wholesale_price+$quote['product_shipping_charge']-$quote['shipping_charges_discount']-$quote['product_discount']; -->
                      <td class="summmarytdsprice">

                         @php
                          $sub_wholesale_price = $quote['qty'] * $quote['unit_wholsale_price'];

                            $total_amount = $sub_wholesale_price+$quote['product_shipping_charge']-$quote['shipping_charges_discount']-$quote['product_discount'];

                            $grand_total_amount += $sub_wholesale_price+$quote['product_shipping_charge']-$quote['shipping_charges_discount']-$quote['product_discount'];

                          $subWholesalePrice += $total_amount;
                          // dump($sub_wholesale_price);

                            $freeShipAmount += isset($quote['product_shipping_charge'])?(float)$quote['product_shipping_charge'] - (float)$quote['shipping_charges_discount']:0;

                         @endphp


                         <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) :0.00}}
                      </td>
                    <!--  <td>@if($quote['product_shipping_charge']>0)+@endif<span class="fa fa-dollar">{{isset($quote['product_shipping_charge'])?num_format($quote['product_shipping_charge']):'0.00'}}</td> -->


                       {{-- <td class="summmarytdsprice">@if($quote['product_shipping_charge']>0)+@endif<span class="fa fa-dollar">{{isset($quote['product_shipping_charge'])?num_format($quote['product_shipping_charge']):'0.00'}}</td>

                      <td class="summmarytdsprice">@if($quote['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar">{{ isset($quote['shipping_charges_discount'])?num_format($quote['shipping_charges_discount']):'0.00' }}</td>

                      <td class="summmarytdsprice">@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar">{{isset($quote['product_discount'])?num_format($quote['product_discount']):'0.00' }}</td>
 --}}
                      <td class="summmarytdsprice"><span class="fa fa-dollar">{{ isset($total_amount)?num_format($total_amount):0.00 }}</td>

                   </tr>
                   <?php 
                    $tot_unit_price_wholesale+= (float)$quote['unit_wholsale_price'];
                    $tot_qty+= (float)$quote['qty'];
                    $tot_sub_tot_wholesale+=(float)$sub_wholesale_price;
                    $tot_shipping_charges+=(float)$quote['product_shipping_charge'];
                    $tot_shipping_discount+=(float)$quote['shipping_charges_discount'];
                    $tot_pro_dis+=(float)$quote['product_discount'];
                    $tot_amt_column+=(float)$total_amount;
                   ?>
               @endif
               
              @endforeach
              <tr>
                  <th class="summmarytdsprice"  colspan="4" align="right">Total</th>                 
                  <td class="summmarytdsprice">{{$tot_qty}}</td> 
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_unit_price_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_sub_tot_wholesale)}}</td>
                  {{-- <td><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td> --}}
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_amt_column)}}</td>
                 

               </tr>
            @else
                <td colspan="7">No Record Found</td>
            @endif

            </table>
         </div>
      
        <div class="col-md-12 p-0">

          {{-- <div class="col-sm-6 pull-right viewsummaryamtbox">

          @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != '')

            <div class="row">
              <div class="left">
                <h3>Total Amount :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($subWholesalePrice)?num_format($subWholesalePrice): '0.00' }}</span>
              </div>
            </div>
                      
          @if(isset($arr_data['promotion_discount']) && $arr_data['promotion_discount'] != '' && $arr_data['promotion_discount'] != 0)
                      
            <div class="row">
              <div class="left">
                <h3>Promotion Discount :</h3>
              </div>
              <div class="right">
                <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($arr_data['promotion_discount'])?num_format($arr_data['promotion_discount']) : 'N/A' }}</span>
              </div>
            </div>
         @endif

          @if(isset($isFreeShipping) && $isFreeShipping == true)
            <div class="row">
               <div class="left">
                 <h3>Promotion(Free shipping) :</h3>
               </div>
               <div class="right">
                 <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($freeShipAmount)?num_format($freeShipAmount) : 0.00 }}</span>
               </div>
            </div>
         @endif
                
         <div class="row totalrow">
           <div class="left">
             <h3>Total Amount (Wholesale) :</h3>
           </div>
           <div class="right">
             <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($arr_data['total_wholesale_price'])?num_format($arr_data['total_wholesale_price']) : 'N/A' }}</span>
           </div>
         </div>
         @else
            <div class="row totalrow">
              <div class="left">
                <h3>Total Amount (Wholesale) :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{isset($grand_total_amount)?num_format($grand_total_amount) : 'N/A' }}</span>
              </div>
            </div>
           @endif    
        </div> --}}

          <div class="col-sm-6 pull-right viewsummaryamtbox">

            @if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges'])))   
                  <div class="row">
                    <div class="left">
                      <h3>Total Amount :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0)  
                    <div class="row">
                        <div class="left">
                          <h3>Promotion Discount ({{ isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 }}%):</h3>
                        </div>
                        <div class="right">
                          <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00 }}</span>
                        </div>
                    </div>
                @endif
                
                @if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)
                    <div class="row">
                        <div class="left">
                          <h3>Promotion(Free shipping) :</h3>
                        </div>
                        <div class="right">
                          <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}</span>
                        </div>
                    </div>
                @endif 

                <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                </div>

                @else
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @endif  
                  
              </div>
             <!--   <div class="pull-right totl-grands">

                 @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != '')

                    <div class="viewtotal-bg">
                       <span>Total Amount :</span>
                      <span class="viewspan">
                      <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($subWholesalePrice)?num_format($subWholesalePrice): '0.00' }}
                      </span>
                    </div>

                 @if(isset($arr_data['promotion_discount']) && $arr_data['promotion_discount'] != '' && $arr_data['promotion_discount'] != 0)

                    <div class="viewtotal-bg">
                       <span>Promotion Discount :</span>
                      <span class="viewspan">
                      - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($arr_data['promotion_discount'])?num_format($arr_data['promotion_discount']) : 'N/A' }}
                    </span>
                    </div>

                  @endif

                 @if(isset($isFreeShipping) && $isFreeShipping == true)

                    <div class="viewtotal-bg">
                       <span>Promotion (freeshipping) :</span>
                      <span class="viewspan">
                      - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($freeShipAmount)?num_format($freeShipAmount) : 0.00 }}
                    </span>
                    </div>

                  @endif

                    <div class="viewtotal-bg border-h">
                       <span>Total Amount (Wholesale) :</span>
                      <span class="viewspan">
                      <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($arr_data['total_wholesale_price'])?num_format($arr_data['total_wholesale_price']) : 'N/A' }}
                    </span>
                    </div>

                @else
                  <span>Total Amount (Wholesale) :</span>
                  
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($grand_total_amount)?num_format($grand_total_amount) : 'N/A' }}

                @endif
                  
               </div>
                -->
            </div>
          <div class="clearfix"></div>
      </div>
      @if(isset($split_order_arr) && count($split_order_arr)>0)
      <div class="white-box">
         <label>
            <h3>Split Order Summary</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Order No</th>
                  <th>Order Date</th>
                  <th>Vendor</th>
                  <th>Total Amount</th>
                  <th>Payment Status</th>
                  <th>Shipping Status</th>
                  <th>Action</th>
               </tr>
               

               @foreach($split_order_arr as $quote)
               <tr>
               
                  <td><a href="{{url('/')}}/retailer/my_orders/order_summary/{{isset($quote['order_no'])?base64_encode($quote['order_no']):0}}/{{isset($quote['order_no'])?base64_encode($quote['maker_id']):0}}">{{isset($quote['order_no'])?$quote['order_no']:"N/A"}}</a></td>

                  <td>{{us_date_format($quote['created_at'])}}</td>

                  <td>{{ $quote['maker_details']['company_name'] or 'N/A' }}</td>

                   <td>${{isset($quote['total_wholesale_price'])?num_format($quote['total_wholesale_price']):0}}</td>
                   @php
                      if($quote['transaction_mapping']['transaction_status'] == 2)
                      {
                        $payment_status = 'Paid';
                      }
                      elseif($quote['transaction_mapping']['transaction_status'] == 1)
                      {
                        $payment_status = 'Pending';
                      }
                      elseif($quote['transaction_mapping']['transaction_status'] == 3)
                      {
                        $payment_status = 'Failed';
                      }
                      else
                      {
                        $payment_status = 'Pending';
                      }
                      
                   @endphp
                  <td>{{ $payment_status }}</td>
                 
                  @php
                    if($quote['ship_status'] == 1)
                    {
                      $shipping_status = 'Shipped';
                    }
                    elseif($quote['ship_status'] == 0)
                    {
                      $shipping_status = 'Pending';
                    }
                   
                 @endphp
                <td>{{ $shipping_status }}</td>

                  
                
                  <td>
                     <a href="{{url('/')}}/retailer/my_orders/order_summary/{{isset($quote['order_no'])?base64_encode($quote['order_no']):0}}/{{isset($quote['order_no'])?base64_encode($quote['maker_id']):0}}" class="btn btn-success">View</a>

                     
                  </td>
               </tr>
               @endforeach
               
            </table>
         </div>
          
         

           
          <div class="clearfix"></div>
      </div>
      @endif
   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{url('/')}}/retailer/my_orders/order_from_representative"><i class="fa fa-arrow-left"></i> Back</a>


                @if($arr_data['ship_status'] != 1 && $arr_data['order_cancel_status'] == 0 && $arr_data['is_confirm'] != "3")
                                 
                 <!-- <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" onclick="cancelOrder({{isset($arr_data['id'])?$arr_data['id']:0}});">Cancel Order</a> -->
                
                @elseif($arr_data['order_cancel_status'] == 1)
                
                  <!-- <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" >Cancel Requested</a> -->
                
                 @elseif($arr_data['order_cancel_status'] == 2)

                <!--   <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" >Cancelled</a> -->

               @endif

              {{--  <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a> --}}
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->


@include('product_description_popup')
<script type="text/javascript">
 

 var ord_no   = '{{isset($arr_data['order_no'])?base64_encode($arr_data['order_no']):false}}';
 var maker_id = '{{isset($arr_data['maker_id'])?base64_encode($arr_data['maker_id']):false}}';
 var url      = '{{url('/checkout/representative/')}}/'+ord_no+'/'+maker_id;
 var net30    = "{{ $module_url_path.'/net_30_payment/'.base64_encode($arr_data['id'])}}";


  
function notAllowedPayment()
{
  swal('Warning','Please wait for order confirmation from the Vendor','warning');
}

function checkoutRedirect(ref)
{

      swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to place order.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'OK',
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        closeOnCancel: true
      },
      //showProcessingOverlay(),                
                
      function(isConfirm){
           
          if(isConfirm==true)
          {
             showProcessingOverlay();
              window.location = url;
          }
          else
          {
            location.reload();
          }

  });
    
} 

function cancelOrder(order_id)
  {
      var module_url_path  = "{{ $module_url_path or '' }}";

      order_id = btoa(order_id);
   
      swal({
        title:'Need Confirmation',
        text: "Are you sure? Do you want to cancel this order.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
      },
      function(isConfirm) 
      {
        if (isConfirm) 
        {
              $.ajax({
                url:module_url_path+'/order_cancel',
                method:'GET',
                data:{order_id:order_id},
                beforeSend : function()
                {
                  showProcessingOverlay();
                 
                },
                success:function(response)
                {
                    hideProcessingOverlay();
                   
                    swal(response.status,response.description,response.status);

                    swal({
                    title:'Success',
                    text: response.description,
                    type: response.status,
                    showCancelButton: false,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "OK",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                  },
                  function(isConfirm) 
                  {
                    location.reload(true);
                  })
                }
             });
          }
       
      });

} 
</script>
@stop