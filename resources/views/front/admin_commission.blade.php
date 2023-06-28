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
                     <td width="60%" style="text-align:right; color: #333;">
                        

                        {{-- 
                        <p style="font-size:12px;padding:0px;margin:0px;">**PLEASE DO NOT SEND CHECK OR CASH TO THIS ADDRESS**</p>
                        --}}
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <td>
            <table width="100%" style="padding:0 10px 0 10px" >
               <tr>
                  <td colspan="2" style="background-color: #d3d7de;padding:10px 10px 10px 30px;font-size:12px;">
                     <table width="100%">
                        <tr>
                           <td width="50%" style="background-color: #d3d7de;padding:10px;font-size:18px; text-align: left;"> <b>Invoice No :</b> {{isset($order_no)?$order_no:''}}</td>

                           <td width="50%" style="background-color: #d3d7de;padding:10px;font-size:18px; text-align: right;">
                               @if(isset($order_date))
                              <b>Invoice Date:</b> {{isset($order_date)?$order_date:''}} <br>
                              @else
                              <b>Invoice Date:</b> {{date("m/d/Y")}} <br>
                              @endif
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               
               <tr>
                  <td colspan="2" style="padding:10px 30px 10px 30px; font-size:12px;">
                    <table width="100%">
                      <tbody>
                        <tr>
                        <td width="30%" style="font-size:12px; text-align: left; word-break: break-all;">
                           <h3 style="margin-bottom: 5px;">From:</h3>
                           
                           <p style="font-size:12px;padding:0px;margin:0px;">
                              {{$site_name or ''}} <br>
                              {{$site_address or ''}} 
                           </p>
                        </td>
                         <td  width="40%">&nbsp; </td>
                        
                        <td width="30%" style="font-size:12px; text-align: left; word-break: break-all;">
                           <h3 style="margin-bottom: 5px;">To:</h3>
                           Store Name : {{isset($vendor_data['store_name'])?$vendor_data['store_name'].'.':''}} <br>
                          
                           Vendor Name  : {{isset($company_name)? $company_name:'Vendor'}}<br>
                           
                           <div class="data_container">
                              @php

                                 $country_name = $postal_code = $address_html = "";
                                 $country_name = isset($vendor_data['country_id']) && $vendor_data['country_id'] !=""  ?get_country($vendor_data['country_id']):'';
                                 $postal_code = isset($vendor_data['post_code']) ? $vendor_data['post_code']:'';
                              if(isset($vendor_data['address']) || isset($country_name) || isset($postal_code)){
                                 $address_html .= isset($vendor_data['address'])?'Address : '.$vendor_data['address'] :'';
                                 $address_html .= isset($country_name) ? ' '.($country_name) .',' :''; 
                                 $address_html .= isset($postal_code) ? ($postal_code) :'';

                                 $address_new_html = trim($address_html);

                              }
                              @endphp
                              {!!html_entity_decode($address_new_html)!!}

                             
                           </div>
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
               <hr>
               <tr>
                  <td colspan="2">
                     <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Sr. No.</th>
                          <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Order No</th>
                          <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">User Type</th>
                          <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Order Date</th>
                           <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Order Amount<br>(Excluded shipping costs)</th>
                           <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Commission(%)</th>
                           <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Commission($)</th>
                           <th style=" background-color: #eeeff1;font-size:14px;font-weight: bold;text-align: left;border-top:2px solid #d5d8dc; padding:12px 12px 12px 30px;">Payment status</th>
                        </tr>
                        @php  $cnt=1; @endphp
                        <tr>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{ $cnt }}</td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;"><b>{{$order_no or '-'}}</td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$user_type or '-'}}</td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$order_date}}</td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">$
                              {{isset($order_amount)?num_format($order_amount):0}}
                           </td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">{{$admin_commission_percent or 0}}%</td>
                           <td style="border-bottom: 1px solid #e5e9f1;font-size:12px;text-align: left;padding:12px 12px 12px 30px;">${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</td>
                           <td align="left">Pending</td>
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
                                 <b><strong>Subtotal</strong></b> 
                              </td>
                              <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 5px 3px 5px" width="0" valign="top" align="right">
                                <b> ${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</b>
                              </td>
                           </tr>
                           <tr>
                              <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 0px 3px 0px" width="0" valign="top" align="left">
                                 <b><strong>Total:</strong></b>
                              </td>
                              <td style="color:#404041;font-size:12px;line-height:16px;padding:15px 5px 3px 5px" width="0" valign="top" align="right">
                                 <b>${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</b>
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
                                 <b><strong>Balance Due:</strong></b>
                              </td>
                              <td style="color:#404041;font-size:12px;line-height:16px;padding:5px 5px 3px 5px;border-top:solid 1px #999999" width="62" valign="top" align="right">
                                <b> ${{isset($admin_commission_amount)?num_format($admin_commission_amount):0}}</b>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
            </table>
         </td>
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