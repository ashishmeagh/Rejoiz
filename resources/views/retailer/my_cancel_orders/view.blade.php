@extends('retailer.layout.master')                
@section('main_content')

<!-- Page Content -->
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
         @include('retailer.layout._operation_status')  
      <div class="white-box small-space-nw">
        <div class="row">
          <div class="col-sm-12 col-md-12 col-lg-6">
              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Order No.</span></label>
                  <div class="col-sm-8">
                      <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                  </div>
                  <div class="clearfix"></div>
              </div>

              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Order Date</span></label>
                  <div class="col-sm-8">
                      <span>{{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</span>
                  </div>
                  <div class="clearfix"></div>
              </div>

              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Order Status</span></label>
                  <div class="col-sm-8">
                      <span class="label label-success">Cancelled</span>
                  </div>
                  <div class="clearfix"></div>
              </div>
             
               @if($enquiry_arr['transaction_mapping'] && $enquiry_arr['transaction_mapping']['transaction_status']==2)        
               <div class="row row-main-vw">
                <label class="col-sm-4"><span>Refund Status</span></label>
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

              @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code']!='')
               <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Promo Code</span></label>
                  <div class="col-sm-8">
                     <span class="label label-success">{{$enquiry_arr['promo_code']}}</span>
                  </div>
                  <div class="clearfix"></div>
              </div>
              @endif

          </div>

          <div class="col-sm-12 col-md-12 col-lg-6">
              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Total Amount (Wholesale)</span></label>
                  <div class="col-sm-8">
                      <span>${{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</span>
                  </div>
                  <div class="clearfix"></div>
              </div>

              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Vendor</span></label>
                  <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['maker_data']['company_name'])?ucfirst($enquiry_arr['maker_data']['company_name']):""}}</span>
                  </div>
                  <div class="clearfix"></div>
              </div>


              <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Shipping Address</span></label>
                  <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['shipping_addr']) || isset($enquiry_arr['shipping_addr_zip_code'])?$enquiry_arr['shipping_addr'] .', '.$enquiry_arr['shipping_addr_zip_code'] :'N/A'}}
                        <!-- Mobile.No : {{isset($enquiry_arr['user_details']['retailer_details']['ship_contact_no'])?$enquiry_arr['user_details']['retailer_details']['ship_contact_no']:''}} --></span>

                  </div>
                  <div class="clearfix"></div>
              </div>


               <div class="row row-main-vw">
                  <label class="col-sm-4"><span>Billing Address</span></label>
                  <div class="col-sm-8">
                      <span>{{isset($enquiry_arr['billing_addr']) || isset($enquiry_arr['billing_addr_zip_code'])?$enquiry_arr['billing_addr'].', '.$enquiry_arr['billing_addr_zip_code']:'N/A'}}
                      <!-- Mobile.No : {{isset($enquiry_arr['user_details']['retailer_details']['bill_contact_no'])?$enquiry_arr['user_details']['retailer_details']['bill_contact_no']:''}} -->

                      </span>
                  </div>
                  <div class="clearfix"></div>
              </div>
          </div>

        </div>
         <!-- <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th width="30%">Order No.</th>
                  <td width="20%">{{ $enquiry_arr['order_no'] or 'N/A' }}</td>
                 

                  <th>Total Amount (Wholesale)</th>
                  <td><span class="fa fa-dollar"></span>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</td> 
               </tr>
               <tr>
             
                 <th>Order Date</th>
                  <td> {{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</td>
                
                 <th width="30%">Vendor</th>
                 <td width="20%">{{isset($enquiry_arr['maker_data']['company_name'])?ucfirst($enquiry_arr['maker_data']['company_name']):""}}</td>

               </tr>

               <tr>
               </tr>
               <tr>
                   <th>Shipping Address</th>
                  <td>{{isset($enquiry_arr['shipping_addr']) || isset($enquiry_arr['shipping_addr_zip_code'])?$enquiry_arr['shipping_addr'] .', '.$enquiry_arr['shipping_addr_zip_code'] :'N/A'}}</td> 

                  <th>Billing Address</th>
                  <td>{{isset($enquiry_arr['billing_addr']) || isset($enquiry_arr['billing_addr_zip_code'])?$enquiry_arr['billing_addr'].', '.$enquiry_arr['billing_addr_zip_code']:'N/A'}}</td>
                 
                 
               </tr>               
           

               <tr>
          
                 <th>Order Status</th>
                  <td><span class="label label-success">Cancelled</span></td>

                  @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code']!='')
  
                     <th>Promo Code </th>
                     <td><span class="label label-success">{{$enquiry_arr['promo_code']}}</span></td>

                  @endif
               </tr>


         

            </table>
         </div> -->
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
                  <th>Unit Price (Wholesale)</th>
                  <th>Sub Total (Wholesale)</th>
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

                          {{--  @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

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
                  

                            <td><div class="truncate">{!! $quote['sku_no']  or 'N/A' !!}</div></td>
                           <td>{{ $quote['qty'] or 'N/A' }}</td>

                           <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$quote['unit_wholsale_price'] or 'N/A' }}</td>
                         
                           <td class="summmarytdsprice">
                              @php 

                             // $sub_wholesale_price = $quote['qty'] * $quote['wholesale_price'];
                              $sub_wholesale_price = $quote['qty'] * $quote['unit_wholsale_price'];
                             
                              $sub_wholesale_total = $sub_wholesale_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];

                              $total_amount += $sub_wholesale_price;

                              @endphp


                              <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : "N/A"}}
                           </td>

                           <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}</td>

                           <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}</td>

                           <td class="summmarytdsprice">@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>

                           <td class="summmarytdsprice">
                              <span class="fa fa-dollar"></span>{{isset($sub_wholesale_total)?num_format($sub_wholesale_total) : "N/A"}} 
                           </td>
                        </tr>
                        <?php
                                        
                          $tot_qty+= (float)$quote['qty'];

                          $tot_unit_price_wholesale+=(float)$quote['unit_wholsale_price'];
                          $tot_sub_tot_wholesale+=(float)$sub_wholesale_price;
                          $tot_shipping_charges+=(float)$quote['shipping_charge'];
                          $tot_shipping_discount+=(float)$quote['shipping_discount'];
                          $tot_pro_dis+=(float)$quote['product_discount'];
                          $tot_amt_column+=(float)$sub_wholesale_total;
                          
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
                <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</span>
              </div>
            </div>
          @endif  
            
        </div>

      <!--       <div class="pull-right totl-grands" >
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
               <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}

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
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>

               <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a>
            </div>
         </div>
      </div>
   </div>
</div>


@include('product_description_popup')
<!-- END Main Content -->
@stop