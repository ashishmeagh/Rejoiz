 <!-- Edit Product model -->

 <div id="editModel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                {{--  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> --}}

                <!--Hidden field for get data only no need to sent it to server-->
                <input type="hidden" id="subcat_id"> 
                
                 <h4 class="modal-title" id="myModalLabel">Edit {{$module_title or ''}}</h4> </div>
             <div class="modal-body">
                  <div class="row">
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
                     <form id="old-item-details-frm">

                      <input type="hidden" name="product_id" id="edit_item_product_id" value="">
                        {{ csrf_field() }}
                     <div class="row">
                        <div class="col-md-12">                          
                           <div class="form-group">
                              <label class="col-md-12">Product Image</label>
                              <div class="col-md-12" id="product-primary-img">
                                <input type="hidden" name="old_product_image" id="old_product_image">
                                <input type="hidden" name="old_product_image_thumb" id="old_product_image_thumb">
                                <input type="hidden" name="is_active" id="old_product_is_active">

                                
                                 {{-- <input type="file" name="product_primary_image" id="old_product_primary_image" class="form-control dropify"  placeholder="Enter Product Name" data-parsley-errors-container="#err_primary_product_img" data-default-file="" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"  data-parsley-required="true"/> --}}
                                 <span class="text-danger" id="err_primary_product_img">{{ $errors->first('product_primary_image') }}</span>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <label class="col-md-12">Product Name<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <input type="text" class="form-control" placeholder="Enter Product Name" name="product_name" id="old_product_name" data-parsley-required="true" data-parsley-trigger="change"  />
                              </div>
                              <div class="clearfix"></div>
                           </div>


                            <div class="form-group">
                              <label class="col-md-12">Brand<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <select name="brand" class="form-control" data-parsley-required="true">
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
                              <div class="form-check bd-example-indeterminate">
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
                                       <input type="text" class="form-control" placeholder="Enter Case Quantity" name="case_qty" id="old_case_qty" data-parsley-required="true" data-parsley-type="number" data-parsley-min="1"/>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Wholesale Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Unit Wholesale Price" name="unit_wholsale_price" id="old_unit_wholsale_price" data-parsley-required="true" data-parsley-type="number" data-parsley-min="0" />
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Retail Price($)</label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Retail Price" name="retail_price" id="old_retail_price" data-parsley-type="number" data-parsley-min="0" />
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                           </div>
                           <div class="form-group">
                              <label class="col-md-12">Description</label>
                              <div class="col-md-12">
                                 <textarea class="form-control" placeholder="Enter Description" rows="5" name="product_description" id="old_product_description" {{-- data-parsley-trigger="keyup" data-parsley-minlength="10" data-parsley-required="true" --}}
                                 ></textarea>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="is_tester_available" id="old_is_tester_available" value="1">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Tester Available</span>
                                    </label>
                                 </div>
                              </div>
                              {{-- <div class="col-md-6">
                                 <div class="form-group ">
                                    <button class="fcbtn btn btn-success btn-outline btn-1c pull-right" id="save_items_details">Next Step</button>
                                 </div>
                                 <div class="clearfix"></div>
                              </div> --}}
                           </div>
                        </div>

                        <div class="form-group">
                        <div class="row">
                           {{-- <div class="col-md-4 text-center">
                              <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
                           </div> --}}
                         {{--   <div class="col-md-4 text-center">
                              <button class="fcbtn btn btn-success btn-outline btn-1c">Save as a Draft</button>
                           </div> --}}
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="old_save_items_details">Save</button>
                           </div>
                        </div>
                     </div>
                     </div>


                     <div class="clearfix"></div>
                      </form>
                  </div>
                
                  <div role="tabpanel" id="edit-profile1">
                     <form id="edit-style-frm">
                        {{ csrf_field() }}
                        <input type="hidden" name="product_id" id="edit-style_product_id" value=""> 
                     <div class="table-responsive">
                        <table class="table" id="style_and_diemension_tbl">
                           <thead>
                              <tr>
                                 <th>Image</th>
                                 <th>
                                    <select class="custom-select col-12" name="optionName" id="old_optionName" data-parsley-required="true">
                                       {{-- <option selected="" value="0">Color</option>
                                       <option value="1">Scent</option>
                                       <option value="2">Size</option>
                                       <option value="3">Material</option> --}}
                                    </select>
                                 </th>
                                 <th>SKU</th>
                                 <th>Weight</th>
                                 <th>Dimensions (length, width, height)</th>
                                 <th>&nbsp;</th>
                              </tr>
                           </thead>
                           <tbody id="style_sec_body"></tbody>
                        </table>
                        <div class="clearfix"></div>
                        <div class="form-group">
                        <div class="row">
                           {{-- <div class="col-md-4 text-center">
                              <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
                           </div> --}}
                          {{--  <div class="col-md-4 text-center">
                              <button class="fcbtn btn btn-success btn-outline btn-1c">Save as a Draft</button>
                           </div> --}}
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="edit_save_style_details">Save</button>
                           </div>
                        </div>
                     </div>
                     </div>
                     <div class="clearfix"></div>
                    </form>
                  </div>                
                
                  <div role="tabpanel" id="edit-messages1">
                     <form id="edit-additional-img-frm">
                        {{ csrf_field() }}
                      <input type="hidden" name="product_id" id="edit-additional_img_product_id">
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now" id="prod-img">Upload Product Image </label>
                           {{-- <input type="file" 
                            id="old_product_image"
                            name="old_product_image" 
                            class="dropify"
                            data-default-file=""
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-max-file-size="2M" 
                            data-errors-position="outside"
                            data-parsley-required="true" />  --}}
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now" id="lifestyle-img">Upload Lifestyle Image </label>
                           {{-- <input type="file" 
                            id="old_lifestyle_image" 
                            name="old_lifestyle_image" 
                            class="dropify"
                            data-default-file=""
                           data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                           data-max-file-size="2M" 
                           data-errors-position="outside"
                           data-parsley-required="true" />  --}}
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now" id="packaging-img">Upload packaging image</label>
                           {{-- <input type="file" 
                           id="old_packaging_image" 
                            name="old_packaging_image" 
                            class="dropify"
                            data-default-file=""
                           data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                           data-max-file-size="2M" 
                           data-errors-position="outside"
                           data-parsley-required="true" />  --}}
                        </div>
                     </div>
                     <div class="clearfix"></div>                     
                        <div class="form-group">
                        <div class="row">
                           {{-- <div class="col-md-4 text-center">
                              <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
                           </div> --}}
                          {{--  <div class="col-md-4 text-center">
                              <button class="fcbtn btn btn-success btn-outline btn-1c">Save as a Draft</button>
                           </div> --}}
                           <div class="col-md-4 text-center">
                              <button type="button" class="fcbtn btn btn-success btn-outline btn-1c" id="edit_additional_img">Save</button>
                           </div>
                        </div>
                     </div>
                    </form>
                  </div>               
                
                  <div role="tabpanel" id="edit-settings1">
                     <form id="edit-category-frm">
                        {{ csrf_field() }}
                        <input type="hidden" name="product_id" id="edit-category_product_id">

                        <div class="form-group">
                        <div class="col-md-6">
                          <label  for="old_category_id">Category <i class="red">*</i></label>
                           <div >
                              <select id="old_category_id" onchange="get_subcategory_for_edit($(this));"  name="category_id" class="form-control" data-parsley-required="true">
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
                           
                          <div class="col-md-6">

                           <label for="second_category">Sub Categories <i class="red">*</i></label>
                           
                              <select class="select2 select2-multiple" multiple="multiple" data-placeholder="Choose" name="sub_category[]" id="edit_second_category" data-parsley-required="true">
                              </select>                              
                              <span class='red'>{{ $errors->first('sub_category') }}</span>
                           </div>
                           <div class="clearfix"></div> 


                           
                        </div>

                        <div class="form-group">
                           
                          <div class="col-md-6">
                            <label  for="old_shipping_charges">Shipping Charges ($) <i class="red">*</i></label>
                            <div >
                              <input type="text" class="form-control" placeholder="Enter Shipping Amount" name="shipping_charges" id="old_shipping_charges" data-parsley-required="true"  data-parsley-type="number"/>
                              <span class='red'>{{ $errors->first('shipping_charges') }}</span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <label  for="old_shipping_type">Shipping Type <i class="red">*</i></label>
                            <div>
                              <select class="form-control" data-parsley-required="true" id="old_shipping_type" name="shipping_type">
                                <option value="">Select Type </option>
                                <option value="1"> Free Shipping</option>
                                <option value="2"> % off</option>
                                <option value="3"> $ off</option>
                              </select>
                            </div>
                          </div>


                          <div class="clearfix"></div> 
                        </div>
                        <div class="form-group" id="shipping_amounts">
                          
                           <div class="clearfix"></div> 
                        </div>
                        <div class="clearfix"></div>                     
                        <div class="form-group">
                        <div class="row">
                           {{-- <div class="col-md-4 text-center">
                              <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
                           </div> --}}
                           {{-- <div class="col-md-4 text-center">
                              <button class="fcbtn btn btn-success btn-outline btn-1c">Save as a Draft</button>
                           </div> --}}
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
                 {{-- <button type="button" class="btn btn-info waves-effect" data-dismiss="modal">Close</button> --}}
                 <a href="" class="btn btn-info waves-effect">Cancel</a>
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
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="display: none;">



<div class="modal-dialog modal-lg">
   <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         <h3 class="modal-title" id="myLargeModalLabel">Add Product</h3>
      </div>
      <div class="modal-body">

         <div class="row">
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

                     <div class="row">
                        <div class="col-md-12">
                           {{-- <div class="img-my-shop-prdcs">
                              <img src="https://justherbs.in/wp-content/uploads/2019/06/Website-Banner-2.jpg" alt="">
                           </div> --}}
                           <div class="form-group">
                              <label class="col-md-12">Product Image</label>
                              <div class="col-md-12">
                                 <input type="file" name="product_primary_image" id="product_primary_image" class="form-control dropify"  placeholder="Enter Product Name"  data-default-file="" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"  data-parsley-required="true"
                                 data-parsley-errors-container="#err_primary_product_img">
                                 <span class="text-danger" id="err_primary_product_img">{{ $errors->first('product_primary_image') }}</span>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">
                              <label class="col-md-12">Product Name<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <input type="text" class="form-control" placeholder="Enter Product Name" name="product_name" id="product_name" data-parsley-required="true" value="{{old('product_name')}}"/>

                              </div>
                              <div class="clearfix"></div>
                           </div>

                           <div class="form-group">
                              <label class="col-md-12">Brand<i class="red">*</i></label>
                              <div class="col-md-12">
                                 <select name="brand" class="form-control" data-parsley-required="true">
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
                              <div class="form-check bd-example-indeterminate">
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
                                       <input type="text" class="form-control" placeholder="Enter Case Quantity" name="case_qty" id="case_qty" data-parsley-required="true" data-parsley-type="number" data-parsley-min="1"/>
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Wholesale Price($)<i class="red">*</i></label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Unit Wholesale Price" name="unit_wholsale_price" id="unit_wholsale_price" data-parsley-required="true" data-parsley-type="number" data-parsley-min="0" />
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-12">Unit Retail Price($)</label>
                                    <div class="col-md-12">
                                       <input type="text" class="form-control" placeholder="Enter Retail Price" name="retail_price" id="retail_price"  data-parsley-type="number" data-parsley-min="0" />
                                    </div>
                                    <div class="clearfix"></div>
                                 </div>
                              </div>
                           </div>
                           <div class="form-group">
                              <label class="col-md-12">Description</label>
                              <div class="col-md-12">
                                 <textarea class="form-control" placeholder="Enter Description" rows="5" name="product_description" id="product_description" {{-- data-parsley-trigger="keyup" data-parsley-minlength="10" data-parsley-required="true" --}}
                                 ></textarea>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        <div class="form-group">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-check">
                                    <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="is_tester_available" id="is_tester_available" value="1">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Tester Available</span>
                                    </label>
                                 </div>
                              </div>
                              {{-- <div class="col-md-6">
                                 <div class="form-group ">
                                    <button class="fcbtn btn btn-success btn-outline btn-1c pull-right" id="save_items_details">Next Step</button>
                                 </div>
                                 <div class="clearfix"></div>
                              </div> --}}
                           </div>
                        </div>

                        <div class="form-group">
			               <div class="row">
			                  <div class="col-md-4 text-center">
			                     <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button  type="button" class="fcbtn btn btn-success btn-outline btn-1c save_items_details" data-is-draft="yes" >Save as a Draft</button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_items_details" id="save_items_details" data-is-draft="no">Save</button>
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
                                 <th>Image</th>
                                 <th>
                                    <select class="custom-select col-12" name="optionName" id="inlineFormCustomSelect" data-parsley-required="true">
                                       <option selected="" value="0">Color</option>
                                       <option value="1">Scent</option>
                                       <option value="2">Size</option>
                                       <option value="3">Material</option>
                                    </select>
                                 </th>
                                 <th>SKU</th>
                                 <th>Weight</th>
                                 <th>Dimensions (length, width, height)</th>
                                 <th>&nbsp;</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td width="150px">
                                    <div class="img-shop-tbl nw-in-shp">
                                       <input type="file" 
                                       	class="form-control dropify" 
                                       	name="product_image[]"
                                        data-default-file=""
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-max-file-size="2M" 
                                        data-errors-position="outside"
                                        data-parsley-required="true"
                                        >           
                                    </div>
                                 </td>
                                 <td>
                                    <input type="text" class="form-control" placeholder="Option" name="option[]" data-parsley-required="true">
                                 </td>
                                 <td>                                    
                                    <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" name="sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/maker/products/does_exists/sku_no') }}"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="SKU no already exists">
                                 </td>
                                 <td>
                                 	<input type="text" class="form-control" placeholder="Weight" name="weight[]" data-parsley-required="true" data-parsley-type="digits">
                                 </td>
                                 <td>
                                    <div class="inline-blk-jst">
                                       <input type="text" class="form-control" placeholder="Length" name="length[]" data-parsley-required="true" data-parsley-type="digits">
                                       <input type="text" class="form-control" placeholder="width" name="width[]" data-parsley-required="true" data-parsley-type="digits">
                                       <input type="text" class="form-control" placeholder="height" name="height[]" data-parsley-required="true" data-parsley-type="digits">
                                    </div>
                                 </td>
                                 <td>
                                    <button type="button" id="addMore" class="btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                        <div class="clearfix"></div>
                        <div class="form-group">
			               <div class="row">
			                  <div class="col-md-4 text-center">
			                     <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button type="button"  class="fcbtn btn btn-success btn-outline btn-1c save_style_details" data-is-draft="yes">Save as a Draft</button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_style_details" id="save_style_details" data-is-draft="no">Save</button>
			                  </div>
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

                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now">Upload Product Image </label>
                           <input type="file" 
                            id="product_image"
                            name="product_image" 
                            class="dropify"
                            data-default-file=""
                            data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                            data-max-file-size="2M" 
                            data-errors-position="outside"
                            data-parsley-required="true" /> 
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now">Upload Lifestyle Image </label>
                           <input type="file" 
                            id="lifestyle_image" 
                            name="lifestyle_image" 
                            class="dropify"
                            data-default-file=""
	                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
	                        data-max-file-size="2M" 
	                        data-errors-position="outside"
	                        data-parsley-required="true" /> 
                        </div>
                     </div>
                     <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
                        <div class="white-box">
                           <label for="input-file-now ">Upload packaging image</label>
                           <input type="file" 
                          	id="packaging_image" 
                            name="packaging_image" 
                            class="dropify"
                            data-default-file=""
	                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
	                        data-max-file-size="2M" 
	                        data-errors-position="outside"
	                        data-parsley-required="true" /> 
                        </div>
                     </div>
                     <div class="clearfix"></div>                     
                        <div class="form-group">
			               <div class="row">
			                  <div class="col-md-4 text-center">
			                     <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button class="fcbtn btn btn-success btn-outline btn-1c save_additional_img" data-is-draft="yes">Save as a Draft</button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_additional_img" id="save_additional_img" data-is-draft="no">Save</button>
			                  </div>
			               </div>
			            </div>
                    </form>
                  </div>
                
                
                  <div role="tabpanel" id="settings1">
                  	<form id="category-frm">
                  		{{ csrf_field() }}
                  		<input type="hidden" name="category_product_id" id="category_product_id">
                      <input type="hidden" name="category_is_draft" id="category_is_draft" value="">
                  		<div class="form-group">
                        <div class="col-md-6">
                          <label for="category_id">Category<i class="red">*</i></label>
                           
                          <select id="category_id" onchange="get_subcategory($(this));"  name="category_id" class="form-control" data-parsley-required="true">
                            <option value="">Select Category</option>
                            @if(isset($category_arr) && count($category_arr)>0)
                              @foreach($category_arr as $category)
                                <option value="{{$category['id'] or 0}}"> {{ $category['category_name']?ucfirst($category['category_name']):'NA'}}</option>
                              @endforeach
                            @endif
                          </select>
                          <span class='red'>{{ $errors->first('category_name') }}</span>
                        </div>
                      

                      
                        <div class="col-md-6">
                          <label for="second_category">Sub Categories <i class="red">*</i></label>
                           
                          <select class="select2 select2-multiple" multiple="multiple" data-placeholder="Choose" name="sub_category[]" id="second_category" data-parsley-required="true">
                          </select>
                          <span class='red'>{{ $errors->first('sub_category') }}</span>
                        </div>
                        <div class="clearfix"></div> 
                      </div>
                      
                      <div class="form-group">
                           
                        <div class="col-md-6">
                          <label  for="shipping_charges">Shipping Charges ($) <i class="red">*</i></label>
                            
                          <input type="text" class="form-control" placeholder="Enter Shipping Amount" name="shipping_charges" id="shipping_charges" data-parsley-required="true"  data-parsley-type="number"/>
                          <span class='red'>{{ $errors->first('shipping_charges') }}</span>
                            
                        </div>

                        <div class="col-md-6">
                          <label  for="shipping_type">Shipping Type <i class="red">*</i></label>
                         
                          <select class="form-control" data-parsley-required="true" id="shipping_type" name="shipping_type">
                            <option selected="selected"> Select Type </option>
                            <option value="1"> Free Shipping</option>
                            <option value="2"> % off</option>
                            <option value="3"> $ off</option>
                          </select>
                        </div>


                        <div class="clearfix"></div> 
                      </div>
                      <div class="clearfix"></div> 
                      <div class="form-group" id="add_shipping_amounts">
                          
                           <div class="clearfix"></div> 
                        </div>
                        <div class="clearfix"></div>                     
                        <div class="form-group">
			               <div class="row">
			                  <div class="col-md-4 text-center">
			                     <button type="button" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
			                  </div>
			                  <div class="col-md-4 text-center">
			                     <button type="button" class="fcbtn btn btn-success btn-outline btn-1c save_category" data-is-draft="yes">Save as a Draft</button>
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
         </div>
         {{-- <div class="modal-footer">
            <div class="form-group">
               <div class="row">
                  <div class="col-md-4 text-center">
                     <button class="fcbtn btn btn-danger btn-outline btn-1c">Cancel </button>
                  </div>
                  <div class="col-md-4 text-center">
                     <button class="fcbtn btn btn-success btn-outline btn-1c">Save as a Draft</button>
                  </div>
                  <div class="col-md-4 text-center">
                     <button class="fcbtn btn btn-success btn-outline btn-1c">Save</button>
                  </div>
               </div>
            </div>
         </div> --}}
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
  var remote_url = '{{ url('/maker/products/does_exists/sku_no') }}';
  var edit_remote_url = '{{ url('/maker/products/does_exists_edit/sku_no') }}';

</script>

<!-- END add product modal-->

