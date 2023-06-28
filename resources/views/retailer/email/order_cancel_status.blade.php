<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title></title>
   </head>
   @php
      $site_logo = get_site_settings(['site_logo']);
      $site_logo = url('/storage/app/'.$site_logo['site_logo']);
      if(file_exists($site_logo)==true && $site_logo!='')
   @endphp
   <body style="background:#f1f1f1; margin:0px; padding:0px; font-size:12px; font-family:Arial, Helvetica, sans-serif; line-height:21px; color:#666; text-align:justify;">
      <div style="max-width:630px;width:100%;margin:0 auto;">
         <div style="padding:0px 15px;">
            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
               <tr>
                  <td>&nbsp;</td>
               </tr>
               <tr>
                  <td bgcolor="#FFFFFF" style="padding:15px; border:1px solid #e5e5e5;">
                     <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                           <td>
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                 <tr>
                                    <td><a href="{{url('/')}}"><img src="{{$site_logo}}" width="150px" alt=""/></a></td>
                                    <td align="right" style="font-size:13px; font-weight:bold;">{{ date('m-d-Y') }}</td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                        <tr>
                           <td height="10"></td>
                        </tr>
                        <tr>
                           <td  height="1" bgcolor="#ddd"></td>
                        </tr>
                        <tr>
                           <td  height="10"></td>
                        </tr>
                        <tr>
                           <td>
                            
                        
                 
                              <p>Dear Sir/Madam,</p> 
                              @if($status == 2)
                                
                              <p>Order No: {{$order_details['order_no']}}, Vendor has been approved your cancel order request.</p> 

                              @elseif($status == 0)

                              <p>Order No: {{$order_details['order_no']}}, Vendor has been rejected your cancel order request. 
                              </p>

                              <p>Vendor Rejection Reason: {{$reason or ''}}</p>

                              @endif
                                 
                                <p>Thanks and Regards, &nbsp; &nbsp; &nbsp;</p> 
                                <p>{{$site_setting_arr['site_name'] or ''}}&nbsp; &nbsp; </p> 
                                <p>&nbsp;</p>
                              
                           </td>
                        </tr>
                        <tr>
                           <td>&nbsp;</td>
                        </tr>
                        <tr>
                           <td height="2" bgcolor="#3f3f3f"></td>
                        </tr>
                        <tr>
                           <td height="10" style="background-color:#2a2a2a;"></td>
                        </tr>
                        <tr>
                           <td style="text-align:center; color:#fff;background-color:#2a2a2a; padding-bottom:10px;"> Copyright {{ date("Y") }} by <a href="{{url('/')}}" style="text-align:center; color:#fff;">{{ strtolower(isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")) }}</a> All Rights Reserved.
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td>&nbsp;</td>
               </tr>
            </table>
         </div>
      </div>
   </body>
</html>