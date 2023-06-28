@extends('maker.layout.master')                
@section('main_content')

<style type="text/css">
  .img-shop-tbl.nw-in-shp.fullwodth-int{display: block; width: 100%;}
  /*.fullwodth-int .dropify-wrapper{height: 120px;}*/
  .spaceleft-form{margin-left: 20px;}
  .table td, .table th{vertical-align: middle;}
.img-shop-tbl.nw-in-shp.fullwodth-int .parsley-errors-list {
    margin-top: -11px;
    margin-bottom: 9px;
}

</style>


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>

   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
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
                              'id'=>'upload-pdf-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>


              <div  class="tab-content">

                  <div class="form-group row">
                    <label class="col-2 col-form-label" for="name">Name<i class="red">*</i></label>
                    <div class="col-10">

                       <input type="text" class="form-control" name="catalog_name" id="catalog_name" data-parsley-required="true" data-parsley-required-message="Please enter catalog name." placeholder="Name">
                      
                    {{-- <div id ="err_container"></div> --}}
                   </div>
                  </div>


              </div>


              <div  class="tab-content">

                  <div class="form-group row">
                    
                    <label class="col-2 col-form-label" for="catlog_image">Pdf<i class="red">*</i></label>
                    <div class="col-10">
                      <div class="indext-jtsd space-index">
                      <div class="nt-rxlcs">
                       <input type="file" class="idx-imprt" name="upload_pdf" id="upload_pdf" data-parsley-required="true" data-parsley-required-message="Please enter catalog pdf." data-parsley-trigger="change" data-default-file data-allowed-file-extensions="pdf">
                       <div class="red noteuploads">Note: Please upload only Pdf file.</div>
                       </div>

                    <div id ="err_container"></div>
                  </div>
                   </div>
                   
                  </div>


              </div>


               <div  class="tab-content">

                  <div class="form-group row">
                    <label class="col-2 col-form-label" for="catlog_image">Cover Image<i class="red">*</i></label>
                    <div class="col-10">

                        <input type="file" 
                            class="form-control dropify" 
                            name="cover_image"
                            id="cover_image"
                            data-default-file=""
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-max-file-size="2M" 
                            data-errors-position="outside"
                            data-parsley-errors-container="#err_cover_img"
                            data-parsley-required="true" 
                            data-parsley-required-message="Please enter cover image."
                            > 

                    <div id ="err_cover_img"></div>
                   </div>
                  </div>


              </div>

          <br>

               <div class="form-group">
                  <div class="col-md-6 text-left">
                  <a class="btn btn-inverse waves-effect waves-light" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>

                  </div>
                  <div class="col-md-6 text-right">
                    <button class="btn btn-success waves-effect waves-light m-r-10" type="button" name="Save" id="btn_add" value="true"> Save</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">

  const module_url_path = "{{ $module_url_path or ''}}";
   
  $(document).ready(function(){

      $('#upload-pdf-form').parsley();

        $('#btn_add').click(function(){
         
          if($('#upload-pdf-form').parsley().validate()==false) return;

          var formdata = new FormData($("#upload-pdf-form")[0]);
        
          $.ajax({
                    url: module_url_path+'/save_pdf',
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
                            $('#upload-pdf-form')[0].reset();

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
                                      //window.location = '{{$module_url_path or ''}}';
                                      window.location.reload();
                                    }
                                });
                        }
                        else
                        {
                            var status = data.status;
                            status = status.charAt(0).toUpperCase() + status.slice(1);
                            swal(status,data.description,data.status);
                        }  
                    }
            
          });   
        
      });

   });




</script>
@stop