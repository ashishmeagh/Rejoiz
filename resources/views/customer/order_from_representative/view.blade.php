@extends('retailer.layout.master')                
@section('main_content')
<!-- Page Content -->
<style type="text/css">
  .row{
  padding-bottom: 20px;
}
</style>



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
       
              <a class="btn btn-inverse" href="{{url('/')}}/retailer/my_orders/order_summary/{{base64_encode($main_split_order_no['order_no'])}}/{{base64_encode($main_split_order_no['maker_id'])}}">This order is split from : {{$main_split_order_no['order_no']}}</a>

            @endif

            <div class="row">
              <div class="col-sm-6">
                  <div class="row">
                    <label class="col-sm-4">Order No.</label>
                    <div class="col-sm-8">
                      <span>{{ $arr_data['order_no'] or 'N/A' }}</span>
                    </div>
                  </div>
                 
                  <div class="row">
                    <label class="col-sm-4">Order Date</label>
                    <div class="col-sm-8">
                      <span>{{ isset($arr_data['created_at'])?us_date_format($arr_data['created_at']):'N/A' }}</span>
                    </div>
                  </div>


                  @if(isset($arr_data['representative_user_details']) && $arr_data['representative_user_details']!='')

                    <div class="row">
                      <label class="col-sm-4">Representative</label>
                      <div class="col-sm-8">
                        <span>{{isset($arr_data['representative_user_details']['first_name'])?ucfirst($arr_data['representative_user_details']['first_name']." ".$arr_data['representative_user_details']['last_name']):""}}</span>
                      </div>
                    </div>

                  @elseif(isset($arr_data['sales_manager_details']) && $arr_data['sales_manager_details']!='')
                    <div class="row">
                      <label class="col-sm-4">Sales Manager</label>
                      <div class="col-sm-8">
                        <span>{{isset($arr_data['sales_manager_details']['first_name'])?ucfirst($arr_data['sales_manager_details']['first_name']." ".$arr_data['sales_manager_details']['last_name']):""}}</span>
                      </div>
                    </div>
                  
                  @endif 

                 
                  <div class="row">
                    <label class="col-sm-4">Shipping Status</label>
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
                    <label class="col-sm-4">Payment Term</label>
                    <div class="col-sm-8">
                      @if(isset($arr_data['payment_term']) && $arr_data['payment_term'] == 'Net30')

                      <span class="label label-success">{{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}</span>

                      @else

                         <span class="label label-success">{{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}</span>

                      @endif
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

                <div class="row">
                  <label class="col-sm-4">Billing Address</label>
                  <div class="col-sm-8">
                    <span>{{isset($arr_data['address_details']['bill_complete_address']) ?$arr_data['address_details']['bill_complete_address'] :'N/A'}}</span>
                  </div>
                </div>


                @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '') 
                  <div class="row">
                    <label class="col-sm-4">Promo Code</label>
                    <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['promo_code'])?$enquiry_arr['promo_code']:'N/A'}}</span>
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
                                   
                                   $Wholsale_sub_total += $product['product_details']['unit_wholsale_price']*$product['qty'] - $product['product_discount'];
                                }
                               
                            }
                          }
                      
                      @endphp

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
                          
                            @if($count == 0)  
                            
                              <div class="button-left-right"> 
                                  @if($arr_data['payment_term'] != 'Online/Credit')
                                  
                                    <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="return checkoutRedirect($(this));">Pay Now</a>
                                  
                                  @endif

                                  @if($arr_data['payment_term'] == '')
                                    <a class="btn btn-success pull-left" href="{{ $module_url_path.'/net_30_payment/'.base64_encode($arr_data['id'])}}" onclick="confirm_action(this,event,'Are you sure? Do you want to make Net30 payment.');"> Net30</a>
                                  @endif
                                      
                              </div>
         
                            @endif

                        @endif
                      
                        <input type="hidden" name="payment_type" id="payment_type" value="{{$arr_data['payment_term'] or ''}}">
                    </div>                    
                  </div> 

            </div>



            <div class="col-sm-6">
              <div class="row">
                <label class="col-sm-4">Total Amount</label>
                <div class="col-sm-8">
                <span>${{ isset($arr_data['total_wholesale_price'])?num_format($arr_data['total_wholesale_price']) : 'N/A' }}</span>
                </div>
              </div>

              <div class="row">
                <label class="col-sm-4">Vendor</label>
                <div class="col-sm-8">{{isset($arr_data['maker_data']['maker_details']['company_name'])?ucfirst($arr_data['maker_data']['maker_details']['company_name']):''}}</div>
              </div>
                 
              <div class="row">
                <label class="col-sm-4">Payment Status</label>
                <div class="col-sm-8">
                    @php 
                      $status = "";

                      if(isset($arr_data['transaction_mapping_details']) && isset($arr_data['transaction_mapping']))
                      { 
                        $status = isset($arr_data['transaction_mapping_details']['transaction_status'])?get_payment_status($arr_data['transaction_mapping_details']['transaction_status']):'';
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
                 

              @if(isset($arr_data['payment_due_date']) && $arr_data['payment_due_date'] == 'Net30')

                <!-- <div class="row">
                    <label class="col-sm-4">Payment Due Date</label>
                    <div class="col-sm-8">
                      <span>{{isset($arr_data['payment_due_date'])?us_date_format($arr_data['payment_due_date']):'N/A' }}</span>
                    </div>
        
                </div> -->
              @endif

              <div class="row">
                <label class="col-sm-4">Shipping Address</label>
                <div class="col-sm-8">
                  <span>{{isset($arr_data['address_details']['ship_complete_address'])?$arr_data['address_details']['ship_complete_address']:'N/A'}}</span>
                </div>
              </div>


              @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != "" && $arr_data['promo_code'] != '0')

                <div class="row">
                  <label class="col-sm-4">Promo Code</label>
                  <div class="col-sm-8">
                    <span class="label label-success">{{$arr_data['promo_code'] or ''}}</span>
                  </div>
                </div> 

              @endif
            
            </div>
          </div>
      </div>
    

     <!--  <div class="white-box small-space-nw">
       
        <div class="main-nm-retailer space-bottom">
          @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
         
          <div class="main-nm-retailer-right"><a class="btn btn-inverse" href="{{url('/')}}/retailer/my_orders/order_summary/{{base64_encode($main_split_order_no['order_no'])}}/{{base64_encode($main_split_order_no['maker_id'])}}">This order is split from : {{$main_split_order_no['order_no']}}</a>
          @endif
        </div>
        
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th width="30%">Order No.</th>
                  <td width="20%">{{ $arr_data['order_no'] or 'N/A' }}</td>

                   <th>Order Date</th>
                  <td> {{ isset($arr_data['created_at'])?us_date_format($arr_data['created_at']):'N/A' }}</td>
               
                
               </tr>
               <tr>
                 @if(isset($arr_data['representative_user_details']) && $arr_data['representative_user_details']!='')
                  <th width="30%">Repesentative</th>
                  <td width="20%">{{isset($arr_data['representative_user_details']['first_name'])?ucfirst($arr_data['representative_user_details']['first_name']." ".$arr_data['representative_user_details']['last_name']):""}}</td>
                
                @elseif(isset($arr_data['sales_manager_details']) && $arr_data['sales_manager_details']!='')
                  <th width="30%">Sales Manager Name</th>
                  <td width="20%">{{isset($arr_data['sales_manager_details']['first_name'])?ucfirst($arr_data['sales_manager_details']['first_name']." ".$arr_data['sales_manager_details']['last_name']):""}}</td>
 
                @endif 
                <th width="30%">Vendor</th>
                  <td width="20%">{{isset($arr_data['maker_data']['maker_details']['company_name'])?ucfirst($arr_data['maker_data']['maker_details']['company_name']):''}}</td>
               </tr>
               <tr>

                 
                  <th>Total Amount</th>
                  <td><span class="fa fa-dollar"></span>{{ isset($arr_data['total_wholesale_price'])?num_format($arr_data['total_wholesale_price']) : 'N/A' }}</td>

                  @if(isset($arr_data['promo_code']) && $arr_data['promo_code'] != "" && $arr_data['promo_code'] != '0')

                    <th>Promo Code </th>
                    <td><span class="label label-success">{{$arr_data['promo_code'] or ''}}</span></td>
              
     
                  @endif

               </tr>               
              <tr>
               <th>Shipping Status</th>
               <td>
                  
                  <?php $status = isset($arr_data['ship_status'])?get_order_status($arr_data['ship_status']):'N/A'?>

                  @if($status=="Pending")
                   <span class="label label-warning">{{isset($status)?ucfirst($status):''}}</span>

                  @elseif($status=="Shipped")
                   <span class="label label-success">{{isset($status)?ucfirst($status):''}}</span>

                  @elseif($status=="Failed")
                  <span class="label label-danger">{{isset($status)?ucfirst($status):''}}</span>

                  @else
                  
                   {{$status or 'N/A'}}
<?php 
                    $status = "";
                    if(isset($arr_data['transaction_mapping_details']) && isset($arr_data['transaction_mapping']))
                    {

                      $status = isset($arr_data['transaction_mapping_details']['transaction_status'])?get_payment_status($arr_data['transaction_mapping_details']['transaction_status']):'';
                    }
                  ?> @if(isset($arr_data['payment_term']) && $arr_data['payment_term'] == 'Net30')

                      <span class="label label-success">{{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}</span>
                      @else
                         {{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}
                      @endif
                  
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
                  @endif
                 
               </td>

               <th>Payment Status</th>
               <td>
                  <?php 
                    $status = "";
                    if(isset($arr_data['transaction_mapping_details']) && isset($arr_data['transaction_mapping']))
                    {

                      $status = isset($arr_data['transaction_mapping_details']['transaction_status'])?get_payment_status($arr_data['transaction_mapping_details']['transaction_status']):'';
                    }
                  ?>
                  
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
                 
               </td>
   
               </tr>
            
                <tr>
                   <th>Payment Term</th>
                   <td>
                      
                      @if(isset($arr_data['payment_term']) && $arr_data['payment_term'] == 'Net30')

                      <span class="label label-success">{{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}</span>
                      @else
                         {{isset($arr_data['payment_term'])?$arr_data['payment_term']:'N/A' }}
                      @endif
        
                   </td>


                  @if(isset($arr_data['payment_term']) && $arr_data['payment_term'] == 'Net30')

                   <th>Payment Due Date</th>
                   <td>
                       {{isset($arr_data['payment_due_date'])?us_date_format($arr_data['payment_due_date']):'N/A' }}
                   </td>
            
                   @endif
       
                </tr>
              
            <tr>
               <th>Shipping Address</th>
               <td>{{isset($arr_data['address_details']['bill_complete_address']) ?$arr_data['address_details']['bill_complete_address'] :'N/A'}}</td>

               <th>Billing Address</th>
               <td>{{isset($arr_data['address_details']['ship_complete_address'])?$arr_data['address_details']['ship_complete_address']:'N/A'}}</td>
            </tr>

            <tr>
               
                  <td colspan="4">
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
                                     
                                     $Wholsale_sub_total += $product['product_details']['unit_wholsale_price']*$product['qty'] - $product['product_discount'];
                                  }
                                 
                              }
                           }
                           
                     
                      @endphp

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
                          
                            @if($count == 0)  
                            
                              <div class="button-left-right"> 
                                  @if($arr_data['payment_term'] != 'Online/Credit')
                                  
                                    <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="return checkoutRedirect($(this));">Pay Now</a>
                                  
                                  @endif

                                  @if($arr_data['payment_term'] == '')
                                    <a class="btn btn-success pull-left" href="{{ $module_url_path.'/net_30_payment/'.base64_encode($arr_data['id'])}}" onclick="confirm_action(this,event,'Are you sure? Do you want to make Net30 payment.');"> Net30</a>
                                  @endif
                                      
                              </div>

                            
                            @endif

                        @endif
                      
                        <input type="hidden" name="payment_type" id="payment_type" value="{{$arr_data['payment_term'] or ''}}">

                  </td>
                     
            </tr>
            </table>
         </div>
      </div>
   </div> -->
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
                  <th>Unit Wholesale Price</th>
                  <th>Wholesale Sub Total</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Charges Discount</th>
                  <th>Product Discount</th>
                  <th>Total Amount </th>

               </tr>
              
               <?php
               $grand_total_amount = 0;
               ?>
               @if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)

               @foreach($arr_data['order_details'] as $quote)

                @if($quote['maker_id'] == base64_decode($enc_maker_id))
          
                  <tr>
                      <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):"N/A"}}</td>

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

                       <td><div class="truncate">{!! $quote['sku']  or 'N/A' !!}</div></td>
                      <td>{{ $quote['qty'] or 'N/A' }}</td>
                     
                   
                      <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($quote['product_details']['unit_wholsale_price'])?num_format($quote['product_details']['unit_wholsale_price']) : 'N/A' }}</td>
                     
                            <!-- $total_amount = $sub_wholesale_price+$quote['product_shipping_charge']-$quote['shipping_charges_discount']-$quote['product_discount']; -->
                      <td class="summmarytdsprice">
                         @php $sub_wholesale_price = $quote['qty'] * $quote['product_details']['unit_wholsale_price'];

                            $total_amount = $sub_wholesale_price+$quote['product_details']['shipping_charges']-$quote['shipping_charges_discount']-$quote['product_discount'];

                            $grand_total_amount += $sub_wholesale_price+$quote['product_details']['shipping_charges']-$quote['shipping_charges_discount']-$quote['product_discount'];

                         @endphp


                         <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) :0.00}}
                      </td>
                    <!--  <td>@if($quote['product_shipping_charge']>0)+@endif<span class="fa fa-dollar">{{isset($quote['product_shipping_charge'])?num_format($quote['product_shipping_charge']):'0.00'}}</td> -->


                       <td class="summmarytdsprice">@if($quote['product_details']['shipping_charges']>0)+@endif<span class="fa fa-dollar">{{isset($quote['product_details']['shipping_charges'])?num_format($quote['product_details']['shipping_charges']):'0.00'}}</td>

                      <td class="summmarytdsprice">@if($quote['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar">{{ isset($quote['shipping_charges_discount'])?num_format($quote['shipping_charges_discount']):'0.00' }}</td>

                      <td class="summmarytdsprice">@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar">{{isset($quote['product_discount'])?num_format($quote['product_discount']):'0.00' }}</td>

                      <td class="summmarytdsprice"><span class="fa fa-dollar">{{ isset($total_amount)?num_format($total_amount):0.00 }}</td>

                   </tr>

               @endif

              @endforeach
            @else
                <td colspan="7">No Record Found</td>
            @endif

            </table>
         </div>
      
            <div class="col-md-12">
               <div class="pull-right totl-grands">

                   @if(isset($arr_data['promotion_discount']) && $arr_data['promotion_discount'] != '' && $arr_data['promotion_discount'] != 0)



                <div class="viewtotal-bg">
                   <span>Total Amount :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($Wholsale_sub_total)?num_format($Wholsale_sub_total) : '0.00' }}
                  </span>
                </div>
                <div class="viewtotal-bg">
                   <span>Promotion Discount :</span>
                  <span class="viewspan">
                  - <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($arr_data['promotion_discount'])?num_format($arr_data['promotion_discount']) : 'N/A' }}
                </span>
                </div>
                <div class="viewtotal-bg border-h">
                   <span>Grand Amount (Wholesale) :</span>
                  <span class="viewspan">
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($grand_total_amount)?num_format($grand_total_amount) : 'N/A' }}
                </span>
                </div>

                @else
                  <span>Grand Amount (Wholesale) :</span>
                  
                  <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($grand_total_amount)?num_format($grand_total_amount) : 'N/A' }}

                @endif
                
                  {{-- <span>Grand Amount (Wholesale):</span>
                  <i class="fa fa-usd"></i>{{ isset($grand_total_amount)?num_format($grand_total_amount) : 'N/A' }} --}}
                 
               </div>
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
                      else
                      {
                        $payment_status = 'Failed';
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
{{-- 
                      @if($quote['maker_confirmation'] == '' && $quote['maker_confirmation'] == 0 && $quote['order_cancel_status'] == 0)
                             
                              <a data-toggle="tooltip"  data-size="small" title="Cancel Order" class="btn btn-outline btn-info btn-circle show-tooltip " href="javascript:void(0);" onclick="cancelOrder($(this));" data-order-id="{{$quote['id']}}">Cancel</a>
                            
                            @elseif($quote['order_cancel_status'] == 1)
                            
                              <a data-toggle="tooltip"  data-size="small" title="Cancel Requested" class="btn btn-outline btn-info btn-circle show-tooltip" href="javascript:void(0);" data-order-id="{{$quote['id']}}">Cancel Requested</a>

                            @elseif($quote['order_cancel_status'] == 2)


                            <a data-toggle="tooltip"  data-size="small" title="Canceled Order" class="btn btn-outline btn-info btn-circle show-tooltip " href="javascript:void(0);">Canceled</a>

                     
                     @endif --}}
                     
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

               <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">
 

 var ord_no   = '{{isset($arr_data['order_no'])?base64_encode($arr_data['order_no']):false}}';
 var maker_id = '{{isset($arr_data['maker_id'])?base64_encode($arr_data['maker_id']):false}}';
 var url      = '{{url('/checkout/representative/')}}/'+ord_no+'/'+maker_id;
 var net30    = "{{ $module_url_path.'/net_30_payment/'.base64_encode($arr_data['id'])}}";


  
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
</script>
@stop