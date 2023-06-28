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

.img-shop-tbl {width:250px !important;}

</style>


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-sm-12 top-bg-title">
      <h4 class="page-title">{{$page_title or ''}}</h4>
      <div class="right">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
        @if(Request::segment(4)!='')
         <li><a href="{{$module_url_path.'/view'}}/{{isset($catalog_id)?$catalog_id:''}}">All Pages</a></li>
        @endif 
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
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
                              'id'=>'catlog-images-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>
            
               <div  class="tab-content">

                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="catlog_image">Name <i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                       <select name="catalog_id" id="catalog_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select catalog name">
                      <option value="">Select Catalog</option>

                     @if(isset($catlog_name_arr) && count($catlog_name_arr)>0)
                       @foreach($catlog_name_arr as $key=>$value)
                         <option value="{{$value['id'] or 0}}">{{$value['catalog_name'] or ''}}</option>
                       @endforeach
                     @endif
                    </select>
                    <div id ="err_container"></div>
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="sequence">Page Sequence<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                      <input type="text" name="sequence" id="sequence" data-parsley-required="true" data-parsley-required-message="Please enter page sequence" class="form-control" placeholder="Page Sequence" data-parsley-pattern="^[0-9]+$" data-parsley-pattern-message="Only numbers are allowed">
                    
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="">Page Type<i class="red">*</i></label>

                    <div class="col-sm-12 col-md-12 col-lg-10 pr-0">
                      <div class="radio-btns">
                        <div class="radio-btn">
                          <input type="radio" class="form-check-input" id="product_img" name="page_type_radio" value="product_images" data-parsley-required="true" data-parsley-required-message="Please select page type" data-parsley-errors-container="#err_page_type"> 
                          <label for="product_img">Product Images </label>
                           <div class="check"></div>
                        </div>

                        <div class="radio-btn">
                          <input type="radio" class="form-check-input" id="cover_img" name="page_type_radio" value="single_image" data-parsley-required="true"  data-parsley-required-message="Please select page type" data-parsley-errors-container="#err_page_type"> 
                          <label for="cover_img">Main Page Image</label>
                           <div class="check"></div>
                        </div>
                        <div class="clearfix"> </div>
                      </div>

                        <div id ="err_page_type"></div>
                
                     
                   </div>

                  </div>

              <div class="form-group row" id="product_images" style="display: none;">

                <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="">Images<i class="red">*</i></label>
                <div class="col-sm-12 col-md-12 col-lg-10">
                <div class="table-responsive">
                    <table class="table" id="style_and_diemension_tbl">
                        <thead>
                            <tr>
                                 <th>Image</th>
                                 <th>SKU</th>
                                 <th>&nbsp;</th>
                            </tr>
                           </thead>

                           <tbody>
                              <tr>
                                 <td>
                                    <div class="img-shop-tbl nw-in-shp fullwodth-int">
                                       <input type="file" 
                                        class="form-control dropify" 
                                        name="catalog_image[]"
                                        id="catalog_image"
                                        data-default-file=""
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-errors-position="outside"
                                        data-parsley-errors-container="#err_product_img"
                                        >   
                                        <div id ="err_product_img"></div>     

                                    </div>
                                 </td>
                                 <td>
                                  <div class="spaceleft-form rw-catalogs-add-pages-sku">
                                    <input type="text" class="form-control" placeholder="SKU" name="sku[]" id="sku" >
                                    </div>
                                 </td>
                                
                                 <td>
                                    <button type="button" id="addMore" class="btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>
                                 </td>
                              </tr>
                              
                           </tbody>

                        </table>
                        <div class="clearfix"></div>
                       
                </div>
                </div>

              </div>

               <div class="form-group row" id="single_image" style="display: none;">

                 <label class="col-sm-2 col-md-2 col-lg-2 col-form-label" for="">Image<i class="red">*</i></label>

                  <div class="col-sm-10 col-md-4 col-lg-4">
                       <div class="img-shop-tbl nw-in-shp fullwodth-int">
                           <input type="file" 
                            class="form-control dropify" 
                            name="cover_image"
                            id="cover_image"
                            data-default-file=""
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-errors-position="outside"
                            data-parsley-errors-container="#err_cover_img"
                            > 
                        
                           <div id ="err_cover_img"></div>
                      
                        </div>
                            
                  </div>
           
               </div>


                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Status</label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggleStatus();" />
                      </div>    
                </div> 

            </div>
          <br>

               <div class="form-group col-sm-12">
                  {{-- <a class="btn btn-inverse waves-effect waves-light backbtn" href="{{$module_url_path.'/view'}}/{{isset($catalog_id)?$catalog_id:''}}"><i class="fa fa-arrow-left"></i> Back</a> --}}
                  <button class="btn btn-success waves-effect waves-light pull-right" type="button" name="Save" id="btn_add" value="true"> Save</button>


              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">

  const module_url_path = "{{ $module_url_path or ''}}";

  var imageCount = 0;
  
   $(document).ready(function(){

      $('#catlog-images-form').parsley();

        $('#btn_add').click(function(){
         
          if($('#catlog-images-form').parsley().validate()==false) return;

          var formdata = new FormData($("#catlog-images-form")[0]);
        
       
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
                   $('#catlog-images-form')[0].reset();

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

    function toggleStatus()
    {
        var status = $('#status').val();
        if(status=='1')
        {
          $('#status').val('1');
        }
        else
        {
          $('#status').val('0');
        }
    }



  //add more rows to the table
  $('#addMore').click(function()
  {  
      if($('#catlog-images-form').parsley().validate()==false) return;

      imageCount = imageCount+1;

      if(imageCount >3)
      {
        $('#addMore').attr('disabled',true);
        return;
      }


      var newRow = '';
      newRow += `<tr>
                    <td>
                       <div class="img-shop-tbl nw-in-shp fullwodth-int">
                         <input type="file" 
                          class="form-control dropify" 
                          name="catalog_image[]"
                          data-default-file=""
                          data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                          data-errors-position="outside"
                          data-parsley-required="true"
                          data-parsley-errors-container="#err_container_`+imageCount+`"
                          data-parsley-required-message="Please upload catalog image"
                          >           
                      </div>
                      <div id ="err_container_`+imageCount+`"></div>
                    </td>
                    
                    <td>
                      <div class="spaceleft-form">
                        <input type="text" class="form-control" placeholder="SKU" name="sku[]" data-parsley-required="true" data-parsley-required-message="Please enter sku no">
                        </div>
                      </td>
                   
                    <td>

                    <div class="spaceleft-form">
                      <button href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </button>
                      </div>
                    </td>

             </tr>`;

     
     $(this).parent().parent().parent().append(newRow);

      initDropify();       
  });



  function removeRows(ref){   
     $(ref).parent().parent().parent().remove();
      
      imageCount = imageCount-1;

      $('#addMore').attr('disabled',false);


  };

  function initDropify()
  {
    $('.dropify').each(function(index,elem){
        
        var tmpDropify = $(elem).data('dropify');

        if(tmpDropify == undefined)
        {             
          $(elem).dropify();              
        } 
    });
  }  
  

   $('input[type="radio"]').click(function()
   {
      if($('#product_img').is(':checked'))
      {
         $('#product_images').show();

         $("#catalog_image").attr('data-parsley-required','true');
         $("#catalog_image").attr("data-parsley-required-message","Please upload catalog image");
 
         $("#sku").attr('data-parsley-required','true');
         $("#sku").attr("data-parsley-required-message","Please enter sku no");


         $('#cover_image').removeAttr('data-parsley-required','true');
         $('#cover_image').removeAttr("data-parsley-required-message","Please upload single image");

         $('#single_image').hide();
      }

      if($('#cover_img').is(':checked'))
      {
         $('#single_image').show();

         $('#cover_image').attr('data-parsley-required','true');
         $('#cover_image').attr("data-parsley-required-message","Please upload single image");


         $("#catalog_image").removeAttr('data-parsley-required','true');
         $("#catalog_image").removeAttr("data-parsley-required-message","Please upload catalog image");
 
         $("#sku").removeAttr('data-parsley-required','true');
         $("#sku").removeAttr("data-parsley-required-message","Please enter sku no");

         $('#product_images').hide();
      }

  });

</script>
@stop