@extends('front.layout.master')
@section('main_content')

<style type="text/css">
   .product-section.border-left-on.spacenon-main{padding-top: 0;}
   .spacenon-main .pagename-main{ margin-bottom: 20px; }
      .btn-successs {
    color: #fff !important;
    background-color: #ee375d;
    border-color: #ee375d; margin-left: 30px;
    float: right;
}
.btn-success:hover, .btn-success:focus {
    color: #ee375d !important;
    background-color: #fff;
    border-color: #ee375d;
}
   .not-found-data {
   text-align: center;
   font-size: 30px;
   padding: 100px 0;
   background-color: #efefef;
   color: #b1b1b1; }
   .font-note{
   font-weight: 100;
   color: #9c9c9c;
  
   }
   .xzoom-container:hover img {

      opacity:0.2; }

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
       color: #ee375d !important;
}

.note-ar-app {
   margin-top: 10px;
    background-color: #f7efde;
    padding: 10px;
    border-radius: 3px;
    color: #716444;
}

</style>
<link href="{{url('/')}}/assets/front/css/xzoom.css" rel="stylesheet" type="text/css" />
{{-- {{dd(base64_decode($request_values['subcategory']))}} --}}
<script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
@php

$maker_img = false;

$maker_base_img = isset($maker_arr['store_details']['store_profile_image']) ? $maker_arr['store_details']['store_profile_image'] : false;

$banner_base_img = isset($maker_arr['store_details']['store_cover_image']) ? $maker_arr['store_details']['store_cover_image'] : false;

$maker_image_base_path  = base_path('storage/app/'.$maker_base_img);
$banner_image_base_path  = base_path('storage/app/'.$banner_base_img);

$banner_default_image = url('assets/front/images/vendor-profile-img-bannr.jpg');

$maker_img = image_resize($maker_image_base_path,130,130);
$banner_img = image_resize($banner_image_base_path,1140,200,$banner_default_image);



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
<div class="vendorlisting-main-div">
<div class="container">
   <div class="vendor-profile-banner-main" style="background-image: url({{ $banner_img or '' }}) !important;">
   </div>
   <div class="row">
      <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
         <div class="vendor-whitebox">
            <a href="#" class="wishlist-vendor">{{-- <i class="fa fa-heart-o"></i> --}}</a>
            <div class="vendor-avatar">                 
               <img src="{{ $maker_img or '' }}" alt="{{ $maker_arr['maker_details']['company_name'] or ''}}" />
            </div>

            <div class="vendor-avtr-title">{{isset($maker_arr['maker_details']['company_name'])?ucfirst($maker_arr['maker_details']['company_name']):""}}
               @if($login_user==true)  
               @if($login_user->inRole('retailer'))
               @if(isset($fav_maker_count) && $fav_maker_count >0)
               <a href="javascript:void(0)" class="heart-active" data-id="{{isset($maker_arr['maker_details']['user_id'])?base64_encode($maker_arr['maker_details']['user_id']):0}}"  data-type="maker" id="fill_heart" onclick="confirmAction($(this),'remove','maker');" title="Remove from favorite"><i class="fa fa-heart"></i></a>        
               @else
               <a href="javascript:void(0)" class="heart-deactive" data-id="{{isset($maker_arr['maker_details']['user_id'])?base64_encode($maker_arr['maker_details']['user_id']):0}}" onclick="confirmAction($(this),'add','maker');" data-type="maker" id="empty_heart" title="Add to favorite"><i class="fa fa-heart-o"></i></a>
               @endif
               @endif
               @endif    
            </div>
         </div>
         @if(isset($cat_arr) && count($cat_arr) > 0) 
          <div class="sidebar-main-lisitng noneborder">

            <div class="title-categorynm">Category</div>
    
            <div id="cssmenu1">
               <ul>
                  <li>
                     @if(isset($cat_arr) && count($cat_arr) > 0) 
                     <a href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}"><span class="submenu1">All Products</span>
                     </a>
                     @endif
                  </li>
                  @if(isset($cat_arr) && count($cat_arr) > 0)   
                  @foreach($cat_arr as $category)
                  @if($category['category_details']['is_active']==1)
                  <li class='has-sub'>
                     <a  @if(isset($request_values['category_id']) && base64_decode($request_values['category_id'])==$category['category_id']) class="active" @endif 
                     href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&category_id={{isset($category['category_details']['id'])?base64_encode($category['category_details']['id']):""}}"><span class="submenu1">{{isset($category['category_details']['category_name'])?ucfirst($category['category_details']['category_name']):""}}</span></a>
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
                     <span class="plus-icon lnkclk"> <i class="fa fa-plus"></i></span>
                     @endif
                     <ul class="sub_menu submenu @if(isset($request_values['subcategory']) && in_array(base64_decode($request_values['subcategory']),$subcategory_id_arr)) link-act @endif" style="display:none">
                        @if(isset($category['category_details']['subcategory_details']) && count($category['category_details']['subcategory_details']) > 0)
                        @foreach($category['category_details']['subcategory_details'] as $sub_category)
                        @if($sub_category['is_active']==1)
                        <li class='has-sub'>
                           <a   
                           @if(isset($request_values['subcategory']) && base64_decode($request_values['subcategory'])== $sub_category['id']) class="active" @endif href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&category_id={{base64_encode($category['category_id'])}}&subcategory={{isset($sub_category['id'])?base64_encode($sub_category['id']):""}}"
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
         @if(isset($promotion_arr) && count($promotion_arr)>0)
         <label>
            <h3>Promotions</h3>
         </label>
         @foreach($promotion_arr as $key=>$promotions)
         <hr class="hr-top">
         <div class="main-all-current-promotions-right none-div-style">
            <div class="title-promotions-txt">{{$promotions['title'] or ''}}</div>
            <div class="date-tm">{{isset($promotions['from_date'])?date('d M Y',strtotime($promotions['from_date'])):''}}  To  {{isset($promotions['to_date'])?date('d M Y',strtotime($promotions['to_date'])):''}}</div>
            <div class="clearfix"></div>
            </br>
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
            @if(isset($promotions['promo_code']) && $promotions['promo_code']!='') 
            <div class="button-ff-year">{{$promotions['promo_code'] or ''}}</div>
            @endif
            <hr>
         </div>
         @endforeach
      @endif

     
         @if(isset($catalog_data) && count($catalog_data)>0)
            <label>
               <h3>Catalogs</h3>
            </label>
               @foreach($catalog_data as $key=>$catalog)
  
               <div class="catalog-list-dv">
                  @php 
                     if(isset($catalog['catalog_page_details'][0]['get_catalog_image_data'][0]['image']) && $catalog['catalog_page_details'][0]['get_catalog_image_data'][0]['image']!='')
                     {
                        $first_catlog_img = $catalog['catalog_page_details'][0]['get_catalog_image_data'][0]['image'];
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
                           <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                           </a>
                        </div>
                     </div>
                     <div class="catlog-nm">{{$catalog['catalog_name'] or 'N/A'}}</div>
                  </div>
               </div>

               @endforeach
         @endif
      </div>
      <div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">
         <div class="text-vendor-top">
           <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="1000">
            <div class="truncate">{!! $shop_arr['shop_story'] or ''!!}</div>
         
            <?php
               $brand_name = '';
               $array_brands = get_maker_brands($maker_arr['maker_details']['user_id']);
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
               <div class="col-md-6">
               <h3 class="mrg-onew" style="margin-top: 0;"><b>{{isset($brand_name)?$brand_name:''}}</b></h3>
               </div>
                @endif
               <div class="col-md-6">
                    @if(isset($array_brands) && count($array_brands) > 1 )
            <a href="{{url('/vendor-details?vendor_id=').base64_encode($maker_arr['maker_details']['user_id'])}}" class="btn btn-successs" title="All Brands">Show All Brands</a>
            @endif
               </div>
            </div>
            

            <div class="pagename-main">
               <div class="pagename-left">All Products</div>
               <div class="pagename-right">
                  <a href=""></a> 
                     <span class="active"></span>
                       
               </div>

                  <div class="clearfix"></div>
               <div class="results-txt">{{isset($product_arr['total_results'])?$product_arr['total_results']:0}} Products</div>
                  <div class="clearfix"></div>
            </div>

               <div class="row">
                  
                  @if(isset($maker_product_arr) && count($maker_product_arr)>0)
                  @foreach($maker_product_arr as $product)
                  <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
                     <div class="product-list-pro vendor-profile-list">
                        <div class="pro-img-list">
                           <div class="heart-products">
                              @if($login_user==true)  
                                 @if($login_user->inRole('retailer'))
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
                              $product_img = image_resize($product_image_base_path,230,230);
                           }
                           else
                           {
                              $product_img = url('/assets/images/no-product-img-found.jpg');
                           }
                          
                           @endphp
                           <img class="potrait" src="{{$product_img or ''}}" alt="img12">
                  
                             <div class="ovrly"></div>
                              <div class="buttons">
                                 <a  href="javascript:void(0)" data-produt-id="{{base64_encode($product['id'])}}"
                                    maker_id="{{ base64_encode($product['user_id']) }}" class="faeye" onclick="show_product_details(this)">
                                 <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="" />
                                 </a>
                              </div>

                             
                        </div>
                        <div class="pro-content-list ptover spacetp-tool">
                           <div class="pro-sub-title-list tooltip-title" title="{{isset($product['product_name'])?ucfirst($product['product_name']):""}}">{{isset($product['product_name'])?ucfirst($product['product_name']):""}}

                                  
                           </div>


                           @php
                           $login_user = Sentinel::check();
                           @endphp
 
                           <div class="price-product inlineprice">
                              @if ($login_user == true) 
                                
                                 <div class="suggested_price inlineprice font-weight-normal">
                                    <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : ''}}
                                 </div>
             
                              
                              @else
                                 <div class="retail_price_product inlineprice font-weight-normal">
                                 
                                    Retail <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['retail_price'])?num_format($product['retail_price']) : ''}}
                                 </div>
                              @endif
                              
                           </div>

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
<div class="vendor-profile-main-modal">
<div class="modal-mask"></div>
<div class="modal-popup" data-keyboard="false" tabindex="-1">
<a href="{{$new_url or '#'}}" class="closemodal">
<img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="close" />
</a>
<div class="modal-bodys">
<div class="main-modalbd">
   <!-- default start -->
   <section id="default" class="padding-top0 xzoom-head-padding">
      <div class="">
         <div class="large-5 column">

            <div id ="gallery-container">
               <div class="button-login-pb">
                <a class="btn btn-default" id="zoom-in"><i class="fa fa-search-plus"></i></a>
               </div>

            <div id ="gallery-container image">

               <div class="xzoom-container">
                 @php 
                         $img_sku = 'WC524';
                         $img_active_class = 'xactive';
                  //$img_sku = null;

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
                           $product_img = image_resize($product_image_base_path,400,400);

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
                     $product_img = image_resize($product_image_base_path,400,400);

    
                
                     @endphp    

                {{--   @if($prod_key==0)
                  <div class="img-thumbnail">
                     <img class="xzoom image" id="xzoom-default" src="{{$product_img or ''}}" xoriginal="{{ $product_original_image or ''}}"  />
                  </div> --}}

   <!-- ---------------------------------->

               @if(isset($img_sku) && $img_sku!='')
           
                 @if($img_sku == $product_details['sku'])   
                     @php
                        $product_img = false;
                        $product_img_thumb = false;

                        $product_original_image = url('/').'/storage/app/'.$product_details['image'];

                        $product_image_thumb_base_path  = base_path('storage/app/'.$product_details['image_thumb']);

                        $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                        $product_img_thumb = image_resize($product_image_thumb_base_path,77,77);
                        $product_img = image_resize($product_image_base_path,400,400);


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
                     @if(isset($first_prod_arr['product_details']))
                     @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 
                     @php
                        $product_img = false;
                        $product_img_thumb = false;


                        //$product_image_thumb_base_path  = base_path('storage/app/'.$product_details['image_thumb']);

                        $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                        $product_img_thumb = image_resize($product_image_base_path,77,77);
                        $product_img = image_resize($product_image_base_path,400,400);
                        $product_original_image = url('/').'/storage/app/'.$product_details['image'];

                      
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
            </div>
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
         <input type="hidden" name="sku_no" id="sku_num" value="{{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}}">
         <input type="hidden" name="retail_price" id="retail_price" value="{{$first_prod_arr['retail_price'] or '' }}">
         <input type="hidden" name="wholesale_price" id="wholesale_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
         @php
         $login_user = Sentinel::check();
         @endphp

         @if($login_user==false)

          <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['retail_price'] or '' }}"> 
         @else
          
          <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
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
              @if($login_user==true)
               <div class="suggested-price mkr-sub-pc inlineblockprice" >
                  <div class="suggested-price-img"><b>Price (Wholesale)</b></div>
                  <span class="inlines first-span"> $</span>
                  <span class="product-price" id="popup_wholesale_price"> {{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span>
               </div>
               @else 
               <div class="suggested-price retailer-price mkr-sub-pc inlineblockprice" >
                  <div class="suggested-price-img"><b>Price (Retail)</b></div>
                  <span class="inlines first-span"> $</span>
                  <span id="popup_retail_price">{{ isset($first_prod_arr['retail_price'])?num_format($first_prod_arr['retail_price']) : '' }}</span>
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
            }
            @endphp
            <ul>
               @if(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==0)
               {{-- <li>
                  <div class="li-left">Color :</div>
                  <div class="option_value newoptionvalue">{{isset($opt_value)?$opt_value:''}}</div>
                  <div class="clearfix"></div>
               </li> --}}
               @elseif(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==1)
              {{--  <li>
                  <div class="li-left">Scent :</div>
                  <div class="option_value newoptionvalue">{{isset($opt_value)?$opt_value:''}}</div>
                  <div class="clearfix"></div>
               </li> --}}
               @elseif(isset($first_prod_arr['product_details'][0]['option_type']) && $first_prod_arr['product_details'][0]['option_type']==2)
               {{-- <li>
                  <div class="li-left">Size :</div>
                  <div class="option_value newoptionvalue">
                     {{isset($opt_value)?$opt_value:''}}
                     <div class="clearfix"></div>
               </li> --}}
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


            

            <div class="out-of-stock-container">
            <span class="outofstock">Out of stock</span>
            </div>
       

            @if($login_user==true)
            <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
            <span class="first-span">$</span>
            <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
            </div>
            @else
            <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
            <span class="first-span">$</span>
            <span id="total_wholesale_price">{{ isset($first_prod_arr['retail_price'])?num_format($first_prod_arr['retail_price']) : '' }}</span> 
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
<script src="{{url('/')}}/assets/front/js/jquery.js"></script>
<script src="{{url('/')}}/assets/front/js/xzoom.min.js"></script>
<script src="{{url('/')}}/assets/front/js/setup.js"></script>
{{-- {{dd($pro_style_data)}} --}}
<script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>
<!-- Modal Script -->
<script type="text/javascript">

   /* window.onbeforeunload = function() {
   console.log('yes');return false;
   $(".vendor-profile-main-modal").style.display='none';

}*/


$(document).ready(function() 
{
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
   if(quantity==0)
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
      }
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
                     $("#total_wholesale_price").text((+product_arr.unit_wholsale_price).toFixed(2));
                     $("#product_price").val((+product_arr.unit_wholsale_price).toFixed(2));
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

      $("#zoom-in").click(function()
      {
         $("#gallery-container").addClass('removeclass-img-hover');
         $(".xzoom-container").addClass('pointeventon');
         $("#gallery-container").addClass('extrazoom');
         

      });


   $("#add-to-bag").click(function()
   { 
   
   let flag ="true";
   let qty = parseInt($('#item_qty').val()); 
   let max_qty = parseInt($('#item_qty').attr('data-max'));
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
                        if(response.user_loggedIn_status == false){

                           window.location.href = SITE_URL+'/login';
                        }
   
                          window.location.href = SITE_URL+'/my_bag';
                         

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
   
   $.ajax({
         url: SITE_URL+'/vendor-details/add_to_favorite',
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
   
     $.ajax({
             url: SITE_URL+'/vendor-details/remove_from_favorite',
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

//Handeling Escape keypress 

$(document).keydown(function(e) {
    if (e.keyCode == 27) {
     setTimeout(closePopup, 0);
    }

});

function fix_price()
{

  $(".vertical-spin").TouchSpin({ max: 1000});
}

function closePopup()
{  
   $('.vendor-profile-main-modal').css('display', 'none');

   //window.location.reload();
}

// After page refresh modal should get closed


    
</script>
<script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>

<script src="{{url('/')}}/assets/front/js/jquery.flexisel.js" type="text/javascript"></script>

<!--footer section start here-->
@stop