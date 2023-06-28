@extends('admin.layout.master')    
@section('main_content')

<style type="text/css">
  .inline-input input{display: inline-block;width: 29%;}
  .table>thead>tr>th{vertical-align: middle;}
  .img-table .dropify-wrapper{height: 110px;font-size: 9px;width: 110px;line-height: 16px;}
  .inpt-in {
    display: inline-block;
    width: 32%;
}
.inline-input.width-ful{
  width: 100%;
}
.inline-input.width-ful input{width: 100%;}
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
                      <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                      <li><a href="{{$module_url_path or url('/')}}">{{$module_title or ''}}</a></li>
                      <li><a href="{{$module_url_path.'/create'}}"> Create {{$module_title or ''}}</a></li>
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
                            <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
                            <div class="box-tool">
                            </div>
                        </div>
                        <form class="form-horizontal" id="add-product-style-frm" enctype="multipart/form-data" method="POST">
                          {{ csrf_field() }}   
                          <input type="hidden" name="product_id" value="{{ $product_arr['id'] or 0}}">
                          <div class="table-responsive">
                            <table class="table table-borderless" id="tblImage">
                              <thead>
                                <tr>
                                  <th scope="col">Image</th>
                                  <th scope="col">
                                    <select name="optionName" id="" class="form-control" data-parsley-required="true">
                                        <option value="">Options</option>
                                        <option value="0" @if(isset($product_details_arr[0]['option_type']) && $product_details_arr[0]['option_type']==0) selected="selected" @endif>Color</option>
                                        <option value="1" @if(isset($product_details_arr[0]['option_type']) && $product_details_arr[0]['option_type']==1) selected="selected" @endif>Scent</option>
                                        <option value="2" @if(isset($product_details_arr[0]['option_type']) && $product_details_arr[0]['option_type']==2) selected="selected" @endif>Size</option>
                                        <option value="3" @if(isset($product_details_arr[0]['option_type']) && $product_details_arr[0]['option_type']==3) selected="selected" @endif>Material</option>
                                    </select>
                                  </th>
                                  <th scope="col">SKU</th>
                                  <th scope="col">Weight</th>
                                  <th scope="col">Dimensions (length, width, height)</th>
                                  <th>&nbsp;</th>
                                </tr>
                              </thead>
                              <tbody>
                                @if(isset($product_details_arr) && count($product_details_arr)>0)
                                @foreach($product_details_arr as $key =>$product_detail)
                                  <tr class="td-row">
                                    <td scope="row" width="200px">
                                      <input type="hidden" name="db_product_image[{{ $product_detail['id'] }}]" value="{{$product_detail['image'] or ''}}">
                                    {{--   {{dd($product_detail[])}} --}}
                                      <input type="hidden" name="db_product_thumb_image[{{ $product_detail['id'] }}]" value="{{$product_detail['image_thumb'] or ''}}">
{{--                                       {{ dd($product_detail['id']) }}
 --}}                                      <div class="img-table">
                                          <input type="file" 
                                                name="old_product_image[{{ $product_detail['id'] }}]" 
                                                id="product_image" 
                                                class="dropify" 
                                                data-default-file="{{ url('/storage/app/'.$product_detail['image'])}}"
                                                data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                data-max-file-size="2M" 
                                                data-errors-position="outside"/>
                                        </div>
                                    </td>
                                    <td>
                                      <input type="text" name="old_option[{{ $product_detail['id'] }}]" class="form-control" data-parsley-required="true" value="{{ $product_detail['option'] or '' }}" placeholder="Options">
                                    </td>
                                   {{--  @php $pro_id = $product_detail['product_id']; @endphp --}}
                                    @php
                                    $pro_id = isset($product_detail['product_id'])?$product_detail['product_id']:'';
                                    @endphp

{{--                                     {{dd("/admin/products/does_exists/sku_no/".$pro_id)}}
 --}}                                    <td><input type="text" name="old_sku[{{ $product_detail['id'] }}]" class="form-control check-dup" data-parsley-required="true" value="{{ $product_detail['sku'] or '' }}" placeholder="SKU" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/admin/products/does_exists_edit/sku_no/'.$pro_id) }}"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists"></td>
                                    <td><input type="text" name="old_weight[{{ $product_detail['id'] }}]" class="form-control" data-parsley-required="true" value="{{ $product_detail['weight'] or '' }}" placeholder="Weight"></td>
                                    <td>
                                       <div class="inline-input">
                                         <input type="text" name="old_length[{{ $product_detail['id'] }}]" class="form-control" data-parsley-required="true" value="{{ $product_detail['length'] or '' }}" placeholder="Length">
                                                                                
                                        <input type="text" name="old_width[{{ $product_detail['id'] }}]" class="form-control" data-parsley-required="true" value="{{ $product_detail['width'] or '' }}" placeholder="Width">
                                        <input type="text" name="old_height[{{ $product_detail['id'] }}]" class="form-control" data-parsley-required="true" value="{{ $product_detail['height'] or '' }}" placeholder="Height">

                                      </div> 
                                    </td>
                                    <td>
                                      @if($key==0)
                                      <a href="javascript:void(0)" id="addMore" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a>
                                      @else
                                      <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                                      @endif
                                    </td>
                                  </tr>                                
                                @endforeach
                                @else
                                  <tr class="td-row">
                                    <td scope="row" width="200px">
                                      <div class="img-table">
                                          <input type="file" 
                                                name="new_product_image[]" 
                                                id="product_image" 
                                                class="dropify" 
                                                data-default-file="" 
                                                data-parsley-required="true" 
                                                data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                data-max-file-size="2M" 
                                                data-errors-position="outside"/>                           
                                        </div>
                                    </td>
                                    <td>
                                      <input type="text" name="new_option[]" class="form-control" data-parsley-required="true" placeholder="Options">
                                    </td>
                                    <td><input type="text" name="new_sku[]" class="form-control check-dup" data-parsley-required="true" placeholder="SKU" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/admin/products/does_exists/sku_no') }}"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists"></td>
                                    <td><input type="text" name="new_weight[]" class="form-control" data-parsley-required="true" placeholder="Weight"></td>
                                    <td>
                                       <div class="inline-input width-ful">
                                         <div class="inpt-in">
                                        <input type="text" name="new_length[]" class="form-control" data-parsley-required="true" placeholder="Length">
                                      </div>
                                       <div class="inpt-in">
                                        <input type="text" name="new_width[]" class="form-control" data-parsley-required="true" placeholder="Width">
                                      </div>
                                       <div class="inpt-in">
                                        <input type="text" name="new_height[]" class="form-control" data-parsley-required="true" placeholder="Height">
                                      </div>
                                      </div> 
                                    </td>
                                    <td>
                                      <a href="javascript:void(0)" id="addMore" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a>
                                    </td>
                                  </tr>
                                @endif
                              </tbody>
                            </table>
                          </div>
                          <div class="input-group row">
                              <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                              
                              <a href="{{ $module_url_path.'/edit/'}}{{base64_encode($product_arr['id'])}}" class="btn btn-success waves-effect waves-light m-r-10">Back<i class="fa fa-arrow-left"></i></a>

                              <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="Save" id="saveAndProceed">Save And Proceed</button>
                              
                              @if(isset($product_details_arr) && count($product_details_arr)>0)
                              <a href="{{ $module_url_path.'/additional_images/'}}{{base64_encode($product_arr['id'])}}" class="btn btn-success waves-effect waves-light m-r-10">Next <i class="fa fa-arrow-right"></i></a>
                              @endif

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
  
  //add more rows to the table
  $('#addMore').click(function()
  {  var product_id = "{{isset($pro_id)?$pro_id:''}}";
     if(product_id!='')
     {
      var url = "{{url('/admin/products/does_exists_edit/sku_no/')}}"+'/'+product_id;
     }
     else
     {
      var url = "{{url('/admin/products/does_exists/sku_no/')}}";
     }
      var newRow = '';
      newRow += `<tr class="td-row">
          <td scope="row" width="200px">
            <div class="img-table">
                <input type="file" 
                      name="new_product_image[]"                      
                      class="dropify" 
                      data-default-file="" 
                      data-parsley-required="true" 
                      data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                      data-max-file-size="2M" 
                      data-errors-position="outside"/>                           
              </div>
          </td>
          <td>
            <input type="text" name="new_option[]" class="form-control" data-parsley-required="true" placeholder="Options">
          </td>
          <td><input type="text" name="new_sku[]" class="form-control check-dup" data-parsley-required="true" placeholder="SKU" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="`+url+`"data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists"></td>
          <td><input type="text" name="new_weight[]" class="form-control" data-parsley-required="true" placeholder="Weight"></td>
          <td>
             <div class="inline-input width-ful">
             <div class="inpt-in">
              <input type="text" name="new_length[]" class="form-control" data-parsley-required="true" placeholder="Length">
             </div>
             <div class="inpt-in">
             <input type="text" name="new_width[]" class="form-control" data-parsley-required="true" placeholder="Width">
             </div>
             <div class="inpt-in">
             <input type="text" name="new_height[]" class="form-control" data-parsley-required="true" placeholder="Height">
             </div>
              
              
              
            </div> 
          </td>
          <td>
            <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
          </td>
      </tr>`;

      $('tbody').append(newRow);

    // var trElem = $(this).closest("tr").clone();
     $('#add-product-style-frm').parsley().refresh();
     init_dropify();       
    // $(trElem).find("span.dropify-render > img").attr("src","");    
    // $(trElem).find("input:text").val("");
    // init_dropify();       
    
    // $("#tblImage").find("tbody").append(trElem);        
    // //append remove button
    // $('td a:not(:first)').replaceWith('<a href="javascript:void(0)" id="Remove" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>');   
  });

  
  function removeRows(ref){
     $(ref).closest('.td-row').remove();
  };


  function init_dropify()
   {
        $('.dropify').each(function(index,elem){
            
            var tmpDropify = $(elem).data('dropify');

            if(tmpDropify == undefined)
            {
              // $(elem).resetPreview();
              // $(elem).data('dropify');
              $(elem).dropify();              
            } 
        });
   }

   $(document).ready(function(){
     
    
     function checkIfArrayIsUnique(myArray)
     {
       return myArray.length === new Set(myArray).size;
     }

    $('#add-product-style-frm').parsley();
       $('#saveAndProceed').click(function(){
        

        if($('#add-product-style-frm').parsley().validate()==false) return;
        if($('.parsley-error').length>0) return;
       	
        var c_dup =[];
        $(".check-dup").each(function() {
          c_dup.push($(this).val());
        });
        var check = checkIfArrayIsUnique(c_dup);
        if(check ==false)
        {
          swal('Info',"SKU numbers must be unique",'info');
          return false;
        }
     
        $.ajax({
          url: module_url_path+'/storeProductStyleAndDiemensions',
          type:"POST",
          data: new FormData($("#add-product-style-frm")[0]),
          contentType:false,
          processData:false,
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                 $('#add-product-style-frm')[0].reset();

                 swal({
                         title: 'Success',
                         text: response.description,
                         type: 'success',
                         confirmButtonText: "OK",
                         closeOnConfirm: false
                      },
                     function(isConfirm,tmp)
                     {
                       if(isConfirm==true)
                       {
                          window.location = response.next_url;
                       }
                     });
              }
              else
              {                
                 swal('Error',response.description,'error');
              }  
          }          
        });
      });
   });
</script> 
@endsection