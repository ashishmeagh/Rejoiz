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
               {!! Form::open([
                              'method'=>'POST',
                              'class'=>'form-horizontal',
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!}
               <!-- <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul> -->

               <div  class="tab-content rw-create-category-admin-tab-content">

                @if(isset($arr_lang) && sizeof($arr_lang)>0)

                    @foreach($arr_lang as $lang)

                      <div class="tab-pane fade {{ $lang['locale']=='en'?'in active':'' }}"
                       id="{{ $lang['locale'] }}">

                       <div class="row">
                          <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="state">Name @if($lang['locale'] == 'en')
                          <i class="red">*</i>
                          @endif
                          </label>
                          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                             @if($lang['locale'] == 'en')
                             {!! Form::text('category_name_'.$lang['locale'],old('category_name_'.$lang['locale']),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-pattern'=>'^[a-zA-Z&/ ]+$','data-parsley-required-message'=>'Please enter category name','data-parsley-minlength'=>'3','data-parsley-minlength-message'=>'Category name is invalid.it should be atleast 3 characters long','placeholder'=>'Enter Category Name']) !!}


                             @else
                             {!! Form::text('category_name_'.$lang['locale'],old('category_name_'.$lang['locale'])) !!}
                             @endif
                          </div>
                          <span class='red'>{{ $errors->first('category_name_'.$lang['locale']) }}</span>
                       </div>

                    </div>
                  @endforeach
                  @endif

                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                         <input type="checkbox" name="category_status" id="category_status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>
                  </div>

                  <div class="row">
                     <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Priority<i class="red">*</i></label>
                       <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                          <input type="text" name="priority" id="priority" class="form-control" placeholder="Enter the priority" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-required-message="Please enter priority." data-parsley-pattern-message="Only numbers are allowed."/>
                       </div>
                   </div>


                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Image<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="category_image" id="category_image" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} data-parsley-required ="true" data-parsley-required-message="Please select category image" data-parsley-errors-container="#err_container" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                      <div id ="err_container"></div>
                   </div>
                  </div>


               </div>

               <div class="cancel_back_flex_btn">
                <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>
              </div>

               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
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