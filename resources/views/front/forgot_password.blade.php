@extends('front.layout.master')
@section('main_content')

<div class="login-main-bg-img ">
    <form id="frm-forgot-password" onsubmit="return false;" method="POST">
        {{csrf_field()}}
        <div class="login-main-page-dv-hm forgt-bg-images">
            
            <div class="col-sm-4 login-right-page frg-min-hieght">
                <div class="kadoe-logo">
                    <img src="{{url('/')}}/storage/app/{{$site_setting_arr['login_site_logo']}}" alt="{{$site_setting_arr['site_name']}}" />
                  </div>
                  <div class="welcomebackkadoe">Please enter your email and we will send you a link to reset your password</div>
                {{-- <div class="titlelogin">Reset your password</div> --}}
                <div id="status_msg"></div>
                <div class="loginform">
                    <div class="user-box">
                        <label class="form-lable">Email Address</label>
                        <input class="cont-frm" placeholder="Enter Your Email Address" type="text" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-pattern="[^@\s]+@(?:[-a-zA-Z0-9]+\.)+[a-z ]{2,}"  data-parsley-pattern-message="Please enter valid email address." id="email" name="email" />
                    </div>
                    <div class="button-login-pb">
                        <button type="button" id="btn-forgot-password" class="gt-button signin-b-margin">Send reset password link</button>
                        <div class="clearfix"></div>
                    </div>
                    <div class="account-links-nw"> <a href="{{url('/login')}}">Back to Sign In</a></div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </form>
</div>
<script type="text/javascript">

 $('#email').blur(function(){
    var myStr = $("#email").val();
    var trimStr = $.trim(myStr);
    $("#email").val(trimStr);
  
});

$('#btn-forgot-password').click(function()
{
    process_forgot_password();
});
	
function process_forgot_password(){
    if($('#frm-forgot-password').parsley().validate()==false) return;

    var form_data = $('#frm-forgot-password').serialize();
    var url       = '{{url('/')}}/forgot_password';
    
    $.ajax({
        url:url,
        data:form_data,
        method:'POST',        
        dataType:'json',
        beforeSend : function()
        {
          showProcessingOverlay();
          $('#btn-forgot-password').prop('disabled',true);
          $('#btn-forgot-password').html('Please Wait... <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
        },
        success:function(response)
        {
          hideProcessingOverlay();
          $('#btn-forgot-password').prop('disabled',false);
          $('#btn-forgot-password').html('Submit');

          if(typeof response =='object')
          {
            if(response.status && response.status=="SUCCESS")
            {
                var success_HTML = '';
                success_HTML +='<div class="alert alert-success alert-dismissible">\
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                        <span aria-hidden="true">&times;</span>\
                    </button>'+response.msg+'</div>';

                    $('#status_msg').html(success_HTML);

               // window.location = response.link;

               //window.location.reload();

               $('#email').val('');
                          
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
}

</script>
@endsection