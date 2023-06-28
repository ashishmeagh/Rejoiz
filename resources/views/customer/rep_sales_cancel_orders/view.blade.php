@extends('retailer.layout.master')                
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
</style>
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
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
            <div class="col-sm-6">
               <div class="row">
                  <label class="col-sm-4">Order No.</label>
                  <div class="col-sm-8">
                     <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4">Retailer</label>
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
                  <label class="col-sm-4">Order Date</label>
                  <div class="col-sm-8">
                     <span>
                     {{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4">Grand Amount (Wholesale)</label>
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
            <div class="col-sm-6">
               <div class="row">
                  <label class="col-sm-4">Shipping Address</label>
                  <div class="col-sm-8">
                     <span>
                     {{isset($enquiry_arr['address_details']['ship_complete_address'])?$enquiry_arr['address_details']['ship_complete_address'] :'N/A'}}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4">Billing Address</label>
                  <div class="col-sm-8">
                     <span>
                     {{isset($enquiry_arr['address_details']['bill_complete_address']) ?$enquiry_arr['address_details']['bill_complete_address']:'N/A'}}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4">Payment Status</label>
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
               @if(isset($enquiry_arr['promo_code'])  && $enquiry_arr['promo_code'] != '')
               <div class="row">
                  <label class="col-sm-4">Promo Code</label>
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
                  <th>Unit Wholesale Price</th>
                  <th>Wholesale Sub Total</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product  Discount</th>
                  <th>Total Amount</th>
               </tr>
               @if(isset($enquiry_arr['leads_details']) && count($enquiry_arr['leads_details'])>0)
               @foreach($enquiry_arr['leads_details'] as $lead)
               <tr>
                  <td>{{isset($lead['product_details']['product_name'])?$lead['product_details']['product_name']:"N/A"}}</td>
                  <td>
                     {{isset($lead['product_details']['brand_details']['brand_name'])?$lead['product_details']['brand_details']['brand_name']:"N/A"}}
                  </td>
                  @if(isset($lead['product_details']['description']) && strlen($lead['product_details']['description']) > 70)
                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">
                  <td>
                     <div class="truncate">{!!$lead['product_details']['description']!!}</div>
                  </td>
                  @else
                  <td>{!!$lead['product_details']['description']!!}</td>
                  @endif
                  <td>{{isset($lead['sku'])?$lead['sku']:""}}</td>
                  <td>{{ $lead['qty'] or 'N/A' }}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$lead['product_details']['unit_wholsale_price'] or 'N/A' }}</td>
                  <td class="summmarytdsprice">
                     @php 
                     $sub_wholesale_price = $lead['qty'] * $lead['product_details']['unit_wholsale_price'];
                     $sub_wholesale_total = $sub_wholesale_price+$lead['product_shipping_charge']-$lead['shipping_charges_discount']-$lead['product_discount'];
                     $total_amount += $sub_wholesale_price;
                     @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : 0.00}}
                  </td>
                  <td class="summmarytdsprice">@if($lead['product_shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($lead['product_shipping_charge'])?num_format($lead['product_shipping_charge']):0.00}}</td>
                  <td class="summmarytdsprice">@if($lead['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($lead['shipping_charges_discount'])?num_format($lead['shipping_charges_discount']):0.00}}</td>
                  <td>@if($lead['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($lead['product_discount'])?num_format($lead['product_discount']):0.00}}</td>
                  <td class="summmarytdsprice">
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_total)?num_format($sub_wholesale_total) : 0.00}}
                  </td>
               </tr>
               @endforeach
               @else
               <td colspan="7">No Record Found</td>
               @endif
            </table>
         </div>
         <div class="col-md-12">
          
            <div class="pull-right totl-grands">
               @if(isset($enquiry_arr['promotion_discount']) && $enquiry_arr['promotion_discount'] != '' && $enquiry_arr['promotion_discount'] != 0)
               <div class="viewtotal-bg">
                  <span>Total Amount :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($total_amount)?num_format($total_amount) : '0.00' }}
                  </span>
               </div>
               <div class="viewtotal-bg">
                  <span>Promotion Discount :</span>
                  <span class="viewspan">
                  - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['promotion_discount'])?num_format($enquiry_arr['promotion_discount']) : 'N/A' }}
                  </span>
               </div>
               <div class="viewtotal-bg border-h">
                  <span>Grand Amount (Wholesale) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}
                  </span>
               </div>
               @else
               <span>Grand Amount (Wholesale) :</span>
               <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}
               @endif
            </div>
         </div>
         <div class="clearfix"></div>
      </div>
   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   var module_url_path = '{{$module_url_path or ''}}';  
   var token           =  $('input[name="csrfToken"]').attr('value');
   var order_id        =  $('#order_id').val();
   
   
  $(document).ready(function(){
     
  });
   

   
 
     
</script>
@stop