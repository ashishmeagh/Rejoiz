@extends('retailer.layout.master')                
@section('main_content')
<!-- Page Content -->

<style type="text/css">
  .totl-grands .viewtotal-bg span.viewspan{
           font-weight: 600;
    position: absolute;
    right: 0;
    margin-top: 0px;
    top: 0;
    text-align: left;
    width: 70px;
  }
  .totl-grands .viewtotal-bg span{
    display: block;
    text-align: right;
  }
  .viewtotal-bg{position: relative; padding-right: 100px;}
 .viewtotal-bg.border-h {
    border-top: 1px solid #ccc;
    padding-top: 10px;
    margin-top: 20px;
}
.totl-grands .viewtotal-bg.border-h span{margin-top: 0px;}
.totl-grands .viewtotal-bg.border-h .viewspan{margin-top: 10px;}

.row{
  padding-bottom: 20px;
}
.spli_order_row {min-width:100%; margin-top:15px;}
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
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->


<div class="row">
  <div class="col-md-12">
     
        <div class="white-box small-space-nw">
          
              <!-- split order button will show -->
                @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
            
                <a href="{{url('/')}}/retailer/my_orders/view/{{base64_encode($main_split_order_no['id'])}}" class="btn btn-inverse pull-right mb-4">This order is split from : {{isset($main_split_order_no['order_no'])?$main_split_order_no['order_no']:""}}</a>

                @endif 

            <div class="row spli_order_row">
              <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="row">
                    <label class="col-sm-4"><span>Order No.</span></label>
                    <div class="col-sm-8">
                      <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                 
                  <div class="row">
                    <label class="col-sm-4"><span>Order Date</span></label>
                    <div class="col-sm-8">
                      <span>{{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                 
                  <div class="row">
                    <label class="col-sm-4"><span>Shipping Status</span></label>
                    <div class="col-sm-8">
                       <?php $status = isset($enquiry_arr['ship_status'])?get_order_status($enquiry_arr['ship_status']):'N/A'?>

                      @if($status=="Pending")
                       <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                      @elseif($status=="Shipped")
                       <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>

                      @elseif($status=="Failed")
                      <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>

                      @else
                      
                      {{$status or 'N/A'}}

                      @endif

                    </div>
                    <div class="clearfix"></div>
                  </div>
     

                  <div class="row">
                    <label class="col-sm-4"><span>Payment Term</span></label>
                    <div class="col-sm-8">
                        @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')
                          <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                        @else
                          <span class="label label-success">{{isset($enquiry_arr['payment_term'])?$enquiry_arr['payment_term']:'N/A' }}</span>
                        @endif
                    </div>
                    <div class="clearfix"></div>
                  </div>
          
                  @php 


                      if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']==null) 
                      {
                        $shipping_address = $enquiry_arr['shipping_addr']; 
                      }
                      else if(isset($enquiry_arr['shipping_addr']) && $enquiry_arr['shipping_addr']!=null && $enquiry_arr['shipping_addr_zip_code']!=null) 
                      {
                        $shipping_address = $enquiry_arr['shipping_addr'].','.$enquiry_arr['shipping_addr_zip_code']; 
                      }
                      else
                      {
                         $shipping_address = 'N/A';
                      }
                       
                      if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']==null) 
                      {
                        $billing_address = $enquiry_arr['billing_addr'];
                      }
                      else if(isset($enquiry_arr['billing_addr']) && $enquiry_arr['billing_addr']!=null && $enquiry_arr['billing_addr_zip_code']!=null) 
                      {
                        $billing_address = $enquiry_arr['billing_addr'].','.$enquiry_arr['billing_addr_zip_code'];
                      }
                      else
                      {
                       $billing_address = 'N/A'; 
                      }

                  @endphp
                   <div class="row">
                    <label class="col-sm-4"><span>Payment Status</span></label>
                    <div class="col-sm-8">
                        @php
                        // $status = isset($enquiry_arr['transaction_mapping']['transaction_status'])?get_payment_status($enquiry_arr['transaction_mapping']['transaction_status']):'';


                         if($enquiry_arr['is_payment_status'] == 1)
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
                        
                        {{$status}}

                        @endif
                    </div>
                  </div>


                @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')
              
                  <div class="row">
                    <label class="col-sm-4"><span>Vendor Rejection Reason</span></label>
                    <div class="col-sm-8">
                      <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>

                @endif
               

                @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '') 
                  <div class="row">
                    <label class="col-sm-4"><span>Promo Code</span></label>
                    <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:'N/A'}}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>

                @endif   

            </div>



            <div class="col-sm-12 col-md-12 col-lg-6">

                  <div class="row">
                    <label class="col-sm-4"><span>Vendor</span></label>
              
                    <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['maker_data']['company_name'])?ucfirst($enquiry_arr['maker_data']['company_name']):'N/A'}}</span>
                    </div>
                  </div>
                 
                  <div class="row">
                    <label class="col-sm-4"><span>Total Amount</span></label>
                    <div class="col-sm-8">
                    <span>${{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</span>
                    </div>
                  </div>
                 
               
                   <div class="row">
                  <label class="col-sm-4"><span>Shipping Address</span></label>
                  <div class="col-sm-8">
                    <span>{{$shipping_address}}</span>
                  </div>
                  <div class="clearfix"></div>
                </div>
                 

                  @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')

                 <!--  <div class="row">
                      <label class="col-sm-4"><span>Payment Due Date</span></label>
                      <div class="col-sm-8">
                        <span>{{isset($enquiry_arr['payment_due_date'])?us_date_format($enquiry_arr['payment_due_date']):'N/A' }}</span>
                      </div>
          
                  </div> -->
                    @endif

                  <div class="row">
                    <label class="col-sm-4">Billing Address</label>
                    <div class="col-sm-8">
                      <span>{{$billing_address or ''}}</span>
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
                    <label class="col-sm-4">Tracking Number</label>
                    <div class="col-sm-8">
                  <!--     <span>{{ isset($tracking_no)?$tracking_no:'--' }}</span> -->
                  <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span>
                  </a>
                    
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


                  @if((isset($enquiry_arr['transaction_mapping']['transaction_id']) && $enquiry_arr['transaction_mapping']['transaction_id'] != '') && (isset($accountHolder) && $accountHolder != ''))
                
                    <div class="row">
                   <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                   <div class="col-sm-12 col-md-12 col-lg-8">
                  
                   <span>{{$enquiry_arr['transaction_mapping']['transaction_id']}}</span>
                  
                  </div>
                </div>
                @endif

              
          {{--   @if(isset($accountHolder) && $accountHolder != '')

             <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Account Holder</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$accountHolder}}</span>
                </div> 
            </div>
            @endif --}}
                  
                  <div class="row">
                      <label class="col-sm-4"></label>
                      <div class="col-sm-8">
                     @if($enquiry_arr['is_split_order']!=1)
                        
                        @if(isset($enquiry_arr['payment_term']) && $enquiry_arr['payment_term'] == 'Net30')  
                          
                          {{-- @if($count == 0 ) --}}

                                @php
                                  Session::put('payment_type','Net30');
                                  Session::put('order_id',$enquiry_arr['id']); 

                                  $eventCall = 'return notAllowedPayment()';
                                  
                                  if(isset($enquiry_arr['maker_confirmation'])  && $enquiry_arr['maker_confirmation'] == 1)
                                  {
                                   
                                    $eventCall = "return checkoutRedirect($(this))";
                                  }    
                                @endphp
                                <div class="col-md-12">
                                  <div class="pull-left" style="margin-top:20px;">
                                  <a class="btn pull-left btn-success" href="javascript:void(0);"  onclick="{{$eventCall}}" >Pay Now</a>
                                  </div>
                                </div> 
                          {{-- @endif        --}}

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
    $total_amount = $freeShipAmount = $freeShipDisAmount = 0;
    @endphp
   
   <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="60">
   <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>Summary</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table ">
               <tr>
                  <th>Product</th>
                  <th>Brand</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th> Price </th>
                  <th>Sub Total </th>
                {{--   <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product  Discount</th> --}}
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
                  <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):"N/A"}} @if($quote['color'])|  Color:&nbsp;{{$quote['color'] or '-'}}@endif| @if(get_size_from_id($quote['size_id']))Size:&nbsp;{{get_size_from_id($quote['size_id']) }}@endif</td>

                  <td>
                     {{isset($quote['product_details']['brand_details']['brand_name'])?ucfirst($quote['product_details']['brand_details']['brand_name']):"N/A"}}
                  </td>


                 {{--  @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                    <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                    <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A' !!}</div></td>
                  
                  @else

                    <td>{!! $quote['product_details']['description'] or 'N/A' !!}</td>
                  
                  @endif
                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="20">
              --}}
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


                   <td><div class="truncate">{!! $quote['sku_no']  or 'N/A' !!}</div></td>
                   
                   <td>{{ $quote['qty'] or 'N/A' }}</td>
                 
                  <td class="summmarytds"><span class="fa fa-dollar"></span>
                  {{ isset($quote['unit_wholsale_price'])?num_format($quote['unit_wholsale_price']) : 0.00 }}</td>
                 
                  <td class="summmarytds">
                     <span class="fa fa-dollar"></span>
                      <?php
                        $sub_tot_wholesale = num_format($quote['qty'] * $quote['unit_wholsale_price'])
                      ?>
                      {{$sub_tot_wholesale}}
                  </td>

                 {{--  <td class="summmarytds">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{ isset($quote['shipping_charge'])?num_format($quote['shipping_charge']) : 0.00 }}</td>

                  <td class="summmarytds">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{ isset($quote['shipping_discount'])?num_format($quote['shipping_discount']) : 0.00 }}</td>

                  <td class="summmarytds">
                     @if($quote['product_discount'] >0)-@endif<span class="fa fa-dollar"></span>{{ isset($quote['product_discount'])?num_format($quote['product_discount']) : 0.00 }}
                  </td> --}}
                   @php 
                     
                         $wholesale_price = $quote['qty'] * $quote['unit_wholsale_price'];
                         $sub_wholesale_price = $wholesale_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];
                         $total_amount += $sub_wholesale_price;
                         
                    @endphp
                  <td class="summmarytds">
                     {{-- <span class="fa fa-dollar"></span>{{isset($quote['wholesale_price'])?num_format($quote['wholesale_price']) : 0.00 }} --}}
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : 0.00 }}
                   
                   <?php
                     if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '' )
                     {
                      /*if promo code type is Free shipping */
                       // $total_amount += getGrandTotal($quote['qty'],$quote['unit_wholsale_price'],$quote['shipping_charge'],$quote['shipping_discount'],$quote['product_discount']);

                      $total_amount += (float)$quote['wholesale_price'];
                     }
                     else
                     {
                        $total_amount += (float)$quote['wholesale_price'];
                     }

                    $freeShipAmount += (float)$quote['shipping_charge']-(float)$quote['shipping_discount'];
                   
                    $tot_unit_price_wholesale+= (float)$quote['unit_wholsale_price'];
                    $tot_qty+= (float)$quote['qty'];
                    $tot_sub_tot_wholesale+=(float)$sub_tot_wholesale;
                    $tot_shipping_charges+=(float)$quote['shipping_charge'];
                    $tot_shipping_discount+=(float)$quote['shipping_discount'];
                    $tot_pro_dis+=(float)$quote['product_discount'];
                    $tot_amt_column+=(float)$sub_wholesale_price;

                    
                   ?>

                  </td>
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
                 {{--  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td> --}}
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
                              <h3>Promotion Discount ({{ isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 }}%) :</h3>
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
                      <h3>Total Amount </h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                @else
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount </h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                 @endif    
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
               
                  <td><a href="{{url('/')}}/retailer/my_orders/view/{{isset($quote['id'])?base64_encode($quote['id']):0}}">{{isset($quote['order_no'])?$quote['order_no']:"N/A"}}</a></td>

                  <td>{{us_date_format($quote['created_at'])}}</td>
                  
                  <td>{{ $quote['maker_data']['company_name'] or 'N/A' }}</td>

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
                      elseif($quote['transaction_mapping']['transaction_status'] == '')
                      {
                        $payment_status = 'Pending';
                      }
                      else
                      {
                        $payment_status = 'Failed';
                      }
                   @endphp
                  <td>
                    @if($payment_status == 'Paid')

                      <span class="label label-success">{{ $payment_status }}</span>

                    @elseif($payment_status == 'Pending')

                      <span class="label label-warning">{{ $payment_status }}</span>

                    @elseif($payment_status == 'Failed')

                      <span class="label label-danger">{{ $payment_status }}</span>  
                    @endif
                  </td>
                 
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
                <td>@if($shipping_status == 'Shipped')

                      <span class="label label-success">{{ $shipping_status }}</span>

                    @elseif($shipping_status == 'Pending')

                      <span class="label label-warning">{{ $shipping_status }}</span>

                    @endif</td>

          
                
                  <td>
                     <a href="{{url('/')}}/retailer/my_orders/view/{{isset($quote['id'])?base64_encode($quote['id']):0}}" class="btn btn-success btn-circle" title="View order details">View</a>

                     <!-- @if($quote['maker_confirmation'] == '' && $quote['maker_confirmation'] == 0 && $quote['order_cancel_status'] == 0)
                             
                              <a data-toggle="tooltip"  data-size="small" title="Cancel Order" class="btn btn-outline btn-info btn-circle show-tooltip " href="javascript:void(0);" onclick="cancelOrder($(this));" data-order-id="{{$quote['id']}}">Cancel</a>
                            
                            @elseif($quote['order_cancel_status'] == 1)
                            
                              <a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip" href="javascript:void(0);" data-order-id="{{$quote['id']}}">Cancel Requested</a>

                            @elseif($quote['order_cancel_status'] == 2)


                            <a data-toggle="tooltip"  data-size="small" title="Canceled Order" class="btn btn-outline btn-info btn-circle show-tooltip " href="javascript:void(0);">Canceled</a>

                     
                     @endif -->

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
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
               <!-- <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a> -->

               @if($enquiry_arr['ship_status'] != 1 && $enquiry_arr['order_cancel_status'] == 0)
                                 
                 <!-- <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" onclick="cancelOrder({{isset($enquiry_arr['id'])?$enquiry_arr['id']:0}});">Cancel Order</a> -->
                
                @elseif($enquiry_arr['order_cancel_status'] == 1)
                
                 <!-- <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" >Cancel Requested</a> -->
                
                 @elseif($enquiry_arr['order_cancel_status'] == 2)

                <!--  <a class="btn btn-inverse waves-effect waves-light pull-right" href="javascript:void(0)" >Cancelled</a> -->

               @endif
               
            </div>
         </div>
      </div>
   </div>
</div>

@include('product_description_popup')

<input type="hidden" name="checkout_page_url" value="{{url('/checkout/retailer')}}/{{isset($enquiry_arr['order_no'])?base64_encode($enquiry_arr['order_no']):false}}/{{isset($enquiry_arr['maker_id'])?base64_encode($enquiry_arr['maker_id']):false}}" id="checkout_page_url">

<!-- END Main Content -->

<script type="text/javascript">

  var module_url_path  = "{{$module_url_path or ''}}";
   
   function checkoutRedirect(ref)
   {
      swal({
          title: "Need Confirmation",
          text: "Are you sure? Do you want to make payment.",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          confirmButtonText: 'OK',
          cancelButtonText: "Cancel",
          closeOnConfirm: false,
          closeOnCancel: true
      },
      function(isConfirm){

            if(isConfirm==true)
            {
              let url = $('#checkout_page_url').val();

              window.location = url;
            }
            
      });
   }

   function cancelOrder(ref)
  {
      var order_id = $(ref).attr('data-order-id');

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
                url:module_url_path+'/cancel',
                method:'GET',
                data:{order_id:order_id},
                beforeSend : function()
                {
                  showProcessingOverlay();
                 
                },
                success:function(response)
                {
                    hideProcessingOverlay();
                   
                 
                  if(response.status = "success")
                  {
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
                  else
                  {
                       swal("Error",response.description,response.status);
                  }

                }
             });
          }
       
      });

}


function notAllowedPayment()
{
  swal('Warning','Please wait for order confirmation from the Vendor','warning');
}

function cancelOrder(order_id)
{
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
              url:module_url_path+'/cancel',
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