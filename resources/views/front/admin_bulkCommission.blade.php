@php
$site_setting_arr = get_site_settings();
$site_base_img    = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
$site_name        = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
$site_address     = isset($site_setting_arr['site_address']) ? $site_setting_arr['site_address'] : false;
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
                    <td width="40%" style="font-size:40px; color: #000;"> <img src="{{ $site_logo }}" alt="{{ $site_name }}" /></td>
                    <td width="60%" style="text-align:right; color: #000;">
                        <!-- <h3 style="line-height:25px;margin:0px;padding:0px;">{{$site_name or ''}}</h3> -->
                        <h1 style="line-height:25px;margin:0px;padding:0px;">INVOICE</h1>
                        {{-- <p style="font-size:12px;padding:0px;margin:0px;">**PLEASE DO NOT SEND CHECK OR CASH TO THIS ADDRESS**</p> --}}
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
                       Order From ({{isset($user_type_show)?$user_type_show:'Vendor'}})
                   </td>
               </tr>
               <tr>
                   <td style="font-size:12px;padding:0px;margin:0px;vertical-align: top;">

                        Store Name : {{isset($vendor_data['store_name'])?$vendor_data['store_name'].'.':''}} <br>
                        <br>
                        Vendor Name  : {{isset($company_name)?$company_name:'Vendor'}}<br>
                        Address:
                        <div class="data_container">
                          
                             {{isset($vendor_data['address'])?$vendor_data['address']:'N/A'}}
                             <br>
                            
                        </div>
                   </td>
               </tr>
           </table>
        </td>
        <td style="text-align: right;">
           <table style="font-size:12px;" align="right">
               <tr>
                   <td>
                       <!-- <b>Invoice #:</b> 0000001 <br> -->
                       <b>Invoice No. :</b> {{$vendor_data['invoice_id']}}  <br>
                   </td>
               </tr>
               <tr>
                   <td>
                      <!--  <b>Invoice Date:</b> 10/15/2020 <br> -->
                                             
                             <b>Invoice Date:</b> {{date("m-d-Y")}} <br>
                       
                   </td>
               </tr>
             <!--   <tr>
                   <td>
                       <b>Due Date:</b> 10/15/2020<br>
                   </td>
               </tr> -->
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
            
            <table width="100%" border="1px" cellspacing="0" cellpadding="10" style="font-size: 13px;">
                <tr>
                    <th align="left" width="5%">Sr. No.</th>
                    <th align="left" width="10%">Order No</th>
                    <th align="left" width="10%">Order Date</th>
                    <th align="right" width="20%"> Order Amount(Excluded shipping costs)</th>

                    <!-- <th align="left">Vendor Payment</th> -->

                    <th align="left" width="10%"> Commission(%)</th>

                    <th align="right" width="10%"> Commission($)</th>

                     <th align="left" width="10%">Payment status</th>
                    
                    
                </tr>
               @php  $cnt=1; @endphp
        
                <?php 
                    $total_order_amount = $total_commission_amount = $total_admin_commission_amount = 0;
                    foreach ($pdf_dataArr as $key => $row) {

                        $total_order_amount += ($row['order_amount']) ? $row['order_amount'] : 0;
                        $total_commission_amount += ($row['commission_amount']) ? $row['commission_amount'] : 0;
                        $total_admin_commission_amount += ($row['admin_commission_amount']) ? $row['admin_commission_amount'] : 0;
                ?>

                <tr>
                    <td >{{ $cnt++ }}</td>

                    <td ><b>{{$row['order_no'] or '-'}}</td>


                    <td >{{$row['order_date']}}</td>

                    <td align="right">$
                      {{isset($row['order_amount'])?num_format($row['order_amount']):0}}
                    </td>
                    
                    <!-- <td >${{isset($row['commission_amount'])?num_format($row['commission_amount']):0}}</td> -->

                    <td >{{$row['admin_commission_percent'] or 0}}%</td>

                    <td align="right">${{isset($row['admin_commission_amount'])?num_format($row['admin_commission_amount']):0}}</td>

                    <td ><b>Pending</b></td>

                </tr>
                <?php 
                    }
                ?>

                <tr >
                    <td  colspan="3"><b> Total &nbsp; </b></td>
                    <td align="right"><b> ${{ num_format($total_order_amount) }} </b></td>
                   <!--  <td > ${{ num_format($total_commission_amount) }} </td> -->
                    <td > -- </td>
                    <td align="right"><b> ${{ num_format($total_admin_commission_amount) }} </b></td>
                    <td > -- </td>
                </tr>
                 
            </table>
        </td>
    </tr>

   


    <tr>
        <td width="50%" >
            <!-- <table  cellspacing="0" cellpadding="0" border="0" align="left">
                <tr>
                    <td style="color:#404041;font-size:14px;">
                        <b>Notes</b>
                    </td>
                </tr>
                <tr>
                    <td style="color:#404041;font-size:12px;">
            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.
                    </td>
                </tr>
            </table> -->
        </td>
        <td width="50%" >
           <table cellspacing="0" cellpadding="0" border="0"  align="right">
                <tbody>
                    <tr>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 0px 3px 0px" width="0" valign="top" align="left">
                            <strong>Subtotal</strong> 
                        </td>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 5px 3px 5px" width="0" valign="top" align="right">
                            ${{ num_format($total_admin_commission_amount) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 0px 3px 0px" width="0" valign="top" align="left">
                            <strong>Total:</strong> 
                        </td>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 5px 3px 5px" width="0" valign="top" align="right">
                           ${{ num_format($total_admin_commission_amount) }}
                        </td>
                    </tr>
                   <!--  <tr>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:5px 0px 3px 0px" valign="top" align="left">
                            <strong>Amount Paid:</strong>
                        </td>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:5px 5px 3px 5px" width="62" valign="top" align="right">                                                                        
                           ${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}
                        </td>
                    </tr> -->
                    
                    <tr>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:5px 0px 3px 0px;border-top:solid 1px #999999" valign="top" align="left">
                            <strong>Balance Due:</strong>
                        </td>
                        <td style="color:#404041;font-size:12px;line-height:16px;padding:5px 5px 3px 5px;border-top:solid 1px #999999" width="62" valign="top" align="right">
                           ${{ num_format($total_admin_commission_amount) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
    </td>
     <tr>


                     
                

                <tr>
                    <td colspan="9" style="padding: 20px 20px 5px; color: #000; background-color: #fff; text-align: center;"> If you have any questions about this invoice, please contact
                    </td>
                </tr>
               
                <tr>
                    <td colspan="9"style="text-align: center; color: #000; font-size: 13px; background-color: #fff;">
                        <b>Email:</b> {{isset($site_setting_arr['site_email_address'])?$site_setting_arr['site_email_address']:""}},&nbsp;&nbsp;&nbsp; <b>Website:</b> {{$site_setting_arr['website_url']}}
                    </td>
                </tr>
               
                <tr>
                    <td colspan="9" style="padding: 5px 10px 10px; color: #000; text-align: center; font-size: 20px; background-color: #fff;">
                       <b>Thank you for your business!</b>
                    </td>
                </tr>  
               
            </table>
    
    
   
     
</div>
</body>

