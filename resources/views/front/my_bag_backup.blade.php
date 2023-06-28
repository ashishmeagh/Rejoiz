@extends('front.layout.master') 
@section('main_content')

 <style>
    

      .continue-shopping-bag a{
    font-size: 15px; padding: 9px 30px 8px;
  }
  .gt-button{
    font-size: 15px; padding: 9px 30px 8px; border-radius: 3px;
  }
    .gt-button {text-transform: capitalize;}
    .bag-clone-group{padding: 10px;}
    .main-titlebag .title-shopping{ margin-bottom: 0;
    margin-top: 20px;}
    .main-titlebag{margin-bottom: 10px;}
    .mrtp{margin-top: 0px;}
    .sweet-alert h3 {
        font-size: 18px;
        line-height: 24px;
        letter-spacing: 0px;
        width: 70%;
        margin: 0 auto;
    }
    .price-det-block{ min-height: 289px;}
    .sweet-alert button.cancel {
        background-color: #f5f5f5;
        color: #333;
    }
    
    .pro_qty {
        box-shadow: none;
        border: none;
        height: 40px;
        width: 51px;
        display: inline-block;
        padding: 3px 10px;
        background-image: url(assets/images/select-arrow.png);
        background-repeat: no-repeat;
        background-position: right;
        background-color: #fff;
        font-size: 14px;
        color: #333;
    }
    
    .qty-shop-bg {
        display: inline-block;
        margin-left: 0px;
    }
</style>
<style type="text/css">

</style>

<?php $login_user = "" ?>
{{Session::forget('guest_back_url')}}
    <div class="containermain-div">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 leftbagsidebar">
                    <div class="white-box theiaStickySidebar">
                        <div class="main-titlebag">
                        <div class="row">
                            <div class="col-md-6"> <div class="title-shopping">Shopping Cart</div></div>
                            <div class="col-md-6">
                             <div class="continue-shopping-bag mg-spc-tp pull-right">
                                <a href="{{url('/')}}/search" class="pull-left">Continue shopping</a> 
                                <div class="clearfix"></div>
                            </div>
                        <div class="clearfix"></div>
                            </div>
                        </div>
                        </div>
                       
                        @include('admin.layout._operation_status')
                        @if(isset($arr_final_data) && count($arr_final_data)>0) 
                        @foreach($arr_final_data as $product_details)
                        
                        <div class="bag-clone-group">
                            <div class="jst-mybg">
                                <div class="row">
                                    <div class="col-md-6">

                                    <div class="brand-name-title spc-top" data-brand="{{ $product_details['maker_details']['id'] or '' }}" data-brand-min="{{ $product_details['maker_details']['shop_settings']['first_order_minimum'] or '' }}">{{ $product_details['maker_details']['company_name'] or '' }}</div>
                                     
                                    @if(isset($product_details['maker_details']['shop_settings']['first_order_minimum']) && $product_details['maker_details']['shop_settings']['first_order_minimum']==0)

                                        <div class="save-it-main">No Minimum Limit</div>
                                    @else
                                        <div class="save-it-main">{{isset($product_details['maker_details']['shop_settings']['first_order_minimum'])?'Minimum Order Amount:$'.$product_details['maker_details']['shop_settings']['first_order_minimum']:'No Minimum Limit'}}</div>
                                    @endif
                                    </div>


                                    <div class="col-md-6">
                                        <div class="save-it-later-main">
                                            <div class="save-it-main-left-a">
                                                {{-- <a href="#">Save it Later</a> --}}
                                            </div>
                                            <div class="save-it-main-right-a">
                                                <span>Ship : ASAP</span> Usually shipped within 4-7 Days
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                          </div>  
                         {{--  {{dd($product_details['product_details'])}} --}}
                            <div class="row">
                                @if(isset($product_details['product_details']) && count($product_details['product_details']>0)) 
                                @foreach($product_details['product_details'] as $details)
{{--                                 {{dd($product_details['product_details'])}}
 --}}                               <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                    <div class="brand-dtls-nw shopping-bag-main mybx-main-shop">
                                        <div class="img-brnd">
                                            <img src="{{url('/storage/app/'.$details['product_image'])}}" alt="">
                                        </div>
                                        <div class="brnd-dtls-mn">
                                            <div class="product-name-bnd"> {{isset($details['product_name'])?ucfirst($details['product_name']):""}} </div>
                                            <div class="inpt-selects qty-bg">
                                                @php
                                                    $none = 'display:block';
                                                @endphp
                                                 
                                                 <div class="qty-shop-bg"><b>Unit Price:</b><span>$</span>{{ isset($details['wholesale_price'])?num_format($details['wholesale_price']) : '' }}</div>

                                                 
                                                 @if($details['product_dis_min_amt']==0)
                                                    @php
                                                        $none = "display:none";
                                                    @endphp
                                                 @endif
                                                    <span style = "{{$none}}">Min amount for product discount:${{isset($details['product_dis_min_amt'])?num_format($details['product_dis_min_amt']):''}}
                                                    </span>
                                                 
                                                 @if($details['product_dis amount']==0)
                                                    @php
                                                        $none = "display:none";
                                                    @endphp
                                                 @endif
                                                    <span style = "{{$none}}">Product Discount:${{isset($details['product_dis amount'])?num_format($details['product_dis amount']):''}}
                                                    </span>
                                                

                                                @php
                                                    //dd($wholesale_subtotal);
                                                    $product_discount += $details['product_dis amount'];
                                                    $wholesale_subtotal -=$details['product_dis amount'];
                                                    $none = 'display:block';
                                                @endphp
                                                
                                                @if($details['shipping_type']==2)
                                                    
                                                     @php $none = "display:block"; @endphp
                                                    
                                        
                                                    @if($details['minimum_amount_off']==0)
                                                        @php $none = "display:none"; @endphp
                                                    @endif
                                                    <span style = "{{$none}}">Min Shipping Order Amount : ${{$details['minimum_amount_off']}}</span>

                                                   
                                                    @php $none = "display:block"; @endphp
                                                    @if($details['shipping_charges']==0)
                                                       
                                                        @php $none = "display:none"; @endphp
                                                        
                                                    @endif


                                                    <span style = "{{$none}}">Min Order Amount(Shipping):${{$details['shipping_charges']+$details['shipping_discount']}}</span>

                                                    @php $none = "display:block"; @endphp
                                                    @if($details['shipping_discount']==0)
                                                         @php $none = "display:none"; @endphp
                                                    @endif
                                                      <span style = "{{$none}}">Min Order Amount(Discount) :${{$details['shipping_discount']}}</span>

                                                    @php
                                                     $shipping_charges += $details['shipping_charges'];
                                                     $shipping_discount += $details['shipping_discount'];
                                                     $wholesale_subtotal += $details['shipping_charges'];
                                                    @endphp
                                                
                                                @elseif($details['shipping_type']==3)
                                                    
                                                      
                                                    @if($details['minimum_amount_off']==0)
                                                        @php $none = "display:none"; @endphp
                                                    @endif 
                                                    <span style ="{{$none}}"> Min Shipping Order Amount : ${{$details['minimum_amount_off']}}</span>
                                                   
                                                   
                                                    @if($details['shipping_charges']==0)
                                                         @php $none = "display:none"; @endphp
                                                    @endif
                                                    <span style ="{{$none}}">Min Order Amount(Shipping):${{$details['shipping_charges']+$details['shipping_discount']}}</span>
                                                  

                                                    @if($details['shipping_discount']==0)
                                                         @php $none = "display:none"; @endphp
                                                    @endif
                                                   
                                                   <span style = "{{$none}}">Min Order Amount(Discount) :${{$details['shipping_discount']}}</span>
                                                   
                                                    @php
                                                     $shipping_charges += $details['shipping_charges'];
                                                     $shipping_discount += $details['shipping_discount'];
                                                     $wholesale_subtotal += $details['shipping_charges'];

                                                     
                                                    @endphp
                                                
                                                @elseif($details['shipping_type']==1)
                                                    
                                                   
                                                    @if($details['shipping_charges']==0)
                                                         @php $none = "display:none"; @endphp
                                                    @endif
                                                    <span style = "{{$none}}" >Min Order Amount(Shipping):${{$details['shipping_charges']+$details['shipping_discount']+$details['shipping_discount']}}</span>

                                                    @if($details['shipping_discount']==0)
                                                        @php $none = "display:none"; @endphp
                                                    @endif
                                                    <span style = "{{$none}}" >Min Order Amount(Discount) :${{$details['shipping_discount']}}</span>

                                                    @php
                                                     
                                                     $shipping_charges += $details['shipping_charges'];
                                                     $shipping_discount += $details['shipping_discount'];
                                                     $wholesale_subtotal += $details['shipping_charges'];
                                                    @endphp

                                                    @else

                                                    
                                                 @endif

                                                @php $login_user = Sentinel::check(); @endphp 
                                                @if($login_user==true && $login_user->inRole('retailer'))
                                               {{--  <div class="qty-shop-bg"><b>Price:</b><span>$</span>{{ isset($details['wholesale_price'])?num_format($details['wholesale_price']) : '' }}</div><br> --}}
                                                <div class="price-bnd-dtls"><span>Total Order Amount:$</span><span id="product_wholesale_total{{$details['sku_no']}}" data-brand-id="{{ isset($product_details['maker_details']['id'])?$product_details['maker_details']['id']:'' }}">{{ isset($details['total_wholesale_price'])?num_format($details['total_wholesale_price']) : '' }}</span></div>
                                                <div class="clearfix"></div>
                                                @else {{-- {{dd($details)}} --}}
                                                <div class="qty-shop-bg"><b>Price:</b><span>$</span>{{ isset($details['unit_retail_price'])?num_format($details['unit_retail_price']) : '' }}{{-- <span>{{ $details['item_qty'] or '' }}</span> --}}</div>
                                                <div class="price-bnd-dtls"><span>$</span><span id="product_total_{{$details['sku_no']}}" data-brand-id="{{ $product_details['maker_details']['id'] }}">{{ isset($details['total_price'])?num_format($details['total_price']) : '' }}

                                                </span></div>

                                                @endif
                                                <div class="main-qnt-bottom">
                                                    <div class="qty-shop-bg">Qty : {{-- <span>{{ $details['item_qty'] or '' }}</span> --}}</div>
                                                    <select name="pro_qty" class="pro_qty" data-sku-no="{{base64_encode($details['sku_no'])}}" data-parsley-max = "{{get_product_quantity($details['sku_no'])}}" data-pro-id="{{base64_encode($details['product_id'])}}">
                                                        @for($i=1;$i
                                                        <=get_product_quantity($details['sku_no']);$i++) <option @if(isset($details[ 'item_qty']) && $details[ 'item_qty']==$i) selected="" @endif value="{{$i}}">{{$i}}</option>
                                                            @endfor
                                                    </select>

                                                </div>

                                                <div class="clearfix"></div>
                                                
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <a href="{{url('/my_bag/delete_product/'.base64_encode($details['sku_no']))}}" class="close-brnd" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');">
                                            <img src="{{url('/')}}/assets/front/images/close-txt.png" alt="">
                                        </a>
                                    </div>
                                </div>
                                @endforeach @else
                                <div class="not-found-data whitebg-no"> No record found</div>
                                @endif
                            </div>

                           
                        
                        @endforeach 
                        <div class="row">
                               <div class="col-md-12">
                                    @if(isset($arr_final_data) && count($arr_final_data)>0)
                                    <a class="gt-button mrtp pull-right" id=empty_cart>Empty Cart</a> 
                                    @endif
                                    <div class="clearfix"></div>
                               </div>
                           </div>
                           
                        @else
                        <div class="not-found-data whitebg-no">Your shopping cart is empty.</div>
                        @endif 

                        {{--
                        <div class="title-shopping top-space-makers">Makers Under Minimum</div>
                        <div class=" pull-left brand-name-title">Brand Name <span> $ 100</span></div>
                        <div class="under-minimum-price-txt">$ 221 Under minimum</div>
                        <div class="clearfix"></div>
                        <div class="save-it-later-main">
                            <div class="save-it-main-left-a">
                                <a href="#">Save it Later</a>
                            </div>
                            <div class="save-it-main-right-a">
                                <span>Ship : ASAP</span> Usually ships within 4-7 Days
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="brand-dtls-nw shopping-bag-main">
                                    <div class="img-brnd">
                                        <img src="{{url('/')}}/assets/images/London.jpg" alt="">
                                    </div>
                                    <div class="brnd-dtls-mn">
                                        <div class="product-name-bnd">Product Name</div>
                                        <div class="inpt-selects">
                                            <div class="qty-shop-bg">Qty: <span>2</span></div>
                                            <div class="price-bnd-dtls">$ 96.00</div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <a href="#" class="close-brnd">
                                        <img src="{{url('/')}}/assets/front/images/close-txt.png" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="brand-dtls-nw shopping-bag-main">
                                    <div class="img-brnd">
                                        <img src="{{url('/')}}/assets/images/London.jpg" alt="">
                                    </div>
                                    <div class="brnd-dtls-mn">
                                        <div class="product-name-bnd">Product Name</div>
                                        <div class="inpt-selects">
                                            <div class="qty-shop-bg">Qty: <span>2</span></div>
                                            <div class="price-bnd-dtls">$ 96.00</div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <a href="#" class="close-brnd">
                                        <img src="{{url('/')}}/assets/front/images/close-txt.png" alt="">
                                    </a>
                                </div>
                            </div>
                        </div> --}}
                       
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 rightbagsidebar">
                    <div class="theiaStickySidebar">

                        

                        <div class="price-det-block">
                            <h2>Order Summary</h2>
                            <div class="order-sum">
                            @if($login_user==true && $login_user->inRole('retailer'))
                                        <h5>Subtotal 
                                  <span id="wholesale_subtotal" class = "subtotal">{{ isset($wholesale_total)?num_format($wholesale_total) : 00 }}</span>
                                  <span>$</span>
                            @else
                                    <h5>Subtotal 
                                    <span id="subtotal" class="subtotal">{{ isset($subtotal)?num_format($subtotal): 00 }}</span>
                                    <span>$</span>
                                    </h5> 
                            @endif
                                </h5>
                                <div class="mybag-list">
                                   
                     
                                    @php $none ="display:block"; @endphp
                                    @if($product_discount==0)
                                       @php $none ="display:none"; @endphp
                                    @endif
                                     <span style="{{$none}}"><div class="mybag-list-left">Product Discount</div></span>
                                     <span style="{{$none}}"><div class="mybag-list-right"></div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($product_discount>0)-@endif${{isset($product_discount)?$product_discount:'0'}}</div></span>

                                    <div class="clearfix"></div>
                                </div>
                                <div class="mybag-list">
                                    @php $none ="display:block"; @endphp
                                    @if($shipping_charges==0)
                                      
                                       @php $none ="display:none"; @endphp
                                       
                                    @endif
                                    <span style="{{$none}}"><div class="mybag-list-left">Shipping Charges</div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($shipping_charges>0)+@endif${{isset($shipping_charges)?$shipping_charges+$shipping_discount:'0'}}</div></span>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="mybag-list">
                                    @php $none ="display:block"; @endphp
                                     @if($shipping_discount==0)
                                       @php $none ="display:none"; @endphp
                                    @endif
                                    <span style="{{$none}}"><div class="mybag-list-left"> Discount On Shipping</div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($shipping_discount>0)-@endif${{isset($shipping_discount)?$shipping_discount:'0'}}</span>
                                    {{Session::put('shipping_discount',$shipping_discount)}}
                                    {{Session::put('product_discount',$product_discount)}}
                                </div>
                                    <div class="clearfix"></div>
                                </div>
                                <hr>
                                <h5>Order Total 
                  @if($login_user==true &&  $login_user->inRole('retailer'))
                    <span id="wholesale_total">{{ isset($wholesale_subtotal)?num_format($wholesale_subtotal) : 00 }}</span> 
                    <span>$</span>
                  @else
                    <span id="cart_total">{{ isset($subtotal)?num_format($subtotal) : 00 }}</span>
                    <span>$</span>
                  @endif  
                  </h5> {{--
                                <h4>Shipping</h4>
                                <div class="row">
                                    <div class="col-xs-7 col-sm-7 col-md-7 col-lg-7">
                                        <div class="flate-rate">Flate Rate</div>
                                        <div class="flate-rate">Flate Rate</div>
                                    </div>
                                    <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
                                        <div class="price-right-ttl">$ 15.00</div>
                                        <div class="price-right-ttl">$ 10.00</div>
                                    </div>
                                </div> --}} {{--
                                <hr> --}} {{--
                                <div class="clearfix"></div>
                                <h5>Order Total <span>$515.00</span></h5> --}}
                                <div class="clearfix"></div>
                            </div>
                        </div>

                         <div class="bag-details">
                             <h2>Product Summary</h2>
                            <div class="innerbag-details">

                                @if(isset($arr_final_data) && count($arr_final_data)>0) 
                                  @foreach($arr_final_data as $product_details)
                                    

                                     @if(isset($product_details['product_details']) && count($product_details['product_details'])>0) 
                                       @foreach($product_details['product_details'] as $arr_product_data)
                                            
                                            @php
                                                $product_shipping_charg = isset($arr_product_data['shipping_charges'])?num_format($arr_product_data['shipping_charges']):'0.00';

                                                $ship_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):'0.00';

                                                $shipping_charg = $product_shipping_charg + $ship_discount;

                                                $prod_discount = isset($arr_product_data['product_dis amount'])?num_format($arr_product_data['product_dis amount']):'0.00';

                                                $item_qty = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:0;

                                                $unit_price = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:0;

                                                $sub_total = $item_qty*$unit_price;

                                                $total_amt = ($sub_total)+($shipping_charg)-($prod_discount)-($ship_discount);
                                            @endphp

                                             <div class="clone-bag-detials">
                                               
                                                <div class="bag-detailstitle">Product Name : {{isset($arr_product_data['product_name'])?$arr_product_data['product_name']:'N/A'}}</div>

                                                <div class="baglist-sub">Sub Total : <span>${{isset($sub_total)?num_format($sub_total):'0.00'}}</span></div>

                                                @php $none = "display:block"; @endphp
                                                @if($shipping_charg==0)
                                                    @php $none = "display:none"; @endphp
                                                @endif 
                                                <span style="{{$none}}"><div class="baglist-sub">Shipping Charges : <span> 
                                                    @if($shipping_charg > 0) +@endif${{isset($shipping_charg)?$shipping_charg:'0.00'}}</div></span>



                                                @php $none = "display:block"; @endphp
                                                @if($ship_discount==0)
                                                    @php $none = "display:none"; @endphp

                                                @endif 
                                                <span style="{{$none}}"><div class="baglist-sub">Shipping Discount : <span>@if($ship_discount>0)-@endif${{isset($ship_discount)?$ship_discount:'0.00'}}</div></span>
                                              



                                                @php $none = "display:block"; @endphp
                                                @if($prod_discount==0)
                                                    @php $none = "display:none"; @endphp
                                                @endif 

                                                <span style ="{{$none}}"><div class="baglist-sub">Product Discount : <span>@if($prod_discount>0)-@endif${{isset($prod_discount)?$prod_discount:'0.00'}}</div></span>

                                                


                                                <strong><div class="baglist-sub">Total : <span>${{isset($total_amt)?num_format($total_amt):'0.00'}}</span></div></strong>



                                            </div>

                                       @endforeach
                                     @endif

                                  @endforeach
                                @endif

                            </div>
                        </div>

                        <form id="frm-apply-quotes">
                            {{ csrf_field() }}
                            @if(isset($product_data) && sizeof($product_data)>0)
                              <input type="hidden" name="product_data" value="{{$product_data}}"> 
                              <input type="hidden" name="bag_id" value="{{ $bag_id or 0 }}">
                            @endif
                            @if(isset($arr_final_data) && count($arr_final_data)>0)
                            <div class="button-login-pb">
                                
                                @if(Sentinel::check() == true)
                                <a href="javascript:void(0)" onclick="save_bag();" class="gt-button apply-quotes">Buy Now</a> 
                                @else
                                <a href="javascript:void(0)" onclick="save_bag();" class="gt-button apply-quotes">Buy Now</a> 
                                @endif


                                {{-- <a href="javascript:void(0)" onclick="tempSaveBag();" class="gt-button apply-quotes">Apply To Quotes</a>  --}}
                               
                                

                                <br>
                        
                               
                                <div class="clearfix"></div>
                            </div>
                            @endif
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{url('/')}}/assets/front/js/theia-sticky-sidebar.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.leftbagsidebar, .rightbagsidebar')
                .theiaStickySidebar({
                    additionalMarginTop: 130
                });
        });
    </script>
    <script type="text/javascript">
        $('.pro_qty').on('change', function() 
        {
            var qty = this.value;
            var pro_id = $(this).attr("data-pro-id");
            var sku_no = $(this).attr("data-sku-no");

            var url = '{{url('/')}}/my_bag/update_qty';

            
            $.ajax({
                url: url,
                data: {
                    pro_id: pro_id,
                    qty: qty,
                    sku_no: sku_no
                },
                method: 'GET',
                beforeSend: function() {
                    showProcessingOverlay();
                    //$('.apply-quotes').prop('disabled',true);
                    //$('.apply-quotes').html('Please Wait <i class="fa fa-spinner fa-pulse fa-fw"></i>');
                },
                success: function(response) {
                    hideProcessingOverlay();
                    if (response.status == "SUCCESS") {
                        
                        var total_price = response.total_price.toFixed(2);
                        
                        var total_wholesale_price = response.total_wholesale_price.toFixed(2);
                        var subtotal = response.subtotal.toFixed(2);
                        var wholesale_subtotal = response.wholesale_subtotal.toFixed(2);

                        $("#product_total_" + atob(sku_no)).text(total_price);
                        $("#subtotal").text(subtotal);
                        $("#product_wholesale_total" + atob(sku_no)).text(total_wholesale_price);
                        $("#wholesale_subtotal").text(wholesale_subtotal);

                        $("#cart_total").text(subtotal);
                        $("#wholesale_total").text(wholesale_subtotal);

                        window.location.reload();
                    }
                }
            })

        });

        $('#empty_cart').on('click', function() {

            var url = '{{url('/')}}/my_bag/empty_cart';

            swal({
                    title: "Need Confirmation",
                    text: "Are you sure? Do you want to empty the cart.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                },
                function() 
                {
                  
                    $.ajax({
                        url: url,
                        method: 'GET',

                        success: function(response) {
                            hideProcessingOverlay();
                            if (response.status == "success")
                            {
                                swal({
                                        title: "Success",
                                        text: "Your cart is empty.",
                                        type: response.status,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false
                                    },
                                    function(isConfirm, tmp) {
                                        if (isConfirm == true) {
                                            window.location = response.next_url;
                                        }
                                    });
                            } 
                            else 
                            {
                                swal({
                                        title: "Error",
                                        text: "Something went wrong,please try again.",
                                        type: response.status,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false
                                    },
                                    function(isConfirm, tmp) 
                                    {
                                        if (isConfirm == true) 
                                        {
                                            window.location = response.next_url;
                                        }
                                    });

                            }
                        }
                    })

                });
        });

    function save_bag(ref) {
        /* Iterate All Products */
        var _arrProductRef = [];
        var _brandIdWiseSubTotal = {};
        var _brandIdWiseDetails = {};
        var _isValidForQuotes = true;
        var _minBrandMsg = "";

        if($('[id^=product_total_]').length > 0)
        {
            _arrProductRef = $('[id^=product_total_]');
        }
        else if($('[id^=product_wholesale_total]').length > 0)
        {
            _arrProductRef = $('[id^=product_wholesale_total]');   
        }
        /* Get Brand Details */
        if($('[data-brand]').length > 0)
        {
            var _arrBrand  = $('[data-brand]');

            $.each(_arrBrand,(_index,_elem) => {
                var _tmpBrandId = $(_elem).attr("data-brand");                
                var _tmpBrandMin = $(_elem).attr("data-brand-min");                
                var _tmpBrandName = $(_elem).html().trim();

                _brandIdWiseDetails[_tmpBrandId] = {};               
                _brandIdWiseDetails[_tmpBrandId]['min'] = _tmpBrandMin;               
                _brandIdWiseDetails[_tmpBrandId]['name'] = _tmpBrandName;               
            });
          
        }


        /* Brand ID wise SubTotal */
        $.each(_arrProductRef,(_index,_elem) => {
            var _tmpTotal = parseFloat($(_elem).html());

           
            var _tmpBrandId = $(_elem).attr("data-brand-id");

            if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
                _brandIdWiseSubTotal[_tmpBrandId] = 0;
            } 

            _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
        });
    
        _minBrandMsg+='<ul>';     
        for(let _tmpBrandId in _brandIdWiseSubTotal)
        {
            if(_brandIdWiseDetails[_tmpBrandId] != undefined)
            {
               if(_brandIdWiseSubTotal[_tmpBrandId] < _brandIdWiseDetails[_tmpBrandId].min )   
               {
                    _isValidForQuotes = false;

                    _minBrandMsg+='<li> <b>'+_brandIdWiseDetails[_tmpBrandId].name+' </b>: Minimum Required $'+_brandIdWiseDetails[_tmpBrandId].min+', Current Subtotal: $ '+_brandIdWiseSubTotal[_tmpBrandId]+'  </li>'
               }
            }
        }
        _minBrandMsg+='</ul>';

        if(_isValidForQuotes == false)
        {
            swal({
                    title: "Note",
                    text: "Please make sure cart total for following brands satisfies with min. amount<br>"+_minBrandMsg,
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
            return; 
        }


            if($('#frm-apply-quotes').parsley().validate()==false) return;

           var bag_data = $("#frm-apply-quotes").serialize();
          
           var bag_form_data = $("#frm-apply-quotes").val();
/*           alert(bag_form_data);
*/           //alert(bag_data);

            $.ajax({
                url: SITE_URL + '/my_bag/save_bag',
                data: bag_data,
                method: 'POST',
                beforeSend: function() {
                    // showProcessingOverlay();
                    $('.apply-quotes').prop('disabled', true);
                    $('.apply-quotes').html('Please Wait <i class="fa fa-spinner fa-pulse fa-fw"></i>');
                },
                success: function(response) {
                    // hideProcessingOverlay();
                    $('.apply-quotes').prop('disabled', false);
                    $('.apply-quotes').html('Buy Now');

                    if (typeof response == 'object') {
                        if (response.status == "SUCCESS") {
                            window.location.href = response.next_url;
                        } else {

                        }
                    }
                }
            });
        }




    function tempSaveBag(ref) {
//alert("okkkk");
        /* Iterate All Products */
        var _arrProductRef = [];
        var _brandIdWiseSubTotal = {};
        var _brandIdWiseDetails = {};
        var _isValidForQuotes = true;
        var _minBrandMsg = "";

        if($('[id^=product_total_]').length > 0)
        {
            _arrProductRef = $('[id^=product_total_]');
        }
        else if($('[id^=product_wholesale_total]').length > 0)
        {
            _arrProductRef = $('[id^=product_wholesale_total]');   
        }
        /* Get Brand Details */
        if($('[data-brand]').length > 0)
        {
            var _arrBrand  = $('[data-brand]');

            $.each(_arrBrand,(_index,_elem) => {
                var _tmpBrandId = $(_elem).attr("data-brand");                
                var _tmpBrandMin = $(_elem).attr("data-brand-min");                
                var _tmpBrandName = $(_elem).html().trim();

                _brandIdWiseDetails[_tmpBrandId] = {};               
                _brandIdWiseDetails[_tmpBrandId]['min'] = _tmpBrandMin;               
                _brandIdWiseDetails[_tmpBrandId]['name'] = _tmpBrandName;               
            });
          
        }


        /* Brand ID wise SubTotal */
        $.each(_arrProductRef,(_index,_elem) => {
            var _tmpTotal = parseFloat($(_elem).html());
            var _tmpBrandId = $(_elem).attr("data-brand-id");

            if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
                _brandIdWiseSubTotal[_tmpBrandId] = 0;
            } 

            _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
        });
    
        _minBrandMsg+='<ul>';     
        for(let _tmpBrandId in _brandIdWiseSubTotal)
        {
            if(_brandIdWiseDetails[_tmpBrandId] != undefined)
            {
               if(_brandIdWiseSubTotal[_tmpBrandId] < _brandIdWiseDetails[_tmpBrandId].min )   
               {
                    _isValidForQuotes = false;

                    _minBrandMsg+='<li> <b>'+_brandIdWiseDetails[_tmpBrandId].name+' </b>: Minimum Required $'+_brandIdWiseDetails[_tmpBrandId].min+', Current Subtotal: $ '+_brandIdWiseSubTotal[_tmpBrandId]+'  </li>'
               }
            }
        }
        _minBrandMsg+='</ul>';

        if(_isValidForQuotes == false)
        {
            swal({
                    title: "Note",
                    text: "Please make sure cart total for following brands satisfies with min. amount<br>"+_minBrandMsg,
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
            return; 
        }


           var bag_data = $("#frm-apply-quotes").serialize();
          
           var bag_form_data = $("#frm-apply-quotes").val();
/*           alert(bag_form_data);
*/           //alert(bag_data);
            $.ajax({
                url: SITE_URL + '/my_bag/temp_save_bag',
                data: bag_data,
                method: 'POST',
                beforeSend: function() {
                    // showProcessingOverlay();
                    $('.apply-quotes').prop('disabled', true);
                    $('.apply-quotes').html('Please Wait <i class="fa fa-spinner fa-pulse fa-fw"></i>');
                },
                success: function(response) {
                    // hideProcessingOverlay();
                    $('.apply-quotes').prop('disabled', false);
                    $('.apply-quotes').html('Applt To Quotes');

                    if (typeof response == 'object') {
                        if (response.status == "SUCCESS") {
                            window.location.href = response.next_url;
                        } else {

                        }
                    }
                }
            });
        }





        function proceed_to_checkout()
        {
            let url = SITE_URL+'/retailer/set_session';
            let bag_data = $("#frm-apply-quotes").serialize();
            let wholesale_total = $('#wholesale_total').text();
                
            bag_data += '&wholesale_total='+wholesale_total;

            $.ajax({
                url: url,
                data: bag_data,
                method: 'POST',
                beforeSend: function() 
                {
                    showProcessingOverlay();                 
                    $('#buy_now').prop('disabled', true);
                    $('#buy_now').html('Please Wait...');
                },
                success: function(response) {
                    hideProcessingOverlay();
                    $('#buy_now').prop('disabled', false);
                    $('#buy_now').html('Pay');

                    if (typeof response == 'object') 
                    {
                        if (response.status == "SUCCESS") 
                        {  
                            location.href = SITE_URL+'/retailer/checkout';
                        }
                    }
                }
            });   
        }

    </script>

    @stop