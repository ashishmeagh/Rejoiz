
@extends('retailer.layout.master')
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
             <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- BEGIN Main Content -->
<div class="row">
   <div class="col-md-12">
      <div class="white-box">
         @include('retailer.layout._operation_status')  
         <div class="row">
            <div class="col-sm-12 col-xs-12">
               {!! Form::open([ 'url' => $module_url_path.'/update_password',
               'method'=>'POST',
               'id'=>'validation-form',
               'class'=>'form-horizontal' 
               ]) !!} 
               {{ csrf_field() }}
               <div class="form-group row">
                  <label class="col-lg-3 col-md-12 col-sm-12 col-form-label">Current password<i class="red">*</i></label>
                  <div class="col-lg-9 col-md-12 col-sm-12">
                     {!! Form::password('current_password',['class'=>'form-control',
                     'data-parsley-required'=>'true',
                     'data-parsley-required-message'=>'Please enter current password.',
                     'id'=>'current_password',
                     'placeholder'=>'Current Password']) !!}
                     <span class='red'>{{ $errors->first('current_password') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <label class="col-lg-3 col-md-12 col-sm-12 col-form-label">New password<i class="red">*</i></label>
                  <div class="col-lg-9 col-md-12 col-sm-12 user-box_password">
                     {!! Form::password('new_password',['class'=>'form-control',
                     'data-parsley-required'=>'true',
                     'data-parsley-required-message'=>'Please enter new password.',
                     'data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"',
                     'data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>"',
                     'data-parsley-minlength'=>'8',
                     'id'=>'new_password',
                     'placeholder'=>'New Password']) !!}
                     <span class='red'>{{ $errors->first('new_password') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <label class="col-lg-3 col-md-12 col-sm-12 col-form-label">Confirm Password<i class="red">*</i></label>
                  <div class="col-lg-9 col-md-12 col-sm-12">
                     {!! Form::password('new_password_confirmation',['class'=>'form-control',
                     'data-parsley-required'=>'true',
                      'data-parsley-required-message'=>'Please re-enter new password.',
                     'data-parsley-equalto'=>'#new_password',
                      'data-parsley-error-message'=>'Password should be same as new password.',
                     'id'=>'new_password_confirmation',
                     'placeholder'=>'Confirm password']) !!}
                     <span class='red'>{{ $errors->first('new_password_confirmation') }}</span>
                  </div>
               </div>
               <div class="form-group row">
                  <div class="col-lg-9 col-md-12 col-sm-12">
                     <button type="submit" class="btn btn-success waves-effect waves-light m-r-10" value="Save">Save</button>
                  </div>
               </div>
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop