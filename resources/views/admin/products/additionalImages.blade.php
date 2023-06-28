@extends('admin.layout.master') @section('main_content')

<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">{{$module_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                      <li><a href="{{$module_url_path or url('/')}}">{{$module_title or ''}}</a></li>                      
                      <li class="active">{{$page_title or ''}}</li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>

        <!-- BEGIN Main Content -->

        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    @include('admin.layout._operation_status')
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <div class="box-title">
                                <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}
                                </h3>
                                <div class="box-tool"></div>
                            </div>
                            <form class="form-horizontal" id="add-product-frm" enctype="multipart/form-data" method="POST">
                                {{ csrf_field() }}

                                <input type="hidden" name="product_id" value="{{$product_id or 0}}">
                                <div class="form-group">
                                    <input type="hidden" name="old_product_image" value="{{ $product_image_arr['product_image'] or '' }}">
                                    <label class="col-md-2 control-label" for="product_image">Upload Product Image<i class="red">*</i> </label>
                                    <div class="col-md-10">
                                        <input type="file" name="product_image" id="product_image" class="dropify" data-default-file="{{ isset($product_image_arr['product_image']) && $product_image_arr['product_image']!=''?url('/storage/app/'.$product_image_arr['product_image']):null}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="hidden" name="old_lifestyle_image" value="{{ $product_image_arr['lifestyle_image'] or '' }}">
                                    <label class="col-md-2 control-label" for="product_image">Upload Life Style Image<i class="red">*</i> </label>
                                    <div class="col-md-10">
                                        <input type="file" name="life_style_image" id="life_style_image" class="dropify" data-default-file="{{ isset($product_image_arr['lifestyle_image']) && $product_image_arr['lifestyle_image']!=''?url('/storage/app/'.$product_image_arr['lifestyle_image']):null}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="hidden" name="old_packaging_image" value="{{ isset( $product_image_arr['packaging_image'])?$product_image_arr['packaging_image']:'' }}">
                                    <label class="col-md-2 control-label" for="product_image">Upload Packaging Image<i class="red">*</i> </label>
                                    <div class="col-md-10">
                                        <input type="file" name="packaging_image" id="packaging_image" class="dropify" data-default-file="{{ isset($product_image_arr['packaging_image']) && $product_image_arr['packaging_image']!=''?url('/storage/app/'.$product_image_arr['packaging_image']):null}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                                    </div>
                                </div>

                                <div class="input-group row">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                                        <a href="{{ $module_url_path.'/style_and_dimensions/'}}{{base64_encode($product_id)}}" class="btn btn-success waves-effect waves-light m-r-10">Back<i class="fa fa-arrow-left"></i></a>
                                        <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="Save" id="saveAndProceed">Finished</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";

  $(document).ready(function() {

      $('#saveAndProceed').click(function() 
      {
          if ($('#add-product-frm').parsley().validate() == false) return;

          $.ajax({
              url: module_url_path + '/storeProductAdditionalImages',
              type: "POST",
              data: new FormData($("#add-product-frm")[0]),
              contentType: false,
              processData: false,
              dataType: 'json',
              success: function(response) {
                  if (response.status == 'SUCCESS') {
                      $('#add-product-frm')[0].reset();

                      swal({
                              title: 'Success',
                              text: response.description,
                              type: 'success',
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                          },
                          function(isConfirm, tmp) {
                              if (isConfirm == true) {
                                  window.location = response.next_url;
                              }
                          });
                  } else {
                      swal('Error', response.description, 'error');
                  }
              }

          });

      });
  });
</script>
@endsection