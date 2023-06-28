@extends('front.layout.master')

@section('main_content')

<style>
   body {background-color:#fff;}

   .content-list-seller.height-new-arrivals{
    height: 170px;
   }
   


</style>

<!--Response message configuration -->

<?php
    $flash_message = '';
    $flash_type = '';
    if(Session::has('flash_notification')){
      $arr_session_flash = Session::get('flash_notification')->toArray();
      if(isset($arr_session_flash) && sizeof($arr_session_flash)>0){
        $flash_message = isset($arr_session_flash[0]->message)?$arr_session_flash[0]->message:'';
        $flash_type = isset($arr_session_flash[0]->level)?$arr_session_flash[0]->level:'';
      }
    }
    $is_secure = Session::get('is_secure');
    
?> 

<input type="hidden" name="" id="flash_message" value="{{$flash_message}}"> 
<input type="hidden" name="" id="flash_type" value="{{$flash_type}}"> 



<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
   {{-- @include('admin.layout._operation_status') --}}
   <!-- Indicators -->

   <!-- Wrapper for slides -->
   <!-- $banner_default_image = url('assets/front/images/ad-img-2.jpg'); -->
 <div class="carousel-inner" role="listbox">
 @if(isset($banner_img_arr) && count($banner_img_arr)>0)  
   
         @foreach($banner_img_arr as $key=>$banner)
         
         @php

            $banner_default_image = url('/assets/images/no-banner-image-desktop.jpg');
            
            $banner_img = false;
            $banner_base_img = isset($banner['banner_image']) ? $banner['banner_image'] : false;
            $banner_image_base_path  = base_path('storage/app/'.$banner_base_img);
            $banner_img = image_resize($banner_image_base_path,1900,550,$banner_default_image);

            
            $banner_default_image_mob = url('/assets/images/no-banner-image-mobile.jpg');

             $banner_img_small = false;
             $banner_small_base_img = isset($banner['banner_image_small']) ? $banner['banner_image_small'] : false;
             $banner_image_small_base_path  = base_path('storage/app/'.$banner_small_base_img);
             $banner_img_small = image_resize($banner_image_small_base_path,670,380,$banner_default_image_mob);
        
         @endphp
      <div class= @if($key==0)'item active' @else {{'item'}} @endif>
         <!-- <img src="{{$banner_img}}" alt=""> -->
        <picture>
           <source type="image/jpg" srcset="{{$banner_img_small}}" media="(max-width: 621px)"> 

          <source type="image/jpg" srcset="{{$banner_img}}" media="(min-width: 622px)"> 

            <source type="image/png" srcset="{{$banner_img_small}}" media="(max-width: 621px)"> 
           <source type="image/png" srcset="{{$banner_img}}" media="(min-width: 622px)"> 
            <source type="image/svg" srcset="{{$banner_img_small}}" media="(max-width: 621px)"> 
           <source type="image/svg" srcset="{{$banner_img}}" media="(min-width: 622px)"> 
             <source type="image/jpeg" srcset="{{$banner_img_small}}" media="(max-width: 621px)"> 
           <source type="image/jpeg" srcset="{{$banner_img}}" media="(min-width: 622px)">
             <source type="image/gif" srcset="{{$banner_img_small}}" media="(max-width: 621px)"> 
           <source type="image/gif" srcset="{{$banner_img}}" media="(min-width: 622px)">

            <img class="cw-image cw-image--loaded obj-fit-polyfill" alt="" aria-hidden="false">
        </picture> 
        <div class="banner_overlay"></div>
         <!--  <img src="{{ url('/')}}/storage/app/{{isset($banner['banner_image'])?$banner['banner_image']:''}}" alt=""> -->
       

         <div class="container">
            <div class="carousel-caption">
               <div class="banner-text-block" style="display: none;">
                  <!-- <h1>Latest &amp; Trendy <span>Girls Clothes</span></h1>
                  <p>Shope &amp; get extra 10% off</p> -->
                  @if($key==0)

                  <a href="{{isset($banner['url'])?$banner['url']:'http://159.89.225.42:9002/search'}}" class="button-defualt btn-space-top first-bnr-btns">Shop Now</a>
                  @else
                    <a href="{{isset($banner['url'])?$banner['url']:'http://159.89.225.42:9002/search'}}" class="button-defualt btn-space-top">Shop Now</a>
                  @endif
               </div>
            </div>
         </div>
      </div>
         @endforeach
        
    @else
    <div class='item active'>
         
        <picture>
           <source type="image/jpg" srcset="{{url('assets/images/default_images/no-banner-image-small.jpg')}}" media="(max-width: 621px)"> 
          <source type="image/jpg" srcset="{{url('assets/images/default_images/no-banner-image.jpg')}}" media="(min-width: 622px)"> 

            <source type="image/png" srcset="{{url('assets/images/default_images/no-banner-image-small.jpg')}}" media="(max-width: 621px)"> 
          <source type="image/png" srcset="{{url('assets/images/default_images/no-banner-image.jpg')}}" media="(min-width: 622px)"> 

            <source type="image/svg" srcset="{{url('assets/images/default_images/no-banner-image-small.jpg')}}" media="(max-width: 621px)"> 
          <source type="image/svg" srcset="{{url('assets/images/default_images/no-banner-image.jpg')}}" media="(min-width: 622px)"> 

            <source type="image/gif" srcset="{{url('assets/images/default_images/no-banner-image-small.jpg')}}" media="(max-width: 621px)"> 
          <source type="image/gif" srcset="{{url('assets/images/default_images/no-banner-image.jpg')}}" media="(min-width: 622px)"> 

            <source type="image/jpeg" srcset="{{url('assets/images/default_images/no-banner-image-small.jpg')}}" media="(max-width: 621px)"> 
          <source type="image/jpeg" srcset="{{url('assets/images/default_images/no-banner-image.jpg')}}" media="(min-width: 622px)"> 
            <img class="cw-image cw-image--loaded obj-fit-polyfill" alt="" aria-hidden="false">
        </picture> 
              
      </div>

    @endif
      </div>

   <!-- Controls -->
   <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
   <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
   <span class="sr-only">Previous</span>
   </a>
   <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
   <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
   <span class="sr-only">Next</span>
   </a>
</div>
<!--Slider section start here-->
<div class="clearfix"></div>

<div class="gallery-of-photo ">
<div class="container">
   <div class="producthomk-list-pro mobileviewset">

      @if(isset($categories_arr) && sizeof($categories_arr)>0)        
       <div class="titleof-seller-home">
         Check our <a href="{{url('/')}}/search">All</a> Categories
      </div>

      {{-- <div class="pull-right cat-btn"> --}}
         {{-- <a href="{{url('/')}}/search" class="button-defualt first-bnr-btns">View all categories</a> --}}
      {{-- </div> --}}
   <div class="row">      
         @foreach ($categories_arr as $key =>$category)
      
         <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
            <figure class="effect-lily">
               @php

                  $category_img = false;

                  $category_base_img = isset($category['category_image']) ? $category['category_image'] : false;
                  $category_image_base_path  = base_path('storage/app/'.$category_base_img);
                  $category_default_image =  url('/assets/images/no-product-img-found.jpg');
                  $category_img = image_resize($category_image_base_path,442,320,$category_default_image);


               @endphp
               <img src="{{$category_img or ''}}" alt="img12">
               <figcaption>
                  <div class="main-imghr">
                     <div class="hovercontent">
                        <h2>{{ucfirst($category['category_name'])}}</h2>
                     <div class="shop-now">
                        <a class="full-a" href="{{url('/')}}/search?category_id={{ base64_encode($category['id'])}}">Shop Now</a>
                     </div>
                     </div>
                  </div>
                  <a class="full-a" href="{{url('/')}}/search">View more</a>
               </figcaption>
            </figure>
         </div>
         
         @endforeach
         @endif
    
</div>
</div>


<!-- Demo slider Start -->

<!-- Demo Slider End -->





<div class="best-seller-main-dv mobilepadding-o">
   <div>
      <hr>
      @if(isset($product_arr) && sizeof($product_arr)>0)
      <div class="titleof-seller-home spacemobileview-bottom">
         Check out our <a href="{{url('/')}}/search?category=new_arrivals">New</a> Arrivals!
      </div>
      <div class="clearfix"></div>



      <!-- Demo Slider Start -->
      <div class="Container slickcontainer">
         <h3 class="Head"><span class="Arrows"></span></h3>
         <!-- Carousel Container -->
         <div class="SlickCarousel">
            <!-- Item -->

            
                  @foreach($product_arr as $product)
                     @php
                        $login_user = Sentinel::check();

                        $product_img = false;

                        $product_base_img = isset($product['product_image']) ? $product['product_image'] : false;
                        $product_image_base_path  = base_path('storage/app/'.$product_base_img);
                        $product_default_image = url('/assets/images/no-product-img-found.jpg'); 
                        $product_img = image_resize($product_image_base_path,320,320,false,true);

                     @endphp
                     <div class="ProductBlock">
                        <div class="best-seller-list">
                           <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['id'])}}&vendor_id={{base64_encode($product['user_id'])}}">
                           <div class="btn06">
                              <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">
                              <!-- <div class="ovrly"></div> -->
                              <div class=""></div>
                              <div class="buttons">
                                 <!-- <a href="{{url('/')}}/vendor-details?product_id={{base64_encode($product['id'])}}&vendor_id={{base64_encode($product['user_id'])}}" class="faeye"> -->
                                 <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                                 </a>
                              </div>
                           </div>

                          <div class="content-list-seller height-new-arrivals">


                              <div class="one-linetxt" title="{{ucfirst($product['product_name'])}}"> {{ucfirst($product['product_name'])}} </div>


                              <!-- if get a quote enable then hide  inline price div -->

                              @php $maker_details = get_maker_all_details($product['user_id']); @endphp

                              @if($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)
                                  
                                  <div class="price-product inlineprice" style="display:none;">
                                  </div>

                              @else 
                                                
                              <div class="price-product inlineprice">


                              <!--    @if ($login_user == true &&  ($login_user->inRole('maker') || $login_user->inRole('customer') ))
                                    <div class="retail_price_product inlineprice font-weight-normal">       
                                       <span class="pricewholsl">Retail</span>
                                       <div class="prices">
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['retail_price'])?num_format($product['retail_price']) : ''}}
                                       </div>
                                    </div>

                                 @elseif ($login_user == true &&  ($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative') ))
                                
                                       <div class="retail_price_product inlineprice font-weight-normal">
                                          <span class="pricewholsl">Wholesale</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                       </div>
                             
                                 @else
                                    <div class="retail_price_product inlineprice font-weight-normal">
                                       <span class="pricewholsl">Retail</span>                              
                                        <div class="prices">
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['retail_price'])?num_format($product['retail_price']) : ''}}
                                        </div>
                                    </div>
                                 @endif -->


                                 <!-- get a quote -->


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
                                      <div class="suggested_price inlineprice font-weight-normal">
                                        <span class="pricewholsl">Price</span>
                                        <div class="prices">
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                        </div>
                                      </div>                        
                                      @elseif($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)                                                            
                                      @elseif($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                      <div class="suggested_price inlineprice font-weight-normal">
                                        <span class="pricewholsl">Price</span>
                                        <div class="prices">
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                        </div>
                                      </div>
                                      @endif
                                    @endif
                                   
                                                     
                              </div> 

                          @endif
                                
                              {{--   
                               <a href="#" title="{{isset($product['product_name'])?ucfirst($product['product_name']):""}}" class="brandnametitle tooltip-title">{{isset($product['product_name'])?($product['product_name']):""}} </a> --}}
                                                   
                              <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($product['user_id'])}}" title="{{$product['brand_name']}}" class="title-product">{{isset($product['brand_name'])?($product['brand_name']):""}}</a>

                             
                            <!-- out stock -->


                              @php

                                $temp_product_id = isset($product['id'])?$product['id']:0;

                                $sku = get_sku($product['id']);
                                
                                $temp_all_sku = get_all_sku($temp_product_id);
                               
                                
                                $temp_all_sku = array_column($temp_all_sku, 'quantity');


                                $product_inventory = array_sum($temp_all_sku);


                                $is_in_stock = check_moq_inventory($product['id']);

                              @endphp
                               

                              @if(isset($product_inventory) && $product_inventory == 0 || ($login_user == true && ($login_user->inRole('retailer')) && $is_in_stock == false))

                                <div class="out-of-stock-container">
                                   <span class="red outofstock_listing">Out of stock</span>
                                </div>

                              @endif


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
                           <!-- </a> -->

                        </div>
                     </div>
                  @endforeach
            <!-- Item -->
         </div>
         <!-- Carousel Container -->
      </div>
      <!-- Demo Slider End -->
      <div class="clr"></div>
      <div class="buttonshop">
         <a href="{{url('/')}}/search" class="buttonshopfull">Shop the full collection</a>
         
      </div>
      @endif
      <div class="clr"></div>
</div>

<!-- Jewelry Store Section End -->
<div class="ad-section-nw-main">
   <div class="">
      <div class="row">
         @if(isset($banner_img1_arr) && count($banner_img1_arr)>0)
         @foreach($banner_img1_arr as $banner)
         @php

         $banner_img = false;

         $banner_base_img = isset($banner['banner_image']) ? $banner['banner_image'] : false;
         $banner_image_base_path  = base_path('storage/app/'.$banner_base_img);
         $banner_default_image = url('assets/front/images/ad-img-2.jpg');
         $banner_img = image_resize($banner_image_base_path,756,187,$banner_default_image);

         @endphp
         <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="ad-sction">
               <img src="{{ $banner_img or ''}}" alt="" />
             
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="btnshopnow" id ="a1">Shop Collection</a>
            </div>
         </div>
         @endforeach
         @else
         <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="ad-sction">
               <img src="{{url('assets/images/default_images/2-ban-img1.jpeg')}}" alt="" />
             
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="btnshopnow" id ="a1">Shop Collection</a>
            </div>
         </div> 

         <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="ad-sction">
               <img src="{{url('assets/images/default_images/2-ban-img.jpeg')}}" alt="" />
             
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="btnshopnow" id ="a1">Shop Collection</a>
            </div>
         </div>
         @endif
        
      </div>
   </div>
</div>
<div class="home-secnt">
   <div class="">
      <div class="row">
         @if(isset($banner_img2_arr) && count($banner_img2_arr)>0)
         @foreach($banner_img2_arr as $banner)
         @php 
           $banner_img = false;

            $banner_base_img = isset($banner['banner_image']) ? $banner['banner_image'] : false;
            $banner_image_base_path  = base_path('storage/app/'.$banner_base_img);
            $banner_default_image = url('assets/front/images/ad-img-2.jpg');
            $banner_img = image_resize($banner_image_base_path,535,444,$banner_default_image);
         @endphp
         <div class="col-xs-6 col-md-6 col-sm-6 col-lg-6">
            <div class="main-hm-sect">
               <img src="{{ $banner_img or '' }}" alt="" />
              
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="buttonshopfull">Shop Now</a>
            </div>
         </div>
         @endforeach

         @else

         <div class="col-xs-6 col-md-6 col-sm-6 col-lg-6">
            <div class="main-hm-sect">
               <img src="{{url('assets/images/default_images/3-ban-img1.jpeg')}}" alt="" />
              
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="buttonshopfull">Shop Now</a>
            </div>
         </div>

         <div class="col-xs-6 col-md-6 col-sm-6 col-lg-6">
            <div class="main-hm-sect">
               <img src="{{url('assets/images/default_images/3-ban-img.jpeg')}}" alt="" />
              
               <a href="{{isset($banner['url'])?$banner['url']:url('/').'/search'}}" class="buttonshopfull">Shop Now</a>
            </div>
         </div>

         @endif
        
      </div>
   </div>
</div>
@if (isset($login_user) && $login_user == true && $login_user->inRole('customer'))
<input type="hidden" name="login_count" id="login_count" value="{{$is_login or ''}}">
@endif


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
                    <label>Product Quantity<span class="text-danger">*</span></label>
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





<!-- Modal Sign IN  Start -->
<div id="CongratulationsModal" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog fancy-modal-popup">
       
        <div class="modal-content">
            <div class="modal-body">

                <button type="button" id="btnClose" class="close" ><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>
               <div class="login-form-block">
                        <div class="login-content-block">
                          <div class="congratulationsimage"></div>
                            <div class="successfully-title">Congratulations {{$arr_user_data['first_name'] or ''}}</div>  


                            <div class="login-content-block">
                           
                             <div class="cong-text">You are registered successfully in {{$site_setting_arr['site_name'] or ''}}.</div>
                           </div>
                        </div>
                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Sign IN  End -->

<div id="ModalCategory" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog modal-lg intrest_modal">
       
        <div class="modal-content sign-up-popup">
            <div class="modal-body">
                    <div class="login-form-block">
                        <div class="login-content-block">

                          <div class="categorymodalslst">
                           
                            @if(isset($arr_category) && count($arr_category) >0)
                              @foreach($arr_category as $key=>$category)

                                 <div class="col-category">
                                    
                                      <div class="round">
                                          <input type="checkbox" id="checkbox_{{$category['id']}}" name="category_checkbox" value="{{$category['id']}}" min="1" max="3" />
                                          <label for="checkbox_{{$category['id']}}"></label>
                                        </div>


                                     <img src="{{url('/')}}/storage/app/{{$category['category_image']}}" alt="" />
                                     <div class="categorty-title">
                                      {{$category['category_name'] or ''}}
                                     </div>
                                 </div>

                              @endforeach
                            @endif

                          </div>

                           <div class="clearfix"></div>
                           <div class="categorynote-main">
                              <div class="note-category">
                            <span>Note:</span> Please choose at least one category
                          </div>
                          <div class="pull-right mb-3">
                            <a class="btn logi-link-block btn-primary"  data-toggle="modal" id="btn_skip">Skip</a> 
                            <a class="btn logi-link-block btn-primary" data-toggle="modal" id="btn_submit">Submit</a>
                          </div>
                           <div class="clearfix"></div>
                           </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>



{{-- <div class="container store_locator">
   <div class="store-locator-main">
      <div class="row">
         <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
            <div class="icon-main-bottom">
               <div class="icon-store">
                  <img src="{{url('/')}}/assets/front/images/store-locator-icon.svg" alt="" />
               </div>
               <div class="title-store">Store Locator</div>
               <div class="sub-title-store">Find your nearest event store</div>
            </div>
         </div>
         <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
            <div class="icon-main-bottom">
               <div class="icon-store">
                  <img src="{{url('/')}}/assets/front/images/free-shipping-icon.svg" alt="" />
               </div>
               <div class="title-store">Free Shipping</div>
               <div class="sub-title-store">Free return & exchange</div>
            </div>
         </div>
         <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
            <div class="icon-main-bottom">
               <div class="icon-store">
                  <img src="{{url('/')}}/assets/front/images/money-back-icon.svg" alt="" />
               </div>
               <div class="title-store">Money Back</div>
               <div class="sub-title-store">100% with 30 days</div>
            </div>
         </div>
         <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
            <div class="icon-main-bottom border-none-nw">
               <div class="icon-store">
                  <img src="{{url('/')}}/assets/front/images/24h-support-icon.svg" alt="" />
               </div>
               <div class="title-store">24H Support</div>
               <div class="sub-title-store">Fast service support 24/7</div>
            </div>
         </div>
      </div>
   </div>
</div> --}}

<!-- Modal for Rejoiz welcome message -->

<div id="WelcomeModal" class="modal fade login-popup auto-modal-open" data-replace="true" style="display: block;">

    <div class="modal-dialog fancy-modal-popup">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" id="welcomeClose" class="close" ><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>
               <div class="login-form-block">
                        <div class="login-content-block">
                          
                            <div class="elcome-title-rejoix">Welcome to Rejoiz </div>
                            <div class="login-content-block">
                             <div class="cong-text">Rejoiz is an online marketplace that connects
                              local retailers, wholesalers, and importers with global exporters and manufacturers.</div>
                           </div>
                          <div class="free-signup-text">Register now for Free</div>  
                          <div class="registered-already" id="alreadyRegistered"><a href="#">Already Registered?</a>  </div>
                           <div class="msin-automodal">
                            <div id="status_msg"></div>
                            <form  id="info-form">
                              {{ csrf_field() }}
                              <div class="form-group">
                                <label for="full_name">
                                  Full Name <span class="red">*</span>
                                </label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter Full Name" data-parsley-required="true" data-parsley-required-message="Please enter your name." data-parsley-maxlength="50" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                              </div>

                              <div class="form-group">
                                <label for="phone_number">
                                  Mobile Number <span class="red">*</span>
                                </label>
                                <div class="flexdiv">
                                  <div class="countrycode">
                                    <input type="text" class="form-control " name="country_code" id="country_code" placeholder="+256" value="+256" data-parsley-pattern="^[0-9*#+]+$" data-parsley-pattern-message="Please enter valid country code.">
                                  </div>
                                  <div class="inlineblock">
                                    <input type="text" class="form-control" name="mobile_no" id="mobile_no" placeholder="Enter Mobile Number" data-parsley-pattern="^[0-9*#+]+$" 
                                    data-parsley-required 
                                    data-parsley-pattern-message="Please enter valid mobile number." data-parsley-required="true" 
                                    {{-- data-parsley-maxlength="18"  --}}
                                    data-parsley-maxlength-message="Mobile No must be less than 14 digits."
                                    {{-- data-parsley-minlength-message="Mobile No should be of 10 digits." --}} data-parsley-required-message="Please enter mobile no.">
                                  </div>
                                </div>
                                
                              </div>

                              <div class="form-group">
                                <div class="labels-readio-i">I am a:</div>
                                <div class="radio-btns">
                                  <div class="radio-btn">
                                    <input type="radio" class="form-check-input" checked id="type1" name="user_type" value="buyer"> 
                                    <label for="type1">Buyer </label>
                                     <div class="check"></div>
                                  </div>
          
                                  <div class="radio-btn">
                                    <input type="radio" class="form-check-input" id="type2" name="user_type" value="seller"> 
                                    <label for="type2">Seller</label>
                                     <div class="check"></div>
                                  </div>
                                  <div class="clearfix"> </div>
                                </div>
                              </div>
                              <div class="butn-sc">
                                <button type="button" class="btn btn-success float-right" id="btn_signup">Submit</button>
                              </div>
                            </form>
                            <ul class="social-footer popup-footer-socials">
                              <li><a class="facebook" target="_blank" href="{{isset($site_setting_arr['fb_url'])?$site_setting_arr['fb_url']:""}}"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>

                              <li><a class="insta" target="_blank" href="{{isset($site_setting_arr['instagram_url'])?$site_setting_arr['instagram_url']:""}}"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>

                              <li><a class="linkdin" target="_blank" href="{{isset($site_setting_arr['linkdin_url'])?$site_setting_arr['linkdin_url']:""}}"><i class="fa fa-linkedin"></i></a></li>

                              <li><a class="whatsapp" target="_blank" href="{{isset($site_setting_arr['whatsapp_url'])?$site_setting_arr['whatsapp_url']:""}}"><i class="fa fa-whatsapp"></i></a></li>

                          {{--     <li><a target="_blank" href="{{isset($site_setting_arr['whatsapp_url'])?$site_setting_arr['whatsapp_url']:""}}"><i class="fa fa-whatsapp"></i></a></li> --}}

                              {{-- <li><a class="youtube" target="_blank" href="{{isset($site_setting_arr['youtube_url'])?$site_setting_arr['youtube_url']:""}}"><i class="fa fa-youtube"></i></a></li> --}}

                          </ul>
                          </div>
                          <div class="border-top my-3"></div>
                        </div>
                    </div>
                <div class="clr"></div>
                
            </div>
            
        </div>
    </div>
</div> 

@php  
   $get_url =  URL::current();
   if($get_url == 'https://rejoiz.ug'){
 @endphp
   <script type="text/javascript" src="{{url('/')}}/assets/front/js/jquery-1.11.3.min.js"></script> 

 @php  } @endphp
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/JavaScript-autoComplete/1.0.4/auto-complete.js" type="ec049b734dfdf605d1bce94a-text/javascript"></script> -->
<script type="text/javascript">
   
   var guest_url = "{{url('/')}}";
   var guest_redirect_url = window.location.href;
   var module_url_path = '{{$module_url_path or ''}}'; 

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



$(document).ready(function()
{
  $('#btn_signup').click(function() {

  if ($('#info-form').parsley().validate() == false) return;

  var form_data = $('#info-form').serialize();
  var url = "{{url('/')}}/visitors_enquiry";

  if ($('#info-form').parsley().isValid() == true) {

    $.ajax({
          url: url,
          data: form_data,
          type:"POST",
          dataType:'json',
          beforeSend: function() {
               showProcessingOverlay();
               $('#btn_signup').prop('disabled', true);
               $('#btn_signup').html('Please Wait <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
            },
          success:function(response)
          {
             location.reload();
          }
          
        }); 
  }
  });
  
  var flash_message = $('#flash_message').val();
  var flash_type = $('#flash_type').val();

  if(flash_message  != '' && flash_type != '' )
  {
    if(flash_type == 'success')
    {
      swal(flash_type,flash_message,'success');
    }
    else if(flash_type == 'danger')
    {
      swal('Warning',flash_message,'warning');
    }
    else
    {
      swal('Error',flash_message,'error');
    }
  }

  $('#btnClose').click(function(){
    
       $('#CongratulationsModal').modal('hide');
     $('#ModalCategory').modal('show');

  });
    
  var login_count = $('#login_count').val();

  if(login_count!='' && login_count == 0)
  {
     update_user_active_time();
     $('#CongratulationsModal').modal('show');

      /*after 5 sec is_login status will update*/
      setTimeout(function(){
        updateIsloginStatus();
          
      }, 5000);

  }
});
 /* welcome modal for rejoiz */
$(window).on('load',function(){  
      if (!sessionStorage.getItem('shown-modal')){
         $('#WelcomeModal').modal('show');
          sessionStorage.setItem('shown-modal', 'true');
          }
      });

    $('#welcomeClose').click(function(){
    
       $('#WelcomeModal').modal('hide');
    });
    $('#alreadyRegistered').click(function(){
    
    $('#WelcomeModal').modal('hide');
 });


/*after 5 sec update login count*/

  function updateIsloginStatus()
  {
       $.ajax({
          url: module_url_path+'/is_login_update',
          type:"GET",
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                
                var html = '<div class="alert alert-danger" >'+response.error_message+'</div>';

                $('#error-msg').html(html);
             }
             else
             {
               
             }
          }
          
        }); 
  }

  
  $('#btn_skip').click(function(){
     
     $('#ModalCategory').modal('hide');
     

        $.ajax({
          url: module_url_path+'/is_login_update',
          type:"GET",
          dataType:'json',
          success:function(response)
          {
            location.reload()
            //$('#CongratulationsModal').modal('hide');
            /* if(response.status == 'success')
             {
                
                var html = '<div class="alert alert-danger" >'+response.error_message+'</div>';

                $('#error-msg').html(html);
             }
             else
             {
               
             }*/
          }
          
        });
   
  });


  $('#btn_submit').click(function(){
     
      var csrf_token      = $("input[name=_token]").val();
      var category_id_arr = category_arr= [];
      
      $.each($("input[name='category_checkbox']:checked"), function(){
        category_id_arr.push($(this).val());
          

      });

      if(category_id_arr.length == 0)
      {
        swal('Warning','Please select atleast one category','warning');
        return false;
      }      
      else if(category_id_arr.length >3)
      {
        swal('Warning','You can select maximum three categories','warning');
        return false;
      }
      else
      {
        window.location = '{{url('/')}}/search?category_id_arr='+encodeURIComponent(JSON.stringify(category_id_arr));
      }

      
   

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


</script>

@endsection