
            @extends('front.layout.master')
            @section('main_content')

            <style type="text/css">
             .product-section.border-left-on.spacenon-main{padding-top: 0; margin-bottom:30px;}
             .spacenon-main .pagename-main{ margin-bottom:0px; overflow:hidden;}

             .product-section-top{
              display:block !important;
            }
            .not-found-data {
               text-align: center;
               font-size: 30px;
               padding: 100px 0;
               background-color: #efefef;
               color: #b1b1b1; 
             }
             .font-note{
               font-weight: 100;
               color: #9c9c9c;

             }
             .outofstock_listing{font-weight: normal;}
              .pro-content-list{
                    min-height: 92px;
              }
             .xzoom-container:hover img {opacity:0.2; }

              .fill {
               display: flex;
               justify-content: center;
               align-items: center;
               overflow: hidden;
               height: 500px;
               width: 400px;
             }
             .fill img {
               flex-shrink: 0;
               min-width: 100%;
               min-height: 100%
             }
             .pagename-left{
               color: #333 !important;
             }

             .note-ar-app {
               margin-top: 10px;
               background-color: #f7efde;
               padding: 10px;
               border-radius: 3px;
               color: #716444;
             }

             .dropdown {
              position: relative;
              display: inline-block;
            }

           
            </style>

            @php
            $previous_url = '';
            if(Request::session()->has('_previous')){
              $previous_url = isset(Session::get('_previous')['url'])?Session::get('_previous')['url']:'';
            }
            @endphp

            <form hidden id="frmFilter">

              <input type="hidden" name="price:low" value="{{ $search_value['price:low'] or ''  }}" />
              <input type="hidden" name="price:high" value="{{ $search_value['price:high'] or ''  }}" />

              <input type="hidden" name="vendor_minimum_high" value="{{$search_value['vendor_minimum_high'] or ''}}" />
              
              <input type="hidden" name="vendor_minimum_low" value="{{$search_value['vendor_minimum_low'] or ''}}"/>

              <input type="hidden" name="free_shipping" value="{{ $search_value['free_shipping'] or '' }}" />
              
              <input type="hidden" name="percent_of" value="{{ $search_value['percent_of'] or '' }}" />
              
              <input type="hidden" name="doller_of" value="{{ $search_value['doller_of'] or '' }}" />


              <input type="hidden" name="vendor_id" value="{{ $search_value['vendor_id'] or '' }}" />

              <input type="hidden" name="brand_id" value="{{ $search_value['brand_id'] or '' }}" />

              <input type="hidden" name="category_id" value="{{ $search_value['category_id'] or '' }}" />
              
              <input type="hidden" name="subcategory" value="{{ $search_value['subcategory'] or '' }}" />
              
              <input type="hidden" name="search_type" value="{{ $search_value['search_type'] or '' }}" />
              
              <input type="hidden" name="search_term" value="{{ $search_value['search_term'] or '' }}" />

              <input type="hidden" name="lead_time_min" value="{{ $search_value['lead_time_min'] or '' }}" />

              <input type="hidden" name="lead_time_max" value="{{ $search_value['lead_time_max'] or '' }}" />

            </form>
              {{-- {{dd("searchvalue",$search_value)}} --}}

            <link href="{{url('/')}}/assets/front/css/xzoom.css" rel="stylesheet" type="text/css" />
            {{-- {{dd(base64_decode($request_values['subcategory']))}} --}}
            <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
            @php

            $maker_img = false;

          /*  $maker_base_img = isset($maker_arr['store_details']['store_profile_image']) ? $maker_arr['store_details']['store_profile_image'] : false;

            $banner_base_img = isset($maker_arr['store_details']['store_cover_image']) ? $maker_arr['store_details']['store_cover_image'] : false;

            $maker_image_base_path  = base_path('storage/app/'.$maker_base_img);
            $banner_image_base_path  = base_path('storage/app/'.$banner_base_img);

            $banner_default_image = url('assets/front/images/vendor-profile-img-bannr.jpg');

            $maker_img = image_resize($maker_image_base_path,130,130);
            $banner_img = image_resize($banner_image_base_path,1140,200,$banner_default_image);*/

            /* For vendor banner image*/
            $banner_base_img = isset($maker_arr['store_details']['store_cover_image']) ? $maker_arr['store_details']['store_cover_image'] : false;

            $is_file_exists = "";
            if (file_exists(base_path().'/storage/app/'.$banner_base_img)) {
            $is_file_exists = "1";
            } else {
            $is_file_exists = "0";
            }     

            if($banner_base_img == "" || $banner_base_img == "false" || $is_file_exists == '0'){
            $banner_img = url('assets/images/no-banner-image-desktop.jpg');
            } else {
            //$banner_default_image = url('assets/front/images/vendor-profile-img-bannr.jpg');
            $banner_image_base_path  = base_path('storage/app/'.$banner_base_img);
            $banner_base_img = url('/storage/app/').'/'.$banner_base_img;
            $banner_img = image_resize($banner_image_base_path,1140,200,$banner_base_img);
            }
            /*Ends vendor banner image*/



            /* For vendor profile image*/
            $maker_base_img = isset($maker_arr['store_details']['store_profile_image']) ? $maker_arr['store_details']['store_profile_image'] : false;

            $is_file_exists = "";
            if (file_exists(base_path().'/storage/app/'.$maker_base_img)) {
            $is_file_exists = "1";
            } else {
            $maker_base_img = false;
            $is_file_exists = "0";
            }   

            if($maker_base_img == false || $is_file_exists == '0'){
            $maker_img = url('assets/images/no-product-img-found.jpg');
            } else {
            //$banner_default_image = url('assets/front/images/vendor-profile-img-bannr.jpg');
            $maker_image_base_path  = base_path('storage/app/'.$maker_base_img);
            $maker_base_img = url('/storage/app/').'/'.$maker_base_img;
            $maker_img = image_resize($maker_image_base_path,130,130);
            }
            /*Ends vendor profile image*/



            @endphp
            <link href="{{url('/')}}/assets/front/css/slider-gallery.css" rel="stylesheet" type="text/css" />
            @php
            $login_user = Sentinel::check();
            if($login_user)
            {
              $is_representative_exist =  $login_user->inRole('representative');
              $is_admin_exist          =  $login_user->inRole('admin');
            }
            @endphp
            <input type="hidden" name="is_representative_exist" id="is_representative_exist" value="{{$is_representative_exist or ''}}">
            <input type="hidden" name="is_admin_exist" id="is_admin_exist" value="{{$is_admin_exist or ''}}">
            <div class="vendorlisting-main-div  maker-details-main-div">
              <div class="container">
              {{--  <div class="vendor-profile-banner-main" style="background-image: url({{ $banner_img or '' }}) !important;">
              </div> --}}
              <div class="vendor-profile-banner-main brand_listing">
               {{-- <img src="{{url('/storage/app/').'/'.$banner_base_img}}"> --}}
               <img src="{{  $banner_img  }}">
             </div>
             <div>
              <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 maker_detail_left_div">
               <div class="vendor-whitebox">
                <a href="#" class="wishlist-vendor">{{-- <i class="fa fa-heart-o"></i> --}}</a>
                <div class="vendor-avatar">                 
                 {{-- <img src="{{url('/storage/app/').'/'.$maker_base_img}}" alt="{{ $maker_arr['maker_details']['company_name'] or ''}}" /> --}}
                 <img src="{{ $maker_img }}" alt="{{ $maker_arr['maker_details']['company_name'] or ''}}" />
               </div>

               <div class="vendor-avtr-title">{{isset($maker_arr['maker_details']['company_name'])?ucfirst($maker_arr['maker_details']['company_name']):""}}
                 @if($login_user==true)  
                 @if($login_user->inRole('retailer') || $login_user->inRole('customer'))
                 @if(isset($fav_maker_count) && $fav_maker_count >0)
                 <a href="javascript:void(0)" class="heart-active" data-id="{{isset($maker_arr['maker_details']['user_id'])?base64_encode($maker_arr['maker_details']['user_id']):0}}"  data-type="maker" id="fill_heart" onclick="confirmAction($(this),'remove','maker');" title="Remove from favorite"><i class="fa fa-heart"></i></a>        
                 @else
                 <a href="javascript:void(0)" class="heart-deactive" data-id="{{isset($maker_arr['maker_details']['user_id'])?base64_encode($maker_arr['maker_details']['user_id']):0}}" onclick="confirmAction($(this),'add','maker');" data-type="maker" id="empty_heart" title="Add to favorite"><i class="fa fa-heart-o"></i></a>
                 @endif
                 @endif
                 @endif    
               </div>
                
                  <div class="retail_price_product inlineprice font-weight-normal vendor_detail_miminum_leadtime">

                    {{-- {{dd($login_user->roles())}} --}}
                    @if($login_user == true && ($login_user->inRole('customer') || $login_user->inRole('influencer')))
                    @else
                    <span STYLE="font-size: 14px">Vendor Minimum : 
                    {{isset($shop_arr['first_order_minimum'])?'$'.$shop_arr['first_order_minimum']:'No Vendor Minimum'}}
                    </span>
                    @endif  
                    <span STYLE="font-size: 14px">Lead Time : {{isset($shop_arr['shop_lead_time'])?$shop_arr['shop_lead_time'].' days':'No Lead time'}}</span>
                  </div>
             </div>
             
           
             
             @if(isset($cat_arr) && count($cat_arr) > 0) 
             <div class="sidebar-main-lisitng noneborder">
               @php
                $build_url = http_build_query($search_value);
                $parsed = parse_url($build_url);
                //dd($parsed);
                $query = $parsed['path'];
                parse_str($query, $params);
                unset($params['category_id']);
                unset($params['vendor_id']);
                unset($params['subcategory']);
                $build_url = http_build_query($params);
              @endphp
             
              <div class="title-categorynm click-category">
                <span class="cat-none">Category</span>
                <img src="assets/front/images/chevron-down.svg" alt="">
              </div>

              <div id="cssmenu1" class="shows-category scrollbar">

               <ul>
                <li>
                 @if(isset($cat_arr) && count($cat_arr) > 0) 
                 <a href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&{{$build_url}}&all_products"><span class="submenu1">All Products</span>
                 </a>
                 @endif
               </li>
              
                           
               @if(isset($cat_arr) && count($cat_arr) > 0)   
                  @foreach($cat_arr as $category)
                      @if($category['category_details']['is_active']==1)
                          
                          <li class='has-sub'>
                 
                              <a  @if(isset($request_values['category_id']) && base64_decode($request_values['category_id'])==$category['category_id']) class="active" @endif 
                             href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&category_id={{isset($category['category_details']['id'])?base64_encode($category['category_details']['id']):""}}&{{$build_url}}"><span class="submenu1">{{isset($category['category_details']['category_name'])?ucfirst($category['category_details']['category_name']):""}}</span>
                              </a>
                 
                             @php 
                             $subcategory_id_arr = [];
                             if($category['category_details']!=null)
                             {
                               $subcategory_id_arr = array_column($category['category_details']['subcategory_details'], 'id');
                             }
                             @endphp

                             @if(isset($request_values['subcategory']) && in_array(base64_decode($request_values['subcategory']),$subcategory_id_arr))
                             <span class="plus-icon lnkclk"> <i class="fa fa-minus"></i></span>
                             @else

                              @if(isset($category['category_details']['subcategory_details']) && count($category['category_details']['subcategory_details']) > 0)
                             <span class="plus-icon lnkclk"> <i class="fa fa-plus"></i></span>
                             @endif
                             @endif

                            <ul class="sub_menu submenu @if(isset($request_values['subcategory']) && in_array(base64_decode($request_values['subcategory']),$subcategory_id_arr)) link-act @endif" style="@if(isset($request_values['subcategory']) && in_array(base64_decode($request_values['subcategory']),$subcategory_id_arr)) "display:block" @else  "display:none" @endif">
                                @if(isset($category['category_details']['subcategory_details']) && count($category['category_details']['subcategory_details']) > 0)
                                
                                    @foreach($category['category_details']['subcategory_details'] as $sub_category)
                                    
                                        @if($sub_category['is_active']==1)
                                            <li class='has-sub'>
                                             <a   
                                             @if(isset($request_values['subcategory']) && base64_decode($request_values['subcategory'])== $sub_category['id']) class="active" @endif href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&category_id={{base64_encode($category['category_id'])}}&subcategory={{isset($sub_category['id'])?base64_encode($sub_category['id']):""}}&{{$build_url}}"
                                             ><span class="submenu1">- {{ucfirst($sub_category['subcategory_name'])}}</span>
                                           </a>
                                         </li>
                                        @endif
                                      @endforeach
                                    @endif
                                  </ul>
                                </li>
                              @endif
                              @endforeach
                              @endif
                              </ul>
                              </div>
                              </div>
                              @endif
                


           @php
            $login_user = Sentinel::check();
            $is_customer_exist = '';
            $is_influencer_exist = '';

            if($login_user)
            {
              $is_customer_exist    =  $login_user->inRole('customer');
              $is_influencer_exist  =  $login_user->inRole('influencer');

              
            }

            $date = date('Y-m-d');
           
            $isPromotionVisible = false;

            @endphp

            
      @if($login_user == false || $login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager')))

            @if(isset($promotion_arr) && count($promotion_arr)>0)
                <label>
                  <h3 class="ttl" id="promotions-section"> Promotions</h3>
                </label>

              <div class="scroll-makrdetails scrollbar-div">

                @foreach($promotion_arr as $key=>$promotions)

                    @if($promotions['to_date'] >= $date && $promotions['is_active'] == 1)

                        @php
                          $isPromotionVisible = true;
                        @endphp

                        <hr class="hr-top">
                        <div class="main-all-current-promotions-right none-div-style">
                          <div class="title-promotions-txt">{{$promotions['title'] or ''}}</div>
                          <div class="date-tm">{{isset($promotions['from_date'])?date('d M Y',strtotime($promotions['from_date'])):''}}  To  {{isset($promotions['to_date'])?date('d M Y',strtotime($promotions['to_date'])):''}}</div>
                          <div class="clearfix"></div>
                          <ul class="promotions-ul">
                           @if(isset($promotions['get_promotions_offer_details']) && count($promotions['get_promotions_offer_details'])>0)
                           @foreach($promotions['get_promotions_offer_details'] as $key=>$promotions_offer)
                           @if($promotions_offer['promotion_type_id'] == 1)
                           <li>Orders of ${{$promotions_offer['minimum_ammount']}} receive free shipping</li>
                           @elseif($promotions_offer['promotion_type_id'] == 2)
                           <li>Orders of ${{$promotions_offer['minimum_ammount']}} receive {{$promotions_offer['discount']}}% off</li>
                           @endif
                           @endforeach
                           @endif
                         </ul>

                         @if(isset($promotions['get_promo_code_details']['promo_code_name']) && $promotions['get_promo_code_details']['promo_code_name']!='') 
                         <div class="button-ff-year">{{$promotions['get_promo_code_details']['promo_code_name'] or ''}}</div>
                         @endif
                        </div>
                    @endif  

                @endforeach
              
            @endif

          @endif    
          </div>
    {{--        
              @if(($is_customer_exist == false) && ($is_influencer_exist == false)) --}}
              
               @if($login_user == false || $login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager')))
 
                          @if(isset($pdf_arr) && count($pdf_arr)>0)


                          
                         
                             
                            <label>
                               <h3 class="ttl">Catalogs</h3>
                            </label>
                            <div class="scroll-makrdetails scrollbar-div">
                            @foreach($pdf_arr as $key=>$catalog)
                           
                                <div class="catalog-list-dv">
                                  @php 
                                    if(isset($catalog['cover_image']) && $catalog['cover_image']!='')
                                    {
                                      $first_catlog_img = $catalog['cover_image'];
                                    }
                                    else
                                    {
                                      $first_catlog_img = '';
                                    }
                                  @endphp

                                  <div class="product-list-pro vendor-profile-list">
                                    <div class="pro-img-list">
                                      <img class="potrait" src="{{url('/storage/app/')}}/{{$first_catlog_img or ''}}" alt="img12">        
                                      <div class="ovrly"></div>
                                      <div class="buttons">
                                          <a href="{{url('/vendor-details/catalogs')}}/{{base64_encode($catalog['id'])}}" target="_blank" class="faeye">
                                           {{-- <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt=""> --}}
                                           <i class="fa fa-link"></i>
                                          </a>
                                      </div>
                                    </div>
                                    <div class="catlog-nm">{{$catalog['catalog_name'] or 'N/A'}}</div>
                                  </div>
                                </div>
                                
                            @endforeach
                             </div>
                          @endif

                      @endif
                     </div>
                     <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10 maker_detail_right_div">
                       <div class="text-vendor-top">
                         <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="1000">
                         <div class="truncate">{!! $shop_arr['shop_story'] or ''!!}</div>

                         <?php
                         $brand_name = '';
                        
                         $brand_id = Request::get('brand_id');
                         $brand_id = isset($brand_id)?base64_decode($brand_id):false;

                         if($brand_id != false)
                         {

                          $brand_name = get_brand_name_brandId($brand_id);

                        }
                        ?>
                        

                        <div class="product-section border-left-on spacenon-main">
                         <div class="row">
                           @if(isset($brand_name))
                           <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 product-section-top">
                             <h3 class="mrg-onew" style="margin-top: 0;">{{isset($brand_name)?$brand_name:''}}</h3>
                             <div class="pagename-main">
                               <div class="pagename-left">All Products</div>
                          <!--  <div class="pagename-right">
                              <a href=""></a> 
                                 <span class="active"></span>
                                   
                               </div> -->
                             </div>
                             <div class="results-txt">{{isset($product_arr['total_results'])?$product_arr['total_results']:0}} Products</div>
                           </div>
                           <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-right">
                            <div class="showbrandbtn pull-right">
                              @if(isset($array_brands) && count($array_brands) > 1 )
                              <a href="{{url('/vendor-details?vendor_id=').base64_encode($maker_arr['maker_details']['user_id'])}}" class="btn filter_search-btn" title="All Brands">Show All Brands</a>
                              @endif
                            </div>
                          </div>
                          @endif
                        </div>

                        @include('front.search._filters',['search_value' => $search_value])

                             {{--  <div class="su-menu res-su-menu style-4 dropdown">
                                <button class="button-ff-year dropdown-toggle" type="button" data-toggle="dropdown">Minimum
                                <span class="caret"></span></button>
                                <ul class="dropdown-menu ">
                                 <li><input type="radio"  name="vendor_minimum" value="0" class ="form_group"><label for ="price1">No minimum</label></li>
                                 <li><input type="radio" name="vendor_minimum" value="100"><label for ="price2">$100 or less</label></li>
                                 <li><input type="radio"  name="vendor_minimum" value="200"><label for ="price3">$200 or less</label></li>
                                 <li><input type="radio"  name="vendor_minimum" value="300"><label for ="price4">$300 or less</label></li>
                                 <div class="applydiv text-right">
                                  <button width="100px" class="btn clearbtn mr-2">Clear</button>
                                  <button width="100px" class="btn applybtn" onclick="applyFilter()">Apply</button>
                                 </div>
                                </ul>
                              </div> --}}
                            </div>

                            <div class="row">

                              @if(isset($maker_product_arr) && count($maker_product_arr)>0)
                              @foreach($maker_product_arr as $product) 

                              @php $maker_details = get_maker_all_details($product['user_id']); @endphp                               
                              <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4 pro_listing_box_div">
                               <div class="product-list-pro vendor-profile-list">
                                <div class="pro-img-list">
                                 <div class="heart-products">
                                  @if($login_user==true)  
                                  @if($login_user->inRole('retailer') || $login_user->inRole('customer'))
                                  @if(isset($fav_product_arr) && in_array($product['id'],$fav_product_arr))
                                  <a href="javascript:void(0)" class="heart-active" data-id="{{isset($product['id'])?base64_encode($product['id']):0}}" data-type="product" onclick="confirmAction($(this),'remove');"><i class="fa fa-heart" title="Remove from favorite"></i></a>  
                                  @else                    
                                  <a href="javascript:void(0)" class="heart-deactive" data-id="{{isset($product['id'])?base64_encode($product['id']):0}}" data-type="product" onclick="confirmAction($(this),'add');" title="Add to favorite"><i class="fa fa-heart-o"></i></a>
                                  @endif
                                  @endif   
                                  @endif
                                </div>
                                @php


                                $product_img = false;


                                $product_image_base_path  = base_path('storage/app/'.$product['product_image']);

                                if(file_exists($product_image_base_path))
                                {
                                          // $product_img = url('storage/app/'.$product['_source']['product_image_thumb']);
                                  $product_img = image_resize($product_image_base_path,230,230,false,true);

                                }
                                else
                                {
                                  $product_img = url('/assets/images/no-product-img-found.jpg');
                                }

                                @endphp
                                <!-- <a  href="javascript:void(0)" data-produt-id="{{base64_encode($product['id'])}}"
                                maker_id="{{ base64_encode($product['user_id']) }}" class="faeye" onclick="show_product_details(this)">
                                <img class="potrait" src="{{$product_img or ''}}" alt="img12"></a> -->

                                <a  href="{{url('/')}}/vendor-details/product_detail?product_id={{ base64_encode($product['id'])}}&vendor_id={{base64_encode($product['user_id'])}}" data-produt-id="{{base64_encode($product['id'])}}"
                                maker_id="{{ base64_encode($product['user_id']) }}" class="faeye" onclick="showProcessingOverlay();">
                                <img class="potrait" src="{{$product_img or ''}}" alt="img12"></a>

                                        <!--  <div class="ovrly"></div>
                                          <div class="buttons">
                                             <a  href="javascript:void(0)" data-produt-id="{{base64_encode($product['id'])}}"
                                                maker_id="{{ base64_encode($product['user_id']) }}" class="faeye" onclick="show_product_details(this)">
                                             <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="" />
                                             </a>
                                           </div> -->


                                         </div>
                                         <div class="pro-content-list ptover spacetp-tool">
                                           <div class="pro-sub-title-list tooltip-title" title="{{isset($product['product_name'])?ucfirst($product['product_name']):""}}">{{isset($product['product_name'])?ucfirst($product['product_name']):""}}


                                           </div>


                                           @php
                                           $login_user = Sentinel::check();
                                           @endphp

                                           <div class="price-product inlineprice">
                                           @if($login_user == true)
                                              @if((($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))   
                                              <div class="retail_price_product inlineprice font-weight-normal">
                                                  <span class="pricewholsl">Price</span>
                                                  <div class="prices">
                                                  <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                  </div>
                                              </div>
                                              @elseif((($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                              <div class="suggested_price inlineprice font-weight-normal">
                                                  <span class="pricewholsl">Price</span>
                                                  <div class="prices">
                                                  <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                  </div>
                                              </div>    
                                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)

                                              @elseif(($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                              <div class="suggested_price inlineprice font-weight-normal">
                                                  <span class="pricewholsl">Price</span>
                                                  <div class="prices">
                                                  <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                  </div>
                                              </div>
                                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                              <div class="retail_price_product inlineprice font-weight-normal">
                                                  <span class="pricewholsl">Price</span>
                                                  <div class="prices">
                                                  <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                  </div>
                                              </div>
                                              @endif                                                       
                                              @else
                                                  @if(($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || ($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                  <div class="retail_price_product inlineprice font-weight-normal">
                                                      <span class="pricewholsl">Price</span>
                                                      <div class="prices">
                                                      <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                      </div>
                                                  </div>                       
                                                  @elseif($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)                                                            
                                                  @elseif($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                  <div class="retail_price_product inlineprice font-weight-normal">
                                                      <span class="pricewholsl">Price</span>
                                                      <div class="prices">
                                                      <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                                      </div>
                                                  </div>
                                                  @endif
                                              @endif

                                          <!-- out stock -->

                                          @php

                                            $temp_product_id = isset($product['id'])?$product['id']:0;

                                            $sku = get_sku($product['id']);
                                            
                                            $temp_all_sku = get_all_sku($temp_product_id);
                                           
                                            
                                            $temp_all_sku = array_column($temp_all_sku,'quantity');


                                            $product_inventory = array_sum($temp_all_sku);

                                            $is_in_stock = check_moq_inventory($product['id']);

                                          @endphp
                                           
                                       
                                          @if(isset($product_inventory) && $product_inventory == 0 || ($login_user == true &&  ($login_user->inRole('retailer')) && $is_in_stock == false))

                                            <div class="out-of-stock-container">
                                               <span class="red outofstock_listing">Out of stock</span>
                                            </div>
   
                                          @endif


                                          </div>



                                        <!-- Get a Quote button -->

                                        @if((isset($maker_details['is_get_a_quote']) && $maker_details['is_get_a_quote'] == 1) || (isset($product['unit_wholsale_price']) && $product['unit_wholsale_price'] <= 0))

                                            <div class="button-login-pb" >

                                              @php

                                               $vendor_email = get_user_email($product['user_id']);

                                               $vendor_name = isset($product['user_id'])?get_user_name($product['user_id']):'';
                                              @endphp

                                                  
                                                <a href="javascript:void(0)" 
                                                   data-product-name="{{$product['product_name'] or ''}}" 
                                                   data-product-dec="{{$product['description'] or ''}}" 
                                                   data-company-name="{{$maker_details['company_name'] or ''}}" 
                                                   data-product-id="{{$product['id'] or ''}}" 

                                                   data-vendor-id = "{{$product['user_id'] or ''}}"

                                                   data-vendor-email = "{{$vendor_email or ''}}"
                                                   data-vendor-name = "{{$vendor_name or ''}}"

                                                   onclick="openGetAQuoteModal(this)"  class="gt-button" id="get-a-quote-modal" >Get A Quote
                                                </a>

                                                <div class="clearfix"></div>

                                            </div>

                                          @endif

                                        </div>
                                      </div>
                                    </div>
                                    @endforeach
                                    <div class="clearfix"></div>

                                    <div class="col-md-12 text-center">{{ $pagination_links->render()}}</div>
                                    @else
                                    <div class="col-md-12">
                                     <div class="not-found-data">Your search did not match any products.</div>
                                   </div>
                                   @endif       
                                 </div>

                               </div>
                             </div>
                           </div>
                         </div>
                       </div>

                       <!-- Modal -->
                       <div class="vendor-profile-main-modal custom-vendor-profile-main-modal">
                        <div class="modal-mask"></div>
                        <div class="modal-popup" data-keyboard="false" tabindex="-1">
                         {{--  <a href="{{$new_url or '#'}}" class="closemodal">
                            <img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="close" />
                          </a> --}}

                          <a href="javascript:void(0)" class="closemodal">
                            <img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="close" />
                          </a>

                          <div class="modal-bodys">
                            <div class="main-modalbd">
                             <!-- default start -->
                             <section id="default" class="padding-top0 xzoom-head-padding">
                              <div class="">
                               <div class="large-5 column zoomleftcolumn">

                                <div id ="gallery-container">
                                 <div class="button-login-pb">
                                  <a class="btn btn-default" id="zoom-in"><i class="fa fa-search-plus"></i></a>
                                </div>

                                <!-- <div id ="gallery-container image"> -->

                                 <div class="xzoom-container">
                                   @php 
                                   $img_sku = isset($request_values['sku'])?base64_decode($request_values['sku']):null;
                                   $img_active_class = 'xactive';

                                   @endphp

                                   @if(isset($first_prod_arr['product_details']))


                                   @if(!isset($img_sku) && $img_sku=="")
                                   @php


                                   $first_product = [
                                     'image_thumb' => isset($first_prod_arr['product_details'][0]['image_thumb']) ? $first_prod_arr['product_details'][0]['image_thumb'] : false,
                                     'image' => isset($first_prod_arr['product_details'][0]['image']) ? $first_prod_arr['product_details'][0]['image'] : false,
                                   ];
                                   $product_img = false;
                                   $product_img_thumb = false;

                                   $product_original_image = url('/').'/storage/app/'.$first_product['image'];



                                   $product_image_thumb_base_path  = base_path('storage/app/'.$first_product['image_thumb']);

                                   $product_image_base_path  = base_path('storage/app/'.$first_product['image']);

                                   $product_img_thumb = image_resize($product_image_thumb_base_path,77,77);
                                   $product_img = image_resize($product_image_base_path,400,400,false,true);

                                   @endphp

                                   <div class="img-thumbnail">
                                     <img class="xzoom image" id="xzoom-default" src="{{$product_img or ''}}" xoriginal="{{ $product_original_image or ''}}"  />
                                   </div> 

                                   @endif   


                                   @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                                   @php


                                   $first_product = [
                                    'image_thumb' => isset($first_prod_arr['product_details'][0]['image_thumb']) ? $first_prod_arr['product_details'][0]['image_thumb'] : false,
                                    'image' => isset($first_prod_arr['product_details'][0]['image']) ? $first_prod_arr['product_details'][0]['image'] : false,
                                  ];

                                  $product_img = false;
                                  $product_img_thumb = false;

                                  $product_original_image = url('/').'/storage/app/'.$first_product['image'];



                                  $product_image_thumb_base_path  = base_path('storage/app/'.$first_product['image_thumb']);

                                  $product_image_base_path  = base_path('storage/app/'.$first_product['image']);

                                  $product_img_thumb = image_resize($product_image_thumb_base_path,77,77);
                                  $product_img = image_resize($product_image_base_path,400,400,false,true);



                                  @endphp    

                            {{--   @if($prod_key==0)
                              <div class="img-thumbnail">
                                 <img class="xzoom image" id="xzoom-default" src="{{$product_img or ''}}" xoriginal="{{ $product_original_image or ''}}"  />
                               </div> --}}

                               <!-- ---------------------------------->

                               @if(isset($img_sku) && $img_sku!='')

                               @if($img_sku == $product_details['sku'])   
                               @php
                               $product_img       = false;
                               $product_img_thumb = false;

                               $product_original_image = url('/').'/storage/app/'.$product_details['image'];

                               $product_image_thumb_base_path  = base_path('storage/app/'.$product_details['image_thumb']);

                               $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                               $product_img_thumb        = image_resize($product_image_thumb_base_path,77,77);
                               $product_img              = image_resize($product_image_base_path,400,400,false,true);


                               @endphp
                               
                               
                               <div class="img-thumbnail">
                                <img class="xzoom image" id="xzoom-default" src="{{$product_img or ''}}" xoriginal="{{ $product_original_image or ''}}"  />
                              </div>

                              
                              @endif

                              @endif   
                              <!-------------------------------------------------------->

                              @endforeach
                              @endif 
                              

                              <div class="xzoom-thumbs">

                                @php
                                $imgSku = isset($request_values['sku'])?base64_decode($request_values['sku']):null; 

                                @endphp

                                @if(isset($first_prod_arr['product_details']))

                                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                                @php
                                $product_img       = false;
                                $product_img_thumb = false;

                                $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                                $product_img_thumb      = image_resize($product_image_base_path,77,77);
                                $product_img            = image_resize($product_image_base_path,400,400,false,true);
                                $product_original_image = url('/').'/storage/app/'.$product_details['image'];


                                if(isset($imgSku) && $imgSku == $product_details['sku'])
                                {
                                 $imgSkuSquence = $prod_key;
                               }

                               @endphp     
                               <a href="{{$product_original_image}}"><img class="xzoom-gallery imgsku"   width="80" height="80" src="{{$product_img_thumb}}"  xpreview="{{$product_img}}" imgsku="{{$product_details['sku']}}"
                                pro-weight="{{$product_details['weight']}}"
                                pro-height = "{{$product_details['height']}}"
                                pro-width = "{{$product_details['width']}}"
                                pro-length = "{{$product_details['length']}}"
                                pro-qty = "{{$product_details['inventory_details']['quantity']}}"
                                option_type ="{{$product_details['option_type']}}"
                                option_value = "{{$product_details['option']}}"
                                ></a>
                                @endforeach

                              </div>
                              @endif

                            </div>
                            <!-- </div> -->
                          </div>
                          <div class="large-7 column"></div>
                        </div>
                      </section>
                      <!-- default end -->
                      <div class="details-of-list">
                        <div class="subminimum-font">
                         @if(isset($maker_arr['maker_details'])) 
                         <a href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}">{{$maker_arr['maker_details']['company_name']}}
                         </a>
                         @php 
                         $get_minimum_order = get_maker_shop_setting($maker_arr['maker_details']['user_id']);
                         $minimum_order = "";
                         if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum'] == 0){$minimum_order = 'No Minimum Limit';}
                         else if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum']!=0)
                           {$minimum_order = '$'. num_format($get_minimum_order['first_order_minimum']).' Minimum';} 
                         @endphp
                         <div class="pro-title-list">{{$minimum_order}} </div>
                         @endif
                       </div>
                       @if(isset($first_prod_arr['category_details']['id']))  

                       @php  
                       $cat_id = base64_encode($first_prod_arr['category_details']['id']); 
                       @endphp  
                       <a href="{{url('/')}}/search?category_id={{isset($cat_id)?$cat_id:""}}"> 
                        <div class="title-detail-pg" id="category_name">{{isset($first_prod_arr['category_details']['category_name'])?$first_prod_arr['category_details']['category_name']:""}}</div>
                      </a>
                      @else
                      <a href ="" id ="cat_search">
                       <div class="title-detail-pg" id="category_name">{{isset($first_prod_arr['category_details']['category_name'])?$first_prod_arr['category_details']['category_name']:""}}</div>
                     </a>
                     @endif
                     <form id="frm-add-to-bag">
                       @php

                       $prod_user_id = isset($first_prod_arr['user_id'])?$first_prod_arr['user_id']:'';

                       $login_usr = isset($login_user)?$login_user:false;

                       if($login_usr !=false)
                       {
                        if($prod_user_id == $login_user->id)
                        {
                         $show_add_cart_btn = "none";
                       }
                       else
                       {
                        $show_add_cart_btn = "";
                      }
                    }
                    else
                    {
                     $show_add_cart_btn = "";
                   }


                   @endphp
                   {{ csrf_field() }}
                   <input type = "hidden" id = prod_user_id value="">

                   <input type="hidden" name="product_id" id="product_id" value="{{ Request::input('product_id') }}">
            {{-- 
             <input type="hidden" name="sku_no" id="sku_num" value="{{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}}"> --}}

             <input type="hidden" name="sku_no" id="sku_num" value="@if(isset($imgSku)) {{$imgSku}} @else {{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}} @endif">

             <input type="hidden" name="retail_price" id="retail_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}">
             <input type="hidden" name="wholesale_price" id="wholesale_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
             @php
             $login_user = Sentinel::check();
             @endphp

             @if($login_user==false)

             <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}"> 
             @else

             @if($login_user->inRole('customer'))
             <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}">  
             <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="customer"}>

             @else
             <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
             <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="retailer"}>

             @endif

             @endif
             <input type="hidden" name="is_logged_in" id = "is_logged_in" value={{$login_user}}>


             <div class="row">
              <div class="col-md-12">
               <div class="lettle-seed-div largfont-prodt" id="popup_product_name">@if(isset($first_prod_arr['product_name'])){{ ucfirst($first_prod_arr['product_name'])}}@endif</div>
             </div>
            </div>
            <hr>

            <div class="row">


              <div class="col-md-12">
                @if($login_user==true && $login_user->inRole('retailer')== true)
                <div class="suggested-price mkr-sub-pc inlineblockprice" >
                  <div class="suggested-price-img"><b>Price (Wholesale) </b></div>
                  <span class="inlines first-span"> $</span>
                  <span class="product-price" id="popup_wholesale_price"> {{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span>
                </div>
                @else 
                <div class="suggested-price retailer-price mkr-sub-pc inlineblockprice" >
                 <div class="suggested-price-img"><b> Retail Price </b></div>
                 <span class="inlines first-span"> $</span>
                 <span id="popup_retail_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span>
               </div>
               @endif 


                    <!--        @if($login_user == false)
                              <div class="note-ar-app">Note: You are applicable for retail price.</div>
                           @else
                              <div class="note-ar-app">Note: You are applicable for wholesale price.</div>
                              @endif  -->
                            </div>


                          </div>
                          @php
                          if(isset($first_prod_arr['product_details'][0]['sku']))
                          {
                           $pro_details = get_style_dimension($first_prod_arr['product_details'][0]['sku']);
                           $qty = get_product_quantity($first_prod_arr['product_details'][0]['sku']);
                         }
                         @endphp   
                         <hr>
                         <div id="demo" class="maker-details-ul-ul">
                          @php 
                          if(isset($first_prod_arr['product_details'][0]['option']))
                          {
                           $opt_value = ucfirst($first_prod_arr['product_details'][0]['option']);  
                           //$color     = ucfirst($first_prod_arr['product_details'][0]['color']);  
                           $weight = ucfirst($first_prod_arr['product_details'][0]['weight']);  
                           $length = ucfirst($first_prod_arr['product_details'][0]['length']);  
                           $width = ucfirst($first_prod_arr['product_details'][0]['width']);  
                           $height = ucfirst($first_prod_arr['product_details'][0]['height']);
                           //echo $first_prod_arr['product_details'][0]['option_type'];
                         }
                         @endphp
                         <ul>
                           @if(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==0)
                           @if(isset($opt_value) && $opt_value != '?' && $opt_value != '' && $opt_value!=0)
                           <li>
                            <div class="li-left">Color :</div>
                            <div class="option_value newoptionvalue">{{isset($first_prod_arr['product_details'][0]['option_type'])?$first_prod_arr['product_details'][0]['option_type']:''}}</div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($weight) && $weight!=0  && $weight != '' && $weight != '?')

                          <li>
                            <div class="li-left">Weight :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($weight)?$weight:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($height) && $height!=0  && $height != '' && $height != '?')
                          <li>
                            <div class="li-left">Height:</div>
                            <div class="newoptionvalue"><span id="height">{{isset($height)?$height:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($length) && $length!=0 && $length != '' && $length != '?')

                          <li>
                            <div class="li-left">Length:</div>
                            <div class=" newoptionvalue"><span id="length">{{isset($length)?$length:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($width) && $width!=0 && $width != '' && $width != '?')

                          <li>
                            <div class="li-left">Width :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($width)?$width:''}}</span></div>
                            <div class="clearfix"></div>
                          </li> 


                          @endif

                          @elseif(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==1)
                          @if(isset($opt_value) && ($opt_value!=0 || $opt_value != '' || $opt_value != '?'))
                          <li>
                            <div class="li-left">Scent :</div>
                            <div class="option_value newoptionvalue">{{isset($opt_value)?$opt_value:''}}</div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($weight) && $weight!=0 && $weight != '' && $weight != '?')

                          <li>
                            <div class="li-left">Weight :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($weight)?$weight:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($height) && $height!=0 && $height != '' && $height != '?')
                          <li>
                            <div class="li-left">Height:</div>
                            <div class="newoptionvalue"><span id="height">{{isset($height)?$height:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($length) && $length!=0 && $length != '' && $length != '?')

                          <li>
                            <div class="li-left">Length:</div>
                            <div class=" newoptionvalue"><span id="length">{{isset($length)?$length:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($width) && $width!=0 && $width != '' && $width != '?')

                          <li>
                            <div class="li-left">Width :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($width)?$width:''}}</span></div>
                            <div class="clearfix"></div>
                          </li> 


                          @endif

                          @elseif(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==2)
                          @if(isset($opt_value) && $opt_value!=0 && $opt_value != '' && $opt_value != '?')
                          <li>
                            <div class="li-left">Size :</div>
                            <div class="option_value newoptionvalue">{{isset($opt_value)?$opt_value:''}}</div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($weight) && $weight!=0 && $weight != '' && $weight != '?')

                          <li>
                            <div class="li-left">Weight :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($weight)?$weight:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>
                          @endif
                          @if(isset($height) && $height!=0 && $height != '' && $height != '?')
                          <li>
                            <div class="li-left">Height:</div>
                            <div class="newoptionvalue"><span id="height">{{isset($height)?$height:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($length) && $length!=0 && $length != '' && $length != '?')

                          <li>
                            <div class="li-left">Length:</div>
                            <div class=" newoptionvalue"><span id="length">{{isset($length)?$length:''}}</span></div>
                            <div class="clearfix"></div>
                          </li>

                          @endif
                          @if(isset($width) && $width!=0 && $width != '' && $width != '?')

                          <li>
                            <div class="li-left">Width :</div>
                            <div class="newoptionvalue"><span id="weight">{{isset($width)?$width:''}}</span></div>
                            <div class="clearfix"></div>
                          </li> 


                          @endif
                          @elseif(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==3)
                          
                          @else

                          @endif

                          <div class="li-left" style="display: none;">Quantity :</div><div id = "quantity" class="newoptionvalue" style="display: none;">{{isset($first_prod_arr['product_details'][0]['inventory_details']['quantity'])?$first_prod_arr['product_details'][0]['inventory_details']['quantity']:''}}</div>
                        </ul>
                      </div>
                      <div class="user-box product-details-box" >
                        <label class="form-lable">Item Quantity</label>                
                        <input class="vertical-spin bucket_spin" 
                        data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value = 1 data-parsley-type="integer" data-parsley-trigger="change" data-parsley-max=1000> 
                        <input type="hidden" id = "prod_qty"> 
                        <div id="error_item_qty" style="display: none;"></div>             
                      </div>
                      <div class="button-login-pb" >
                        <a href="javascript:void(0)" class="gt-button" id="add-to-bag" style= "display:{{$show_add_cart_btn}}" >Add to Cart</a>
                        <div class="clearfix"></div>
                      </div>


                    {{--   <div class="out-of-stock-container">
                        <span class="outofstock">Out of stock</span>
                      </div> --}}


                      @if($login_user==true &&  $login_user->inRole('retailer')== true)
                      <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                      <span class="first-span">$</span>
                      <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                    </div>
                    @else
                    <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                    <span class="first-span">$</span>
                    <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                  </div>

                  @endif




                </form>
              </div>
              <div class="clearfix"></div>
            </div>
            <hr>
            <div class="about-product-title">About Product</div>
            <div class="product-about-p" id="popup_product_description">
              @if(isset($first_prod_arr['description']) && strlen($first_prod_arr['description']) > 500)

              <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="500">

              <td><div class="truncate">{!! $first_prod_arr['description'] or '' !!}</div></td>

              @else

              <td>{!! $first_prod_arr['description'] or '' !!}</td>

              @endif

            </div>
            <div class="clearfix"></div>
            </div>
            </div>
            </div>

            <input type="hidden" name="img_sku" id="img_sku" value="{{isset($imgSkuSquence)?$imgSkuSquence:null}}">

            <?php
            $afterAddingCardUrl = $category_id = '';
                  //echo Session::get('category'); exit();

            if(Session::get('category')!="")
            {
              $afterAddingCardUrl="category";
              $category_id       = Session::get('category');
            }

            else
            {
             $afterAddingCardUrl='search';
            }
            ?>


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

          <input type="hidden" name="get_quote_product_id" id="get_quote_product_id" value="">
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

  $(document).ready(function() 
  {
   
   //when product comes fron catalog.
   var sku_sequence ='';
   sku_sequence = $('#img_sku').val(); 

   $('#img_sku_sequence').val(sku_sequence);

 });



</script>


  <script src="{{url('/')}}/assets/front/js/setup.js"></script>

  <script src="{{url('/')}}/assets/front/js/xzoom.min.js"></script>

  <script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>
  <script type="text/javascript">
   $("#zoom-in").click(function()
   {
     $("#gallery-container").addClass('removeclass-img-hover');
     $(".xzoom-container").addClass('pointeventon');
     $("#gallery-container").addClass('extrazoom');


   });
  </script>
  <script src="{{url('/')}}/assets/front/js/jquery.js"></script>
  <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
  <script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>
  <!-- Modal Script -->
  <script type="text/javascript">


     /* window.onbeforeunload = function() {
     console.log('yes');return false;
     $(".vendor-profile-main-modal").style.display='none';

   }*/



  var vendor_id = $("#frmFilter").find("input[name='vendor_id']").val();

  var all_products='';
  if(window.location.href.indexOf("all_products") > -1){
  var all_products ="all_products";
  }


   $(document).ready(function() 
   {

     var isVisible = '{{$isPromotionVisible or ''}}';
     
     if(isVisible == false)
     {
       $('#promotions-section').css('display','none');
     }

     $("#item_qty").keyup(function(){
      var check_qty = $("#item_qty").val();
      if(check_qty>1000)
      {
        swal('Warning','Purchase limit 1000 units.','warning');
        flag ="false";
        $("#item_qty").val(1000);
        return
      }
    });

     $('#item_qty').on('touchspin.on.startspin', function ()
     {
       var check_qty = $("#item_qty").val();
       if(check_qty>1000)
       {
         swal('Warning','Purchase limit 1000 units.','warning');
         flag ="false";
         $("#item_qty").val(1000);
         return
       }
     });

     var product_id      = $("#product_id").val(); 
     var retail_price    = $("#retail_price").val();
     var wholesale_price = $("#wholesale_price").val();
     var product_price   = $("#product_price").val();

     const module_url_path = "{{ $module_url_path or '' }}";
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
           $(location).attr('href', guest_url+'/signup_retailer/guest')
         }

       }
     });
     });




              check_quantity();
               
              function check_quantity()
              { 
                  var quantity =  $('#quantity').text();

             
                   /*if(quantity==0)
                   {
                     var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
                     $(".button-login-pb").hide();
                     $(".out-of-stock-container").show();
                     $("#item_qty").prop('disabled', true);
                   }*/

                  /* else
                   {
                     */

                  var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                   <div class="clearfix"></div>`;
                   $("#item_qty").prop('disabled', false);
                   $(".button-login-pb").show();
                   $(".out-of-stock-container").hide();


                   $("#prod_qty").val(quantity);
                   let qty = $('#item_qty').val();    
                   var total_wholesale_price = 0;

                   var max_qty = $("#item_qty").attr('data-parsley-max');

                   $('#item_qty').keyup(function() {

                     $('#frm-add-to-bag').parsley().validate();
                     let qty = $('#item_qty').val(); 
                     if(qty>1000)
                     { 
                      total_wholesale_price = parseFloat(1000) * parseFloat(product_price);

                    }
                    else
                    { 
                      total_wholesale_price = qty * parseFloat(product_price);

                    }

                    $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));

                    if(qty =='' || qty==undefined || isNaN(qty)==true)
                    {  
                     total_product_price = 0;
                     $('#total_wholesale_price').html(total_product_price.toFixed(2));
                   }
                 });

                   $("#item_qty").change(function(){
                    $('#frm-add-to-bag').parsley().validate();
                    total_wholesale_price = parseFloat(1000) * parseFloat(product_price);
                    //fix_price(total_wholesale_price);

                  });

                   $(".vertical-spin").TouchSpin({
                     min: 0,
                   //max: 1000//max_qty

                 }).on('touchspin.on.startspin blur change click', function (event) 
                 {  
                   let qty = $('#item_qty').val();   
                   console.log(qty); 
                   let wholesale_price = $("#popup_wholesale_price").text();

                   var total_wholesale_price = 0; 
                   if(qty>1000)
                   {
                    total_wholesale_price = parseFloat(1000) * parseFloat(product_price);     
                  }
                  else
                  {
                   total_wholesale_price = parseFloat(qty) * parseFloat(product_price);      
                 } 


                 $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));  

                 if(qty =='' || qty==undefined || isNaN(qty)==true)
                 {  
                   total_product_price = 0;
                   $('#total_wholesale_price').html(total_product_price.toFixed(2));
                 } 

               });
               //}
             }
             $('.imgsku').click(function()
             {    
               var imgsku = $(this).attr('imgsku');
               var img_option_type = $(this).attr('option_type');
               var img_option_value = $(this).attr('option_value');
               var img_height = $(this).attr('pro-height');
               var img_width = $(this).attr('pro-width');
               var img_length = $(this).attr('pro-length');
               var img_weight = $(this).attr('pro-weight');
               var img_qty = $(this).attr('pro-qty');
               
               $("#option_type").text(img_option_type);
               
               
               $("#weight").text(img_weight);
               $("#height").text(img_height); 
               $("#width").text(img_width); 
               $("#length").text(img_length);
               $("#quantity").text(img_qty);
               
               $("#prod_qty").val(img_qty);
               
               $('#frm-add-to-bag').parsley().reset();

               check_quantity();
               var opt_val = img_option_value;
               opt_val = opt_val.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                 return letter.toUpperCase();
               });
               $('.option_value').text(opt_val);
               $("#sku_num").val(imgsku);

             });   
            });

            function show_product_details(reff) 
            {   

             var module_url_path = "{{url('/')."/vendor-details"}}";
             var product_id = $(reff).attr('data-produt-id');
             var maker_id = $(reff).attr('maker_id');
             var product_details_url = module_url_path +'/get_product_details';
             var is_representative_exist = $('#is_representative_exist').val();
             var is_admin_exist          = $('#is_admin_exist').val();

               // if(is_representative_exist == 1)
               // { 
               //   swal('Warning','Please login as retailer','warning');
               
               // }


               $.ajax({

                 url: product_details_url,
                 type: "GET",
                 data:{product_id:product_id},
                 beforeSend:function(){
                  showProcessingOverlay();
                },
                error:function(){
                  hideProcessingOverlay();
                },
                success: function(response) 
                {

                 hideProcessingOverlay();
                 $("#item_qty").val(1);
                 if(response.status=="SUCCESS")
                 {  

                   var product_arr = response.arr_data;
                   var pro_details = response.pro_details;
                   var meta_details = response.meta_details
                   var product_details = product_arr.product_details[0];

                   var product_id   = btoa(product_arr.id);
                   var product_name = product_arr.product_name;
                   var product_description = product_arr.description;
                   var product_retail_price = product_arr.retail_price;
                   var is_logged_in = $("#is_logged_in").val();
                   var logged_in_user_role = $("#logged_in_user_role").val();
                   console.log(meta_details);

                   $("meta[property='twitter:card']").attr("content", meta_details.meta_image);
                   $("meta[property='og:image']").attr("content", meta_details.meta_image);
                   $("meta[property='twitter:image']").attr("content", meta_details.meta_image);

                   $("meta[property='title']").attr("content", meta_details.meta_title);
                   $("meta[property='og:title']").attr("content", meta_details.meta_title);
                   $("meta[property='twitter:title']").attr("content", meta_details.meta_title);

                   $("#product_id").val(product_id);
                   $("#retail_price").val((+product_arr.retail_price).toFixed(2));
                   $("#wholesale_price").val((+product_arr.unit_wholsale_price).toFixed(2));

                   if(is_logged_in == "true")
                   {
                     if(logged_in_user_role == "customer")
                     {
                      $("#total_wholesale_price").text((+product_arr.retail_price).toFixed(2));
                      $("#product_price").val((+product_arr.retail_price).toFixed(2));
                    }
                    else
                    {
                      $("#total_wholesale_price").text((+product_arr.unit_wholsale_price).toFixed(2));
                      $("#product_price").val((+product_arr.unit_wholsale_price).toFixed(2));
                    }
                  }
                  else
                  {
                   $("#total_wholesale_price").text((+product_arr.retail_price).toFixed(2));
                   $("#product_price").val((+product_arr.retail_price).toFixed(2));
                 }



                 $("#sku_num").val(response.sku_id);

                 $("#popup_product_name").text(product_arr.product_name);
                 $("#popup_retail_price").html((+product_arr.retail_price).toFixed(2));
                 $("#popup_product_description").html(product_arr.description);
                 $("#popup_wholesale_price").html((+product_arr.unit_wholsale_price).toFixed(2)); 

                 $("#opt_type").text(pro_details.option_type);
                 $("#weight").text(pro_details.weight);
                 $("#height").text(pro_details.height); 
                 $("#width").text(pro_details.width); 
                 $("#length").text(pro_details.length);
                 if(product_details.inventory_details!=null)
                 {

                   $("#quantity").text(product_details.inventory_details.quantity);
                                 //$("#item_qty").attr('data-parsley-max',product_details.inventory_details.quantity);
                                 var max_qty = $("#item_qty").attr('data-parsley-max');
                                 //$('#frm-add-to-bag').parsley().reset();
                                // $( "input" ).trigger("touchspin.updatesettings", {max: max_qty});
                              }

                              $("#default").html();


                              $("#default").html(response.html);


                              if(product_arr.category_details!= null)
                              {
                               $("#category_name").text(product_arr.category_details.category_name);

                             }
                             
                             var opt_val = pro_details.option;
                             opt_val = opt_val.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                               return letter.toUpperCase();
                             });
                             $('.option_value').text(opt_val);
                             $('.collapse.in').collapse('hide'); 
                             show_product_model();
                             $("#example1").html('');
                             $("#example1").html(response.html);   
                           }
                           else
                           {                
                             swal('Error','Something went wrong,please try again','error');
                           } 
                         }
                       });


            function show_product_model()
            {
             $(".modal-mask, .modal-popup").fadeIn();
             $('.vendor-profile-main-modal').fadeIn();
             $('body').toggleClass('modal-open');
             $(".modal-popup").animate({

               left: '10%'
             }, 'slow', function () {
               $(".modal-popup").animate({
                 'top': '5%'
               }, 200, "swing", function () {});
             });
            }

            }





            $("#add-to-bag").click(function()
            { 

             let flag        ="true";
             let qty         = parseInt($('#item_qty').val()); 
             let max_qty     = parseInt($('#item_qty').attr('data-max'));
             let current_qty =  parseInt($("#prod_qty").val());


             if(qty<=0)
             {
               swal('Warning','Please enter quantity greater than zero.','warning');
               flag ="false";
               return
             } 

             if(qty>1000)
             {   
                   //swal('Warning','Available Quantity:'+current_qty+'','warning');
                   swal('Warning','Purchase limit 1000 units.','warning');
                   flag ="false";
                   return
                 }  
                 if($('#frm-add-to-bag').parsley().validate()==false)
                   {   flag ="false";
                 return         
               }

               
               if(flag=="true")
               { 

                 $.ajax({
                  url: SITE_URL+'/my_bag/add',
                  type:"POST",
                  data: $('#frm-add-to-bag').serialize(),             
                  dataType:'json',
                  beforeSend: function(){            
                   showProcessingOverlay();
                 },
                 success:function(response)
                 {
                   console.log(response);
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

                        var previous_url = '{{$previous_url}}';

                        if(previous_url != '')
                        {
                          window.location.href = previous_url;
                        }
                        else
                        {
                          window.location.href = SITE_URL+'/search';
                        }

                                    /*if(response.user_loggedIn_status == false){

                                       window.location.href = SITE_URL+'/login';
                                    }
                     
                                    var URL        = '{{$afterAddingCardUrl}}';
                                    var categoryID = '{{$category_id}}';


                                    if(URL == 'search')
                                    {
                                       window.location.href = SITE_URL+'/search';
                                    }
                                    else if(URL == 'category')
                                    {
                                       window.location.href = SITE_URL+'/search?category_id='+categoryID;
                                     } */
                                     
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
              
   });
   
   
   
   /* new code for add to favorite and remove to favrite*/
   function addToFavorite(ref)
   {
   var id   = $(ref).attr('data-id');
   var type = $(ref).attr('data-type');
   var csrf_token = $("input[name=_token]").val();
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

    // alert(red_url);
    // return false;
   $.ajax({
         url: red_url,
         type:"POST",
         data: {id:id,type:type,_token:csrf_token},             
         dataType:'json',
         beforeSend: function(){            
         // showProcessingOverlay();
         },
         success:function(response)
         {
           // hideProcessingOverlay();
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

function confirmAction(ref,action,is_maker="")
{
      var text = confirmButtonText = "";
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
             // showProcessingOverlay();

                },
                success:function(response)
                {
                    // hideProcessingOverlay();
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
        var vendor_minimum = $('input[name="vendor_minimum"]:checked').val();
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

      if (price_filter != null) {

        var price_low = parseFloat(price_filter.split("-")[0]);
        var price_high = parseFloat(price_filter.split("-")[1]);

        $("#frmFilter").find("input[name='price:low']").val(price_low);
        $("#frmFilter").find("input[name='price:high']").val(price_high);
        $('.caret').hide();
        $('.close').show();
      }

      if (vendor_minimum != undefined) {
        var vendor_minimum_low = parseFloat(vendor_minimum.split("-")[0]);
        var vendor_minimum_high = parseFloat(vendor_minimum.split("-")[1]);

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
     

    window.location.href = SITE_URL + '/vendor-details?' + $("#frmFilter").serialize()+'&'+all_products;

}

    $("#price_clear").click(function(){


      $("#frmFilter").find("input[name='price:low']").val('');
      $("#frmFilter").find("input[name='price:high']").val('');

      window.location.href = SITE_URL + '/vendor-details?vendor_id=' + vendor_id + '?' + $("#frmFilter").serialize()+'&'+all_products;;
    });

    $("#vendor_minimum_clear").click(function(){


      $("#frmFilter").find("input[name='vendor_minimum_low']").val('');
      $("#frmFilter").find("input[name='vendor_minimum_high']").val('');

      window.location.href = SITE_URL + '/vendor-details?vendor_id=' + vendor_id + '&' + $("#frmFilter").serialize()+'&'+all_products;;
    });

    $("#vendor_special_clear").click(function() {

      $("#frmFilter").find("input[name='free_shipping']").val('');
      $("#frmFilter").find("input[name='percent_of']").val('');
      $("#frmFilter").find("input[name='doller_of']").val('');

      window.location.href = SITE_URL + '/vendor-details?vendor_id=' + vendor_id + '&' + $("#frmFilter").serialize()+'&'+all_products;;
    });

    $("#lead_time_clear").click(function(){

     $("#frmFilter").find("input[name='lead_time_min']").val('');
     $("#frmFilter").find("input[name='lead_time_max']").val('');

     window.location.href = SITE_URL + '/vendor-details?vendor_id=' + vendor_id + '&' + $("#frmFilter").serialize()+'&'+all_products;;

    });


    $("#brand_clear").click(function(){

      $('[name="brands[]"]').val('');

      window.location.href = SITE_URL + '/vendor-details?vendor_id=' + vendor_id + '&' + $("#frmFilter").serialize()+'&'+all_products;;

    });


    $('.closedrop-left').click(function(){

      $(this).closest(".dropdown-menu").prev().dropdown("toggle");
      //$('.dropdown-menu dropdown-filter-menu').hide();
    });


    //filter clear buttons
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


    $(".dropdown-filter-menu").click(function(evt){
      evt.stopImmediatePropagation();
    });



    //Handeling Escape keypress 

    $(document).keydown(function(e) {
      if (e.keyCode == 27) {
        console.log("ok11");
      $("body").removeClass("mainbody nimbus-is-editor modal-open");
       setTimeout(closePopup, 0);
     }

    });

    function fix_price()
    {

      $(".vertical-spin").TouchSpin({ max: 1000});
    }

    function closePopup()
    {  console.log("ok00");
     $('.vendor-profile-main-modal').css('display', 'none');
     $("body").removeClass(".mainbody nimbus-is-editor modal-open");
       //window.location.reload();
     }

    $(".closemodal").click(function() {
      
       $('.vendor-profile-main-modal').css('display', 'none');
       $("body").removeClass(".mainbody nimbus-is-editor modal-open");
    });
            


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
  $("#get_quote_product_id").val($(ref).attr('data-product-id'));
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


  // After page refresh modal should get closed



  </script>
  <script>
    $(document).ready(function(){
      $(".click-category").click(function(){

        $(".shows-category").toggleClass("showscategorydiv");
      });
    });
  </script>

  <script src="{{url('/')}}/assets/front/js/jquery.flexisel.js" type="text/javascript"></script>

  <!--footer section start here-->
  @stop