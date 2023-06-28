@extends('representative.layout.master')  
@section('main_content')

<style>
  .row{
    padding-bottom:20px; 
  }
</style>
<!-- Page Content -->

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
       <?php
         $confirm_order_title  = 'My orders';
         $module_title = $module_title;
         $url_path = $module_url_path;

         if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 1)
         {
            $module_title = "Order";
            $confirm_order_title = 'Confirm Orders';
            $url_path = $module_url_path.'/confirmed';
         } 
        
      ?>{{$module_title}} Details</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
        <li><a href="{{$url_path or ''}}">{{$confirm_order_title or ''}}</a></li>
         <li class="active">{{$confirm_order_title or ''}} Details </li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
    @include('admin.layout._operation_status')  
    <div class="col-md-12">
        <div class="white-box small-space-nw">
          @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
     
          <a href="{{url(config('app.project.representative_panel_slug'))}}/leads/view_lead_listing/{{base64_encode($main_split_order_no['id'])}}/{{base64_encode($main_split_order_no['order_no'])}}" class="btn btn-inverse pull-right">This order is split from : {{$main_split_order_no['order_no']}}</a>
          @endif
  
            @php 

                if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='2')
                {
                    $status = 'Pending';
                }
                elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='3')
                {
                    $status = 'Rejected';

                }
                elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='1')
                {
                    $status = 'Approved';

                }
                else
                {
                    $status = 'Pending';
                }
            @endphp

            @if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='0')         
                <a class="btn btn-inverse waves-effect waves-light pull-right  small-btn-padding" id = "confirmbtn" data-value="{{$module_url_path.'/update_lead_listing/'.base64_encode($leads_arr['id'])}}">Confirm Order</a> 

            

            @endif

        <div class="row">
          <div class="col-sm-12 col-md-12 col-lg-6">
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Order No.</span></label>
              <div class="col-sm-8">
                <span>{{ $leads_arr['order_no'] or 'N/A' }}</span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Order Date</span></label>
              <div class="col-sm-8">
                <span> {{ isset($leads_arr['created_at'])?us_date_format($leads_arr['created_at']):'N/A' }}</span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Retailer</span></label>
              <div class="col-sm-8">
                <span>
                 {{isset($leads_arr['retailer_user_details']['retailer_details']['store_name'])?$leads_arr['retailer_user_details']['retailer_details']['store_name']:"N/A"}}
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Vendor</span></label>
              <div class="col-sm-8">
                <span>
                 {{isset($leads_arr['maker_details']['company_name'])?ucfirst($leads_arr['maker_details']['company_name']):'N/A'}}
                </span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Total Amount</span></label>
              <div class="col-sm-8">
                <span>
                 ${{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : 0.00 }}
                </span>
              </div>
            </div>
<div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Shipping Status</span></label>
              <div class="col-sm-8">
                <span> <?php $status = isset($leads_arr['ship_status'])?get_order_status($leads_arr['ship_status']):'N/A'?>

                            @if($status=="Pending")
                                <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                                @elseif($status=="Shipped")
                                    <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>

                                @elseif($status=="Failed")
                                    <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                                @else
                              
                                    {{$status or 'N/A'}}

                            @endif</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Retailer Payment Status</span></label>
              <div class="col-sm-8">
                <span>
                  @if(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==1)
                    <span class="label label-warning">Pending</span>

                  @elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==2)
                    <span class="label label-success">Paid</span>

                  @elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==3)
                    <span class="label label-danger">Failed</span>
                  @else
                    <span class="label label-warning">Pending</span>
                  @endif
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Retailer Approval</span></label>
              <div class="col-sm-8">
               <span>
                  @if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 0)
                                <span class="label label-warning">Pending</span>

                                @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 1)
                                    <span class="label label-success">Approved</span>

                                @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 2)
                                    <span class="label label-warning">Pending</span>

                                @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 3)
                                    <span class="label label-warning">Rejected</span>

                                @else

                                    <span class="label label-warning">Pending</span>

                            @endif


               </span>
              </div>
            </div>



                @php
                  $trans_status = isset($leads_arr['stripe_transaction_detail']['status'])?$leads_arr['stripe_transaction_detail']['status']:'';
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
               @endphp
               @if(isset($leads_arr['stripe_transaction_detail']) && isset($leads_arr['stripe_transaction_detail'])!=null) 
                  <div class="row">
                     <label class="col-sm-4">Commission Status</label>
                     <div class="col-sm-8">
                        <span class="label label-success">{{$status}}</span>
                     </div> 

                  </div>
               @endif


          </div>

          <div class="col-sm-12 col-md-12 col-lg-6">
           
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Total Amount (Wholesale)</span></label>
              <div class="col-sm-8">
                <span>${{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : '0.00' }}</span>
              </div>
            </div>
             
            
              <div class="row">
                <label class="col-sm-4"><span class="sanssemibold">Payment Term</span></label>
                <div class="col-sm-8">
                  <span>{{ isset($leads_arr['payment_term']) && $leads_arr['payment_term'] != "" ? $leads_arr['payment_term'] : "N/A"}}</span> 
                </div>
              </div>

             @if(isset($leads_arr['payment_term']) && $leads_arr['payment_term']!="Online/Credit")  
                <!-- <div class="row">
                  <label class="col-sm-4"><span class="sanssemibold">Payment Due Date</span></label>
                  <div class="col-sm-8">
                    <span> {{isset($leads_arr['payment_due_date'])?us_date_format($leads_arr['payment_due_date']):'N/A' }}</span>
                  </div>
                </div> -->
            @endif

               <div class="row">
                <label class="col-sm-4"><span class="sanssemibold">Shipping Address</span></label>
                <div class="col-sm-8">
                  <span>  

                    {{isset($leads_arr['address_details']['ship_street_address'])?$leads_arr['address_details']['ship_street_address'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_suit_apt'])?$leads_arr['address_details']['ship_suit_apt'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_city'])?$leads_arr['address_details']['ship_city'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_state'])?$leads_arr['address_details']['ship_state'].',':''}}

                    {{isset($leads_arr['address_details']['ship_country'])?get_country($leads_arr['address_details']['ship_country']).',':''}} 
                                
                    {{isset($leads_arr['address_details']['ship_zip_code'])?$leads_arr['address_details']['ship_zip_code'].',':''}}

                    Mobile.No: {{isset($leads_arr['address_details']['ship_mobile_no'])?$leads_arr['address_details']['ship_mobile_no']:''}}


                  </span> 
                </div>
              </div>

              <div class="row">
                <label class="col-sm-4"><span class="sanssemibold">Billing Address</span></label>
                <div class="col-sm-8">
                  <span>

                      {{isset($leads_arr['address_details']['bill_street_address'])?$leads_arr['address_details']['bill_street_address'].',':''}} 

                      {{isset($leads_arr['address_details']['bill_suit_apt'])?$leads_arr['address_details']['bill_suit_apt'].',':''}} 

                      {{isset($leads_arr['address_details']['bill_city'])?$leads_arr['address_details']['bill_city'].',':''}} 

                      {{isset($leads_arr['address_details']['bill_state'])?$leads_arr['address_details']['bill_state'].',':''}}

                      {{isset($leads_arr['address_details']['bill_country'])?get_country($leads_arr['address_details']['bill_country']).',':''}}

                      {{isset($leads_arr['address_details']['bill_zip_code'])?$leads_arr['address_details']['bill_zip_code'].',':''}}
                      Mobile.No: {{isset($leads_arr['address_details']['bill_mobile_no'])?$leads_arr['address_details']['bill_mobile_no']:''}}
                      
                  </span> 
                </div>
              </div>

   
              @if(isset($leads_arr['retailer_reject_reason'])  && $leads_arr['retailer_reject_reason'] !='')
                 <div class="row">
                    <label class="col-sm-4"><span class="lbl-font-wight">Retailer Rejection Reason</span></label>
                    <div class="col-sm-8">
                       <span>{!!isset($leads_arr['retailer_reject_reason'])?$leads_arr['retailer_reject_reason']:'N/A' !!}</span>
                    </div>
                 </div>
              @endif   


          <div class="clearfix"></div>


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
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="sanssemibold">Tracking Number</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                    </div>
                    
                  </div>

                @endif 

             @if(isset($leads_arr['order_rejected_reason'])  && $leads_arr['order_rejected_reason'] !='')
                   <div class="row">
                      <label class="col-sm-4"><span class="lbl-font-wight">Vendor Rejection Reason</span></label>
                      <div class="col-sm-8">
                         <span>{!!isset($leads_arr['order_rejected_reason'])?$leads_arr['order_rejected_reason']:'N/A' !!}</span>
                      </div>
                   </div>
                @endif   


             @if(isset($leads_arr['promo_code']) && $leads_arr['promo_code'] != '0')
              <div class="row">
                <label class="col-sm-4"><span class="sanssemibold">Promo Code</span></label>
                <div class="col-sm-8">
                  <span class="label label-success">{{$leads_arr['promo_code'] or ''}}</span>
                </div>
              </div>
            @endif 

          <?php
            $accountHolder = '';
            if(isset($leads_arr['stripe_key_id']))
            {
              $getAccountDetails = get_stripe_account_details($leads_arr['stripe_key_id']);

              if($getAccountDetails)
              {
                $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
              }
            }
          ?>

            @if((isset($leads_arr['transaction_mapping']['transaction_id'])) && (isset($accountHolder) && $accountHolder != ''))
             <div class="row">
                 <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                 <div class="col-sm-12 col-md-12 col-lg-8">
                
                 <span>{{$leads_arr['transaction_mapping']['transaction_id']}}</span>
                
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

            @if(isset($split_order_arr) && sizeof($split_order_arr) > 0)
            <div class="row">
              <label class="col-sm-4"><span class="sanssemibold">Split Orders</span></label>
  
              @foreach($split_order_arr as $key => $split_order)
                <div class="col-sm-3">
                  <a href="{{url(config('app.project.representative_panel_slug'))}}/leads/view_lead_listing/{{base64_encode($split_order['id'])}}/{{base64_encode($split_order['order_no'])}}"><span class="label label-success">{{$split_order['order_no'] or 'N/A'}}</span></a>
                </div>
              @endforeach
                                </div> 

            @endif 

          </div>
        </div>

  
      </div>
   </div>

   @php

      $promo_total_amount = 0;
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
                  <th>Product Name</th>
                  <th>Brand Name</th>
                  <th>SKU No</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit Price (Wholesale)</th>
                  <th>Sub Total (Wholesale)</th>
                {{--   <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product Discount</th> --}}
                  <th>Total Amount</th>
               </tr>
               
               <?php


               $show_total_qty = 0;
               $show_total_wholesale_price = 0;
               $show_total_wholesale_subtotal= 0;
               $show_total_shipping_charges = 0;
               $show_total_shipping_discount= 0;
               $show_total_product_discount = 0;
               $show_total_amount = 0;
               $freeShippingAmt = 0;
               ?>
               @if(isset($leads_arr['order_details']) && count($leads_arr['order_details'])>0)
               @foreach($leads_arr['order_details'] as $lead)
            
               <tr>
                  <td>{{ $lead['product_details']['product_name'] or 'N/A' }}</td>

                  <td>{{$lead['product_details']['brand_details']['brand_name'] or 'N/A'}}</td>

                  <td>{{ $lead['sku'] or 'N/A' }}</td>

                 {{--  <td> <div class="truncate">{!! $lead['product_details']['description'] or 'N/A' !!}</div></td> --}}

                 <td>
                     @if(isset($lead['product_details']['description']) && strlen($lead['product_details']['description']) > 70 && $lead['product_details']['description']!='' )   
                        @php
                        $desc_html = $desc = "";
                        $desc_html = ($lead['product_details']['description']);
                        $desc =  substr(html_entity_decode($desc_html), 0, 70);
                        @endphp               
                       <p class="prod-desc"> {!!html_entity_decode($desc)!!}
                        <br>
                          <a class="readmorebtn" message="{{$lead['product_details']['description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else                      
                       {!!$lead['product_details']['description']!!}
                    @endif
                  </td>
                  
                  <td>{{ $lead['qty'] or 'N/A' }}

                    @php
                    if(isset($lead['qty'])){
                      $show_total_qty += $lead['qty'];
                    }
                    @endphp
                  </td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($lead['unit_wholsale_price'])?num_format($lead['unit_wholsale_price']) : 0.00 }}
                     @php
                      if(isset($lead['unit_wholsale_price'])){
                        $show_total_wholesale_price += $lead['unit_wholsale_price'];
                      }
                    @endphp
                  </td>
                  <td class="summmarytdsprice">
                     @php $sub_wholesale_price = $lead['qty'] * $lead['unit_wholsale_price'];

                          $total_amount = $sub_wholesale_price+$lead['product_shipping_charge']-$lead['shipping_charges_discount']-$lead['product_discount'];

                          $promo_total_amount += $total_amount;

                          $freeShippingAmt += $lead['product_shipping_charge'] -$lead['shipping_charges_discount'];

                          if(isset($sub_wholesale_price)){
                            $show_total_wholesale_subtotal += $sub_wholesale_price;
                          }

                      @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : "N/A"}}
                  </td>
                  {{-- <td class="summmarytdsprice">@if($lead['product_shipping_charge']>0)+@endif<span class="fa fa-dollar">{{isset($lead['product_shipping_charge'])?num_format($lead['product_shipping_charge']):'0.00'}}
                    @php
                      if(isset($lead['product_shipping_charge'])){
                        $show_total_shipping_charges += $lead['product_shipping_charge'];
                      }
                    @endphp
                  </td>

                  <td class="summmarytdsprice">@if($lead['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar">{{isset($lead['shipping_charges_discount'])?num_format($lead['shipping_charges_discount']):'0.00'}}
                     @php
                      if(isset($lead['shipping_charges_discount'])){
                        $show_total_shipping_discount += $lead['shipping_charges_discount'];
                      }
                    @endphp
                  </td>

                  <td class="summmarytdsprice">@if($lead['product_discount']>0)-@endif<span class="fa fa-dollar">{{ num_format($lead['product_discount']) }}
                    @php
                      if(isset($lead['product_discount'])){
                        $show_total_product_discount += $lead['product_discount'];
                      }
                    @endphp
                  </td> --}}

                  <td class="summmarytdsprice"><span class="fa fa-dollar">{{isset($total_amount)?num_format($total_amount):'0.00'}}
                    @php
                      if(isset($total_amount)){
                        $show_total_amount += $total_amount;
                      }
                    @endphp
                  </td>


               </tr>
               @endforeach
                <tr>
            
                  <th colspan="4" style="text-align:right">Total</th>
                  <th class="summmarytdsprice">{{ $show_total_qty or '0'}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_wholesale_price)?num_format($show_total_wholesale_price):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_wholesale_subtotal)?num_format($show_total_wholesale_subtotal):0.00}}</th>
                  {{-- <th class="summmarytdsprice">$ {{isset($show_total_shipping_charges)?num_format($show_total_shipping_charges):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_shipping_discount)?num_format($show_total_shipping_discount):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_product_discount)?num_format($show_total_product_discount):0.00}}</th> --}}
                  <th class="summmarytdsprice">$ {{isset($show_total_amount)?num_format($show_total_amount):0.00}}</th>
               </tr>
               @else
               <th></th>
               <td colspan="7">No Record Found</td>
               @endif

            </table>
         </div>


{{--           <div class="col-sm-6 pull-right viewsummaryamtbox">

            @if(isset($leads_arr['promo_code']) && $leads_arr['promo_code'] != '' )

                <div class="row">
                  <div class="left">
                    <h3>Total Amount :</h3>
                  </div>
                  <div class="right">
                    <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($promo_total_amount)?num_format($promo_total_amount) : 0.00 }}</span>
                  </div>
                </div>
                
            @if(isset($leads_arr['promotion_discount']) && $leads_arr['promotion_discount'] != '' && $leads_arr['promotion_discount'] != 0)

                <div class="row">
                  <div class="left">
                    <h3>Promotion Discount :</h3>
                  </div>
                  <div class="right">
                    <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['promotion_discount'])?num_format($leads_arr['promotion_discount']) : 0.00 }}</span>
                  </div>
                </div>

            @endif

            @if(isset($isFreeShipping) && $isFreeShipping == true)
                   
             <div class="row">
              <div class="left">
                <h3>Promotion(free shipping) :</h3>
              </div>
              <div class="right">
                <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($freeShippingAmt)?num_format($freeShippingAmt) : 0.00 }}</span>
              </div>
            </div>

          @endif
    
              <div class="row totalrow">
                <div class="left">
                  <h3>Total Amount (Wholesale) :</h3>
                </div>
                <div class="right">
                  <span> <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']):0.00}}</span>
                </div>
              </div>
          @else
            <div class="row totalrow">
              <div class="left">
                <h3>Total Amount (Wholesale) :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']):0.00 }}</span>
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
                   {{--  <div class="row">
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
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                </div> --}}

                 <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                </div>
                @else
                  {{-- <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div> --}}
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Wholesale) :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @endif  
                  
              </div>
         

         <!--      <div class="col-md-12">
               <div class="pull-right totl-grands" >

              @if(isset($leads_arr['promo_code']) && $leads_arr['promo_code'] != '' )

                <div class="viewtotal-bg">
                   <span>Total Amount :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($promo_total_amount)?num_format($promo_total_amount) : 0.00 }}
                  </span>
                </div>

                @if(isset($leads_arr['promotion_discount']) && $leads_arr['promotion_discount'] != '' && $leads_arr['promotion_discount'] != 0)

                  <div class="viewtotal-bg">
                     <span>Promotion Discount :</span>
                    <span class="viewspan">
                    - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['promotion_discount'])?num_format($leads_arr['promotion_discount']) : 0.00 }}
                  </span>
                  </div>
                @endif


                 @if(isset($isFreeShipping) && $isFreeShipping == true)
                  <div class="viewtotal-bg">
                     <span>Promotion(free shipping) :</span>
                    <span class="viewspan">
                    - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($freeShippingAmt)?num_format($freeShippingAmt) : 0.00 }}
                  </span>
                  </div>
                @endif



                <div class="viewtotal-bg border-h">
                   <span>Total Amount (Wholesale) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']):0.00}}
                </span>
                </div>

                @else

                  <span>Total Amount (Wholesale) :</span>
                  
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']):0.00 }}

                @endif
                  {{-- <span>Total Amount (Wholesale): </span>
                   <i class="fa fa-usd"></i>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) :0.00 }} --}}
                 
               </div>
            </div> -->
            <div class="clearfix"></div>
      </div>
   </div>                     @php

   if(isset($leads_arr['stripe_transaction_data']) && count($leads_arr['stripe_transaction_data']) > 0)
   {

    foreach ($leads_arr['stripe_transaction_data'] as $stripeData) 
    {      
        $userRole = Sentinel::findById($stripeData['received_by'])->roles;

        if($userRole[0]->slug == 'representative')
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
          if($userRole[0]->slug == 'representative')
          {
            $label = 'Representative';
          }
          
        }


   if(isset($trans_status) && $trans_status != '')
   {
    @endphp
        <div class="col-md-12">
          <div class="white-box">
             <label>
                <h3>Commission Details</h3>
             </label>
             <div class="table-responsive">
                
                <table class="table table-striped table-bordered view-porifile-table">
                   <tr>
                      <th>Order No</th>
                      <th>Order Amount</th>
                      <th>Admin Commission Percent</th>
                      <th>Admin Commission Amount</th>
                      <th>Representative Commission Percent</th>

                      @if($label == 'Representative')
                        <th>Amount Paid By Admin</th>
                      @endif   

                      <th>Status</th>
                      <th>Payment Account Holder</th>
                      <th>Transaction Id</th>

                   </tr>
                  
                    <?php
                      

                      $orderAmount = isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : 0.00;


                      $is_freeshipping = is_promocode_freeshipping(isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false);

                      if($is_freeshipping == true)
                      {
                          $orderShippingCharges = 0.00;
                      }
                      else
                      {
                          $orderShippingCharges = isset($leads_arr['total_shipping_charges'])?$leads_arr['total_shipping_charges']:0.00;
                      }


                      $excludingAmount = $orderAmount - $orderShippingCharges;
     
                     
                      if($label == 'Representative')
                      {
                        $repSaleCommission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission']:0;

                        $adminCommissionAmount = $excludingAmount * ($leads_arr['admin_commission'] / 100);

                        $repSaleAmount = $adminCommissionAmount * ($repSaleCommission / 100);

                      }

                      if($label == 'Representative')
                      {
                        $repSaleCommission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission']:0;

                        $adminCommissionAmount = $excludingAmount * ($leads_arr['admin_commission'] / 100);

                        $repSaleAmount = $adminCommissionAmount * ($repSaleCommission / 100);

                      }

                     ?>

                  <tr>
                    <td>{{isset($leads_arr['order_no'])?$leads_arr['order_no']:"N/A"}}</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($excludingAmount)?num_format($excludingAmount):0.00}}
                      <label class="shippingLabel">Excluded shipping costs</label>
                    </td>
                    
                    <td><i class="label" aria-hidden="true"></i>{{isset($leads_arr['admin_commission'])?num_format($leads_arr['admin_commission']):'N/A'}} %</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($adminCommissionAmount)?num_format($adminCommissionAmount):0.00}}</td>

                      
                        <td><i class="label" aria-hidden="true"></i>{{isset($leads_arr['admin_commission'])?num_format($leads_arr['rep_sales_commission']):'N/A'}} %</td>

                        
                        <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($repSaleAmount)?num_format($repSaleAmount):0.00}}</td>


                    <td><span class="label label-success">{{isset($status)?$status:'Pending'}}</span></td>
                    <?php

                        $accountHolder = '';
                        if(isset($leads_arr['transfer_commission_stripe_key_id']))
                        {
                          $getAccountDetails = get_stripe_account_details($leads_arr['transfer_commission_stripe_key_id']);

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
   <div class="col-md-12">
      <div class="form-group row">
            <div class="commonbackbtn">
               <a class="btn btn-success pull-right btn-rounded btn-outline waves-effect waves-light pull-left" href="{{$url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
      </div>
   </div>
</div>



@include('product_description_popup')
<script type="text/javascript">
   
$( document ).ready(function() {
   
   $("#confirmbtn").click(function(){
     var confirmurl = $("#confirmbtn").data("value");
     

    swal({
    title: "Need Confirmation",
    text: "Are you sure? Do you want to confirm this order.",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "OK",
    cancelButtonText: "Cancel",
    closeOnConfirm: true,
    closeOnCancel: true,
  },
  function(isConfirm){
    if (isConfirm) {
      $.ajax({
        url:confirmurl,
        method:"GET",
        
        success:function(response) {
          
          if(response.status == 'success')
          {
             swal({
                    title: "Success",
                    text: response.description,
                    type: response.status
              }, function() {
                  window.location = response.url;
              });
          }
          else
          {
            swal("Error",response.description,response.status)
          }
        }
      })
    }
  }
);
  
});
    
    
});

   
</script>
<!-- END Main Content -->
@stop