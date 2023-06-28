@extends('maker.layout.master')                
@section('main_content')

<style>
  /*.table td input.form-control{
width: 80px;
  }*/
  .table td{
    position: relative;
  }
  .table .parsley-errors-list{
    position: absolute; left: 0px; bottom: 10px;
  }
  .table td .inline-blk-jst .parsley-errors-list{
    bottom: -30px;
  }
  .dropify-wrapper{overflow: visible;}
  .table  .img-shop-tbl.nw-in-shp .dropify-wrapper .parsley-errors-list{ bottom: -20px;}

/*/*#mceu_15 {
    /*display: none !important;
  position:absolute;
  top: -9999px;
  visibility:hidden;
}
textarea {
    display: block !important;
    width: 100%;
    padding: 10px;
}


#mceu_91 {
    display: none !important;
}
textarea {
    display: block !important;
    width: 100%;
    padding: 10px;
}


.form-group.prod-desc-div textarea.form-control{display: none !important;}*/
.remove-edtr #mceu_91 {
    display: none !important;
}
.remove-edtr .form-control {display:block !important;}
.form-group .input-to-textarea {
    height: 70px;
    width: 100%;
    border: 1px solid #ccc;
    box-shadow: none;
}
.form-control {height:auto !important;}

/*remove ckeditor*/
.removeeditor .mce-tinymce.mce-container.mce-panel{display: none !important;}
.removeeditor .form-control{
  display: block !important; height: 90px !important;
}
.ui-widget.ui-widget-content {z-index:0 !important; }

/*.form-control.custom-text-area
{
  height: 120px !important;
}*/


</style>
<!-- Page Content -->
@php
      $product_img_path = ""; 
      $image_name = "";
      $image_type = "category";
      $is_resize = 0; 
      $product_img_path = imagePath($image_name, $image_type, $is_resize);              
    @endphp
    <input type="hidden" name="hid_product_img_path" value="{{ $product_img_path }}" id="hid_product_img_path">
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">Products</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

@php

  $row = 1;

@endphp
<!-- .row -->
<div class="row">
   <div class="col-sm-12">
      <div class="white-box">
           @include('admin.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
               <div class="">
            <div class="white-box" id="edit-sec-tabs">
               <!-- Nav tabs -->
                
              <ul class="nav customtab nav-tabs">
                  <li role="presentation" class="nav-item">
                     <a href="#edit-home1" id="edit-item-sec-link" aria-controls="home" ><span class="visible-xs"><i class="ti-home"></i></span><span class="hidden-xs"> Item Details</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#edit-profile1" id="edit-style-sec-link" aria-controls="profile"><span class="visible-xs"><i class="ti-user"></i></span> <span class="hidden-xs">Style & Dimensions</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#edit-messages1" id="edit-additional-img-sec-link" aria-controls="messages"><span class="visible-xs"><i class="ti-email"></i></span> <span class="hidden-xs">Additional Images</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#edit-settings1" id="edit-category-sec-link" aria-controls="settings"><span class="visible-xs"><i class="ti-settings"></i></span> <span class="hidden-xs">Category & Shipping Details</span></a>
                  </li>
               </ul>              
               <!-- Tab panes -->
               <div class="tab-content">
                  <div role="tabpanel" id="edit-home1">
                     <form id="old-item-details-frm" class="old-item-details-frm">
                       {{ csrf_field() }}
                      <input type="hidden" name="product_id" id="edit_item_product_id" value="">
                      
                      <input type="hidden" name="is_click_on_storeProduct" id="is_click_on_storeProduct" value="0"> 
                        <input type="hidden" name="is_click_on_update_style_and_dimension" id="is_click_on_update_style_and_dimension" value="0">                
                        <input type="hidden" name="is_click_on_store_additional_images" id="is_click_on_store_additional_images" value="0"> 
                        <input type="hidden" name="is_click_on_update_product_dategory" id="is_click_on_update_product_dategory" value="0">  
                     <div>                      
                           <div class="form-group">
                              <label class="col-md-12">Product Image</label>
                              <div class="col-md-12" id="product-primary-img">
                                <input type="hidden" name="old_product_image" id="old_product_image">
                                <input type="hidden" name="old_product_image_thumb" id="old_product_image_thumb">
                                <input type="hidden" name="is_active" id="old_product_is_active">   
                                
                                {{--  <span class="text-danger" id="err_primary_product_img">{{ $errors->first('product_primary_image') }}</span> --}}
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        <div class="row">
                          <div class="col-md-12">
                           <div class="form-group">
                              <label class="col-md-12">Product Name<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <input type="text" class="form-control" placeholder="Enter Product Name" name="product_name" id="old_product_name" data-parsley-required="true" data-parsley-required-message="Please enter product name" data-parsley-trigger="change"  />
                              </div>
                              <div class="clearfix"></div>
                           </div>


                            <div class="form-group">
                              <label class="col-md-12">Brand<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <select name="brand" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter product brand">
                                   <option value="">-- Select Brand --</option>

                                   @if(isset($arr_brand) && count($arr_brand) > 0)
                                    @foreach($arr_brand as $brand)
                                      <option value="{{$brand['id'] or ''}}">{{$brand['brand_name'] or ''}}</option>
                                    @endforeach
                                   @endif
                                 </select>

                              </div>
                              <div class="clearfix"></div>
                           </div>


                           <div class="form-group">
                              <div class="form-check bd-example-indeterminate col-sm-12">
                                 <label class="custom-control custom-checkbox">
                                 <input type="checkbox" class="custom-control-input" 
                                 id="old_is_best_seller" name="is_best_seller" value="1">
                                 <span class="custom-control-indicator"></span>
                                 <span class="custom-control-description">Mark as a Best Seller</span>
                                 </label>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Case Quantity<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Case Quantity" name="case_qty" id="old_case_qty" data-parsley-required="true" data-parsley-required-message="Please enter case quantity" data-parsley-type="number"  data-parsley-type-message="Please enter valid case quantity"  data-parsley-min="1"/>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Restock Days<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Restock Days" name="restock_days" id="old_restock_days" data-parsley-required="true" data-parsley-required-message="Please enter restock days" data-parsley-type="number" />
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" 
                                       class="form-control" 
                                       placeholder="Enter Price"
                                       name="unit_wholsale_price" 
                                       id="old_unit_wholsale_price" 
                                       data-parsley-required="true" 
                                       data-parsley-required-message="Please enter price" 
                                       {{-- data-parsley-type="number"   --}}
                                       data-parsley-type-message="Please enter valid price" 
                                       data-parsley-lte="10" 

                                        @if($chk_quote_status == 0)
                                        min="1"
                                        @else 
                                        min="0"  
                                        data-parsley-notEqual=""
                                        data-parsley-trigger="keyup" 
                                        @endif                                     
                                       
                                        data-parsley-type="number"
                                       />
                                      <div id="err_unit_wholsale_price_edit" class="red"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              {{-- <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Retail Price($) <i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" 
                                       class="form-control" 
                                       placeholder="Enter Unit Retail Price" 
                                       name="retail_price" 
                                       id="old_retail_price" 
                                       data-parsley-required="true" 
                                       data-parsley-required-message="Please enter unit retail price" 
                                       data-parsley-type="number" 
                                       data-parsley-type-message="Please enter valid unit retail price"
                                       min="0"                                        
                                       data-parsley-trigger="keyup" 
                                       data-parsley-type="number"
                                       />
                                    <div id="err_unit_retail_price_edit" class="red"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div> --}}
                           </div>
                           <div class="form-group textare-new-frm">
                              <label class="col-md-12">Product Ingredients</label>
                              <div class="col-md-12">
                                   <input class="input-to-textarea form-control" height="55" row="4" type="text" id="old_product_ingrediants" name="product_ingrediants" placeholder="Enter Product Ingredients">

                                   <div id="err_product_ingrediants" class="red"></div>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                           <div class="form-group">
                              <label class="col-md-12">About This Product</label>
                              <div class="col-md-12 ">
                                 <textarea class="form-control " placeholder="Enter About This Product" rows="5" name="product_description" id="old_product_description"
                                 >

                                     @php

                                    $product_desc = isset($product_arr['description']) ? trim($product_arr['description']) : ""; 
                                    @endphp
                                    {{ strip_tags($product_desc) }}

                                 </textarea>
                                 <span id="err_product_desc_edit" class="red"></span>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        </div>

                        <div class="form-group">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-check">
                                    
                                 </div>
                              </div>
                             
                           </div>
                        </div>

                        <div class="form-group">
                        <div class="row">
                          
                           <div class="col-md-4 text-center">
                           
                           </div>
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="old_save_items_details">Save & Next</button>
                           </div>
                        </div>
                     </div>
                     </div>


                     <div class="clearfix"></div>
                      </form>
                  </div>
               
                  <div role="tabpanel" id="edit-profile1">
                     <form id="edit-style-frm" class="edit-style-frm">
                        {{ csrf_field() }}
                        <input type="hidden" name="product_id" id="edit-style_product_id" value=""> 

                        <input type="hidden" name="is_click_on_storeProduct" id="is_click_on_storeProduct" value="0"> 
                        <input type="hidden" name="is_click_on_update_style_and_dimension" id="is_click_on_update_style_and_dimension" value="0">                
                        <input type="hidden" name="is_click_on_store_additional_images" id="is_click_on_store_additional_images" value="0"> 
                        <input type="hidden" name="is_click_on_update_product_dategory" id="is_click_on_update_product_dategory" value="0">  
                    
                        <div class="col-sm-12 style-dimension" id="innerRowBox">
                      

                        @php

                       // dd($product_arr);

                        //dd(json_decode($product_arr['subcat_id']));
                        $arr_sub_category_details = json_decode($product_arr['subcat_id']);
                        $arr_third_sub_category_details = json_decode($product_arr['third_subcat_id']);
                        $arr_fourth_sub_category_details = json_decode($product_arr['fourth_subcat_id']);
                        //echo "<pre>";print_r($sub_cat_arr);die;
                        $row = 0;
                        $dropify_cnt = 0;
                        $details_cnt = 0;
                        if(isset($product_arr['product_details'])) { 
                          $details_cnt = count($product_arr['product_details']) - 1;
                          foreach($product_arr['product_details'] as $key => $details) { 
                            
                        @endphp
                        <div class="innerbox" id="innerbox_{{$row}}">
                                <div class="col-sm-12 text-right">
                                    <div class="form-group plus-btn">
                                      {{-- <button type="button" class="btn" onclick="show_multiple_dropify({{$row}})" id="show_multiple_dropify_{{$row}}"><span id="btn_label_{{$row}}">Add</span> Multiple Images</button> --}}

                                        
                                      <button type="button" id="btn_add_new_row_{{$row}}" class="btn" onclick="addNewRow({{$row}});" title="Add new SKU" @if($key == $details_cnt)  @else style="display: none" @endif><i class="fa fa-plus" ></i></button>
                                      

                                      @if(count($product_arr['product_details']) != 1)
                                      <button type="button" class="btn" onclick="deleteRow({{$row}},{{$details['id']}},{{$details['product_id']}},'{{$details['sku']}}');" title="Delete SKU"><i class="fa fa-trash"></i></button>
                                      @endif
                                    </div>
                                  </div>
                                  <div class="row">
                                      <div class="col-sm-3 left">
                                        <div class="col-sm-12">
                                          <label>Image<i class="red">*</i></label>
                                          <div class="img-shop-tbl nw-in-shp">
                                                     @php
                                                        $product_img_path = ""; 
                                                        $image_name = "";
                                                        $image_type = "category";
                                                        $is_resize = 0; 
                                                        $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                                     @endphp
                                                     @php
                                                      if (isset($details['image']) && $details['image'] == '') {
                                                        $default_img = url('/').'/assets/images/default_images/default.jpeg';
                                                      } else {                                       
                                                         $default_img = url('/').'/storage/app/'.$details['image'];
                                                      }
                                                     @endphp
                                                     <input type="file" 
                                                      class="form-control dropify" 
                                                      name="old_product_image[{{$details['id']}}]"
                                                      data-default-file="{{ $default_img }}"
                                                      data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                      data-max-file-size="2M" 
                                                      data-errors-position="outside"
                                                      {{-- data-parsley-required="true"
                                                      data-parsley-required-message="Please select image" --}} id="product_img_{{$row}}"
                                                      > 
                                                       <input type="hidden" name="db_product_image_original[{{$details['id']}}]" value="{{$details['image']}}">   
                                                       <input type="hidden" name="db_product_image[{{$details['id']}}]" value="{{url('/')}}/storage/app/product_image/product_img_thumb/{{$details['image_thumb']}}">       
                                                  </div>
                                        </div>
                                      </div>
                                      <div class="col-sm-9 right">
                                        <div class="row">
                                          <div class="col-sm-3 box">
                                          <div class="form-group">
                                            <label>SKU<i class="red">*</i></label>
                                          
                                            <input type="text" 
                                            class="form-control check-dup" 
                                            placeholder="SKU" 
                                            data-parsley-required="true" 
                                            data-parsley-required-message="Please enter sku no" name="old_sku[{{$details['id']}}]" data-parsley-trigger="change" 
                                            data-parsley-whitespace="trim" 
                                            data-parsley-whitespace-message="Whitespaces are not allowed"  
                                            data-parsley-remote="{{ url('/vendor/products/does_exists/sku_no') }}/{{$details['product_id']}}"
                                            data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'  
                                            data-parsley-remote-message="SKU no already exists"
                                            value="{{$details['sku']}}"
                                            id="sku_{{$row}}" @if($details['sku'] != "") readonly="true" @endif>
                                          </div>
                                        </div>
                                        <div class="col-sm-3 box">
                                          <div class="form-group">
                                                @php
                                                $Product_min_qty = "";
                                                if($details['product_min_qty'] != '0'){
                                                  $Product_min_qty = $details['product_min_qty'];
                                                }
                                                @endphp
                                              <div class="row">
                                               <label>Min Quantity<i class="red">*</i></label>
                                               <input type="text" 
                                               class="form-control" 
                                               placeholder="Min Quantity" 
                                               name="old_min_quantity[{{$details['id']}}]" 
                                               data-parsley-required="true" 
                                               data-parsley-required-message="Please enter minimum quantity" 
                                               data-parsley-type="digits" 
                                               data-parsley-type-message="Please enter valid minimum quantity" 
                                               min="1" 
                                               id="min_quantity_{{$row}}" 
                                               data-row="{{$row}}" 
                                               value="{{$Product_min_qty}}"
                                               onkeyup="checkMinQuantity(this);">

                                               <span class="red" id="quantity_error_msg_{{$row}}"></span>

                                              </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-3 box">
                                          <div class="form-group">
                                            @php
                                                $Product_inventory = "";
                                                if($details['product_min_qty'] != '0'){
                                                  $Product_inventory = $details['inventory_details']['quantity'];
                                                }
                                                @endphp
                                            <label>Inventory<i class="red">*</i></label>
                                            <input type="text" 
                                            class="form-control" 
                                            placeholder="Inventory" 
                                            name="old_quantity[{{$details['id']}}]" 
                                            data-parsley-required="true" 
                                            data-parsley-required-message="Please enter inventory" 
                                            data-parsley-type="digits" 
                                            data-parsley-type-message="Please enter valid inventory" 
                                            id="quantity_{{$row}}" 
                                            data-row="{{$row}}" 
                                            value="{{$Product_inventory}}"
                                            onkeyup="checkInventory(this);">

                                            <span class="red" id="inventory_error_msg_{{$row}}"></span>
                                          </div>
                                        </div>
                                        <div class="col-sm-3 box">
                                          <div class="form-group">
                                            
                                            <label>Description</label>
                                            <textarea  class="form-control" 
                                            placeholder="Enter Description" 
                                            rows="1" 
                                            name="old_sku_product_description[{{$details['id']}}]" 
                                            id="sku_product_description_{{$row}}">{{ isset($details['sku_product_description']) ? $details['sku_product_description'] : ""}}</textarea>

                                            <span id="err_product_desc_add_prod" class="red"></span>
                                          </div>
                                        </div>
                                         <div class="col-sm-3 box">
                                         <div class="form-group">
                                             <label>Color</label>
                                                 <input type="text" class="form-control" placeholder="Color" name="old_color[{{$details['id']}}]" id="color_{{$row}}" data-row="{{$row}}" value="{{ isset($details['color']) ? $details['color'] : ""}}">
                                                 <span class="red" id="color_error_msg_{{$row}}"></span>
                                         </div>
                                         </div>
                                         <div class="col-sm-3 box">
                                         <div class="form-group">
                                             <label>Category</label>
                                                 <select id="category_id{{$row}}" onchange="show_size({{$row}},0)" name="category_for_size" class="form-control" >
                                                 <option value="">Select Category</option>
                                                 @if(isset($category_arr) && count($category_arr)>0)
                                                   @foreach($category_arr as $category)

                                                     <option value="{{$category['id'] or 0}}" @if($category['id'] == $product_arr['category_id']) selected @endif> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                                                   @endforeach
                                                 @endif
                                               </select>
                                         </div>
                                         </div>
                                         
                                          @php
                                          $new_row = 0;
                                          if(isset($details['product_multiple_images']) && count($details['product_multiple_images']) > 0)
                                          {
                                            $dropify_cnt = count($details['product_multiple_images']);


                                          @endphp
                                        </div>
                                         <div class="row mulitple-img-box" id="mulitple-img-box_{{$row}}" row_id="{{$row}}" product_det_id="{{$details['id']}}">
                                          <div class="col-md-12"><label>Multiple Images</label></div>
                                          <script type="text/javascript">
                                            $("#show_multiple_dropify_"+{{$row}}).hide();
                                          </script>
                                          
                                        @php
                                          
                                          
                                            foreach($details['product_multiple_images'] as $key => $productMultipleImage){
                                        @endphp
                                       
                                            <div class="col-sm-12 col-md-6 col-lg-3  mult_img_div product_multiple_image_{{$row}}_{{$new_row}}">
                                              <div class="img-shop-tbl nw-in-shp">
                                                       @php
                                                          $product_img_path = ""; 
                                                          $image_name = "";
                                                          $image_type = "category";
                                                          $is_resize = 0; 
                                                          $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                                       @endphp

                                                       @php
                                                        if (isset($productMultipleImage['product_image']) && $productMultipleImage['product_image'] == '') {
                                                          $default_mul_img = url('/').'/storage/app/'.$productMultipleImage['product_image'];
                                                        } else {                                       
                                                           $default_mul_img = url('/').'/storage/app/'.$productMultipleImage['product_image'];
                                                        }
                                                        @endphp
                                                       <input type="file" 
                                                        class="form-control dropify" 
                                                        name="db_old_product_multiple_image[{{$details['id']}}][]"
                                                        data-default-file="{{ $default_mul_img }}"
                                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                        data-max-file-size="2M" 
                                                        data-errors-position="outside"
                                                        
                                                        id="product_multiple_image_{{$row}}_{{$new_row}}"
                                                        onchange="appendAnotherDropify({{$row}},{{$details['id']}})"
                                                        multiimgid="{{$productMultipleImage['id']}}"
                                                        >    
                                                           
                                                        <input type="hidden" name="db_old_product_multiple_image[{{$details['id']}}][]" value="{{$productMultipleImage['product_image']}}">   
                                                        <input type="hidden" name="multiple_image_id[{{$details['id']}}][]" value="{{$productMultipleImage['id']}}">
                                                    </div>
                                            </div>                           
                                       @php
                                        $new_row++;
                                     } 

                                     @endphp
                                         {{--  <div class="col-sm-12 col-md-6 col-lg-3 mult_img_div" >
                                            <div class="img-shop-tbl nw-in-shp">
                                                     @php
                                                        $product_img_path = ""; 
                                                        $image_name = "";
                                                        $image_type = "category";
                                                        $is_resize = 0; 
                                                        $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                                     @endphp         

                                                       <input type="file" 
                                                        class="form-control dropify" 
                                                        name="old_product_multiple_image[{{$details['id']}}][]"
                                                        data-default-file="{{ $product_img_path }}"
                                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                        data-max-file-size="2M" 
                                                        data-errors-position="outside"                                       
                                                        id="product_multiple_image_{{$row}}_{{$new_row}}"
                                                        onchange="appendAnotherDropify({{$row}},{{$details['id']}})"
                                                        >           
                                                  </div>
                                          </div> --}}
                                        @php 

                                        if($dropify_cnt < 5){ 
                                        @endphp
                                        <div class="col-sm-12 col-md-6 col-lg-3 mult_img_div">
                                            <div class="img-shop-tbl nw-in-shp">
                                                     @php
                                                        $product_img_path = ""; 
                                                        $image_name = "";
                                                        $image_type = "category";
                                                        $is_resize = 0; 
                                                        $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                                     @endphp         

                                                       <input type="file" 
                                                        class="form-control dropify" 
                                                        name="old_product_multiple_image[{{$details['id']}}][]"
                                                        data-default-file="{{ $product_img_path }}"
                                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                        data-max-file-size="2M" 
                                                        data-errors-position="outside"                                       
                                                        id="product_multiple_image_{{$row}}_{{$new_row}}"
                                                        onchange="appendAnotherDropify({{$row}},{{$details['id']}})"
                                                        >           
                                                  </div>
                                          </div> 

                                          @php } @endphp
                                        </div>
                                        
                                         @php
                                          
                                          } else { 
                                     
                                          @endphp
                                        </div>
                                           <div class="row mulitple-img-box" id="mulitple-img-box_{{$row}}" row_id="{{$row}}" >
                                            <div class="col-sm-12 col-md-6 col-lg-3 mult_img_div">
                                                <div class="img-shop-tbl nw-in-shp">
                                                @php
                                                $product_img_path = ""; 
                                                $image_name = "";
                                                $image_type = "category";
                                                $is_resize = 0; 
                                                $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                                @endphp
                                                <input type="file" 
                                                class="form-control dropify" 
                                                name="old_product_multiple_image[{{$details['id']}}][]"
                                                data-default-file="{{ $product_img_path }}"
                                                data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                data-max-file-size="2M" 
                                                data-errors-position="outside"                                       
                                                id="product_multiple_image_{{$row}}_{{$new_row}}"
                                                onchange="appendAnotherDropify({{$row}},{{$details['id']}})"
                                                >           
                                                </div>
                                            </div>

                                         </div>


                                            @php 
                                              }
                                            @endphp
                                            {{-- Div for adding multiple sizes start --}}
                                            @php

                                            $size_details = get_size_details($details['product_id'],$details['sku']);
                                      
                                            @endphp
                                            
                                            <div class="row showSize" id="showSize{{$row}}">
                                            @if(count($size_details)>0)

                                              @php
                                              $size_row_id = 0;

                                             
                                              $size_arr = get_size_for_category($product_arr['category_id']);

                                              foreach($size_details as $key => $size_data){
                                              @endphp

                                        {{-- Add Size Grid Start --}}
                                          <div class="row" id="size_{{$row.$size_row_id}}">

                                                          <div class="col-sm-4 box">
                                                              <div class="form-group">
                                                                  <label>Size</label>
                                                                  <input type="hidden" name="product_size_id{{$row}}[]" value="{{$size_data['id'] or 0}}">
                                                                      <select id="size_id_{{$row.$size_row_id}}"  name="size_id{{$row}}[]" class="form-control" >
                                                                      <option value="">Select Size</option>
                                                                      @if(isset($size_arr) && count($size_arr)>0)
                                                                        @foreach($size_arr as $size)

                                                                          <option value="{{$size['id'] or 0}}" @if($size['id'] == $size_data['size_id']) selected @endif> {{ $size['size']?$size['size']:'NA'}}</option>
                                                                        @endforeach
                                                                      @endif
                                                                    </select>
                                                              </div>
                                                         </div>
                                                         <div class="col-sm-4 box">
                                                              <div class="form-group">
                                                                  <label>Size Inventory</label>
                                                                      <input type="text" class="form-control size_class_{{$row}}" placeholder="size_inventory" onkeyup="checkSizeInventory('{{$row.$size_row_id}}','{{$row}}')" data-row="{{$row}}" name="size_inventory{{$row}}[]" id="size_inventory_{{$row.$size_row_id}}" value="{{$size_data['size_inventory']}}">
                                                                      <span class="red main_sku_class{{$row}}" id="size_inventory_error_msg_{{$row.$size_row_id}}"></span>
                                                              </div>
                                                         </div>
                                                         <div class="col-sm-3 box">
                                                        <div class="form-group plus-btn">
                                                          <label>Add/Delete</label>
                                                          @if(!@$size_details[$key+1]['id'])
                                                            <button type="button" class="btn" id="add_size_{{$row.$size_row_id}}" onclick="show_size({{$row}},{{$size_row_id+1}});" title="Add new SKU"><i class="fa fa-plus"></i></button>
                                                            @endif
                                                            {{-- @if($size_row_id != 0) --}}
                                                              <button type="button" class="btn" onclick="deleteRowSize('{{$row.$size_row_id}}','','','');" title="Delete SKU"><i class="fa fa-trash"></i></button>
                                                            {{-- @endif   --}}
                                                        </div>
                                                    </div>
                                          </div>

                                          {{-- Size End --}}

                                              @php
                                              
                                             }
                                            @endphp
                                            @endif
                                            </div>
                                           
                                            {{-- Div for adding multiple sizes end --}}

                                </div>
                                  </div>                               
                        </div>                 
                               @php
                                $row++;
                             }
                        }
                        @endphp
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                        <div class="row">
                        
                           <div class="col-md-12 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="edit_save_style_details" data-flag="">Save & Next</button>
                           </div>
                        </div>
                     </div>
                    </div>
                     <div class="clearfix"></div>
                    </form>
                  </div>                
                
                  <div role="tabpanel" id="edit-messages1" class="edit_pro_additional_img">
                     <form id="edit-additional-img-frm" class="edit-additional-img-frm">
                        {{ csrf_field() }}
                      <input type="hidden" name="product_id" id="edit-additional_img_product_id">

                      <input type="hidden" name="is_click_on_storeProduct" id="is_click_on_storeProduct" value="0"> 
                        <input type="hidden" name="is_click_on_update_style_and_dimension" id="is_click_on_update_style_and_dimension" value="0">                
                        <input type="hidden" name="is_click_on_store_additional_images" id="is_click_on_store_additional_images" value="0"> 
                        <input type="hidden" name="is_click_on_update_product_dategory" id="is_click_on_update_product_dategory" value="0">  
                      <div class="row">
                         <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4 uploadimg_box">
                            <div class="white-box uploadimgpro">
                               <label for="input-file-now" id="prod-img">Upload Product Image </label>
                               <input type="hidden" name="old_additional_prod_image" id="old_additional_prod_image">
                            </div>
                         </div>
                         <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4 uploadimg_box">
                            <div class="white-box uploadimgpro">
                               <label for="input-file-now" id="lifestyle-img">Upload Lifestyle Image </label>
                               <input type="hidden" name="old_lifestyle_image" id="old_lifestyle_image">
                            </div>
                         </div>
                         <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4 uploadimg_box">
                            <div class="white-box uploadimgpro">
                               <label for="input-file-now" id="packaging-img">Upload Packaging Image</label>
                               <input type="hidden" name="old_packaging_image" id="old_packaging_image">
                            </div>
                         </div>
                      </div>
                     <div class="clearfix"></div>                     
                        <div class="form-group">
                        <div class="row">
                          
                           <div class="col-md-12 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="edit_additional_img">Save & Next</button>
                           </div>
                        </div>
                     </div>
                    </form>
                  </div>               
                
                  <div role="tabpanel" id="edit-settings1">
                     <form id="edit-category-frm" class="edit-category-frm">
                        {{ csrf_field() }}
                        <input type="hidden" name="product_id" id="edit-category_product_id">


                        <input type="hidden" name="is_click_on_storeProduct" id="is_click_on_storeProduct" value="0"> 
                        <input type="hidden" name="is_click_on_update_style_and_dimension" id="is_click_on_update_style_and_dimension" value="0">                
                        <input type="hidden" name="is_click_on_store_additional_images" id="is_click_on_store_additional_images" value="0"> 
                        <input type="hidden" name="is_click_on_update_product_dategory" id="is_click_on_update_product_dategory" value="0">  
                       
                        <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                           <div class="form-group">
                              <label for="old_category_id">Category<i class="red">*</i></label>
                              <div>
                                 <select id="old_category_id" onchange="get_subcategory_for_edit($(this));"  name="category_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select category">
                                    <!-- <option value="">Select Category</option> -->
                                    @if(isset($category_arr) && count($category_arr)>0)
                                       @foreach($category_arr as $category)
                                          <option value="{{$category['id']}}" @if($category['id'] == $product_arr['category_id']) selected="selected" @endif>{{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                                       @endforeach
                                    @endif
                                 </select>
                                 <span class='red'>{{ $errors->first('category_name') }}</span>
                              </div>
                           </div>
                        </div>
                           
                          <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                              <div class="form-group">
                                 <label for="second_category">Sub Categories</label>
                                    <select class="select2 select2-multiple" multiple="multiple" onchange="get_third_subcategory_for_edit($(this));" data-placeholder="Select Subcategories" name="sub_category[]" id="edit_second_category">
                                       @if(isset($sub_cat_arr))
                                          @foreach($sub_cat_arr as $sub_cat)
                                             <option value="{{$sub_cat['id']}}" @if(isset($product_arr['subcat_id']) && in_array($sub_cat['id'], $arr_sub_category_details)) selected="selected" @endif>{{$sub_cat['subcategory_name']}}</option>
                                          @endforeach
                                       @endif
                                    </select>                              
                                    <span class='red'>{{ $errors->first('sub_category') }}</span>
                                    <input type="hidden" name="subcat_id" id="subcat_id">
                              </div>
                           </div>

                           <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                              <div class="form-group">
                                 <label for="second_category">Third Level Categories</label>
                                 <select class="select2 select2-multiple" multiple="multiple" onchange="get_fourth_subcategory_for_edit($(this));" data-placeholder="Select Third level categories" name="sub_category3[]" id="edit_third_category">
                                    @if(isset($third_sub_cat_arr))
                                          @foreach($third_sub_cat_arr as $third_sub_cat)
                                             <option value="{{$third_sub_cat['id']}}" @if(isset($product_arr['third_subcat_id']) && in_array($third_sub_cat['id'], $arr_third_sub_category_details)) selected="selected" @endif>{{$third_sub_cat['third_sub_category_name']}}</option>
                                          @endforeach
                                    @endif
                                 </select>                              
                                 <span class='red'>{{ $errors->first('sub_category') }}</span>
                                 <input type="hidden" name="third_subcat_id" id="third_subcat_id">
                              </div>  
                           </div>

                           <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                             <div class="form-group">
                                 <label for="second_category">Fourth Level Categories</label>
                                 <select class="select2 select2-multiple" multiple="multiple" data-placeholder="Select Fourth level categories" name="sub_category4[]" id="edit_fourth_category">
                                    @if(isset($fourth_sub_cat_arr))
                                          @foreach($fourth_sub_cat_arr as $fourth_sub_cat)
                                             <option value="{{$fourth_sub_cat['id']}}" @if(isset($product_arr['fourth_subcat_id']) && in_array($fourth_sub_cat['id'], $arr_fourth_sub_category_details)) selected="selected" @endif>{{$fourth_sub_cat['fourth_sub_category_name']}}</option>
                                          @endforeach
                                    @endif
                                 </select>                              
                                 <span class='red'>{{ $errors->first('sub_category') }}</span>
                                 <input type="hidden" name="fourth_subcat_id" id="fourth_subcat_id">
                              </div>  
                           </div>

                            <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                              {{-- <div class="form-group">
                            <label  for="old_shipping_charges">Shipping Charges ($) <i class="red">*</i></label>
                            <div>
                              <input type="text" 
                              class="form-control" 
                              placeholder="Enter Shipping Amount" 
                              name="shipping_charges" 
                              id="old_shipping_charges" 
                              data-parsley-required="true" 
                              data-parsley-required-message="Please enter shipping charges" 
                              data-parsley-type="number" 
                              data-parsley-type-message="Please enter valid shipping charges"
                              min="0"                                        
                              data-parsley-trigger="keyup" 
                              data-parsley-type="number"
                               />
                              <span class='red'>{{ $errors->first('shipping_charges') }}</span>
                            </div>
                          </div> --}}
                          </div>
                           <div class="clearfix"></div> 

                        <div class="form-group">
                           
                         

                          {{-- <div class="col-md-12">
                            <label  for="old_shipping_type">Shipping Discount Type <i class="red">*</i></label>
                            <div>
                              <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select shipping discount type" id="old_shipping_type" name="shipping_type">
                                <option value="" >Select Type</option>
                                <option value="1"> Free Shipping</option>
                                <option value="2"> % off</option>
                                <option value="3"> $ off</option>
                              </select>
                            </div>
                          </div>
                          <div class="clearfix"></div>  --}}
                           </div>
                           <div class="form-group">
                             <div id="shipping_amounts" class="mdls-nw">
                            </div>
                            <div style="padding-left:15px" id="err_ship_min_amount" class="red"></div>
                             <div class="clearfix"></div> 
                          </div>

                            <div class="form-group">
                           {{-- <div class="col-md-12">
                            <label  for="old_shipping_type">Product Discount Type</label>
                            <div>
                              <select class="form-control" id="old_product_discount" name="product_discount_type">
                                <option value="">Select Type</option>
                                <option value="1"> % off</option>
                                <option value="2"> $ off</option>
                              </select>
                            </div>
                          </div>
                          <div class="clearfix"></div> --}}
                        </div>


                          <div class="clearfix"></div> 
                       
                       
                        <div class="form-group" id="product_dis_amt">
                          
                           <div class="clearfix"></div> 
                        </div>

                        <div class="clearfix"></div>                     
                        <div class="form-group">
                        <div class="row">
                       
                           <div class="col-md-4 text-center">
                            
                           </div>
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="edit_category">Save</button>
                           </div>
                        </div>
                        <div class="clearfix"></div> 
                     </div>
                     <div class="clearfix"></div>
                      </form>
                  </div>
              </div>
                
                
                 
               </div>
            </div>
         </div>
      </div>
   </div>
</div>



<script type="text/javascript">
  var remote_url = '{{ url('/vendor/products/does_exists/sku_no') }}';
  var edit_remote_url = '{{ url('/vendor/products/does_exists_edit/sku_no') }}';
  var module_url_path  = "{{ $module_url_path or '' }}";  
  $(document).ready(function(){
   
    // var drEvent = $('.dropify').dropify();
    // drEvent.on('dropify.beforeClear', function(event, element){
        
    //    removeImage(element.input[0].attributes.multiimgid,element.input[0].attributes.id);
    // });
    
     /*Remove already init TinyMCE*/
      setTimeout(()=>{
        tinymce.remove();

        initalizedTinyMCE();
      },200);
      /*------------------------*/




    var product_id = '{{$product_id}}';
    
    edit_product(product_id);
    


  });

  function initalizedTinyMCE(){
    tinymce.init({
     selector: '#old_product_description',
     relative_urls: false,
     remove_script_host:false,
     convert_urls:false,
     plugins: [
       'link',
       'fullscreen',
       'contextmenu '
     ],
     toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
     content_css: [
       // '//www.tinymce.com/css/codepen.min.css'
     ]
   });
  }

 $('#close_id').click(function(){
   
   location.reload();
 });

$('.close, .cancel_btn').click(function(){
  $('#item-details-frm').trigger('reset');
   location.reload();
 });

//this function for checking inventory
function checkInventory(ref)
{
   var row = $(ref).attr('data-row');
 
   var minQty = $("#min_quantity_"+row).val();
   var inventory = $("#quantity_"+row).val();
  
   var minQty = parseInt(minQty);
   var inventory = parseInt(inventory); 
   var flag = 0;
   if(inventory < minQty)
   {
      $("#inventory_error_msg_"+row).html("Inventory should be greater than product min qty");
       flag = 1;
       $("#save_style_details").attr('data-flag',flag);
       $("#edit_save_style_details").attr('data-flag',flag);
      return false;

   }
   else
   {  flag = 0;
      $("#inventory_error_msg_"+row).html("");
      $("#save_style_details").attr('data-flag',flag);
      $("#edit_save_style_details").attr('data-flag',flag);
   }

   row++;

   $("#row").val(row);
}

//this function for checking product min qty
function checkQuantity(ref)
{ 
   var row = $(ref).attr('data-row');
 
   var minQty = $("#min_quantity_"+row).val();
   var inventory = $("#quantity_"+row).val();
  
   var minQty = parseInt(minQty);
   var inventory = parseInt(inventory); 
   var flag = 0;

   if(minQty > inventory)
   {
      $("#quantity_error_msg_"+row).html("Product min quantity should be less than inventory");
       flag = 1;
       $("#save_style_details").attr('data-flag',flag);
       $("#edit_save_style_details").attr('data-flag',flag);
      return false;

   }
   else
   {  flag = 0;
      $("#quantity_error_msg_"+row).html("");
      $("#save_style_details").attr('data-flag',flag);
      $("#edit_save_style_details").attr('data-flag',flag);
   }

   row++;

   $("#row").val(row);
}

// $(function () {

//     $('#old_retail_price').on('keyup', function(){ 
//        var elem       =  $('#old_retail_price').parsley();
//        var elem_whole       =  $('#old_retail_price').parsley();
//        var old_retail_price = $('#old_retail_price').val(); 
//        var old_wholsale_price = $('#old_unit_wholsale_price').val();
//        old_retail_price =  parseFloat(old_retail_price);
//        old_wholsale_price = parseFloat(old_wholsale_price);
//        var error_name = 'custom_error';       
//        var price_error = 'price_error';
//         if ($(this).val()!="" && $(this).val() == 0)
//         {
//             elem.removeError(error_name);
//             elem.addError(error_name, {message:'Unit retail price should not be 0'});
//         }
//         else
//         {
//            elem.removeError(error_name);
//         }

//         if(old_wholsale_price>old_retail_price)
//        {      

//             elem_whole.removeError(price_error);
//             elem_whole.removeError(error_name);
//             elem_whole.addError(price_error, {message:'Unit wholesale price should be less than unit retail price '});
//        } else {
//             $("#old_unit_wholsale_price").removeClass('parsley-error');
//             $(".parsley-price_error").html('');
//             //elem_whole.removeError(error_name);
//        }
        
//     });
// });

function removeImage(ref)
{
          var parentDiv = ref.parentNode;
          var parentParentDiv1 = parentDiv.parentNode;
          var parentParentDiv2 = parentParentDiv1.parentNode;
          var parentParentDiv3 = parentParentDiv2.parentNode;

          var module_url_path  = "{{ $module_url_path or '' }}"; 

          var product_det_id = $("#"+parentParentDiv3.id).attr('product_det_id');

          var deleted_div_row_id = $("#"+parentParentDiv3.id).attr('row_id');
          
          if (parentParentDiv2.className.split(' ').indexOf('mult_img_div')>=0){ 

            var numItems  = $("#mulitple-img-box_"+deleted_div_row_id+" .mult_img_div").length;    
            // var numItems = $('.mult_img_div').length;

            if(numItems == '1'){
                parentParentDiv2.remove();
                appendAnotherDropify(deleted_div_row_id,product_det_id);
            } else {
                parentParentDiv2.remove();
            }
           
          } 
          
}



//this function for checking inventory
function checkInventory(ref)
{
     var row = $(ref).attr('data-row');
   
     var minQty = $("#min_quantity_"+row).val();
     var inventory = $("#quantity_"+row).val();
    
     var minQty = parseInt(minQty);
     var inventory = parseInt(inventory); 
     var flag = 0;

      if(inventory < minQty)
      {
          $("#inventory_error_msg_"+row).html("Inventory should be greater than product minimum quantity");
           flag = 1;
           $("#save_style_details").attr('data-flag',flag);
           $("#edit_save_style_details").attr('data-flag',flag);
          return false;

     }
     else
     {  
        flag = 0;
        $("#inventory_error_msg_"+row).html("");
        $("#save_style_details").attr('data-flag',flag);
        $("#edit_save_style_details").attr('data-flag',flag);
     }

     row++;

     $("#row").val(row);
}

function checkMinQuantity(ref)
{  
    var row = $(ref).attr('data-row');
   
    var minQty = $("#min_quantity_"+row).val();
     var inventory = $("#quantity_"+row).val();
    
    var minQty = parseInt(minQty);
    var inventory = parseInt(inventory); 
    var flag = 0;

    if(minQty > inventory)
    {
          $("#quantity_error_msg_"+row).html("Product minimum quantity should be less than inventory");
           flag = 1;
          $("#save_style_details").attr('data-flag',flag);
          $("#edit_save_style_details").attr('data-flag',flag);
          return false;

     }
     else
     {  
        flag = 0;
        $("#quantity_error_msg_"+row).html("");
        $("#save_style_details").attr('data-flag',flag);
        $("#edit_save_style_details").attr('data-flag',flag);
     }

     row++;

     $("#row").val(row);

}

$('#edit_category1').click(function(){


  if ($('#edit-category-frm').parsley().validate() == false) return;


        var flag = 0;
        var min_amt_free_shiping   = ($('#old_free_ship_min_amount').val()) ? $('#old_free_ship_min_amount').val() : 0;
        var product_unit_price     = ($('#old_unit_wholsale_price').val()) ? $('#old_unit_wholsale_price').val() : 0;
        var ship_percent_off_value = ($(".ship_percent_off").val()) ? $('.ship_percent_off').val() : 0;
        var ship_dolar_off_value   = ($(".ship_dollar_off").val()) ? $('.ship_dollar_off').val() : 0;
        var shipping_charges       = ($('#old_shipping_charges').val()) ? $('#old_shipping_charges').val() : 0;
        var discount_type       = ($('#old_shipping_type').val()) ? $('#old_shipping_type').val() : "";
     
        if(discount_type == ""){
          return false;
        }
        if(parseFloat(product_unit_price) > parseFloat(min_amt_free_shiping) && discount_type == '1')
        { 
             flag = 1;

            if(min_amt_free_shiping != ""){
             $('#err_ship_min_amount').text('Min amount for getting free shipping should be greater than price');
             return false;
           }
         

         
        }
        else
        {
           $('#err_ship_min_amount').text(' ');
        }

        if(parseInt(ship_percent_off_value) >100)
        {
            $('#error_ship_per_off_amt').text('% Off amount for getting discount on shipping should be less than 100');
            flag = 1;
        }


        if(parseInt(shipping_charges) < parseInt(ship_dolar_off_value))
        { 
             $('#error_ship_per_off_amt').text('$ off amount for getting discount on shipping should be less than shipping charges');
             flag = 1;
        }
          /*------------------------------------------------------------------*/


        /*------------validation for product discount ------------------------*/

        var percent_off_product_dis = $('.product_percent_off').val(); 
        var dolar_off_product_dis   = $('.product_dolar_off').val(); 
        var min_amt_product_dis     = $('#old_product_discount_min_amount').val();
        


        if(parseInt(product_unit_price) > parseInt(min_amt_product_dis))
        { 
          
          $('#error_min_product_amt').text('Min amount for getting product discount should be greater than  price');
          flag = 1;
        }

          
        if(percent_off_product_dis > 100)
        {
            $('#error_per_off_product_amt').text('% off amount for getting product discount should be less than 100');
            flag = 1;
        }

         if (parseInt(product_unit_price) < parseInt(dolar_off_product_dis)) 
          {
            $('#error_per_off_product_amt').text('$ off amount for product discount should be less than price');
             flag = 1;
          }


        if(flag == 1)
        {
            return false;
        }
      /*-----------------------------------------------------------------------------------*/



          $.ajax({
              url: module_url_path+'/update_product_category',
              type: "POST",
              data: new FormData($("#edit-category-frm")[0]),
              contentType: false,
              processData: false,
              dataType: 'json',
              beforeSend: function() 
              {
                showProcessingOverlay();                
              },
              success: function(response) {
                 hideProcessingOverlay();
                  if (response.status == 'success') {
                      $('#edit-category-frm')[0].reset();

                      swal({
                              title: 'Success',
                              text: response.description,
                              type: 'success',
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                          },
                          function(isConfirm, tmp) {
                              if (isConfirm == true) {
                                location.reload(true);
                              }
                          });
                  } else {
                      swal('Error', response.description, 'error');
                  }
              }

          });

});

  // Function to append multiple image dropify after selecting image 
  function appendAnotherDropify(row_id,product_det_id = false){

     var hid_product_img_path = $("#hid_product_img_path").val();  
    if(product_det_id == false){
        product_det_id = 0;
        var html = '<div class="col-sm-12 col-md-6 col-lg-3 mult_img_div"><div class="img-shop-tbl nw-in-shp"><input type="file" class="form-control dropify" name="old_product_multiple_image['+row_id+'][]" data-default-file="'+hid_product_img_path+'" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M"  data-errors-position="outside"  id="product_img_'+row_id+'" onchange="appendAnotherDropify('+row_id+','+product_det_id+')"> </div></div>';
    } else {
           var html = '<div class="col-sm-12 col-md-6 col-lg-3 mult_img_div"><div class="img-shop-tbl nw-in-shp"><input type="file" class="form-control dropify" name="old_product_multiple_image['+product_det_id+'][]" data-default-file="'+hid_product_img_path+'" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M"  data-errors-position="outside"  id="product_img_'+row_id+'" onchange="appendAnotherDropify('+row_id+','+product_det_id+')"> </div></div>';
    } 
     
      var totalDivCount=$("#mulitple-img-box_"+row_id+"  .mult_img_div").length;  
      var img_length = $('input[name="db_old_product_multiple_image['+product_det_id+'][]"]').length;

      if(img_length == 0){
          $("#show_multiple_dropify_"+row_id).show();
      } else {
          $("#show_multiple_dropify_"+row_id).hide();
      }
      
      if(totalDivCount < 5){
        setTimeout(function(){
              $("#mulitple-img-box_"+row_id).append(html);
              $('.dropify').dropify();
          }, 500);   
      }
     
  }

  // Function to hide / show multiple images box
  function show_multiple_dropify(row_id){
      
      var label_text = $("#btn_label_"+row_id).html();
      (label_text == "Add") ? $("#btn_label_"+row_id).html('Hide') : $("#btn_label_"+row_id).html('Add');
      (label_text == "Add") ? $("#mulitple-img-box_"+row_id).show() : $("#mulitple-img-box_"+row_id).empty(); 

      if ($.trim($("#mulitple-img-box_"+row_id).html() == '') && label_text == "Add"){
        var html = '<div class="col-md-12"><label>Multiple Images</label></div><div class="col-sm-12 col-md-6 col-lg-3 mult_img_div"><div class="img-shop-tbl nw-in-shp"><input type="file" class="form-control dropify" name="product_multiple_image['+row_id+'][]" data-default-file="{{ $product_img_path }}" data-max-file-size="2M"  data-errors-position="outside"   id="product_img_'+row_id+'" onchange="appendAnotherDropify('+row_id+')">  </div></div>';
        (label_text == "Add") ? $("#mulitple-img-box_"+row_id).html(html) : "";
        $('.dropify').dropify();
      }  

  }

  function testFunction(ele)
  {
    console.log($(ele).val());

    $(ele).attr('data-parsley-remote','{{ url("/vendor/products/does_exists/424")}}');
  }

  /* Function to create new row */
    function addNewRow(row_id){


      var totalDivRowCount=$(".innerbox").length;  

      /* Condition that user can not add more than 10 SKU for a product*/
      var varTotalSKUCount = 10;
      if(totalDivRowCount >= varTotalSKUCount)
      {
        var msg = 'Sorry ! You can not add more than '+varTotalSKUCount+' SKU for this product.';
        
        swal({
              title: "",
              text: msg,
              type: "warning",
              showCancelButton: false,                
              confirmButtonColor: "#8CD4F5",
              confirmButtonText: "OK",
              closeOnConfirm: true
        },

         function(isConfirm,tmp)
        {
            /*if(isConfirm==true)
            {

                return true;
            }  
            else
            {
              return false;
            }*/
             return false;

          });
        return false;
      } 

     
       if($('#edit-style-frm').parsley().validate()==false) return;
       var csrf_token = "{{csrf_token()}}";
       $.ajax({
              url: module_url_path+'/addneweditrow',
              type: "POST",
              data: { row_id : row_id,'_token':csrf_token },                     
              success: function(response) {
                  $("#innerRowBox").append(response);
                  $('.dropify').dropify();
                  var prev_row_id = row_id;                 
                  $("#btn_add_new_row_"+prev_row_id).hide();
                  $('#edit-style-frm').parsley().refresh();
                  
              }
            });
  }

  /* Function to delete row */
  function deleteRow(row_id,prod_det_id,product_id,sku_no){
    var totalDivRowCount=$(".innerbox").length;  
    if(totalDivRowCount == 1){
         var msg = 'Sorry! You can not remove this SKU. At least One SKU is mandatory for this product.';
        
        swal({
              title: "",
              text: msg,
              type: "warning",
              showCancelButton: false,                
              confirmButtonColor: "#8CD4F5",
              confirmButtonText: "OK",
              closeOnConfirm: true
        },

         function(isConfirm,tmp)
        {
            /*if(isConfirm==true)
            {

                return true;
            }  
            else
            {
              return false;
            }*/
             return false;

          });
        return false;
    } 


    var msg = 'Are you sure? Do you want to delete this SKU ?';
    var csrf_token = "{{csrf_token()}}";
    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },

     function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {

            $.ajax({
                url: module_url_path+'/deleterow',
                type:'GET',
                data:{'prod_det_id':prod_det_id,'_token':csrf_token,'product_id' : product_id,'SKU_no' : sku_no},
                dataType:'json',
                beforeSend : function()
                {
                  showProcessingOverlay();
                },
                success: function(response)
                { 
                    hideProcessingOverlay();
                    if(row_id == '1'){
                        $("#innerbox_"+row_id).remove();
                        //addNewRow(1);
                    } else {
                    
                        $("#innerbox_"+row_id).remove();
                    }
                    var prev_row_id = row_id - 1;
                    $("#btn_add_new_row_"+prev_row_id).show();
                }
            }); 
        }  
        else
        {
          return false;
        }

      });
  }
  

  //get third level categories


// get third level categories
function get_third_subcategory_for_edit(referance) {
   $("#edit_third_category").select2('val', '');
   $("#edit_fourth_category").select2('val', '');
    var sub_category_id = referance.val();

    var url = SITE_URL + '/get_third_sub_categories/' + sub_category_id;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) 
        {
            var third_sub_categories = '';
            if (response.status == 'SUCCESS') {
               if (typeof(response.third_sub_categories_arr) == "object") {

                  let subcat_data = $('#subcat_id').val();
                  let already_selected_subcategories = $.parseJSON(subcat_data);

                  $(response.third_sub_categories_arr).each(function(index, third_category_arr) {
                     $(third_category_arr).each(function(index, third_category) {
                        var is_selected = (already_selected_subcategories.indexOf(third_category.id) > -1);
                        if (is_selected == true) {
                           third_sub_categories += '<option selected="selected" value="' + third_category.id + '">' + third_category.third_sub_category_name + '</option>';
                        } else {
                           third_sub_categories += '<option value="' + third_category.id + '">' + third_category.third_sub_category_name + '</option>';
                        }
                     });
                  });
               }
            } else {
               third_sub_categories += '';
            }
            $('#edit_third_category').html(third_sub_categories);
        }
    });
}

//get fourth level categories
function get_fourth_subcategory_for_edit(referance) {
   
   $("#edit_fourth_category").select2('val', '');
    var sub_category_id = referance.val();

    var url = SITE_URL + '/get_fourth_sub_categories/' + sub_category_id;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var fourth_sub_categories = '';
            if (response.status == 'SUCCESS') {
                if (typeof(response.fourth_sub_categories_arr) == "object") {

                    let subcat_data = $('#subcat_id').val();
                    let already_selected_subcategories = $.parseJSON(subcat_data);

                    $(response.fourth_sub_categories_arr).each(function(index, fourth_category_arr) {
                     $(fourth_category_arr).each(function(index, fourth_category) 
                     {
                        var is_selected = (already_selected_subcategories.indexOf(fourth_category.id) > -1);

                        if (is_selected == true) {
                           fourth_sub_categories += '<option selected="selected" value="' + fourth_category.id + '">' + fourth_category.fourth_sub_category_name + '</option>';
                        } else {
                           fourth_sub_categories += '<option value="' + fourth_category.id + '">' + fourth_category.fourth_sub_category_name + '</option>';
                        }
                     });
                    });
                }
            } else {
               fourth_sub_categories += '';
            }
            $('#edit_fourth_category').html(fourth_sub_categories);
        }
    });
}

function show_size(row_id,size_row_id)
   {
      // var category_id = referance.val();
      var category_id = $("#category_id"+row_id).val();
      
      $.ajax({
              url: module_url_path+'/show_size',
              type: "POST",
              data: { size_row_id:size_row_id,row_id:row_id,category_id : category_id,'_token':csrf_token },                 
              success: function(response) {

                  if(response != 0)
                  {
                     
                     $("#showSize"+row_id).append(response);
                      
                     if(size_row_id > 0)
                     {
                        size_row_id=size_row_id-1;
                        // $("#add_size_"+row_id+size_row_id).hide();      
                        $("#add_size_"+row_id+size_row_id).prop("disabled", "true") ;      
                     }
                     
                   
                  }
                  else
                  {
                     $("#showSize"+row_id).html("");
                  }
                  //$('.dropify').dropify();
              }
            });
   }

   function deleteRowSize(row_id)
   {
 
    var msg = 'Are you sure? Do you want to delete this size ?';
    var csrf_token = "{{csrf_token()}}";
    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },

    function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {

            $("#size_"+row_id).remove();
        }  
        else
        {
          return false;
        }

      });
   }

   //this function for checking size inventory
   function checkSizeInventory(size_row_id=false,row_id)
   {
      var sum = 0;
      var remaining_inv = 0;
      // var row = $(ref).attr('data-row');
      // alert(size_row_id);
      
      var minQty = $("#min_quantity_"+row_id).val();
      var size_inventory = $("#size_inventory_"+size_row_id).val();
      var inventory = $("#quantity_"+row_id).val();

      var minQty = parseInt(minQty);
      var size_inventory = parseInt(size_inventory); 
      $(".size_class_"+row_id).each(function(){
           sum += +$(this).val();
       });
      remaining_inv = inventory - sum;

      var inventory = parseInt(inventory);
      var flag = 0;
      // alert(remaining_inv);
      // if(size_inventory > inventory)
      // {
      //    $("#size_inventory_error_msg_"+size_row_id).html("Size Inventory should be less than product inventory");
      //     flag = 1;
      //     $("#save_style_details").attr('data-flag',flag);
      //     $("#edit_save_style_details").attr('data-flag',flag);
      //    return false;

      // }

      if(remaining_inv < 0 || remaining_inv > 0)
      {
         /*if(size_row_id != '')
         {*/
            // alert("null")
            $("#size_inventory_error_msg_"+size_row_id).html("Sum of all Size Inventory should be equal to product inventory");
       /*  }else{
            // alert("hi");
            $("#inventory_error_msg_"+row_id).html("Sum of all Size Inventory should be equal to product inventory");
         }   */
         
          flag = 1;
          $("#save_style_details").attr('data-flag',flag);
          $("#edit_save_style_details").attr('data-flag',flag);
         return false;
      }
      else
      {  
         $(".main_sku_class"+row_id).html("");
         flag = 0;
         $("#size_inventory_error_msg_"+size_row_id).html("");
         $("#save_style_details").attr('data-flag',flag);
         $("#edit_save_style_details").attr('data-flag',flag);
      }
    }




</script>
<script type="text/javascript" src="{{url('/assets/js/module_js/edit_product.js')}}"></script>
<script type="text/javascript" src="{{url('/assets/js/module_js/add_product.js')}}"></script>
@stop