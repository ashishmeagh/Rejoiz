@php
$site_setting_arr = get_site_settings();
$site_base_img    = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
$site_name        = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

$arr_site_data    = get_invoice_logo($site_base_img,$site_name);

$site_logo          = isset($arr_site_data['site_img'])?$arr_site_data['site_img']:'';
$site_default_image = isset($arr_site_data['site_default_image'])?$arr_site_data['site_default_image']:'';

$admin_email  = isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:'';
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
            <table bgcolor="#fff" width="100%" border="0" cellspacing="10" cellpadding="0">
                <tr>
                    <td width="40%" style="font-size:40px; color: #fff;"> <img src="{{ $site_logo }}" alt="{{ $site_name }}" /></td>
                    <td width="60%" style="text-align:right; color: #fff;">
                        <h3 style="line-height:25px;margin:0px;padding:0px;">{{$site_name or ''}}</h3>
                        {{-- <p style="font-size:12px;padding:0px;margin:0px;">**PLEASE DO NOT SEND CHECK OR CASH TO THIS ADDRESS**</p> --}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
   
    <tr>
        <td colspan="2" style="background-color: #d3d7de;padding:10px 10px 10px 30px;font-size:12px;">
           
            
            <table width="100%">
                <tr>
                    <td width="50%">
                         <h3 style="font-size:18px;padding:0;margin:0px;">Order ID: {{isset($order_no)?$order_no:$order_no}} </h3>
                    </td>
                    <td width="50%" style="background-color: #d3d7de;padding:10px;font-size:12px; text-align: right;">

                        @if(isset($customer_data['created_at']))

                            <b>Order Date:</b> {{isset($customer_data['created_at'])?us_date_format($customer_data['created_at']):''}}

                        @else

                           <b>Order Date:</b> {{date("m-d-Y")}}

                        @endif
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

        <td colspan="2" style="padding:10px 30px 10px 30px; font-size:12px;">
            <table width="100%">
             <tbody>
                <tr>
                <td width="30%" style="font-size:12px; text-align: left; word-break: break-all;"> <h3 style="margin-bottom: 5px;">Order From ({{isset($Role)?$Role:'Customer'}}) :</h3>
                <div class = "data_container">
                <b>{{isset($customer_data['user_details']['first_name'])?$customer_data['user_details']['first_name'].' ':''}}{{isset($customer_data['user_details']['last_name'])?$customer_data['user_details']['last_name'].'.':''}}</b><br>
                    </div>
                </td>
                
                </tr>
                <tr>
                    <td style=" border-bottom: 2px solid #d5d8dc;" colspan="3" height="10px">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <table width="100%">
                             <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Shipping Address:</h3>
                            <div class="data_container">
                                @if(isset($customer_data['address_details']))

                                    {{isset($customer_data['address_details']['ship_complete_address'])?$customer_data['address_details']['ship_complete_address']:'N/A'}}
                                @else
                                    {{isset($customer_data['shipping_addr'])?$customer_data['shipping_addr']:'N/A'}}
                                    <br>
                                    {{isset($customer_data['shipping_addr_zip_code'])?$customer_data['shipping_addr_zip_code']:''}}
                                @endif
                            </div>
                           </td>

                           <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Billing Address:</h3>
                            <div class="data_container">
                                @if(isset($customer_data['address_details']))
                                
                                    {{isset($customer_data['address_details']['bill_complete_address'])?$customer_data['address_details']['bill_complete_address']:'N/A'}}
                                @else
                                    {{isset($customer_data['billing_addr'])?$customer_data['billing_addr']:'N/A'}}
                                    <br>
                                    {{isset($customer_data['billing_addr_zip_code'])?$customer_data['billing_addr_zip_code']:''}}
                                @endif
                                
                            </div>
                           </td>
                        </table>
                    </td>
                   
                </tr>
                 <tr>
                    <td style=" border-bottom: 2px solid #d5d8dc;" colspan="3" height="10px">&nbsp;</td>
                </tr>

                <tr>
                    <td colspan="3">
                       <table width="100%">
                            <td width="50%" style="font-size:12px;"> 
                            <h3 style="margin-bottom: 5px;">Payment Type: </h3>
                            <div class="data_container">
                                {{isset($customer_data['payment_term'])?$customer_data['payment_term']:'-'}}
                            </div>
                       
                        </td>    

                        @if($customer_data['payment_due_date'] !='')
                            <td width="50%" style="font-size:12px;">
                                <h3 style="margin-bottom: 5px;">Payment Due Date: </h3>
                                <div class="data_container">

                                    {{isset($customer_data['payment_due_date'])?$customer_data['payment_due_date']:'N/A'}}
                                </div>
                            </td>
                        
                        @endif
                       </table>
                    </td>
                   
                </tr>
                @if(isset($customer_data['charge_status']))
                <tr>
                    <td style=" border-bottom: 2px solid #f68525;" colspan="3" height="10px">&nbsp;</td>
                </tr>
                    <tr>
                        <td colspan="3">
                            <table width="100%">
                                <td width="50%" style="font-size:12px;"> 
                                    <h3 style="margin-bottom: 5px;">Payment Status: </h3>
                                    <div class="data_container">
                                        {{isset($customer_data['charge_status'])?$customer_data['charge_status']:'-'}}
                                    </div>
                           
                                </td>    

                           
                            </table>
                        </td>
                    </tr>
                @endif    

             </tbody>
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
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Sr. No.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Name/Description</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Qty.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Unit Price</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Sub Total</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Charges</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Discount</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Discount</th>
                    
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Total</th>
                </tr>
                 <?php 
                    $tot_qty = 0;
                    $tot_unit_price=0.00;
                    $tot_shipping_charges=0.00;
                    $tot_pro_dis=0.00;
                    $tot_shipping_dis_amt=0.00;
                    $tot_pro_final_tot=0.00;  
                    $tot_sub_total = 0.00; 
                    $final_sub_total = 0.00;
                ?>

                @php  $cnt=0; @endphp
                @if(isset($order) && count($order)>0) 
                @foreach($order as $key => $ord)
                <?php

                        $product_final_total = $final_total = $product_discount_amt = $shipping_discount_amt = 0;
                        if(isset($ord['product_discount_amount']))
                        {
                            $product_discount_amount = isset($ord['product_discount_amount'])?$ord['product_discount_amount']:0;

                            
                            $product_discount_amt = isset($ord['product_discount_amount'])?num_format($ord['product_discount_amount']):0;

                            if(isset($ord['product_discount_amount']) && $ord['product_discount_amount'] > 0)
                            {
                                $product_discount_amount = '-'.(isset($ord['product_discount_amount'])?$ord['product_discount_amount']:0);

                                $product_discount_amt = isset($ord['product_discount_amount'])?num_format($ord['product_discount_amount']):0.00;
                            }   

                        }
                        if(isset($ord['product_discount']))
                        {
                            $product_discount_amount = isset($ord['product_discount'])?$ord['product_discount']:0;

                            
                            $product_discount_amt = isset($ord['product_discount'])?num_format($ord['product_discount']):0;

                            if(isset($ord['product_discount']) && $ord['product_discount'] > 0)
                            {
                                $product_discount_amount = '-'.(isset($ord['product_discount'])?$ord['product_discount']:0);

                                $product_discount_amt = isset($ord['product_discount'])?num_format($ord['product_discount']):0.00;
                            }   

                        }
                        
                        $shipping_discount = isset($ord['shipping_discount'])?$ord['shipping_discount']:0;

                        $shipping_discount_amt = '$'.isset($ord['shipping_discount'])?num_format($ord['shipping_discount']):0;

                        if(isset($ord['shipping_discount']) && $ord['shipping_discount'] > 0)
                        {
                            $shipping_discount = '-'.($ord['shipping_discount']);

                            $shipping_discount_amt =isset($ord['shipping_discount'])?num_format($ord['shipping_discount']):0.00;
                        }


                        $shipping_charges  = isset($ord['shipping_charges'])?$ord['shipping_charges']:0;
                        $shipping_discount = isset($ord['shipping_discount'])?$ord['shipping_discount']:0;
                        $product_discount =  isset($ord['product_discount_amount'])?$ord['product_discount_amount']:0;

                        $product_final_total = ($ord['item_qty']*$ord['unit_price']) + $shipping_charges - $shipping_discount - $product_discount;

                        $tot_sub_total = ($ord['item_qty']*$ord['unit_price']); 

                        $tot_sub_total = isset($tot_sub_total)?num_format($tot_sub_total):0.00;
                        
                        $final_total += $product_final_total;

                $cnt++;
                        
                ?>


                <tr>
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{ $cnt }}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$ord['product_name'] or '-'}} (SKU: {{$ord['sku_no'] or ''}})</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$ord['item_qty'] or 0}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($ord['unit_price'])?num_format($ord['unit_price']):0}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($tot_sub_total)?num_format($tot_sub_total):0}}</td>

                    {{-- <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{$ord['item_qty']*$ord['unit_price']}}</td> --}}

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($ord['shipping_charges']>0)+@endif${{isset($ord['shipping_charges'])?num_format($ord['shipping_charges']):'0.00'}}  </td>
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($shipping_discount_amt>0)-@endif${{$shipping_discount_amt or '$0.00'}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($product_discount_amt>0)-@endif ${{isset($product_discount_amt)?$product_discount_amt : '$0.00'}}</td>
                    
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($product_final_total)?num_format($product_final_total):0}}</td>
                     
                </tr>
                 <?php 

                        $tot_qty+= (float)$ord['item_qty'];
                        $tot_unit_price+=(float)$ord['unit_price'];
                        $tot_shipping_charges+=(float)$ord['shipping_charges'];
                        $tot_pro_dis+=(float)$product_discount_amt;
                        $tot_shipping_dis_amt+=(float)$shipping_discount_amt;
                        $tot_pro_final_tot+=(float)$product_final_total;  
                        $final_sub_total  += (float)$tot_sub_total;  
                    ?>

                @endforeach 
                <tr>
                    <!-- <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #f68525; padding:12px 12px 12px 30px;"></td> -->
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b></td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b>Total</td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">{{$tot_qty}}</td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_unit_price)}}</td>

                     <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($final_sub_total)}}</td>
                        
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_shipping_charges)}}
                    </td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_pro_dis)}}
                    </td>    

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_shipping_dis_amt)}}</td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_pro_final_tot)}}</td>

                    
                </tr>    
                
                @endif
            </table>
        </td>
    </tr>
     @php
      $promo_discount = 0;
      $sub_total               = array_sum(array_column($order,'total_retail_price'));
      $total_shipping_charges  = array_sum(array_column($order,'shipping_charges'));
      $total_shipping_discount = array_sum(array_column($order,'shipping_discount'));

      $total_product_discount  = array_sum(array_column($order,'product_discount_amount'));

      if (isset($customer_data['promo_discount'])) {
        $promo_discount = $customer_data['promo_discount'];
      }

      $final_total = $sub_total+$total_shipping_charges-$total_shipping_discount-$total_product_discount-$promo_discount;
      
     @endphp


      @if((isset($order_details['discount_amt']) && $order_details['discount_amt'] != 0) || (isset($order_details['promotion_shipping_charges']))) 

       <tr>
            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
                Total Amount
            </td>

            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;padding:12px;">
              ${{ isset($order_details['sub_grand_total'])?num_format($order_details['sub_grand_total']) : 0.00 }}
               
            </td>
            
        </tr>

        @if(isset($order_details['discount_amt']) && $order_details['discount_amt'] != 0)

        <tr>
            <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                Promotion Discount 
            </td>
            <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
              ${{ isset($order_details['discount_amt'])?num_format($order_details['discount_amt']) : 0.00 }}
            </td>
        </tr>

        @endif

        @if(isset($order_details['promotion_shipping_charges']) && $order_details['promotion_shipping_charges'] != 0)
            <tr>
                <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   Promotion(Free shipping)
                </td>
                <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
                   ${{ isset($order_details['promotion_shipping_charges'])?num_format($order_details['promotion_shipping_charges']) : 0.00 }}
                </td>
            </tr>
        @endif

    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            Total Amount (Wholesale)
        </td>

        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: center; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
          ${{ isset($order_details['grand_total'])?num_format($order_details['grand_total']) : 0.00 }}

        </td>
    </tr>
 
    @else
    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            Total Amount (Wholesale)
        </td>
        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: center; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            ${{ isset($order_details['sub_grand_total'])?num_format($order_details['sub_grand_total']) : 0.00 }}
        </td>
    </tr> 

    @endif

  <!--   <tr>
        <td style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
            Sub Total
        </td>
        <td style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: center;padding:12px;">
          ${{isset($sub_total)?num_format($sub_total):0}}
           
        </td>
            
    </tr>

     

    <tr>
        <td width="80%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #fff;padding:12px;">
             Total Shipping Charges
        </td>
        <td width="20%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #fff;padding:12px;">
            @if($total_shipping_charges > 0)+@endif${{isset($total_shipping_charges)?num_format($total_shipping_charges):0}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #fff;padding:12px;">
            Total Shipping Discount
        </td>
        <td width="20%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #fff;padding:12px;">
            @if($total_shipping_discount > 0)-@endif${{isset($total_shipping_discount)?num_format($total_shipping_discount):0}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #fff;padding:12px;">
            Total Product Discount
        </td>

        <td width="20%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #fff;padding:12px;">
            @if($total_product_discount>0)-@endif${{isset($total_product_discount)?num_format($total_product_discount):0}}
        </td>
    </tr>
    @if(isset($customer_data['promo_discount']))

        <tr>
            <td width="80%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #fff;padding:12px;">
                Total Promotion Discount
            </td>

            <td width="20%" style="background-color: #d3d7de;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #fff;padding:12px;">
                @if($customer_data['promo_discount']>0)-@endif${{isset($customer_data['promo_discount'])?num_format($customer_data['promo_discount']):0}}
            </td>
        </tr>

    @endif

    <tr>
        <td width="80%" style="text-align: right; background-color: #ff9a45;font-size:13px; font-weight: bold; border-right:1px solid #ffa457;border-bottom:1px solid #ffa457; padding:12px; text-transform: uppercase; color: #fff;">
            Total
        </td>
        <td width="20%" style="text-align: center; background-color: #ff9a45;font-size:13px; font-weight: bold; border-right:1px solid #ffa457;border-bottom:1px solid #ffa457; padding:12px; text-transform: uppercase; color: #fff;">
            ${{isset($final_total)?num_format($final_total):''}}
        </td>
    </tr> -->
 
  
    <tr>
        <td colspan="2" style="padding: 20px 20px 5px; color: #fff; background-color: #404040; text-align: center;"> If you have any questions about this invoice, please contact
        </td>
    </tr>
    <tr>
        <td colspan="2"style="text-align: center; color: #fff; font-size: 13px; background-color: #404040;">

            <b>Email:</b> {{isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:""}},&nbsp;&nbsp;&nbsp; <b>Website:</b> {{$site_setting_arr['website_url']}}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 5px 10px 10px; color: #fff; text-align: center; font-size: 20px; background-color: #404040;">
           <b>Thank you for your business!</b>
        </td>
    </tr>  
</table>
</div>
</body>

