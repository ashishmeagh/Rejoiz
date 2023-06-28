        @extends('front.layout.master')
        @section('main_content')
    
        <form hidden id="frmFilter">

          <input type="hidden" name="price:low" value="{{ $search_value['price:low'] or ''  }}" />
          <input type="hidden" name="price:high" value="{{ $search_value['price:high'] or ''  }}" />
          
          <input type="hidden" name="vendor_minimum_high" value="{{$search_value['vendor_minimum_high'] or ''}}" />
          
          <input type="hidden" name="vendor_minimum_low" value="{{$search_value['vendor_minimum_low'] or ''}}"/>

          <input type="hidden" name="free_shipping" value="{{ $search_value['free_shipping'] or '' }}" />
          
          <input type="hidden" name="percent_of" value="{{ $search_value['percent_of'] or '' }}" />
          
          <input type="hidden" name="doller_of" value="{{ $search_value['doller_of'] or '' }}" />

          <input type="hidden" name="lead_time_min" value="{{ $search_value['lead_time_min'] or '' }}" />

          <input type="hidden" name="lead_time_max" value="{{ $search_value['lead_time_max'] or '' }}" />

          <input type ="hidden" name = brands[] value="">

          <input type="hidden" name="category_id" value="{{ $search_value['category_id'] or '' }}" />
          <input type="hidden" name="subcategory" value="{{ $search_value['subcategory'] or '' }}" />
          <input type="hidden" name="search_type" value="{{ $search_value['search_type'] or '' }}" />
          <input type="hidden" name="search_term" value="{{ $search_value['search_term'] or '' }}" />

        </form>

        <style>
        .dropdown {
          position: relative;
          display: inline-block;
        }
        .pagination-bar {display:flex; align-items:center; justify-content:space-between; margin-top:10px;}
        .pagination-bar .pagination {margin-right:20px; padding:0px; margin:0px;}
        .goto-page .form-group {display:flex; align-items:center; white-space:nowrap; margin-bottom:0px;}
        .inner-goto {display:flex; align-items:center; margin-right:10px;}
        .inner-goto input {width:80px; height:24px;}
        .inner-goto .gt-button {padding: 1px 10px !important;}
        .goto-page .form-group span {margin-right:10px;}
        </style>
        @php 
        if(Session::has('category')){Session::forget('category');}

        if(str_contains(Request::fullUrl(), 'category_id'))
        {
          Session::put('category',isset($search_value['category_id'])?$search_value['category_id']:'');
        }
        else
        {
          Session::forget('category');
        }
        @endphp
        <div class="listing-main-div mobile-list-main search-listing-main-div-page search-pagelist-space">
            <div class="container-fluid">
                <div>
                
                @include('admin.layout._operation_status')

                @include('front.search._front_sidebar')
                 
                    <div class="col-sm-12 col-md-8 col-lg-9 pro-list-width-custom pro-list-width-custom_new">
                     
                        @include('front.search._search_readcrum_header')
                        @include('front.search._filters',['search_value' => $search_value,'brands_arr' => $brands_arr])
                        

                        <div class="product-section">
                            @php
                                
                                $login_user = Sentinel::check();
                                // dd($login_user);
                                if($login_user)
                                {
                                  $user_admin_role = $login_user->inRole('admin');  
                                  $user_representative = $login_user->inRole('representative');

                                }
                               
                            @endphp
                            <input type="hidden" name="user_admin_role" id="user_admin_role" value="{{$user_admin_role or ''}}">

                            <input type="hidden" name="user_representative" id="user_representative" value="{{$user_representative or ''}}">
                            <div class="row">
                                @if(isset($arr_data) && sizeof($arr_data)>0)

                                    @foreach($arr_data as $product)     


                                      <!-- Get vendor settings -->
                                      @php $maker_details = get_maker_all_details($product['_source']['user_id']); @endphp                                     
                                      <div class="col-xs-6 col-sm-4 col-md-4 col-lg-3 pro_listing_box_div">
                                          <div class="product-box ">
                                              <div class="heart-products text-center">
                                              @if($login_user==true)
                                                  @if($login_user->inRole('retailer') || $login_user->inRole('customer'))
                                                    @if(isset($fav_product_arr) && in_array($product['_source']['id'],$fav_product_arr))
                                                        <a href="javascript:void(0)" class="heart-active" data-id="{{isset($product['_source']['id'])?base64_encode($product['_source']['id']):0}}" data-type="product" onclick="confirmAction($(this),'remove');"><i class="fa fa-heart heart-active" title="Remove from favorite"></i></a> 
                                                      @else 
                                                        <a href="javascript:void(0)" class="heart-deactive" data-id="{{isset($product['_source']['id'])?base64_encode($product['_source']['id']):0}}" data-type="product" onclick="confirmAction($(this),'add');" ><i class="fa fa-heart-o" title="Add to favorite"></i></a>
                                                      @endif 
                                                  @endif                                        
                                              @else 
                                               <a href="javascript:void(0)" class="heart-deactive" data-id="{{isset($product['_source']['id'])?base64_encode($product['_source']['id']):0}}" data-type="product"  onclick="confirmAction($(this),'add');" title="Add to favorite"><i class="fa fa-heart-o"></i></a> 
                                              @endif
                                              </div>  
                                                @if($login_user==true && $login_user->inRole('admin') ==false && $login_user->inRole('representative') == false)
                                                   
                                                  @if($login_user->inRole('maker'))
                                                   
                                                    @if($login_user->id==$product['_source']['user_id'])
                                                    
                                                      <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['_source']['id'])}}&vendor_id={{base64_encode($product['_source']['user_id'])}}" class="img-block" onclick="showProcessingOverlay();">
                                                   
                                                    @else
                                                      <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($product['_source']['user_id'])}}" class="img-block" onclick="showProcessingOverlay();">
                                                    @endif
                                                    @else
                                                    
                                                      <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['_source']['id'])}}&vendor_id={{base64_encode($product['_source']['user_id'])}}" class="img-block" onclick="showProcessingOverlay();">
                                                    @endif
                                                 @else
                                                 <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['_source']['id'])}}&vendor_id={{base64_encode($product['_source']['user_id'])}}&brand_id={{base64_encode($product['_source']['brand'])}}" class="img-block" onclick="showProcessingOverlay();">
                                                 <!-- <a href="{{url('/')}}/signup_retailer/guest" class="img-block"> -->
                                                    @endif
                                                @php

                                                $product_img = false;


                                                $product_image_thumb_base_path  = base_path('storage/app/'.$product['_source']['product_image_thumb']);

                                                $product_image_base_path  = base_path('storage/app/'.$product['_source']['product_image']);

                                                if(file_exists($product_image_thumb_base_path))
                                                {

                                                  $product_img = image_resize($product_image_thumb_base_path,370,370,false,true);
                                                }
                                                elseif(file_exists($product_image_base_path))
                                                {


                                                  $product_img = image_resize($product_image_base_path,370,370,false,true);
                                                }
                                                else
                                                {
                                                  $product_img = url('/assets/images/no-product-img-found.jpg');
                                                }
                                                
                                                @endphp
                                                   
                                                    <img class="potrait" src="{{$product_img or ''}}" alt="Mawisam product">

                                                </a>
                                                <!-- <div class="product-hover"> -->
                                                   
                                                    @php
                                                    $login_user = Sentinel::check();
                                                    @endphp

                                                     {{-- @if($login_user==true && $login_user->inRole('admin') ==false) --}}

                                                        {{-- @if($login_user->inRole('representative') == true)
                                                          
                                                           <a href="javascript:void(0)" onclick="swal('Warning','Please login as retailer','warning');"><i class="fa fa-eye" aria-hidden="true"></i></a>

                                                        @else --}}

                                                          {{-- @if($login_user->inRole('maker')) --}}
                                                             {{--  @if($login_user->id==$product['_source']['user_id'])
                                                                <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['_source']['id'])}}&vendor_id={{base64_encode($product['_source']['user_id'])}}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                              @else
                                                                <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($product['_source']['user_id'])}}&brand_id={{base64_encode($product['_source']['brand'])}}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                              @endif
                                                          @else --}}
                                                            <!-- <a href="{{url('/')}}/vendor-details?product_id={{base64_encode($product['_source']['id'])}}&vendor_id={{base64_encode($product['_source']['user_id'])}}&brand_id={{base64_encode($product['_source']['brand'])}}"><i class="fa fa-eye" aria-hidden="true"></i></a> -->
                                                          {{-- @endif
                                                        
                                                        @endif
                                                    
                                                    @else

                                                     {{-- <a href="javascript:void(0)" class = "guest_url_btn"><i class="fa fa-eye" aria-hidden="true"></i></a> --}}

                                                     
                                                    

                                                    
                                                    
                                                   {{--  @endif --}}


                                                <!-- </div> -->
                                                <div class="product-info ptover">

                                                    <a href="#" title="{{isset($product['_source']['product_name'])?ucfirst($product['_source']['product_name']):""}}" class="brandnametitle tooltip-title">{{isset($product['_source']['product_name'])?($product['_source']['product_name']):""}}
    
                                                    </a>

                                              <!-- if get a quote enable then hide  inline price div -->

                                              @if($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)
                                                  
                                                  <div class="price-product inlineprice" style="display:none;">
                                                  </div>


                                              @else  

                                                    <div class="price-product inlineprice">
                                                     
                                                      @if($login_user == true)
                                                        @if((($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))   
                                                        <div class="retail_price_product inlineprice font-weight-normal">
                                                          <span class="pricewholsl">Price</span>
                                                          <div class="prices">
                                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                          </div>
                                                        </div>
                                                        @elseif((($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                        <div class="suggested_price inlineprice font-weight-normal">
                                                            <span class="pricewholsl">Price</span>                                                           
                                                            <div class="prices">
                                                              <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                            </div> 
                                                        </div>    
                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)

                                                        @elseif(($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        <div class="suggested_price inlineprice font-weight-normal">
                                                            <span class="pricewholsl">Price</span>                                                           
                                                            <div class="prices">
                                                              <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                            </div> 
                                                        </div>
                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        <div class="retail_price_product inlineprice font-weight-normal">
                                                          <span class="pricewholsl">Price</span>
                                                          <div class="prices">
                                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                          </div>
                                                        </div>
                                                        @endif                                                       
                                                        @else
                                                          @if(($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || ($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                          <div class="suggested_price inlineprice font-weight-normal">
                                                            <span class="pricewholsl">Price</span>
                                                            <div class="prices">
                                                              <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                            </div>
                                                          </div>                        
                                                          @elseif($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)                                                            
                                                          @elseif($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                          <div class="suggested_price inlineprice font-weight-normal">
                                                            <span class="pricewholsl">Price</span>
                                                            <div class="prices">
                                                              <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}
                                                            </div>
                                                          </div>
                                                          @endif
                                                        @endif
                                                       
                                                     </div>
                                                   
                                                @endif
                                                   
                                                    <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($product['_source']['user_id'])}}" title="{{$product['_source']['brand_name']}}" class="title-product">{{isset($product['_source']['brand_name'])?($product['_source']['brand_name']):""}}</a>



                                                @php 
                                                    
                                                    $temp_product_id = isset($product['_source']['id'])?$product['_source']['id']:'0';

                                                    $sku = get_sku($product['_source']['id']);
                                                    
                                                    $temp_all_sku = get_all_sku($temp_product_id);
                                                   
                                                    
                                                    $temp_all_sku = array_column($temp_all_sku, 'quantity');


                                                    $product_inventory = array_sum($temp_all_sku);

                                                 
                                                    $is_in_stock = check_moq_inventory($product['_source']['id']);
                                                

                                                @endphp

                                                @if(isset($product_inventory) && $product_inventory == 0 || ($login_user == true &&  ($login_user->inRole('retailer')) && $is_in_stock == false))
                                                   <span class="red outofstock_listing">Out of stock</span>

                                                @endif


                                                 <!-- Get a Quote button -->

                                                @if((isset($maker_details['is_get_a_quote']) && $maker_details['is_get_a_quote'] == 1) || (isset($product['_source']['unit_wholsale_price']) && $product['_source']['unit_wholsale_price'] <= 0))

                                                  <div class="button-login-pb" >

                                                    @php

                                                     $vendor_email = get_user_email($product['_source']['user_id']);

                                                     $vendor_name = isset($product['_source']['user_id'])?get_user_name($product['_source']['user_id']):'';
                                                    @endphp

                                                        
                                                      <a href="javascript:void(0)" 
                                                         data-product-name="{{$product['_source']['product_name'] or ''}}" 
                                                         data-product-dec="{{$product['_source']['description'] or ''}}" 
                                                         data-company-name="{{$maker_details['company_name'] or ''}}" 
                                                         data-product-id="{{$product['_source']['id'] or ''}}" 

                                                         data-vendor-id = "{{$product['_source']['user_id'] or ''}}"

                                                         data-vendor-email = "{{$vendor_email or ''}}"
                                                         data-vendor-name = "{{$vendor_name or ''}}"

                                                         onclick="openGetAQuoteModal(this)"  class="gt-button" id="get-a-quote-modal" >Get A Quote</a>
                                                        <div class="clearfix"></div>
                                                  </div>

                                                @endif




                                                  {{--   @if($login_user==true && $login_user->inRole('admin') ==false && $login_user->inRole('representative') == false)

                                                        @if($login_user->inRole('maker'))
                                                          @if($login_user->id == $product['_source']['user_id']) 
                                                          <div class="price-product"><i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}</div>
                                                          @endif
                                                        @else
                                                          <div class="price-product"><i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['_source']['unit_wholsale_price'])?num_format($product['_source']['unit_wholsale_price']) : ''}}</div>
                                                        @endif
                                                  
                                                    @endif --}}



                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else    
                                   
                                  @if(Request::has('search_term')=="false")
                                      <div class="col-md-12"><div class="not-found-data">No results,
                                    We couldn’t find exact matches for"{{ Request::input('search_term')}}"<br>Try searching other keywords.</div>
                                    @elseif(Request::has('category_id') && Request::has('subcategory') && Request::has('thirdsubcategory') && Request::has('fourthsubcategory'))
                                      @php
                                        $third_sub_category_name = base64_decode(Request::input('fourthsubcategory'));
                                        $third_sub_category_name = get_third_subcategory_name($third_sub_category_name);
                                      @endphp
                                      <div class="col-md-12"><div class="not-found-data">No results,
                                      We couldn’t find products of subcategory <b>"{{$third_sub_category_name}}"</b></div>
                                    
                                    @elseif(Request::has('category_id') && Request::has('subcategory') && Request::has('thirdsubcategory'))
                                      @php
                                        $sec_sub_category_name = base64_decode(Request::input('thirdsubcategory'));
                                        $sec_sub_category_name = get_second_subcategory_name($sec_sub_category_name);
                                      @endphp
                                      <div class="col-md-12"><div class="not-found-data">No results,
                                      We couldn’t find products of subcategory <b>"{{$sec_sub_category_name}}"</b></div>
                                    
                                    @elseif(Request::has('category_id') && Request::has('subcategory'))
                                      @php
                                        $sub_category_name = base64_decode(Request::input('subcategory'));
                                        $sub_category_name = get_subcategory_name($sub_category_name);
                                      @endphp
                                      <div class="col-md-12"><div class="not-found-data">No results,
                                      We couldn’t find products of subcategory <b>"{{$sub_category_name}}"</b></div>
                                    
                                    @elseif(Request::has('category_id'))
                                      @php
                                        $category_name = base64_decode(Request::input('category_id'));
                                        $category_name = get_catrgory_name($category_name);
                                      @endphp 
                                       <div class="col-md-12"><div class="not-found-data">No results,
                                      We couldn’t find products of category <b>"{{$category_name}}"</b></div> 
                                    @endif

                                </div>
                                @endif   

                            </div>
                        </div>

                        <div class="pagination-bar">

                           @if(!empty($arr_pagination))                             
                            {{$arr_pagination->render()}}    
                           @if($arr_pagination->hasPages())
                            <div class="goto-page">
                              <div class="form-group">
                                  <div class="inner-goto">
                                    <span>Go to</span>
                                  <input type="text" name="search_page_no" id="search_page_no" class="form-control" value="">
                                  </div>
                                  <div class="inner-goto">
                                    <span>Page</span> 
                                  <button class="gt-button" onclick="redirect_to_page()">Go</button>
                                  </div>
                              </div>
                            </div> 
                            @endif  
                           @endif 

                        </div>

                    </div>
                </div>
            </div>
        </div>

<!-- Get a Quote Modal -->
<div class="modal fade vendor-Modal" id="get_a_Quote" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Get A Quote</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     
      <div class="modal-body">
        <form method="post" id="getaQuote"> 
          {{csrf_field()}}
          <div id="page-wrapper">
            <div class="container-fluid">
              <div class="get-qoute-modal-body">
              <div class="get-qoute-row">              
                <div class="col-get-qoute">Vendor Name</div>

               {{--  @php
                 
                  $first_name = isset($maker_details['first_name'])?$maker_details['first_name']:'';
                  $last_name  = isset($maker_details['last_name'])?$maker_details['last_name']:'';

                  $full_name = $first_name.' '.$last_name;
                @endphp --}}

                <div class="col-get-qoute-right" id="vendor_company_name"></div>
              </div>
              <div class="get-qoute-row">
                <div class="col-get-qoute">Product Name</div>
                <div class="col-get-qoute-right" id="vendor_product_name"></div>
              </div>

              <div class="get-qoute-row">
                <div class="col-get-qoute">Description</div>
                <div class="col-get-qoute-right description-content" id="product_description"></div>
              </div>
              </div>              
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Product Quantity <span class="text-danger">*</span></label>
                      <div>
                        <input type="number" min="1" maxlength="9" pattern="^[0-9]*$" data-parsley-type="digits" class="form-control" placeholder="Enter Product Quantity" id="quote_quantity" name="quote_quantity" data-parsley-required="true" data-parsley-required-message="Please enter product quantity." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Name <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="100" class="form-control" placeholder="Enter name" id="quote_name" name="quote_name" data-parsley-required="true" data-parsley-required-message="Please enter name." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">                
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Email <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" class="form-control" placeholder="Enter email" id="quote_email" name="quote_email" data-parsley-required="true" data-parsley-required-message="Please enter email." data-parsley-type="email" data-parsley-type-message="please enter valid email." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Contact Number <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="20" class="form-control" placeholder="Enter contact number" id="quote_contact_no" name="quote_contact_no" data-parsley-required="true" data-parsley-required-message="Please contact number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." data-parsley-minlength="10" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Expected Delivery Days <span class="text-danger">*</span></label>
                    <div>
                      <input type="number" min="0" maxlength="5" pattern="^[0-9]*$" data-parsley-type="digits" data-parsley-type-message="Please enter numbers" class="form-control" placeholder="Enter expected delivery days" id="quote_no_of_days" name="quote_no_of_days" data-parsley-required="true" data-parsley-required-message="Please enter number of days to expect delivery." value="">
                    </div>
                  </div>                
                </div>

                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Influencer Code <span class="text-danger">*</span></label>
                    <div>
                      <input type="text" class="form-control" placeholder="Enter Your Rejoiz influencer Code" id="influencer_code" name="influencer_code" data-parsley-required="true" data-parsley-required-message="Please enter rejoiz influencer code."
                      data-parsley-maxlength="50"
                       value="">
                    </div>
                  </div>                
                </div>
              </div> 
              <div class="row">
                <div class="col-lg-12">  
                  <div class="form-group">              
                    <label>Additional Notes</label>
                      <div>
                      <textarea class="form-control" name="quote_additional_notes" id="quote_additional_notes" placeholder="Add additional notes" data-parsley-maxlength="500" data-parsley-maxlength-message="Only a maximum of 500 characters is allowed." ></textarea>
                      </div>
                    </div>                
                  </div>
              </div>
            </div>
          </div>

          <input type="hidden" name="vendor_id" id="vendor_id" value="">
          <input type="hidden" name="company_name" id="company_name" value="">

          <input type="hidden" name="product_id" id="product_id" value="">
          <input type="hidden" name="vendor_email" id="vendor_email" value="">
          <input type="hidden" name="vendor_name" id="vendor_name" value="">

        </form>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">        
        <button type="button" id="sendGetaQuote" class="btn btn-submit-get">Submit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>     
      </div>
    </div>
  </div>
</div>




<script type="text/javascript">

  $(document).ready(function(){

      $("li.disabled").click(function(e){                   
                   location.reload();
      });

              var guest_url = "{{url('/')}}";
              var guest_redirect_url = window.location.href;



              $(".guest_url_btn").click(function(){

                $.ajax({
                        url:guest_url+'/set_guest_url',
                        method:'GET',
                        data:{guest_link:guest_redirect_url},
                        dataType:'json',
                   
                        
                        success:function(response)
                        {
                          if(response.status=="success")
                          {
                            // $(location).attr('href', guest_url+'/signup_retailer/guest')
                            $(location).attr('href', guest_url+'/login/guest')
  }

                        }
                    });
              });

              $("ul.pagination > li").each(function(index,elem){
                $(elem).on("click",function(e){
                  // e.preventDefault();

                  showProcessingOverlay();
                })
              });
        });


function confirmAction(ref,action,is_maker="")
{
    var text   = confirmButtonText = "";
   if(is_maker=="")
   { 
    if(action =='remove')
    {
      var text = 'Are you sure? Do you want to remove product from your favorite list.';
      var confirmButtonText = 'OK';
    } 

    if(action=='add')
    {
      var text = 'Are you sure? Do you want to add product to your favorite list.';
      var confirmButtonText = 'OK';
    }
   }
   else
   {
      if(action =='remove')
    {
      var text = 'Are you sure? Do you want to remove vendor from your favorite list.';
      var confirmButtonText = 'OK';
    } 

    if(action=='add')
    {
      var text = 'Are you sure? Do you want to add vendor to your favorite list.';
      var confirmButtonText = 'OK';
    }

   } 

    swal({
    title: "Need Confirmation",
    text: text,
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: confirmButtonText,
    closeOnConfirm: false
  },
  function(){
    if(action=='remove')
    removeFromFavorite(ref);
    if(action=='add')
    addToFavorite(ref);  
  });
}  

/* new code for add to favorite and remove to favrite*/
function addToFavorite(ref)
{   
    var guest_url = "{{url('/')}}";
    var guest_redirect_url = window.location.href;
   

    var id   = $(ref).attr('data-id');
    var type = $(ref).attr('data-type');
    var csrf_token = $("input[name=_token]").val();

    var logged_in_user  = '{{$login_user}}';

    var is_admin_exist          = $('#user_admin_role').val();
    var is_representative_exist =  $('#user_representative').val();

    

    if(logged_in_user == '')
    {
           $.ajax({
                url:guest_url+'/set_guest_url',
                method:'GET',
                data:{guest_link:guest_redirect_url},
                dataType:'json',
           
                
                success:function(response)
                {
                  if(response.status=="success")
                  {
                    // $(location).attr('href', guest_url+'/signup_retailer/guest')
                    $(location).attr('href', guest_url+'/login/guest')

                  }

                }
            });
    }
    
    else if(is_admin_exist == 1 || is_representative_exist == 1)
    {
           $.ajax({
                url:guest_url+'/set_guest_url',
                method:'GET',
                data:{guest_link:guest_redirect_url},
                dataType:'json',
           
                
                success:function(response)
                {
                  if(response.status=="success")
                  {
                    // $(location).attr('href', guest_url+'/signup_retailer/guest')
                    $(location).attr('href', guest_url+'/login/guest')

                  }

                }
            });

    }
    else
    {
      @php
      if($login_user == true && $login_user->inRole('customer'))
      {
      @endphp
        var red_url = SITE_URL+'/vendor-details/add_to_customer_favorite';
      @php
      }
      else
      {
      @endphp
        var red_url = SITE_URL+'/vendor-details/add_to_favorite';
      @php
      }
      @endphp
      
        $.ajax({
            url: red_url,
            type:"POST",
            data: {id:id,type:type,_token:csrf_token},             
            dataType:'json',
            beforeSend: function(){            
            showProcessingOverlay();
            },
            success:function(response)
            {
              hideProcessingOverlay();
              if(response.status == 'SUCCESS')
              { 
                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                     },
                    function(isConfirm,tmp)
                    {                       
                      if(isConfirm==true)
                      {
                        window.location.reload();
                        $add_favorite.addClass('favorited');
                      }



                    });
              }
              else
              {                
                swal('Error',response.description,'error');
              }  
            }  
            }); 
    } 
}


        function removeFromFavorite(ref)
        {
            var id   = $(ref).attr('data-id');
            var type = $(ref).attr('data-type');
            var csrf_token = $("input[name=_token]").val();

            @php
              if($login_user == true && $login_user->inRole('customer'))
              {
              @endphp
                var red_url = SITE_URL+'/vendor-details/remove_from_customer_favorite';
              @php
              }
              else
              {
              @endphp
                var red_url = SITE_URL+'/vendor-details/remove_from_favorite';
              @php
              }
              @endphp

            $.ajax({
                      url: red_url,
                      type:"POST",
                      data: {id:id,type:type,_token:csrf_token},             
                      dataType:'json',
                      beforeSend: function(){            
                      showProcessingOverlay();
                      },
                      success:function(response)
                      {
                        hideProcessingOverlay();
                        if(response.status == 'SUCCESS')
                        { 
                          swal({
                                  title: 'Success',
                                  text: response.description,
                                  type: 'success',
                                  confirmButtonText: "OK",
                                  closeOnConfirm: true
                              },
                              function(isConfirm,tmp)
                              {                       
                                if(isConfirm==true)
                                {
                                  window.location.reload();
                                }

                              });
                        }
                        else
                        {                
                          swal('Error',response.description,'error');
                        }  
                      }  
              }); 
        }

        function applyFilter()
        {  

          showProcessingOverlay();
          var csrf_token = $("input[name=_token]").val();

          var price_filter = $('input[name="price"]:checked').val();
          var vendor_minimum_filter = $('input[name="vendor_minimum"]:checked').val();
          var lead_time_filter = $('input[name="lead_time"]:checked').val();


          if ($('#free_shipping').is(":checked"))
          {
            var free_shipping = $('#free_shipping').val();
            $("#frmFilter").find("input[name='free_shipping']").val(free_shipping);
          }
          else
          {
           $("#frmFilter").find("input[name='free_shipping']").val('');
          }

          if ($('#percent_of').is(":checked"))
          {
            var percent_of = $('#percent_of').val();
            $("#frmFilter").find("input[name='percent_of']").val(percent_of);
          }
          else
          {
           $("#frmFilter").find("input[name='percent_of']").val('');
          }

          if ($('#doller_of').is(":checked"))
          {
            var doller_of = $('#doller_of').val();
            $("#frmFilter").find("input[name='doller_of']").val(doller_of);
          }
          else
          {
           $("#frmFilter").find("input[name='doller_of']").val('');
          }

          


          if(price_filter!=null)
          {
             
              var price_low = parseFloat(price_filter.split("-")[0]);
              var price_high = parseFloat(price_filter.split("-")[1]);
              
              $("#frmFilter").find("input[name='price:low']").val(price_low);
              $("#frmFilter").find("input[name='price:high']").val(price_high);
              $('.caret').hide();
              $('.close').show();
          }
            
          if(vendor_minimum_filter!=undefined)
          {
            var vendor_minimum_low = parseFloat(vendor_minimum_filter.split("-")[0]);
            var vendor_minimum_high = parseFloat(vendor_minimum_filter.split("-")[1]);

            $("#frmFilter").find("input[name='vendor_minimum_high']").val(vendor_minimum_high);
            $("#frmFilter").find("input[name='vendor_minimum_low']").val(vendor_minimum_low);
          }

          if(lead_time_filter!=undefined)
          {
           var lead_time_min = parseFloat(lead_time_filter.split("-")[0]);
           var lead_time_max = parseFloat(lead_time_filter.split("-")[1]);

           $("#frmFilter").find("input[name='lead_time_min']").val(lead_time_min);
           $("#frmFilter").find("input[name='lead_time_max']").val(lead_time_max);

          }

          var brand_names = [];
          var i =0;

          $('input[name="brands"]:checked').each(function() {
           
           var data_id = $(this).attr('data_id');
           //console.log(data_id);

           brand_names.push(data_id);

          // console.log(brand_names)

           $('[name="brands[]"]').val(brand_names);


        });

          window.location.href = SITE_URL+'/search?'+$("#frmFilter").serialize();
        }

        //close filter buttons
        $("#price_clear").click(function() {

            $("#frmFilter").find("input[name='price:low']").val('');
            $("#frmFilter").find("input[name='price:high']").val('');
            window.location.href = SITE_URL + '/search?' + $("#frmFilter").serialize();
        });

        $("#vendor_minimum_clear").click(function() {

            $("#frmFilter").find("input[name='vendor_minimum_low']").val('');
            $("#frmFilter").find("input[name='vendor_minimum_high']").val('');
            window.location.href = SITE_URL + '/search?' + $("#frmFilter").serialize();
        });

        $("#vendor_special_clear").click(function(){
         
         $("#frmFilter").find("input[name='free_shipping']").val('');
         $("#frmFilter").find("input[name='percent_of']").val('');
         $("#frmFilter").find("input[name='doller_of']").val('');
          window.location.href = SITE_URL + '/search?' + $("#frmFilter").serialize();
        });

        $("#lead_time_clear").click(function(){
         
         $("#frmFilter").find("input[name='lead_time_min']").val('');
         $("#frmFilter").find("input[name='lead_time_max']").val('');

         window.location.href = SITE_URL + '/search?' + $("#frmFilter").serialize();
        });


        $("#brand_clear").click(function(){


        $('[name="brands[]"]').val('');

         window.location.href = SITE_URL + '/search?' + $("#frmFilter").serialize();

        });


        //filters clear buttons

        $("#price_unset").click(function(evt){
          $('.price_btn').removeAttr('checked');
          $("#frmFilter").find("input[name='price:low']").val('');
          $("#frmFilter").find("input[name='price:high']").val('');
        });

        $("#vendor_minimum_unset").click(function(evt){
          $('.vendor_minimum_btn').removeAttr('checked');
          $("#frmFilter").find("input[name='vendor_minimum_low']").val('');
          $("#frmFilter").find("input[name='vendor_minimum_high']").val('');
        });

        $("#vendor_special_unset").click(function(evt){
          $('.vendor_special_btn').removeAttr('checked');
          
          $("#frmFilter").find("input[name='free_shipping']").val('');
          $("#frmFilter").find("input[name='percent_of']").val('');
          $("#frmFilter").find("input[name='doller_of']").val('');
        });

        $("#lead_time_unset").click(function(evt){

          $('.lead_time_btn').removeAttr('checked');

          $("#frmFilter").find("input[name='lead_time_min']").val('');
          $("#frmFilter").find("input[name='lead_time_max']").val('');
        });

        $("#brand_unset").click(function(evt){
         
         $('.brand_name').removeAttr('checked');


});  

        $('.closedrop-left').click(function(){

          $(this).closest(".dropdown-menu").prev().dropdown("toggle");
         //$('.dropdown-menu dropdown-filter-menu').hide();

        });

        $(".dropdown-filter-menu").click(function(evt){
          evt.stopImmediatePropagation();
        });

        $('.close').click(function(){
          
          showProcessingOverlay();
        });




        /*suffari issue*/
        (function () {
          window.onpageshow = function(event) {
            if (event.persisted) {
              window.location.reload();
            }
          };
        })();


function openGetAQuoteModal(ref){
  var login = "{{ $login_user['email'] }}";
  // alert(login);
  if(login.trim() == "")
  {
     window.location = '{{url('/')}}/login';
     return;
  }
  $("#get_a_Quote").modal('show');

  $("#vendor_company_name").html($(ref).attr('data-company-name'));
  $("#vendor_product_name").html($(ref).attr('data-product-name'));
  $("#product_description").html($(ref).attr('data-product-dec'));


  $("#vendor_id").val($(ref).attr('data-vendor-id'));
  $("#vendor_name").val($(ref).attr('data-vendor-name'));
  $("#vendor_email").val($(ref).attr('data-vendor-email'));
  $("#product_id").val($(ref).attr('data-product-id'));
  $("#company_name").val($(ref).attr('data-company-name'));


}

// Submit get quote

jQuery("#sendGetaQuote").bind("click touchstart", function(e){

    e.preventDefault();

    if ($('#getaQuote').parsley().validate() == false) {
          return;
        }
        var formData = $('#getaQuote').serialize();   
        
        $.ajax({
          url: '{{url("vendor-details/send_get_a_quote")}}',
          method: 'POST',
          dataType: 'JSON',
          data: formData,
          beforeSend: function() {
            showProcessingOverlay();
          },
          success: function(response) {
            hideProcessingOverlay();
            
            if (response.status == 'SUCCESS') {              
              swal({
                  title: "Success",
                  text: response.description,
                  type: 'success',
                  showCancelButton: false,
                  confirmButtonClass: "btn-success",
                  confirmButtonText: "OK",
                  closeOnConfirm: true
                },
                function() {                  
                  $('#get_a_Quote').modal('hide');  
                  $('#getaQuote').find("input[type=text], textarea, input[type=number]").val("");                
                });
            } else {    
              var status = response.status;
              status = status.charAt(0).toUpperCase() + status.slice(1);
              swal(status, response.description, response.status);
            }
          }
    
        });
  })


function redirect_to_page(){
  var page_no = Math.abs($("#search_page_no").val()); 
  var url     = window.location.href;
  var uri_segment = "{{url('/search')}}";
  var tech    = getUrlParameter('page');
  var search_term    = getUrlParameter('search_term');
  if(url.indexOf('page=') != -1){
      if(url.indexOf('search_term=') != -1 && search_term == ""){
         url = url.replace("search_term=", "");
      }
      search_url = url.replace("page="+tech, "page=");
      search_url1 = search_url.replace("page=", "page="+page_no);

  } else {
    if(url.indexOf('search_term=') != -1 && search_term == ""){
         url = url.replace("search_term=", "");
     }
     if(url == uri_segment)
     {
      search_url1 = url+"?page="+page_no;
     }else{
      search_url1 = url+"&page="+page_no;  
     }
    
  }
 // alert(search_url1);
 // return;
       
  if(page_no != '0'){
    /*var search_url = "{{url('/search')}}?page="+page_no;*/
    var new_search_url = search_url1;
    window.location.href = new_search_url;
  } else {
    /*var search_url = "{{url('/search')}}?page=";*/
    var new_search_url = search_url1;
    window.location.href = new_search_url;
  }
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};



</script>
@stop