@extends('admin.layout.master')    

@section('main_content')
{{-- {{dd($arr_makers)}}
 --}}   <!-- Page Content -->
  <div id="page-wrapper">
      <div class="container-fluid">
          <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                  <h4 class="page-title">{{$module_title or ''}}</h4> 
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                  <ol class="breadcrumb">
                      <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                       <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
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
                            <div class="box-tool"></div>
                        </div>
                            <form class="form-horizontal" id="update-product-frm" enctype="multipart/form-data" method="POST">
                            	{{ csrf_field() }}
                              <input type="hidden" name="product_id" value="{{ $product_arr['id'] or 0 }}">
                              <input type="hidden" name="old_product_image" value="{{ $product_arr['product_image'] or '' }}">


                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="col-md-4 control-label" for="category_id">Vendors<i class="red">*</i></label>
                                <div class="col-md-8">
                                  <select id="maker_id" name="maker_id" class="form-control" data-parsley-required="true">
                                    <option value="">Select Vendors</option>
                                    @if(isset($arr_makers) && count($arr_makers)>0)
                                    @foreach($arr_makers as $maker)
                                    <option @if(isset($maker->id) && $maker->id==$product_arr['user_id']) selected @endif value="{{$maker->id or 0}}"> {{ $maker->user_name?ucfirst($maker->user_name):'NA'}}</option>
                                    @endforeach
                                    @endif
                                  </select>
                                </div>                                  
                            </div>   

                            

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="category_id">Product Sub Category <i class="red">*</i></label>
                                <div class="col-md-8">
                                  <select id="second_category" multiple="" name="sub_category[]" class="form-control" data-parsley-required="true" >
                                   @if(isset($arr_all_sub_categories) && sizeof($arr_all_sub_categories)>0)
                                    @foreach($arr_all_sub_categories as $sub_cat)
                                      <option @if(in_array($sub_cat['id'],$selected_sub_cat)) selected="" @endif  value="{{$sub_cat['id']}}">{{$sub_cat['subcategory_name']}}</option>
                                    @endforeach
                                   @endif
                                  </select>
                                </div>                                  
                            </div>

                            <span class='red'>{{ $errors->first('product_name') }}</span>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="product_name">Product Name <i class="red">*</i></label>
                                <div class="col-md-8">
                                  <input type="text" name="product_name" id="product_name" class="form-control" data-parsley-required="true" data-parsley-maxlength="255" value="{{ $product_arr['product_name'] or ''}}" placeholder="Product Name">
                                </div>                                  
                            </div>
                            <span class='red'>{{ $errors->first('product_name') }}</span>
                             <div class="form-group">                                   
                                     <label class="col-md-4 control-label" for="product_image">Product Image<i class="red">*</i> </label>
                                      <div class="col-md-8">
                                        <input type="file" name="product_image" id="product_image" class="dropify" data-default-file="{{ url('/storage/app/'.$product_arr['product_image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"/>
                                      </div>
                            </div>
                          </div>
                          <div class="col-md-6">
                          <div class="form-group">
                                <label class="col-md-4 control-label" for="category_id">Product Category <i class="red">*</i></label>
                                <div class="col-md-8">
                                  <select id="category_id" onchange="get_second_category($(this));" name="category_id" class="form-control" data-parsley-required="true">
                                    <option value="">Select Category</option>
                                    @if(isset($categories_arr) && count($categories_arr)>0)
                                    @foreach($categories_arr as $category)
                                    <option @if($category['id']==$product_arr['category_id']) selected="selected" @endif value="{{$category['id'] or 0}}"> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                                    @endforeach
                                    @endif
                                  </select>
                                </div>                                  
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="case_qty">Case Quantity <i class="red">*</i></label>
                                <div class="col-md-8">
                                  <input type="text" name="case_qty" id="case_qty" class="form-control" data-parsley-required="true" data-parsley-type="number" data-parsley-min="1" value="{{ $product_arr['available_qty'] or ''}}" placeholder="Case Quantity">
                                </div>                                  
                            </div>
                            <span class='red'>{{ $errors->first('case_qty') }}</span>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="unit_wholesale_price">Unit Wholesale Price($)<i class="red">*</i></label>
                                <div class="col-md-8">
                                  <input type="text" name="unit_wholesale_price" id="unit_wholesale_price" class="form-control" data-parsley-required="true" data-parsley-min="1" data-parsley-type="number" value="{{ isset($product_arr['unit_wholsale_price'])?num_format($product_arr['unit_wholsale_price']) : ''}}"  placeholder="Unit Wholesale Price">
                                </div>                                  
                            </div>
                            <span class='red'>{{ $errors->first('unit_wholesale_price') }}</span>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="retail_price">Retail Price($)<i class="red">*</i></label>
                                <div class="col-md-8">
                                  <input type="text" name="retail_price" id="retail_price" class="form-control" data-parsley-required="true" data-parsley-type="number" data-parsley-min="1" value="{{isset($product_arr['retail_price']) ?num_format($product_arr['retail_price']) : ''}}" placeholder="Retail Price">
                                </div>                                  
                            </div>
                            <span class='red'>{{ $errors->first('retail_price') }}</span>

                            <div class="form-group">
                                <label class="col-md-4 control-label" for="description">Product Description <i class="red">*</i></label>
                                <div class="col-md-8">

                                  <input type="text"name="description" id="description" class="form-control" value="{{ $product_arr['description'] or ''}}" placeholder="Product Description">
                                </div>                                  
                            </div>
                            <span class='red'>{{ $errors->first('description') }}</span>
                      </div>
                      <div class="col-md-12">
                         <div class="input-group row">
                            <div class="col-md-12 text-center">
                            <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="update" id="updateAndProceed">Save And Proceed</button>                              
                            <a href="{{ $module_url_path.'/style_and_dimensions/'}}{{base64_encode($product_arr['id'])}}" class="btn btn-success waves-effect waves-light m-r-10">Next <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
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

    $('#update-product-frm').submit(function(){
       tinyMCE.triggerSave();
    });

   $(document).ready(function(){

       $('#updateAndProceed').click(function(){
        if($('#update-product-frm').parsley().validate()==false) return;
       	
        $.ajax({
          url: module_url_path+'/storeProduct',
          type:"POST",
          data: new FormData($("#update-product-frm")[0]),
          contentType:false,
          processData:false,
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                 $('#update-product-frm')[0].reset();

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

    //get second level categories
  function get_second_category(referance)
  {
    var category_id = referance.val();
    var url = '{{$module_url_path}}/get_second_level_categories/'+btoa(category_id);  

    $.ajax({
      url:url,          
      type:'GET',
      dataType:'json',
      success:function(response)
      {
        var second_categories= '';
        if(response.status=='SUCCESS')
        {
           if(typeof(response.second_level_category_arr) == "object")
           {
              /*second_categories += '<option value="">Select product sub category</option>';*/
              $(response.second_level_category_arr).each(function(index,second_category)
              {
                second_categories +='<option value="'+second_category.id+'">'+second_category.subcategory_name+'</option>';
              });
           }          
        }
        else
        {
          second_categories += '';          
        }
        
        $('#second_category').html(second_categories);
      }
    });
  }


</script> 
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
@endsection