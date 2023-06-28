@extends('maker.layout.master')                
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
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
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
                  <label class="col-sm-4"><span class="semibold-mkr">Order No.</span></label>
                  <div class="col-sm-8">
                     <span>{{ $enquiry_arr['order_no'] or 'N/A' }}</span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Customer</span></label>
                  <div class="col-sm-8">
                     <span> {{isset($enquiry_arr['user_details']['first_name'])?ucfirst($enquiry_arr['user_details']['first_name']):"N/A"}} {{isset($enquiry_arr['user_details']['last_name'])?ucfirst($enquiry_arr['user_details']['last_name']):"N/A"}}</span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Order Date</span></label>
                  <div class="col-sm-8">
                     <span>
                     {{ isset($enquiry_arr['created_at'])?us_date_format($enquiry_arr['created_at']):'N/A' }}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Total Amount (Retail)</span></label>
                  <div class="col-sm-8">
                     <span>
                     ${{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 'N/A' }}
                     </span>
                  </div>
               </div>
               @php
               /*$payment_status = isset($enquiry_arr['transaction_mapping']['transaction_status'])?$enquiry_arr['transaction_mapping']['transaction_status'] : 'N/A';*/

               $payment_status = isset($enquiry_arr['is_payment_status'])?$enquiry_arr['is_payment_status'] : 'N/A';
               @endphp
            </div>
            <div class="col-sm-6">
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Shipping Address</span></label>
                  <div class="col-sm-8">
                     <span>
                     {{isset($enquiry_arr['shipping_addr'])?$enquiry_arr['shipping_addr']:'N/A'}}
                     </span>
                  </div>
               </div>
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Billing Address</span></label>
                  <div class="col-sm-8">
                     <span>
                     {{isset($enquiry_arr['billing_addr'])?$enquiry_arr['billing_addr']:'N/A'}}
                     </span>
                  </div>
               </div>

               @if(isset($enquiry_arr['order_rejected_reason']) && $enquiry_arr['order_rejected_reason']!='')
              
                  <div class="row">
                    <label class="col-sm-4"><span class="semibold-mkr">Vendor Rejection Reason</span></label>
                    <div class="col-sm-8">
                      <span>{!!$enquiry_arr['order_rejected_reason'] or ''!!}</span>
                    </div>
                    
                  </div>

               @endif

               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Payment Status</span></label>
                  <div class="col-sm-8">
                     <span>
                       @if($payment_status == 1) 
                       <span class="label label-success">Paid</span>
                       @else
                       <span class="label label-warning">Pending</span>
                       @endif
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
               @if(isset($enquiry_arr['promo_code'])  && $enquiry_arr['promo_code'] != '')
               <div class="row">
                  <label class="col-sm-4"><span class="semibold-mkr">Promo Code</span></label>
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
                  <th>Order No</th>
                  <th>Product</th>
                  <th>Brand</th>
                  <th>Description</th>
                  <th>SKU No</th>
                  <th>QTY</th>
                  <th>Unit Price (Retail)</th>
                  <th>Sub Total (Retail)</th>
                  <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product  Discount</th>
                  <th>Total Amount</th>
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
                  <td>{{isset($quote['product_details']['product_name'])?$quote['product_details']['product_name']:"N/A"}}</td>
                  <td>
                     {{isset($quote['product_details']['brand_details']['brand_name'])?$quote['product_details']['brand_details']['brand_name']:"N/A"}}
                  </td>
                  @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70)
                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="70">
                  <td>
                     <div class="truncate">{!!$quote['product_details']['description']!!}</div>
                  </td>
                  @else
                  <td>{!!$quote['product_details']['description']!!}</td>
                  @endif
                  <td class="summmarytdsprice">{{isset($quote['sku_no'])?$quote['sku_no']:""}}</td>
                  <td class="summmarytdsprice">{{ $quote['qty'] or 'N/A' }}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{$quote['unit_retail_price'] or 'N/A' }}</td>
                  <td class="summmarytdsprice">
                     @php 
                     $retail_price = $quote['qty'] * $quote['unit_retail_price'];

                     $sub_retail_total = $retail_price+$quote['shipping_charge']-$quote['shipping_discount']-$quote['product_discount'];
                     $total_amount += $sub_retail_total;
                     @endphp
                     <span class="fa fa-dollar"></span>{{isset($retail_price)?num_format($retail_price): 0.00}}
                  </td>
                  <td class="summmarytdsprice">@if($quote['shipping_charge']>0)+@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}</td>
                  <td class="summmarytdsprice">@if($quote['shipping_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}</td>
                  <td>@if($quote['product_discount']>0)-@endif<span class="fa fa-dollar"></span>{{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>
                  <td >
                     <span class="fa fa-dollar"></span>{{isset($sub_retail_total)?num_format($sub_retail_total) : 0.00}}
                  </td>
               </tr>
               <?php
                                        
                    $tot_qty+= (float)$quote['qty'];

                    $tot_unit_price_wholesale+=(float)$quote['unit_retail_price'];
                    $tot_sub_tot_wholesale+=(float)$retail_price;
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
                  <td></td>
                  <td class="summmarytdsprice">Total :</td>
                  <td class="summmarytdsprice">{{$tot_qty}}</td> 
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_unit_price_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_sub_tot_wholesale)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_discount)}}</td>
                  <td ><span class="fa fa-dollar"></span> {{num_format($tot_pro_dis)}}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_amt_column)}}</td>
                </tr>
               @else
               <td colspan="7">No Record Found</td>
               @endif
            </table>
         </div>
        {{--  <div class="col-md-12 p-0">
            <div class="pull-left" style="margin-top:20px;">
               <div class="btn-cnt">
                  <button type="button" class="btn btn-success"  id="is_approved" value="1">Approved</button>
                  <button type="button" class="btn btn-inverse"  id="is_rejected" value="0">Rejected</button>
               </div>
            </div>
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
         <div class="row">
            <div class="col-md-12 rw-customer-cancelled-order-request-button-amt-flex">
              <div class="pull-left">
               <div class="btn-cnt">
                  <button type="button" class="btn btn-success"  id="is_approved" value="1">Approve</button>
                  <button type="button" class="btn btn-inverse"  id="is_rejected" value="0">Reject</button>
               </div>
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
                      <span> <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
                  </div>
                 @endif    
              </div>
          <!--   <div class="pull-right totl-grands" >
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
               <i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}

               @endif
             
            </div> -->
         </div>
       </div>
         <div class="clearfix"></div>
      </div>
   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>


<div id="ReasonModal" class="modal fade" data-replace="true" style="display: none;">
    <div class="modal-dialog">
    
        <div class="modal-content sign-up-popup">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>

            <div class="modal-body">
              <form id="rejection_form"> 
                  <div class="login-form-block">
                      <div class="login-content-block">
                   
                          <div class="form-group">
                              <label style="margin-bottom:10px; font-weight:600;" for="reason" class="col-form-label">Order Rejection Reason<i class="red">*</i></label>
                              <div class="">
                               <textarea row="4" id="reason" name="reason" data-parsley-required="true" data-parsley-required-message="Please enter order rejection reason."></textarea>
                              </div>
                              <span class='red'>{{ $errors->first('reason') }}</span>
                          </div>

                          <span class='red'id="reason_error">{{ $errors->first('reason') }}</span>
          
                           <div class="categorynote-main">
                              
                            <div class="pull-right">
                           
                            <a style="color: #000;" class="logi-link-block btn btn-inverse" data-toggle="modal" id="btn_submit">Submit</a>
                            </div>
                     
                           <div class="clearfix"></div>
                           </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
              </form>
            </div>
        </div>
    </div>
</div>
<script>
   var module_url_path = '{{$module_url_path or ''}}';  
   var token           =  $('input[name="csrfToken"]').attr('value');
   var order_id        =  $('#order_id').val();
   
   
   $(document).ready(function(){
       $('#btn_ship_order').click(function(){
         swal({
           title: "Need Confirmation",
           text: "Are you sure? Do you want to ship this order.",
           type: "warning",
           showCancelButton: true,
           confirmButtonClass: "btn-danger",
           confirmButtonText: "OK",
           closeOnConfirm: false
         },
         function(){
   
            $.ajax({
              url:'{{url(config('app.project.maker_panel_slug'))}}/payment/{{isset($enquiry_arr['id'])?base64_encode($enquiry_arr['id']) : ''}}',
              method:'GET',
              dataType:'JSON',
              beforeSend : function()
              {
                showProcessingOverlay();
                
              },
              success:function(response)
              {
                 hideProcessingOverlay();
                
                if(typeof response =='object')
                {
                  if(response.status && response.status=="success")
                  {   
                   swal({
                        title: "Success",
                        text: response.description,
                        type: "success",
                        showCancelButton: false,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        closeOnCancel: false
                      },
                      function(isConfirm) {
                        if (isConfirm) {
                          location.reload(true);
                        } 
                      });
                    
                  }
                  else
                  {
                    swal('Error',response.description,response.status);
                  }
                  
                }
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) 
              {
                 
              }
           });
   
         });
       });
     });
   
   
   $('#is_approved').click(function(){

      swal({
            title: "Need Confirmation",
            type: "warning",
            text: 'Do you want to approve the cancel order request?',
            showCancelButton: true,
            confirmButtonColor: "#8CD4F5",
            confirmButtonText: "OK",
            closeOnConfirm: true
        },
        function(isConfirm,tmp)
        { 
          if(isConfirm==true)
          {
               showProcessingOverlay(); 
           
               var order_cancel_status  = 2;
              
               $.ajax({
                           url: module_url_path+'/order_confirmation',
                           type:"POST",
                           data: {status:order_cancel_status,order_id:order_id,_token:"{{ csrf_token() }}"},
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
                                           closeOnConfirm: false
                                        },
                                       function(isConfirm,tmp)
                                       {
                                         if(isConfirm==true)
                                         {
                                            window.location.href = response.link;
                                         }
                                       });
                                }
                                else
                                {                
                                   swal('Error',response.description,'error');
                                }  
                            }
                      
               }); 

          }

        });
        
   
   });

   $('#btn_submit').click(function(){
      order_cancel_status = 0;
    
     // if($('#rejection_form').parsley().validate()==false) return;

      var reason = tinyMCE.get('reason').getContent();
      
      if(reason == '')
      {
         $('#reason_error').html('Please enter order rejection reason.');
         return false;
      }
      else
      {
         $.ajax({
               url: module_url_path+'/order_confirmation',
               type:"POST",
               data: {status:order_cancel_status,order_id:order_id,reason:reason,_token:"{{ csrf_token() }}"},
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
                               closeOnConfirm: false
                            },
                           function(isConfirm,tmp)
                           {
                             if(isConfirm==true)
                             {
                                window.location.href = response.link;
                             }
                           });
                    }
                    else
                    {                
                       swal('Error',response.description,'error');
                    }  
               }
              
         });

    }   


   });
   
   
   $('#is_rejected').click(function(){
   
      $('#ReasonModal').modal('show');
      // order_cancel_status = 0;
      //  $.ajax({
      //          url: module_url_path+'/order_confirmation',
      //          type:"POST",
      //          data: {status:order_cancel_status,order_id:order_id,_token:"{{ csrf_token() }}"},
      //          dataType:'json',
      //          beforeSend: function() 
      //          {
      //            showProcessingOverlay();                 
      //          },
      //          success:function(response)
      //          {
      //              hideProcessingOverlay(); 
      //              if(response.status == 'success')
      //              {
                       
      //                  swal({
      //                          title: 'Success',
      //                          text: response.description,
      //                          type: 'success',
      //                          confirmButtonText: "OK",
      //                          closeOnConfirm: false
      //                       },
      //                      function(isConfirm,tmp)
      //                      {
      //                        if(isConfirm==true)
      //                        {
      //                           window.location.href = response.link;
      //                        }
      //                      });
      //               }
      //               else
      //               {                
      //                  swal('Error',response.description,'error');
      //               }  
      //          }
              
      //    });
   
   });
     
</script>
@stop