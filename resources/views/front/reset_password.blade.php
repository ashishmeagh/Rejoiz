@extends('front.layout.master')
@section('main_content')

<div class="login-main-bg-img">
    
    <form id="frm-change-pwd">
         {{ csrf_field() }}
         <input type="hidden" name="code" value="{{$code or ''}}">
        <div class="login-main-page-dv-hm">
           
            <div class="col-sm-4 login-right-page frg-min-hieght">
               <div class="kadoe-logo">
                    <img src="{{url('/')}}/assets/front/images/{{$site_setting_arr['login_site_logo']}}" alt="{{$site_setting_arr['project_name'] or ''}}" />
                  </div>
                <div class="titlelogin">Reset Password</div>
                 <div id="status_msg"></div>
                <div class="loginform">
                    <div class="user-box largeerror" id="user_pwd">
                        <label class="form-lable">New Password</label>
                        <input class="cont-frm" 
                               type="password" 
                               placeholder="New Password"  
                               name="new_password" 
                               id="new_password"
                               data-parsley-required="true"
                               data-parsley-required-message="Please enter password."
                               data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                               data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>"
                               data-parsley-minlength="8"/>
                    </div>
                    <div class="user-box">
                        <label class="form-lable">Confirm Password</label>
                        <input class="cont-frm" 
                               type="password" 
                               placeholder="Confirm Password" 
                               data-parsley-equalto="#new_password" 
                               data-parsley-error-message="Confirm password should be same as new password." data-parsley-required="true" 
                               data-parsley-required-message="Please enter confirm password." 
                               name="cnfm_new_password" 
                               id="cnfm_new_password" />
                    </div>
                    <div class="button-login-pb">
                        <button type="button" id="btn-change-pwd" class="gt-button">Reset Password</button>
                        <div class="clearfix"></div>
                    </div>

                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </form>
</div>

<script type="text/javascript">
     $('#btn-change-pwd').click(function(){


       /*------------password validation---------------------*/

        var password = $('#new_password').val();

        if(password != '')
        { 
          $('#user_pwd').addClass('user-box_password');
        }
        else if(password == '')
        {
          $('#user_pwd').removeClass('user-box_password');
        }

      /*----------------------------------------------------*/



      if($('#frm-change-pwd').parsley().validate()==false) return;

      var form_data = $('#frm-change-pwd').serialize();
      var url = '{{url('/')}}/process_reset_password';
      $.ajax({
        url:url,
        data:form_data,
        method:'POST',        
        dataType:'json',
        beforeSend : function()
        {
          showProcessingOverlay();
          $('#btn-change-pwd').prop('disabled',true);
          $('#btn-change-pwd').html('Updating... <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
        },
        success:function(response)
        {  
          

          hideProcessingOverlay();
          $('#btn-change-pwd').prop('disabled',false);
          $('#btn-change-pwd').html('Reset password');

          if(typeof response =='object')
          {
            if(response.status && response.status=="SUCCESS")
            {
                 /* var success_HTML = '';
                  success_HTML +='<div class="alert alert-success alert-dismissible">\
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                        <span aria-hidden="true">&times;</span>\
                      </button>'+response.msg+'</div>';

                  $('#status_msg').html(success_HTML);*/

                 window.location = response.link;
              
            }
            else
            {                    
                var error_HTML = '';   
                error_HTML+='<div class="alert alert-danger alert-dismissible">\
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                        <span aria-hidden="true">&times;</span>\
                    </button>'+response.msg+'</div>';
                
                $('#status_msg').html(error_HTML);
            }
          }
        }
      });
  });

/*password validation on password field onchange*/
$('#new_password').on("change keyup blur",function(){
  
    var password = $('#new_password').val();

    if(password != '')
    { 
       $('#user_pwd').addClass('user-box_password');
    }
        
    else if(password == '')
    {
        $('#user_pwd').removeClass('user-box_password');
    }
   
    if($('#new_password').parsley().validate()==false) return;

 });

</script>
@endsection