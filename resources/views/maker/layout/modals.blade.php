 <!-- Edit Product model -->
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

/*.form-control.custom-text-area
{
  height: 120px !important;
}*/


</style>

@php

  $row = 1;

@endphp

<input type="hidden" name="row" id="row" value="{{$row or ''}}">


 <div id="editModel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
     <div class="modal-dialog modal-lg addproduct-modal">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_id">×</button>

              
                <!--Hidden field for get data only no need to sent it to server-->
                <input type="hidden" id="subcat_id">

                 
                
                 <h4 class="modal-title" id="myModalLabel">Edit Product</h4> </div>
             <div class="modal-body rw-vendor-product-edit-modal-body">
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
                                    <label class="col-md-12">Unit Wholesale Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Unit Wholesale Price" name="unit_wholsale_price" id="old_unit_wholsale_price" data-parsley-required="true" data-parsley-required-message="Please enter unit wholesale price" data-parsley-type="number" data-parsley-type-message="Please enter valid unit wholesale price" data-parsley-lte="10"/>
                                      <div id="err_unit_wholsale_price_edit" class="red"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Retail Price($) <i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Unit Retail Price" name="retail_price" id="old_retail_price" data-parsley-required="true" data-parsley-required-message="Please enter unit retail price" data-parsley-type="number" data-parsley-type-message="Please enter valid unit retail price"/>
                                    <div id="err_unit_retail_price_edit" class="red"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
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
                                 ></textarea>
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
                     <div class="table-responsive">
                        <table class="table" id="style_and_diemension_tbl">
                           <thead>
                              <tr>
                                 <th>Image<i class="red">*</i></th>
                                 <!-- <th>
                                    <select class="custom-select col-12" name="optionName" id="old_optionName" data-parsley-required="true">
                                      
                                    </select><i class="red">*</i>
                                 </th> -->
                                <th>SKU<i class="red">*</i></th>
                                <th>Min Quantity<i class="red">*</i></th>                                
                                <!-- <th>Weight<i class="red">*</i></th> -->
                                <th>Inventory<i class="red">*</i></th>
                                 <!-- <th>Dimensions (Length, Width, Height)<i class="red">*</i></th> -->
                                <th>Product Description</th>
                                 <th>&nbsp;</th>
                              </tr>
                           </thead>
                           <tbody id="style_sec_body"></tbody>
                        </table>
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
                          <label  for="old_category_id">Category <i class="red">*</i></label>
                           <div >
                              <select id="old_category_id" onchange="get_subcategory_for_edit($(this));"  name="category_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select category">
                                 <option value="">Select Category</option>
                                 @if(isset($category_arr) && count($category_arr)>0)
                                 @foreach($category_arr as $category)
                                 <option value="{{$category['id'] or 0}}"> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
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
                           
                              <select class="select2 select2-multiple" multiple="multiple" data-placeholder="Select Subcategories" name="sub_category[]" id="edit_second_category">
                              </select>                              
                              <span class='red'>{{ $errors->first('sub_category') }}</span>
                            </div>
                           </div>

                            <div class="col-xs-12 col-md-12 col-sm-12 col-lg-4">
                              <div class="form-group">
                            <label  for="old_shipping_charges">Shipping Charges ($) <i class="red">*</i></label>
                            <div >
                              <input type="text" class="form-control" placeholder="Enter Shipping Amount" name="shipping_charges" id="old_shipping_charges" data-parsley-required="true" data-parsley-required-message="Please enter shipping charges"  data-parsley-type="number" data-parsley-type-message="Please enter valid shipping charges" />
                              <span class='red'>{{ $errors->first('shipping_charges') }}</span>
                            </div>
                          </div>
                          </div>
                           <div class="clearfix"></div> 

                        <div class="form-group">
                           
                         

                          <div class="col-md-12">
                            <label  for="old_shipping_type">Shipping Discount Type <i class="red">*</i></label>
                            <div>
                              <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select shipping discount type" id="old_shipping_type" name="shipping_type">
                                <option value="">Select Type</option>
                                <option value="1"> Free Shipping</option>
                                <option value="2"> % off</option>
                                <option value="3"> $ off</option>
                              </select>
                            </div>
                          </div>
                          <div class="clearfix"></div> 
                           </div>
                           <div class="form-group">
                             <div id="shipping_amounts" class="mdls-nw">
                            </div>
                            <div style="padding-left:15px" id="err_ship_min_amount" class="red"></div>
                             <div class="clearfix"></div> 
                          </div>

                            <div class="form-group">
                           <div class="col-md-12">
                            <label  for="old_shipping_type">Product Discount Type</label>
                            <div>
                              <select class="form-control" id="old_product_discount" name="product_discount_type">
                                <option value="">Select Type</option>
                                <option value="1"> % off</option>
                                <option value="2"> $ off</option>
                              </select>
                            </div>
                          </div>
                          <div class="clearfix"></div>
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
             <div class="modal-footer">
                <div class="col-sm-12">
                  <a href="" class="btn btn-info waves-effect">Close</a>
                </div>
             </div>
         </div>
         <!-- /.modal-content -->
     </div>
     <!-- /.modal-dialog -->
 </div>

<style type="text/css">
  .customtab.nav-tabs .nav-link.the-active.active{
    border-bottom: none;
        color: #2b2b2b;
  }
</style>

<!-- START add product modal-->
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">


<div class="modal-dialog modal-lg manage_product_product_modal addproduct-modal">
   <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         <h3 class="modal-title" id="myLargeModalLabel">Add Product</h3>
      </div>
      <div class="modal-body">

         <div class="">
            <div class="white-box" id="add-sec-tabs">
               <!-- Nav tabs -->
                

               <ul class="nav customtab nav-tabs" role="tablist">
                  <li role="presentation" class="nav-item">
                     <a href="#home1" id="item-sec-link" aria-controls="home" ><span class="visible-xs"><i class="ti-home"></i></span><span class="hidden-xs"> Item Details</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#profile1" id="style-sec-link" aria-controls="profile" ><span class="visible-xs"><i class="ti-user"></i></span> <span class="hidden-xs">Style & Dimensions</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#messages1" id="additional-img-sec-link" ><span class="visible-xs"><i class="ti-email"></i></span> <span class="hidden-xs">Additional Images</span></a>
                  </li>
                  <li role="presentation" class="nav-item" >
                     <a href="#settings1" id="category-sec-link" aria-controls="settings" ><span class="visible-xs"><i class="ti-settings"></i></span> <span class="hidden-xs">Category & Shipping Details</span></a>
                  </li>
               </ul>               
               <!-- Tab panes -->
               <div class="tab-content">
                  <div role="tabpanel" id="home1">
                     <form id="item-details-frm">
                        {{ csrf_field() }}
                        <input type="hidden" name="items_is_draft" id="items_is_draft" value="">
                        <input type="hidden" name="is_up1" id="is_up1"/>
                        <input type="hidden" name="product_id" id="pro_id"/>
                        <input type="hidden" name="is_active" value="0" />

                        <input type="hidden" name="is_click_on_storeProduct" id="is_click_on_storeProduct" value="0"> 
                        <input type="hidden" name="is_click_on_update_style_and_dimension" id="is_click_on_update_style_and_dimension" value="0">                
                        <input type="hidden" name="is_click_on_store_additional_images" id="is_click_on_store_additional_images" value="0"> 
                        <input type="hidden" name="is_click_on_update_product_dategory" id="is_click_on_update_product_dategory" value="0"> 

                         <!-- <div> -->
                           
                           <div style="width:100%;">
                            <div class="form-group">
                              <label class="col-md-12">Product Image<i class="red">*</i></label>
                              <div class="col-md-12">
                                 @php
                                    $product_img_path = ""; 
                                    $image_name = "";
                                    $image_type = "category";
                                    $is_resize = 0; 
                                    $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                                 @endphp
                                 <input type="file" name="product_primary_image" id="product_primary_image" class="form-control dropify"  placeholder="Enter Product Name"  data-default-file="{{ $product_img_path }}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" data-parsley-required="true" data-parsley-required-message="Please select product image"
                                 data-parsley-errors-container="#err_primary_product_img">

                                 <span class="text-danger" id="err_primary_product_img">{{ $errors->first('product_primary_image') }}</span>
                              </div>

                              <div class="clearfix"></div>
                           </div>
                        <div class="row">
                         <div class="col-md-12">

                           <div class="form-group">
                              <label class="col-md-12">Product Name<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <input type="text" class="form-control" placeholder="Enter Product Name" name="product_name" id="product_name" data-parsley-required="true" data-parsley-required-message="Please enter product name" value="{{old('product_name')}}"/>

                              </div>
                              <div class="clearfix"></div>
                           </div>

                           <div class="form-group">
                              <label class="col-md-12">Brand<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <select name="brand" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select brand">
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
                                 id="is_best_seller" name="is_best_seller" value="1">
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
                                       <input type="text" class="form-control" placeholder="Enter Case Quantity" name="case_qty" id="case_qty" data-parsley-required="true" data-parsley-required-message="Please enter case quantity" data-parsley-type="number" data-parsley-type-message="Please enter valid case quantity" data-parsley-min="1" data-parsley-min-message="case quantity should be minimum 1 or more"/>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Wholesale Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">

                                       <input type="text" class="form-control" placeholder="Enter Unit Wholesale Price" name="unit_wholsale_price" id="unit_wholsale_price" data-parsley-required="true" data-parsley-required-message="Please enter unit wholesale price"  data-parsley-type="number" data-parsley-type-message="Please enter valid unit wholesale price" data-parsley-notEqual=""/>
                                       <div id="err_unit_wholsale_price" class="red"></div>

                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Retail Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Unit Retail Price" name="retail_price" id="retail_price" data-parsley-required="true" data-parsley-required-message="Please enter unit retail price"  data-parsley-type="number" data-parsley-type-message="Please enter valid unit retail price"/>
                                    <div id="err_unit_retail_price" class="red"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                           </div>
                          {{--   <div class="form-group">
                              <label class="col-md-12">Product Ingredients</label>
                              <div class="col-md-12">
                                   <textarea placeholder="Enter Product ingredients" rows="5" name="product_ingrediants" id="product_ingrediants"></textarea>
                                   input-to-textarea form-control

                                   <div id="err_product_ingrediants" class="red"></div>
                              </div>

                              <div class="clearfix"></div>
                           </div> --}}

                           <div class="form-group textare-new-frm">
                              <label class="col-md-12">Product Ingredients</label>
                              <div class="col-md-12">
                                   <input class="input-to-textarea form-control" height="55" row="4" type="text" id="product_ingrediants" name="product_ingrediants" placeholder="Enter Product Ingredients">

                                   <div id="err_product_ingrediants" class="red"></div>
                              </div>

                              <div class="clearfix"></div>
                           </div>
        
                           

                           <div class="form-group prod-desc-div">
                              <label class="col-md-12">About This Product</label>
                              <div class="col-md-12">
                                 <textarea class="form-control custom-text-area" placeholder="Enter About This Product" rows="5" name="product_description" id="product_description" {{-- data-parsley-trigger="keyup" data-parsley-minlength="10" data-parsley-required="true" --}}
                                 ></textarea>
                                 <span id="err_product_desc" class="red"></span>
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

                        {{--   <div class="form-group">
                           <div class="row">
                               <div class="col-md-12 dflexbtn">
                                 <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c cancel_btn">Cancel</button>
                                 <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_items_details" id="save_items_details" data-is-draft="no">Save & Next</button>
                               </div>
                         </div>
                     </div> --}}

                        <div class="form-group">
                        <div class="row">
                           <div class="col-md-4 text-center">
                            
                           </div>
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_items_details"  id="save_items_details" data-is-draft="no">Save & Next</button>
                           </div>
                        </div>
                     </div>
                 </div>

                     <div class="clearfix"></div>
                   </form>
                  </div>
                
                  <div role="tabpanel" id="profile1">
                    <form id="style-frm">
                      {{ csrf_field() }}
                      <input type="hidden" name="product_id" id="style_product_id" value=""> 
                      <input type="hidden" name="style_is_draft" id="style_is_draft" value="">
                      <input type ="hidden" name = "is_up" id= "is_up"/>
                      
                     <div class="table-responsive">
                        <table class="table" id="style_and_diemension_tbl">
                           <thead>
                           
                              <tr>
                                <th>Image<i class="red">*</i></th>
                                <th>SKU<i class="red">*</i></th>
                                <th>Min Quantity<i class="red">*</i></th>
                                <th>Inventory<i class="red">*</i></th>
                                <th>Product Description</th>
                                <th>&nbsp;</th>
                              </tr>
                           </thead>
                           <tbody>

                                 <tr class="pro-add-tr">

                                  <td class="pro_add_edit_style_dimension">
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
                                        name="product_image[]"
                                        data-default-file="{{ $product_img_path }}"
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-max-file-size="2M" 
                                        data-errors-position="outside"
                                        data-parsley-required="true"

                                        data-parsley-required-message="Please select image" id="product_img_{{$row}}"
                                        >           
                                    </div>
                                 </td>

                                 <td class="parsley_error_sku_inventory">      

                                  <div class="row">
                                    <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" data-parsley-required-message="Please enter sku no" name="sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-whitespace-message="Whitespaces are not allowed"  data-parsley-remote="{{ url('/vendor/products/does_exists/sku_no') }}"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists" id="sku_{{$row}}">
                                  </div>

                                    
                                 </td>

                                  <td>
                                      <div class="row">
                                         <input type="text" class="form-control" placeholder="Min Quantity" name="min_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter minimum quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" min="1" id="min_quantity_{{$row}}" data-row="{{$row}}" onkeyup="checkMinQuantity(this);">

                                         <span class="red" id="quantity_error_msg_{{$row}}"></span>

                                      </div>

                                  </td>

                                  <td class="parsley_error_sku_inventory">

                                    <div class="row">
                                      <input type="text" class="form-control" placeholder="Inventory" name="quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter inventory" data-parsley-type="digits" data-parsley-type-message="Please enter valid inventory" id="quantity_{{$row}}" data-row="{{$row}}" onkeyup="checkInventory(this);">

                                      <span class="red" id="inventory_error_msg_{{$row}}"></span>
                                    </div>
                                    
                                  </td>

                                  <td class="remove-edtr">
                                 
                                    <div class="row">
                                       <textarea  class="form-control" placeholder="Enter Description" rows="1" name="sku_product_description[]" id="sku_product_description_{{$row}}"></textarea>

                                       <span id="err_product_desc_add_prod" class="red"></span>

                                    </div> 

                                  </td>

                                 <td>
                                    <button type="button" id="addMore" class="btn btn-success btn-circle" ><i class="fa fa-plus"></i> </button>
                                 </td>

                              </tr>
                           </tbody>
                        </table>
                      </div>
                        <div class="clearfix"></div>
                        <div class="form-group">

                           <div class="row">
                              <div class="col-md-4 text-left">
                                 <button type="button" data-dismiss="modal" class="fcbtn btn btn-info btn-outline btn-1c cancel_btn">Cancel </button>

                              </div>
                              <div class="col-md-4 text-center">
                              
                              </div>
                              <div class="col-md-4 text-right">
                                 <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_style_details" id="save_style_details" data-is-draft="no" data-flag="">Save & Next</button>
                              </div>
                        </div>
                     </div>
                     <div class="clearfix"></div>
                    </form>
                  </div>

                
                  <div role="tabpanel" id="messages1">
                    <form id="additional-img-frm">
                      {{ csrf_field() }}
                     <input type="hidden" name="product_id" id="additional_img_product_id">
                     <input type="hidden" name="images_is_draft" id="images_is_draft" value="">
                     <div class="row">
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">

                        <div class="white-box uploadimgpro">
                           <label for="input-file-now">Upload Product Image<i class="red">*</i> </label>
                           @php
                              $product_img_path = ""; 
                              $image_name = "";
                              $image_type = "category";
                              $is_resize = 0; 
                              $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                           @endphp
                           <input type="file" 
                            id="product_image"
                            name="product_image" 
                            class="dropify"
                            data-default-file="{{ $product_img_path }}"
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-max-file-size="2M" 
                            data-errors-position="outside"
                            data-parsley-required="true"  data-parsley-required-message="Please upload product image" /> 
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">

                        <div class="white-box uploadimgpro">
                           <label for="input-file-now">Upload Lifestyle Image<i class="red">*</i> </label>
                           @php
                              $product_img_path = ""; 
                              $image_name = "";
                              $image_type = "category";
                              $is_resize = 0; 
                              $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                           @endphp
                           <input type="file" 
                            id="lifestyle_image" 
                            name="lifestyle_image" 
                            class="dropify"
                            data-default-file="{{ $product_img_path }}"
                          data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                          data-max-file-size="2M" 
                          data-errors-position="outside"
                          data-parsley-required="true" data-parsley-required-message="Please upload lifestyle image"/> 
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">

                        <div class="white-box uploadimgpro">
                           <label for="input-file-now ">Upload Packaging Image<i class="red">*</i></label>
                           @php
                              $product_img_path = ""; 
                              $image_name = "";
                              $image_type = "category";
                              $is_resize = 0; 
                              $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                           @endphp
                           <input type="file" 
                            id="packaging_image" 
                            name="packaging_image" 
                            class="dropify"
                            data-default-file="{{ $product_img_path }}"
                          data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                          data-max-file-size="2M" 
                          data-errors-position="outside"
                          data-parsley-required="true" data-parsley-required-message="Please upload packaging image"/> 
                        </div>
                     </div>
                   </div>
                     <div class="clearfix"></div>                     
                        <div class="form-group">

                     <div class="row">
                        <div class="col-md-4 text-left">
                           <button type="button" data-dismiss="modal" class="fcbtn btn btn-info btn-outline btn-1c">Cancel </button>

                        </div>
                        <div class="col-md-4 text-center">
                          
                        </div>
                        <div class="col-md-4 text-right">
                           <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_additional_img" id="save_additional_img" data-is-draft="no">Save & Next</button>
                        </div>
                         </form>
                  </div>
                   </div>
              </div>
                
                
                  <div role="tabpanel" id="settings1">
                    <form id="category-frm">
                      {{ csrf_field() }}
                      <input type="hidden" name="category_product_id" id="category_product_id">
                      <input type="hidden" name="category_is_draft" id="category_is_draft" value="">

                      
                        <div class="col-sm-12 col-md-12 col-lg-4">
                          <div class="form-group">
                          <label for="category_id">Category<i class="red">*</i></label>
                           
                          <select id="category_id" onchange="get_subcategory($(this));"  name="category_id" class="form-control" data-parsley-required="true" data-parsley-required-message="Please select category">
                            <option value="">Select Category</option>
                            @if(isset($category_arr) && count($category_arr)>0)
                              @foreach($category_arr as $category)
                                <option value="{{$category['id'] or 0}}"> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                              @endforeach
                            @endif
                          </select>
                          <span class='red'>{{ $errors->first('category_name') }}</span>
                        </div>
                        </div>
                      
                        
                        
                        <div class="col-sm-12 col-md-12 col-lg-4">
                          <div class="form-group">
                          <label for="second_category">Sub Categories</label>
                           
                          <select class="select2 select2-multiple" multiple="multiple" data-placeholder="Select Subcategories" name="sub_category[]" id="second_category">
                          </select>
                          <span class='red'>{{ $errors->first('sub_category') }}</span>
                        </div>
                        </div>
                      

                      
                        <div class="col-sm-12 col-md-12 col-lg-4">
                          <div class="form-group">
                          <label  for="shipping_charges">Shipping Charges ($) <i class="red">*</i></label>
                            
                          <input type="text" class="form-control" placeholder="Enter Shipping charges" name="shipping_charges" id="shipping_charges" data-parsley-required="true"  data-parsley-type="number" data-parsley-required-message="Please enter shipping charges" data-parsley-type-message="Please select valid shipping charges"/>
                          <span class='red'>{{ $errors->first('shipping_charges') }}</span>
                        </div>
                        </div>
                      

                        <div class="clearfix"></div> 
                      
                      
                        <div class="col-md-12">
                          <div class="form-group">
                          <label  for="shipping_type">Shipping Discount Type <i class="red">*</i></label>
                         
                          <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select shipping discount type" id="shipping_type" name="shipping_type">
                            <option value=""> Select Type </option>                            
                            <option value="1"> Free Shipping</option>
                            <option value="2"> % off</option>
                            <option value="3"> $ off</option>
                          </select>
                          </div>
                        </div>
                      
                      <div class="form-group" id="add_shipping_amounts">
                        <div id="err_ship_min_amount" class="red"></div>  
                      </div>

                      <div class="clearfix"></div> 

                       
                        <div class="col-md-12">
                          <div class="form-group">
                          <label  for="shipping_type">Product Discount Type</label>
                          <div>
                            <select class="form-control" id="product_discount" name="product_discount_type">
                              <option value="">Select Type</option>
                              <option value="1"> % off</option>
                              <option value="2"> $ off</option>
                            </select>
                          </div>
                          </div>
                        </div>
                        

                        <div class="form-group" id="add_product_dis_amt">
                           <div class="clearfix"></div> 
                        </div>
                        <div class="clearfix"></div>                     
                        <div class="form-group">

                           <div class="row">
                              <div class="col-md-4 text-center">
                              </div>
                              <div class="col-md-4 text-center">
                                 <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_category" id="save_category" data-is-draft="no">Save</button>
                              </div>
                         </div>
                       </div>
                  <div class="clearfix"></div>
                   </form>
                  </div>
               </div>

              </div>

              <div class="modal-footer">
                
                 <a href="" class="btn btn-info waves-effect">Close</a>
             </div>


            </div>
         </div>
          </div>
            
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
 </div> 
 </div>
 </div> 
</div>

<script type="text/javascript">
  var remote_url = '{{ url('/vendor/products/does_exists/sku_no') }}';
  var edit_remote_url = '{{ url('/vendor/products/does_exists_edit/sku_no') }}';

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

$(function () {

    $('#old_retail_price').on('keyup', function(){ 
       var elem       =  $('#old_retail_price').parsley();
       var elem_whole       =  $('#old_retail_price').parsley();
       var old_retail_price = $('#old_retail_price').val(); 
       var old_wholsale_price = $('#old_unit_wholsale_price').val();
       old_retail_price =  parseFloat(old_retail_price);
       old_wholsale_price = parseFloat(old_wholsale_price);
       var error_name = 'custom_error';       
       var price_error = 'price_error';
        if ($(this).val()!="" && $(this).val() == 0)
        {
            elem.removeError(error_name);
            elem.addError(error_name, {message:'Unit retail price should not be 0'});
        }
        else
        {
           elem.removeError(error_name);
        }

        if(old_wholsale_price>old_retail_price)
       {      

            elem_whole.removeError(price_error);
            elem_whole.removeError(error_name);
            elem_whole.addError(price_error, {message:'Unit wholesale price should be less than unit retail price '});
       } else {
            $("#old_unit_wholsale_price").removeClass('parsley-error');
            $(".parsley-price_error").html('');
            //elem_whole.removeError(error_name);
       }
        
    });
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

$('#edit_category').click(function(){


  //if ($('#edit-category-frm').parsley().validate() == false) return;


      /*-----------------validation for shipping charges---------------------------------*/
        var flag = 0;
        var min_amt_free_shiping   = ($('#old_free_ship_min_amount').val()) ? $('#old_free_ship_min_amount').val() : 0;
        var product_unit_price     = ($('#old_unit_wholsale_price').val()) ? $('#old_unit_wholsale_price').val() : 0;
        var ship_percent_off_value = ($(".ship_percent_off").val()) ? $('.ship_percent_off').val() : 0;
        var ship_dolar_off_value   = ($(".ship_dollar_off").val()) ? $('.ship_dollar_off').val() : 0;
        var shipping_charges       = ($('#old_shipping_charges').val()) ? $('#old_shipping_charges').val() : 0;



        if(parseFloat(product_unit_price) > parseFloat(min_amt_free_shiping))
        { 
             flag = 1;
           $('#err_ship_min_amount').text('Min amount for getting free shipping should be greater than unit wholesale price');

         // $('#err_unit_retail_price_edit').html('Min amount for getting free shipping should be greater than unit wholesale price');

         
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
          
          $('#error_min_product_amt').text('Min amount for getting product discount should be greater than unit wholesale price');
          flag = 1;
        }

          
        if(percent_off_product_dis > 100)
        {
            $('#error_per_off_product_amt').text('% off amount for getting product discount should be less than 100');
            flag = 1;
        }

         if (parseInt(product_unit_price) < parseInt(dolar_off_product_dis)) 
          {
            $('#error_per_off_product_amt').text('$ off amount for product discount should be less than unit wholesale price');
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

</script>

<!-- END add product modal-->

