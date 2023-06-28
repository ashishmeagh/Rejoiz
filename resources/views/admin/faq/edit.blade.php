@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
{{-- {{dd($arr_data)}}
 --}}
{{-- {{dd($enc_id)}} --}}
 <div id="page-wrapper">

<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
            <li><a href="{{url('/')}}/admin/faq">{{$module_title or ''}}</a></li>
            <li class="active">Edit {{str_singular($module_title)}}</li>
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
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs" style="border:none;">
                  @include('admin.layout._multi_lang_tab')
               </ul>

               <div  class="tab-content">
        {{ csrf_field() }}

             
                

                 @if(isset($enc_id))
    
                    <input type="hidden" name="enc_id" value="{{$enc_id}}">
                    
                    @endif


                 @if(isset($arr_data['banner_image']))
    
                    <input type="hidden" name="old_question" value="{{$arr_data['question']}}">
                    
                    @endif

                    @if(isset($arr_data['faq_for']))
    
                    <input type="hidden" name="old_type" value="{{$arr_data['faq_for']}}">
                    
                    @endif

                     @if(isset($arr_data['answer']))
    
                    <input type="hidden" name="old_answer" value="{{$arr_data['answer']}}">
                    
                    @endif
                  
                  <div class="form-group row">
                   <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="question">Question <i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                      <input type="text" name="question" id="question" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter question" data-parsley-maxlength="255" placeholder="Question" value="{{isset($arr_data['question'])?$arr_data['question']:''}}">

                      <span class='red' id="question">{{ $errors->first('qurestion') }}</span>
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="answer">Answer <i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                      <textarea name="answer" id="answer" data-parsley-required="true" data-parsley-errors-container="#error_container" data-parsley-required-message="Please enter answer" data-parsley-maxlength="1000" data-parsley-maxlength-message="Answer is too long. It should have 1000 characters or fewer." class="form-control" placeholder="Answer">{{isset($arr_data['answer'])?$arr_data['answer']:''}}</textarea>
                      
                      <span class='red' id="answer">{{ $errors->first('answer') }}</span>
                      <div id = "error_container">
                      </div>
                    </div>
                  </div>

               <div class="form-group row">
                      <label for="type" class="col-sm-12 col-md-12 col-lg-2 col-form-label">FAQ for<i class="red">*</i></label>

                         <div class="col-sm-12 col-md-12 col-lg-10">
                          <select name="type" id="type" class="form-control" data-parsley-required ='true' data-parsley-required-message ='Please select FAQ type.'>
                              <option value="1" {{$arr_data['faq_for'] == '1' ? 'selected' : ''}}>Customers</option>
                              <option value="2" {{$arr_data['faq_for'] == '2' ? 'selected' : ''}}>Vendors</option>
                          </select>
                        </div>
                      </div>

               <div class="form-group">
                  <div class="col-sm-12 cancel_back_flex_btn"><a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true" onclick="saveTinyMceContent()"> Save</button>
                  </div>
              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
  </div>
</div>
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
<script type="text/javascript">


function saveTinyMceContent()
    {
      tinyMCE.triggerSave();
    }
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        
        $.ajax({
          url: module_url_path+'/save',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend : function()
          {
           showProcessingOverlay();
          },
          success:function(data)
          {

             
             hideProcessingOverlay();    
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

    function toggle_status()
    {
        var site_status = $('#site_status').val();
        if(site_status=='1')
        {
          $('#site_status').val('1');
        }
        else
        {
          $('#site_status').val('0');
        }
    }
  
</script>
@stop