@extends('admin.layout.master')                
@section('main_content')

<style type="text/css">
  .pointerhand{    cursor: pointer;}
   .main-nm-retailer-right {
    margin-left: 110px;
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
     padding-bottom: 20px;
  }
}
 .text_btn{ 
         color:#0290FF; 
         cursor: pointer;
      }
   .text_btn:hover{
          text-decoration: underline;
          font-weight:bold;
       }
       
</style>

<?php

  $ordNo = base64_encode($enquiry_arr['order_no']);
  $vendorId = base64_encode($enquiry_arr['maker_id']);
  $custId = base64_encode($enquiry_arr['customer_id']);
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
        <div class="row">
          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-4 commonlabel_bold">Order No.</label>
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
                <label class="col-xs-12 col-sm-12 col-md-4 col-lg-4"><span class="semibold-mkr">Payment Type</span></label>
                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                  @if(isset($enquiry_arr['is_direct_payment']) && $enquiry_arr['is_direct_payment'] == 1)
                    <span class="label label-success">Direct</span>
                  @else
                     <span class="label label-success">In-Direct</span>
                  @endif
  
                </div>
              </div>

         

            <div class="row">
                    <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Status</label>
                    <div class="col-sm-12 col-md-12 col-lg-8">
                        @php

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
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Refund Status</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <?php
                    $refund_status = isset($enquiry_arr['refund_status'])? $enquiry_arr['refund_status']:'0';

                    $onclick = "return false;";   $is_refund=0;  
                    switch($refund_status)
                    {
                      case '1': $spanLabel_class="success"; 
                                $spanLabel="Paid";  break;
                      default : $spanLabel_class="warning"; 
                                $spanLabel="Pending"; 
                                if($enquiry_arr['refund_status'] == 0 && $enquiry_arr['is_direct_payment'] == 0)
                                    {
                                       $is_refund=1;  
                                    }
                                break;
                    }
                ?>
                <span class="label label-{{$spanLabel_class}}" style="cursor: pointer;">{{$spanLabel}}</span>

                   &nbsp; &nbsp;  
                  @if($is_refund==1)         
                     <i style="font-size: 12px;">
                        
                      {{-- <em class="text_btn pointerhand" onclick="refundProcess('{{$enquiry_arr['id']}}')"> click </em> to refund.  --}}

                      <a href="javascript:void(0);" class="text_btn" onclick="refundProcess('{{$enquiry_arr['id']}}')"> Click to refund.</a>

                     </i>
                  @endif

              </div>
            </div>

          @endif  

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Order Status</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span class="label label-success">Cancelled</span>
              </div>
            </div>
  

            @if(isset($enquiry_arr['promo_code']) && $enquiry_arr['promo_code'] != '0' && $enquiry_arr['promo_code'] != '')
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Promo Code</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$enquiry_arr['promo_code'] or ''}}</span>
                </div>
              </div>
            @endif                     

          </div>

          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Customer</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                 <a href="{{url('/')}}/admin/customer/view/{{$custId}}" class="link_view">
                    <span>{{ isset($enquiry_arr['user_details']['first_name'])?$enquiry_arr['user_details']['first_name']:'N/A' }} {{ isset($enquiry_arr['user_details']['last_name'])?$enquiry_arr['user_details']['last_name']:'N/A' }}</span>
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

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Billing Address</label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>{{$billing_address}}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
   </div>
   @php
   $total_amount = 0;
   @endphp
   <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>Summary</h3>
         </label>
         <div class="table-responsive">
          {{-- {{dd($enquiry_arr)}} --}}
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
                  {{-- @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)

                     <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">

                     <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A' !!}</div></td>
                   
                   @else

                     <td>{!! $quote['product_details']['description'] or 'N/A' !!}</td>
                   
                   @endif --}}

                   <td>
                    @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70 && $quote['product_details']['description']!='' )   
                        @php
                        /*$desc_html = html_entity_decode($quote['product_details']['description']);
                        $desc = str_limit($desc_html,70);*/

                        $desc_html = $desc = "";
                        $desc_html = ($quote['product_details']['description']);
                        $desc = substr(strip_tags( $desc_html), 0, 70);
                      
                        @endphp               
                       <p class="prod-desc"> {!! $desc !!}
                        <br>
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

                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$quote['product_details']['retail_price'] or 'N/A' }}</td>
                         
                  <td class="summmarytdsprice">
                     @php  

                      //$sub_retail_price = $quote['qty'] * $quote['wholesale_price'] 
                      $sub_retail_price = $quote['qty'] * $quote['product_details']['retail_price']; 

                      $sub_retail_total = $sub_retail_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];

                      $total_amount += $sub_retail_total; 

                      @endphp

                     <span class="fa fa-dollar"></span>{{ isset($sub_retail_price)?num_format($sub_retail_price) :0.00}}

                  </td>

                  <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}
                  </td>

                  <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}
                  </td>

                  <td class="summmarytdsprice">@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}
                  </td>
               
                  <td class="summmarytdsprice">
                     <span class="fa fa-dollar"></span>{{ isset($sub_retail_total)?num_format($sub_retail_total):0.00}}
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
@include('product_description_popup')
<script>

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
@stop