@extends('influencer.layout.master')                
@section('main_content')

<style type="text/css">
.main-nm-retailer-right {
    margin-left: 110px;
}
.main-nm-retailer-right {
    margin-left: 110px;
}
.main-nm-retailer { margin-top: 0px;
    position: relative; float: right;
}
.main-nm-retailer-left {
    position: absolute; font-weight: 600;
    left: 0;
}
.row{
     padding-bottom: 20px;
  }
</style>

<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   @include('influencer.layout._operation_status')  
   <div class="col-md-12">
      <div class="white-box small-space-nw">

        <div class="main-nm-retailer space-bottom">
          @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
            <a class="btn btn-inverse" href="{{url('/')}}/admin/customer_orders/view/{{base64_encode($main_split_order_no['id'])}}">This order is split from : {{$main_split_order_no['order_no']}}</a>
          
          @endif
        </div>

        <div class="row">
          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Order No.</label>
              <div class="col-sm-8">
                <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Vendor</label>
              <div class="col-sm-8">
                <span>{{ $enquiry_arr['maker_data']['company_name'] or 'N/A' }}</span>
              </div>
            </div>


            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Order Date</label>
              <div class="col-sm-8">
                <span>
                  <?php $us_date = us_date_format($enquiry_arr['created_at']); ?>
                  {{ isset($enquiry_arr['created_at'])?$us_date:'N/A' }}
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Shipping Status</label>
              <div class="col-sm-8">
                <span>
                  @if(isset($enquiry_arr['ship_status']) && $enquiry_arr['ship_status']==0)
                      <span class="label label-warning">Pending</span>
                   @elseif(isset($enquiry_arr['ship_status']) &&$enquiry_arr['ship_status']==1)
                      <span class="label label-success">Shipped</span>
                   @elseif(isset($enquiry_arr['ship_status']) &&$enquiry_arr['ship_status']==2)
                      <span class="label label-danger">Failed</span>
                   @else
                   <span class="label label-danger">{{isset($enquiry_arr['ship_status'])?$enquiry_arr['ship_status']:''}}</span>
                   @endif
                </span>
         
              </div>
            </div>

            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Payment Status</label>
              <div class="col-sm-8">
                <span>
                  @if(isset($enquiry_arr['transaction_mapping']['transaction_status']) && $enquiry_arr['transaction_mapping']['transaction_status']==1)
                    <span class="label label-warning">Pending</span>

                  @elseif(isset($enquiry_arr['transaction_mapping']['transaction_status']) && $enquiry_arr['transaction_mapping']['transaction_status']==2)
                    <span class="label label-success">Paid</span>

                  @elseif(isset($enquiry_arr['transaction_mapping']['transaction_status']) && $enquiry_arr['transaction_mapping']['transaction_status']==3)
                    <span class="label label-danger">Failed</span>
                  @else
                    <span class="label label-warning">Pending</span>
                  @endif
                </span>
              </div>
            </div>

           

            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Payment Term</label>
              <div class="col-sm-8">
                @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
                 <span class="label label-danger">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                 @else
                  <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                 @endif
              </div>
            </div>
    

            @php

                $trans_status = isset($enquiry_arr['stripe_transaction_detail']['status'])?$enquiry_arr['stripe_transaction_detail']['status']:'';
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
            @if(isset($enquiry_arr['stripe_transaction_detail']) && isset($enquiry_arr['stripe_transaction_detail'])!=null) 
              <div class="row">
                  <label class="col-sm-4">Vendor Commission Status</label>
                <div class="col-sm-8">
                  <span class="label label-success">{{$status}}</span>
                </div> 
              </div>

            @endif  

          </div>

          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Customer</label>
              <div class="col-sm-8">
             
                <span>{{ isset($enquiry_arr['user_details']['first_name'])?$enquiry_arr['user_details']['first_name']." ".$enquiry_arr['user_details']['last_name']:'N/A' }}</span>
                
              </div>
            </div>

           

            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Total Amount (Retail)</label>
              <div class="col-sm-8">
                <span>${{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : '0.00' }}</span>
              </div>
            </div>

          
             <div class="row">
              <label class="col-sm-4 commonlabel_bold">Shipping Address</label>
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
              <div class="col-sm-8">
                <span>
                  {{$shipping_address}}
                </span>
              </div>
            </div>
            @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
              <div class="row">
                <label class="col-sm-4 commonlabel_bold">Payment Due Date</label>
                <div class="col-sm-8">
                  <span>{{isset($enquiry_arr['payment_due_date'])?us_date_format($enquiry_arr['payment_due_date']):'N/A' }}</span> 
                </div>
              </div>
            @endif  

            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Billing Address</label>
              <div class="col-sm-8">
                <span>{{$billing_address}}</span>
              </div>
            </div>
             @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')

              <div class="row">
                <label class="col-sm-4">Rejection Reason</label>
                <div class="col-sm-8">
                  
                  <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
                  
                </div>
              </div>

            @endif

            @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '')
              <div class="row">
                <label class="col-sm-4 commonlabel_bold">Promo Code</label>
                <div class="col-sm-8">
                  <span class="label label-success">{{$enquiry_arr['promo_code'] or ''}}</span>
                </div>
              </div>
            @endif  

            @if(isset($split_order_arr) && sizeof($split_order_arr) > 0)
            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Split Orders</label>
  
              @foreach($split_order_arr as $key => $split_order)
                <div class="col-sm-3">
                
                  <a href="{{url('/')}}/admin/customer_orders/view/{{isset($split_order['id'])?base64_encode($split_order['id']):'0'}}"><span class="label label-success">{{$split_order['order_no'] or 'N/A'}}</span> </a>
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
                  <th>Order No</th>
                  <th>Product Name</th>
                  <th>Brand Name</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th>Unit Retail Price</th>
                  <th>Retail Sub Total</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product Discount</th>
                  <th>Total Amount (Retail)</th>
                
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
                  <td> {{ isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:'N/A' }} </td>
                  <td>{{ $quote['product_details']['product_name'] or 'N/A' }} @if(isset($quote['color']))|  Color:&nbsp;{{$quote['color'] or '-'}}@endif @if(isset($quote['size_id']))| Size:&nbsp;{{get_size_from_id($quote['size_id'])}}@endif</td>
                  <td>{{ $quote['product_details']['brand_details']['brand_name'] or 'N/A'}}</td>
                  <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A'!!}</div></td>
                  <td class="summmarytdsprice">{{ $quote['sku_no'] or 'N/A' }}</td>
                  <td class="summmarytdsprice">{{ $quote['qty'] or 'N/A' }}</td>
                  
                
                     
               {{-- {{dd($quote['product_details'])}} --}}
                  
                     @php
                     $product_discount = 0;
                     
                     $unit_retail_price = $quote['product_details']['retail_price'];

                     $unit_total           = $quote['qty'] * $unit_retail_price;
             
                     $shipping_charges     =  $quote['shipping_charge'];
                     $shipping_discount    =  $quote['shipping_discount'];
                     $product_discount     =  $quote['product_discount'];


                     $total_amount =  $unit_total+$shipping_charges-$shipping_discount-$product_discount;
                     $promo_total_amount += $total_amount;
                     @endphp

            
                  <td class="summmarytdsprice">
                  <span class="fa fa-dollar"></span>{{ isset($unit_retail_price)?num_format($unit_retail_price) : 0.00}}
                  </td>

                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($unit_total)?num_format($unit_total) :0.00}}</td>
                  
                  <td class="summmarytdsprice">
                     @if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{ isset($quote['shipping_charge'])?num_format($quote['shipping_charge']) : 0.00}}
                  </td>

                  <td class="summmarytdsprice">
                     @if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{ isset($quote['shipping_discount'])?num_format($quote['shipping_discount']) : 0.00}}
                  </td>

                  <td class="summmarytdsprice">
                     @if($product_discount>0)-@endif<span class="fa fa-dollar"></span>{{ isset($product_discount)?num_format($product_discount) : 0.00}}
                  </td>
                  
                  <td class="summmarytdsprice">
                       <span class="fa fa-dollar"></span>{{ isset($total_amount)?num_format($total_amount) : 0.00}}
                  </td>
                
               </tr>
               <?php
                                        
                    $tot_qty+= (float)$quote['qty'];

                    $tot_unit_price_wholesale+=(float)$unit_retail_price;
                    $tot_sub_tot_wholesale+=(float)$unit_total;
                    $tot_shipping_charges+=(float)$quote['shipping_charge'];
                    $tot_shipping_discount+=(float)$quote['shipping_discount'];
                    $tot_pro_dis+=(float)$quote['product_discount'];
                    $tot_amt_column+=(float)$total_amount;
                    
                   ?>
               @endforeach
               <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td class="summmarytdsprice">Total :</td>
                  <td class="summmarytdsprice">{{$tot_qty}}</td> 
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_unit_price_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_sub_tot_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_shipping_charges)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_shipping_discount)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_pro_dis)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{num_format($tot_amt_column)}}</td>
                  <td>&nbsp;</td>
                </tr>
               @else
               <td colspan="7">No record found</td>
               @endif
            </table>
         </div>
             {{-- <div class="col-md-12">
               <div class="pull-right" style="margin-top:20px;">
                  <th><b>Total Amount (Wholesale): </b></th>
                  <td><b> <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 0.00}}</b></td>
                 
               </div>
            </div> --}}


        <div class="col-md-12 p-0">
          <div class="col-sm-6 pull-right viewsummaryamtbox">

          @if(isset($enquiry_arr['promotion_discount']) && $enquiry_arr['promotion_discount'] != '' && $enquiry_arr['promotion_discount'] != 0) 

            <div class="row">
              <div class="left">
                <h3>Total Amount :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($promo_total_amount)?num_format($promo_total_amount) : '0.00' }}</span>
              </div>
            </div>
                     
            <div class="row">
              <div class="left">
                <h3>Promotion Discount :</h3>
              </div>
              <div class="right">
                <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['promotion_discount'])?num_format($enquiry_arr['promotion_discount']) : 0.00}}</span>
              </div>
            </div>
        
                
         <div class="row totalrow">
           <div class="left">
             <h3>Total Amount (Retail) :</h3>
           </div>
           <div class="right">
             <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 0.00}}</span>
           </div>
         </div>
         @else
            <div class="row totalrow">
              <div class="left">
                <h3>Total Amount (Retail) :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 0.00}}</span>
              </div>
            </div>
           @endif    
        </div>
           <!--     <div class="pull-right totl-grands">
                @if(isset($enquiry_arr['promotion_discount']) && $enquiry_arr['promotion_discount'] != '' && $enquiry_arr['promotion_discount'] != 0)


                <div class="viewtotal-bg">
                   <span>Total Amount :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($promo_total_amount)?num_format($promo_total_amount) : '0.00' }}
                  </span>
                </div>
                <div class="viewtotal-bg">
                   <span>Promotion Discount :</span>
                  <span class="viewspan">
                  - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['promotion_discount'])?num_format($enquiry_arr['promotion_discount']) : 0.00}}
                </span>
                </div>
                <div class="viewtotal-bg border-h">
                   <span>Total Amount (Retail) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 0.00}}
                </span>
                </div>

                @else


                  <span>Total Amount (Retail) :</span>
                  
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 0.00}}

                @endif
                  
                 
               </div> -->
            </div>
          <div class="clearfix"></div>
      </div>

   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-success waves-effect waves-light pull-left" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop