<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title></title>
   </head>
   <body style="background:#f1f1f1; margin:0px; padding:0px; font-size:12px; font-family:Arial, Helvetica, sans-serif; line-height:21px; color:#666; text-align:justify;">
      <div style="max-width:630px;width:100%;margin:0 auto;">
         <div style="padding:0px 15px;">
            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
               <tr>
                  <td>&nbsp;</td>
               </tr>
               <tr>
                  <td bgcolor="#FFFFFF" style="padding:15px; box-shadow: 0px 3px 13px 2px rgba(0, 0, 0, 0.17); color: #425065; font-size: 14px;">
                     <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                           <td>
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                 <tr>

                                    <td><a href="{{url('/')}}"><img src="{{ url('/')}}/storage/app/{{isset($site_setting_arr['site_logo'])?$site_setting_arr['site_logo']:""}}"  alt="logo" width="150px" height="" /></a></td>
                                    <td align="right" style="font-size:13px; font-weight:bold; color: #3671e2;">{{ date('m-d-y') }}</td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                        <br>
                        Your order has been splitted successfuly please check attachements for further details
            </table>
         </div>
      </div>
   </body>
</html>