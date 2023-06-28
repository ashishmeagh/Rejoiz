@extends('maker.layout.master')                
@section('main_content')
{{-- {{dd($arr_data)}}
 --}}<!-- Page Content -->

<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$page_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/{{$curr_panel_slug}}/dashboard">Dashboard</a></li>
            <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
            <li class="active">{{$page_title or ''}} </li>
         </ol>
      </div>
      <!-- /.col-lg-12 -->
   </div>
   <!-- .row -->
   <div class="row">
      <div class="col-sm-12">
         <div class="white-box">
            @include('admin.layout._operation_status')
            {!! Form::open([ 
                           'method'=>'POST',
                           'enctype' =>'multipart/form-data',   
                           'class'=>'form-horizontal', 
                           'id'=>'validation-form' 
            ]) !!} 
         
            <!--  <ul class="nav nav-tabs" role="tablist">
               @include('admin.layout._multi_lang_tab') 
            </ul>  -->

            <div id="myTabContent1" class="tab-content">
               @if(isset($arr_lang) && sizeof($arr_lang)>0)
               
               @foreach($arr_lang as $lang)
               <?php 

                     /* Locale Variable */  
                     $locale_category_name = "";

                     if(isset($arr_data['translations'][$lang['locale']]))
                     {
                        $locale_category_name = $arr_data['translations'][$lang['locale']]['category_name'];
                     }
                     ?>
                  <div class="tab-pane fade {{ $lang['locale']=='en'?'in active':'' }}" 
                     id="{{ $lang['locale'] }}">
      
                    @if(isset($arr_data['category_image']))
    
                    <input type="hidden" name="old_category_image" value="{{$arr_data['category_image']}}">
                    
                    @endif

                 
                     <div class="form-group row">
                        <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_name_">Name
                        @if($lang['locale'] == 'en') 
                        <i class="red">*</i>
                        @endif
                        </label>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">

                          @if($lang['locale'] == 'en')        
                           {!! Form::text('category_name_'.$lang['locale'],$locale_category_name,['class'=>'form-control','data-parsley-required'=>'true','data-parsley-pattern'=>'^[a-zA-Z&/ ]+$','data-parsley-pattern-message'=>'Please enter valid category name','data-parsley-required-message'=>'Please enter category name','data-parsley-minlength'=>'3','data-parsley-minlength-message'=>'Category name is invalid.It should be atleast 3 characters long','id' => 'category_name', 'placeholder'=>'Enter Category Name']) !!}
                           @else
                           {!! Form::text('category_name_'.$lang['locale'],$locale_category_name,['class'=>'form-control','placeholder'=>'Category Name']) !!}
                           @endif    
                        </div>
                        <span class='red'>{{ $errors->first('category_name_'.$lang['locale']) }}</span>  
                     </div>

               

                </div>
                
               @endforeach

              @endif

            </div>
               
               <div class="form-group row">
                      <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image">Image
                      </label>
                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                        
                 
                        <input type="file" name="category_image" id="category_img" class="dropify" data-default-file="{{ url('/storage/app/'.$arr_data['category_image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" 
                        />
                      </div>
                    </div>

              
              
            <div class="form-group row">
              <div class="col-sm-12 cancel_back_flex_btn">
              <input type="hidden" name="enc_id" value="{{$enc_id or ''}}">
                  <input type="hidden" name="status" value="{{$arr_data['is_active'] or ''}}">

                  <input type="hidden" name="old_image" value="{{isset($arr_data['icon'])?$arr_data['icon']:''}}">
              
                  <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>

                   <button type="button" onclick="saveTinyMceContent();" class="btn btn-success waves-effect waves-light" value="Update" id="btn_update" >Save</button>
               

            </div>
            </div>
            {!! Form::close() !!}
         </div>
      </div>
   </div>
</div>

<!-- END Main Content -->
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

       $('#btn_update').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        
        $.ajax({
          url: module_url_path+'/store',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend: function() 
          {
           showProcessingOverlay();                 
          },
          success:function(data)
          {

             hideProcessingOverlay();   
             if('success' == data.status)
             {
                 //swal(data.status,data.description,data.status);
                swal( {
                       title: "Success",
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
                
                 swal("Error",data.description,data.status);
              }  
          }
          
        });   

       
      });
   });



  function toggle_status()
  {
        var category_status = $('#category_status').val();

        if(category_status =='1')
        {
          $('#category_status').val('1');
        }
        else
        {
          $('#category_status').val('0');
        }
  } 

 </script>
 <script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
 
 <script type="text/javascript">
    function saveTinyMceContent()
    {
      tinyMCE.triggerSave();
    }
 </script> 
@stop