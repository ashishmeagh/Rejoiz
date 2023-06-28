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

                           <b>Hello</b> {{ isset($user_name)?$user_name:'N/A' }}   
                           <div style="padding-top: 10px">
                                 This is inform you that {{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME") }} needs to associate your stripe account , to facilitate seemless payments.

                                 Please click the following link to complete the process.  
                                 @if( (isset($stripe_account_holder) && $stripe_account_holder != '') && (isset($stripe_secret_key)&&$stripe_secret_key != ''))
                                 
                                 <br><br>

                                 Please use below stripe details for registration/login :<br>
                                

                                    <b>Stripe Account Holder : </b>{{isset($stripe_account_holder)?$stripe_account_holder:''}} 
                                    <br>
                                    <b>Stripe secret Key : </b>{{isset($stripe_secret_key)?$stripe_secret_key:''}} 

                                 @endif
                                
                           </div> 
                           <div>
                          
                           <?php

                           $userData              = [];
                           $vendor_id             = isset($vendor_id)?$vendor_id:false;
                        
                           $clientId = isset($client_id)?$client_id:'';

                           if($clientId && $vendor_id)
                           {
                              $link = 'https://dashboard.stripe.com/oauth/authorize?response_type=code&client_id='.$clientId.'&scope=read_write&state='.$user_id.'/'.$vendor_id;
                           }
                           else
                           {
                              //$link = config('app.project.stripe_connection_link').$user_id;
                              $link = 'https://dashboard.stripe.com/oauth/authorize?response_type=code&client_id='.$clientId.'&scope=read_write&state='.$user_id;
                           }
                           
                           ?>
                           <br>
                           <center><a href="{{$link}}" class="btn btn-success" style="display: inline-block;padding: 8px 20px;background-color: #666;border-radius: 4px;color: #fff;font-size: 13px;text-decoration: none;text-transform: uppercase;font-weight: 600;">Connect to Stripe</button></center>
                          
                           </div>                          
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
                           <td style="text-align:center; color:#fff;background-color:#2a2a2a; padding-bottom:10px;"> &copy; {{ date("Y") }} by <a href="{{url('/')}}" style="text-align:center; color:#fff;">{{ strtolower(isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")) }}</a> All Rights Reserved.
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