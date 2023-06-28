@extends('admin.layout.master')                
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

<?php

  $ordNo = base64_encode($enquiry_arr['order_no']);
  $vendorId = base64_encode($enquiry_arr['maker_id']);
  $custId = base64_encode($enquiry_arr['customer_id']);
  
?>

@php
 $admin_commission = $enquiry_arr['admin_commission'];

  $pay_vendor_button = $generate_invoice = '';

  if($enquiry_arr['maker_commission_status'] == '1')
  {
     $is_disabled = 'display:none';
  }
  else
  {
     $is_disabled='display:block';
  }

  $ord_wholesale_price = isset($enquiry_arr['total_retail_price'])?$enquiry_arr['total_retail_price']:0;

  // $ord_wholesale_price = $ord_wholesale_price - $enquiry_arr['shipping_charge'];
   //$ord_wholesale_price = $ord_wholesale_price - $order_shipping_charge;

   $is_freeshipping = is_promocode_freeshipping(isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:false);

   if($is_freeshipping == false)
   {
     $ord_wholesale_price = $ord_wholesale_price - $order_shipping_charge;
   }

   $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

   $vendor_pay_amount = $ord_wholesale_price - $admin_commission_amount;


   if($enquiry_arr['ship_status'] == 1 && $enquiry_arr['is_direct_payment'] == 0 && $enquiry_arr['payment_term'] != 'Net30' && $enquiry_arr['is_split_order'] == 0)
   { 

      $pay_vendor_button = '<div class="col-sm-12"><button type="button" class="btn btn-outline btn-info btn-circle show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$enquiry_arr['maker_id'].','.$enquiry_arr['id'].')" style="'.$is_disabled.'" >Pay Vendor</button></div>';
   }

@endphp

<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   @include('admin.layout._operation_status')  
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
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Order No.</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Vendor</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <a href="{{url('/')}}/admin/vendor/view/{{$vendorId}}" class="link_view">
                  <span>{{ $enquiry_arr['maker_data']['company_name'] or 'N/A' }}</span>
                </a>
              </div>
            </div>


            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Order Date</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  <?php $us_date = us_date_format($enquiry_arr['created_at']); ?>
                  {{ isset($enquiry_arr['created_at'])?$us_date:'N/A' }}
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Shipping Status</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
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
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Status</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  @if(isset($enquiry_arr['transaction_status']) && $enquiry_arr['transaction_status']==1)
                    <span class="label label-warning">Pending</span>

                  @elseif(isset($enquiry_arr['transaction_status']) && $enquiry_arr['transaction_status']==2)
                    <span class="label label-success">Paid</span>

                  @elseif(isset($enquiry_arr['transaction_status']) && $enquiry_arr['transaction_status']==3)
                    <span class="label label-danger">Failed</span>
                  @else
                    <span class="label label-warning">Pending</span>
                  @endif
                </span>
              </div>
            </div>

           

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Term</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
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
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Vendor Commission Status</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$status}}</span>
                </div> 
              </div>

            @endif  

          </div>

          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Customer</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <a href="{{url('/')}}/admin/customer/view/{{$custId}}" class="link_view">
                <span>{{ isset($enquiry_arr['user_details']['first_name'])?$enquiry_arr['user_details']['first_name']." ".$enquiry_arr['user_details']['last_name']:'N/A' }}</span>
                </a>
              </div>
            </div>

           

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Total Amount (Retail)</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>${{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : '0.00' }}</span>
              </div>
            </div>

          
             <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Shipping Address</label>
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
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  {{$shipping_address}}
                </span>
              </div>
            </div>
            @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Due Date</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span>{{isset($enquiry_arr['payment_due_date'])?us_date_format($enquiry_arr['payment_due_date']):'N/A' }}</span> 
                </div>
              </div>
            @endif  

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Billing Address</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>{{$billing_address}}</span>
              </div>
            </div>
             @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')

              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Cancel Order Request Rejection Reason</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
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
                <label class="col-sm-12 col-md-12 col-lg-4"><span class="commonlabel_bold">Tracking Number</span></label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                </div>
              </div>

            @endif 



            @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '')
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Promo Code</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$enquiry_arr['promo_code'] or ''}}</span>
                </div>
              </div>
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

            @if((isset($enquiry_arr['is_payment_status']) && $enquiry_arr['is_payment_status']=='1') && (isset($accountHolder) && $accountHolder != ''))
            
              @if(isset($enquiry_arr['transaction_mapping']['transaction_id']))
                <div class="row">
                 <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                 <div class="col-sm-12 col-md-12 col-lg-8">
                 <span>{{$enquiry_arr['transaction_mapping']['transaction_id']}}</span>
                </div>
              </div>
              @endif
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
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Split Orders</label>
  
              @foreach($split_order_arr as $key => $split_order)
                <div class="col-sm-12 col-md-12 col-lg-8">
                
                  <a href="{{url('/')}}/admin/customer_orders/view/{{isset($split_order['id'])?base64_encode($split_order['id']):'0'}}"><span class="label label-success">{{$split_order['order_no'] or 'N/A'}}</span> </a>
                </div>
              @endforeach
            </div>
            @endif

           
          <div class="row">
                
                {!! $pay_vendor_button  !!}

                @if($enquiry_arr['is_direct_payment'] == 1 && $enquiry_arr['admin_commission_status'] == 0 && $enquiry_arr['ship_status'] == 1)
                        
                  <div class="col-sm-12">
                    <button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="generate_invoice" title="Generate Invoice" onclick = 'generate_invoice("{{$enquiry_arr['id']}}")'>Generate Invoice</button>
                  </div>

                @endif

            </div>

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
                  <td>{{ $quote['product_details']['product_name'] or 'N/A' }}</td>
                  <td>{{ $quote['product_details']['brand_details']['brand_name'] or 'N/A'}}</td>
                  <td>{{-- <div class="truncate">{!! $quote['product_details']['description'] or 'N/A'!!}</div> --}}

                    @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 100 && $quote['product_details']['description']!='' )   
                        @php
                        /*$desc_html = html_entity_decode($quote['product_details']['description']);
                        $desc = str_limit($desc_html,100);*/
                        $desc_html = $desc = "";
                        $desc_html = ($quote['product_details']['description']);
                        $desc =  substr(html_entity_decode($desc_html), 0, 70);
                        @endphp               
                       <p class="prod-desc"> {!! $desc !!}
                          <a class="readmorebtn" message="{{$quote['product_details']['description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else
                       
                        {!!$quote['product_details']['description']!!}
                    @endif
                  </td>
                  <td>{{ $quote['sku_no'] or 'N/A' }}</td>
                  <td>{{ $quote['qty'] or 'N/A' }}</td>
                  
                
                     
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
                  
                   <?php
                                        
                    $tot_qty+= (float)$quote['qty'];

                    $tot_unit_price_wholesale+=(float)$unit_retail_price;
                    $tot_sub_tot_wholesale+=(float)$unit_total;
                    $tot_shipping_charges+=(float)$quote['shipping_charge'];
                    $tot_shipping_discount+=(float)$quote['shipping_discount'];
                    $tot_pro_dis+=(float)$quote['product_discount'];
                    $tot_amt_column+=(float)$total_amount;
                    
                   ?>

               </tr>
               @endforeach
               <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td class="summmarytdsprice">Total :</td>
                  <td class="summmarytdsprice">{{$tot_qty}}</td> 
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_unit_price_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_sub_tot_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_amt_column)}}</td>
                </tr>
               @else
               <td colspan="7">No record found</td>
               @endif
            </table>
         </div>
           

          

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
                              <span>@if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt']>0)-@endif<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00 }}</span>
                            </div>
                          </div>
                        @endif
                
                      @if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)
                        <div class="row">
                            <div class="left">
                              <h3>Promotion(Free shipping) :</h3>
                            </div>
                            <div class="right">
                              <span>@if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges']>0)-@endif<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}</span>
                            </div>
                        </div>
                      @endif 

                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Retail)</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @else
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount (Retail)</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                 @endif    
              </div>


          <div class="clearfix"></div>
      </div>

   </div>

   @if(isset($trans_status) && $trans_status != '')
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
                            <th>Amount Paid to Vendor</th>
                            <th>Status</th>
                            <th>Transfer From Account</th>
                            <th>Transaction Id</th>

                         </tr>
                        
                          <?php
                            $orderShippingCharges = isset($order_shipping_charge)?$order_shipping_charge:0.00;

                            $orderAmount = isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 0.00;

                            $excludingAmount = $orderAmount - $orderShippingCharges;

                           
                            if($enquiry_arr['is_direct_payment']==1)
                            {
                              $adminCommissionAmount = isset($enquiry_arr['stripe_transaction_detail']['amount'])?num_format($enquiry_arr['stripe_transaction_detail']['amount']):'0.00';
                              
                              $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 

                              $amountPaidToVendor = $excludingAmount - $adminCommissionAmount;
                            }
                            else
                            {
                              $amountPaidToVendor = isset($enquiry_arr['stripe_transaction_detail']['amount'])?num_format($enquiry_arr['stripe_transaction_detail']['amount']):'0.00';

                              $adminCommissionAmount = $excludingAmount - $amountPaidToVendor;
                              
                              $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 

                            }  
                          ?>

                        <tr>
                          <td>{{isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:"N/A"}}</td>

                          <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($excludingAmount)?num_format($excludingAmount):0.00}}
                            <label class="shippingLabel">Excluded shipping costs</label>
                          </td>
                          
                          <td><i class="label" aria-hidden="true"></i>{{isset($adminCommission)?num_format($adminCommission):'N/A'}} %</td>

                          <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($adminCommissionAmount)?num_format($adminCommissionAmount):0.00}}</td>

                          <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($amountPaidToVendor)?num_format($amountPaidToVendor):0.00}}</td>

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

                          <td>{{isset($enquiry_arr['stripe_transaction_detail']['transaction_id'])?$enquiry_arr['stripe_transaction_detail']['transaction_id']:'N/A'}}</td>
                        </tr>
                      </table>
                   </div>
                 </div>
               </div>
               
      @endif
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

<!-- Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Vendor Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="vendorPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="maker_id"   id="maker_id">
          <input type="hidden" name="order_id"   id="orderId" >
          <input type="hidden" name="amount"     id="amount">

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
              <div class="admin-commission-lnk-right">$<span id="actual_amount"></span>
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
                <div class="admin-commission-lnk">Amount Payable to Vendor :</div>
              <div class="admin-commission-lnk-right">$<span id="pay_amount" class="pay_amount"></span>
              </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">
    
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

       
        <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>
        
         <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>
@include('product_description_popup')
<!-- /#page-wrapper -->
<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>


<script type="text/javascript">
  
  function fillData(orderPrice,vendorAmount,adminCommission,adminCommissionAmount,makerId,orderId)
  {

    $('.vendor-Modal').modal('show');
    $('#order_amount').html(orderPrice.toFixed(2));
    $('#admin_commission').html(adminCommission.toFixed(2));         
    $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
    $('.pay_amount').html(vendorAmount.toFixed(2));    
    $('#maker_id').val(makerId);    
    $('#amount').val(vendorAmount.toFixed(2));    
    $('#orderId').val(orderId);    
  }

  function payVendor()
  {
     var paymentFormData = new FormData($("#vendorPaymentForm")[0]);
    
    $.ajax({
            url: '{{url('/admin')}}'+'/customer_payment/vendor',
            type:"POST",
            data: paymentFormData,
            contentType:false,
            processData:false,
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
                          type: data.status},
                         
                          function(){ 
                             location.reload();
                          }
                      );               
               }
               else if('warning' == data.status)
               {
                  $('#user_id').val(data.user_id);

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
    let user_id = $('#user_id').val();
    let token = "{{csrf_token()}}";

     $.ajax({
            url: '{{url('/admin/leads')}}'+'/send_stripe_acc_creation_link',
            type:"POST",
            data: {"_token":token,"user_id":user_id},
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

  function generate_invoice(order_no = false)
  {
    var csrf_token = "{{csrf_token()}}";
    var order_no   = order_no;
    var orderType  = 'customer';
     var generate_invoice_url  = "{{ url(config('app.project.admin_panel_slug')."/admin_commission_reports")."/admin_commission_invoice_generator" }}";
     generate_invoice_url =  generate_invoice_url+'/'+btoa(order_no)+'/'+orderType;

     $.ajaxSetup({
        headers : { "X-CSRF-TOKEN" :csrf_token}
      });
    
    $.ajax({
               url: generate_invoice_url,
               type:"POST",
               contentType:false,
               processData:false,
               data:{order_no:order_no,orderType:orderType},
               dataType:'json',
               beforeSend: function() 
               {
                 showProcessingOverlay();                
               },
               success:function(response)
               {
                   hideProcessingOverlay();
                  
                  if(response.status == 'success')
                  { 
                    
       
                    swal({
                            title: 'Success',
                            text: response.description,
                            type: 'success',
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                         },
                        function(isConfirm,tmp)
                        {                       
                          if(isConfirm==true)
                          {
                            window.location.reload();
                          }
                        });
                   }
                   else
                   {                
                      swal('Error',response.description,'error');
                   }  
               }           
             });     
    return;
  }

</script>
@stop