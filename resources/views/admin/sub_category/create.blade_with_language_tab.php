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
        <li><a href="{{url('/')}}/{{$curr_panel_slug}}/dashboard">Dashboard</a></li>
         <li><a href="{{ $module_url_path or url('/') }}">{{$module_title or ''}}</a></li>
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
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>

               <div  class="tab-content">

                <div class="row m-b-15">
                        <label for="categories" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Select Caetgory <i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-10 controls">
                          <select name="category" id="category" class="form-control" data-parsley-required ='true' data-parsley-required-message='Please select category.'>
                              <option value="">Select Category</option>
                                @foreach($categories_arr as $category)
                                  <option value="{{ $category['id'] }}">{{ $category['category_name'] }}</option>
                                @endforeach
                              </select>
                           </div>
                  </div>

                @if(isset($arr_lang) && sizeof($arr_lang)>0)

                    @foreach($arr_lang as $lang)

                      <div class="tab-pane fade {{ $lang['locale']=='en'?'in active':'' }}" 
                       id="{{ $lang['locale'] }}">
  
    
                       <div class="row m-b-15">
                          <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="sub_category_name"> Sub Category Name @if($lang['locale'] == 'en') 
                          <i class="red">*</i>
                          @endif
                          </label>
                          <div class="col-sm-12 col-md-12 col-lg-10 controls">
                             @if($lang['locale'] == 'en')        
                             {!! Form::text('sub_category_name_'.$lang['locale'],old('sub_category_name_'.$lang['locale']),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-pattern'=>'^[a-zA-Z&/ ]+$','data-parsley-required-message'=>'Please enter subcategory name.','data-parsley-minlength'=>'3','data-parsley-minlength-message'=>'Subcategory name is invalid.it should be atleast 3 characters long.', 'placeholder'=>'Subcategory Name']) !!}
                             @else
                             {!! Form::text('sub_category_name_'.$lang['locale'],old('sub_category_name_'.$lang['locale'])) !!}
                             @endif    
                          </div>
                          <span class='red'>{{ $errors->first('sub_category_name_'.$lang['locale']) }}</span>  
                       </div>

                    </div>
                  @endforeach
                  @endif

                  <div class="row m-b-15">
                    <label class="col-md-12 col-form-label">Status</label>
                      <div class="col-sm-6 col-md-12 col-lg-8 controls">
                         <input type="checkbox" name="sub_category_status" id="sub_category_status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 

               </div>
               <br>

               <div class="cancel_back_flex_btn">

                <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path or url('/')}}";
   $(document).ready(function(){

       $('#btn_add').click(function(){

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
                 
                 $('#validation-form')[0].reset();

                 swal({
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