@extends('front.layout.master')
@section('main_content')

<style type="text/css">
      .product-section.border-left-on.spacenon-main{padding-top: 0;}
   .not-found-data {
      text-align: center;
      font-size: 30px;
      padding: 100px 0;
      background-color: #efefef;
      color: #b1b1b1;}
      .font-note{
      font-weight: 100;
      color: #9c9c9c;
      
   }

   xzoom-container:hover img {

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
</style>


<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>

@php

$maker_img = false;




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
   

/*$maker_image_base_path  = base_path('storage/app/'.$maker_base_img);
$maker_img = image_resize($maker_image_base_path,130,130);*/


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
      <!-- <div class="vendor-profile-banner-main" style="background-image: url({{ $banner_img or '' }}) !important;">
      </div> -->

      <div class="vendor-profile-banner-main brand_listing">
         <img src="{{ $banner_img }}">
      </div>

      <div class="row">
         <div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 maker_detail_left_div">
            <div class="vendor-whitebox">
               <a href="#" class="wishlist-vendor">{{-- <i class="fa fa-heart-o"></i> --}}</a>
               <div class="vendor-avatar">                 
                  {{-- <img src="{{url('/storage/app/').'/'.$maker_base_img}}" alt="{{ $maker_arr['maker_details']['company_name'] or ''}}" /> --}}
                  <img src="{{ $maker_img }}" alt="{{ $maker_arr['maker_details']['company_name'] or ''}}" />
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
                  <div class="retail_price_product inlineprice font-weight-normal vendor_detail_miminum_leadtime">
                    <span STYLE="font-size: 14px">Vendor Minimum : {{isset($shop_arr['first_order_minimum'])?'$'.$shop_arr['first_order_minimum']:'No Vendor Minimum'}}</span>
                      <div class="clearfix"></div>
                    <span STYLE="font-size: 14px">Lead Time : {{isset($shop_arr['shop_lead_time'])?$shop_arr['shop_lead_time'].' days':'No Lead time'}}</span>
                  </div>  
               </div>
            </div>
            @if(isset($cat_arr) && count($cat_arr) > 0) 
               <div class="sidebar-main-lisitng noneborder">

                  <div class="title-categorynm click-category">
                     <span class="cat-none">Category</span>
                  <img src="assets/front/images/chevron-down.svg" alt="">
               </div>
    
                  <div id="cssmenu1" class="shows-category scrollbar">
                     <ul>
                        <li>
                           @if(isset($cat_arr) && count($cat_arr) > 0) 
                           <a href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}&all_products"><span class="submenu1">All Products</span>
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
                                    @if(isset($category['category_details']['subcategory_details']) && count($category['category_details']['subcategory_details']) > 0)
                                       <span class="plus-icon lnkclk"> <i class="fa fa-plus"></i></span>
                                       @endif
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


         @php
            $login_user          = Sentinel::check();
            $is_customer_exist   = '';
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
               <h3 class="ttl" id="promotions-section">Promotions</h3>

            </label>
            <div class="scrollbar-div"> 
            @foreach($promotion_arr as $key=>$promotions)

              @if($promotions['to_date'] >= $date && $promotions['is_active'] == 1)

               @php
                  $isPromotionVisible = true;
               @endphp

               <hr class="hr-top">
                  <div class="main-all-current-promotions-right none-div-style">
                     <div class="title-promotions-txt">{{$promotions['title'] or ''}}</div>
                     <div class="date-tm">{{isset($promotions['from_date'])?date('d M Y',strtotime($promotions['from_date'])):''}}  To  {{isset($promotions['to_date'])?date('d M Y',strtotime($promotions['to_date'])):''}}
                     </div>
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
                     <hr>
                  </div>

              @endif 

            @endforeach
         </div>
         @endif
      @endif   

         <!-- if catalog does not contain any pages then no need to show catalog -->


            {{-- @if(isset($catalog_data) && count($catalog_data)>0)
               <label id="label_catalog">
                  <h3>Catalogs</h3>
               </label>

               @foreach($catalog_data as $key=>$catalog)

                  @if(isset($catalog['catalog_page_details']) && count($catalog['catalog_page_details'])>0)

                   <input type="hidden" name="catalog_page_count" id="catalog_page_count" value="{{$key or ''}}">

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

                  @endif
   
               @endforeach

              
            @endif --}}

      {{--  @if(($is_customer_exist == false) && ($is_influencer_exist == false))
 --}}

    @if($login_user == false || $login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager')))
    
            @if(isset($pdf_arr) && count($pdf_arr)>0)
              <label>
                  <h3>Catalogs</h3>
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

     
      <!--  -->

         </div>
         <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10 maker_detail_right_div">
            <div class="text-vendor-top">
              <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="1000">
               <div class="truncate">{!! $shop_arr['shop_story'] or ''!!}</div>
               <hr>
               <div class="product-section border-left-on spacenon-main">
                  <div class="row">
                     {{-- {{dd($arr_brands)}} --}}
                     
                     @if(isset($arr_brands) && count($arr_brands)>0)
                        @foreach($arr_brands as $brand)
                           <div class="col-xs-6 col-sm-6 col-md-6 col-lg-4">
                              <div class="product-list-pro vendor-profile-list">
                                 <div class="pro-img-list">
                                    <div class="heart-products">
                                       @if($login_user==true)  
                                          @if($login_user->inRole('retailer'))
                                            
                                          @endif   
                                       @endif
                                    </div>
                                    @php
                                    

                                    $brand_image = false;

                                    $brand_image_base_path  = base_path('storage/app/'.$brand['brand_image']);
                                    
                                    /*if(file_exists($brand_image_base_path))
                                    {
                                       // $product_img = url('storage/app/'.$product['_source']['product_image_thumb']);
                                       $brand_image = image_resize($brand_image_base_path,230,230);
                                       
                                    }
                                    else
                                    {
                                       $brand_image = url('/assets/images/no-product-img-found.jpg');
                                    }*/

                                    if(file_exists($brand_image_base_path))
                                    {
                                       // $product_img = url('storage/app/'.$product['_source']['product_image_thumb']);
                                       $brand_image = image_resize($brand_image_base_path,230,230);
                                    }
                                    else
                                    {
                                       $brand_image = url('/assets/images/no-product-img-found.jpg');
                                    }

                                    @endphp
                                    <a href="{{url('vendor-details?vendor_id=').base64_encode($brand['user_id'])}}&brand_id={{base64_encode($brand['id'])}}" class="faeye" >
                                    <img class="potrait" src="{{ $brand_image }}" alt="{{$brand['brand_name']}}">
                                 </a>
                                    @php
                                       $login_user = Sentinel::check();
                                    @endphp
                                    {{-- @if($login_user==true)
                                       @if($login_user->inRole("maker"))
                                          @if($login_user->id==$brand['user_id']) --}}
                                             <!-- <div class="ovrly"></div> -->
                                           <!--   <div class="buttons">
                                                <a href="{{url('vendor-details?vendor_id=').base64_encode($brand['user_id'])}}&brand_id={{base64_encode($brand['id'])}}" class="faeye" >
                                                <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="" />
                                                </a>
                                             </div> -->
                                        
                                 </div>
                                 <div class="pro-content-list ptover spacetp-tool">
                                    <div class="pro-sub-title-list tooltip-title" title="{{isset($brand['brand_name'])?ucfirst($brand['brand_name']):""}}">{{isset($brand['brand_name'])?ucfirst($brand['brand_name']):""}}
                                       
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
</div>
</div>
</div>
</div>
</div>


<script type="text/javascript">
   $(document).ready(function(){

     var catalog_page_count = $('#catalog_page_count').val();

     if(catalog_page_count=='' || catalog_page_count==undefined || catalog_page_count==null)
     {
         $('#label_catalog').hide();
     }


      $(".click-category").click(function(){
     
       $(".shows-category").toggleClass("showscategorydiv");
      });



      var isVisible = '{{$isPromotionVisible or ''}}';
               
      if(isVisible == false)
      {
        $('#promotions-section').css('display','none');
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
  
</script>

<script src="{{url('/')}}/assets/front/js/jquery.flexisel.js" type="text/javascript"></script>

<!--footer section start here-->
@stop