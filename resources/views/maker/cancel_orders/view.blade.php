@extends('maker.layout.master')              
@section('main_content')
<style>
.row{
  padding-bottom: 20px;
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
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
</div>
<!-- .row -->
<div class="row">
   <div class="col-md-12">
        @include('admin.layout._operation_status')
      <div class="white-box small-space-nw">
        <div class="row">
          <div class="col-sm-6">
              <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Order No.</span></label>
                  <div class="col-sm-8">
                    <span>{{$enquiry_arr['order_no'] or 'N/A'}}</span>
                  </div>
              </div>

              <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Order Date</span></label>
                  <div class="col-sm-8">
                    <span>{{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}</span>
                  </div>
              </div>

              @php 
                $shipping_addr = $billing_addr = ""; 

                if(isset($enquiry_arr['shipping_addr']))
                { 

                    $shipping_addr = $enquiry_arr['shipping_addr'];

                    if(isset($enquiry_arr['shipping_addr_zip_code'])) 
                    {
                      $shipping_addr = $shipping_addr . " ".$enquiry_arr['shipping_addr_zip_code'];
                    }
                    
                }   

                if(isset($enquiry_arr['billing_addr']))
                { 
                      $billing_addr = $enquiry_arr['billing_addr'];

                      if(isset($enquiry_arr['billing_addr_zip_code'])) 
                      {
                        $billing_addr = $billing_addr . " ".$enquiry_arr['billing_addr_zip_code'];
                      }

                      
                } 

              @endphp

              <div class="row">
                    <label class="col-sm-4"><span class="semibold-mkr">Payment Status</span></label>
                    <div class="col-sm-8">
                        @php
                       /* $status = isset($enquiry_arr['transaction_mapping']['transaction_status'])?get_payment_status($enquiry_arr['transaction_mapping']['transaction_status']):'';*/


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

            @if($status=="Paid")    
              <div class="row">
                <label class="col-sm-4 commonlabel_bold">Refund Status</label>
                <div class="col-sm-8">
                  <?php

                      $refund_status = isset($enquiry_arr['refund_status'])? $enquiry_arr['refund_status']:'0';

                      $onclick = "return false;";
                      $is_refund=0; 

                      switch($refund_status)
                      { 
                        case '1': $spanLabel_class="success"; 
                                  $spanLabel="Paid";  break;
                        default : $spanLabel_class="warning"; 
                                  $spanLabel="Pending"; 

                                  if($enquiry_arr['refund_status'] == 0 && $enquiry_arr['is_direct_payment'] == 1)
                                  {
                                     $is_refund = 1;  
                                  } 
                                 
                                  break;
                      }
                  ?>
                  <span class="label label-{{$spanLabel_class}}" style="cursor: pointer;">{{$spanLabel}}</span>

                    &nbsp; &nbsp;  
                    @if($is_refund==1)         
                      <i style="font-size: 12px;">
                        {{-- <em class="text_btn" onclick="refundProcess('{{$enquiry_arr['id']}}')"> click </em> to refund.  --}}

                         <a class="text_btn" href="javascript:void(0);" onclick="refundProcess('{{$enquiry_arr['id']}}')">Click to refund.</a> 
                      </i>
                    @endif

                </div>
              </div>

            @endif  

              <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Order Status</span></label>
                  <div class="col-sm-8">
                    <span class="label label-success">Cancelled</span>
                  </div>
              </div>

               <div class="row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Payment Type</span></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    @if(isset($enquiry_arr['is_direct_payment']) && $enquiry_arr['is_direct_payment'] == 1)
                      <span class="label label-success">Direct</span>
                    @else
                       <span class="label label-success">In-Direct</span>
                    @endif
    
                  </div>
                </div>

                @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '')
          
                  <div class="row">
                    <label class="col-sm-4"><span class="semibold-mkr">Promo Code</span></label>
                    <div class="col-sm-8">
                      <span>{{$enquiry_arr['promo_code'] or ''}}</span>
                    </div>
                  </div>
               
              @endif

          </div>

          <div class="col-sm-6">
             <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Retailer</span></label>
                  <div class="col-sm-8">
                    {{-- <span>{{isset($enquiry_arr['user_details']['retailer_details']['store_name'])?ucfirst($enquiry_arr['user_details']['retailer_details']['store_name']):""}}</span> --}}
                    <span>{{isset($enquiry_arr['user_details']['retailer_details']['dummy_store_name'])?ucfirst($enquiry_arr['user_details']['retailer_details']['dummy_store_name']):""}}</span>
                  </div>
              </div>

               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Total Amount</span></label>
                  <div class="col-sm-8">
                    <span>${{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</span>
                  </div>
              </div>

               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Billing Addresss</span></label>
                  <div class="col-sm-8">
                    <span>{{$billing_addr or 'N/A'}}</span>
                  </div>
              </div>

               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Shipping Address</span></label>
                  <div class="col-sm-8">
                    <span>{{$shipping_addr or 'N/A'}}</span>
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
                  <th>Order No</th>
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
                  <td>{{isset($enquiry_arr['order_no'])?$enquiry_arr['order_no']:"N/A"}}</a></td>
                  <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):"N/A"}}</td>

                  <td>
                     {{isset($quote['product_details']['brand_details']['brand_name'])?ucfirst($quote['product_details']['brand_details']['brand_name']):"N/A"}}
                  </td>
                  
                 {{-- 
                  @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                       <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                       <td><div class="truncate">{!!$quote['product_details']['description']!!}</div></td>
                     
                     @else

                       <td>{!!$quote['product_details']['description']!!}</td>
                     
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

                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$quote['unit_wholsale_price'] or 'N/A' }}</td>
                
                  <td class="summmarytdsprice">
                     @php 
                     
                     $sub_wholesale_price = $quote['qty'] * $quote['unit_wholsale_price'];

                     $sub_wholesale_total = $sub_wholesale_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];
                     $total_amount += $sub_wholesale_price;

                     @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : "N/A"}}
                  </td>

                  <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}</td>

                  <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}</td>

                  <td>@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>

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
                  <td></td>
                  <td>Total :</td>
                  <td>{{$tot_qty}}</td> 
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
           {{--  <div class="col-md-12">
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
            </div> --}}


            <div class="col-sm-6 pull-right viewsummaryamtbox">

              @if((isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0) || (isset($orderCalculationData['promotion_shipping_charges']))) 

                  <div class="row">
                    <div class="left">
                      <h3>Total Amount :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}
                      </span>
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
                      <span>-<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}
                      </span>
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
<!-- 
        <div class="col-md-12">
            <div class="pull-right totl-grands" >
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
             
            </div>
         </div> -->
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
   <!-- end refund detail -->
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>

              {{--  <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a> --}}
            </div>
         </div>
      </div>
   </div>
</div>
@include('product_description_popup')
<!-- END Main Content -->
@stop

<script type="text/javascript">
  
function refundProcess(order_id)
{ 
    swal({  
              title:'Need Confirmation',
              text : "Are you sure? Do you want to pay refund.",
              type : "warning",
              showCancelButton: true,                
              confirmButtonColor: "#8CD4F5",
              confirmButtonText: "OK",
              closeOnConfirm: true
        },
        function(isConfirm,tmp)
        {
              if(isConfirm==true)
              {
                     
                  $.ajax({
                        url:'{{url($module_url_path.'/refund_process')}}',
                        type: 'GET',
                        dataType:'json',
                        data: {'order_id':order_id},
                        beforeSend: function() 
                        {
                         showProcessingOverlay();                 
                        },

                        success:function(data)
                        {
                            hideProcessingOverlay();   
                            if(data.status =='success')
                            {    
                                swal({
                                      title: "Success",
                                      text: data.msg,
                                      type: data.status,
                                      confirmButtonText: "OK",
                                      closeOnConfirm: false
                                    },
                                    function(isConfirm) {
                                      if (isConfirm) 
                                      {
                                         location.reload();
                                      } 
                                    });
                            }
                            else
                            {   
                              swal(data.status,data.msg,'error');
                            }
                        }
                  });

              }


        }); 
}

</script>