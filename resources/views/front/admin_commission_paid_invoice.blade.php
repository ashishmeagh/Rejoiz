@php
$site_setting_arr = get_site_settings();
$site_base_img    = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
$site_name        = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
$site_address     = isset($site_setting_arr['site_address']) ? $site_setting_arr['site_address'] : false;
$arr_site_data    = get_invoice_logo($site_base_img,$site_name);

$site_logo          = isset($arr_site_data['site_img'])?$arr_site_data['site_img']:'';
$site_default_image = isset($arr_site_data['site_default_image'])?$arr_site_data['site_default_image']:'';

$admin_email  = isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:'';
$cnt = 0;

$total_order_amount = $total_admin_commission = $total_vendor_commission = $total_reps_commission = $total_vendor_commission = 0;
// dd($invoiceData,$arrOrderData,$arrUserData);
@endphp
<body style="background-color: #eaebec;">
<style type="text/css">
 .data_container {
  width: 200px;
  word-wrap: break-word;
}

.data_container2 {
  word-wrap: break-word;

}
</style>



<div style="margin:0 auto; width:100%;">
<table width="100%" bgcolor="#fff" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #ddd;font-family:Arial, Helvetica, sans-serif;">
    <tr>
        <td colspan="2">
            <table bgcolor="#d3d7de" width="100%" border="0" cellspacing="10" cellpadding="0">
                <tr>
                    <td width="40%" style="font-size:40px; color: #333;"> <img src="{{ $site_logo }}" alt="{{ $site_name }}" /></td>
                    <td width="60%" style="text-align:right; color: #333;">
                       {{$site_name or ''}}</h3> 
                        <h1 style="line-height:25px;margin:0px;padding:0px;">INVOICE</h1>
                       
                    </td>
                </tr>
            </table>
        </td>
    </tr>
   <td>

    <table width="100%" style="padding:0 10px 0 10px" >
      <tr>
        <td colspan="2" >
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                   
                    <td  style="text-align:left; ">
                        <h3 style="line-height:25px;margin:0px;padding:0px;">From</h3>
                        <p style="font-size:12px;padding:0px;margin:0px;">
                            {{$site_name or ''}} <br>
                           {{$site_address or ''}} </p>
                    </td>
                     <td  style="font-size:40px; text-align: right; color:#fff;"> Invoice</td>
                </tr>
            </table>
        </td>
    </tr>
 
    <tr>
        <td colspan="2" style="height:10px;">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td>
             <table style="font-size:12px;">
               <tr>
                   <td style="font-size:14px;padding:0px;margin:0px;">
                       <h3 style="line-height:25px;margin:0px;padding:0px;">To</h3>
                   </td>
               </tr>
              
               <tr>
                   <td style="font-size:12px;padding:0px;margin:0px;vertical-align: top;">

                        @php
                        
                          $company_name = isset($arrUserData['business_details']['company_name'])?$arrUserData['business_details']['company_name'].'.':'';

                          $address = isset($arrUserData['personla_details']['address'])?$arrUserData['personla_details']['address']:'';

                          $postCode = isset($arrUserData['personla_details']['post_code'])?$arrUserData['personla_details']['post_code']:'';

                        @endphp

                        @if(isset($company_name) && $company_name != '')
                          Company Name : {{$company_name}}                         <br>
                        @endif
                        Name  : {{isset($arrUserData['personla_details']['first_name'])?$arrUserData['personla_details']['first_name']:''}} {{isset($arrUserData['personla_details']['last_name'])?$arrUserData['personla_details']['last_name']:'N/A'}}<br>

                        @if(isset($address) && $address != '')
                           Address:
                            <div class="data_container">
                              {{$address}}
                                 <br>
                            </div>
                        @endif

                        @if(isset($postCode) && $postCode != '')
                           Post Code:
                            <div class="data_container">
                              {{$postCode}}
                                 <br>
                            </div>
                        @endif
                       
                   </td>
               </tr>
           </table>
        </td>
     
        <td style="text-align: right;">
           <table style="font-size:12px;" align="right">
               <tr>
                   <td>
                       <b>Invoice No. :</b> {{isset($invoiceData['invoice_no'])?$invoiceData['invoice_no']:0}}  <br>
                   </td>
               </tr>
               <tr>
                   <td>              
                        <b>Invoice Date:</b> {{isset($invoiceData['invoice_date'])?$invoiceData['invoice_date']:date("m-d-Y")}} <br>
                   </td>
               </tr>
            
           </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="height:10px;">
            &nbsp;
        </td>
    </tr>

     <tr>
        <td colspan="2">
            
            <table width="100%" cellspacing="0" cellpadding="10" style="font-size: 13px;">
                <tr style="background-color:#eeeff1;">
                    <th align="left" width="5%">Sr. No.</th>
                    <th align="left" width="10%">Order Date</th>
                    <th align="left" width="6%">Order No</th>
                    <th align="left" width="20%">Order Amount<br>(Excluded shipping costs)</th>
                    <th align="left" width="10%">Admin Commission(%)</th>
                    <th align="left" width="10%">Admin Commission($)</th>

                    @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                    <th align="left" width="10%">Rep/Sales Commission(%)</th>
                    <th align="left" width="10%">Rep/Sales Commission($)</th>
                    @else
                    <th align="left" width="10%">Vendor Commission Amount</th>                             
                    @endif

                </tr>

                @if(isset($arrOrderData['rep_sales_order_data']) && count($arrOrderData['rep_sales_order_data']) > 0)

                   @foreach($arrOrderData['rep_sales_order_data'] as $repKey => $repSalesData)

                  @php
                      $cnt = $cnt+1;
                      $order_amount = 0;
                      $is_freeshipping = false;

                      $total_wholesale_price = isset($repSalesData['total_wholesale_price'])?$repSalesData['total_wholesale_price']:0; 

                      $total_shipping_charge = isset($repSalesData['total_shipping_charges'])?$repSalesData['total_shipping_charges']:0;

                      $order_amount = $total_wholesale_price - $total_shipping_charge;


                      $is_freeshipping = is_promocode_freeshipping($repSalesData['promo_code']);

                      if($is_freeshipping == true)
                      {
                         $order_amount = $total_wholesale_price ;
                      }

                      $admin_commission = isset($repSalesData['admin_commission'])?$repSalesData['admin_commission']:0;

                      $admin_commission_amount = $order_amount*($admin_commission / 100);

                      $vendor_pay_amount = $order_amount - $admin_commission_amount;

                      if(isset($isRepSalesOrder) && $isRepSalesOrder != false)
                      {
                        $representative_commission = isset($repSalesData['rep_sales_commission'])?$repSalesData['rep_sales_commission'] : 0;

                        $representative_pay_amount = $admin_commission_amount * ($representative_commission / 100);
                      }

                      $total_order_amount             += $order_amount;
                      $total_admin_commission         += $admin_commission_amount;
                      $total_vendor_commission        += $vendor_pay_amount;

                      

                    @endphp

                    @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                       @php  $total_reps_commission += $representative_pay_amount; @endphp
                    @endif

                    <tr>
                        <td align="left">{{$cnt}}</td>
                        <td align="left">{{isset($repSalesData['created_at'])?format_date($repSalesData['created_at']):0}}</td>
                        <td align="left">{{isset($repSalesData['order_no'])?$repSalesData['order_no']:0}}</td>
                        <td align="left">${{isset($order_amount)?num_format($order_amount):0}}</td>
                        <td align="left">{{isset($repSalesData['admin_commission'])?num_format($repSalesData['admin_commission']):0}}</td>
                        <td align="left">${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</td>

                        @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                          <td align="left">{{isset($representative_commission)?num_format($representative_commission):0}}</td>
                          <td align="left">${{isset($representative_pay_amount)?num_format($representative_pay_amount):0}}</td>
                        @else
                          <td align="left">${{isset($vendor_pay_amount)?num_format($vendor_pay_amount):0}}</td>
                        @endif

                        
                    </tr>
                  @endforeach
                @endif

                @if(isset($arrOrderData['retailer_order_data']) && count($arrOrderData['retailer_order_data']) > 0)

                   @foreach($arrOrderData['retailer_order_data'] as $retailerKey => $retailerData)

                    @php
                      $cnt = $cnt+1;
                      $is_freeshipping = false;
                      $order_amount = $admin_commission = $admin_commission_amount = $total_shipping_charge = $total_wholesale_price = 0; 

                      $total_wholesale_price = isset($retailerData['total_wholesale_price'])?$retailerData['total_wholesale_price']:0; 


                      $total_shipping_charge = isset($arrOrderData['retailer_order_shipping_charge'][$retailerKey])?$arrOrderData['retailer_order_shipping_charge'][$retailerKey]:0;

                      $order_amount = $total_wholesale_price - $total_shipping_charge;

                      $is_freeshipping = is_promocode_freeshipping($retailerData['promo_code']);

                      if($is_freeshipping == true)
                      {
                         $order_amount = $total_wholesale_price;
                      }

                      $admin_commission = isset($retailerData['admin_commission'])?$retailerData['admin_commission']:0;

                      $admin_commission_amount = $order_amount*($admin_commission / 100);

                      $vendor_pay_amount = $order_amount - $admin_commission_amount;

                      $total_order_amount             += $order_amount;
                      $total_admin_commission        += $admin_commission_amount;
                      $total_vendor_commission        += $vendor_pay_amount;

                    @endphp


                    <tr>
                        <td align="left">{{$cnt}}</td>
                        <td align="left">{{isset($retailerData['created_at'])?format_date($retailerData['created_at']):0}}</td>
                        <td align="left">{{isset($retailerData['order_no'])?$retailerData['order_no']:0}}</td>
                        <td align="left">${{isset($order_amount)?num_format($order_amount):0}}</td>
                        <td align="left">{{isset($retailerData['admin_commission'])?num_format($retailerData['admin_commission']):0}}</td>
                        <td align="left">${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</td>
                        <td align="left">${{isset($vendor_pay_amount)?num_format($vendor_pay_amount):0}}</td>
                        
                    </tr>
                  @endforeach
                @endif


                 @if(isset($arrOrderData['customer_order_data']) && count($arrOrderData['customer_order_data']) > 0)

                   @foreach($arrOrderData['customer_order_data'] as $CustomerKey => $CustomerData)

                    @php

                    // dd($CustomerData);
                      $cnt = $cnt+1;
                      $is_freeshipping = false;
                      $order_amount = $admin_commission = $admin_commission_amount = 0; 

                      $total_wholesale_price = isset($CustomerData['total_retail_price'])?$CustomerData['total_retail_price']:0; 

                       $total_shipping_charge = isset($CustomerData['total_shipping_charges'])?$repSalesData['total_shipping_charges']:0;

                       $total_shipping_charge = isset($arrOrderData['customer_order_shipping_charge'][$CustomerKey])?$arrOrderData['customer_order_shipping_charge'][$CustomerKey]:0;

                      // $total_shipping_charge = isset($arrOrderData['retailer_order_shipping_charge'][$CustomerKey])?$arrOrderData['retailer_order_shipping_charge'][$CustomerKey]:0;

                      $order_amount = $total_wholesale_price - $total_shipping_charge;

                      $is_freeshipping = is_promocode_freeshipping($CustomerData['promo_code']);

                      if($is_freeshipping == true)
                      {
                         $order_amount = $total_wholesale_price;
                      }
                    
                      $admin_commission = isset($CustomerData['admin_commission'])?$CustomerData['admin_commission']:0;

                      $admin_commission_amount = $order_amount*($admin_commission / 100);

                      $vendor_pay_amount = $order_amount - $admin_commission_amount;

                      $total_order_amount             += $order_amount;
                      $total_admin_commission        += $admin_commission_amount;
                      $total_vendor_commission        += $vendor_pay_amount;

                    @endphp


                    <tr>
                        <td align="left">{{$cnt}}</td>
                        <td align="left">{{isset($CustomerData['created_at'])?format_date($CustomerData['created_at']):0}}</td>
                        <td align="left">{{isset($CustomerData['order_no'])?$CustomerData['order_no']:0}}</td>
                        <td align="left">${{isset($order_amount)?num_format($order_amount):0}}</td>
                        <td align="left">{{isset($CustomerData['admin_commission'])?$CustomerData['admin_commission']:0}}</td>
                        <td align="left">${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</td>
                        <td align="left">${{isset($vendor_pay_amount)?num_format($vendor_pay_amount):0}}</td>
                        
                    </tr>
                  @endforeach
                @endif



                @if(isset($arrOrderAmount) && count($arrOrderAmount) > 0)
                 <tr style="background-color:#eeeff1;">
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">${{isset($arrOrderAmount['total_order_amount'])?num_format($arrOrderAmount['total_order_amount']):0}}</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">${{isset($arrOrderAmount['total_admin_commission'])?num_format($arrOrderAmount['total_admin_commission']):0}}</td>
                    
                    @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                      <td align="left">&nbsp;</td>
                      <td align="left">${{isset($arrOrderAmount['total_vendor_commission'])?num_format($arrOrderAmount['total_vendor_commission']):0}}</td>
                    @else
                        <td align="left">${{isset($arrOrderAmount['total_vendor_commission'])?num_format($arrOrderAmount['total_vendor_commission']):0}}</td>
                    @endif
                </tr>
                <tr>
                  <td colspan="5">&nbsp;</td>
                   <td style="font-style: 14px; font-weight: 600;">Total:</td>

                  @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                    <td align="left">&nbsp;</td>
                   <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($arrOrderAmount['total_vendor_commission'])?num_format($arrOrderAmount['total_vendor_commission']):0}}</span>
                    </td>
                  @else
                     <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($arrOrderAmount['total_vendor_commission'])?num_format($arrOrderAmount['total_vendor_commission']):0}}</span>
                    </td>
                  @endif
                  
                  
                </tr>
              @else 
                  <tr style="background-color:#eeeff1;">
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">${{isset($total_order_amount)?num_format($total_order_amount):0}}</td>
                    <td align="left">&nbsp;</td>
                    <td align="left">${{isset($total_admin_commission)?num_format($total_admin_commission):0}}</td>
                    {{-- <td align="left">${{isset($total_vendor_commission)?num_format($total_vendor_commission):0}}</td> --}}
                   @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                    <td align="left">&nbsp;</td>
                   <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($total_reps_commission)?num_format($total_reps_commission):0}}</span>
                    </td>
                  @else
                     <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($total_vendor_commission)?num_format($total_vendor_commission):0}}</span>
                    </td>
                  @endif                    
                  </tr>
                  <tr>
                    <td colspan="5">&nbsp;</td>
                     <td style="font-style: 14px; font-weight: 600;">Total:</td>
                    @if(isset($isRepSalesOrder) && $isRepSalesOrder == true)
                    <td align="left">&nbsp;</td>
                   <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($total_reps_commission)?num_format($total_reps_commission):0}}</span>
                    </td>
                  @else
                     <td style="text-align: left; font-style: 14px; font-weight: 600;">
                       <span style="display: inline-block;">${{isset($total_vendor_commission)?num_format($total_vendor_commission):0}}</span>
                    </td>
                  @endif
                  </tr>
                @endif


            </table>
        </td>
    </tr>

</table>
    </td>
          <tr>
              <td colspan="9" style="padding: 20px 20px 5px; color: #fff; background-color: #404040; text-align: center;"> If you have any questions about this invoice, please contact
              </td>
          </tr>
         
          <tr>
              <td colspan="9"style="text-align: center; color: #fff; font-size: 13px; background-color: #404040;">
                  <b>Email:</b> {{isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:""}},&nbsp;&nbsp;&nbsp; <b>Website:</b> {{$site_setting_arr['website_url']}}
              </td>
          </tr>
         
          <tr>
              <td colspan="9" style="padding: 5px 10px 10px; color: #fff; text-align: center; font-size: 20px; background-color: #404040;">
                 <b>Thank you for your business!</b>
              </td>
          </tr>  
         
      </table>     
</div>
</body>




