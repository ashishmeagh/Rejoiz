@extends('customer.layout.master')                
@section('main_content')
 <style>
.row{
  padding-bottom: 20px;
}
</style>
<?php

   $ordNo = base64_encode($enquiry_arr['order_no']);
   $vendorId = base64_encode($enquiry_arr['maker_id']);
?>
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.customer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-md-12">
         @include('customer.layout._operation_status')  
      <div class="white-box small-space-nw">
         {{-- <label>
            <h3>{{$module_title or ''}} Details</h3>
         </label> --}}
         <div class="row">
              <div class="col-sm-12 col-md-12 col-lg-6 orders_view"> 
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Order No.</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                    </div>
                  </div>
                 
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Order Date</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</span>
                    </div>
                  </div>
                 
                   <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Order Status</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        <span class="label label-success">Cancelled</span>
                    </div>
                  </div>
     

                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Payment Term</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
                          <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                        @else
                          <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                        @endif
                    </div>
                  </div>

                  <?php
                   if($enquiry_arr['is_payment_status'] == 1)
                   {
                    $status = 'Paid';
                   }
                   else
                   {
                    $status = 'Pending';
                   }
                   ?>

                   <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Payment Status</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        <span class="label label-success">{{isset($status)?$status:'--'}}</span>
                    </div>
                  </div>
          
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

               

                @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '') 
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Promo Code</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:'N/A'}}</span>
                    </div>
                  </div>

                @endif   

            </div>



            <div class="col-sm-12 col-md-12 col-lg-6 orders_view">

                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Vendor</label>
              
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{isset($enquiry_arr['maker_data']['company_name'])?ucfirst($enquiry_arr['maker_data']['company_name']):'N/A'}}</span>
                    </div>
                  </div>
                 
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Total Amount</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                    <span>${{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 'N/A' }}</span>
                    </div>
                  </div>
                  

                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Shipping Address</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{$shipping_address}}</span>
                    </div>
                  </div>
                 

                  @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')

                  <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Payment Due Date</label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        <span>{{isset($enquiry_arr['payment_due_date'])?us_date_format($enquiry_arr['payment_due_date']):'N/A' }}</span>
                      </div>
          
                  </div>
                    @endif

                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Billing Address</label>
                    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                      <span>{{$billing_address or ''}}</span>
                    </div>
                  </div>


                 @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')
                
                    <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold">Vendor Rejection Reason</label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
                      </div>
                      <div class="clearfix"></div>
                    </div>

                 @endif
                  
                  <div class="row">
                      <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 commonlabel_bold"></label>
                      <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                        @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')  
                          
                          @if($count == 0 )

                                @php
                                  Session::put('payment_type','Net30');
                                  Session::put('order_id',$enquiry_arr['id']);     
                                @endphp
                                <div class="col-md-12">
                                  <div class="pull-left" style="margin-top:20px;">
                                  <a class="btn pull-left btn-success" href="javascript:void(0);" onclick="return checkoutRedirect($(this));">Pay Now</a>
                                  </div>
                                </div> 
                          @endif       

                        @endif 

                        <?php $status = isset($enquiry_arr['transaction_mapping']['transaction_status'])?get_payment_status($enquiry_arr['transaction_mapping']['transaction_status']):'';?>
                        <?php $shipping_status = isset($enquiry_arr['ship_status'])?get_order_status($enquiry_arr['ship_status']):'N/A'?>

                      </div>
                  </div>
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
                  <th>Product Name</th>
                  <th>Brand Name</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th>Unit Price (Retail)</th>
                  <th>Sub Total (Retail)</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product  Discount</th>
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
               @if(isset($enquiry_arr['quotes_details']) && count($enquiry_arr['quotes_details'])>0)

                     @foreach($enquiry_arr['quotes_details'] as $quote)
              
                        <tr>
                           <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):"N/A"}}
                           </td>

                           <td>
                              {{isset($quote['product_details']['brand_details']['brand_name'])?ucfirst($quote['product_details']['brand_details']['brand_name']):"N/A"}}
                           </td>

                           @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                              <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                              <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A' !!}</div></td>
                            
                            @else

                              <td>{!! $quote['product_details']['description'] or 'N/A' !!}</td>
                            
                            @endif
                           
                           <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="20">

                            <td><div class="truncate">{!! $quote['sku_no']  or 'N/A' !!}</div></td>
                           <td>{{ $quote['qty'] or 'N/A' }}</td>

                           <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$quote['product_details']['retail_price'] or 'N/A' }}</td>
                         
                           <td>
                              @php 

                             // $sub_wholesale_price = $quote['qty'] * $quote['wholesale_price'];
                              $sub_retail_price = $quote['qty'] * $quote['unit_retail_price'];
                             
                              $sub_retail_total = $sub_retail_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];

                              $total_amount += $sub_retail_price;

                              @endphp


                              <span class="fa fa-dollar"></span>{{isset($sub_retail_price)?num_format($sub_retail_price) : "N/A"}}
                           </td>

                           <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}</td>

                           <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}</td>

                           <td>@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>

                           <td class="summmarytdsprice">
                              <span class="fa fa-dollar"></span>{{isset($sub_retail_total)?num_format($sub_retail_total) : "N/A"}} 
                           </td>
                        </tr>
                         <?php
                                              
                          $tot_qty+= (float)$quote['qty'];

                          $tot_unit_price_wholesale+=(float)$quote['product_details']['retail_price'];
                          $tot_sub_tot_wholesale+=(float)$sub_retail_price;
                          $tot_shipping_charges+=(float)$quote['shipping_charge'];
                          $tot_shipping_discount+=(float)$quote['shipping_discount'];
                          $tot_pro_dis+=(float)$quote['product_discount'];
                          $tot_amt_column+=(float)$sub_retail_total;
                          
                         ?>
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
                  <td colspan="7">No Record Found</td>
               @endif

            </table>
         </div>
            {{-- <div class="col-md-12">
               <div class="pull-right totl-grands" >
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
                   <span>Total Amount (Wholesale) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}
                </span>
                </div>

                @else

                  <span>Total Amount (Wholesale) :</span>
                  
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}

                @endif

                 
               </div>
            </div>
 --}}
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
             <h3>Total Amount (Retail) :</h3>
           </div>
           <div class="right">
             <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
           </div>
         </div>
         @else
            <div class="row totalrow">
              <div class="left">
                <h3>Total Amount (Retail) :</h3>
              </div>
              <div class="right">
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
              </div>
            </div>
           @endif    
        </div>
           <!--  <div class="pull-right totl-grands" >
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
                  <span>Total Amount (Retail) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}
                  </span>
               </div>
               @else

               <span>Total Amount (Retail) :</span>
               <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($enquiry_arr['total_retail_price']) : 'N/A' }}

               @endif
               
            </div> -->
         </div>
          <div class="clearfix"></div>
      </div>
   </div>

    <!-- Refund Details -->
    @if(isset($arr_refund_detail) && count($arr_refund_detail) > 0)
     <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>Refund Details</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Order No</th>
                  <th>Transaction Id</th>
                  <th>Payment Account Holder</th>
                  <th>Amount</th>
                  <th>Status</th>
               </tr>              
                <tr>
                    <td>{{isset($arr_refund_detail['order_no'])?$arr_refund_detail['order_no']:"--"}}
                   </td>

                   <td>{{isset($arr_refund_detail['balance_transaction'])?$arr_refund_detail['balance_transaction']:"--"}}
                   </td>

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

                   <td>{{isset($accountHolder)?$accountHolder:"--"}}</td>

                   <td>
                      ${{isset($arr_refund_detail['amount'])?num_format($arr_refund_detail['amount']):"--"}}
                   </td>
                   <td>
                      @if(isset($arr_refund_detail['status']) && $arr_refund_detail['status'] == '1')
                      <span class="label label-success">Pending</span>
                      @elseif(isset($arr_refund_detail['status']) && $arr_refund_detail['status'] == '2')
                      <span class="label label-success">Paid</span>
                      @elseif(isset($arr_refund_detail['status']) && $arr_refund_detail['status'] == '3')
                      <span class="label label-success">Failed</span>
                      @endif
                   </td>
                </tr>                
            </table>
         </div>
        <div class="clearfix"></div>
      </div>
   </div>
   @endif
    <!-- end -->


      <div class="form-group">
         <div class="col-md-12">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>

               <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a>
            </div>
         </div>
      </div>
</div>
<!-- END Main Content -->
@stop

