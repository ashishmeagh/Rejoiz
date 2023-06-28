@extends('front.layout.master')
@section('main_content')

<style type="text/css">
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
.retail_price_product .pricewholsl {display:inline-block;}
</style>

<!-- Product Detail link Start -->
    {{-- <script defer async type="text/javascript" src="{{url('/')}}/assets/front/js/jdetail.js"></script> --}}
    <link href="{{url('/')}}/assets/front/css/prodetail.css" rel="stylesheet" type="text/css" />

    <link href="{{url('/')}}/assets/front/css/gallery.css" rel="stylesheet" type="text/css" />
   
 <script   type="text/javascript" src="{{url('/')}}/assets/front/js/gallery.min.js"></script>
    <script>
			$(document).ready(function() {
				$('#example1').rwaltzGallery({
					openGalleryStyle: 'transform',
					changeMediumStyle: true
				});


			});

		</script>
    <!-- Product Detail link End -->


<!-- Breadcrubm section Start -->
<section class="prodetail_sec breadcrumb_section">
			<div class="container">
        <div class="row">
          <div class="pro_breadcrumb">
         <ul class="breadcrumb">
          <li><a href="{{url('/')}}">Home</a></li>
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
$previous_url = '';
if(Request::session()->has('_previous')){
  $previous_url = isset(Session::get('_previous')['url'])?Session::get('_previous')['url']:'';
}

@endphp

<!-- Product Detail Section Start -->
<section class="prodetail_sec">
	<div class="container">
		<div class="row">
			<div class="col-sm-8">
					<!-- gallery hear -->
					<div id="example1" class="rwaltz-gallery img300">
						<div class="prod-carousel">
              @if(isset($first_prod_arr['product_details']) && count($first_prod_arr['product_details']) > 0)

                @foreach($first_prod_arr['product_details'] as $prod_key =>$product_details) 

                  @php
                      $product_img       = false;
                      $product_img_thumb = false;

                      $product_image_base_path  = base_path('storage/app/'.$product_details['image']);

                      $product_img_thumb      = image_resize($product_image_base_path,77,77);
                      $product_img            = image_resize($product_image_base_path,400,400);
                      $product_original_image = url('/').'/storage/app/'.$product_details['image'];


                      if(isset($imgSku) && $imgSku == $product_details['sku'])
                      {
                       $imgSkuSquence = $prod_key;
                     }

                     @endphp     

                 <!-- <img src="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-medium-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-big-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" alt=""> -->

                 <img src="{{$product_img or ''}}" data-medium-img="{{$product_img or ''}}" data-big-img="{{$product_img or ''}}" data-title="Mustang Shelby GT500 - big black car with red lines is very beautiful and powerful" alt="" data-produt-id="{{base64_encode($product_details['product_id'])}}" data-sku-id="{{base64_encode($product_details['sku'])}}" data-sku-description="{{ $product_details['sku_product_description'] }}" data-sku-inventory="{{ $product_details['inventory_details']['quantity'] or '' }}" onclick="sku_detail(this)">

							    <!-- <img src="{{$meta_details['meta_image'] or ''}}" data-medium-img="{{$meta_details['meta_image'] or ''}}" data-big-img="{{$meta_details['meta_image'] or ''}}" data-title="Mustang Shelby GT500 - big black car with red lines is very beautiful and powerful" alt=""> -->
                @endforeach
              @endif

							<!-- <img src="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-medium-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-big-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" alt="">

							<img src="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-medium-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-big-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" alt="">
							
							<img src="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-medium-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-big-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-title="The R/T Plus is both an athlete and a scholar. 2015 Challenger R/T Plus kicks the HEMI® V8 engine up a notch by adding upgraded technology and features." alt="">
							
							<img src="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-medium-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" data-big-img="https://travelmoredotph.files.wordpress.com/2018/02/001-getgo-shopping-rewards.jpeg?w=709" alt=""> -->
							
							
						</div>
					</div>
					<!-- gallery hear End -->
				</div>
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


    				<div class="col-sm-4 pro_detail_right">
    					<!-- <h5>{{$product_arr['brand_details']['brand_name'] or ''}}</h5> -->
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
                             {{-- {{dd($login_user)}} --}}
                             @if($login_user == true && ($login_user->inRole('customer') || $login_user->inRole('influencer')))
                             @else
                             <div class="pro-title-list brand_price">{{$minimum_order}} </div>
                             @endif
                             @endif

                        </h5>
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
                               data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value = 1 data-parsley-type="integer" data-parsley-trigger="change" data-parsley-max=1000> 
                            <input type="hidden" id = "prod_qty"> 
                            <div id="error_item_qty" style="display: none;"></div>             
                            </div>
                        </div>
    						<!-- <button class="btn cartbtn">Add to Bag</button> -->
                        @php
                            $login_user = Sentinel::check();
                        @endphp
                        @if($login_user==true &&  $login_user->inRole('maker')== true)
                            <div class="button-login-pb" >
                            <a href="javascript:void(0)" style="cursor: not-allowed;" class="gt-button">Add to Bag</a>
                            <div class="clearfix"></div>
                            </div>
                        @else
                            <div class="button-login-pb" >
                            <a href="javascript:void(0)" class="gt-button" id="add-to-bag" style= "display:{{$show_add_cart_btn}}" >Add to Bag</a>
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
                                   <input type="hidden" name="logged_in_user_role" id = "logged_in_user_role" value="retailer"}>

                                @endif

                             @endif
                            <input type="hidden" name="is_logged_in" id = "is_logged_in" value={{$login_user}}>


                             @php
                             $login_user = Sentinel::check();
                             @endphp
                        
                            @if($login_user==true && 
                            ($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative') ))
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


    						<!-- <div class="total_price text-center">
    							<h3>Total Price - $1449.00</h3>
    						</div> -->
    						<!-- <div class="shippingtime">
    							<svg class="sc-qZtVr dLIPjF" focusable="false" viewBox="0 0 36 36" color="#677161" aria-hidden="true" role="presentation" font-size="33px"><path d="M16.2253 5.61973L23.8873 1.11267L27.9437 3.14085M16.2253 5.61973L20.0563 7.98593M16.2253 5.61973V12.3803L23.8873 16.8874M23.8873 10.3521L32 5.61973M23.8873 10.3521L20.0563 7.98593M23.8873 10.3521V16.8874M32 5.61973L27.9437 3.14085M32 5.61973V12.3803L23.8873 16.8874M20.0563 7.98593L27.9437 3.14085" stroke="currentColor" fill="transparent" stroke-linecap="round" stroke-linejoin="round"></path><line x1="5.90845" y1="3.7677" x2="11.2183" y2="3.7677" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></line><line x1="0.5" y1="8.27454" x2="9.41553" y2="8.27454" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></line><line x1="5.90845" y1="12.3311" x2="11.2183" y2="12.3311" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></line></svg>
    							<p>Next-day delivery, & shipping available</p>
    						</div>
    						<div class="awaybox">
    							<div class="innerawaybox text-center">
    								<img src="{{url('/assets/front/images/map-pin.svg')}}">
    								<h3>Cove</h3>
    								<h5>0.6 miles away</h5>
    							</div>
    						</div> -->
    				</div>
                </form>
		</div>
	</div>
	<div class="container tab_container">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab1" data-toggle="tab">Description</a></li>  
    @if(isset($product_arr['ingrediants']) && !empty($product_arr['ingrediants']))
        <li><a href="#tab2" data-toggle="tab">Ingredients</a></li>
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
<!-- Product Detail Section End -->




<!--Add Home page slider hearer-->
<section>
  <div class="container">
    <div>
      <div class="best-seller-main-dv mobilepadding-o">
<div>
<hr>
<div class="titleof-seller-home">
    More collections you might like
</div>
<div class="clearfix"></div>
<!-- Demo Slider Start -->
<div class="Container slickcontainer">
    <h3 class="Head"><span class="Arrows"></span></h3>
    <!-- Carousel Container -->
    <div class="SlickCarousel">
        <!-- Item --> 
        @if(isset($related_product_arr) && sizeof($related_product_arr)>0)
                  @foreach($related_product_arr as $product)

                  @if(isset($product['product_details']) && count($product['product_details']) > 0)

                     @php
                        $login_user = Sentinel::check();

                        $product_img = false;

                        $product_base_img = isset($product['product_details']['product_image']) ? $product['product_details']['product_image'] : false;
                        $product_image_base_path  = base_path('storage/app/'.$product_base_img);
                        $product_default_image = url('/assets/images/no-product-img-found.jpg'); 
                        $product_img = image_resize($product_image_base_path,320,320,$product_default_image);

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
                              <div class="content-list-seller">
                                  <div class="one-linetxt" title="Product name"> {{ucfirst($product['product_details']['product_name'])}} </div>
                                  <div class="price-product inlineprice">
                                     <!--  <div class="retail_price_product inlineprice font-weight-normal">
                                          <i class="fa fa-usd" aria-hidden="true"></i>12
                                      </div> -->                                     
                                       @php
                                          $login_user = Sentinel::check();
                                      @endphp

                                      @if($login_user==false)
                                        <div class="retail_price_product inlineprice font-weight-normal">
                                            <span class="pricewholsl">Retail</span>
                                       
                                              <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                          </div>

                                      @elseif($login_user == true && $login_user->inRole('customer') || $login_user->inRole('maker') ||
                                      $login_user->inRole('admin')||
                                      $login_user->inRole('influencer'))
                                        
                                          <div class="retail_price_product inlineprice font-weight-normal">
                                          
                                            <span class="pricewholsl">Retail</span>
                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['retail_price'])?num_format($product['product_details']['retail_price']) : ''}}
                                          </div>                                         
                               
                                       @else
                                          <div class="retail_price_product inlineprice font-weight-normal">
                                            <span class="pricewholsl">Wholesale</span>
                                            <i class="fa fa-usd" aria-hidden="true"></i>{{isset($product['product_details']['unit_wholsale_price'])?num_format($product['product_details']['unit_wholsale_price']) : ''}}
                                         </div>
                                          
                                       @endif
                                  </div>
                              </div>
                              <!-- </a> -->
                          </div>
                      </div>
              @endif
        @endforeach
        @endif
        <!-- <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div> -->
        <!-- <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div> -->
        <!-- <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div> -->
        <!-- <div class="ProductBlock">
            <div class="best-seller-list">
                <a href="">
                    <div class="btn06">
                        <img src="{{$product_img or ''}}" class="portraitpro" alt="img12">                        
                        <div class=""></div>
                        <div class="buttons">
                            <img src="{{url('/')}}/assets/front/images/eye-icon-view.png" alt="">
                </a>
                </div>
                </div>
                <div class="content-list-seller">
                    <div class="one-linetxt" title="Product name">Product name </div>
                    <div class="price-product inlineprice">
                        <div class="retail_price_product inlineprice font-weight-normal">
                            <i class="fa fa-usd" aria-hidden="true"></i>12
                        </div>
                    </div>
                </div>                
            </div>
        </div> -->
        <!-- foreach -->
        <!-- Item -->
    </div>
    <!-- Carousel Container -->

    <input type="hidden" id="previous_url_bag" name="previous_url_bag" value="{{$previous_url or ''}}">
</div>
    </div>
  </div>
</section>
<!--Add Home page slider hearer-->











<!-- <script src="{{url('/')}}/assets/front/js/setup.js"></script>
<script src="{{url('/')}}/assets/front/js/xzoom.min.js"></script>
 -->
<script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>

<!-- <script src="{{url('/')}}/assets/front/js/jquery.js"></script> -->
<script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>


<script type="text/javascript">

$(document).ready(function() 
{
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
        var qty_limit = 20;
        
      @php
      }
      else
      {
      @endphp
        var qty_limit = 1000;
      @php
      }
      @endphp
      if(check_qty>qty_limit)
      {
          swal('Warning','Purchase limit '+qty_limit+' units.','warning');
          flag ="false";
          $("#item_qty").val(qty_limit);
          return
      }
   });

    $('#item_qty').on('touchspin.on.startspin', function ()
    {
        var check_qty = $("#item_qty").val();
        @php
        if($login_user == true && $login_user->inRole('customer'))
        {
        @endphp
          var qty_limit = 20;
          
        @php
        }
        else
        {
        @endphp
          var qty_limit = 1000;
        @php
        }
        @endphp
        if(check_qty>qty_limit)
        {
            swal('Warning','Purchase limit '+qty_limit+' units.','warning');
            flag ="false";
            $("#item_qty").val(qty_limit);
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
          @php
          if($login_user == true && $login_user->inRole('customer'))
          {
          @endphp
            var qty_limit = 20;
            
          @php
          }
          else
          {
          @endphp
            var qty_limit = 1000;
          @php
          }
          @endphp 
          if(qty>qty_limit)
          { 
            total_wholesale_price = parseFloat(qty_limit) * parseFloat(product_price);
            
          }
          else
          { 
            total_wholesale_price = qty * parseFloat(product_price);

            console.log(qty,product_price);
            return;
           
          }
           
           $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
           console.log(total_wholesale_price);

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
            @php
            if($login_user == true && $login_user->inRole('customer'))
            {
            @endphp
              var qty_limit = 20;
              
            @php
            }
            else
            {
            @endphp
              var qty_limit = 1000;
            @php
            }
            @endphp
            if(qty>qty_limit)
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
      var qty_limit = 20;
      
    @php
    }
    else
    {
    @endphp
      var qty_limit = 1000;
    @php
    }
    @endphp

   if(qty<=0)
   {
       swal('Warning','Please enter quantity greater than zero.','warning');
       flag ="false";
       return
   } 
   
   if(qty>qty_limit)
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
          @php
          if($login_user == true && $login_user->inRole('customer'))
          {
          @endphp
            var red_url = SITE_URL+'/customer_my_bag/add';
            
          @php
          }
          else
          {
          @endphp
            var red_url = SITE_URL+'/my_bag/add';
          @php
          }
          @endphp

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

                        // var previous_url = '{{$previous_url}}';
                        var previous_url = $('#previous_url_bag').val();

                        if(previous_url != '')
                        {
                          @php
                          if($login_user == true && $login_user->inRole('customer'))
                          {
                          @endphp
                          //window.location.href = SITE_URL+'/customer_my_bag';
                          window.location.href = SITE_URL+'/search';
                            
                          @php
                          }
                          else
                          {
                          @endphp
                              window.location.href = SITE_URL+'/search';
                             //window.location.href = SITE_URL+'/my_bag';
                            
                          @php
                          }
                          @endphp
                          //window.location.href = previous_url;
                          // window.location.href = SITE_URL+'/my_bag';
                        }
                        else
                        {
                          window.location.href = SITE_URL+'/search';
                        }

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


 function sku_detail(ref)
 {
    var prod_id = $(ref).attr('data-produt-id');
    var enc_sku_change_id = $(ref).attr('data-sku-id');
    var description_change = $(ref).attr('data-sku-description');
    var replace_description_change = description_change.replace(/<[^>]*>?/gm, '');

    var sku_inventory = $(ref).attr('data-sku-inventory');
    var sku_unit_price = $('.sku_unit_price_for_change').val();


    var sku_change_id = atob(enc_sku_change_id);
  
    $('#sku_num').val(sku_change_id);
    $('#sku_change_description').text(replace_description_change);
    $('#quantity').text(sku_inventory);

    if(sku_inventory == 0)
    {
      var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
      $(".button-login-pb").hide();
      $(".out-of-stock-container").show();
      $("#item_qty").prop('disabled', true);
      $('#item_qty').val('1');
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

    // console.log(prod_id,sku_change_id,description_change);
    // return;
 }






</script>


@stop