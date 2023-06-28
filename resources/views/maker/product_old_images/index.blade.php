
@extends('maker.layout.master')
@section('main_content')
<style type="text/css">
.img-shop-tbl.nw-in-shp { width: 140px;}
.img-shop-tbl.nw-in-shp .dropify-wrapper{ height: 100px;}
th {
    white-space: nowrap;
}
.btn.btn-circle.btn-danger.btn-outline{
  border: 1px solid #DFDFDF;
    background-color: #fff;
    color: #444;
    font-size: 14px;
    padding: 13px 30px 12px;
    border-radius: 4px;
    display: inline-block;
    height: auto;
}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
     <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
          <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
              <ol class="breadcrumb">
                <li><a href="{{url('/')}}/{{$maker_panel_slug or ''}}/dashboard">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
              </ol>
          </div>
        </div>
         <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12 upload-box">
          @include('admin.layout._operation_status')
          <div class="alert alert-danger" id="err_file" style="display:none;">
             <button type="button" class="close" style="margin-top: 0px !important;padding: 0px !important;" data-dismiss="alert" aria-hidden="true">×</button>
          </div>
           <div class="indext-jtsd">
          <form id="upload_zip_file" class="rw-vendor-upload-zip" enctype="multipart/form-data" method="POST" >
            {{csrf_field()}}
            <div class="nt-rxlcs upload_zip-choos-file">
             <input type="file" accept=".zip" class="idx-imprt" name="upload_bulk_data" id="upload_bulk_data" data-parsley-required="true" data-parsley-trigger="change" data-default-file data-allowed-file-extensions="zip" data-parsley-required-message="Please select .zip format file">
              <div id="err_file" class="red"></div>
              </div>
             <input type="button" name="upload_zip" id="upload_zip" value="Upload Zip" class="btn btn-info rw-upload-zip">
            </form>
             <div class="clearfix"></div>
           </div>
           <div class="col-sm-12 terms red">
              <ul>
                <h3 class="red">Note</h3>
                <li>File size must be 250MB or less.</li>
                <li>File must be in .zip format.</li>
                {{-- <li>Subfolder(s) are not allowed in zip.</li> --}}
                <li>Only jpg, png & jpeg extensions images are allowed.</li>
                {{-- <li>Image name must be SKU Number of its product.</li> --}}
                <li>Folder name must be SKU Number of its product images.</li>
                <li>Number of multiple images must be up to 5. </li>
                <li>Image dimension must be 407 * 500 or greater.</li>
              </ul>
            </div>
        <div class="clearfix"></div>
         </div>
         <!-- /.col-lg-12 -->

   </div>
</div>
<!-- /#page-wrapper -->
<script type="text/javascript">
var module_url_path = "{{ $module_url_path or '' }}";
$('#upload_zip').click(function() {
    if ($('#upload_zip_file').parsley().validate() == false) {
        hideProcessingOverlay();
        return;
    } else {
        // showProcessingOverlay();
        $.LoadingOverlay("show",{
          imageColor      : "#447ec7",
          text: "Uploading..."
        });
        return checkfilesize();
    }
});

function checkfilesize() {
    var ext = $('#upload_bulk_data').val().split('.').pop().toLowerCase();
    var input = document.getElementById('upload_bulk_data');
    var file_size = parseInt(input.files[0].size);
    var fileSizeInMB = file_size / 1024;
    if ($.inArray(ext, ['zip']) == -1) {
        hideProcessingOverlay();
        //$('#err_file').show();
        /* $('#err_file').fadeIn();
         $('#err_file').fadeOut(4000);
         $("#err_file").html('Only zip file is allowed, please try again.');*/

        swal({
                title: 'Warning',
                text: 'Only zip file is allowed, please try again.',
                type: 'warning'
            },
            function() {
                location.reload();
            }
        );
        return false;
    } else if (file_size > 250000000) {
        hideProcessingOverlay();
        //$('#err_file').show();
        /*$('#err_file').fadeIn();
        $("#err_file").html('File size too large, please try again.');
        $('#err_file').fadeOut(40000);
        return false;*/

        swal({
                title: 'Warning',
                text: 'File size too large, please try again.',
                type: 'warning'
            },
            function() {
                location.reload();
            }
        );

        return false;
    } else {

        // $("#upload_zip_file").submit();
        var formdata = new FormData($("#upload_zip_file")[0]);



        $.ajax({
            url: "{{url('/')}}/{{$maker_panel_slug}}" + '/product_old_images/uploadZip',
            type: "POST",
            data: formdata,
            contentType: false,
            processData: false,
            dataType: 'json',
            xhr: function() {
              var xhr = new window.XMLHttpRequest();
              xhr.upload.addEventListener('progress',function(evt){
                if (evt.lengthComputable) {
                  var percentComplete = parseInt((evt.loaded / evt.total) * 100);
                  if(percentComplete < 100)
                  {
                    $.LoadingOverlay("text",percentComplete+"% uploaded...");

                  }
                  else
                  {
                    $.LoadingOverlay("text","Please wait while we are processing.");
                  }


                }
              },false);

              return xhr;
            },
            beforeSend: function() {

            },
            success: function(data) {
                $.LoadingOverlay("hide",{});

                if ('success' == data.status) {
                    $('#upload_zip_file')[0].reset();

                    swal({
                            title: "Success",
                            text: data.message,
                            type: "success"
                        },
                        function() {
                            location.reload();
                        }
                    );
                } else {
                    var status = data.status;
                    status = status.charAt(0).toUpperCase() + status.slice(1);

                    // swal(status,data.message,data.status);

                    swal({
                            title: status,
                            text: data.message,
                            type: data.status
                        },
                        function() {
                            location.reload();
                        }
                    );
                }
            }


        });

        // location.reload();
    }
}
$("document").ready(function() {
    setTimeout(function() {
        $("div.alert").remove();
    }, 50000); // 5 secs
});
</script>
@stop