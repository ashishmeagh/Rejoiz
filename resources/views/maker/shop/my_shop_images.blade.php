@extends('maker.layout.master')  

@section('main_content')


<style type="text/css">
  .control-label {text-align:left !important;}
	.mainshop-box-left{
		float: left;
	}
	.mainshop-box-right{margin-left: 100px;}
	.mainshop-box{
		margin-bottom: 20px;
		color: #555;
		display: block;

	}
	.mainshop-box:hover{
		color: #444;
	}
	.titleshop-t {
	    font-size: 17px;
	    font-weight: 600;
	    margin-bottom: 5px;
	}
	.mainshop-box-left i{
		font-size: 70px;
	}
</style>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Vendor Company Images</h4> </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                  <li><a href="{{url(config('app.project.maker_panel_slug').'/dashboard')}}">Dashboard</a></li>
                  <li><a href="{{url(config('app.project.maker_panel_slug').'/company_settings')}}">Company Settings </a></li>
                    <li class="active">Vendor Company Images</li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    <form class="form-horizontal" id="validation-form">

                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-12 col-lg-3 control-label" for="cover_image">Company Cover Photo<i class="red">*</i> </label>
                            <div class="col-sm-12 col-md-12 col-lg-9">
                                <input type="file" name="cover_image" id="cover_img" class="dropify company_img" data-default-file="{{url('/storage/app/'.$cover_image)}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M"
                               data-errors-position="outside" {{-- data-parsley-required="true" data-parsley-errors-container="#image_error" --}}/>
                              <div id="image_error" style="color:red"></div>
                            </div>
 
                            <input type="hidden" name="old_cover_img" id="old_cover_img" value="{{$cover_image or ''}}">
                        </div>

                
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-12 col-lg-3 control-label" for="profile_image">Company Logo<i class="red">*</i> </label>
                            <div class="col-sm-12 col-md-12 col-lg-9">
                                <input type="file" name="profile_image" id="profile_img" class="dropify"  data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M"
                                data-default-file="{{url('/storage/app/'.$profile_image)}}"
                                data-errors-position="outside" {{-- data-parsley-required="true" data-parsley-errors-container="#company_logo_error" --}}/>

                                <div id="company_logo_error" style="color:red"></div>
                            </div>
                            <input type="hidden" name="old_profile_img" id="old_profile_img" value="{{$profile_image or ''}}">
                           
                        </div>
                        <div class="form-group">
                                <div class="col-md-12 common_back_save_btn">
                                   <a class="btn btn-inverse waves-effect waves-light" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
                                   <button type="button" class="btn btn-success waves-effect waves-light" id="btn_update" value="Save">Update</button>
                                </div>

                        </div>
                    </form>
                  </div>
                </div>
              </div>


<script type="text/javascript">
    const module_url_path = "{{ $module_url_path or ''}}";

    $(document).ready(function(){

        $('#btn_update').click(function(){

        var cover_photo  = $('#cover_img').attr('data-default-file');

        var company_logo = $('#profile_img').attr('data-default-file');
        
        var cover_img   = $('#cover_img').val();
        var pro_omg     = $('#profile_img').val();
       

  
       var is_cover_img_exist = cover_photo.split('/').pop();  
       var is_company_logo_exist = company_logo.split('/').pop();  
       


        if(cover_img == ''  && is_cover_img_exist == 'app')
        {
           swal('warning','Company cover photo field is required');
           return false;
        } 

        if(pro_omg == '' && is_company_logo_exist == 'app')
        {
           swal('warning','Company logo field is required');
           return false;
        }

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);

        
        $.ajax({
          url: module_url_path+'/save_images',
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
                          window.location = data.url;
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


   
</script>
@stop                    