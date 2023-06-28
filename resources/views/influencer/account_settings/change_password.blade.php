@extends('influencer.layout.master')
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
         <li><a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- BEGIN Main Content -->
<div class="row">
   <div class="col-md-12">
      <div class="white-box">
         @include('influencer.layout._operation_status')  
         <div class="row">
            <div class="col-sm-12 col-xs-12">
               {!! Form::open([ 'url' => $influencer_panel_slug.'/change_password',
                                'method'=>'POST',
                                'id'=>'validation-form',
                                'class'=>'form-horizontal' 
                            ]) 
                !!} 
               {{ csrf_field() }}
               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Current Password<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input  class="form-control" 
                             type="password" 
                             name="current_password" 
                             id="current_password"
                             data-parsley-required="true"
                             data-parsley-required-message="Please enter current password"
                             placeholder="Current Password"
                             >
                     <span class='red'>{{ $errors->first('current_password') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">New Password<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input class="form-control" 
                            type="password" 
                            name="new_password"
                            id="new_password"
                            data-parsley-required="true"
                            data-parsley-required-message="Please enter password"
                            data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                            data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character</li><li>Password length should be at least 8 or more characters</li></ul>"
                            data-parsley-minlength="8"
                            placeholder="New Password"
                            >
                     <span class='red'>{{ $errors->first('new_password') }}</span>
                  </div>
               </div>
               <div class="form-group row">

                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Confirm Password<i class="red">*</i></label>

                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input class="form-control" 
                            type="password" 
                            name="new_password_confirmation"
                            id="new_password_confirmation"
                            data-parsley-required="true"
                            data-parsley-required-message="Please re-type new password"
                            data-parsley-equalto="#new_password"
                            data-parsley-equalto-message="Password should be same as new password"
                            placeholder="Confirm Password"
                            >
                     <span class='red'>{{ $errors->first('new_password_confirmation') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <div class="col-sm-12 common_back_save_btn">
                     <a class="btn btn-inverse waves-effect waves-light pull-left backbtn" href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}"><i class="fa fa-arrow-left"></i> Back</a>
                     <button type="submit" style="float: right"  class="btn btn-success waves-effect waves-light" value="Save" id="btn_save">Save</button>
                  </div>
               </div>

                
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">
$("#btn_save").click(function()
{   
     if($('#validation-form').parsley().validate()==false)
     {
       hideProcessingOverlay();
       return; 
     }
     else
     {
       showProcessingOverlay();   
       $('#validation-form').submit();
     }
}); 



</script>
<!-- END Main Content -->
@stop