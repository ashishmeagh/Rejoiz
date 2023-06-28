@extends('representative.layout.master') 
@section('main_content')

@php
  $login_user = Sentinel::getUser();
  $cnt_sku_wise_data = 0;
  $max_product_purchase_limit = isset($site_setting_arr['product_max_qty'])?$site_setting_arr['product_max_qty']:1000;

@endphp

<link href="{{url('/')}}/assets/front/css/prodetail.css" rel="stylesheet">
<link href="{{url('/')}}/assets/front/css/gallery.css" rel="stylesheet">
<style type="text/css">
  #page-wrapper {background-color:#fff !important;}
  .prodetail_sec {
    margin-bottom: 20px;
    padding: 30px;
}
.button-login-pb {
    margin-top: 10px;
}
.button-login-pb .gt-button {
    text-transform: none;
    width: 100%;
    font-size: 15px;
    padding: 9px 30px 8px;border-radius: 2px;display: block; color: #333; text-align: center;
}
.gt-button:hover {
    background-color: #333;
    color: #fff;
}
.gt-button {
    display: block;
    padding: 9px 40px 8px;
    font-size: 14px;
    text-align: center;
    text-transform: uppercase;
    border: 1px solid #333;
    background-color: none;
    color: #333;
}

.nav > li > a:hover, .nav > li > a:focus {background:none;}
.prod-det-sidenav-sku {margin-top:15px; padding:0px;}
.prod-det-sidenav-sku ul li {margin-left:0px;}
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
 .morecontent span {
    display: none;
}
.morelink {
    display: block;
    color: red;
}
.morelink:hover,.morelink:focus{
  color: red;
}
.morelink.less
{
  color: red;
}
</style>
  
  <script type="text/javascript" src="{{url('/')}}/assets/front/js/gallery.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#example1').rwaltzGallery({
        openGalleryStyle: 'transform',
        changeMediumStyle: true
      });
    });
  </script>


<!-- Product Detail Section Start -->
<div id="page-wrapper" class="dashboard_page_main_div">
  <section class="prodetail_sec">
    <div class="container-fluid">
        <div class="pro_breadcrumb">
         <ul class="breadcrumb">
          <li><a href="{{$module_base_path}}/dashboard">Dashboard</a></li>
          <li><a href="{{url()->previous()}}">Add Product</a></li>
          <li>Product Details</li>
        </ul> 
      </div>
      <div class="row">
        <div class="col-sm-7">
            <!-- gallery hear -->
          <div id="example1" class="rwaltz-gallery img300">
            <div class="prod-carousel div_mul_img" >
              @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 


                @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400,false,true);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];


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


                @if(isset($first_prod_arr['product_details']) && isset($first_prod_detail_mul_images) && count($first_prod_detail_mul_images) > 0)


                @php
                $arr_sku_wise_count[$product_details['sku']][] = count($first_prod_detail_mul_images);

                @endphp
                @if(isset($first_prod_detail_mul_images[$product_details['sku']]))
                @foreach($first_prod_detail_mul_images[$product_details['sku']] as $prod_mul_key =>$product_mul_details) 
                  @php
                      $product_mul_img       = false;              
                    
                      $product_mul_image_base_path  = base_path('storage/app/'.$product_mul_details['product_image']);

                      $product_mul_img_thumb      = image_resize($product_mul_image_base_path,77,77);
                      $product_mul_img            = image_resize($product_mul_image_base_path,400,400);
                      $product_mul_original_image = url('/').'/storage/app/'.$product_mul_details['product_image'];

                     @endphp     
                
                 <img src="{{$product_mul_img or ''}}" data-medium-img="{{$product_mul_img or ''}}" data-big-img="{{$product_mul_img or ''}}" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" class="cls_hide_show_mul_img multiple_image_{{$product_details['id']}}" >

                 
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

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];


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
            <div class="third_div">
              {{-- <ul class="list-inline">
                <li class="list-inline-item">
                  <a class="badge badge-primary" href="#">fgdfksjd</a>
                </li>
              </ul> --}}
              <div class="col-sm-12 prod-det-sidenav-sku p-0">
                  <ul class="list-inline">
                     @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                  @php
                      $product_img       = false;
                      $product_img_thumb = false;
                      $product_min_qty_value = '';

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];


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
                    <li class="list-inline-item"><a 
                      id="li_a_{{$product_details['id']}}"
                      class="li_a_cls label  "
                      data-produt-id="{{base64_encode($product_details['product_id'])}}" data-product-det-id="{{$product_details['id']}}" 
                      data-sku-id="{{base64_encode($product_details['sku'])}}" 
                      data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" 

                      data-medium-img="{{$product_img or ''}}" 
                      onclick="sku_detail(this)" 
                      data-product-min-qty="{{isset($product_details['product_min_qty'])?$product_details['product_min_qty']:1}}"
                      
                      >{{$product_details['sku']}}</a></li>
                      
                @endforeach
              @endif 
                  </ul>
            </div>
            </div>
          </div>
          <!-- Get product details id from name array -->
          @php 
          $mycnt = 0;
          
          @endphp
          @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 
                 @php 

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
                  
              <div class="col-sm-5 pro_detail_right">
                <form id="frm-add-to-bag">
                     <input type="hidden" id="order_no" name="order_no" value="{{$order_no}}">
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
              
                          <h5>
                              @if(isset($maker_arr['maker_details'])) 
                              <span class="form-txt">By</span> 
                               <a href="{{url('/')}}/vendor-details?vendor_id={{isset($request_values['vendor_id'])?$request_values['vendor_id']:""}}">{{$maker_arr['maker_details']['company_name']}}
                               </a>
                               @php 

                               $get_minimum_order = get_maker_shop_setting($maker_arr['maker_details']['user_id']);
                               $minimum_order = "";
                               if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum'] == 0){$minimum_order = 'No Minimum Limit';}
                               else if(isset($get_minimum_order['first_order_minimum']) && $get_minimum_order['first_order_minimum']!=0)
                               {$minimum_order = '$'. num_format($get_minimum_order['first_order_minimum']).' Minimum';} 
                               $login_user = Sentinel::check();
                               @endphp
                              
                               @if($login_user == true && ($login_user->inRole('customer') || $login_user->inRole('influencer')))
                               @else
                               <div class="pro-title-list brand_price">{{$minimum_order}} </div>
                               @endif
                               @endif

                          </h5>


                          <!-- product min qty -->
                          @if(isset($first_prod_arr['product_details'][0]['product_min_qty']) && $first_prod_arr['product_details'][0]['product_min_qty']!='')

                              <h5>
                                <div class="pro-title-list brand_price" id="product_min_qty_value">Product Min Qty : {{isset($first_prod_arr['product_details'][0]['product_min_qty'])?$first_prod_arr['product_details'][0]['product_min_qty']:'-'}} </div>
                              </h5>
                          @endif

                        

                         <!-- case qty -->
                          @if(isset($first_prod_arr['case_quantity']) && $first_prod_arr['case_quantity']!="")
                         
                            <div class="title-detail-pg">Case Qty : {{$first_prod_arr['case_quantity'] or ''}}</div>
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
                          @php
                              $login_user = Sentinel::check();
                          @endphp

                          @if($login_user==false)
                       <h3>Retail Price <span>${{$product_arr['retail_price'] or ''}} </span></h3>                        
                          @elseif($login_user == true && $login_user->inRole('customer') || $login_user->inRole('maker') ||
                          $login_user->inRole('admin')||
                          $login_user->inRole('influencer'))
                              <h3>Retail Price <span>${{$product_arr['retail_price'] or ''}} </span></h3>                        
                          @else
                              <h3>Wholesale Price <span>${{$first_prod_arr['unit_wholsale_price'] or ''}} </span></h3>                        
                          @endif


                           <div class="li-left" style="display: none;">Quantity :</div><div id = "quantity" class="newoptionvalue" style="display: none;">{{isset($first_prod_arr['product_details'][0]['inventory_details']['quantity'])?$first_prod_arr['product_details'][0]['inventory_details']['quantity']:''}}</div>

                        <div class="qtydrop">
                              <div class="user-box product-details-box" >
                                <label class="form-lable">Item Quantity</label>                
                                <input class="vertical-spin bucket_spin" 
                                   data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value = "{{isset($first_prod_arr['product_details'][0]['product_min_qty'])?$first_prod_arr['product_details'][0]['product_min_qty']:'1'}}" data-parsley-type="integer" data-parsley-trigger="change" data-parsley-max="{{$max_product_purchase_limit or 1000}}" onkeydown="return (event.keyCode!=13);"> 
                                <input type="hidden" id = "prod_qty"> 
                                <div id="error_item_qty" style="display: none;"></div>             
                              </div>

                          <input type="hidden" name="product_min_qty" id="product_min_qty" value="{{isset($product_arr['product_details'][0]['product_min_qty'])?$product_arr['product_details'][0]['product_min_qty']:''}}"> 

                        </div>


                
                          @php
                              $login_user = Sentinel::check();
                          @endphp
                          @if($login_user==true &&  $login_user->inRole('maker')== true)
                              <div class="button-login-pb" >
                              <a href="javascript:void(0)" style="cursor: not-allowed;" class="gt-button">Add to Cart</a>
                              <div class="clearfix"></div>
                              </div>
                          @else
                              <div class="button-login-pb" >
                              <a href="javascript:void(0)" class="gt-button" id="add-to-bag" style= "display:{{$show_add_cart_btn}}" >Add to Cart</a>
                              <div class="clearfix"></div>
                              </div>

                          @endif

                              <div class="out-of-stock-container" style="display: none;">
                                  <span class="outofstock">Out of stock</span>
                              </div>

                              <!-- Hide element -->
                              <input type = "hidden" id = prod_user_id value="">

                           <input type="hidden" name="product_id" id="product_id" value="{{ Request::input('product_id') }}">
                  

                           <input type="hidden" name="sku_no" id="sku_num" value="@if(isset($imgSku)) {{$imgSku}} @else {{isset($first_prod_arr['product_details'][0]['sku'])?$first_prod_arr['product_details'][0]['sku']:""}} @endif">

                           <input type="hidden" name="retail_price" id="retail_price" value="{{$first_prod_arr['retail_price'] or '' }}">
                           <input type="hidden" name="wholesale_price" id="wholesale_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
                              <!-- end -->
                            <input type="hidden" name="maker_id" id="maker_id" value="{{isset($maker_arr['id'])?$maker_arr['id']:''}}">  

                              @php
                               $login_user = Sentinel::check();
                               @endphp

                               @if($login_user==false)

                                  <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['retail_price'] or '' }}"> 
                               @else
                                  
                                  @if($login_user->inRole('customer') || $login_user->inRole('maker')|| $login_user->inRole('admin')|| $login_user->inRole('influencer'))
                                     <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['retail_price'] or '' }}">  
                                     <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="customer"}>

                                  @else
                                     <input type="hidden" name="product_price" id="product_price" value="{{$first_prod_arr['unit_wholsale_price'] or ''}}">
                                     <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="representative"}>

                                  @endif

                               @endif
                             <!--  <input type="hidden" name="is_logged_in" id = "is_logged_in" value={{$login_user}}> -->


                               @php
                               $login_user = Sentinel::check();
                               @endphp
                          
                                @if($login_user==true)
                                  <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                  <span class="first-span">$</span>
                                  <span id="total_wholesale_price">{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}</span> 
                                  </div>
                                  <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['unit_wholsale_price'])?num_format($first_prod_arr['unit_wholsale_price']) : '' }}">

                                @else
                                  <div class="suggested-price case-right mkr-sub-pc"><div class="suggested-price-img">Total Price</div> 
                                  <span class="first-span">$</span>
                                  <span id="total_wholesale_price">{{ isset($first_prod_arr['retail_price'])?num_format($first_prod_arr['retail_price']) : '' }}</span> 
                                  </div>
                                  <input type="hidden" name="sku_unit_price_for_change" class="sku_unit_price_for_change" value="{{ isset($first_prod_arr['retail_price'])?num_format($first_prod_arr['retail_price']) : '' }}">
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

                  </form>
              </div>
      </div>
    </div>
    <div class="tab_container">
    <ul class="nav nav-tabs">

     <input type="hidden" id="title_description_first" name="title_description_first" class="title_description_first" value="{{ isset($product_arr['description']) && ($product_arr['description']!=null || $product_arr['description']!='')?$product_arr['description']:''}}">

      @if(isset($product_arr['description']) && ($product_arr['description']!=null || $product_arr['description']!=""))

      <li class="active" id = "title_description_li"><a href="#tab1" data-toggle="tab" id="title_description">Description</a></li>  

      @endif

      @if(isset($product_arr['ingrediants']) && !empty($product_arr['ingrediants']))

        <li id= "ingreTab_li"><a href="#tab2" id="ingreTab" data-toggle="tab">Ingredients</a></li>

      @endif
    
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
             
              @if(isset($product_arr['description']) && !empty($product_arr['description']))            
                <td><div class="truncate" id="sku_change_description">{!! $product_arr['description'] or '' !!}</div></td>
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
      <!-- <div class="tab-pane" id="tab2">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent=".tab-pane" href="#collapseTwo">
                Details
              </a>
            </h4>
          </div>
          <div id="collapseTwo" class="panel-collapse collapse">
            <div class="panel-body">
              <div class="col-sm-12 prodetailattribute">
                  <div class="row">
                      <div class="col-sm-4 left">sku</div>
                      <div class="col-sm-8 right">{{$pro_details['sku'] or ''}}</div>
                  </div>
                  <div class="row">
                      <div class="col-sm-4 left">Color</div>
                      <div class="col-sm-8 right">Red</div>
                  </div>
                  <div class="row">
                      <div class="col-sm-4 left">Color</div>
                      <div class="col-sm-8 right">Red</div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div> -->
      <!-- <div class="tab-pane" id="tab3">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent=".tab-pane" href="#collapseThree">
                Brand Story
              </a>
            </h4>
          </div>
          <div id="collapseThree" class="panel-collapse collapse">
            <div class="panel-body">
             Sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
              Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. 
            </div>
          </div>
        </div>
      </div> -->
    </div>
  </div>
  </section>
</div>  
<!-- Product Detail Section End -->

<!-- Product Detail link Start -->

<script>
  var minValue = $("#product_min_qty").val();
</script>

 
  <script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>

<!-- <script src="{{url('/')}}/assets/front/js/jquery.js"></script> -->
<script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>



<script type="text/javascript">

var max_product_purchase_limit = '{{ $max_product_purchase_limit or 1000 }}';

$(document).ready(function() 
{

    var title_description =$('#title_description_first').val();
    var title_ingredients =$('#title_ingredients').val();
    if(title_description == null || title_description =='')
    {
      $('#ingreTab').click();
      $('#title_description').hide();
    }


    $("#ingreTab").click(function() {
       $("#ingreTab_li").addClass("temp-active");
       $("#title_description_li").removeClass("temp-active");

       
       $("#title_description").attr("aria-expanded","false");
       $("#title_description").removeClass("active");

       $("#ingreTab_li").removeAttr("aria-expanded");
       $("#ingreTab").removeClass("active");
       $("#ingreTab").attr("aria-expanded","true");
       
    });

    $("#title_description").click(function() {
      $("#title_description_li").addClass("temp-active");
      $("#ingreTab_li").removeClass("temp-active");
 
      $("#ingreTab").attr("aria-expanded","false");
      $("#ingreTab").removeClass("active");
      
      $("#title_description_li").removeAttr("aria-expanded");
      $("#title_description").removeClass("active");
      $("#title_description").attr("aria-expanded","true");
    });


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
  if(cnt_sku_wise_data > 5){
     $(".clickable").show();
  } else {
     $(".clickable").hide();
  }

  /* Ends */


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
                max: max_product_purchase_limit,
                stepinterval: 2,
                maxboostedstep: 10000000,
    });

    // Configure/customize these variables.
    var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more >";
    var lesstext = "Show less";
    

    $('.description-tx-pdl').each(function() {
        var content = $(this).html().trim();
 
        if(content.length > showChar) {
 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
 
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

            $(this).html(html);
        }
 
    });
 
    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });


    $("#item_qty").keyup(function(){
      var check_qty = $("#item_qty").val();

      @php
      if($login_user == true && $login_user->inRole('customer'))
      {
      @endphp
        var qty_limit = max_product_purchase_limit;
        
      @php
      }
      else
      {
      @endphp
        var qty_limit = max_product_purchase_limit;
      @php
      }
      @endphp
  /*    if(check_qty>qty_limit)
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
        @php
        if($login_user == true && $login_user->inRole('customer'))
        {
        @endphp
          var qty_limit = max_product_purchase_limit;
          
        @php
        }
        else
        {
        @endphp
          var qty_limit = max_product_purchase_limit;
        @php
        }
        @endphp
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
var product_min_qty = $("#product_min_qty").val();
const module_url_path = "{{ $module_url_path or '' }}";
var guest_url = "{{url('/')}}";
var guest_redirect_url = window.location.href;



check_quantity();

//bedefault total price should be calculated from product min qty
if(product_min_qty && product_min_qty!='' && product_min_qty!=undefined)
{
    var total_wholesale_price = product_min_qty * parseFloat(product_price);

    $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
}
   
function check_quantity()
{
   var quantity =  $('#quantity').text();

   var quantity = parseInt(quantity);
    
   
   //if(quantity==0)
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
          let qty = $('#item_qty').val();
          @php
          if($login_user == true && $login_user->inRole('customer'))
          {
          @endphp
            var qty_limit = max_product_purchase_limit;
            
          @php
          }
          else
          {
          @endphp
            var qty_limit = max_product_purchase_limit;
          @php
          }
          @endphp 


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

      $("#item_qty").change(function(){
        $('#frm-add-to-bag').parsley().validate();
        total_wholesale_price = parseFloat(max_product_purchase_limit) * parseFloat(product_price);
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
            @php
            if($login_user == true && $login_user->inRole('customer'))
            {
            @endphp
              var qty_limit = max_product_purchase_limit;
              
            @php
            }
            else
            {
            @endphp
              var qty_limit = max_product_purchase_limit;
            @php
            }
            @endphp

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
   
   let flag        ="true";
   let qty         = parseInt($('#item_qty').val()); 
   let max_qty     = parseInt($('#item_qty').attr('data-max'));
   let current_qty =  parseInt($("#prod_qty").val());
   
   @php
    if($login_user == true && $login_user->inRole('customer'))
    {
    @endphp
      var qty_limit = max_product_purchase_limit;
      
    @php
    }
    else
    {
    @endphp
      var qty_limit = max_product_purchase_limit;
    @php
    }
    @endphp

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
    
       if(flag=="true")
       { 
           var red_url = '{{$module_url_path}}/store_lead';

           var total_wholesale_price = $("#total_wholesale_price").text();  
           var formData = new FormData($("#frm-add-to-bag")[0]);
           formData.append('total_wholesale_price',total_wholesale_price);

           $.ajax({
            url: red_url,
            type:"POST",
            data: formData,             
            dataType:'json',
            contentType:false,
            processData:false,
            beforeSend: function(){            
             showProcessingOverlay();
            },
            success:function(response)
            {
                hideProcessingOverlay();
                //$(".modal-mask, .modal-popup").fadeIn();
                $('#frm-add-to-bag')[0].reset();
                      
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
                                   window.location.href = response.next_url;
                                   //scrollToTop();
                                   // location.reload(true);
                                }
                              });
                        }
                        else
                        {                
                            swal({
                                  title: 'Error',
                                  text: response.description,
                                  type: 'error',
                                  confirmButtonText: "OK",
                                  closeOnConfirm: true
                               },
                              function(isConfirm,tmp)
                              {                       
                                if(isConfirm==true)
                                {
                                   window.location.href = response.next_url;
                                }
                              });
                        }      
        
            }       
   
          });
          }
              
   });


 function sku_detail(ref)
 { 
    var prod_id            = $(ref).attr('data-produt-id');
    var prod_det_id        = $(ref).attr('data-product-det-id');
    var enc_sku_change_id  = $(ref).attr('data-sku-id');
    var description_change = $(ref).attr('data-sku-description');
    var replace_description_change = description_change.replace(/<[^>]*>?/gm, '');
    var product_min_qty    = $(ref).attr('data-product-min-qty');
    var sku_inventory      = $(ref).attr('data-sku-inventory');
    var sku_inventory      = parseInt(sku_inventory);
    var sku_unit_price     = $('.sku_unit_price_for_change').val();
    var minValue           = $("#product_min_qty").val();
    var sku_change_id      = atob(enc_sku_change_id);
  
    $('#sku_num').val(sku_change_id);
    $('#sku_change_description').text(replace_description_change);
    $('#quantity').text(sku_inventory);


     /* code for multiplee imahe */
     var medium_img         = $(ref).attr('data-medium-img'); 
    $('#example1 .rwaltz-medium-wrap .rwaltz-view-medium-img img').attr('src',medium_img);
    $('.mouse-down img').attr('src',medium_img);
    var cnt_sku_wise_data = $("#sku_wise_prod_det_id_"+prod_det_id).val();
    if(cnt_sku_wise_data > 5){
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

    if(product_min_qty!=null && product_min_qty!=undefined && product_min_qty!='')
    {
        $("#product_min_qty_value").html('Product Min Qty : '+product_min_qty);

        $("#item_qty").val(product_min_qty);

        $("#product_min_qty").val(product_min_qty);

    
        minValue = product_min_qty;

        $("#item_qty").trigger("touchspin.updatesettings", {min: minValue});

        //bedefault total price should be calculated from product min qty

        if(product_min_qty && product_min_qty!='' && product_min_qty!=undefined)
        {
           var total_wholesale_price = product_min_qty * parseFloat(product_price);
           
           $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
        }


    }
   
   /* if(sku_inventory == 0)
    {
      var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
      $(".button-login-pb").hide();
      $(".out-of-stock-container").show();
      $("#item_qty").prop('disabled', true);
      $('#item_qty').val('1');
      $("#total_wholesale_price").text(sku_unit_price);  
    }*/

    if(sku_inventory < minValue)
    { 
      var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
      $(".button-login-pb").hide();
      $(".out-of-stock-container").show();
      $("#item_qty").prop('disabled', true);
      //$('#item_qty').val('1');
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



</script>
<!-- Product Detail link End -->


@stop