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

                
                @php
                 
                $first_name = $last_name = $address2 = $address = $city = $state = $country = $post_code = $user_name = '';

                $first_name = isset($customer_data['user_details']['first_name']) && $customer_data['user_details']['first_name']!='' ?$customer_data['user_details']['first_name']:'';

                $last_name = isset($customer_data['user_details']['last_name']) && $customer_data['user_details']['last_name']!='' ?$customer_data['user_details']['last_name']:'';

                $user_name = $first_name.' '.$last_name;

                $address2 = isset($customer_data['user_details']['customer_details']['address2']) && $customer_data['user_details']['customer_details']['address2']!='' ?$customer_data['user_details']['customer_details']['address2'].',':'';

                $address = isset($customer_data['user_details']['address']) && $customer_data['user_details']['address']!='' ?$customer_data['user_details']['address'].',':'';

                $city = isset($customer_data['user_details']['customer_details']['city']) && $customer_data['user_details']['customer_details']['city']!='' ?$customer_data['user_details']['customer_details']['city'].', ':'';

                $state = isset($customer_data['user_details']['customer_details']['state']) && $customer_data['user_details']['customer_details']['state']!='' ?$customer_data['user_details']['customer_details']['state'].',':'';

                $country = isset($customer_data['user_details']['country_id']) && $customer_data['user_details']['country_id']!='' ?get_country($customer_data['user_details']['country_id']).', ':'';

                $post_code = isset($customer_data['user_details']['post_code']) && $customer_data['user_details']['post_code']!='' ?$customer_data['user_details']['post_code'].',':'';



                @endphp



                <div class = "data_container">

                    <b>{{$user_name or ''}}</b><br>

                    {{$address2}}{{$address}}

                    @if(isset($city) && $city!='' || isset($state) && $state!='')
                    <br>
                    @endif

                    {{$city}} {{$state}}
  
                    @if(isset($country) && $country!='' || isset($post_code) && $post_code!='')
                    <br>
                    @endif
                    
                    {{$country}}{{$post_code}}

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
                                    {{isset($customer_data['shipping_addr_zip_code'])?$customer_data['shipping_addr_zip_code'].',':''}}
                                @endif
                                
                              <!--  Mobile.No: {{isset($customer_data['user_details']['customer_details']['ship_contact_no'])?$customer_data['user_details']['customer_details']['ship_contact_no']:''}} -->



                            </div>
                           </td>

                           <td width="50%" style="font-size:12px;"> <h3 style="margin-bottom: 5px;">Billing Address:</h3>
                            <div class="data_container">
                                @if(isset($customer_data['address_details']))
                                
                                    {{isset($customer_data['address_details']['bill_complete_address'])?$customer_data['address_details']['bill_complete_address']:'N/A'}}
                                @else
                                    {{isset($customer_data['billing_addr'])?$customer_data['billing_addr']:'N/A'}}
                                    <br>
                                    {{isset($customer_data['billing_addr_zip_code'])?$customer_data['billing_addr_zip_code'].',':''}}


                                    <!-- Mobile.No: {{isset($customer_data['user_details']['customer_details']['bill_contact_no'])?$customer_data['user_details']['customer_details']['bill_contact_no']:''}} -->
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
                            <tr>
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

                                @if(isset($customer_data['charge_status']))               
                                    
                                    <td width="50%" style="font-size:12px;"> 
                                        <h3 style="margin-bottom: 5px;">Payment Status: </h3>
                                        <div class="data_container">
                                            {{isset($customer_data['charge_status'])?$customer_data['charge_status']:'-'}}
                                        </div>
                               
                                    </td>   
                                @endif  

                                @if(isset($customer_data['cancel_status']))               
                                    
                                   <td width="50%" style="font-size:12px;"> 
                                        <h3 style="margin-bottom: 5px;">Order Status: </h3>
                                        <div class="data_container">
                                            {{isset($customer_data['cancel_status'])?$customer_data['cancel_status']:'-'}}
                                        </div>
                                   
                                    </td>   
                                @endif  
                            </tr>
                       </table>
                    </td>
                   
                </tr>
                  

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
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Vendor Company Name</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Qty.</th>
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Unit Price</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Charges</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Shipping Discount</th>

                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Product Discount</th>
                    
                    <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Total</th>
                </tr>
                <?php 
                    $tot_qty = 0;
                    $tot_unit_price=0.00;
                    $tot_shipping_charges=0.00;
                    $tot_shipping_dis=0.00;
                    $tot_pro_dis=0.00;
                    $tot_shipping_dis_amt=0.00;
                    $tot_pro_final_tot=0.00;   
                ?>

                @php  $cnt= $sub_total = 0; @endphp
                @if(isset($order) && count($order)>0) 
                @foreach($order as $key => $ord)
                <?php
         
                        $product_final_total = $final_total = $product_discount_amt = $shipping_discount_amt = 0;

                        $product_discount_amount = isset($ord['product_discount_amount'])?$ord['product_discount_amount']:0.00;

                        $product_discount_amt = isset($ord['product_discount_amount'])?num_format($ord['product_discount_amount']):0.00;

                        if(isset($ord['product_discount_amount']) && $ord['product_discount_amount'] > 0)
                        {
                            $product_discount_amount = $ord['product_discount_amount'];
                            $product_discount_amt = num_format($ord['product_discount_amount']);
                        }
                        
                        
                        $shipping_discount = isset($ord['shipping_discount'])?$ord['shipping_discount']:0;

                        $shipping_discount_amt = isset($ord['shipping_discount'])?num_format($ord['shipping_discount']):0;

                        if(isset($ord['shipping_discount']) && $ord['shipping_discount'] > 0)
                        {
                            $shipping_discount = $ord['shipping_discount'];

                            $shipping_discount_amt =isset($ord['shipping_discount'])?num_format($ord['shipping_discount']):0.00;
                        }


                        $shipping_charges  = isset($ord['shipping_charges'])?$ord['shipping_charges']:0.00;
                        $shipping_discount = isset($ord['shipping_discount'])?$ord['shipping_discount']:0.00;
                        $product_discount =  isset($ord['product_discount_amount'])?$ord['product_discount_amount']:$ord['product_discount'];


                        $product_final_total = ($ord['item_qty']*$ord['unit_price']) + $shipping_charges - $shipping_discount - $product_discount;


                        $final_total += $product_final_total;
     

                $cnt++;
                        
                ?>


                <tr>
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{ $cnt }}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$ord['product_name'] or '-'}} (SKU: {{$ord['sku_no'] or ''}})</td>
                    
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$ord['maker_company_name'] or '-'}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$ord['item_qty'] or 0}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($ord['unit_price'])?num_format($ord['unit_price']):0}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($ord['shipping_charges']>0)+@endif${{isset($ord['shipping_charges'])?num_format($ord['shipping_charges']):'0.00'}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($ord['shipping_discount']>0)-@endif ${{isset($ord['shipping_discount'])?num_format($ord['shipping_discount']):'$0.00'}}</td>

                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">@if($ord['product_discount_amount']>0)-@endif ${{isset($ord['product_discount_amount'])?num_format($ord['product_discount_amount']): '$0.00'}}</td>
                    
                    <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($product_final_total)?num_format($product_final_total):0}} 
                    <?php $sub_total +=$product_final_total; ?> </td>
                     
                     <?php 
                        $tot_qty+= (float)$ord['item_qty'];
                        $tot_unit_price+=(float)$ord['unit_price'];
                        $tot_shipping_charges+=(float)$ord['shipping_charges'];
                        $tot_shipping_dis+=(float)$ord['shipping_discount'];
                        $tot_pro_dis+=(float)$ord['product_discount_amount'];
                        //$tot_shipping_dis_amt=(float)$shipping_discount_amt;
                        $tot_pro_final_tot=(float)$sub_total;   
                    ?>
                </tr>
            @endforeach 
            <tr>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"></td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b></td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;"><b>Total</td>
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">{{$tot_qty}}</td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_unit_price)}}</td>
                        
                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_shipping_charges)}}
                    </td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_shipping_dis)}}
                    </td>    

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_pro_dis)}}</td>

                    <td style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">${{num_format($tot_pro_final_tot)}}</td>

                    
                </tr>
            @endif   
            </table>
        </td>
    </tr>
     @php
      $promo_discount = 0;
      // $sub_total               = array_sum(array_column($order,'total_retail_price'));
      $total_shipping_charges  = array_sum(array_column($order,'shipping_charges'));
      $total_shipping_discount = array_sum(array_column($order,'shipping_discount'));

      $total_product_discount  = array_sum(array_column($order,'product_discount_amount'));

      if (isset($customer_data['promo_discount'])) {
        $promo_discount = $customer_data['promo_discount'];
        $final_total = $sub_total-$promo_discount;
      }
      else
      {
        $final_total = $sub_total;

      }

      
     @endphp

    <tr>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
            Sub Total
        </td>
        <td style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;padding:12px;">
          ${{isset($sub_total)?num_format($sub_total):0}}
           
        </td>
            
    </tr>

     

    {{-- <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
             Total Shipping Charges
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            @if($total_shipping_charges > 0)+@endif${{isset($total_shipping_charges)?num_format($total_shipping_charges):0}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Shipping Discount
        </td>
        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            @if($total_shipping_discount > 0)-@endif${{isset($total_shipping_discount)?num_format($total_shipping_discount):0}}
        </td>
    </tr>

    <tr>
        <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            Total Product Discount
        </td>

        <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
            @if($total_product_discount>0)-@endif${{isset($total_product_discount)?num_format($total_product_discount):0}}
        </td>
    </tr> --}}
    @if(isset($customer_data['promo_discount']))

        <tr>
            <td width="80%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                Promotion Discount (@if($discount_percent>0)@endif{{isset($discount_percent)?num_format($discount_percent):0}}%)
            </td>

            <td width="20%" style="background-color: #d5d8dc;font-size:13px;font-weight: bold;text-align: right;border-bottom:1px solid #d5d8dc;padding:12px;">
                @if($customer_data['promo_discount']>0)-@endif${{isset($customer_data['promo_discount'])?num_format($customer_data['promo_discount']):0}}
            </td>
        </tr>

    @endif

    <tr>
        <td width="80%" style="text-align: right; background-color: #9E9E9E;font-size:13px; font-weight: bold; border-right:1px solid #9E9E9E;border-bottom:1px solid #9E9E9E; padding:12px; text-transform: uppercase; color: #505050;">
            Total
        </td>
        <td width="20%" style="text-align: right; background-color: #9E9E9E;font-size:13px; font-weight: bold; border-right:1px solid #9E9E9E;border-bottom:1px solid #9E9E9E; padding:12px; text-transform: uppercase; color: #505050;">
            ${{isset($final_total)?num_format($final_total):''}}
        </td>
    </tr>
 
  
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


