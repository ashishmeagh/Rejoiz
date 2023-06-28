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
                <td width="30%" style="font-size:12px; text-align: left; word-break: break-all;"> <h3 style="margin-bottom: 5px;">Order From ({{$role or ''}}) :</h3>


                @if(isset($role) && $role == 'Representative')

                   @php

                    $first_name = $last_name = $email = $country = $post_code = $user_name = '';

                    $first_name = isset($order_arr['representative_user_details']['first_name'])&&$order_arr['representative_user_details']['first_name']!=''?$order_arr['representative_user_details']['first_name']:'';

                    $last_name = isset($order_arr['representative_user_details']['last_name'])&&$order_arr['representative_user_details']['last_name']!=''?$order_arr['representative_user_details']['last_name']:'';

                    $user_name = $first_name.' '.$last_name;

                    $email = isset($order_arr['representative_user_details']['email'])&&$order_arr['representative_user_details']['email']!=''?$order_arr['representative_user_details']['email'].', ':'';

                    $country = isset($order_arr['representative_user_details']['country_id'])&&$order_arr['representative_user_details']['country_id']!=''?get_country($order_arr['representative_user_details']['country_id']).', ':'';

                    $post_code = isset($order_arr['representative_user_details']['post_code'])&&$order_arr['representative_user_details']['post_code']!=''?$order_arr['representative_user_details']['post_code']:'';

                   @endphp


                    <div class = "data_container">

                    {{$user_name or ''}}

                    @if(isset($email) && $email!='')
                      <br>
                    @endif

                    {{$email or ''}}

                    @if(isset($country) && $country!='' || isset($post_code) && $post_code!='')
                    <br>
                    @endif

                    {{$country or ''}} {{$post_code or ''}}

                    </div>



                @elseif(isset($role) && $role == 'Sales Manager')

                    @php
                        
                    $first_name = $last_name = $email = $country = $post_code = $user_name = '';

                    $first_name = isset($order_arr['sales_manager_details']['first_name'])&&$order_arr['sales_manager_details']['first_name']!=''?$order_arr['sales_manager_details']['first_name']:'';

                    $last_name = isset($order_arr['sales_manager_details']['last_name'])&&$order_arr['sales_manager_details']['last_name']!=''?$order_arr['sales_manager_details']['last_name']:'';

                    $user_name = $first_name.' '.$last_name;

                    $email = isset($order_arr['sales_manager_details']['email'])&&$order_arr['sales_manager_details']['email']!=''?$order_arr['sales_manager_details']['email'].', ':'';

                    $country = isset($order_arr['sales_manager_details']['country_id'])&&$order_arr['sales_manager_details']['country_id']!=''?get_country($order_arr['sales_manager_details']['country_id']).', ':'';

                    $post_code = isset($order_arr['sales_manager_details']['post_code'])&&$order_arr['sales_manager_details']['post_code']!=''?$order_arr['sales_manager_details']['post_code']:'';
                    @endphp


                    <div class = "data_container">

                        {{$user_name or ''}}

                        @if(isset($email) && $email!='')
                          <br>
                        @endif

                        {{$email or ''}}

                        @if(isset($country) && $country!='' || isset($post_code) && $post_code!='')
                        <br>
                        @endif

                        {{$country or ''}} {{$post_code or ''}} 

                    </div> 

                @else
                   <div class = "data_container">
                   </div>
                   
                @endif

                </td>

                <td  width="40%">&nbsp; </td>
                <td width="30%" style="font-size:12px; text-align: right;  word-break: break-all;"> <h3 style="margin-bottom: 5px;">Order To (Vendor ) :</h3>
                    <div class= "data_container2">
                        <b>{{$order_arr['maker_details']['company_name'] or 'N/A'}}</b><br>

                        {{isset($order_arr['maker_data']['address'])?$order_arr['maker_data']['address']:'N/A'}}
                        <br>
                        {{isset($order_arr['maker_data']['post_code'])?$order_arr['maker_data']['post_code']:''}}
                    </div>
                 </td>
                </tr>
                <tr>
                    <td style=" border-bottom: 2px solid #d5d8dc;" colspan="3" height="10px">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <table width="100%">
                             <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Shipping Address</h3>
                            <div class="data_container">
                                 {{isset($order_arr['address_details']['ship_street_address'])?$order_arr['address_details']['ship_street_address'].',':''}} 

                                {{isset($order_arr['address_details']['ship_suit_apt'])?$order_arr['address_details']['ship_suit_apt'].',':''}} 

                                {{isset($order_arr['address_details']['ship_city'])?$order_arr['address_details']['ship_city'].',':''}} 

                                {{isset($order_arr['address_details']['ship_state'])?$order_arr['address_details']['ship_state'].',':''}}

                                {{isset($order_arr['address_details']['ship_country'])?get_country($order_arr['address_details']['ship_country']).',':''}} 
                                
                                {{isset($order_arr['address_details']['ship_zip_code'])?$order_arr['address_details']['ship_zip_code'].',':''}}
                                Mobile.No: {{isset($order_arr['address_details']['ship_mobile_no'])?$order_arr['address_details']['ship_mobile_no']:''}}

                                
                            </div>
                           </td>

                           <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Billing Address</h3>
                            <div class="data_container">
                               {{isset($order_arr['address_details']['bill_street_address'])?$order_arr['address_details']['bill_street_address'].',':''}} 

                               {{isset($order_arr['address_details']['bill_suit_apt'])?$order_arr['address_details']['bill_suit_apt'].',':''}} 

                               {{isset($order_arr['address_details']['bill_city'])?$order_arr['address_details']['bill_city'].',':''}} 

                               {{isset($order_arr['address_details']['bill_state'])?$order_arr['address_details']['bill_state'].',':''}}

                               {{isset($order_arr['address_details']['bill_country'])?get_country($order_arr['address_details']['bill_country']).',':''}}

                               {{isset($order_arr['address_details']['bill_zip_code'])?$order_arr['address_details']['bill_zip_code'].',':''}}
                               Mobile.No: {{isset($order_arr['address_details']['bill_mobile_no'])?$order_arr['address_details']['bill_mobile_no']:''}}
                                
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
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">S.No.</th>
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

            @if(isset($order_arr['leads_details']) && count($order_arr['leads_details'])>0)
                 
                @php 
                 $sub_total = $product_sub_total = $total_sub_total = 0.00;

                @endphp


                @foreach($order_arr['leads_details'] as $key=>$quote)

                    @php
 
                    $sku_no = isset($quote['sku'])?$quote['sku']:"-";
                    @endphp
 
                    <tr>
                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{++$key}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{isset($quote['product_details']['product_name'])?$quote['product_details']['product_name'].' (SKU: '.$sku_no.')':'-'}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$quote['qty'] or 0}}</td>

                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($quote['unit_wholsale_price'])?num_format($quote['unit_wholsale_price']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['product_shipping_charge']>0)+@endif${{isset($quote['product_shipping_charge'])?num_format($quote['product_shipping_charge']):0.00}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['product_discount']>0)-@endif${{isset($quote['product_discount'])?num_format($quote['product_discount']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($quote['shipping_charges_discount']>0)-@endif${{isset($quote['shipping_charges_discount'])?num_format($quote['shipping_charges_discount']):0.00}}</td>

                        
                        @php
                        
                        $sub_total = $quote['qty']*$quote['unit_wholsale_price'];

                        //$total_sub_total+= $sub_total;

                        //$product_sub_total = $sub_total +$quote['shipping_charge']-$quote['product_discount']-$quote['shipping_discount'];

                        $subtotal = $sub_total+$quote['product_shipping_charge']-$quote['shipping_charges_discount']-$quote['product_discount'];

                        @endphp

                       <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($subtotal)?num_format($subtotal):0.00}}</td>
                    <?php 

                        // $tot_qty+= (float)$ord['item_qty'];
                        // $tot_unit_price+=(float)$ord['unit_price'];
                        // $tot_shipping_charges+=(float)$ord['shipping_charges'];
                        // $tot_pro_dis+=(float)$product_discount_amt;
                        // $tot_shipping_dis_amt+=(float)$shipping_discount_amt;
                        // $tot_pro_final_tot+=(float)$product_final_total; 


                        $tot_qty+= (float)$quote['qty'];
                        $tot_unit_price+=(float)$quote['unit_wholsale_price'];
                        $tot_shipping_charges+=(float)$quote['product_shipping_charge'];
                        $tot_pro_dis+=(float)$quote['product_discount'];
                        $tot_shipping_dis_amt+=(float)$quote['shipping_charges_discount'];
                        $tot_pro_final_tot+=(float)$subtotal;  
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





    @if((isset($main_orderCalculationData['discount_amt']) && $main_orderCalculationData['discount_amt'] != 0) || (isset($main_orderCalculationData['promotion_shipping_charges']))) 

        <tr>
            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
                Total Amount
            </td>

            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
              ${{ isset($main_orderCalculationData['sub_grand_total'])?num_format($main_orderCalculationData['sub_grand_total']) : 0.00 }}
               
            </td>
            
        </tr>

        @if(isset($main_orderCalculationData['discount_amt']) && $main_orderCalculationData['discount_amt'] != 0)

        <tr>
            <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                Promotion Discount ({{ isset($main_orderCalculationData['discount_per'])?$main_orderCalculationData['discount_per'] : 0 }}%)
            </td>
            <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
              -${{ isset($main_orderCalculationData['discount_amt'])?num_format($main_orderCalculationData['discount_amt']) : 0.00 }}
            </td>
        </tr>

        @endif

        @if(isset($main_orderCalculationData['promotion_shipping_charges']) && $main_orderCalculationData['promotion_shipping_charges'] != 0)
            <tr>
                <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   Promotion(Free shipping)
                </td>
                <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   -${{ isset($main_orderCalculationData['promotion_shipping_charges'])?num_format($main_orderCalculationData['promotion_shipping_charges']) : 0.00 }}
                </td>
            </tr>
        @endif

    <tr>
        <td width="80%" style="text-align: right; background-color:#d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>

        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
          ${{ isset($main_orderCalculationData['grand_total'])?num_format($main_orderCalculationData['grand_total']) : 0.00 }}

        </td>
    </tr>
 
    @else
    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>
        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            ${{ isset($main_orderCalculationData['sub_grand_total'])?num_format($main_orderCalculationData['sub_grand_total']) : 0.00 }}
        </td>
    </tr> 

    @endif


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


                 @php
                  $shipping_charges = $fulfill_order['shipping_charges'] + $fulfill_order['shipping_discount'];
                 @endphp


                    <tr>
                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{++$key}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$fulfill_order['product_name']}}</td>

                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$fulfill_order['qty']}}</td>

                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{isset($fulfill_order['unit_price'])?num_format($fulfill_order['unit_price']):0.00}}</td>


                        <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($fulfill_order['shipping_charges'] >0)+@endif${{isset($fulfill_order['shipping_charges'])?num_format($fulfill_order['shipping_charges']):0.00}}</td>

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


   


    @if((isset($confirmed_orderCalculationData['discount_amt']) && $confirmed_orderCalculationData['discount_amt'] != 0) || (isset($confirmed_orderCalculationData['promotion_shipping_charges']))) 

        <tr>
            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
                Total Amount
            </td>

            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
              ${{ isset($confirmed_orderCalculationData['sub_grand_total'])?num_format($confirmed_orderCalculationData['sub_grand_total']) : 0.00 }}
               
            </td>
            
        </tr>

        @if(isset($confirmed_orderCalculationData['discount_amt']) && $confirmed_orderCalculationData['discount_amt'] != 0)

        <tr>
            <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                Promotion Discount ({{ isset($confirmed_orderCalculationData['discount_per'])?$confirmed_orderCalculationData['discount_per'] : 0 }}%)
            </td>
            <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
              -${{ isset($confirmed_orderCalculationData['discount_amt'])?num_format($confirmed_orderCalculationData['discount_amt']) : 0.00 }}
            </td>
        </tr>

        @endif

        @if(isset($confirmed_orderCalculationData['promotion_shipping_charges']) && $confirmed_orderCalculationData['promotion_shipping_charges'] != 0)
            <tr>
                <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   Promotion(Free shipping)
                </td>
                <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   -${{ isset($confirmed_orderCalculationData['promotion_shipping_charges'])?num_format($confirmed_orderCalculationData['promotion_shipping_charges']) : 0.00 }}
                </td>
            </tr>
        @endif

    <tr>
        <td width="80%" style="text-align: right; background-color:#d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>

        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
          ${{ isset($confirmed_orderCalculationData['grand_total'])?num_format($confirmed_orderCalculationData['grand_total']) : 0.00 }}

        </td>
    </tr>
 
    @else
    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>
        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            ${{ isset($confirmed_orderCalculationData['sub_grand_total'])?num_format($confirmed_orderCalculationData['sub_grand_total']) : 0.00 }}
        </td>
    </tr> 

    @endif

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
              

                    @php
                      $shipping_charges = $partial_order['shipping_charges'] +$partial_order['shipping_discount'];
                    @endphp

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
   
   

   @if((isset($pending_orderCalculationData['discount_amt']) && $pending_orderCalculationData['discount_amt'] != 0) || (isset($pending_orderCalculationData['promotion_shipping_charges']))) 

        <tr>
            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
                Total Amount
            </td>

            <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
              ${{ isset($pending_orderCalculationData['sub_grand_total'])?num_format($pending_orderCalculationData['sub_grand_total']) : 0.00 }}
               
            </td>
            
        </tr>

        @if(isset($pending_orderCalculationData['discount_amt']) && $pending_orderCalculationData['discount_amt'] != 0)

        <tr>
            <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                Promotion Discount ({{ isset($pending_orderCalculationData['discount_per'])?$pending_orderCalculationData['discount_per'] : 0 }}%)
            </td>
            <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
              -${{ isset($pending_orderCalculationData['discount_amt'])?num_format($pending_orderCalculationData['discount_amt']) : 0.00 }}
            </td>
        </tr>

        @endif

        @if(isset($pending_orderCalculationData['promotion_shipping_charges']) && $pending_orderCalculationData['promotion_shipping_charges'] != 0)
            <tr>
                <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   Promotion(Free shipping)
                </td>
                <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                   -${{ isset($pending_orderCalculationData['promotion_shipping_charges'])?num_format($pending_orderCalculationData['promotion_shipping_charges']) : 0.00 }}
                </td>
            </tr>
        @endif

    <tr>
        <td width="80%" style="text-align: right; background-color:#d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>

        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #fff;">
          ${{ isset($pending_orderCalculationData['grand_total'])?num_format($pending_orderCalculationData['grand_total']) : 0.00 }}

        </td>
    </tr>
 
    @else
    <tr>
        <td width="80%" style="text-align: right; background-color: #d5d8dc;font-size:13px; font-weight: bold; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            Total Amount (Wholesale)
        </td>
        <td width="20%" style="background-color: #d5d8dc; font-size:13px; font-weight: bold; text-align: right; border-right:1px solid #ccc;border-bottom:1px solid #d5d8dc; padding:12px; text-transform: uppercase; color: #505050;">
            ${{ isset($pending_orderCalculationData['sub_grand_total'])?num_format($pending_orderCalculationData['sub_grand_total']) : 0.00 }}
        </td>
    </tr> 

    @endif
    
    <!-- ------------------------------------------------------------------------------------ -->
 
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
