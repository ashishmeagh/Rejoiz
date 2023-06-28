@extends('admin.layout.master')                
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
         <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
<div class="col-sm-12">
   <div class="white-box">
      @include('admin.layout._operation_status')
      <div class="row">
         <div class="col-sm-12 col-xs-12">
            {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_data['id']),
            'method'=>'POST',
            'enctype' =>'multipart/form-data',   
            'class'=>'form-horizontal', 
            'id'=>'validation-form' 
            ]) !!} 
            {{ csrf_field() }}
            <div class="tab-content">
               <div class="m-b-20 row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="email"> Template Name<i class="red">*</i> 
                  </label>
                  <div class="col-sm-12 col-md-12 col-lg-10">       
                     {!! Form::text('template_name',$arr_data['template_name'],['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter template name','data-parsley-maxlength'=>'255', 'placeholder'=>'Email Template Name']) !!}  
                  </div>
                  <span class='red'> {{ $errors->first('template_name') }} </span>  
               </div>
               <div class="m-b-20 row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="email"> From 
                  <i class="red">*</i> 
                  </label>
                  <div class="col-sm-12 col-md-12 col-lg-10">      
                     {!! Form::text('template_from',$arr_data['template_from'],['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter template from','data-parsley-maxlength'=>'255', 'placeholder'=>'Email Template From']) !!}  
                  </div>
                  <span class='help-block'> {{ $errors->first('template_from') }} </span>  
               </div>
               <div class="m-b-20 row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="email">  From Email 
                  <i class="red">*</i> 
                  </label>
                  <div class="col-sm-12 col-md-12 col-lg-10">     
                     {!! Form::text('template_from_mail',$arr_data['template_from_mail'],['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter template from email','data-parsley-maxlength'=>'255','data-parsley-type'=>'email','data-parsley-type-message'=>'Please enter valid email', 'placeholder'=>'Email Template From Email']) !!}  
                  </div>
                  <span class='help-block'> {{ $errors->first('template_from_mail') }} </span>  
               </div>
               <div class="m-b-20 row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="email"> Subject 
                  <i class="red">*</i> 
                  </label>
                  <div class="col-sm-12 col-md-12 col-lg-10">      
                     {!! Form::text('template_subject',$arr_data['template_subject'],['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter subject','data-parsley-maxlength'=>'255', 'placeholder'=>'Email Template Subject']) !!}  
                  </div>
                  <span class='help-block'> {{ $errors->first('template_subject') }} </span>  
               </div>
               <div class="m-b-20 row">
                  <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="email">  Body 
                  <i class="red">*</i> 
                  </label>
                  <div class="col-sm-12 col-md-12 col-lg-10">   
                     {!! Form::textarea('template_html',$arr_data['template_html'],['class'=>'form-control', 'class'=>'form-control','id' => 'template_html', 'rows'=>'25', 'data-parsley-required'=>'true','data-parsley-errors-container'=>'#error_container','data-parsley-required-message'=>'Please enter content', 'placeholder'=>'Email Template Body']) !!}  
                     <div id="error_container">
                     </div>
                     <span class='red'> {{ $errors->first('template_html') }} </span> 
                     
                     <p class="text-info m-t-20"> Variables </p>
                     
                     @if(sizeof($arr_variables)>0)
                     @foreach($arr_variables as $variable)
                     <br> <label> {{ $variable }} </label> 
                     @endforeach
                     @endif
                     
                  </div>
               </div>
               <div class="m-b-20 row">
                  <div class="col-md-12 text-center">
                     <button type="submit" class="btn btn-success waves-effect waves-light" value="Update" id="btn_update" onclick="saveTinyMceContent();">Update</button>
                     <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}">Back</a>
                     <a class="btn btn-success" target="_blank" href="{{ url('/admin/email_template').'/view/'.base64_encode($arr_data['id']) }}"  title="Preview">Preview</a>
                  </div>
               </div>

               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<!-- END Main Content -->
<script type="text/javascript">
function saveTinyMceContent()
{
      tinyMCE.triggerSave();  
}
   $(document).ready(function()
   {
     $("#btn_update").click(function()
       {
          if($('#validation-form').parsley().validate()==false)
          {  
             hideProcessingOverlay();
             return;
          }
          else
          { 
            showProcessingOverlay();
            tinyMCE.triggerSave();
            $("#validation-form").submit();
          }   
       });    

   });
</script>


<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
@stop