
@php
$product_img_path = ""; 
$image_name = "";
$image_type = "category";
$is_resize = 0; 
$product_img_path = imagePath($image_name, $image_type, $is_resize);              
@endphp
  <div class="innerbox" id="innerbox_{{$row}}">
    
          <div class="col-sm-12 text-right">
              <div class="form-group plus-btn">
                 {{--  <button type="button" class="btn" onclick="show_multiple_dropify({{$row}})"><span id="btn_label_{{$row}}">Add</span> Multiple Images</button> --}}
                  <button type="button" class="btn" onclick="addNewRow({{$row}});" title="Add new SKU"><i class="fa fa-plus"></i></button>
                  <button type="button" class="btn" onclick="deleteRow({{$row}},'','','');" title="Delete SKU"><i class="fa fa-trash"></i></button>
              </div>
          </div>
           <div class="row">
              <div class="col-sm-3 left">
                  <div class="col-sm-12">
                    <label>Image<i class="red">*</i></label>
                    <div class="img-shop-tbl nw-in-shp">
                          <input type="file" 
                          class="form-control dropify" 
                          name="new_product_image[]"
                          data-default-file="{{ $product_img_path }}"
                          data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                          data-max-file-size="2M" 
                          data-errors-position="outside"
                          data-parsley-required="true"
                          data-parsley-required-message="Please select image" 
                          id="product_img_{{$row}}"
                          data-parsley-errors-container="#show_validate_msg_{{$row}}"
                          >       
                          <div class="show_validate_msg_div" id="show_validate_msg_{{$row}}"></div>    
                    </div>
                    
                  </div>
              </div>
              <div class="col-sm-9 right">
              <div class="row">
                    <div class="col-sm-3 box">
                        <div class="form-group">
                        <label>SKU<i class="red">*</i></label>
                            <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" data-parsley-required-message="Please enter sku no" name="new_sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-whitespace-message="Whitespaces are not allowed"  data-parsley-remote="{{ url('/vendor/products/does_exists/sku_no') }}"
                            data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists" id="sku_{{$row}}"
                            
                            >
                            
                        </div>

                    </div>
                      
                    <div class="col-sm-3 box">
                        <div class="form-group">
                            <label>Min Quantity<i class="red">*</i></label>
                            <input type="text" class="form-control" placeholder="Min Quantity" name="new_min_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter min quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" min="1" id="min_quantity_{{$row}}" data-row="{{$row}}" onkeyup="checkMinQuantity(this);">
                            <span class="red" id="quantity_error_msg_{{$row}}"></span>
                        </div>
                    </div>
                    <div class="col-sm-3 box">
                    <div class="form-group">
                        <label>Inventory<i class="red">*</i></label>
                            <input type="text" class="form-control" placeholder="Inventory" name="new_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter inventory" data-parsley-type="digits" data-parsley-type-message="Please enter valid inventory" id="quantity_{{$row}}" data-row="{{$row}}" onkeyup="checkInventory(this);">
                            <span class="red" id="inventory_error_msg_{{$row}}"></span>
                    </div>
                    </div>
                    <div class="col-sm-3 box">
                    <div class="form-group">
                        <label>Description</label>
                          <textarea  class="form-control" placeholder="Enter Description" rows="1" name="new_sku_product_description[]" id="sku_product_description_{{$row}}"></textarea>
                          <span id="err_product_desc_add_prod" class="red"></span>
                    </div>
                    </div>
                    <div class="col-sm-3 box">
                    <div class="form-group">
                        <label>Color</label>
                            <input type="text" class="form-control" placeholder="Color" name="color[]" id="color_{{$row}}" data-row="{{$row}}">
                            <span class="red" id="color_error_msg_{{$row}}"></span>
                    </div>
                    </div>
                    <div class="col-sm-3 box">
                    <div class="form-group">
                        <label>Category</label>
                            <select id="category_id{{$row}}" onchange="show_size({{$row}},0)" name="category_id" class="form-control" >
                            <option value="">Select Category</option>
                            @if(isset($category_arr) && count($category_arr)>0)
                              @foreach($category_arr as $category)

                                <option value="{{$category['id'] or 0}}"> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                              @endforeach
                            @endif
                          </select>
                    </div>
                    </div>
              </div>
               <div class="row showSize" id="showSize{{$row}}">
               
              </div>
              <div class="row mulitple-img-box" id="mulitple-img-box_{{$row}}" {{-- style="display: none" --}} row_id="{{$row}}">
                  <div class="col-md-12"><label>Multiple Images</label></div>
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
                      name="old_product_multiple_image[{{$row}}][]"
                      data-default-file="{{ $product_img_path }}"
                      data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                      data-max-file-size="2M" 
                      data-errors-position="outside"                                       
                      id="product_img_{{$row}}"
                      onchange="appendAnotherDropify({{$row}})"
                      >           
                      </div>
                  </div>

               </div>

               {{-- <div class="row mulitple-img-box extra-div" id="mulitple-img-box_{{$row}}" row_id="{{$row}}" style="display: none">
                 <div class="col-md-12"><label>Multiple Images</label></div>
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
                      name="old_product_multiple_image[{{$row}}][]"
                      data-default-file="{{ $product_img_path }}"
                      data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                      data-max-file-size="2M" 
                      data-errors-position="outside"                                       
                      id="product_img_{{$row}}"
                      onchange="appendAnotherDropify({{$row}})"
                      >           
                      </div>
                  </div>

               </div> --}}
           </div>
      </div>

  </div>