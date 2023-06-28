
<body style="background-color: #eaebec;">


<div style="margin:0 auto; width:650px;">
<table width="100%" bgcolor="#fff" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #ddd;font-family:Arial, Helvetica, sans-serif;">
    <tr>
        <td colspan="2">
            <table bgcolor="#498cff" width="100%" border="0" cellspacing="10" cellpadding="0">
                <tr>
                    <td width="40%" style="font-size:40px; color: #fff;"> <img src="images/just-got-to-have-it-logo.png" width="100px" alt=""/></td>
                    <td width="60%" style="text-align:right; color: #fff;">
                        <h3 style="line-height:25px;margin:0px;padding:0px;">{{$site_setting_arr['site_name'] or ''}}</h3>
                       
                    </td>
                </tr>
            </table>
        </td>
    </tr>
   
    <tr>
        <td colspan="2" style="background-color: #eaeef5;padding:10px 10px 10px 30px;font-size:12px;">
            <h3 style="font-size:18px;padding:0px 0 10px 0;margin:0px;">Order ID:{{$order_details['order_no']}}</h3>
            <b>Order Placed Date:</b>{{$order_details['created_at']}}<br>
            <b>Retailer Name:</b>{{$order_details['user_details']['first_name'].' '.$order_details['user_details']['last_name']}}<br>
           
        </td>
    </tr>
    <tr>
        <td colspan="2" style="height:10px;">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:10px 30px 10px 30px; font-size:12px;">
         
        </td>
    </tr>


<tr>
    <td colspan="2" style="color: #333333; font-size: 14px; padding: 0 30px;">
        Hello <span style="color: #ff9801;font-family:'arial', sans-serif;">{{$order_details['maker_details']['first_name'].' '.$order_details['maker_details']['last_name']}},</span>
    </td>
</tr>

<tr>
    <td colspan="2" style="color: #545454;font-size: 13px;padding: 12px 30px;">
       Retailer requesting you to cancel the order {{$order_details['order_no']}}, The items listed below were part of the canceled order.
    </td>
</tr>

{{-- <tr>
    <td colspan="2" style="font-size: 15px; color: #333;padding: 12px 30px;">
        <b>Cancelation Reason:</b>##REASON##
    </td>
</tr> --}}



    <tr>
        <td colspan="2" style="height:10px;">
            &nbsp;
        </td>
    </tr>
     <tr>
        <td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">

                @php $sr_no = 0; @endphp 
                <tr>
                    <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">Sr. No.</th>
                     <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">Sku No.</th>
                    <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">Product Name</th>
                    <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">QTY</th>
                    <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">Retail Price</th>
                    <th style=" background-color: #e5e9f1;font-size:11px;font-weight: bold;text-align: left;border-top:2px solid #498cfc; padding:12px; text-transform: uppercase; ">Wholesale Price</th>
                </tr>

                @if(isset($order_details['quotes_details']) && count($order_details['quotes_details'])>0)
                 @foreach($order_details['quotes_details'] as $key=>$product)
                  @php $sr_no++; @endphp
                <tr>
                    <td style="font-size:12px;padding:12px;">{{$sr_no or 'N/A'}}</td>
                     <td style="font-size:12px;padding:12px;">{{$product['sku_no'] or 'N/A'}}</td>
                     <td style="font-size:12px;padding:12px;">{{$product['product_details']['product_name'] or 'N/A'}}</td>
                     <td style="font-size:12px;padding:12px;">{{$product['qty'] or 'N/A'}}</td>
                     <td style="font-size:12px;padding:12px;">{{$product['retail_price'] or 'N/A'}}</td>
                     <td style="font-size:12px;padding:12px;">{{$product['wholesale_price'] or 'N/A'}}</td>
                </tr>

                @endforeach

             @endif
                
            </table>
        </td>
    </tr>
     <tr>
        <td height="30px" colspan="2"> &nbsp;</td>
    </tr>

    <tr>
        <td colspan="2" style="text-align: right;  font-weight: bold; padding:12px; text-transform: uppercase; color: #fff;">
            <table width="100%">
               <tbody>
                    <tr>
                    <td width="70%">&nbsp;</td>
                    <td width="30%" style="color: #fff;background-color: #f68525;padding:12px;font-size:13px;"><b>Total:$ {{$order_details['total_wholesale_price']}}</b></td>
                </tr>
               </tbody>
            </table>
        </td>
    </tr>
 
 
</table>
</div>

</body>