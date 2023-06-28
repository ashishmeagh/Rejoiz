@extends('front.layout.master')
@section('main_content')
@php
          
    $menu_settings = array();
    $menu_settings = get_menu_detail();
    foreach($menu_settings as $menus){
        array_push($menu_settings,$menus['menu_slug']);
    }
@endphp
<style type="text/css">
  .pagination-bar.myfavorite-br{
    
  }
</style>
@php
$login_user = Sentinel::getUser();
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
<link href="{{url('/')}}/assets/css/easy-responsive-tabs.css" rel="stylesheet" type="text/css" />

<div class="listing-main-div">
    <div class="container">
      {{-- <div class="row"> --}}
           {{--  @include('front.search._front_sidebar') --}}
            {{-- <div class="col-sm-8 col-md-8 col-lg-9"> --}}
               {{--  @include('front.search._search_readcrum_header')
                --}}
              <div class="pagename-left">
                 My Favorites
               </div> 
                <div class="product-section">
                 
                    <div class="tabbing_area">
                        <div id="horizontalTab">
                            <ul class="resp-tabs-list">
                                <li>Products</li>
                                <li>Vendors</li>
                            </ul>
                            <div class="resp-tabs-container">
                             
                               <div>
                              <div class="row">
                                  @if(isset($favorite_arr['product']['data']) && count($favorite_arr['product']['data'])>0)

                                    @foreach($favorite_arr['product']['data'] as $key=> $fav_product)
                                    
 
                                     <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="product-box text-center">
                                          <div class="heart-products"> 

                                            <a href="javascript:void(0)" class="heart-active" data-id="{{isset($fav_product['product_id'])?base64_encode($fav_product['product_id']):0}}" data-type="product" onclick="confirmAction($(this));"><i class="fa fa-heart" title="Remove from favorite"></i></a>                               
                                          </div>
                                      <div class="like-products">
                                      </div>  
                                      <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($fav_product['product_id'])}}&vendor_id={{base64_encode($fav_product['product_details']['user_id'])}}" class="img-block">
                                                
                                        @php  

                                            if(isset($fav_product['product_details']['product_image']) && $fav_product['product_details']['product_image']!='' && file_exists(base_path().'/storage/app/'.$fav_product['product_details']['product_image']))
                                            {
                                               $product_img = url('/storage/app/'.$fav_product['product_details']['product_image']);
                                            }
                                            else
                                            {                  
                                               $product_img = url('/assets/images/no-product-img-found.jpg');
                                            }

                                        @endphp   

                                            <img class="potrait" src="{{$product_img or ''}}" alt="Mawisam product">

                                      </a>                                                               
                                      <div class="product-hover">
                                           <a @if(!in_array('vendors',$menu_settings)) style="cursor: auto !important;" href="#" @else href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($fav_product['product_id'])}}&vendor_id={{base64_encode($fav_product['product_details']['user_id'])}}" @endif><i class="fa fa-eye" aria-hidden="true"></i></a>
                                      </div>

                                      <div class="product-info">
                                        <a href="#" class="brandnametitle">{{isset($fav_product['product_details']['product_name'])?strtoupper($fav_product['product_details']['product_name']):'N/A'}}</a>


                                        <a @if(!in_array('vendors',$menu_settings)) style="cursor: auto !important;" href="#" @else href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($fav_product['product_details']['user_id'])}}" @endif class="title-product">

                                          {{isset($fav_product['product_details']['brand_details']['brand_name'])?strtoupper($fav_product['product_details']['brand_details']['brand_name']):'N/A'}}
                                        </a>

                                        <div class="price-product">
                                         {{--  <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}} --}}
                                             @php $maker_details = get_maker_all_details($fav_product['product_details']['user_id']); @endphp
                                                      @if($login_user == true)
                                                        @if((($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))   
                                                        
                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}


                                                        @elseif((($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                        
                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}


                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)

                                                        @elseif(($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        

                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}

                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        

                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}

                                                        @endif                                                       
                                                        @else
                                                          @if(($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || ($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                          


                                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}

                                                          @elseif($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)                                                            
                                                          @elseif($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                         

                                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['retail_price'])?num_format($fav_product['product_details']['retail_price']):'0.00'}}
                                                         
                                                          @endif
                                                        @endif
                                        </div>


                                      </div>


                                  </div>
                                </div>
                                 @endforeach
                                  
                                @else
                                  {{--   <div class="vendr-img-no">
                                      <img  src="{{url('/uploads')}}/no-product.jpg" alt="Mawisam product">
                                    </div> --}}
                                    <div class="not-found-data whitebg-no vendor-no-avail">No products available here</div>
                                @endif  

                                 
                              </div>
                              <div class="pagination-bar myfavorite-br">
                                   @if(!empty($arr_product_pagination)) 
                                                      
                                    {{$arr_product_pagination->render()}}  
                                        
                                   @endif 

                               </div>
                               <div class="clearfix"></div>
                              </div>


                                <!--tab-2 start-->
                                <div>

                                 <div class="row">
                                  @if(isset($favorite_arr['maker']['data']) && count($favorite_arr['maker']['data'])>0)

                                    @foreach($favorite_arr['maker']['data'] as $key=> $fav_maker)

                                      
                                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="product-box text-center">
                                          <div class="heart-products"> 

                                            <a href="javascript:void(0)" class="heart-active" data-id="{{isset($fav_maker['maker_id'])?base64_encode($fav_maker['maker_id']):0}}" data-type="maker" onclick="confirmAction($(this),'maker');"><i class="fa fa-heart" title="Remove from favorite"></i></a>                               
                                          </div>


                                  <a @if(!in_array('vendors',$menu_settings)) style="cursor: auto !important;" href="#" @else href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($fav_maker['maker_id'])}}" @endif>

                                  <div class="product-list-pro">
                                    <div class="pro-img-list">
                                    
                                    @php
                                   
                                    if(isset($fav_maker['store_image_details']['store_profile_image']) && $fav_maker['store_image_details']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$fav_maker['store_image_details']['store_profile_image']))
                                    {
                                      $shop_img = url('/storage/app/'.$fav_maker['store_image_details']['store_profile_image']);
                                    }
                                    else
                                    {                  
                                      $shop_img = url('/assets/images/no-product-img-found.jpg');
                                    }
                                    @endphp

                                  <img class="potrait" src="{{$shop_img or ''}}" alt="" />
                                </div>

                                <div class="pro-content-list">
                                <div class="pro-sub-title-list"> {{isset($fav_maker['maker_details']['company_name'])?strtoupper($fav_maker['maker_details']['company_name']):'N/A'}}</div>
                                

                                 
                                </div>
                            </div>
                            </a>
                          </div>

                        </div>

                           @endforeach
                         
                          @else
                           {{--  <div class="vendr-img-no">
                             <img  src="{{url('/uploads')}}/no-vendor-available.jpg" alt="Mawisam vendor">
                             </div> --}}
                             <div class="not-found-data whitebg-no vendor-no-avail">No vendors available here</div>
                          @endif
                          </div> 

                               <div class="pagination-bar myfavorite-br">
                                   @if(!empty($arr_maker_pagination)) 
                                                      
                                    {{$arr_maker_pagination->render()}}  

                                   @endif 

                               </div>
                              
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



           {{--  </div> --}}
       {{--  </div> --}}
    </div>
</div>

<script type="text/javascript" src="{{url('/')}}/assets/js/easyResponsiveTabs.js"></script>

<script type="text/javascript">

$(document).ready(function(){
   
});

function confirmAction(ref,is_maker="")
{
    if(is_maker=="")
    {
      text ="Are you sure? Do you want to remove product from your favorite list.";
    }
    else
    {
      text ="Are you sure? Do you want to remove vendor from your favorite list.";
    }
    swal({
            title: "Need Confirmation",
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "OK",
            closeOnConfirm: false
  },
  function(){
    removeFromFavorite(ref);
  });
}

function removeFromFavorite(ref)
{
    var id   = $(ref).attr('data-id');
    var type = $(ref).attr('data-type');
    var csrf_token = $("input[name=_token]").val();

    $.ajax({
              url: SITE_URL+'/vendor-details/remove_from_customer_favorite',
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
//<!--tab js script-->  
$('#horizontalTab').easyResponsiveTabs({
        
       type: 'default', //Types: default, vertical, accordion           
       width: 'auto', //auto or any width like 600px
       fit: true, // 100% fit in a container
       closed: 'accordion', // Start closed if in accordion view
       activate: function(event) { // Callback function if tab is switched
           var $tab = $(this);
           var $info = $('#tabInfo');
           var $name = $('span', $info);
     
           $name.text($tab.text());
     
           $info.show();
       }
});

</script>

@stop