@extends('sales_manager.layout.master')  
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- BEGIN Main Content -->
<div class="row">
   <div class="col-md-12">
      <div class="white-box">
         @include('maker.layout._operation_status')  
         <div class="row">
            <div class="col-sm-12 col-xs-12">
               {!! Form::open([ 'url' => $module_url_path,
               'method'=>'POST',
               'id'=>'validation-form',
               'class'=>'form-horizontal' 
               ]) !!} 
               {{ csrf_field() }}
               <div class="form-group row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Current Password<i class="red">*</i></label>
                  <div class="col-sm-12 col-md-12 col-lg-10">
                     {!! Form::password('current_password',['class'=>'form-control',
                     'data-parsley-required'=>'true',
                     'data-parsley-required-message'=>'Please enter current password',
                     'id'=>'current_password',
                     'placeholder'=>'Current Password']) !!}
                     <span class='red'>{{ $errors->first('current_password') }}</span>
                  </div>
               </div>

               <div class="form-group row" >
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">New Password<i class="red">*</i></label>
                  <div class="col-sm-12 col-md-12 col-lg-10">
                     {!! Form::password('new_password',['class'=>'form-control',
                     'data-parsley-required'=>'true','data-parsley-pattern'=>"^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$",
                     'data-parsley-pattern-message'=>'<ul><li>Password must contain at least one number,one uppercase letter,one special character</li><li>Password length should be at least 8 or more characters</li></ul>',
                     'data-parsley-minlength'=>'8','data-parsley-required-message'=>'Please enter password',
                     'id'=>'new_password',
                     'placeholder'=>'New Password']) !!}
                     <span class='red'>{{ $errors->first('new_password') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Confirm Password<i class="red">*</i></label>
                  <div class="col-sm-12 col-md-12 col-lg-10">
                     {!! Form::password('new_password_confirmation',['class'=>'form-control',
                     'data-parsley-required'=>'true',
                     'data-parsley-required-message'=>'Please re-type new password',
                     'data-parsley-equalto'=>'#new_password',
                     'data-parsley-equalto-message'=>'Password should be same as new password',
                     'id'=>'new_password_confirmation',
                     'placeholder'=>'Confirm Password']) !!}
                     <span class='red'>{{ $errors->first('new_password_confirmation') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <div class="col-sm-12 col-md-12 col-lg-12 text-right">
                     <button type="submit" class="btn btn-success waves-effect waves-light" value="Save" id="save">Save</button>
                  </div>
               </div>
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">
$('#save').click(function(){
      
    /*------------password validation---------------------*/

      /* var password = $('#new_password').val();

       if(password == '')
       {
          $('#new_password').attr('data-parsley-required',true);
          $('#new_password').attr('data-parsley-required-message',"Please enter password.");
          $('#user_pwd').removeClass('user-box_password')
       }
       else
       {
          $('#new_password').attr('data-parsley-pattern',"^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$");         

          $('#new_password').attr('data-parsley-pattern-message',"<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>");

          $('#new_password').attr('data-parsley-minlength',8);

          $('#user_pwd').addClass('user-box_password');
       }*/


      /*----------------------------------------------------*/

    if($('#validation-form').parsley().validate()==false)
    {  
       hideProcessingOverlay();
       return;
    }
    else
    { 
      showProcessingOverlay();
       $("#validation-form").submit();
    } 

 }); 


 /*password validation on password field onchange*/
/*$('#new_password').change(function(){
  
    var password = $('#new_password').val();

      if(password == '')
      {
          $('#new_password').attr('data-parsley-required',true);
          $('#new_password').attr('data-parsley-required-message',"Please enter password.");
          $('#user_pwd').removeClass('user-box_password')
      }
      else
      {
          $('#new_password').attr('data-parsley-pattern',"^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$");         

          $('#new_password').attr('data-parsley-pattern-message',"<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>");

          $('#new_password').attr('data-parsley-minlength',8);

          $('#user_pwd').addClass('user-box_password');
      }

      if($('#new_password').parsley().validate()==false) return;

 });*/
</script>


@stop