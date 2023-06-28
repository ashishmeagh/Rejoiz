@extends('maker.layout.master')                
@section('main_content')
<!-- Page Content -->
<style type="text/css">
  .img-shop-tbl.nw-in-shp.fullwodth-int{display: block; width: 100%;}
  /*.fullwodth-int .dropify-wrapper{height: 120px;}*/
</style>
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path.'/view/'.base64_encode(isset($catlog_arr['catalog_id'])?$catlog_arr['catalog_id']:'')}}">View Catalog</a></li>
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

                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="catlog_image">Catalog Name <i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                       <select name="catalog_id" id="catalog_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select catalog name">
                      <option value="">Select Catalog</option>

                     @if(isset($catlog_name_arr) && count($catlog_name_arr)>0)
                       @foreach($catlog_name_arr as $key=>$value)
                         <option value="{{$value['id'] or 0}}" @if(isset($catlog_arr['get_catalog_data']) && $catlog_arr['get_catalog_data']['id'] == $value['id']) selected="true" @endif>{{$value['catalog_name'] or ''}}</option>
                       @endforeach
                     @endif
                    </select>
                   
                   </div>
                  </div>


                  <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="sequence">Page Sequence<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                    <input type="text" name="sequence" id="sequence" data-parsley-required="true" data-parsley-required-message="Please enter page sequence" class="form-control" placeholder="Page Sequence" value="{{$catlog_arr['sequence'] or ''}}" data-parsley-pattern="^[0-9]+$" data-parsley-pattern-message="Only numbers are allowed">
                  
                 </div>
                  </div>

                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="">Page Type<i class="red">*</i></label>

                    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                      <div class="radio-btns">
                        <div class="radio-btn">
                        <input type="radio" class="form-check-input" id="product_img" name="page_type_radio" value="product_images" data-parsley-required="true" data-parsley-required-message="Please select page type" @if(isset($catlog_arr['page_type']) && $catlog_arr['page_type'] == 'product_images') checked="true" @endif>  
                        <label for="product_img">Product Images</label>
                        <div class="check"></div>
                        </div>
                        <div class="radio-btn">
                        <input type="radio" class="form-check-input" id="cover_img" name="page_type_radio" value="single_image" data-parsley-required="true" data-parsley-required-message="Please select page type" @if(isset($catlog_arr['page_type']) && $catlog_arr['page_type'] == 'single_image') checked="true" @endif> 
                          <label for="cover_img">Main page image</label>
                          <div class="check"></div>
                        </div>
                        <div class="clearfix"></div>
                     </div>
                   </div>

                  </div>
   

        @if(isset($catlog_arr['page_type']) && $catlog_arr['page_type'] =='product_images')
          
              <div class="form-group row" id="product_images">

                <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="">Product Images<i class="red">*</i></label>
                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                <div class="table-responsive">

               
                    <table class="table" id="style_and_diemension_tbl">
                        <thead>
                            <tr>
                                 <th>Image</th>
                                 <th>SKU</th>
                                 <th>&nbsp;</th>
                            </tr>
                           </thead>

                @if(isset($catlog_arr['get_catalog_image_data']) && count($catlog_arr['get_catalog_image_data'])>0)


                    @foreach($catlog_arr['get_catalog_image_data'] as $key=>$catalog_image_data)    
                     
                           <tbody>
                            <input type="hidden" name="product_images_count" id="product_images_count" value="{{count($catlog_arr['get_catalog_image_data'])}}">
                              <tr>
                                 <td width="180px">
                                    <div class="img-shop-tbl nw-in-shp fullwodth-int">
                                       <input type="file" 
                                        class="form-control dropify" 
                                        name="catalog_image[]"
                                        id="catalog_image"
                                        data-default-file="{{ url('/storage/app/'.$catalog_image_data['image'])}}"
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-errors-position="outside">           
                                    </div>
                                 </td>
                                 <td width="40%">
                                  <div class="spaceleft-form">
                                    
                                    <input type="text" class="form-control" placeholder="SKU" name="sku[]" id="sku" value="{{isset($catalog_image_data['sku'])?$catalog_image_data['sku']:''}}">
                                    </div>

                                    <input type="hidden" name="image_pk_id[]" id="image_pk_id" value="{{isset($catalog_image_data['id'])?$catalog_image_data['id']:0}}">
                                 </td>
                                
                                   <input type="hidden" name="old_product_img[]" id="old_product_img" value="{{isset($catalog_image_data['image'])?$catalog_image_data['image']:''}}">
                                 <td>
                                    
                                    @if($key == 0)
                     
                                    <button type="button" id="addMore" class="btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>
                                     
                                  @else
                                       <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip" data-catalog-img-id="{{isset($catalog_image_data['id'])?$catalog_image_data['id']:0}}" onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                                   @endif

                                 </td>
                              </tr>
                              
                           </tbody>

                    @endforeach

                @endif  

                  </table>
             
                <div class="clearfix"></div>
                       
                </div>
                </div>

              </div>
        @else

              <div class="form-group row" id="product_images" style="display: none;">

                <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="">Product Images<i class="red">*</i></label>
               <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
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
                                 <td width="180px">
                                    <div class="img-shop-tbl nw-in-shp fullwodth-int">
                                       <input type="file" 
                                        class="form-control dropify" 
                                        name="catalog_image[]"
                                        id="catalog_image"
                                        data-default-file=""
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG"  
                                        data-errors-position="outside"
                                        data-parsley-errors-container="#err_product_img">           
                                    </div>
                                    <div id ="err_product_img"></div> 
                                 </td>
                                 <td width="40%">
                                  <div class="spaceleft-form">
                                    <input type="text" class="form-control" placeholder="SKU" name="sku[]" id="sku" value="">
                                    </div>
                                 </td>
                                
                                <input type="hidden" name="old_product_img[]" id="old_product_img" value="">
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
              
        @endif  

       

          @if(isset($catlog_arr['page_type']) && $catlog_arr['page_type'] =='single_image')
            
               <div class="form-group row" id="single_image">

                 <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="">Single Image<i class="red">*</i></label>

                  <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                       <div class="img-shop-tbl nw-in-shp fullwodth-int">
                           <input type="file" 
                            class="form-control dropify" 
                            name="cover_image"
                            id="cover_image"
                            data-default-file="{{ url('/storage/app/'.$catlog_arr['get_catalog_image_data'][0]['image'])}}"
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-errors-position="outside"
                            data-parsley-errors-container="#err_container"> 

                        </div>
                      <div id ="err_container"></div>
                            
                  </div>
           
               </div>

           @else    

               <div class="form-group row" id="single_image" style="display: none;">

                 <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="">Single Image<i class="red">*</i></label>

                 <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                       <div class="img-shop-tbl nw-in-shp fullwodth-int">
                           <input type="file" 
                            class="form-control dropify" 
                            name="cover_image"
                            id="cover_image"
                            data-default-file=""
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-errors-position="outside"
                            data-parsley-errors-container="#err_container"
                            > 

                        </div>
                      <div id ="err_container"></div>
                            
                  </div>
           
               </div>
          @endif



            <input type="hidden" name="old_cover_page_img" id="old_cover_page_img" value="{{$catlog_arr['get_catalog_image_data'][0]['image'] or ''}}">     
        
           <div class="form-group row">
              <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label">Status</label>
                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                    @php

                      if(isset($catlog_arr['is_active'])&& $catlog_arr['is_active']!='')
                      {
                        $status = $catlog_arr['is_active'];
                      } 
                      else
                      {
                        $status = '';
                      }

                    @endphp
                    <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " @if($status =='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggleStatus();" />
                </div>

            </div>

          </div>
        <br>

        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($catlog_arr['id'])?base64_encode($catlog_arr['id']) :0}}">

        <input type="hidden" name="enc_catlog_id" id="enc_catlog_id" value="{{isset($catlog_arr['id'])?base64_encode($catlog_arr['catalog_id']) :0}}">

        <div class="form-group row">
        <div class="col-sm-12 common_back_save_btn">
          <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path.'/view/'.base64_encode(isset($catlog_arr['catalog_id'])?$catlog_arr['catalog_id']:'')}}"><i class="fa fa-arrow-left"></i> Back</a>

            <button class="btn btn-success waves-effect waves-light m-r-10" type="button" name="Save" id="btn_update" value="true"> Update</button>

        </div>
      </div>

       {!! Form::close() !!}

        </div>
      </div>
    </div>
   </div>
</div>




<script type="text/javascript">
  const module_url_path = "{{ $module_url_path or '' }}";
  var imageCount = '';
  var product_images_count = $('#product_images_count').val();

  if(product_images_count!='' && product_images_count >0 && product_images_count!=undefined) 
  {
     imageCount = product_images_count;
  }
  else
  {
     imageCount = 0;
  }

  var catalog_id = "{{isset($catlog_arr['catalog_id'])?$catlog_arr['catalog_id']:''}}";

  $(document).ready(function()
  {
      $('#validation-form').parsley();


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
                            //window.location = '{{$module_url_path or ''}}';
                            window.location = module_url_path+'/view/'+btoa(catalog_id);
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
      if($('#validation-form').parsley().validate()==false) return;
     
      //imageCount = imageCount+1;

      if(imageCount > 3)
      {
        $('#addMore').attr('disabled',true);
        return;
      }
   

      var newRow = '';
      newRow += `<tr>
                    <td width="180px">
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
                    
                    <td width="40%">
                      <div class="spaceleft-form">
                        <input type="text" class="form-control" placeholder="SKU" name="sku[]" data-parsley-required="true" data-parsley-required-message="Please enter sku no">
                        </div>
                      </td>
                   
                    <td>

                    <div class="spaceleft-form">
                      <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip" id="" onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                      </div>
                    </td>

             </tr>`;

     //console.log($(this).parent().parent().parent());
     //$(this).parent().parent().parent().append(newRow);
     $("#style_and_diemension_tbl tr:last").after(newRow);

      initDropify();       
  }); 



  function removeRows(ref)
  {   
    /*console.log(imageCount,$(ref).parent().parent().val(),$(ref).parent().val());
    return false;*/
      /*if row is empty then no need to show the confirmation box.*/
    var row =  $(ref).closest('tr').find('input[type=text]').val();
  
    if(row!="")
    { 
       swal({
         title: "Need Confirmation",
         text: "Are you sure? Do you want to delete this product from catalog.",
         type: "warning",
         showCancelButton: true,
         confirmButtonClass: "btn-danger",
         confirmButtonText: "OK",
         closeOnConfirm: false
       },
       function(isConfirm){
    
        if(isConfirm==true)
        {
              $(ref).parent().parent().parent().remove();
            
              var id = $(ref).attr('data-catalog-img-id');

              id = btoa(id);

              $.ajax({
                    url: module_url_path+'/delete_row',
                    type:"GET",
                    data: {id:id},
                   
                    dataType:'json',
                    success:function(data)
                    {
                        if('success' == data.status)
                        {
                            swal({
                              title:'Success',
                              type: 'success',
                              text: "product has been deleted from catalog.",
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                            },
                            function(isConfirm,tmp)
                            {
                                if(isConfirm==true)
                                {
                                  location.reload();
                                }
                            });
                        }

                        else if('error' == data.status)
                        {
                           swal('error','Something went wrong,please try again.'); 
                        }  

                        else
                        {
                          $(ref).parent().parent().parent().remove();
                          swal({
                              title:'Success',
                              type: 'success',
                              text: "product has been deleted from catalog.",
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                            },
                            function(isConfirm,tmp)
                            {
                                if(isConfirm==true)
                                {
                                  location.reload();
                                }
                            });
                        }
                    }
              
              });  
           }

        });
    }

    else
    {
       $(ref).parent().parent().parent().remove();
    }   

  }


  
  function initDropify()
  {
      $('.dropify').each(function(index,elem)
      {
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
         /*-------------product img--------------------------------*/
         $('#product_images').show();
         $("#catalog_image").attr('data-parsley-required','true');
         $("#catalog_image").attr("data-parsley-required-message","Please upload catalog image");
 
         $("#sku").attr('data-parsley-required','true');
         $("#sku").attr("data-parsley-required-message","Please enter sku no");

         /*------------------------------------------------------------------*/

          /*----------------cover img------------------------------*/
         $('#cover_image').removeAttr('data-parsley-required','true');
         $('#cover_image').removeAttr("data-parsley-required-message","Please upload single image");
         
         //empty the value

         $('#cover_image').empty();
         $('#old_cover_page_img').empty();
     
         $('#single_image').hide();

         /*--------------------------------------------------------------------------------*/
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

          //empty the value

          $('#catalog_image').empty();
          $('#sku').empty();
          $('#old_product_img').empty();

          $('#product_images').hide();
      }

  }); 
  
  
</script>
@stop