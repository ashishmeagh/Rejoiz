@extends('maker.layout.master')                
@section('main_content')

<style type="text/css">
  .img-shop-tbl.nw-in-shp.fullwodth-int{display: block; width: 100%;}
  /*.fullwodth-int .dropify-wrapper{height: 120px;}*/
  .spaceleft-form{margin-left: 20px;}
  .table td, .table th{vertical-align: middle;}
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
         <li><a href="{{$module_url_path or ''}}">Manage Catelog Images</a></li>
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
                              'id'=>'catlog-images-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>
            
               <div  class="tab-content">

                  <div class="form-group row">
                    <label class="col-2 col-form-label" for="catlog_image">Catalog Name <i class="red">*</i></label>
                    <div class="col-10">
                       <select name="catalog_id" id="catalog_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select catalog name.">
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


                <div class="table-responsive">
                    <table class="table" id="style_and_diemension_tbl">
                        <thead>
                            <tr>
                                 <th>Image</th>
                                 <th>Sequence</th>
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
                                        data-default-file=""
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        
                                        data-errors-position="outside"
                                        data-parsley-required="true"
                                        data-parsley-required-message="Please upload image."
                                        >           
                                    </div>
                                 </td>
                                 <td width="40%">
                                  <div class="spaceleft-form">
                                    <input type="text" class="form-control" placeholder="Sequence" name="sequence[]" data-parsley-required="true"  data-parsley-required-message="Please enter sequence.">
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

                 {{--  <div class="form-group row">
                    <label class="col-md-2 col-form-label">Status</label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div>  --}}

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
                            window.location = '{{$module_url_path or ''}}';
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

    function toggle_status()
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
                          >           
                      </div>
                    </td>
                    
                    <td width="40%">
                      <div class="spaceleft-form">
                        <input type="text" class="form-control" placeholder="Sequence" name="sequence[]" data-parsley-required="true">
                        </div>
                      </td>
                   
                    <td>

                    <div class="spaceleft-form">
                      <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                      </div>
                    </td>

             </tr>`;

     
     $(this).parent().parent().parent().append(newRow);

     // $('#style-frm').parsley().refresh();

      init_dropify();       
  });

  function removeRows(ref){   
     $(ref).parent().parent().parent().remove();
  };

  function init_dropify()
  {
    $('.dropify').each(function(index,elem){
        
        var tmpDropify = $(elem).data('dropify');

        if(tmpDropify == undefined)
        {             
          $(elem).dropify();              
        } 
    });
  }  
  
</script>
@stop