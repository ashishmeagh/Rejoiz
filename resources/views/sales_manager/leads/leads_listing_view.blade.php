@extends('sales_manager.layout.master')  
@section('main_content')

<style>
.row{
     padding-bottom: 20px;
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
        
         if($is_rep_sales_order == '1'){
            $confirm_order_title  = 'Orders by Reps';
            $url_path = $module_url_path.'/reps';
         }
         

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
         <li><a href="{{ url(config('app.project.sales_manager_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$url_path}}">{{$confirm_order_title or ''}}</a></li>
         <li class="active">Order Details</li>
      </ol>
   </div>
</div>
<div class="row">
   @include('admin.layout._operation_status')  
   <div class="col-md-12">
      <div class="white-box small-space-nw">
         @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
         <a href="{{url(config('app.project.sales_manager_panel_slug'))}}/leads/view_lead_listing/{{base64_encode($main_split_order_no['id'])}}/{{base64_encode($main_split_order_no['order_no'])}}" class="btn btn-inverse pull-right mb-4">This order is split from : {{$main_split_order_no['order_no']}}</a>
         @endif
         @php 
         if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='2')
         {
         $status = 'Pending';
         }
         elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='3'){
         $status = 'Reject';
         }else{
         $status = 'Approved';
         }
         @endphp
         @if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm']=='0')         
         <a class="btn btn-inverse waves-effect waves-light pull-right  small-btn-padding"
            id = "confirmbtn" data-value="{{$module_url_path.'/update_lead_listing/'.base64_encode($leads_arr['id'])}}">Confirm Order</a> 
         
         @endif
         <div class="clearfix"></div>
    <section>
            <div class="container">
               <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Order No.</span></label>
                              <div class="col-sm-8">
                                 <span>{{ $leads_arr['order_no'] or 'N/A' }}</span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Order Date</span></label>
                              <div class="col-sm-8">
                                 <span> {{ isset($leads_arr['created_at'])?us_date_format($leads_arr['created_at']):'N/A' }}</span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Customer</span></label>
                              <div class="col-sm-8">
                                 <span>{{isset($leads_arr['retailer_user_details']['retailer_details']['store_name'])?$leads_arr['retailer_user_details']['retailer_details']['store_name']:"N/A"}}</span>
                              </div>
                           </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Vendor</span></label>
                              <div class="col-sm-8">
                                 <span>{{isset($leads_arr['maker_details']['company_name'])?$leads_arr['maker_details']['company_name']:'N/A'}}</span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Total Amount</span></label>
                              <div class="col-sm-8">
                                 <span><span class="fa fa-dollar"></span>{{isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']):"0.00"}}</span>
                              </div>
                           </div>
                           
                        </div>

                        @if(isset($leads_arr['order_cancel_status']) && $leads_arr['order_cancel_status'] == "2")
                          <div class="row">
                            <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="semibold-mkr">Order Status</span></label>
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                            <label class="label label-success">Canceled</label>
                            </div>
                          </div>

                        @endif

                        <div class="col-sm-12 col-md-12 col-lg-6">
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Customer Payment Status</span></label>
                              <div class="col-sm-8">
                                 <span>@php 
                                 $status = isset($leads_arr['transaction_mapping']['transaction_status'])?get_payment_status($leads_arr['transaction_mapping']['transaction_status']):'';
                                 $status = isset($leads_arr['transaction_mapping_details']['transaction_status'])?get_payment_status($leads_arr['transaction_mapping_details']['transaction_status']):'';@endphp
                                 @if($status=="Pending")
                                 <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>
                                 @elseif($status=="Paid")
                                 <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>
                                 @elseif($status=="Failed")
                                 <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>
                                 @elseif($status =="")
                                 <span class="label label-warning">Pending</span>
                                 @else
                                 {{$status or ''}}
                                 @endif
                                 </span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Customer Approval</span></label>
                              <div class="col-sm-8">
                                 <span>@if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 0)
                                 <span class="label label-warning">Pending</span>
                                 @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 1)
                                 <span class="label label-success">Approved</span>
                                 @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 2)
                                 <span class="label label-warning">Pending</span>
                                 @elseif(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 3)
                                 <span class="label label-warning">Rejected</span>
                                 @else
                                 <span class="label label-warning">Pending</span>
                                 @endif</span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Shipping Status</span></label>
                              <div class="col-sm-8">
                                 <span>                      <?php $status = isset($leads_arr['ship_status'])?get_order_status($leads_arr['ship_status']):'N/A'?>
                                 @if($status=="Pending")
                                 <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>
                                 @elseif($status=="Shipped")
                                 <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>
                                 @elseif($status=="Failed")
                                 <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>
                                 @else
                                 {{$status or 'N/A'}}
                                 @endif</span>
                              </div>
                           </div>
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Payment Term</span></label>
                              <div class="col-sm-8">
                                 <span class="label label-success">{{ isset($leads_arr['payment_term']) && $leads_arr['payment_term'] != '' ?$leads_arr['payment_term']:'N/A'}}</span>
                              </div>
                           </div>

                        @if(isset($leads_arr['order_rejected_reason'])  && $leads_arr['order_rejected_reason'] !='')
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Vendor Rejection Reason</span></label>
                              <div class="col-sm-8">
                                 <span>{!!isset($leads_arr['order_rejected_reason'])?$leads_arr['order_rejected_reason']:'N/A' !!}</span>
                              </div>
                           </div>
                        @endif   


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
                                 <label class="col-sm-4"><span class="lbl-font-wight">Commission Status</span></label>
                                 <div class="col-sm-8">
                                    <span class="label label-success">{{$status}}</span>
                                 </div> 

                              </div>
                           @endif
                           
                        </div>

                        
                        <div class="col-sm-12 col-md-12 col-lg-6">
                           @if(isset($leads_arr['payment_term']) && $leads_arr['payment_term']!="Online/Credit") 
                          <!--  <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Payment Due Date</span></label>
                              <div class="col-sm-8">
                                 <span> {{isset($leads_arr['payment_due_date'])?us_date_format($leads_arr['payment_due_date']):'N/A' }}</span>
                              </div>
                           </div> -->
                           @endif

                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Shipping Address</span></label>
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
                              <label class="col-sm-4"><span class="lbl-font-wight">Billing Address</span></label>
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
                              <label class="col-sm-4"><span class="lbl-font-wight">Customer Rejection Reason</span></label>
                              <div class="col-sm-8">
                                 <span>{!!isset($leads_arr['retailer_reject_reason'])?$leads_arr['retailer_reject_reason']:'N/A' !!}</span>
                              </div>
                           </div>
                        @endif   

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
                                <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="lbl-font-wight">Tracking Number</span></label>
                                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                                  <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                                </div>
                              </div>

                           @endif 

                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6">
                           @if(isset($leads_arr['promo_code']) && $leads_arr['promo_code'] != '0')
                           <div class="row">
                              <label class="col-sm-4"><span class="lbl-font-wight">Promo Code</span></label>
                              <div class="col-sm-8">
                                 <span>{{$leads_arr['promo_code'] or ''}}</span>
                              </div>
                           </div>
                           @endif
                          {{--  <div class="row">
                              <label class="col-sm-4"></label>
                              <div class="col-sm-8">
                                 <span>  
                                 @if(isset($leads_arr['is_confirm']) && $leads_arr['is_confirm'] == 1)
                                 @php  
                                 Session::put('representative_order_id',$leads_arr['id']);
                                 @endphp


                                 @if($count == 0)  
                                 @if($leads_arr['payment_term'] == '')
                                 <a class="btn blueclr pull-right" href="{{ $module_url_path.'/net_30_payment/'.base64_encode($leads_arr['id'])}}">Net 30</a>
                                 @endif
                                 @endif
                                 @endif
                                 </span>
                              </div>
                           </div> --}}


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
                              <label class="col-sm-4"><span class="lbl-font-wight">Split Orders</span></label>
                              @foreach($split_order_arr as $key => $split_order)
                              <div class="col-sm-3">
                                 <span><a href="{{url(config('app.project.sales_manager_panel_slug'))}}/leads/view_lead_listing/{{base64_encode($split_order['id'])}}/{{base64_encode($split_order['order_no'])}}" ><span class="label label-success"> {{$split_order['order_no'] or 'N/A'}}</span></a>
                                 </span>
                              </div>
                              @endforeach
                           </div>
                           @endif
                        </div>
                  </div>
            </div>
         </section>
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
                  <th>Product</th>
                  <th>Brand</th>
                  <th>SKU No</th>
                  <th>Description</th>
                  <th>Qty</th>
                  <th>Unit Price (Wholesale)</th>
                  <th>Sub Total (Wholesale)</th>
                  {{-- <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product Discount</th> --}}
                  <th>Total Amount</th>
               </tr>

               <?php 
                  $grand_total_amount = 0;
                  $tot_qty = 0;
                  
                  $sub_tot_wholesale = 0.00;       
                  $tot_unit_price_wholesale = 0.00;
                  $tot_sub_tot_wholesale = 0.00;
                  $tot_shipping_charges = 0.00;
                  $tot_shipping_discount = 0.00;
                  $tot_pro_dis = 0.00;
                  $tot_amt_column = 0.00;
              ?> 

               @if(isset($leads_arr['order_details']) && count($leads_arr['order_details'])>0)
               @foreach($leads_arr['order_details'] as $lead)
               <tr>
                  <td>{{ $lead['product_details']['product_name'] or 'N/A' }}</td>
                  <td>{{$lead['product_details']['brand_details']['brand_name'] or 'N/A'}}</td>
                  <td>{{ $lead['sku'] or 'N/A' }}</td>
                  {{-- <td>
                     <div class="truncate">{!! $lead['product_details']['description'] or 'N/A' !!}</div>
                  </td> --}}
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
                  <td>{{ $lead['qty'] or 'N/A' }}</td>

                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($lead['unit_wholsale_price'])?num_format($lead['unit_wholsale_price']):0.00}}</td>
                  <td class="summmarytdsprice">
                     @php $sub_wholesale_price = $lead['qty'] * $lead['unit_wholsale_price'];
                     $total_amount = $sub_wholesale_price+$lead['product_shipping_charge']-$lead['shipping_charges_discount']-$lead['product_discount'];
                     $promo_total_amount += $total_amount;

                     $shipCharge = isset($lead['product_shipping_charge'])?num_format($lead['product_shipping_charge']):0.00;

                      $shipDiscount = isset($lead['shipping_charges_discount'])?num_format($lead['shipping_charges_discount']):0.00;
                     @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price):0.00}}
                  </td>
                 {{--  <td class="summmarytdsprice">@if($shipCharge>0)+@endif<span class="fa fa-dollar">{{$shipCharge}}</td>
                  <td class="summmarytdsprice">@if($shipDiscount>0)-@endif<span class="fa fa-dollar">{{$shipDiscount}}</td>
                  <td class="summmarytdsprice">@if($lead['product_discount']>0)-@endif<span class="fa fa-dollar">{{isset($lead['product_discount'])?num_format($lead['product_discount']):0.00}}</td> --}}
                  <td class="summmarytdsprice"><span class="fa fa-dollar">{{isset($total_amount)?num_format($total_amount):0.00}}</td>
               </tr>
               <?php
                $tot_qty+= (float)$lead['qty'];
                $tot_unit_price_wholesale+=(float)$lead['unit_wholsale_price'];
                $tot_sub_tot_wholesale+=(float)$sub_wholesale_price;
                $tot_shipping_charges+=(float)$lead['product_shipping_charge'];
                $tot_shipping_discount+=(float)$lead['shipping_charges_discount'];
                $tot_pro_dis+=(float)$lead['product_discount'];
                $tot_amt_column+=(float)$total_amount;
              ?>
               @endforeach
                <tr>
                    
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total :</td>
                    <td>{{$tot_qty}}</td> 
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_unit_price_wholesale)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_sub_tot_wholesale)}}</td>
                    {{-- <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td> --}}
                    <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_amt_column)}}</td>
                </tr>
               @else
               <th></th>
               <td colspan="7">No Record Found</td>
               @endif
            </table>
         </div>

         <div class="col-md-12 p-0">

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

       <!--      <div class="pull-right totl-grands" >
               @if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges']))) 
               <div class="viewtotal-bg">
                  <span>Total Amount :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}
                  </span>
               </div>

               @if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0)
               <div class="viewtotal-bg">
                  <span>Promotion Discount :</span>
                  <span class="viewspan">
                  - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00 }}
                  </span>
               </div>
               @endif

               @if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)
               <div class="viewtotal-bg">
                  <span>Promotion(Free shipping) :</span>
                  <span class="viewspan">
                  - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}
                  </span>
               </div>
               @endif


               <div class="viewtotal-bg border-h">
                  <span>Total Amount (Wholesale) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}
                  </span>
               </div>
               @else
               <span>Total Amount (Wholesale) :</span>
               <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}
               @endif

            </div> -->
         </div>
         <div class="clearfix"></div>
      </div>
   </div>

                       @php

   if(isset($leads_arr['stripe_transaction_data']) && count($leads_arr['stripe_transaction_data']) > 0)
   {

    foreach ($leads_arr['stripe_transaction_data'] as $stripeData) 
    {      
        $userRole = Sentinel::findById($stripeData['received_by'])->roles;

        if($userRole[0]->slug == 'sales_manager' || $userRole[0]->slug == 'representative')
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
          if($userRole[0]->slug == 'sales_manager')
          {
            $label = 'Sales Manager';
          }

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
                      <th>{{$label}} Commission Percent</th>
                      @if($label == 'Sales Manager' || $label == 'Representative')
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

                      $repSaleCommission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission']:0;

                      $adminCommissionAmount = $excludingAmount * ($leads_arr['admin_commission'] / 100);

                      $repSaleAmount = $adminCommissionAmount * ($repSaleCommission / 100);

                      $repSaleCommission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission']:0;

                      $adminCommissionAmount = $excludingAmount * ($leads_arr['admin_commission'] / 100);

                      $repSaleAmount = $adminCommissionAmount * ($repSaleCommission / 100);                   

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
      <div class="text-right">
         <a class="btn btn-inverse waves-effect waves-light" href="{{$url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
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
             beforeSend: function() {
                showProcessingOverlay();
            },
           
           success:function(response) {
                hideProcessingOverlay();

              swal({
               title: "Success",
               text: response.description,
               type: response.status
           }, function() {
               window.location = response.url;
           });
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