@extends('front.layout.master')
@section('main_content')
@php

$login_user = Sentinel::getUser();
$cnt_sku_wise_data = 0;
//dd($first_prod_detail_mul_images);

$max_product_purchase_qty = isset($arr_site_setting['product_max_qty'])?$arr_site_setting['product_max_qty']:1000;

$menu_settings = array();
$menu_settings = get_menu_detail();
foreach($menu_settings as $menus){
  array_push($menu_settings,$menus['menu_slug']);
}


@endphp

@php
  $arr_sku_wise_count = [];
@endphp
<style type="text/css">
  .outofstock_listing{
    font-weight: normal;
  }
 .morecontent span {
    display: none;
}
.morelink {
    display: block;
    color: red;
    font-size:12px;
}
.morelink:hover,.morelink:focus{
  color: red;
}
.morelink.less
{
  color: red;
}
.content-list-seller.height-new-arrivals{
    /*height: 130px;*/

    height: 152px;
   }
.retail_price_product .pricewholsl {display:inline-block;}
.nav > li > a:hover, .nav > li > a:focus {background:none;}
.second_div { margin-top: 10px; }
.li_a_cls {
    letter-spacing: .05em;
    border-radius: 60px;
    padding: 4px 16px 3px;
    font-family: 'open_sansregular';
    font-weight: inherit;
    background-color: #fff !important;
    color: #313131 !important;
    font-family: 'open_sanssemibold' !important;
    border: 1px solid #bbbbbb !important;
}

.li_a_cls_active{
  background-color: #666 !important;
    color: #fff !important;
    font-family: 'open_sanssemibold' !important;
    border: 1px solid #313131 !important;
}
.prod-det-sidenav-sku {margin-top:15px; padding:0px;}
.prod-det-sidenav-sku ul li {margin-left:0px;}
.new-button-login-pb{
  margin-top:10px;
}
.new-button-login-pb .gt-button{
    border-radius: 2px;
    text-transform: none;
    width: 100%;
    font-size: 15px;
    padding: 5px 30px;
  }

  .fa-facebook {
  background: #3B5998;
  color: white;
}
.fa-twitter {
  background: #55ACEE;
  color: white;
}
.fa-linkedin {
  background: #007bb5;
  color: white;
}
.fa-pinterest {
  background: #cb2027;
  color: white;
}
.st_facebook_hcount{
  display:none;
 
}
.s_share:hover .st_facebook_hcount,.s_share:active .st_facebook_hcount
{
  display:inline-block;
}
.share-wrap {
    border: 1px solid #ced3d9;
    border-radius: 3px;
    box-shadow: 2px 2px 3px rgb(7 10 12 / 10%);
    position: absolute;
    z-index: 1;
    padding: 5px;
    background: #fff;
    white-space: nowrap;
    display: none;
    top:10px;
    width: auto;
    /* left: 0 */
}


.share-wrap {
	position: relative;
	background: #fff;
	border: 1px solid #ccc;
  width: 100%;
  text-align: center;
}
.share-wrap:after, .share-wrap:before  {
	bottom: 100%;
	left: 50%;
	border: solid transparent;
	content: "";
	height: 0;
	width: 0;
	position: absolute;
	pointer-events: none;
}
.share-wrap:before {
	border-color: rgba(194, 225, 245, 0);
	border-bottom-color: #ccc;
	border-width: 11px;
	margin-left: -11px;
}
.share-wrap:after {
	border-color: rgba(136, 183, 213, 0);
	border-bottom-color: #fff;
	border-width: 10px;
	margin-left: -10px;
}



.s_share{
  position: relative;
  display: inline-block;
}
.share-wrap .fa:hover{
  color:#fff;
}
.share-wrap .fa{
  padding: 5px;
  margin: 5px 2px;
  width: 35px;
  height: 35px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 3px;
}
.a_share:hover
{
  color: #f5507e;
}
/* New added css  for zoom effect */
#example1 .rwaltz-medium-wrap {
  display: none;
}

.cls-main-image{
    left: 0;
    top: 0;
    z-index: 2;
    opacity: 1;
    height: 100%;
    width: 100%;
    font-size: 0;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
    /*border: 1px solid rgb(234, 234, 234);*/
    padding: 30px;
    width: 100%;
    height: 100%;
    object-fit: contain;

}

#main_image:hover {
  cursor: pointer;
  
}

.rwaltz-medium-wrap {
  border: 1px solid rgb(234, 234, 234);
}

.cust-vertical{
 
    display: inline-block;
    position: relative;
    width: 90px;
    margin-right: 5px;
}
.cust-vertical .prod-controls.clickable{display: none !important;}
.cust-vertical .prod-carousel.div_mul_img.mini-slider{
  width: auto;
  display: inline-block
}
.flexContent .rwaltz-gallery .prod-carousel.mini-slider .prod-item{
  width: 90px !important;
}
.cust-vertical .prod-wrapper{
  width: 90px !important;
}
.flexContent{
  display: flex;
}
.order-1{order: 1}
.order-2{order: 2}

.imColorGrid li{
  width: 60px;height: 60px;
  margin: 5px;
  border: 1px solid #cfd5dc;
}
.color_label_cust{
  width: 100%;
  display: flex;
  align-items: center;
  margin: 5px 0;
}
.color_label_cust .color_label {
    margin: 0;
    margin-right: 5px;
}
.rwaltz-gallery .prod-carousel.mini-slider .prod-item{
  margin: 0 !important;
}

</style>
@if(Request::segment(2) == 'product_detail')

<script type="text/javascript" src="https://kenwheeler.github.io/slick/slick/slick.js"></script>

<!-- Demo Script Start -->
<script>
    $(document).ready(function(){
  $(".SlickCarouselpro").slick({
    rtl:false, // If RTL Make it true & .slick-slide{float:right;}
    autoplay:true, 
    autoplaySpeed:5000, //  Slide Delay
    speed:800, // Transition Speed
    slidesToShow:4, // Number Of Carousel
    slidesToScroll:1, // Slide To Move 
    pauseOnHover:false,
    appendArrows:$(".Container .Head .Arrows"), // Class For Arrows Buttons
    prevArrow:'<span class="Slick-Prev"></span>',
    nextArrow:'<span class="Slick-Next"></span>',
    easing:"linear",
    responsive:[
      {breakpoint:801,settings:{
        slidesToShow:3,
      }},
      {breakpoint:641,settings:{
        slidesToShow:3,
      }},
      {breakpoint:481,settings:{
        slidesToShow:1,
      }},
    ],
  })
})
</script>
@endif
<script   type="text/javascript" src="{{url('/')}}/assets/front/js/gallery.min.js"></script>
<script   type="text/javascript" src="{{url('/')}}/assets/front/js/jquery.zoom.js"></script>
    <script>
      $(document).ready(function() {
        $('#example1').rwaltzGallery({
          openGalleryStyle: 'transform',
          changeMediumStyle: true
        });
        $('#main_image').zoom();
      });

    </script> 

<!-- Product Detail link Start -->
    {{-- <script defer async type="text/javascript" src="{{url('/')}}/assets/front/js/jdetail.js"></script> --}}
    <link href="{{url('/')}}/assets/front/css/prodetail.css" rel="stylesheet" type="text/css" />

    <link href="{{url('/')}}/assets/front/css/gallery.css" rel="stylesheet" type="text/css" />
   


    <!-- Product Detail link End -->


<!-- Breadcrubm section Start -->
<section class="prodetail_sec breadcrumb_section">
      <div class="container">
        <div class="row">
          <div class="pro_breadcrumb">
         <ul class="breadcrumb">
          <li><a href="{{url('/')}}">Home </a></li>
                  @php
                    $category_id = '';

                    if(isset($first_prod_arr['category_details']['id']) && !empty($first_prod_arr['category_details']['id']))
                    {
                        $category_id =  base64_encode($first_prod_arr['category_details']['id']);
                    }
                    

                 @endphp

          <li><a href="{{url('/').'/search'}}">Products</a></li>
                 
          <li><a href="{{url('/search?category_id=')}}{{$category_id}}">{{isset($first_prod_arr['category_details']['category_name'])?$first_prod_arr['category_details']['category_name']:"Category"}}</a></li>
          <li>{{$product_arr['product_name'] or ''}}</li>
        </ul> 
      </div>
        </div>
      </div>
</section>

<!-- Breadcrubm section End -->

@php 

// $cat_url = URL::previous();
// // dd($cat_url);
$previous_url = $role ='';
if(Request::session()->has('_previous'))
{
  $previous_url = isset(Session::get('_previous')['url'])?Session::get('_previous')['url']:'';
  if($login_user != null && $login_user->inRole('retailer'))
  {
    $role = 'retailer';
  }

}


@endphp

<!-- Product Detail Section Start -->
<section class="prodetail_sec">
  <div class="container">
    <div class="alert alert-danger" id="alert-danger" style="display: none;">
        <button type="button" class="close" onclick="close_button()" style="margin-top: 0px !important;padding: 0px !important;" data-dismiss="alert" >×</button>
                  You are not able to purchase this product, please login as customer. 
     </div>
    <div class="row">

      <div class="col-sm-12 col-md-6 col-lg-6 flexContent">

    
          <!-- gallery hear -->
        <div class="rwaltz-gallery img300 order-2">
          <div class="rwaltz-medium-wrap zoom " id='main_image'>
            @php
              $product_main_img       = false;
              $product_main_img_thumb = false;                      
              $product_main_image_base_path  = base_path('storage/app/'.$first_prod_arr['product_image']);
              $product_main_img_thumb      = image_resize($product_main_image_base_path,77,77);
              $product_main_img            = image_resize($product_main_image_base_path,400,400,false,true);
              $product_original_main_image = url('/').'/storage/app/'.$first_prod_arr['product_image'];

              $product_main_img  = imagePath($first_prod_arr['product_image'],'product',0);

            @endphp
            <img class="cls-main-image" src="{{$product_main_img or ''}}" >                
          </div>
        </div>
          <div id="example1" class="rwaltz-gallery img300 order-1 cust-vertical">
            <div class="prod-carousel div_mul_img" >

              @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 


                @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);
                      // $product_image_base_path  = $image_url.$product_details['image'];

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400,false,true);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];
                      //$product_original_image = $image_url.$product_details['image'];


                      if(isset($imgSku) && $imgSku == $product_details['sku'])
                      {
                       $imgSkuSquence = $prod_key;
                      }

                      if($login_user != null && $login_user->inRole('customer'))
                      {
                         $product_min_qty_value = 1;
                      }
                      elseif ($login_user != null && $login_user->inRole('retailer')) 
                      {
                         $product_min_qty_value = isset($product_details['product_min_qty'])?$product_details['product_min_qty']:1;
                      }
                      else
                      {
                         $product_min_qty_value = 1;
                      }

                     @endphp     


                 <img src="{{$product_img or ''}}" data-medium-img="{{$product_img or ''}}" data-big-img="{{$product_img or ''}}" data-origional-img = "{{$product_original_image or ''}}" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-product-det-id="{{$product_details['id']}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" onclick="sku_detail(this),showAsMainImage(this)" data-product-min-qty="{{$product_min_qty_value}}" class="new-class" >


                @if(isset($first_prod_arr['product_details']) && isset($first_prod_detail_mul_images) && count($first_prod_detail_mul_images) > 0)


                @php
                $arr_sku_wise_count[$product_details['sku']][] = count($first_prod_detail_mul_images);

                @endphp
                @if(isset($first_prod_detail_mul_images[$product_details['sku']]))
                @foreach($first_prod_detail_mul_images[$product_details['sku']] as $prod_mul_key =>$product_mul_details) 
                  @php
                      $product_mul_img       = false;              
                    
                      $product_mul_image_base_path  = base_path('storage/app/'.$product_mul_details['product_image']);

                      // $product_mul_image_base_path  = $image_url.$product_mul_details['product_image'];
                      $product_mul_img_thumb      = image_resize($product_mul_image_base_path,77,77);
                      $product_mul_img            = image_resize($product_mul_image_base_path,400,400);

                      $product_mul_original_image = url('/').'/storage/app/'.$product_mul_details['product_image'];

                      //$product_mul_original_image = $image_url.$product_mul_details['product_image'];
                     @endphp     
                
                 <img src="{{$product_mul_img or ''}}" data-medium-img="{{$product_mul_img or ''}}" data-big-img="{{$product_mul_img or ''}}" data-origional-img = "{{$product_mul_original_image or ''}}" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" class="cls_hide_show_mul_img multiple_image_{{$product_details['id']}}" onclick="showAsMainImage(this)">

                 
                   @endforeach
                  @endif
                  @endif
                @endforeach
              @endif

              
              
            </div>
           {{--  <div class="prod-carousel second_div">

              @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                  @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);
                      //$product_image_base_path  = $image_url.$product_details['image'];

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];
                      //$product_original_image = $image_url.$product_details['image'];


                      if(isset($imgSku) && $imgSku == $product_details['sku'])
                      {
                       $imgSkuSquence = $prod_key;
                     }

                      if($login_user != null && $login_user->inRole('customer'))
                      {
                         $product_min_qty_value = 1;
                      }
                      elseif ($login_user != null && $login_user->inRole('retailer')) 
                      {
                         $product_min_qty_value = isset($product_details['product_min_qty'])?$product_details['product_min_qty']:1;
                      }
                      else
                      {
                         $product_min_qty_value = 1;
                      }

                     @endphp     


                 <img src="{{$product_img or ''}}" data-medium-img="{{$product_img or ''}}" data-big-img="{{$product_img or ''}}" data-title="Mustang Shelby GT500 - big black car with red lines is very beautiful and powerful" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-product-det-id="{{$product_details['id']}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" onclick="sku_detail(this)" data-product-min-qty="{{$product_min_qty_value}}">

                @endforeach
              @endif

              
            </div> --}}
            {{-- <div class="third_div"> --}}
              {{-- <ul class="list-inline">
                <li class="list-inline-item">
                  <a class="badge badge-primary" href="#">fgdfksjd</a>
                </li>
              </ul> --}}
              {{-- <div class="col-sm-12 prod-det-sidenav-sku p-0">
                  <ul class="list-inline">
                     @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                  @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);
                      //$product_image_base_path  = $image_url.$product_details['image'];

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];
                      //$product_original_image = $image_url.$product_details['image'];


                      if(isset($imgSku) && $imgSku == $product_details['sku'])
                      {
                       $imgSkuSquence = $prod_key;
                     }

                      if($login_user != null && $login_user->inRole('customer'))
                      {
                         $product_min_qty_value = 1;
                      }
                      elseif ($login_user != null && $login_user->inRole('retailer')) 
                      {
                         $product_min_qty_value = isset($product_details['product_min_qty'])?$product_details['product_min_qty']:1;
                      }
                      else
                      {
                         $product_min_qty_value = 1;
                      }

                     @endphp   --}}   
                    {{-- <li class="list-inline-item"> --}}
                      {{-- <a id="li_a_{{$product_details['id']}}"
                      class="li_a_cls label  "
                      data-produt-id="{{base64_encode($product_details['product_id'])}}" 
                      data-product-det-id="{{$product_details['id']}}" 
                      data-sku-id="{{base64_encode($product_details['sku'])}}" 
                      data-sku-description="{{ $product_details['sku_product_description'] }}" 
                      data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" 
                      data-medium-img="{{$product_img or ''}}" 
                      data-origional-img="{{$product_original_image or ''}}"
                      onclick="sku_detail(this)" 
                      data-product-min-qty="{{$product_min_qty_value}}">{{$product_details['sku']}}
                      </a> --}}
                      
                     {{--  <img src="{{$product_img or ''}}" title="{{$product_details['sku']}}" data-medium-img="{{$product_img or ''}}" data-big-img="{{$product_img or ''}}" data-origional-img = "{{$product_original_image or ''}}" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-product-det-id="{{$product_details['id']}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" onclick="sku_detail(this),showAsMainImage(this)" data-product-min-qty="{{$product_min_qty_value}}" class="new-class" height="50px" width="50px">
                    
                    </li>
                      
                @endforeach
              @endif 
                  </ul>
            </div>
            </div> --}}
          </div>
          <!-- Get product details id from name array -->
          @php 
          $mycnt = 0;
          
          @endphp
          @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 
                 @php 
                 // dd($product_details);
                  if(isset($first_prod_detail_mul_images[$product_details['sku']])){            
                    $cnt_sku_wise_data = count($first_prod_detail_mul_images[$product_details['sku']]) + 1;
                  }
                @endphp
                <input type="hidden" name="prod_det_id[]" class="div_mul_img" id="prod_det_id{{$mycnt}}" value="{{$product_details['id']}}">
                 <input type="hidden"  id="sku_wise_prod_det_id_{{$product_details['id']}}" value="{{$cnt_sku_wise_data}}">  
                @php
                 $mycnt++;
                 @endphp
                @if(isset($first_prod_detail_mul_images) && count($first_prod_detail_mul_images) > 0)
                @if(isset($first_prod_detail_mul_images[$product_details['sku']]))
                @foreach($first_prod_detail_mul_images[$product_details['sku']] as $prod_mul_key =>$product_mul_details)
                 <input type="hidden" name="prod_det_id[]" class="div_mul_img" id="prod_det_id{{$mycnt}}" value="{{$product_mul_details['product_detail_id']}}"> 
                 @php
                 $mycnt++;
                 @endphp
                 @endforeach
              @endif
              @endif
              @endforeach
              @endif

          <!-- gallery hear End -->
        </div>
                <form id="frm-add-to-bag">
                     @php
            
                        $prod_user_id = isset($first_prod_arr['user_id'])?$first_prod_arr['user_id']:'';


                           if($login_user !=null)
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


            <div class="col-sm-12 col-md-6 col-lg-6 pro_detail_right">
              <!-- <h5>{{$product_arr['brand_details']['brand_name'] or ''}}</h5> -->
                        <h5>
                            @if(isset($maker_arr['maker_details'])) 
                            <span class="form-txt">By</span> 
                             <a @if(!in_array('vendors',$menu_settings)) style="cursor: auto !important;" href="#" @else href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}" @endif>{{$maker_arr['maker_details']['company_name']}}
                             </a>

                             <!-- <a href="#" style="cursor: auto !important;">{{$maker_arr['maker_details']['company_name']}}</a> -->
                             @php 

                             $get_minimum_order = get_maker_shop_setting($maker_arr['maker_details']['user_id']);
                             $minimum_order = "";
                             if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum'] == 0){$minimum_order = 'No Minimum Limit';}
                             else if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum']!=0)
                             {$minimum_order = '$'. num_format($get_minimum_order['first_order_minimum']).' Minimum';} 
                             
                             @endphp
                             
                             @if($login_user != null)
                             
                              @if(($login_user->inRole('customer') || $login_user->inRole('influencer') || $login_user->inRole('retailer')) && ($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || ($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                                <div class="pro-title-list brand_price">{{$minimum_order}} </div>
                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer') || $login_user->inRole('retailer')) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)
                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer') || $login_user->inRole('retailer')) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                                <div class="pro-title-list brand_price">{{$minimum_order}} </div>  
                              @else
                                <div class="pro-title-list brand_price">{{$minimum_order}} </div>  
                              @endif 
                            @else
                              @if(($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || ($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                                <div class="pro-title-list brand_price">{{$minimum_order}} </div>                        
                              @elseif($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                                <div class="pro-title-list brand_price">{{$minimum_order}} </div>
                              @elseif($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)
                              @endif
                            @endif
                            @endif

                       </h5>
                        <!-- product min qty -->

                     

                         @if(isset($product_arr['case_quantity']) && $product_arr['case_quantity']!="")
                         
                            <div class="title-detail-pg">Case Qty : {{$product_arr['case_quantity'] or ''}}</div>
                         @endif

                          @if($login_user != null && ($login_user->inRole('customer') == false))

                        @if(isset($first_prod_arr['product_details'][0]['product_min_qty']) && $first_prod_arr['product_details'][0]['product_min_qty']!='')

                              <h5>
                                <div class="pro-title-list brand_price" id="product_min_qty_value">Product Min Qty : 
                                   {{isset($first_prod_arr['product_details'][0]['product_min_qty'])?$first_prod_arr['product_details'][0]['product_min_qty']:''}}
                                </div>
                              </h5>
                        @endif

                      @endif    

                        @if(isset($product_arr['restock_days']) && $product_arr['restock_days']!="")
                        
                         <div class="pro-title-list">Restock Day : {{$product_arr['restock_days'] or ''}} days
                        </div> 

                        @endif
                         

                        <h4>@if(isset($first_prod_arr['category_details']['id']))  
     
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
                      </h4>
                      <h2>{{$product_arr['product_name'] or ''}}</h2>
                        
                        
                        @if($login_user != null) 
                                                  
                          @if((($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']['is_get_a_quote'])&& $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                           <h3>Price <span>${{$product_arr['unit_wholsale_price'] or ''}} </span></h3>                        
                          @elseif(($login_user->inRole('retailer') || $login_user->inRole('representative') || $login_user->inRole('sales_manager'))&& (isset($maker_arr['maker_details']) && (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0)))
                            <h3>Price <span>${{$first_prod_arr['unit_wholsale_price'] or ''}} </span></h3>                        
                          @elseif($login_user->inRole('retailer') && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_add_to_bag'] == 0)  

                          @elseif(($login_user->inRole('retailer') || $login_user->inRole('representative') || $login_user->inRole('sales_manager')) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_add_to_bag'] == 1)  
                            <h3>Price <span>${{$first_prod_arr['unit_wholsale_price'] or ''}} </span></h3>

                          @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && isset($maker_arr['maker_details']) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_add_to_bag'] == 0)

                          @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_add_to_bag'] == 1)                          
                          <h3>Price <span>${{$product_arr['unit_wholsale_price'] or ''}} </span></h3>
                          @endif 
                        @else
                          @if(isset($maker_arr['maker_details']['is_get_a_quote']) && (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                            <h3>Price <span>${{$product_arr['unit_wholsale_price'] or ''}} </span></h3>
                          @elseif(isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)  
                          @elseif(isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                            <h3>Price <span>${{$product_arr['unit_wholsale_price'] or ''}} </span></h3>
                          @else
                            <h3>Price <span>${{$product_arr['unit_wholsale_price'] or ''}} </span></h3>
                          @endif
                        @endif

                        <div id="color_data" class="color_label_cust">
                    
                        </div>
              
                  <ul class="list-inline imColorGrid">
                     @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                  @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);
                      //$product_image_base_path  = $image_url.$product_details['image'];

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];
                      //$product_original_image = $image_url.$product_details['image'];


                      if(isset($imgSku) && $imgSku == $product_details['sku'])
                      {
                       $imgSkuSquence = $prod_key;
                     }

                      if($login_user != null && $login_user->inRole('customer'))
                      {
                         $product_min_qty_value = 1;
                      }
                      elseif ($login_user != null && $login_user->inRole('retailer')) 
                      {
                         $product_min_qty_value = isset($product_details['product_min_qty'])?$product_details['product_min_qty']:1;
                      }
                      else
                      {
                         $product_min_qty_value = 1;
                      }

                     @endphp     
                    <li class="list-inline-item">
                      {{-- <a id="li_a_{{$product_details['id']}}"
                      class="li_a_cls label  "
                      data-produt-id="{{base64_encode($product_details['product_id'])}}" 
                      data-product-det-id="{{$product_details['id']}}" 
                      data-sku-id="{{base64_encode($product_details['sku'])}}" 
                      data-sku-description="{{ $product_details['sku_product_description'] }}" 
                      data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" 
                      data-medium-img="{{$product_img or ''}}" 
                      data-origional-img="{{$product_original_image or ''}}"
                      onclick="sku_detail(this)" 
                      data-product-min-qty="{{$product_min_qty_value}}">{{$product_details['sku']}}
                      </a> --}}
                      
                      <img src="{{$product_img or ''}}" title="{{$product_details['sku']}}" data-medium-img="{{$product_img or ''}}" data-big-img="{{$product_img or ''}}" data-origional-img = "{{$product_original_image or ''}}" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-product-det-id="{{$product_details['id']}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-color="{{$product_details['color']}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" onmouseover="showcolor(this)"  onclick="sku_detail(this),showAsMainImage(this),showcolor(this),checkSizeInventory()" onload="checkSizeInventory()" data-product-min-qty="{{$product_min_qty_value}}" class="new-class sku_img_class" id="sku_img{{$prod_key}}" height="50px" width="50px">

                    
                    </li>
                      
                @endforeach
              @endif 

                  </ul>
                  <input type="text" id="hidden_sku_no" name="hidden_sku_no" hidden="true">
                  
                  
                  <input type="hidden" name="color" id="input_color">
                  <input type="hidden" name="size_inventory" id="total_left_size">

                  @if(isset($size_arr) && count($size_arr)>0)
                  <div class="form-group">
                        <label>Size</label>
                            <select id="size"  name="size" class="form-control" data-produt-id="{{$product_details['product_id']}}" onchange="checkSizeInventory()" >
                            <option value="">Select Size</option>
                            
                              @foreach($size_arr as $size)

                                <option value="{{$size['id'] or 0}}"> {{ $size['size']?$size['size']:'NA'}}</option>
                              @endforeach
                           
                          </select>
                    </div>
                   @endif
                        <div class="li-left" style="display: none;">Quantity :</div><div id = "quantity" class="newoptionvalue" style="display: none;">{{isset($first_prod_arr['product_details'][0]['inventory_details']['quantity'])?$first_prod_arr['product_details'][0]['inventory_details']['quantity']:0}}</div>

                   
                        @php  

                          $minValue = '';

                          if($login_user != null)
                          {
                                if($login_user != null && $login_user->inRole('customer'))
                                {
                                   $minValue = 1;
                                }
                                else
                                {
                                   $minValue = isset($product_arr['product_details'][0]['product_min_qty'])?$product_arr['product_details'][0]['product_min_qty']:'1';
                                }
                          }
                          else
                          {

                             $minValue = 1;
                          }

                        
                        @endphp 


                        @if(isset($maker_arr['maker_details']) && ($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))             
                        <div class="qtydrop">
                          <div class="user-box product-details-box" >

                            <label class="form-lable">Item Quantity</label>                
                            <input class="vertical-spin bucket_spin" 
                                data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value ="{{$minValue}}" data-parsley-type="integer" data-parsley-trigger="change" data-parsley-max="{{$max_product_purchase_qty or '10000'}}" onkeydown="return (event.keyCode!=13);"> 

                            <input type="hidden" id = "prod_qty"> 
                            <div id="error_item_qty" style="display: none;"></div>             
                            </div>
                        </div>
                        @elseif(isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)
                        @else
                        <div class="qtydrop">
                          <div class="user-box product-details-box" >
                            <label class="form-lable">Item Quantity</label>                
                            <input class="vertical-spin bucket_spin" 
                                data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value ="{{$minValue}}" data-parsley-type="integer" data-parsley-trigger="change" data-parsley-max="{{$max_product_purchase_qty or '10000'}}" onkeydown="return (event.keyCode!=13);"> 
                            <input type="hidden" id = "prod_qty"> 
                            <div id="error_item_qty" style="display: none;"></div>             
                            </div>
                        </div>
                        @endif  
        
                        <input type="hidden" name="product_min_qty" id="product_min_qty" value="{{$minValue}}"> 

                    

                        @if($login_user != null && 
                          ($login_user->inRole('maker')== true || 
                           $login_user->inRole('influencer') == true|| $login_user->inRole('sales_manager') == true || $login_user->inRole('representative') == true || $login_user->inRole('admin') == true))

                            <div class="button-login-pb" >
                              <!-- onclick="unable_to_add()" -->
                            <a disabled ="true" class="gt-button cursor-not" title="You are not able to purchase this product, please login as customer. ">Add to Cart</a>
                            <!-- style="cursor: not-allowed;" -->
                            <div class="clearfix"></div>
                            </div>
                        @elseif(isset($maker_arr['maker_details']) && ($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))             
                            <div class="button-login-pb" >
                            <a href="javascript:void(0)" class="gt-button" id="add-to-bag" style= "display:{{$show_add_cart_btn}}" >Add to Cart</a>
                            <div class="clearfix"></div>
                            </div>                        
                        @elseif(isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)                          
                        @else
                          <div class="button-login-pb" >
                          <a href="javascript:void(0)" class="gt-button" id="add-to-bag" style= "display:{{$show_add_cart_btn}}" >Add to Cart</a>
                          <div class="clearfix"></div>
                          </div>  
                        @endif

                        @if((isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1) || (isset($first_prod_arr['unit_wholsale_price']) && $first_prod_arr['unit_wholsale_price'] <= 0))
                        <div class="button-login-pb width-50" >
                          <!-- data-toggle="modal" data-target="#get_a_Quote" -->
                          <a href="javascript:void(0)" onclick="openGetAQuoteModal(this)"  class="gt-button" id="get-a-quote-modal" >Get A Quote</a>
                          <div class="clearfix"></div>
                        </div>
                        @endif

                        <div class="s_share width-50">
                            <a  class="a_share" href="javascript:void(0);">
                              <i class="fa fa-share-alt"></i>
                                Share
                            </a>

                          <div class="st_facebook_hcount share-wrap">
                            <a href="javascript:void(0);" onclick="return fbs_click()" target="_blank" class="fa1 fa fa-facebook"></a>
                            <a href="javascript:void(0);" onclick="return twit_click()" target="_blank" class="fa1 fa fa-twitter"></a>
                            <a href="javascript:void(0);" onclick="return linkd_click()" target="_blank" class="fa1 fa fa-linkedin"></a>
                            <a href="javascript:void(0);" onclick="return pint_click()" target="_blank" class="fa1 fa fa-pinterest"></a>
                          </div>
                      </div>
                      <div class="clearfix"></div>
                            <div class="out-of-stock-container" style="display: none;">
                                <span class="outofstock">Out of stock</span>
                            </div>

                            <!-- Hide element -->
                            <input type = "hidden" id = prod_user_id value="">

                        {{-- 
                           <input type="hidden" name="product_id" id="product_id" value="{{ Request::input('product_id') }}">
                        --}}

                        <input type="hidden" name="product_id" value="{{isset($first_prod_arr['id'])?base64_encode($first_prod_arr['id']):base64_encode('')}}">

                         <input type="hidden" name="sku_no" id="sku_num" value="@if(isset($imgSku)) {{$imgSku}} @else {{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}} @endif">

                         <input type="hidden" name="retail_price" id="retail_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}">
                         <input type="hidden" name="wholesale_price" id="wholesale_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
                            <!-- end -->

                           

                             @if($login_user==null)

                                <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}"> 
                             @else
                                
                                @if($login_user->inRole('customer') || $login_user->inRole('maker')|| $login_user->inRole('admin')|| $login_user->inRole('influencer'))
                                   <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or '' }}">  
                                   <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="customer"}>

                                @else
                                   <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
                                   <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="retailer"}>

                                @endif

                             @endif
                            <input type="hidden" name="is_logged_in" id = "is_logged_in" value={{$login_user}}>


                             
                             @if($login_user != null)
                             
                             @if((($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">                        
                              @elseif(($login_user->inRole('retailer') || $login_user->inRole('representative') || $login_user->inRole('sales_manager')) && isset($maker_arr['maker_details']['is_get_a_quote'])&&($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || ($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">                        
                              @elseif(($login_user->inRole('retailer') || $login_user->inRole('representative') || $login_user->inRole('sales_manager')) && isset($maker_arr['maker_details']['is_get_a_quote'])&& $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)  
                              @elseif($login_user->inRole('retailer') && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)  
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">
                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)
                              @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">
                              @endif 
                            @else
                            
                              @if((isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">
                              @elseif(isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)  
                              
                              @elseif(isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                              
                              <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                <span class="first-span">$</span>
                                <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                              </div>
                              <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">
                              @endif
                            @endif 
                            
    <div class="description-product-details-mkr">

      

      <div class="title-mkr-above">
        About this product
      </div>
      <div class="description-tx-pdl">
       
        @php
          if(isset($product_arr['description']) && !empty($product_arr['description']))
          {
            $replace_description = strip_tags($product_arr['description'],'\r\n');

          }
        @endphp
          {{$replace_description or ''}}
      </div>
      <!-- <a href="#" class="clickreadmore">Read More</a> -->
    </div>


               
            </div>
                </form>
    </div>
  </div>
  <div class="container tab_container">
  <ul class="nav nav-tabs">
    <!--  @if(isset($first_prod_arr['product_details'][0]['sku_product_description']) && !empty($first_prod_arr['product_details'][0]['sku_product_description']))  
    <li class="active"><a href="#tab1" data-toggle="tab">Description</a></li>  
    @endif -->
     <!-- <input type="hidden" id="title_description_first" name="title_description_first" class="title_description_first" value="{{ isset($first_prod_arr['product_details'][0]['sku_product_description'])}}"> -->
    
     <input type="hidden" id="title_description_first" name="title_description_first" class="title_description_first" value="{{ isset($first_prod_arr['product_details'][0]) && ($first_prod_arr['product_details'][0]['sku_product_description']!=null || $first_prod_arr['product_details'][0]['sku_product_description']!='')?$first_prod_arr['product_details'][0]['sku_product_description']:''}}">
      @if(isset($first_prod_arr['product_details'][0]) && ($first_prod_arr['product_details'][0]['sku_product_description']!=null || $first_prod_arr['product_details'][0]['sku_product_description']!=""))
      <li class="active"><a href="#tab1" data-toggle="tab" id="title_description">Description</a></li>  
      @endif
      @if(isset($product_arr['ingrediants']) && !empty($product_arr['ingrediants']))
        <li><a href="#tab2" id="ingreTab" data-toggle="tab">Ingredients</a></li>
      @endif
    <!-- <li><a href="#tab3" data-toggle="tab">Brand Story</a></li> -->
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab1">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent=".tab-pane" href="#collapseOne">
              Description
            </a>
          </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in">
          <div class="panel-body">
            <!-- <td><div class="truncate" id="sku_change_description">{!! $product_arr['description'] or '' !!}</div></td> -->
            <!-- {{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}} -->
            @if(isset($first_prod_arr['product_details'][0]['sku']) && !empty($first_prod_arr['product_details'][0]['sku']))            
              <td><div class="truncate" id="sku_change_description">{!! $first_prod_arr['product_details'][0]['sku_product_description'] or '' !!}</div></td>
            @endif
            <!-- {{$product_arr['description'] or ''}} -->
          </div>
        </div>
      </div>
    </div>
    <div class="tab-pane" id="tab2">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent=".tab-pane" href="#collapseOne">
              Ingrediants
            </a>
          </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in">
          <div class="panel-body">
            <td><div class="truncate" id="sku_change_ingrediants">{{$product_arr['ingrediants'] or ''}}</div></td>
            <!-- {{$product_arr['description'] or ''}} -->
          </div>
        </div>
      </div>
    </div>
   
  </div>
</div>
</section>
<!-- Product Detail Section End -->




<!--Add Home page slider hearer-->
<section>
  <div class="container">
    <div>
      <div class="best-seller-main-dv mobilepadding-o">
<div>
<hr>

@if(isset($related_product_arr) && count($related_product_arr)>0)
<div class="titleof-seller-home">
    More collections you might like
</div>
@endif
<div class="clearfix"></div>
<!-- Demo Slider Start -->
<div class="Container slickcontainer">
    <h3 class="Head"><span class="Arrows"></span></h3>
    <!-- Carousel Container -->
    <div class="SlickCarouselpro">
        <!-- Item --> 
        @if(isset($related_product_arr) && count($related_product_arr)>0)
                  @foreach($related_product_arr as $product)
                     @php  


                        $product['product_details'] = $product;                   
                       

                        $product_img = false;

                        $product_base_img = isset($product['product_details']['product_image']) ? $product['product_details']['product_image'] : '';

                        if($product_base_img != ""){
                        $product_img_path  =  base_path('storage/app/'.$product_base_img);
                        //$product_default_image = url('/assets/images/no-product-img-found.jpg');
                        //$product_img_path  =  $image_url.$product_base_img;
                        $product_default_image = get_default_image('product');
                        $product_img = image_resize($product_img_path,370,370,false,true);
                      }
                      else {
                        //$product_default_image = url('/assets/images/no-product-img-found.jpg'); 
                        $product_default_image = get_default_image('product');
                        $product_img = image_resize($product_image_base_path,320,320,$product_default_image);
                    }

                     @endphp                                
                      <div class="ProductBlock">
                          <div class="best-seller-list">
                              <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($product['product_details']['id'])}}&vendor_id={{base64_encode($product['product_details']['user_id'])}}">
                                  <div class="btn06">
                                      <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">
                                      <!-- <div class="ovrly"></div> -->
                                      <div class=""></div>
                                      <div class="buttons">
                                          <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                              </a>
                              </div>
                              </div>
                              <div class="content-list-seller height-new-arrivals">
                                  <div class="one-linetxt" title="Product name"> {{ucfirst($product['product_details']['product_name'])}} </div>


                              <!-- if get a quote enable then hide  inline price div -->
                              @php $maker_details = get_maker_all_details($product['user_id']); @endphp    


                              @if($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)
                                  
                                  <div class="price-product inlineprice" style="display:none;">
                                  </div>

                              @else 

                                  <div class="price-product inlineprice">
                                     <!--  <div class="retail_price_product inlineprice font-weight-normal">
                                          <i class="fa fa-usd" aria-hidden="true"></i>12
                                      </div> -->                                     
                                     
                                    
                                   {{--  @if($product['product_details']['user_id'] == $maker_arr['id'])
                                    
                                      @if($login_user != null)                                      
                                      @if((($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && 
                                      isset($maker_arr['maker_details']['is_get_a_quote'])&&
                                      $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                                        <div class="retail_price_product inlineprice font-weight-normal">                                          
                                          <span class="pricewholsl">Retail </span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                        </div>                        
                                        @elseif($login_user->inRole('retailer') && isset($maker_arr['maker_details']['is_get_a_quote']) && ($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || ($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                                        <div class="retail_price_product inlineprice font-weight-normal">
                                          <span class="pricewholsl">Wholesale</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['unit_wholsale_price'])?num_format($product['product_details']['unit_wholsale_price']) : ''}}
                                        </div>                        
                                        @elseif($login_user->inRole('retailer') && isset($maker_arr['maker_details']['is_get_a_quote']) &&$maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)  
                                        @elseif($login_user->inRole('retailer') && $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)  
                                        <div class="retail_price_product inlineprice font-weight-normal">
                                          <span class="pricewholsl">Wholesale</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['unit_wholsale_price'])?num_format($product['product_details']['unit_wholsale_price']) : ''}}
                                        </div>
                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && 
                                        isset($maker_arr['maker_details']['is_get_a_quote'])&&
                                        $maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 0)
                                        
                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer')) && 
                                        isset($maker_arr['maker_details']['is_get_a_quote'])&&
                                        $maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                                        <div class="retail_price_product inlineprice font-weight-normal">                                          
                                          <span class="pricewholsl">Retail </span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                        </div>
                                        @endif 
                                      @else
                                        @if(($maker_arr['maker_details']['is_get_a_quote'] == 1 && $maker_arr['maker_details']['is_add_to_bag'] == 1) || ($maker_arr['maker_details']['is_get_a_quote'] == 0 && isset($maker_arr['maker_details']['is_get_a_quote']) && $maker_arr['maker_details']['is_add_to_bag'] == 0))
                                        <div class="retail_price_product inlineprice font-weight-normal">                                          
                                          <span class="pricewholsl">Retail</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                        </div>                        
                                        @elseif($maker_arr['maker_details']['is_get_a_quote'] == 1 && isset($maker_arr['maker_details']['is_get_a_quote'])&& $maker_arr['maker_details']['is_add_to_bag'] == 0)  
                                        @elseif($maker_arr['maker_details']['is_get_a_quote'] == 0 && $maker_arr['maker_details']['is_add_to_bag'] == 1)
                                        <div class="retail_price_product inlineprice font-weight-normal">                                          
                                          <span class="pricewholsl">Retail</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                        </div>
                                        @endif
                                      @endif
                                    @else
                                      @if($login_user != null) 
                                        @if($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('admin')|| $login_user->inRole('influencer'))
                                          <div class="retail_price_product inlineprice font-weight-normal">                                          
                                            <span class="pricewholsl">Retail</span>
                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                          </div>
                                        @elseif($login_user->inRole('retailer'))
                                          <div class="retail_price_product inlineprice font-weight-normal">
                                            <span class="pricewholsl">Wholesale</span>
                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['unit_wholsale_price'])?num_format($product['product_details']['unit_wholsale_price']) : ''}}
                                          </div>  
                                        @endif
                                      @else
                                        <div class="retail_price_product inlineprice font-weight-normal">                                          
                                          <span class="pricewholsl">Retail</span>
                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                        </div>  
                                      @endif
                                    @endif --}}


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


                                  <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($product['user_id'])}}" title="{{$product['brand_name']}}" class="title-product">{{isset($product['brand_name'])?($product['brand_name']):""}}</a>
                                  
                                    <!--out of stock  -->

                                    @php  

                                        $temp_product_id = isset($product['product_details']['id'])?$product['product_details']['id']:0;

                                        $sku = get_sku($product['product_details']['id']);
                                              
                                        $temp_all_sku = get_all_sku($temp_product_id);
                                             
                                        $temp_all_sku = array_column($temp_all_sku,'quantity');

                                        $product_inventory = array_sum($temp_all_sku);

                                        $is_in_stock = check_moq_inventory($product['product_details']['id']);

                                    @endphp

                                    @if(isset($product_inventory) && $product_inventory == 0 || ($login_user == true &&  ($login_user->inRole('retailer')) && $is_in_stock == false))
                                    
                                      <span class="red outofstock_listing">Out of stock</span>
                                   
                                    @endif



                                    <!-- Get a Quote button -->

                                    @if((isset($maker_details['is_get_a_quote']) && $maker_details['is_get_a_quote'] == 1) || (isset($product['unit_wholsale_price']) && $product['unit_wholsale_price'] <= 0))

                                        <div class="new-button-login-pb" >

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

                                               onclick="openGetAQuoteModalSimialProduct(this)"  class="gt-button" id="get-a-quote-modal" >Get A Quote
                                            </a>

                                            <div class="clearfix"></div>

                                        </div>

                                      @endif

                                  
                              </div>
                              <!-- </a> -->
                          </div>
                      </div>
        @endforeach
        @endif
      
        <!-- foreach -->
        <!-- Item -->
    </div>
    <!-- Carousel Container -->

    <input type="hidden" id="previous_url_bag" name="previous_url_bag" value="{{$previous_url or ''}}">
    <input type="hidden" name="role" id="role" value="{{$role or ''}}">
</div>
    </div>
  </div>
</section>
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

                @php
                 
                  $first_name = isset($maker_arr['first_name'])?$maker_arr['first_name']:'';
                  $last_name  = isset($maker_arr['last_name'])?$maker_arr['last_name']:'';

                  $full_name = $first_name.' '.$last_name;
                @endphp

                <div class="col-get-qoute-right">{{$maker_arr['maker_details']['company_name'] or ''}}</div>
              </div>
              <div class="get-qoute-row">
                <div class="col-get-qoute">Product Name</div>
                <div class="col-get-qoute-right">{{isset($first_prod_arr['product_name'])?ucfirst($first_prod_arr['product_name']):''}}</div>
              </div>

              <div class="get-qoute-row">
                <div class="col-get-qoute">Description</div>
                <div class="col-get-qoute-right description-content">

                  {{ isset($first_prod_arr['description'])?strip_tags($first_prod_arr['description']):'' }}

                </div>
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

          <input type="hidden" name="vendor_id" id="vendor_id" value="{{isset($maker_arr['id'])?$maker_arr['id']:''}}">
          <input type="hidden" name="company_name" id="company_name" value="{{isset($maker_arr['maker_details']['company_name'])?$maker_arr['maker_details']['company_name']:''}}">

          <input type="hidden" name="product_id" id="product_id" value="{{isset($first_prod_arr['id'])?$first_prod_arr['id']:''}}">
          <input type="hidden" name="vendor_email" id="vendor_email" value="{{isset($maker_arr['email'])?$maker_arr['email']:''}}">
          <input type="hidden" name="vendor_name" id="vendor_name" value="{{isset($maker_arr['first_name'])?ucfirst($maker_arr['first_name']):''}}">
          <div class="modal-footer">        
            <button type="button" id="sendGetaQuote" class="btn btn-submit-get">Submit</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>     
          </div>
        </form>
      </div>
      <div class="clearfix"></div>
      
    </div>
  </div>
</div>





<!-- Get a Quote Modal -->
<div class="modal fade vendor-Modal" id="similar_product_get_a_Quote" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Get A Quote</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     
      <div class="modal-body">
        <form method="post" id="getaQuoteSimilarProduct"> 
          {{csrf_field()}}
          <div id="page-wrapper">
            <div class="container-fluid">
              <div class="get-qoute-modal-body">
              <div class="get-qoute-row">              
                <div class="col-get-qoute">Vendor Name</div>

            <div class="col-get-qoute-right" id="similar_vendor_company_name"></div>
              </div>
              <div class="get-qoute-row">
                <div class="col-get-qoute">Product Name</div>
                <div class="col-get-qoute-right" id="similar_vendor_product_name"></div>
              </div>

              <div class="get-qoute-row">
                <div class="col-get-qoute">Description</div>
                <div class="col-get-qoute-right description-content" id="similar_product_description"></div>
              </div>
              </div>              
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Product Quantity <span class="text-danger">*</span></label>
                      <div>
                        <input type="number" min="1" maxlength="9" pattern="^[0-9]*$" data-parsley-type="digits" class="form-control" placeholder="Enter Product Quantity" id="similar_quote_quantity" name="quote_quantity" data-parsley-required="true" data-parsley-required-message="Please enter product quantity." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Name <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="100" class="form-control" placeholder="Enter name" id="similar_quote_name" name="quote_name" data-parsley-required="true" data-parsley-required-message="Please enter name." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">                
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Email <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" class="form-control" placeholder="Enter email" id="similar_quote_email" name="quote_email" data-parsley-required="true" data-parsley-required-message="Please enter email." data-parsley-type="email" data-parsley-type-message="please enter valid email." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Contact Number <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="20" class="form-control" placeholder="Enter contact number" id="similar_quote_contact_no" name="quote_contact_no" data-parsley-required="true" data-parsley-required-message="Please contact number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." data-parsley-minlength="10" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Expected Delivery Days <span class="text-danger">*</span></label>
                    <div>
                      <input type="number" min="0" maxlength="5" pattern="^[0-9]*$" data-parsley-type="digits" data-parsley-type-message="Please enter numbers" class="form-control" placeholder="Enter expected delivery days" id="similar_quote_no_of_days" name="quote_no_of_days" data-parsley-required="true" data-parsley-required-message="Please enter number of days to expect delivery." value="">
                    </div>
                  </div>                
                </div>
              </div> 
              <div class="row">
                <div class="col-lg-12">  
                  <div class="form-group">              
                    <label>Additional Notes</label>
                      <div>
                      <textarea class="form-control" name="quote_additional_notes" id="similar_quote_additional_notes" placeholder="Add additional notes" data-parsley-maxlength="500" data-parsley-maxlength-message="Only a maximum of 500 characters is allowed." ></textarea>
                      </div>
                    </div>                
                  </div>
              </div>
            </div>
          </div>

          <input type="hidden" name="vendor_id" id="similar_vendor_id" value="">
          <input type="hidden" name="company_name" id="similar_company_name" value="">

          <input type="hidden" name="get_quote_product_id" id="similar_get_quote_product_id" value="">
          <input type="hidden" name="vendor_email" id="similar_vendor_email" value="">
          <input type="hidden" name="vendor_name" id="similar_vendor_name" value="">

        </form>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">        
        <button type="button" id="btnSendGetQuotes" class="btn btn-submit-get">Submit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>     
      </div>
    </div>
  </div>
</div>





@php

$role = '';

if($login_user!=null){
    if($login_user->inRole('customer')){
      $role = 'customer';
    }
    elseif($login_user->inRole('retailer')){
      $role = 'retailer';
    }
    elseif($login_user->inRole('representative')){
      $role = 'representative';
    }
    elseif($login_user->inRole('sales_manager')){
      $role = 'sales_manager';
    }
}

@endphp

<input type="hidden" name="login_role" id="login_role" value="{{$role or ''}}">
<input type="hidden" id="retail_price" value="{{$product_arr['unit_wholsale_price']}}">

<script>

  var role = $("#login_role").val();

  if(role == 'customer')
  { 
     var minValue = 1;
  }
  else if(role == 'retailer')
  {
     var minValue = $("#product_min_qty").val();
  }
  else
  {
    var minValue = 1;
  }
 
</script>


@php
  $is_customer_login = 0;
  if($login_user != null && $login_user->inRole('customer')){
    $is_customer_login = 1;
  }
@endphp

<!-- <script src="{{url('/')}}/assets/front/js/setup.js"></script>
<script src="{{url('/')}}/assets/front/js/xzoom.min.js"></script>
 -->
<script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>

<!-- <script src="{{url('/')}}/assets/front/js/jquery.js"></script> -->
<script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>

<script type='text/javascript' src="{{url('/')}}/assets/front/js/menu_jquery.js"></script>


<script type="text/javascript">

var is_customer_login = '{{ $is_customer_login or 0 }}'; 

var max_product_qty = '{{$max_product_purchase_qty or 10000}}'
$(document).ready(function() 
{
   var ref = $("#sku_img0");
   showcolor(ref);
  /* Code for multiple images */
  var prod_det_id = document.getElementsByName('prod_det_id[]').length;
  var i = 0;
  /*For small Slider*/
  $('.div_mul_img .prod-item').each(function() {
        var prod_det_id = $("#prod_det_id"+i).val();
         $(".prod-item").eq(i).addClass("hide_show_multImageClass");
         $(".prod-item").eq(i).addClass("multImageClass"+prod_det_id);
         $(".prod-item").eq(i).attr('prod-det-id',prod_det_id);
         $(".multImageClass"+prod_det_id).hide();
        i++;
  }); 

  var active_id = $('.div_mul_img .active').attr('prod-det-id');
  $(".multImageClass"+active_id).show();
  $(".clickable").hide();
  $(".li_a_cls").removeClass("li_a_cls_active");
  $("#li_a_"+active_id).addClass("li_a_cls_active");




  var cnt_sku_wise_data = $("#sku_wise_prod_det_id_"+active_id).val();
  if(cnt_sku_wise_data > 3){
     $(".clickable").show();
  } else {
     $(".clickable").hide();
  }

  /* Ends */

  var user_role = $("#login_role").val();
  if(user_role == ""){

  }

  // var d = $(ref).attr('data-sku-description');
  var title_description =$('#title_description_first').val();
  var title_ingredients =$('#title_ingredients').val();
  if(title_description == null || title_description =='')
  {
    $('#ingreTab').click();
    $('#title_description').hide();
  }


     $("#item_qty").TouchSpin({
                min: minValue,
                max: max_product_qty,
                stepinterval: 2,
                maxboostedstep: 10000000,
      });


    // Submit get quote for single product
 
    jQuery("#sendGetaQuote").bind("click touchstart", function(e){

    e.preventDefault();
    //alert("123");
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


  //this function for send get a quotes for similar product

  jQuery("#btnSendGetQuotes").bind("click touchstart", function(e){
    
    e.preventDefault();

    if ($('#getaQuoteSimilarProduct').parsley().validate() == false) {
          return;
        }
        var formData = $('#getaQuoteSimilarProduct').serialize();   
        
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
                  $('#getaQuoteSimilarProduct').find("input[type=text], textarea, input[type=number]").val("");                
                });
            } else {    
              var status = response.status;
              status = status.charAt(0).toUpperCase() + status.slice(1);
              swal(status, response.description, response.status);
            }
          }
    
        });
  })  

     // Configure/customize these variables.
    var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more";
    var lesstext = "Show less";
    

    // $('.description-tx-pdl').each(function() {
    //     var content = $(this).html().trim();
 
    //     if(content.length > showChar) {
 
    //         var c = content.substr(0, showChar);
    //         var h = content.substr(showChar, content.length - showChar);
 
    //         var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

    //         $(this).html(html);
    //     }
 
    // });
    
    // $(".morelink").click(function(){
    //     if($(this).hasClass("less")) {
    //         $(this).removeClass("less");
    //         $(this).html(moretext);
    //     } else {
    //         $(this).addClass("less");
    //         $(this).html(lesstext);
    //     }
    //     $(this).parent().prev().toggle();
    //     $(this).prev().toggle();
    //     return false;
    // });


    $("#item_qty").keyup(function(){
      var check_qty = $("#item_qty").val();
      var qty_limit = max_product_qty;
   /*   if(check_qty>qty_limit)
      {
          swal('Warning','Purchase limit '+qty_limit+' units.','warning');
          flag ="false";
          $("#item_qty").val(qty_limit);
          return
      }*/
   });

    $('#item_qty').on('touchspin.on.startspin', function ()
    {
        var check_qty = $("#item_qty").val();
        var qty_limit = max_product_qty;
       /* if(check_qty>qty_limit)
        {
            swal('Warning','Purchase limit '+qty_limit+' units.','warning');
            flag ="false";
            $("#item_qty").val(qty_limit);
            return
        }*/
   });


var product_id      = $("#product_id").val(); 
var retail_price    = $("#retail_price").val();
var wholesale_price = $("#wholesale_price").val();
var product_price   = $("#product_price").val();

var module_url_path    = "{{ $module_url_path or '' }}";
var guest_url          = "{{url('/')}}";
var guest_redirect_url = window.location.href;

var product_min_qty   = $("#product_min_qty").val();



check_quantity();


//bedefault total price should be calculated from product min qty
if(role == 'customer')
{ 
   $("#total_wholesale_price").text(product_price);
}
else if(role == 'retailer' || role == 'representative' || role == 'sales_manager')
{
   
    if(product_min_qty && product_min_qty!='' && product_min_qty!=undefined)
    {
       var total_wholesale_price = product_min_qty * parseFloat(product_price);

       $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
    }
}
else
{ 

    var retail_vendor_price =  $("#retail_price").val();
    var item_vendor_qty = $("#item_qty").val();
    var total_vendor_price = parseFloat(retail_vendor_price) * parseInt(item_vendor_qty);

    $("#total_wholesale_price").html(parseFloat(total_vendor_price).toFixed(2));

   //$("#total_wholesale_price").text(product_price);
}


   
function check_quantity()
{
    var quantity =  $('#quantity').text();

    var quantity = parseInt(quantity);

          /* if(quantity==0)
           {*/

           if(quantity < minValue)
           { 
              var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
              $(".button-login-pb").hide();
              $(".out-of-stock-container").show();
              $("#item_qty").prop('disabled', true);
           }
           
           else
           {
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
                  var qty = $('#item_qty').val();
                  var qty_limit = max_product_qty;

                  if(parseInt(qty) > parseInt(qty_limit))
                  { 
                    total_wholesale_price = parseFloat(qty_limit) * parseFloat(product_price);
                    
                  }
                  else
                  { 
                    total_wholesale_price = qty * parseFloat(product_price);

                    
                    return;
                   
                  }
                   
                   $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
                   

                    if(qty =='' || qty==undefined || isNaN(qty)==true)
                    {  
                       total_product_price = 0;
                       $('#total_wholesale_price').html(total_product_price.toFixed(2));
                    }
              });



              /*------------*/
              $("#item_qty").change(function(){

                $('#frm-add-to-bag').parsley().validate();

                total_wholesale_price = parseFloat(max_product_qty) * parseFloat(product_price);
                  //fix_price(total_wholesale_price);
                
              });
  
      $(".vertical-spin").TouchSpin({
       min: 0,
       //max: 1000//max_qty
           
       }).on('touchspin.on.startspin blur change click', function (event) 
       {  
           let qty = $('#item_qty').val();   
           
           let wholesale_price = $("#popup_wholesale_price").text();
                  
            var total_wholesale_price = 0; 
            var qty_limit = max_product_qty;

            if(parseInt(qty) > parseInt(qty_limit))
            {
              total_wholesale_price = parseFloat(qty_limit) * parseFloat(product_price);     
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
      }
   }

});

$("#add-to-bag").click(function()
{ 
   var prev_url = "{{ URL::previous()}}";
   var url = prev_url.replaceAll('&amp;','&');
   

   var flag        ="true";
   var qty         = parseInt($('#item_qty').val()); 
   var max_qty     = parseInt($('#item_qty').attr('data-max'));
   var current_qty =  parseInt($("#prod_qty").val());
   var qty_limit = max_product_qty;

   var size = $("#size").val();
   var size_inventory = $("#total_left_size").val();


   if(size == "")
   {
         swal('Warning','Please Select Available size','warning');
         flag ="false";
         return
   }

  /* if(is_customer_login==1){
      qty_limit = max_product_qty;    
   }
   */

   if(size != undefined)
   { 
       
       if(size_inventory<qty)
       {
          swal({ 
                title: 'Warning',
                text: 'Product purchase limit is exceed. Available quantity is: "'+size_inventory+'"',
                type: 'warning'
            },
            function(){
                window.location.reload();
            });

                        
        flag ="false";
        return
       } 
   }
   

   if(qty<=0)
   {
       swal('Warning','Please enter quantity greater than zero.','warning');
       flag ="false";
       return
   } 
   
   if(parseInt(qty) > parseInt(qty_limit))
   {   
       //swal('Warning','Available Quantity:'+current_qty+'','warning');
       swal('Warning','Purchase limit '+qty_limit+' units.','warning');
       flag ="false";
       return
   }  
   if($('#frm-add-to-bag').parsley().validate()==false)
   {   flag ="false";
       return         
   }
    
   
  var role = $("#role").val();
  // alert(role);
  if(role == 'retailer')
  {
  /*if($login_user == true && $login_user->inRole('customer'))
  {*/

      if(parseInt(qty) > parseInt(current_qty))
      {
         swal({ 
                title: 'Warning',
                text: 'Product purchase limit is exceed. Available quantity is: "'+current_qty+'"',
                type: 'warning'
            },
            function(){
                window.location.reload();
            });

                        
        flag ="false";
        return
      }
      
  }

  
  if(flag=="true")
  { 
      var red_url = SITE_URL+'/my_bag/add';
      if(is_customer_login==1){
        red_url = SITE_URL+'/customer_my_bag/add';
      }

       $.ajax({
          url: red_url,
          type:"POST",
          data: $('#frm-add-to-bag').serialize(),             
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
                       cancelButtonText:"Continue Shopping",
                       confirmButtonText: "OK",
                       closeOnConfirm: true,
                       showCancelButton: true,
                    },
                   function(isConfirm,tmp)
                   {                       
                     if(isConfirm==true)
                     {  

                      window.location.href = SITE_URL+'/my_bag';
                      
                      // var previous_url = '{{$previous_url}}';
                      // var previous_url = $('#previous_url_bag').val();

                      // if(previous_url != '')
                      // {
                        @php
                        // if($login_user == true && $login_user->inRole('customer'))
                        // {
                        @endphp
                        //window.location.href = SITE_URL+'/customer_my_bag';
                        // window.location.href = SITE_URL+'/search';
                          
                        @php
                        
                        // }

                        // else
                        // {
                        // @endphp
                        //     window.location.href = SITE_URL+'/search';
                        //    //window.location.href = SITE_URL+'/my_bag';
                          
                        // @php
                        // }
                        @endphp
                        //window.location.href = previous_url;
                        // window.location.href = SITE_URL+'/my_bag';
                      // }
                      // else
                      // {
                      //   window.location.href = SITE_URL+'/search';
                      // }

                      /*if(response.user_loggedIn_status == false){

                         window.location.href = SITE_URL+'/login';
                      }
       
                     


                      if(URL == 'search')
                      {
                         window.location.href = SITE_URL+'/search';
                      }
                      else if(URL == 'category')
                      {
                         window.location.href = SITE_URL+'/search?category_id='+categoryID;
                      } */
                       
                     }
                     else{
                      window.location.href = url;
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


function showcolor(ref)
{
   var color = $(ref).attr('data-sku-color');

   $("#hidden_sku_no").val($(ref).attr('data-sku-id'));

   if(color != "")
   {
      $("#color_data").html('<label class=color_label>Color:</label><span id=color_text>'+color+'</span>'); 
      $("#input_color").val(color);
   }
   else{
      $("#color_data").html("");
   }
   
}

function checkSizeInventory()
{
   // alert($("#hidden_sku_no").val());
   var sku_id = $("#hidden_sku_no").val();
   var product_id = $("#size").attr('data-produt-id');
   var size = $("#size").val();
   if(size != "" && size != undefined)
   {

// alert(size);

   var csrf_token = "{{csrf_token()}}";

            $.ajax({
              url: SITE_URL+'/my_bag/check_size_inventory',
              type: "POST",
              data: { sku_id : sku_id,product_id : product_id,size : size,'_token':csrf_token },                     
              success: function(response) {
                  // $("#innerRowBox").append(response);
                  // $('.dropify').dropify();
                  if(response.status == "success" )
                  {
                     // alert(response.size);

                     if(response.size == 0)
                      { 
                        var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
                        $(".button-login-pb").hide();
                        $(".out-of-stock-container").show();
                        $("#item_qty").prop('disabled', true);
                        $('#item_qty').val(minValue);
                        $("#total_left_size").val();
                       
                      }
                      else
                      {
                        var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                                  <div class="clearfix"></div>`;
                       $("#item_qty").prop('disabled', false);
                       $(".button-login-pb").show();
                       $(".out-of-stock-container").hide();
                       $("#total_left_size").val(response.size);
                      }
                  }
                  else
                  {

                    var unavailable = ` <span style="color:red;font-weight:bold">out of stock</span>`;
                        $(".button-login-pb").hide();
                        $(".out-of-stock-container").show();
                        $("#item_qty").prop('disabled', true);
                        $('#item_qty').val(minValue);
                       
                  }
              }
            });
    }else{

        var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                                  <div class="clearfix"></div>`;
                       $("#item_qty").prop('disabled', false);
                       $(".button-login-pb").show();
                       $(".out-of-stock-container").hide();

    }        
   
}  

function sku_detail(ref)
{ 
    var prod_id            = $(ref).attr('data-produt-id');
    var prod_det_id        = $(ref).attr('data-product-det-id');
    var enc_sku_change_id  = $(ref).attr('data-sku-id');
    var description_change = $(ref).attr('data-sku-description');
    var product_min_qty    = $(ref).attr('data-product-min-qty');   
    // var medium_img         = $(ref).attr('data-medium-img');     
    var replace_description_change = description_change.replace(/<[^>]*>?/gm, '');

    
   //$( "#example1 .rwaltz-medium-wrap .rwaltz-view-medium-img img" ).trigger( "click" );
    var sku_inventory = $(ref).attr('data-sku-inventory');
    var sku_unit_price = $('.sku_unit_price_for_change').val();
    var sku_change_id = atob(enc_sku_change_id);
  
    $('#sku_num').val(sku_change_id);
    $('#sku_change_description').text(replace_description_change);
    $('#quantity').text(sku_inventory);


    /* code for multiplee imahe */
    var medium_img         = $(ref).attr('data-medium-img'); 


    /*To append image as main image */
    var origional_img         = $(ref).attr('data-origional-img'); 

    $('#main_image img').attr('src',origional_img);
    $('#main_image .zoomImg').attr('src',origional_img);
    /*Ends*/
    $("")
    //$('#example1 .rwaltz-medium-wrap .rwaltz-view-medium-img img').attr('src',medium_img);
   // $('.mouse-down img').attr('src',medium_img);
    var cnt_sku_wise_data = $("#sku_wise_prod_det_id_"+prod_det_id).val();
    if(cnt_sku_wise_data > 3){
       $(".clickable").show();
    } else {
       $(".clickable").hide();
    }

    $(".li_a_cls").removeClass("li_a_cls_active");
    $("#li_a_"+prod_det_id).addClass("li_a_cls_active");
    $(".cls_hide_show_mul_img").hide();
    $(".multiple_image_"+prod_det_id).show();
    $(".hide_show_multImageClass").hide();
    $(".multImageClass"+prod_det_id).show();
    $(".hide_show_multImageClass").removeClass('active');
    $(".multImageClass"+prod_det_id).eq(0).addClass('active');
    
    /* Ends */

    /*For zoom slider*/
   /*   var i = 0;
      $( "#example1 .rwaltz-medium-wrap .rwaltz-view-medium-img img" ).trigger( "click" );
      $('.gallery-slider .prod-item').each(function() {
            var prod_det_id = $("#prod_det_id"+i).val();
             $(".prod-item").eq(i).addClass("hide_show_zoom_multImageClass");
             $(".prod-item").eq(i).addClass("zoom_multImageClass"+prod_det_id);
             $(".prod-item").eq(i).attr('prod-det-id',prod_det_id);
             $(".zoom_multImageClass"+prod_det_id).hide();
            i++;
      });
      $(".zoom_multImageClass"+active_id).addClass("active");*/
      /*Ends*/


    if(description_change == null || description_change =='')
    {
      $('#title_description').hide();
    }
    else
    {
      $('#title_description').show();
    }


    if(role != 'retailer')
    {
          if(product_min_qty!=null && product_min_qty!=undefined && product_min_qty!='')
          {
              $("#product_min_qty_value").html('Product Min Qty : '+product_min_qty);

              $("#item_qty").val(product_min_qty);
              
              $("#product_min_qty").val(product_min_qty);


              minValue = parseInt(product_min_qty);

              $("#item_qty").trigger("touchspin.updatesettings", {min: minValue});
              // load_js();

              //bedefault total price should be calculated from product min qty

              if(product_min_qty && product_min_qty!='' && product_min_qty!=undefined)
              {
                 var total_wholesale_price = product_min_qty * parseFloat(product_price);
                 
                 $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
              }


          }

    }


    if(role == 'retailer')
    {
       
        if(sku_inventory == 0)
        { 
          var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
          $(".button-login-pb").hide();
          $(".out-of-stock-container").show();
          $("#item_qty").prop('disabled', true);
          $('#item_qty').val(minValue);
          $("#total_wholesale_price").text(sku_unit_price);  
        }
        else
        {
          var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                    <div class="clearfix"></div>`;
         $("#item_qty").prop('disabled', false);
         $(".button-login-pb").show();
         $(".out-of-stock-container").hide();
        }
      

    }
    else
    {
          if(sku_inventory < minValue)
          { 
            var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
            $(".button-login-pb").hide();
            $(".out-of-stock-container").show();
            $("#item_qty").prop('disabled', true);
            $('#item_qty').val(minValue);
            $("#total_wholesale_price").text(sku_unit_price);  
          }
          else
          {
            var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                      <div class="clearfix"></div>`;
           $("#item_qty").prop('disabled', false);
           $(".button-login-pb").show();
           $(".out-of-stock-container").hide();
          }

    }

    if(replace_description_change != undefined && replace_description_change != null && replace_description_change != ""){
      $('#title_description').click();
    }
    else{
      $('#ingreTab').click();
    }

    
}
  
/* Function to appned slider image as main image */
function showAsMainImage(ref)
{    
    var origional_img         = $(ref).attr('data-origional-img'); 

    //$(".zoomImg").remove();
    $('#main_image img').attr('src',origional_img);
    $('#main_image .zoomImg').attr('src',origional_img);
   
}

function unable_to_add(){
  //location.reload();
  $("#alert-danger").show();
}

function close_button(){
  $("#alert-danger").hide();
}


//this function for show popup for single product
function openGetAQuoteModal(ref){
  var login = "{{ $login_user['email'] }}";
  // alert(login);
  if(login.trim() == "")
  {
    window.location = '{{url('/')}}/login';
    return;
  }
  $("#get_a_Quote").modal('show');
}


//this function for show get a quote popup for similar products
function openGetAQuoteModalSimialProduct(ref){
  $("#similar_product_get_a_Quote").modal('show');

  $("#similar_vendor_company_name").html($(ref).attr('data-company-name'));
  $("#similar_vendor_product_name").html($(ref).attr('data-product-name'));
  $("#similar_product_description").html($(ref).attr('data-product-dec'));


  $("#similar_vendor_id").val($(ref).attr('data-vendor-id'));
  $("#similar_vendor_name").val($(ref).attr('data-vendor-name'));
  $("#similar_vendor_email").val($(ref).attr('data-vendor-email'));
  $("#similar_get_quote_product_id").val($(ref).attr('data-product-id'));
  $("#similar_company_name").val($(ref).attr('data-company-name'));


}

//for social shairing
      function fbs_click() 
      {
             var pageUrl = encodeURIComponent(document.URL);

             window.open('https://www.facebook.com/sharer.php?u='+pageUrl,
                 'sharer',
                'toolbar=0,status=0,width=626,height=436');

            return false;
        }

        function twit_click() 
       {
            var pageUrl = encodeURIComponent(document.URL);
            window.open('https://www.twitter.com/intent/tweet?url='+pageUrl,
                 'sharer',
                'toolbar=0,status=0,width=626,height=436');

            return false;
        }

        function linkd_click() 
       {
            var pageUrl = encodeURIComponent(document.URL);

            window.open('https://www.linkedin.com/sharing/share-offsite/?url='+pageUrl,
                 'sharer',
                'toolbar=0,status=0,width=626,height=436');

            return false;
        }

        function pint_click() 
       {
            var pageUrl = encodeURIComponent(document.URL);   
            window.open('http://pinterest.com/pin/create/button/?url='+pageUrl,
                 'sharer',
                'toolbar=0,status=0,width=626,height=436');

            return false;
        }

</script>



@stop

