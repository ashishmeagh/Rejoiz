@extends('sales_manager.layout.master')                
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
   .row{
   padding-bottom:20px; 
   }
   .col-left-vw{font-weight:600;}
</style>

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.sales_manager_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">My {{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
</div>
@include('admin.layout._operation_status')  
<div class="row">
   <div class="col-md-12">
      <div class="white-box small-space-nw">
         <div class="main-nm-retailer space-bottom">
         </div>
         <input type="hidden" name="order_id" id="order_id" value="{{$enquiry_arr['id'] or 0}}">
         <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-6">
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Order No.</span></label>
                  <div class="col-sm-8">
                     <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Retailer</span></label>
                  <div class="col-sm-8">
                     <span> {{isset($enquiry_arr['retailer_user_details']['retailer_details']['store_name'])?ucfirst($enquiry_arr['retailer_user_details']['retailer_details']['store_name']):"N/A"}}</span>
                  </div>
               </div>

               @if(isset($enquiry_arr['maker_details']['company_name']) && $enquiry_arr['maker_details']['company_name'] != "")
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw"> Vendor </span></label>
                  <div class="col-sm-8">
                     <span> {{isset($enquiry_arr['maker_details']['company_name'])?ucfirst($enquiry_arr['maker_details']['company_name']):"N/A"}}</span>
                  </div>
               </div>
               @endif

               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Order Date</span></label>
                  <div class="col-sm-8">
                     <span>
                     {{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Total Amount (Wholesale)</span></label>
                  <div class="col-sm-8">
                     <span>
                     ${{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}
                     </span>
                  </div>
               </div>
               @php
               $payment_status = isset($enquiry_arr['transaction_mapping']['transaction_status'])?$enquiry_arr['transaction_mapping']['transaction_status'] : 'N/A';
               @endphp
            </div>
            <div class="col-sm-12 col-md-12 col-lg-6">
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Shipping Address</span></label>
                  <div class="col-sm-8">
                     <span>
            
                      {{isset($enquiry_arr['address_details']['ship_street_address'])?$enquiry_arr['address_details']['ship_street_address'].',':''}} 

                      {{isset($enquiry_arr['address_details']['ship_suit_apt'])?$enquiry_arr['address_details']['ship_suit_apt'].',':''}} 

                      {{isset($enquiry_arr['address_details']['ship_city'])?$enquiry_arr['address_details']['ship_city'].',':''}} 

                      {{isset($enquiry_arr['address_details']['ship_state'])?$enquiry_arr['address_details']['ship_state'].',':''}}

                      {{isset($enquiry_arr['address_details']['ship_country'])?get_country($enquiry_arr['address_details']['ship_country']).',':''}} 
                                
                      {{isset($enquiry_arr['address_details']['ship_zip_code'])?$enquiry_arr['address_details']['ship_zip_code'].',':''}}

                      Mobile.No: {{isset($enquiry_arr['address_details']['ship_mobile_no'])?$enquiry_arr['address_details']['ship_mobile_no']:''}}

                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Billing Address</span></label>
                  <div class="col-sm-8">
                     <span>
              
                    {{isset($enquiry_arr['address_details']['bill_street_address'])?$enquiry_arr['address_details']['bill_street_address'].',':''}} 

                    {{isset($enquiry_arr['address_details']['bill_suit_apt'])?$enquiry_arr['address_details']['bill_suit_apt'].',':''}} 

                    {{isset($enquiry_arr['address_details']['bill_city'])?$enquiry_arr['address_details']['bill_city'].',':''}} 

                    {{isset($enquiry_arr['address_details']['bill_state'])?$enquiry_arr['address_details']['bill_state'].',':''}}

                    {{isset($enquiry_arr['address_details']['bill_country'])?get_country($enquiry_arr['address_details']['bill_country']).',':''}}

                    {{isset($enquiry_arr['address_details']['bill_zip_code'])?$enquiry_arr['address_details']['bill_zip_code'].',':''}}

                    Mobile.No: {{isset($enquiry_arr['address_details']['bill_mobile_no'])?$enquiry_arr['address_details']['bill_mobile_no']:''}}
                    
                     </span>
                  </div>
               </div>

              @if(isset($enquiry_arr['order_cancel_status']) && $enquiry_arr['order_cancel_status'] == "2")
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="semibold-mkr">Order Status</span></label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">   
                    <label class="label label-success">Cancelled</label>
                    </div>
                  </div>
                @endif   
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Payment Status</span></label>
                  <div class="col-sm-8">
                     <span>
                     @if($payment_status == 2) 
                     <span class="label label-success">Paid</span>
                     @elseif($payment_status == 1)
                     <span class="label label-warning">Pending</span>
                     @elseif($payment_status == 3)
                     <span class="label label-danger">Failed</span>
                     @else
                     <span class="label label-warning">Pending</span>
                     @endif
                     </span>
                  </div>
               </div>


              @if($payment_status==2)   

                <div class="row">
                  <label class="col-sm-4"><span class="sanssemibold">Refund Status</span></label>
                    <div class="col-sm-8">
                     <?php
                       $refund_status = isset($enquiry_arr['refund_status'])? $enquiry_arr['refund_status']:'0';
                       $onclick = "return false;";
                       switch($refund_status)
                       {
                         case '1': $spanLabel_class="success"; 
                                   $spanLabel="Paid";  break;
                         default :
                                    $spanLabel_class="warning"; 
                                    $spanLabel="Pending";  
                                   
                                    break;
                       }
                   ?>
                   <span class="label label-{{$spanLabel_class}}" onclick="{{$onclick}}" style="cursor: pointer;">{{$spanLabel}}</span>
                    </div>
                 </div>
              @endif 
                
               @if(isset($enquiry_arr['promo_code'])  && $enquiry_arr['promo_code'] != '')
               <div class="row">
                  <label class="col-sm-4"><span class="col-left-vw">Promo Code</span></label>
                  <div class="col-sm-8">
                     <span class="label label-success">{{$enquiry_arr['promo_code'] or ''}}</span>
                  </div>
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
                  <th>Product</th>
                  <th>Brand</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th>Unit Price (Wholesale)</th>
                  <th>Sub Total (Wholesale)</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product  Discount</th>
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

               @if(isset($enquiry_arr['leads_details']) && count($enquiry_arr['leads_details'])>0)
               @foreach($enquiry_arr['leads_details'] as $lead)
               <tr>
                  <td>{{isset($lead['product_details']['product_name'])?$lead['product_details']['product_name']:"N/A"}}</td>
                  <td>
                     {{isset($lead['product_details']['brand_details']['brand_name'])?$lead['product_details']['brand_details']['brand_name']:"N/A"}}
                  </td>
                  {{-- @if(isset($lead['product_details']['description']) && strlen($lead['product_details']['description']) > 70)
                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">
                  <td>
                     <div class="truncate">{!!$lead['product_details']['description']!!}</div>
                  </td>
                  @else
                  <td>{!!$lead['product_details']['description']!!}</td>
                  @endif --}}

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
                  <td>{{isset($lead['sku'])?$lead['sku']:""}}</td>

                  <td>{{ $lead['qty'] or 'N/A' }}
                    @php
                      if(isset($lead['qty'])){
                        $show_total_qty += $lead['qty'];
                      }
                    @endphp
                  </td>


                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$lead['unit_wholsale_price'] or 'N/A' }}

                    @php
                      if(isset($lead['unit_wholsale_price'])){
                        $show_total_wholesale_price += $lead['unit_wholsale_price'];
                      }
                    @endphp

                  </td>


                  <td>
                     @php 
                     $sub_wholesale_price = $lead['qty'] * $lead['unit_wholsale_price'];
                     $sub_wholesale_total = $sub_wholesale_price+$lead['product_shipping_charge']-$lead['shipping_charges_discount']-$lead['product_discount'];
                     $total_amount += $sub_wholesale_price;


                      if(isset($sub_wholesale_price)){
                            $show_total_wholesale_subtotal += $sub_wholesale_price;
                      }


                     @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : 0.00}}
                  </td>
                  <td class="summmarytdsprice">@if($lead['product_shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($lead['product_shipping_charge'])?num_format($lead['product_shipping_charge']):0.00}}
                    
                    @php
                      if(isset($lead['product_shipping_charge'])){
                        $show_total_shipping_charges += $lead['product_shipping_charge'];
                      }
                    @endphp

                  </td>



                  <td class="summmarytdsprice">@if($lead['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($lead['shipping_charges_discount'])?num_format($lead['shipping_charges_discount']):0.00}}
                     @php
                      if(isset($lead['shipping_charges_discount'])){
                        $show_total_shipping_discount += $lead['shipping_charges_discount'];
                      }
                    @endphp


                  </td>



                  <td class="summmarytdsprice">@if($lead['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($lead['product_discount'])?num_format($lead['product_discount']):0.00}}
                      @php
                      if(isset($lead['product_discount'])){
                        $show_total_product_discount += $lead['product_discount'];
                      }
                      @endphp
                  </td>



                  <td class="summmarytdsprice">
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_total)?num_format($sub_wholesale_total) : 0.00}}

                     @php
                      if(isset($sub_wholesale_total)){
                        $show_total_amount += $sub_wholesale_total;
                      }
                    @endphp
                  </td>
               </tr>
               @endforeach

                <tr>
              
                  <th colspan="4" style="text-align:right">Total:</th>
                  <th class="summmarytdsprice">{{ $show_total_qty or '0'}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_wholesale_price)?num_format($show_total_wholesale_price):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_wholesale_subtotal)?num_format($show_total_wholesale_subtotal):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_shipping_charges)?num_format($show_total_shipping_charges):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_shipping_discount)?num_format($show_total_shipping_discount):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_product_discount)?num_format($show_total_product_discount):0.00}}</th>
                  <th class="summmarytdsprice">$ {{isset($show_total_amount)?num_format($show_total_amount):0.00}}</th>
               </tr>

               @else
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


         </div>
         <div class="clearfix"></div>
      </div>
   </div>
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
</div>

@include('product_description_popup')
<script>
   var module_url_path = '{{$module_url_path or ''}}';  
   var token           =  $('input[name="csrfToken"]').attr('value');
   var order_id        =  $('#order_id').val();
   
   
  $(document).ready(function(){
     
  });
   

   
 
     
</script>
@stop