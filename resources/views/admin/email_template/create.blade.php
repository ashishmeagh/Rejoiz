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
               {!! Form::open([ 'url' => $module_url_path.'/store',
               'method'=>'POST',
               'enctype' =>'multipart/form-data',   
               'class'=>'form-horizontal', 
               'id'=>'validation-form' 
               ]) !!} 
               {{ csrf_field() }}
               <div  class="tab-content">
                  <div class="form-group row">
                     <label class="col-2 col-form-label" for="email"> Name <i class="red">*</i> </label>
                     <div class="col-10">       
                        {!! Form::text('template_name',old('template_name'),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-maxlength'=>'255', 'placeholder'=>'Email Template Name']) !!}  
                     </div>
                     <span class='red'> {{ $errors->first('template_name') }} </span>  
                  </div>
                  <div class="form-group row">
                     <label class="col-2 col-form-label" for="email">  Subject 
                     <i class="red">*</i> 
                     </label>
                     <div class="col-10">       
                        {!! Form::text('template_subject',old('template_subject'),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-maxlength'=>'255', 'placeholder'=>'Email Template Subject']) !!}  
                     </div>
                     <span class='red'> {{ $errors->first('template_subject') }} </span>  
                  </div>
                  <div class="form-group row">
                     <label class="col-2 col-form-label" for="email"> Body 
                     <i class="red">*</i> 
                     </label>
                     <div class="col-10">   
                        {!! Form::textarea('template_html',old('template_html'),['class'=>'form-control', 'class'=>'form-control wysihtml5', 'rows'=>'20','placeholder'=>'Email Template Body']) !!}  
                     </div>
                     <span class='red'> {{ $errors->first('template_html') }} </span>  
                  </div>
                  <div class="form-group row">
                     <label class="col-2 col-form-label" for="email"> Variables 
                     <i class="red">*</i> 
                     </label>
                     <div class="col-10">   
                        {!! Form::text('variables[]',old('variables[]'),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-maxlength'=>'500', 'placeholder'=>'Variables']) !!}  
                     </div>
                     <a class="btn btn-info btn-outline" href="javascript:void(0)" onclick="add_text_field()">
                     <i class="fa fa-plus"></i>
                     </a>
                     <a class="btn btn-danger btn-outline" href="javascript:void(0)" onclick="remove_text_field(this)">
                     <i class="fa fa-minus"></i>
                     </a>
                     <span class='red'> {{ $errors->first('variables[]') }} </span>  
                  </div>
                  <div id="append_variables"></div>
                  <br>
                  <div class="form-group row">
                     <div class="col-10">
                       <button type="submit" onclick="saveTinyMceContent();" class="btn btn-success waves-effect waves-light" value="Create" id="btn_add">Create</button>
                        <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path}}">Back</a>

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
   function add_text_field() 
   {
       var html = "<div class='form-group row appended' id='appended'><label class='col-2 col-form-label'></label><div class='col-10'><input class='form-control' name='variables[]' data-rule-required='true' placeholder='Variables' /></div><div id='append_variables'></div></div>";
       jQuery("#append_variables").append(html);
   }
   
   function remove_text_field(elem)
   {
      $( ".appended:last" ).remove();
   }
   
   $(document).ready(function()
   {
      var module_url_path  = "{{ $module_url_path or ''}}";

      $('#btn_add').click(function()
      {

         if($('#validation-form').parsley().validate()==false) return;

            var formdata = $('#validation-form').serialize();

            $.ajax({
                           
                     url: module_url_path+'/store',
                     type:"POST",
                     data: formdata,
                     dataType:'json',
                     success:function(data)
                     {
                           if('success' == data.status)
                           {
                            
                            $('#validation-form')[0].reset();

                              swal({
                                     title:"Success",
                                     text: data.description,
                                     type: data.status,
                                     confirmButtonText: "OK",
                                     closeOnConfirm: false
                                  },
                                 function(isConfirm,tmp)
                                 {
                                   if(isConfirm==true)
                                   {
                                      window.location = data.link;
                                   }
                                 });
                           }
                           else
                           {
                             swal('Error',data.description,data.status);
                           }  
                     }
                   
                  });   
      });
   
   });
</script>
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
@stop