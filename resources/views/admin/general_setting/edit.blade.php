@extends('admin.layout.master')                

@section('main_content')
<!-- Page Content -->

  <div id="page-wrapper">
      <div class="container-fluid">
          <div class="row bg-title">
              <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                  <h4 class="page-title">{{$page_title or ''}}</h4> </div>
              <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                  <ol class="breadcrumb">
                      <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                      <li><a href="{{$module_url_path or ''}}">{{$module_title or ''}}</a></li>
                      <li class="active">Create Banner</li>
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
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'validation-form' 
                                ]) !!} 
                                 
                                <input type="hidden" name="id" id="id" value="{{ $arr_banner_data['id'] or '' }}" />
                                       
                                <div class="form-group row">
                                  <label class="col-2 col-form-label" for="title"> Order Sequence <i class="red">*</i></label>
                                  <div class="col-10">
                                     
                                    <input type="text" name="order_sequence" id="order_sequence" class="form-control" data-parsley-required="true" placeholder="Enter Sequence" data-parsley-pattern="^[0-9]+$" data-parsley-pattern-message="Only Numbers Are Allowed." value="{{$arr_banner_data['banner_order_sequence'] or ''}}">

                                  </div>
                                </div>

                                 <div class="form-group row">
                                  <label class="col-2 col-form-label" for="cat_image"> Image <i class="red">*</i></label>
                                  <div class="col-10">
                                  <input type="file" name="banner_image" id="banner_image" class="dropify"  data-max-file-size="2M" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-errors-position="outside" data-parsley-errors-container="#image_error" data-default-file="{{$banner_public_img_path or ''}}{{$arr_banner_data['banner_image'] or ''}}">

                                  <span id="image_error">{{ $errors->first('banner_image') }}  </span>
                                  </div>
                                  </div>
                                     
                                    @php
                                      $status = '';
                                     if(isset($arr_banner_data['is_active']) && $arr_banner_data['is_active'] != '') 
                                     {
                                        $status = $arr_banner_data['is_active'];
                                     }
                                     else
                                     {
                                        $status = '';
                                     }
                                    @endphp 

                                  <div class="form-group row">
                                  <label class="col-md-2 col-form-label">Is Active</label>
                                    <div class="col-sm-6 col-lg-8 controls">
                                       <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();"  @if($status == '1') checked="checked" @endif/>
                                    </div>    
                                  </div> 

                                        
                                  <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="Save" id="btn_add">Add</button>
                                  <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}">Back</a>
                                    
                                  <input type="hidden" name="old_img" value="{{isset($arr_banner_data['banner_image'])?$arr_banner_data['banner_image']:''}}">

                                        <!-- form-group -->
                                  {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!-- END Main Content -->
<script type="text/javascript">
  $(document).ready(function(){

  var module_url_path  = "{{ $module_url_path or ''}}";

  $('#btn_add').click(function(){

  if($('#validation-form').parsley().validate()==false) return;
   
      $.ajax({
                  
          url: module_url_path+'/store',
          data: new FormData($('#validation-form')[0]),
          contentType:false,
          processData:false,
          method:'POST',
          cache: false,
          dataType:'json',
          success:function(data)
          {

              if('success' == data.status)
              {
                
                $('#validation-form')[0].reset();

                  swal({
                         title: data.status,
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
                 swal('warning',data.description,data.status);
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