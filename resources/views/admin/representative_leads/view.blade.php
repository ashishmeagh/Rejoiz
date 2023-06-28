@extends('admin.layout.master')                
@section('main_content')
<style type="text/css">
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

</style>

<?php
   
   $vendorId = base64_encode($leads_arr['maker_id']);
   $retailerId = base64_encode($leads_arr['retailer_id']);  

?>

@php
    $pay_vendor_button = $pay_sales_man_commission = $pay_commition = $generate_invoice = '';
    
    $promo_total_amount = 0;

    $sales_manager_commission_status = isset($leads_arr['sales_manager_commission_status'])?$leads_arr['sales_manager_commission_status']:0;

    $rep_commission_status = isset($leads_arr['rep_commission_status'])?$leads_arr['rep_commission_status']:0;

    $admin_commission = isset($leads_arr['admin_commission'])?$leads_arr['admin_commission']:0;


    $ord_wholesale_price = isset($leads_arr['total_wholesale_price'])?$leads_arr['total_wholesale_price']:0;

    $is_freeshipping = is_promocode_freeshipping(isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false);

    if($is_freeshipping == false)
    {
      $ord_wholesale_price = $ord_wholesale_price - $leads_arr['total_shipping_charges'];
    }


    $admin_commission_amount = $ord_wholesale_price*($admin_commission / 100);

    $vendor_pay_amount = $ord_wholesale_price - $admin_commission_amount;

    $representative_pay_amount = $admin_commission_amount * ($representative_commission / 100);

    if($leads_arr['ship_status'] == '1')
    {
      if($leads_arr['is_direct_payment'] == 0 && $leads_arr['payment_term'] != 'Net30' && $leads_arr['is_split_order'] == 0)
      {
       /* $pay_vendor_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="Pay Vendor"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$leads_arr['maker_id'].','.$leads_arr['id'].')" style="'.$is_disabled.'" >Pay Vendor</button>';

       $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['representative_id'].','.$leads_arr['id'].')" >Pay Representative</button>';

       $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['sales_manager_id'].','.$leads_arr['id'].')" >Pay Sales Manager</button>';*/

        $pay_vendor_button = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_vendor_btn" title="View Vendor Commission"  onclick="fillData('.$ord_wholesale_price.','.num_format($vendor_pay_amount).','.$admin_commission.','.num_format($admin_commission_amount).','.$leads_arr['maker_id'].','.$leads_arr['id'].')" style="'.$is_disabled.'" >View Vendor Commission</button>';

       $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Reps Commission"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['representative_id'].','.$leads_arr['id'].')" >View Reps Commission</button>';

       $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Sales Commission"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['sales_manager_id'].','.$leads_arr['id'].')" >View Sales Commission</button>';
      }
   
    }

    /* Pay representative commission if order payment type is direct pay\ment */
    if($leads_arr['is_direct_payment'] == 1 && $leads_arr['admin_commission_status'] == 1 && $leads_arr['payment_term'] != 'Net30' && $leads_arr['is_split_order'] == 0 && ($sales_manager_commission_status == 0 && $rep_commission_status == 0))
    {

       /*$pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Representative"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['representative_id'].','.$leads_arr['id'].')" >Pay Representative</button>';

       $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="Pay Sales Manager"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['sales_manager_id'].','.$leads_arr['id'].')" >Pay Sales Manager</button>';*/

       $pay_commition = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Reps Commission"  style="'.$rep_pay_btn.'"" onclick="payCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['representative_id'].','.$leads_arr['id'].')" >View Reps Commission</button>';

       $pay_sales_man_commission = '<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="pay_commition" title="View Sales Commission"  style="'.$sale_manager_pay_btn.'"" onclick="paySalesCommission('.$ord_wholesale_price.','.num_format($admin_commission_amount).','.$representative_commission.','.num_format($representative_pay_amount).','.$leads_arr['sales_manager_id'].','.$leads_arr['id'].')" >View Sales Commission</button>';
    }

    if($leads_arr['representative_id'] == 0) 
    {
      $pay_commition = '';
    }

   @endphp



<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-12 col-lg-4">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">Order Details</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   @include('admin.layout._operation_status')  
   <div class="col-md-12">
      <div class="white-box small-space-nw">

         @if(isset($main_split_order_no) && sizeof($main_split_order_no) > 0)
            <div class="col-md-12 mb-4">
              <div class="row">
                <a class="btn btn-success pull-left" href="{{url('/')}}/admin/leads/view/{{base64_encode($main_split_order_no['id'])}}">This order is split from : {{$main_split_order_no['order_no']}}</a>
              </div>
            </div>
            
         @endif
         <div class="clearfix"></div>

        <div class="row">
          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Order No.</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>{{ $leads_arr['order_no'] or 'N/A' }}</span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Vendor</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                 <a href="{{url('/')}}/admin/vendor/view/{{$vendorId}}" class="link_view">
                    <span>{{ $leads_arr['maker_details']['company_name'] or 'N/A' }}</span>
                  </a>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Order Date</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  <?php $us_date = us_date_format($leads_arr['created_at']); ?>
                  {{ isset($leads_arr['created_at'])?$us_date:'N/A' }}
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Shipping Status</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  @if(isset($leads_arr['ship_status']) && $leads_arr['ship_status']==0)
                      <span class="label label-warning">Pending</span>
                   @elseif(isset($leads_arr['ship_status']) &&$leads_arr['ship_status']==1)
                      <span class="label label-success">Shipped</span>
                   @elseif(isset($leads_arr['ship_status']) &&$leads_arr['ship_status']==2)
                      <span class="label label-danger">Failed</span>
                   @else
                   <span class="label label-danger">{{isset($leads_arr['ship_status'])?$leads_arr['ship_status']:''}}</span>
                   @endif
                </span>

                
              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Payment Status</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                 @php
                 
                     if($leads_arr['is_payment_status'] == 1)
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

                {{-- <span>
                  
                  @if((isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==1) && ($leads_arr['order_no'] == $leads_arr['order_no']))

                    <span class="label label-warning">Pending</span>

                  @elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==2)
                    <span class="label label-success">Paid</span>

                  @elseif(isset($leads_arr['transaction_mapping']['transaction_status']) && $leads_arr['transaction_mapping']['transaction_status']==3)
                    <span class="label label-danger">Failed</span>
                  @else
                    <span class="label label-warning">Pending</span>
                  @endif
                </span> --}}
              </div>
            </div>

          

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Payment Term</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                @if(isset($leads_arr['payment_term']) && $leads_arr['payment_term'] == 'Net30')
                 <span class="label label-danger">{{isset($leads_arr['payment_term'])?$leads_arr['payment_term']:'N/A' }}</span>
                 @else
                  <span class="label label-success">{{isset($leads_arr['payment_term']) && $leads_arr['payment_term'] != "" ?$leads_arr['payment_term']:'N/A' }}</span>
                 @endif
              </div>
            </div>

             <?php
                  $accountHolder = '';
                  if(isset($leads_arr['stripe_key_id']))
                  {
                    $getAccountDetails = get_stripe_account_details($leads_arr['stripe_key_id']);
                    
                    if($getAccountDetails)
                    {
                      $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
                    }
                  }
              ?>

              @if((isset($leads_arr['transaction_mapping']['transaction_id']) && $leads_arr['transaction_mapping']['transaction_id'] != '') && (isset($accountHolder) && $accountHolder != ''))

              <div class="row">
                 <label class="col-sm-12 col-md-12 col-lg-4"><b>Transaction Id</b></label>
                 <div class="col-sm-12 col-md-12 col-lg-8">
                
                 <span>{{$leads_arr['transaction_mapping']['transaction_id']}}</span>
                
                </div>
              </div>

              @endif

            @if(isset($accountHolder) && $accountHolder != '')

             <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4 commonlabel_bold">Payment Account Holder</label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$accountHolder}}</span>
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
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                  <a href="{{$url}}" target="_blank"><span>{{ isset($tracking_no)?$tracking_no:'--' }}</span></a>
                </div>
              </div>

            @endif 

          

            @php
           
                $trans_status = isset($leads_arr['stripe_transaction_detail']['status'])?$leads_arr['stripe_transaction_detail']['status']:'';
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

              @if(isset($leads_arr['stripe_transaction_detail']) && $leads_arr['stripe_transaction_detail']['received_by'] == $leads_arr['representative_id'])
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4
"><b>Representative commission status</b></label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$status}}</span>
                </div> 

              </div> 

              @elseif(isset($leads_arr['stripe_transaction_detail']) && $leads_arr['stripe_transaction_detail']['received_by'] == $leads_arr['sales_manager_id'])
                
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4
"><b>Sales manager commission status</b></label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$status}}</span>
                </div> 

              </div>
              @else
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4
"></label>
                <div class="col-sm-12 col-md-12 col-lg-8"></div> 

              </div>

              @endif 

          @endif  

            
          </div>

          <div class="col-sm-12 col-md-6 col-lg-6 orders_view">
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
"><b>Customer</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <a href="{{url('/')}}/admin/retailer/view/{{$retailerId}}" class="link_view">
                <span>{{ isset($leads_arr['retailer_user_details']['retailer_details']['store_name'])?$leads_arr['retailer_user_details']['retailer_details']['store_name']:'N/A' }}</span>
              </a>
              </div>
            </div>

           

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
"><b>Total Amount </b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>${{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : '0.00' }}</span>
              </div>
            </div>

              <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
"><b>Shipping Address</b></label>
              
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
               
                    {{isset($leads_arr['address_details']['ship_street_address'])?$leads_arr['address_details']['ship_street_address'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_suit_apt'])?$leads_arr['address_details']['ship_suit_apt'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_city'])?$leads_arr['address_details']['ship_city'].',':''}} 

                    {{isset($leads_arr['address_details']['ship_state'])?$leads_arr['address_details']['ship_state'].',':''}}

                    {{isset($leads_arr['address_details']['ship_country'])?get_country($leads_arr['address_details']['ship_country']).',':''}} 
                                
                    {{isset($leads_arr['address_details']['ship_zip_code'])?$leads_arr['address_details']['ship_zip_code'].',':''}}

                    Mobile.No: {{isset($leads_arr['address_details']['ship_mobile_no'])?$leads_arr['address_details']['ship_mobile_no']:''}}

                </span>
              </div>
            </div>
            @if(isset($leads_arr['payment_term']) && $leads_arr['payment_term'] == 'Net30')
             <!-- <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4
"><b>Payment Due Date</b></label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span>{{isset($leads_arr['payment_due_date'])?us_date_format($leads_arr['payment_due_date']):'N/A' }}</span> 
                </div>
              </div> -->
            @endif  

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
"><b>Billing Address</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
               
                  {{isset($leads_arr['address_details']['bill_street_address'])?$leads_arr['address_details']['bill_street_address'].',':''}} 

                  {{isset($leads_arr['address_details']['bill_suit_apt'])?$leads_arr['address_details']['bill_suit_apt'].',':''}} 

                  {{isset($leads_arr['address_details']['bill_city'])?$leads_arr['address_details']['bill_city'].',':''}} 

                  {{isset($leads_arr['address_details']['bill_state'])?$leads_arr['address_details']['bill_state'].',':''}}

                  {{isset($leads_arr['address_details']['bill_country'])?get_country($leads_arr['address_details']['bill_country']).',':''}}

                  {{isset($leads_arr['address_details']['bill_zip_code'])?$leads_arr['address_details']['bill_zip_code'].',':''}}
                  Mobile.No: {{isset($leads_arr['address_details']['bill_mobile_no'])?$leads_arr['address_details']['bill_mobile_no']:''}}

              </span>
              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
 commonlabel_bold">Payment Type</label>
                  
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    @if(isset($leads_arr['is_direct_payment']) && $leads_arr['is_direct_payment'] == 1)
                      <span class="label label-success">Direct</span>
                    @else
                       <span class="label label-success">In-Direct</span>
                    @endif
    
                  </div>
                </div>

                

             @if(isset($leads_arr['order_rejected_reason']) && $leads_arr['order_rejected_reason']!='')

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4
"><b>Vendor Rejection Reason</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                
                <span>{!!$leads_arr['order_rejected_reason'] or ''!!}</span>
                
              </div>
            </div>

            @endif

            @if(isset($leads_arr['promo_code']) && $leads_arr['promo_code'] != '0' && $leads_arr['promo_code'] != '')
              <div class="row">
                <label class="col-sm-12 col-md-12 col-lg-4
"><b>Promo Code</b></label>
                <div class="col-sm-12 col-md-12 col-lg-8">
                  <span class="label label-success">{{$leads_arr['promo_code'] or ''}}</span>
                </div>
              </div>
            @endif  




              @if(isset($split_order_arr) && sizeof($split_order_arr) > 0)
                    <div class="row">
                    <label class="col-sm-12 col-md-12 col-lg-4
"><b>Split Orders</b></label>
        
                    @foreach($split_order_arr as $key => $split_order)
                      <div class="col-sm-12 col-md-12 col-lg-8">
                      
                        <a href="{{url('/')}}/admin/leads/view/{{isset($split_order['id'])?base64_encode($split_order['id']):'0'}}"><span class="label label-success">{{$split_order['order_no'] or 'N/A'}}</span> </a>
                      </div>
                    @endforeach
                  </div>
                  @endif

              <div class="row">
                <div class="col-sm-12">
                {!! $pay_vendor_button.'  '. $pay_commition.'&nbsp;' .$pay_sales_man_commission !!}

                @if($leads_arr['is_direct_payment'] == 1 && $leads_arr['admin_commission_status'] == 0 &&$leads_arr['ship_status'] == 1)
                
                   <!-- <button type="button" class="btn btn-circle btn-success btn-outline show-tooltip"  id="generate_invoice" title="Generate Invoice" onclick = 'generate_invoice("{{$leads_arr['id']}}")'>Generate Invoice</button> -->
                @endif
              </div>
              </div>



          </div>
        </div>
         
      </div>
   </div>


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
                  <th>Price</th>
                  <th>Sub Total</th>
                  {{-- <th>Shipping Charges</th>
                  <th>Shipping Discount</th>
                  <th>Product Discount</th> --}}
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

               @if(isset($leads_arr['leads_details']) && count($leads_arr['leads_details'])>0)
               
               @foreach($leads_arr['leads_details'] as $quote)
               <tr>
                  <td>{{ $quote['product_details']['product_name'] or 'N/A' }}</td>
                  <td>{{ $quote['product_details']['brand_details']['brand_name'] or 'N/A'}}</td>
                  <td> {{-- <div class="truncate">{!! $quote['product_details']['description'] or 'N/A'!!} </div> --}}

                  @if(isset($quote['product_details']['description']) && strlen($quote['product_details']['description']) > 70 && $quote['product_details']['description']!='' )   
                        @php
                       
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
                  <td>{{ $quote['sku'] or 'N/A' }}</td>
                  <td>{{ $quote['qty'] or 'N/A' }}</td>
                  
                
                      <!-- $product_discount = $unit_total*$quote['product_details']['product_discount']/100; -->
                 
                     @php
                     $product_discount = 0;

                     $unit_wholesale_price = $quote['product_details']['unit_wholsale_price'];

                     $unit_total = $quote['qty'] * $unit_wholesale_price;

                     $sub_wholesale_total = $unit_total+$quote['shipping_charges']-$quote['shipping_charges_discount'];

                     if ($quote['product_details']['product_discount']!='') {
                       
                        $product_discount = isset($quote['product_discount'])?num_format($quote['product_discount']):0;
                     }
                     $shipping_charges = 0;

                     if ($quote['shipping_charges'] != '') {
                        $shipping_charges = $quote['shipping_charges'] + $quote['shipping_charges_discount'];
                     }
                     $shipping_discount = 0;

                     if ($quote['shipping_charges_discount'] != '') {
                        $shipping_discount = $quote['shipping_charges_discount'];
                     }

                     $total_amount =  $unit_total+$shipping_charges-$product_discount;

                      $promo_total_amount += $total_amount;
                     @endphp

                  <td class="summmarytdsprice">
                  <span class="fa fa-dollar"></span>{{ isset($unit_wholesale_price)?num_format($unit_wholesale_price) : 0.00}}
                  </td>

                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($unit_total)?num_format($unit_total) : 0.00}}</td>
                  
                  {{-- <td class="summmarytdsprice">
                     @if($quote['shipping_charges']>0)+@endif<span class="fa fa-dollar"></span>{{ isset($shipping_charges)?num_format($shipping_charges) : 0.00}}
                  </td>

                  <td class="summmarytdsprice">
                     @if($quote['shipping_charges_discount']>0)-@endif<span class="fa fa-dollar"></span>{{ isset($shipping_discount)?num_format($shipping_discount) : 0.00}}
                  </td>

                  <td class="summmarytdsprice">
                     @if($product_discount>0)-@endif<span class="fa fa-dollar"></span>{{ isset($product_discount)?num_format($product_discount) : 0.00}}
                  </td> --}}
                  
                  <td class="summmarytdsprice">
                       <span class="fa fa-dollar"></span>{{ isset($total_amount)?num_format($total_amount) : 0.00}}
                  </td>
                  
                  <?php
                     
                    $tot_qty+= (float)$quote['qty'];
                    $tot_unit_price_wholesale+= (float)$unit_wholesale_price;
                    $tot_sub_tot_wholesale+=(float)$unit_total;
                    $tot_shipping_charges+=(float)$shipping_charges;
                    $tot_shipping_discount+=(float)$shipping_discount;
                    $tot_pro_dis+=(float)$product_discount;
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
                  {{-- <td class="summmarytdsprice"><span class="fa fa-dollar"></span> {{num_format($tot_shipping_charges)}}</td>
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

                  {{-- <div class="row">
                    <div class="left">
                      <h3>Total Amount :</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
                  </div> --}}
                      
              @if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0)
                      
                  {{-- <div class="row">
                    <div class="left">
                      <h3>Promotion Discount ({{ isset($orderCalculationData['discount_per'])?$orderCalculationData['discount_per'] : 0 }}%):</h3>
                    </div>
                    <div class="right">
                      <span>@if(isset($orderCalculationData['discount_amt'])&& $orderCalculationData['discount_amt']>0)-@endif<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['discount_amt'])?num_format($orderCalculationData['discount_amt']) : 0.00 }}</span>
                    </div>
                  </div> --}}
              @endif  

              @if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges'] != 0)
                 
                  {{-- <div class="row">
                    <div class="left">
                      <h3>Promotion(Free shipping) :</h3>
                    </div>
                    <div class="right">
                   <span>@if(isset($orderCalculationData['promotion_shipping_charges']) && $orderCalculationData['promotion_shipping_charges']>0)-@endif<i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['promotion_shipping_charges'])?num_format($orderCalculationData['promotion_shipping_charges']) : 0.00 }}</span>
                    </div>
                  </div> --}}

              @endif
                       
               
              <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}</span>
                    </div>
              </div>
              {{-- <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}</span>
                    </div>
              </div> --}}
              
              @else
                 {{--  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00 }}
                      </span>
                    </div>
                  </div> --}}
                  <div class="row totalrow">
                    <div class="left">
                      <h3>Total Amount</h3>
                    </div>
                    <div class="right">
                      <span><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($orderCalculationData['sub_grand_total'])?num_format($orderCalculationData['sub_grand_total']) : 0.00 }}
                      </span>
                    </div>
                  </div>
                 @endif    
              </div>

          <div class="clearfix"></div>
      </div>

   </div>
@include('product_description_popup')
   @php

   if(isset($leads_arr['stripe_transaction_data']) && count($leads_arr['stripe_transaction_data']) > 0)
   {

    foreach ($leads_arr['stripe_transaction_data'] as $stripeData) 
    {      
        $trans_status = isset($stripeData['status'])?$stripeData['status']:'';

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
        $userRole = Sentinel::findById($stripeData['received_by'])->roles;
       
        if(isset($userRole[0]->slug) && $userRole[0]->slug != '')
        {
          if($userRole[0]->slug == 'maker')
          {
            $label = 'Vendor';
          }
          elseif($userRole[0]->slug == 'admin')
          {
            $label = 'Admin';
          }
          elseif($userRole[0]->slug == 'representative' || $userRole[0]->slug == 'sales_manager')
          {
            $label = 'Rep/Sales';
          }
        }


   if(isset($status) && $status != '')
   {
    @endphp
        <div class="col-md-12">
          <div class="white-box">
             <label>
                <h3>{{$label}} Commission Details</h3>
             </label>
             <div class="table-responsive">
                
                <table class="table table-striped table-bordered view-porifile-table">
                   <tr>
                      <th>Order No</th>
                      <th>Order Amount</th>
                      <th>Admin Commission Percent</th>
                      @if($label == 'Admin')
                        <th>Amount Paid By Vendor</th>
                        @else
                        <th>Admin Commission Amount</th>
                      @endif

                      @if($label == 'Rep/Sales')
                        <th>{{$label}} Commission Percent</th>
                      @endif

                      @if($label != 'Admin')
                      <th>Amount Paid to {{$label}}</th>
                      @endif
                      

                      <th>Status</th>
                      <th>Transfer Account Holder</th>
                      <th>Transaction Id</th>

                   </tr>
                  
                    <?php
                      

                      $orderAmount = isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : 0.00;

                      $is_freeshipping = is_promocode_freeshipping(isset($leads_arr['promo_code'])?$leads_arr['promo_code']:false);

                      if($is_freeshipping == true)
                      {
                          $orderShippingCharges = 0.00;
                      }
                      else
                      {
                         $orderShippingCharges = isset($leads_arr['total_shipping_charges'])?$leads_arr['total_shipping_charges']:0.00;
                      }


                      $excludingAmount = $orderAmount - $orderShippingCharges;
      
                      
                      if($leads_arr['is_direct_payment']==1)
                      {
                        $adminCommissionAmount = isset($stripeData['amount'])?num_format($stripeData['amount']):'0.00';

                        $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 

                        $amountPaidToVendor = $excludingAmount - $adminCommissionAmount;
                      }
                      else
                      {
                        $amountPaidToVendor = isset($stripeData['amount'])?num_format($stripeData['amount']):'0.00';

                        $adminCommissionAmount = $excludingAmount - $amountPaidToVendor;
                        $adminCommission = "0.00";
                        if($excludingAmount != "0" && $excludingAmount != "0.00"){
                        $adminCommission =  ($adminCommissionAmount * 100) / $excludingAmount; 
                      }

                      } 
                     
                      if($label == 'Rep/Sales')
                      {
                        $repSaleCommission = isset($leads_arr['rep_sales_commission'])?$leads_arr['rep_sales_commission']:0;

                        $adminCommissionAmount = $excludingAmount * ($leads_arr['admin_commission'] / 100);

                        $repSaleAmount = $adminCommissionAmount * ($repSaleCommission / 100);

                      }

                    ?>

                  <tr>
                    <td>{{isset($leads_arr['order_no'])?$leads_arr['order_no']:"N/A"}}</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($excludingAmount)?num_format($excludingAmount):0.00}}
                      <label class="shippingLabel">Excluded shipping costs</label>
                    </td>
                    
                    <td><i class="label" aria-hidden="true"></i>{{isset($leads_arr['admin_commission'])?num_format($leads_arr['admin_commission']):'N/A'}} %</td>

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($adminCommissionAmount)?num_format($adminCommissionAmount):0.00}}</td>

                     @if($label == 'Rep/Sales')
                      
                        <td><i class="label" aria-hidden="true"></i>{{isset($leads_arr['admin_commission'])?num_format($leads_arr['rep_sales_commission']):'N/A'}} %</td>

                        
                        <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($repSaleAmount)?num_format($repSaleAmount):0.00}}</td>

                    @endif

                    @if($label == 'Vendor')

                    <td><i class="fa fa-usd" aria-hidden="true"></i>{{isset($amountPaidToVendor)?num_format($amountPaidToVendor):0.00}}</td>

                    @endif

                    <td><span class="label label-success">{{isset($status)?$status:'Pending'}}</span></td>
                    
                    <?php

                        $accountHolder = '';
                        if(isset($leads_arr['transfer_commission_stripe_key_id']))
                        {
                          $getAccountDetails = get_stripe_account_details($leads_arr['transfer_commission_stripe_key_id']);

                          if($getAccountDetails)
                          {
                            $accountHolder = isset($getAccountDetails['account_holder'])?$getAccountDetails['account_holder']:'';
                          }
                        }
                    ?>

                    <td><span class="label label-success">{{isset($accountHolder)?$accountHolder:'N/A'}}</span></td>

                    <td>{{isset($stripeData['transaction_id'])?$stripeData['transaction_id']:'N/A'}}</td>
                  </tr>
                </table>
             </div>
           </div>
         </div>
         @php
               
    }
    }
   }

    @endphp

   
      

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



<!--Vendor Payment Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
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
                <div class="admin-commission-lnk">Total Order Amount($) :</div>
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
        {{-- <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button> --}}
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
     
      </div>
    </div>
  </div>
</div>

<!-- Representative Payment Modal -->
<div class="modal fade rep-Modal" id="repPaymentModal" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Pay Commission</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="repPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="rep_id"     id="rep_id">
          <input type="hidden" name="sales_id"   id="sales_id">
          <input type="hidden" name="order_id"   id="rep_orderId" >
          <input type="hidden" name="amount"     id="rep_amount">

          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
               <div class="innerbox">
                <div class="admin-commission-lnk"><span id="commission_lable"></span></div>
              <div class="admin-commission-lnk-right"><span id="rep_commission"></span>%
              </div>
               </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Total Order Amount($) :</div>
              <div class="admin-commission-lnk-right">$<span id="rep_order_amount"></span>
              </div>
              <label class="shippingLabel">Excluded shipping costs</label>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission($) :</div>
              <div class="admin-commission-lnk-right">$<span id="admin_commission_amount"></span>
              </div>
              </div>
            </div>            
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk"><span id="commission_amount_lable"></span></div>
              <div class="admin-commission-lnk-right">$<span id="rep_actual_amount"></span>
              </div>
              </div>
            </div>

          </div>

        </form>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-primary" onclick="payRepCommission()" >Pay</button>-->
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
     
      </div>
    </div>
  </div>
</div>

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

<script type="text/javascript">

  function fillData(orderPrice,vendorAmount,adminCommission,adminCommissionAmount,makerId,orderId)
{
  $('.vendor-Modal').modal('show');
  $('#order_amount').html(orderPrice.toFixed(2));    
  $('#actual_amount').html(adminCommissionAmount.toFixed(2));   
  $('#admin_commission').html(adminCommission.toFixed(2));      
  $('.pay_amount').html(vendorAmount.toFixed(2));    
  $('#maker_id').val(makerId);    
  $('#amount').val(vendorAmount.toFixed(2));    
  $('#orderId').val(orderId);    
}

function payCommission(orderPrice,adminCommission,repcommission,repCommissionAmount,repId,orderId)
{
  $('.rep-Modal').modal('show');
  $('#rep_commission').html(repcommission);    
  $('#rep_pay_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_actual_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_order_amount').html(orderPrice.toFixed(2));    
  $('#admin_commission_amount').html(adminCommission.toFixed(2));    
  $('#rep_id').val(repId);  
  $('#commission_lable').html("Representative Commission(%) :");
  $('#commission_amount_lable').html("Representative Commission($) :");
  $('#rep_amount').val(repCommissionAmount.toFixed(2));    
  $('#rep_orderId').val(orderId);    
}

function paySalesCommission(orderPrice,adminCommission,repcommission,repCommissionAmount,salesId,orderId)
{
  $('.rep-Modal').modal('show');
  $('#rep_commission').html(repcommission);    
  $('#rep_pay_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_actual_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_order_amount').html(orderPrice.toFixed(2));    
  $('#admin_commission_amount').html(adminCommission.toFixed(2));    
  $('#sales_id').val(salesId);  
  $('#commission_lable').html("Sales Manager Commission(%) :");
  $('#commission_amount_lable').html("Sales Manager Commission($) :");  
  $('#rep_amount').val(repCommissionAmount.toFixed(2));    
  $('#rep_orderId').val(orderId);    
}

function payVendor()
{
  var paymentFormData = new FormData($("#vendorPaymentForm")[0]);
  commssionTransaction(paymentFormData);
}

function commssionTransaction(data)
{
  $.ajax({
          url: '{{url('/admin/leads')}}'+'/pay_commission',
          type:"POST",
          data: data,
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
                  swal({
                         title:"Success", 
                         text: data.message, 
                         type: data.status,
                         allowEscapeKey : false,
                         allowOutsideClick: false
                       },
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

function payRepCommission()
{
  var repPaymentData = new FormData($("#repPaymentForm")[0]);
  commssionTransaction(repPaymentData);
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
                  swal({title: "Success", 
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
  var order_no = order_no;
  var orderType = 'rep-sales';

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