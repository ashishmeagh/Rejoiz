@php
$site_setting_arr = get_site_settings();
$site_base_img    = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
$site_name        = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

$arr_site_data    = get_invoice_logo($site_base_img,$site_name);

$site_logo          = isset($arr_site_data['site_img'])?$arr_site_data['site_img']:'';
$site_default_image = isset($arr_site_data['site_default_image'])?$arr_site_data['site_default_image']:'';

$admin_email  = isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:'';
@endphp
<div style="margin:0 auto; width:100%;">
<table width="100%" bgcolor="#fff" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #ddd;font-family:Arial, Helvetica, sans-serif;">
    <tr>
        <td colspan="2">
            <table bgcolor="#fff" width="100%" border="0" cellspacing="10" cellpadding="0">
                <tr>
                    <td width="40%" style="font-size:40px; color: #fff;"> <img src="{{ $site_logo }}" alt="{{ $site_name }}" /></td>
                    <td width="60%" style="text-align:right; color: #fff;">
                        <h3 style="line-height:25px;margin:0px;padding:0px;">{{$site_name or ''}}</h3>
                     
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
                        {{--  <h3 style="font-size:18px;padding:0;margin:0px;">Order ID: TJ65456645 </h3> --}}
                    </td>
                    <td width="50%" style="background-color: #d3d7de;padding:10px;font-size:12px; text-align: right;">
                       <b>Order Date:</b> {{isset($order_arr['created_at'])?us_date_format($order_arr['created_at']) : '-'}}
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

        <td colspan="2" style="padding:10px 30px 10px 30px; font-size:12px; background-color: #f3f3f3;">
            <table width="100%">
             <tbody>
                <tr>
                <td width="30%" style="font-size:12px; text-align: left; word-break: break-all;"> <h3 style="margin-bottom: 5px;">Order From (Customer) :</h3>
                <div class = "data_container">
                    @php
                     
                     $customer_name = $order_arr['user_details']['first_name'].' '.$order_arr['user_details']['last_name'];

                    @endphp

                    <b>{{$customer_name or ''}}</b><br>

                     </div>
                </div>

                </td>
                <td  width="40%">&nbsp; </td>
                <td width="30%" style="font-size:12px; text-align: right;  word-break: break-all;"> <h3 style="margin-bottom: 5px;">Order To (Vendor) :</h3>
                    <div class= "data_container2">
                        <b>{{$order_arr['maker_data']['company_name'] or 'N/A'}}</b><br>

                        {{isset($order_arr['maker_data']['user_details']['address'])?$order_arr['maker_data']['user_details']['address']:'N/A'}}
                        <br>
                        {{isset($order_arr['maker_data']['user_details']['post_code'])?$order_arr['maker_data']['user_details']['post_code']:''}}
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
                                {{isset($order_arr['user_details']['shipping_addr'])?$order_arr['user_details']['shipping_addr']:'N/A'}}
                                <br>
                               {{isset($order_arr['user_details']['billing_addr_zip_code'])?$order_arr['user_details']['billing_addr_zip_code']:''}}
                            </div>
                           </td>

                           <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Billing Address:</h3>
                            <div class="data_container">
                               {{isset($order_arr['user_details']['billing_addr'])?$order_arr['user_details']['billing_addr']:'N/A'}}
                                <br>
                              {{isset($order_arr['user_details']['billing_addr_zip_code'])?$order_arr['user_details']['billing_addr_zip_code']:''}}
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
                           
                                {{isset($order_arr['payment_term'])?$order_arr['payment_term']:'-'}}
                            </div>
                       
                        </td>    

                        @if($order_arr['payment_due_date'] !='')

                            <td width="50%" style="font-size:12px;">
                                <h3 style="margin-bottom: 5px;">Payment Due Date: </h3>
                                <div class="data_container">
                                   {{isset($order_arr['payment_due_date'])?$order_arr['payment_due_date']:'N/A'}}
                                </div>
                            </td>
                        @endif
                            
                       </table>
                    </td>
                   
                </tr>
             </tbody>
            </table>
        </td>
    </tr>

   <tr>
        <td colspan="2" style="height:50px;">
            &nbsp;
        </td>
    </tr>

    <tr>
        <td colspan="2" style="padding: 0 0 20px; font-size: 20px; font-weight: 600;">
            Main Order {{'(Order ID:'.$order_arr['order_no'].')'}}
        </td>
    </tr>
     <tr>

        <td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Sr.No.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Name/Description</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Qty.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Unit Price</th>

                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Charges</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Discount</th>
                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Discount</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Total</th>
                </tr>
                <?php 
                    $tot_qty = 0;
                    $tot_unit_price=0.00;
                    $tot_shipping_charges=0.00;
                    $tot_pro_dis=0.00;
                    $tot_shipping_dis_amt=0.00;
                    $tot_pro_final_tot=0.00;   
                ?>

                @if(isset($order_arr['quotes_details']) && count($order_arr['quotes_details'])>0)
                 
                @php 
                 $sub_total = $product_sub_total = $total_sub_total = 0.00;
                 $cnt = 0;
                @endphp


                @foreach($order_arr['quotes_details'] as $key=>$quote)
                @php $cnt++; @endphp
                    <tr>
                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{++$cnt}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$quote['product_details']['product_name'] or '-'}} ({{++$key}})</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$quote['qty'] or 0}}</td>

                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($quote['unit_retail_price'])?num_format($quote['unit_retail_price']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['shipping_charge']>0)+@endif${{isset($quote['shipping_charge'])?num_format($quote['shipping_charge']):0.00}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['product_discount']>0)-@endif${{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['shipping_discount']>0)-@endif${{isset($quote['shipping_discount'])?num_format($quote['shipping_discount']):0.00}}</td>

                        
                        @php
                        
                        //$sub_total = $quote['qty']*$quote['unit_wholsale_price'];

                        //$total_sub_total+= $sub_total;

                        //$product_sub_total = $sub_total +$quote['shipping_charge']-$quote['product_discount']-$quote['shipping_discount'];

                        @endphp

                       <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($quote['retail_price'])?num_format($quote['retail_price']):0.00}}</td>
                        
                        <?php 
                            $tot_qty+= (float)$quote['qty'];
                            $tot_unit_price+=(float)$quote['unit_retail_price'];
                            $tot_shipping_charges+=(float)$quote['shipping_charge'];
                            $tot_pro_dis+=(float)$quote['product_discount'];
                            $tot_shipping_dis_amt+=(float)$quote['shipping_discount'];
                            $tot_pro_final_tot+=(float)$quote['retail_price'];   
                        ?>

                    </tr>

                @endforeach    
                <tr>
                    <!-- <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #f68525; padding:12px 12px 12px 30px;"></td> -->
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b></td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b>Total</td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">{{$tot_qty}}</td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_unit_price)}}</td>
                        
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


    <tr>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
            Sub Total
        </td>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;padding:12px;">
      
         ${{isset($calculation['mail_order']['subtotal'])?num_format($calculation['mail_order']['subtotal']):0.00}}

        </td>
    </tr>
    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
             Total Shipping Charges
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
         
           @if($calculation['mail_order']['total_shipping_charges'] > 0)+@endif${{isset($calculation['mail_order']['total_shipping_charges'])?num_format($calculation['mail_order']['total_shipping_charges']):0.00}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Shipping Discount
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
      
             @if($calculation['mail_order']['total_shipping_discount']> 0)-@endif${{isset($calculation['mail_order']['total_shipping_discount'])?num_format($calculation['mail_order']['total_shipping_discount']):0.00}}

        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Product Discount
        </td>

        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
         
             @if($calculation['mail_order']['total_product_discount'] >0)-@endif${{isset($calculation['mail_order']['total_product_discount'] )?num_format($calculation['mail_order']['total_product_discount'] ):0.00}}

        </td>
    </tr>


    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            Total
        </td>
        <td width="20%" style="text-align: center; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
       
           ${{isset($calculation['mail_order']['final_total'])?num_format($calculation['mail_order']['final_total']):0.00}}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="height:50px;">
            &nbsp;
        </td>
    </tr>

    <!-- -------------------------------------------------------------------------->
    <tr>
        <td colspan="2" style="padding: 0 0 20px; font-size: 20px; font-weight: 600;">
            Confirmed Order {{'(Order ID:'.$fulfill_order_no.')'}}
        </td>
    </tr>
     <tr>

        <td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">S.No.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Name/Description</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Qty.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Unit Price</th>

                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Charges</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Discount</th>
                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Discount</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Total</th>
                </tr>



             @if(isset($order_arr['fulfill']) && count($order_arr['fulfill'])>0)

               @php
                
                $sub_total = $product_sub_total = $total_sub_total = 0.00;

               @endphp


                @foreach($order_arr['fulfill'] as $key=>$fulfill_order)

                    <tr>
                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{++$key}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$fulfill_order['product_name']}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$fulfill_order['qty']}}</td>

                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{isset($fulfill_order['unit_price'])?num_format($fulfill_order['unit_price']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($fulfill_order['shipping_charges']>0)+@endif${{isset($fulfill_order['shipping_charges'])?num_format($fulfill_order['shipping_charges']):0.00}}</td>

                         <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($fulfill_order['product_discount']>0)-@endif${{isset($fulfill_order['product_discount'])?num_format($fulfill_order['product_discount']):0.00}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($fulfill_order['shipping_discount']>0)-@endif${{isset($fulfill_order['shipping_discount'])?num_format($fulfill_order['shipping_discount']):0.00}}</td>
       

                       @php

                         $sub_total = $fulfill_order['qty'] * $fulfill_order['unit_price'];


                         $product_sub_total = $sub_total+$fulfill_order['shipping_charges']-$fulfill_order['shipping_discount']-$fulfill_order['product_discount'];

                       @endphp

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($product_sub_total)?num_format($product_sub_total):0.00}}</td>
                         
                    </tr>

                @endforeach    

             @endif   

               
            </table>
        </td>
    </tr>  


    <tr>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
            Sub Total
        </td>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;padding:12px;">
        {{--   ${{isset($sub_total)?num_format($sub_total):0}} --}}

        ${{isset($calculation['fulfill']['sub_total'])?num_format($calculation['fulfill']['sub_total']):0.00}}
           
        </td>
            
    </tr>

     

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
             Total Shipping Charges
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
         
           @if($calculation['fulfill']['total_shipping_charges'] > 0)+@endif${{isset($calculation['fulfill']['total_shipping_charges'])?num_format($calculation['fulfill']['total_shipping_charges']):0.00}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Shipping Discount
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
            
               @if($calculation['fulfill']['total_shipping_discount'] > 0)-@endif${{isset($calculation['fulfill']['total_shipping_discount'])?num_format($calculation['fulfill']['total_shipping_discount']):0.00}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Product Discount
        </td>

        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
         
            @if($calculation['fulfill']['total_product_discount']>0)-@endif${{isset($calculation['fulfill']['total_product_discount'])?num_format($calculation['fulfill']['total_product_discount']):0.00}}
        </td>
    </tr>


    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            Total
        </td>
        <td width="20%" style="text-align: center; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">

          ${{isset($calculation['fulfill']['final_total'])?num_format($calculation['fulfill']['final_total']):0.00}}
        </td>
    </tr>

    <tr>
        <td colspan="2" style="height:50px;">
            &nbsp;
        </td>
    </tr>

     <!------------------------------------------------------------------------------------>
 
    <tr>
        <td colspan="2" style="padding: 0 0 20px; font-size: 20px; font-weight: 600;">
            Pending Order {{'(Order ID:'.$partial_order_no.')'}}
        </td>
    </tr>
      <tr>

        <td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">S.No.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Name/Description</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Qty.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Unit Price</th>

                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Charges</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Discount</th>
                     <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Discount</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Total</th>
                </tr>

            @if(isset($order_arr['partial']) && count($order_arr['partial'])>0)

                @foreach($order_arr['partial'] as $key=>$partial_order)
              
                    <tr>
                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{++$key}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$partial_order['product_name']}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$partial_order['qty']}}</td>

                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{isset($partial_order['unit_price'])?num_format($partial_order['unit_price']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($partial_order['shipping_charges']>0)+@endif${{isset($partial_order['shipping_charges'])?num_format($partial_order['shipping_charges']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($partial_order['product_discount']>0)-@endif${{isset($partial_order['product_discount'])?num_format($partial_order['product_discount']):0.00}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($partial_order['shipping_discount']>0)-@endif${{isset($partial_order['shipping_discount'])?num_format($partial_order['shipping_discount']):0.00}}</td>

                        
                        @php

                        $sub_total = $partial_order['qty'] * $partial_order['unit_price'];
                        $product_sub_total = $sub_total+$partial_order['shipping_charges']-$partial_order['shipping_discount']-$partial_order['product_discount'];

                        @endphp

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($product_sub_total)?num_format($product_sub_total):0.00}}</td>
                         
                    </tr>

               @endforeach    

             @endif   

            </table>
        </td>
    </tr>  
   
    <tr>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
            Sub Total
        </td>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;padding:12px;">
    
           ${{isset($calculation['partial']['sub_total'])?num_format($calculation['partial']['sub_total']):0.00}}
           
        </td>
            
    </tr>

   
    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
             Total Shipping Charges
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
       
       @if($calculation['partial']['total_shipping_charges'] > 0)+@endif${{isset($calculation['partial']['total_shipping_charges'])?num_format($calculation['partial']['total_shipping_charges']):0.00}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Shipping Discount
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
         
              @if( $calculation['partial']['total_shipping_discount'] > 0)-@endif${{isset( $calculation['partial']['total_shipping_discount'])?num_format( $calculation['partial']['total_shipping_discount']):0.00}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Product Discount
        </td>

        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: center;border-bottom:1px solid #d5d8dc;padding:12px;">
             
            @if($calculation['partial']['total_product_discount']>0)-@endif${{isset($calculation['partial']['total_product_discount'])?num_format($calculation['partial']['total_product_discount']):0.00}}
        </td>
    </tr>


    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
            Total
        </td>
        <td width="20%" style="text-align: center; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
           
             ${{isset($calculation['partial']['final_total'])?num_format($calculation['partial']['final_total']):0.00}}
        </td>
    </tr>
   <tr></tr>
    <!-- ------------------------------------------------------------------------------------------ -->
 
   <!--footer-->
  
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
