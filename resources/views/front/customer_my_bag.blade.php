@extends('front.layout.master') 
@section('main_content')
<style>

.newss-bag{
    text-align: right;
}
.cpn-buttons.cpn-clrs-btn{ 
    border: 1px solid #4c5058;
    background-color: #4c5058;
    color: #fff;
 }

.rightbagsidebar{position: -webkit-sticky;
    position: sticky;
    top:100px;}
    .brand-dtls-nw.shopping-bag-main.mybx-main-shop .qty-shop-bg {
    font-size: 12px;
    margin-bottom: 10px;
  }
  .cpn-buttons:focus, .cpn-buttons:hover{
    color: #fff background-color: #fb3b61;;
  }
  .cnt-bg-fnt{font-size: 16px; font-weight: 600; margin-top: 10px;}
  .dis-amt{
      text-align: left;
    padding-right: 100px;
    position: relative;
    margin-bottom: 9px;}  
    .dis-amt span{
            display: inline-block;
        font-weight: 600;
        position: absolute;
        right: 0;
        top: 0;
        width: 60px;
        text-align: left;
    }
      .continue-shopping-bag a{
    font-size: 15px; padding: 9px 30px 8px;
  }
  .gt-button{
    font-size: 15px; padding: 9px 30px 8px; border-radius: 0px;
  }
    .gt-button {text-transform: capitalize;}
    .bag-clone-group{padding: 10px;}
    .main-titlebag .title-shopping{ margin-bottom: 0;
    margin-top: 20px;}
    .main-titlebag{margin-bottom: 10px;}
    .mrtp{margin-top: 0px;}

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

{{-- {{dd(Session::all())}} --}}

@php
// dump(Session::get('customer_promotion_data'));

@endphp

    <div class="containermain-div mybagpage">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8 leftbagsidebar">
                    <div class="white-box theiaStickySidebar mybagleftbox">
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
                        <?php $bag_count = get_bag_count(); ?>
                          @if (session()->has('unavailable_products'))
                           <div class="alert alert-danger">
                              <button type="button" class="close" style="margin-top: 0px !important;padding: 0px !important;" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {!! session()->get('unavailable_products')!!}
                         </div>
                         @endif
                        @include('admin.layout._operation_status')
                        @php
                            /* Promotion Data in Session */
                            $session_promotion_data = [];
                            $total_promotion_discount_amt = 0;
                            $retail_subtotal = 0;

                            // dd(Session::get('customer_promotion_data'));

                            if(is_array(Session::get('customer_promotion_data')) && Session::get('customer_promotion_data') != ""){

                                $session_promotion_data = Session::get('customer_promotion_data');
 
                                /*$promo_shipping_charges = isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges'])?$session_promotion_data[$key]['final_total'][$key]['shipping_charges']:1;*/

                                $total_wholesale_price = isset($session_promotion_data['total_wholesale_price'])?$session_promotion_data['total_wholesale_price']:0;

                                $promotion_discount_amt = isset($session_promotion_data['discount_amt'])?$session_promotion_data['discount_amt']:0;

                                $promotion_discount_percent = isset($session_promotion_data['discount_percent'])?$session_promotion_data['discount_percent']:0;

                                $total_promotion_discount_amt += $promotion_discount_amt;

                                // dd($total_promotion_discount_amt);

                            }

                        @endphp
                        
                            @if(isset($bag_count) && $bag_count > 0)
                         

                        <div class="bag-clone-group customer-bag-clone-group">
                            <div class="jst-mybg">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="coupncode-inpt newcoupncode">
                                            @if(!empty($session_promotion_data))
                                                <input type="text" placeholder="Enter Promo Code" name="promo_code" readonly="" value="{{ $session_promotion_data['promo_code'] or ''}}" />

                                                <button type="button" class="cpn-buttons cpn-clrs-btn" id="btn_apply_code" name="btn_apply_code" onclick="clearPromoCode();"><i class="fa fa-times"> </i> Clear</button>
                                            @else
                                                <input type="text" placeholder="Enter Promo Code" id="promo_code" name="promo_code" data-parsley-required="true"/>
                                                
                                                <button type="button" class="cpn-buttons" id="btn_apply_code" name="btn_apply_code" onclick="applyPromoCode();"><i class="fa fa-check"> </i> Apply</button>
                                                <span class="red" id="promo_error"></span>

                                            @endif    
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="save-it-later-main">
                                          
                                            <div class="save-it-main-right-a">
                                                <span>Ship : ASAP</span> Usually shipped within 4-7 days
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="newss-bag">
                                            {{-- @if(isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges']))
                                                <div class="cnt-bg-fnt">Free Shipping</div>
                                            @endif --}}

                                            @if(isset($session_promotion_data['total_wholesale_price']) && isset($total_wholesale_price))
                                                <div>Promotion Discount({{$promotion_discount_percent}}%): ${{num_format($promotion_discount_amt)}} </div>
                                            @endif
                                        </div>
                                    </div>

                                    
                                </div>
                                
                            </div>
                        </div>  
                         @endif            
            
                             
                        @if(isset($arr_final_data) && count($arr_final_data)>0) 

                        @foreach($arr_final_data as $key => $product_details)
                        

                         @php
                             if($is_reorder == '1')  
                             {
                                $maker_amount = isset($product_details['maker_details']['shop_settings']['re_order_minimum'])?$product_details['maker_details']['shop_settings']['re_order_minimum']:'No Minimum Limit'; 
                             }
                             else{
                                 $maker_amount = isset($product_details['maker_details']['shop_settings']['first_order_minimum'])?num_format($product_details['maker_details']['shop_settings']['first_order_minimum']):'No Minimum Limit';
                             } 
                       @endphp

                          

                        
                            <div class="row">
                                <div class="col-sm-12 brand-name-title-main-div">
                                    <div class="brand-name-title spc-top m" data-brand="{{ $product_details['maker_details']['id'] or '' }}" data-brand-min="{{ $maker_amount or '' }}">
                                    {{ $product_details['maker_details']['company_name'] or '' }}
                                    </div>  
                                </div>
                                {{-- {{dd(empty($session_promotion_data))}} --}}
                                
                                @if(isset($product_details['product_details']) && count($product_details['product_details']>0)) 
                                    @foreach($product_details['product_details'] as $details)

                                        @php 
                                            $product_available_quantity = "";
                                            $product_available_quantity = check_product_quantity( $details['sku_no']); 
                                        @endphp
                             
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
                                            <div class="brand-dtls-nw shopping-bag-main mybx-main-shop">
                                                <div class="img-brnd">
                                                    @php
                                                    if(isset($details['product_image']) && $details['product_image']!='' && file_exists(base_path().'/storage/app/'.$details['product_image']))
                                                    {
                                                      $product_img = url('/storage/app/'.$details['product_image']);
                                                    }
                                                    
                                                    else
                                                    {
                                                        $product_img = url('/assets/images/no-product-img-found.jpg');
                                                    }                
                                                      
                                                    
                                                    @endphp
                                                    <img src="{{$product_img or ''}}" alt="">
                                                </div>

                                                <div class="brnd-dtls-mn">
                                                    <div class="product-name-bnd" title="{{isset($details['product_name'])?ucfirst($details['product_name']):""}}"> {{isset($details['product_name'])?ucfirst($details['product_name']):""}} 
                                                    </div>
                                                    <div class="inpt-selects qty-bg">
                                                        @php
                                                            $none = 'display:block';
                                                        @endphp
                                                         
                                                        <div class="qty-shop-bg">SKU No: <b>{{ isset($details['sku_no'])?$details['sku_no'] : '' }}</b></div><br>
                                                        @php
                                                        $login_user = Sentinel::check();
                                                       @endphp

            
                                                        @if($login_user->inRole('customer'))
                                                            <div class="qty-shop-bg">Unit Price:<span>$</span><b>{{ isset($details['unit_retail_price'])?num_format($details['unit_retail_price']) : '' }}</b></div>
                                                            <br>
                                                            @php
                                                            $subtotal = $details['unit_retail_price']*$details['item_qty'];
                                                            @endphp
        

                                                        @else
    
                                                            <div class="qty-shop-bg">Unit Price:<span>$</span><b>{{ isset($details['wholesale_price'])?num_format($details['wholesale_price']) : '' }}</b></div>
                                                            <br>
                                                            @php
                                                                $subtotal = $details['wholesale_price']*$details['item_qty'];
                                                            @endphp
                                                        @endif
                                                        <div class="dis-amt">Sub Total: 
                                                            <span>${{isset($subtotal)?num_format($subtotal):''}}
                                                             </span>
                                                        </div>

                                                        @if(isset($details['product_dis_min_amt']) && $details['product_dis_min_amt'] != 0)
                                                             
                                                            <div class="dis-amt">Min amount to get discount on the product: 
                                                                <span>
                                                                    ${{isset($details['product_dis_min_amt'])?num_format($details['product_dis_min_amt']):''}}
                                                                </span>
                                                            </div>
                                                        @endif
                                                     
                                                        @if($details['prodduct_dis_type']==1)
                                                         
                                                            @if(isset($details['product_dis amount']) && $details['product_dis amount'] != 0)
                                                                <div class="dis-amt">Product Discount({{isset($details['product_discount_value'])?num_format($details['product_discount_value']).'%':''}}): 
                                                                    <span>${{isset($details['product_dis amount'])?num_format($details['product_dis amount']):0.00}}</span>
                                                                </div>
                                                             
                                                            @endif
                                                        @endif

                                                        @if($details['prodduct_dis_type']==2)  
                                                           
                                                            <div class="dis-amt">
                                                                Product Discount({{isset($details['product_discount_value'])?'$'.num_format($details['product_discount_value']):'0'}}):<span>${{isset($details['product_dis amount'])?num_format($details['product_dis amount']):0.00}} </span>
                                                            </div>
                                                        @endif

                                                        @php
                                                            
                                                            $product_discount += $details['product_dis amount'];
                                                            $wholesale_subtotal -=$details['product_dis amount'];
                                                            $none = 'display:block';

                                                            if(isset($details['shipping_type']) && $details['shipping_type'] == 2)
                                                            {
                                                               $ship_dis_type = '('.num_format($details['off_type_amount']).'%)';
                                                            }
                                                            elseif(isset($details['shipping_type']) && $details['shipping_type'] == 3)
                                                            {
                                                                $ship_dis_type = '($'.num_format($details['off_type_amount']).')';
                                                            }
                                                            else
                                                            {
                                                                $ship_dis_type = '';
                                                            }


                                                        @endphp
                                                        
                                                       
                                                        @if($details['shipping_type']==2)
                                                            
                                                             @if(isset($details['minimum_amount_off']) && $details['minimum_amount_off'] != '')
                                                                <div class="dis-amt">
                                                                    Min Order Amount to get shipping discount: <span>${{$details['minimum_amount_off']}}</span>
                                                                </div>
                                                            @endif
                                                            
                                                            @if(isset($details['shipping_charges']) && $details['shipping_charges'] != 0)
                                                                
                                {{-- ******* --}}               @php  
                                                                      $shipping_charges = isset($details['shipping_charges'])?num_format($details['shipping_charges']):0.00;
                                                                      $shipping_dis = isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00;

                                                                    $ship_charges =  $shipping_charges+$shipping_dis;
                                                                @endphp

                                                                <div class="dis-amt">
                                                                    Shipping Charges: <span>${{$shipping_charges or 0.00}}</span>
                                                                </div>
                                                            @endif

                                                            @if(isset($details['shipping_discount']) && $details['shipping_discount'] != 0)
                                                                <div class="dis-amt">

                                                                    Shipping Discount {{$ship_dis_type or ''}} :<span>${{isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00}}</span>
                                                                </div>
                                                            @endif
                                                       
                                                            @php
                                                            

                                                                /*if(isset($promo_shipping_charges) && $promo_shipping_charges == 0)
                                                                {                                                                   
                                                                    $details['shipping_charges'] = 0;
                                                                    $details['shipping_discount'] = 0;
                                                                }*/
                                                                $shipping_charges += $details['shipping_charges'];
                                                                $shipping_discount += $details['shipping_discount'];
                                                                $wholesale_subtotal += $details['shipping_charges'];

                                                            @endphp
                                                        
                                                        @elseif($details['shipping_type']==3)
                                                            
                                                          
                                                            @if(isset($details['minimum_amount_off']) && $details['minimum_amount_off'] != 0)
                                                                <div class="dis-amt">
                                                                    Min Order Amount to get shipping discount : <span>${{$details['minimum_amount_off']}}</span>
                                                                </div>
                                                            @endif 

                                                            @php  
                                                                $shipping_charges = isset($details['shipping_charges'])?num_format($details['shipping_charges']):0.00;
                                                                $shipping_dis = isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00;

                                                                $ship_charges =  $shipping_charges+$shipping_dis;
                                                            @endphp
                                                           
{{-- *********** --}}    
                                                            @if(isset($details['shipping_charges']) && $details['shipping_charges'] != 0)
                                                                <div class="dis-amt">
                                                                    Shipping Charge: <span>${{$shipping_charges or 0.00}}</span>
                                                                </div>
                                                            @endif
                                                          

                                                            @if(isset($details['shipping_discount']) && $details['shipping_discount'] != 0)
                                                                <div class="dis-amt">
                                                                Shipping Discount {{$ship_dis_type or ''}} : <span>${{isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00}}</span>
                                                            </div>
                                                            @endif
                                                            
                                                            
                                                            @php
                                                                /*if(isset($promo_shipping_charges) && $promo_shipping_charges == 0)
                                                                {
                                                                    $details['shipping_charges'] = 0;
                                                                    $details['shipping_discount'] = 0;
                                                                }*/
                                                                $shipping_charges += $details['shipping_charges'];
                                                                $shipping_discount += $details['shipping_discount'];
                                                                $wholesale_subtotal += $details['shipping_charges'];
                                                            @endphp
                                                        
                                                        @elseif($details['shipping_type']==1)
                                                            
                                                            @if(isset($details['minimum_amount_off']) && $details['minimum_amount_off'] != 0)
                                                                <div class="dis-amt">
                                                                    Min Order Amount to get free shipping:<span>${{$details['minimum_amount_off']}}</span>
                                                                </div>
                                                            @endif

                                                            @php  
                                                                $shipping_charges = isset($details['shipping_charges'])?num_format($details['shipping_charges']):0.00;
                                                                $shipping_dis = isset($details['shipping_discount'])?num_format($details['shipping_discount']):0.00;

                                                                $ship_charges =  $shipping_charges+$shipping_dis;
                                                            @endphp
                                                           

{{-- ********* --}}
                                                            @if(isset($details['shipping_charges']) && $details['shipping_charges'] != 0)
                                                                <div class="dis-amt">
                                                                    Shipping Charge: <span>${{$shipping_charges or 0.00}}</span>
                                                                </div>
                                                            @endif

                                                           
                                                            @php
                                                              
                                                                /*if(isset($promo_shipping_charges) && $promo_shipping_charges == 0)
                                                                {
                                                                    $details['shipping_charges'] = 0;
                                                                    $details['shipping_discount'] = 0;
                                                                }*/
                                                                

                                                                $shipping_charges += $details['shipping_charges'];
                                                                $shipping_discount += $details['shipping_discount'];
                                                                $wholesale_subtotal += $details['shipping_charges'];
                                                            @endphp

                                                            
                                                        @endif

                                                         
                                                        @php



                                                            $details['product_dis amount'] = isset($details['product_dis amount']) ? $details['product_dis amount']: 0;
                                                            $details['shipping_discount'] = isset($details['shipping_discount']) ? $details['shipping_discount']: 0;
                                                            $details['shipping_charges'] = isset($details['shipping_charges']) ? $details['shipping_charges']: 0;

                                                        if($login_user->inRole('customer'))
                                                        { 
                                                            if(is_numeric($details['product_dis amount']) &&
                                                                is_numeric($details['shipping_discount']) &&
                                                                is_numeric($details['total_price']) &&
                                                                is_numeric($details['shipping_charges']))
                                                            { 
                                                                
                                                                $product_wise_total_discount = num_format($details['product_dis amount'])+num_format($details['shipping_discount']);


                                                                /*  $product_wise_total = (num_format($details['total_price']) + num_format($details['shipping_charges']))-$product_wise_total_discount+num_format($details['shipping_discount']);*/

                                                                $product_wise_total = (num_format($details['total_price']) + num_format($details['shipping_charges']))-$product_wise_total_discount;                                                   
                                                           
                                                            }
                                                            else
                                                            {

                                                                $product_wise_total = isset($details['total_price'])?$details['total_price']:'';
                                                               
                                                            }
                                                        }
                                                        else
                                                        {
                                                            if(is_numeric($details['product_dis amount']) &&
                                                                is_numeric($details['shipping_discount']) &&
                                                                is_numeric($details['total_wholesale_price']) &&
                                                                is_numeric($details['shipping_charges']))
                                                            { 
                                                                
                                                                $product_wise_total_discount = num_format($details['product_dis amount'])+num_format($details['shipping_discount']);


                                                             /*   $product_wise_total = (num_format($details['total_wholesale_price']) + num_format($details['shipping_charges']))-$product_wise_total_discount+num_format($details['shipping_discount']);*/



                                                             $product_wise_total = (num_format($details['total_wholesale_price']) + num_format($details['shipping_charges']))-$product_wise_total_discount;                                                   
                                                           
                                                            }
                                                            else
                                                            {

                                                                $product_wise_total = isset($details['total_wholesale_price'])?$details['total_wholesale_price']:'';
                                                               
                                                            }

                                                        }

                                              
                                                        @endphp 
                                                       
                                                        @php $login_user = Sentinel::check(); @endphp 

                                                        @if($login_user==true && $login_user->inRole('retailer'))
                                                           
                                                            <div class="price-bnd-dtls">
                                                                <span>Total Order Amount:$</span>
                                                                <span id="product_wholesale_total{{$details['sku_no']}}" data-maker-id="{{$key}}" data-brand-id="{{ isset($product_details['maker_details']['id'])?$product_details['maker_details']['id']:'' }}">
                                                                    <b>{{ isset($product_wise_total)?num_format($product_wise_total) : '' }}</b>
                                                                </span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                           
                                                        @else 

                                                            <div class="price-bnd-dtls"><span>Total Order Amount:$</span><span id="product_wholesale_total{{$details['sku_no']}}" data-maker-id="{{$key}}" data-brand-id="{{ isset($product_details['maker_details']['id'])?$product_details['maker_details']['id']:'' }}"><b>{{ isset($product_wise_total)?num_format($product_wise_total) : '' }}</b></span></div>
                                                            <div class="clearfix"></div>
                                                            
                                                        @endif
                                                        @php
                                                          if($login_user == true && $login_user->inRole('customer'))
                                                          {
                                                            $qty_limit = 10000;                                                          
                                                          }
                                                          else
                                                          {
                                                            $qty_limit = 10000;   
                                                          }
                                                          @endphp
                                                        <div class="main-qnt-bottom">
                                                            <div class="qty-shop-bg">Qty : </div>

                                                            <select name="pro_qty" class="pro_qty" data-sku-no="{{base64_encode($details['sku_no'])}}" data-parsley-max = "{{$qty_limit}}" data-pro-id="{{base64_encode($details['product_id'])}}" @if(isset($product_available_quantity) && $product_available_quantity==0) disabled="true" @endif>


                                                            @for($i=1;$i<=10000;$i++)          
                                                               <option @if(isset($details['item_qty']) && $details['item_qty']==$i) selected="" @endif value="{{$i}}">{{$i}}</option>

                                                            @endfor
                                                            
                                                            </select>

                                                        </div>

                                                        <div class="clearfix"></div>
                                                        
                                                    </div>

                                                </div>
                                                <div class="clearfix"></div>
                                                <a href="{{url('/customer_my_bag/delete_product/'.base64_encode($details['sku_no']))}}" class="close-brnd" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');">
                                                    <img src="{{url('/')}}/assets/front/images/close-txt.png" alt="">
                                                </a>
                                            </div>
                                            @if(isset($product_available_quantity) && $product_available_quantity == 0)
                                            <span class="outofstock">Out of stock</span>
                                            @endif
                                        </div>
                                    @endforeach 
                                @else
                                    <div class="not-found-data whitebg-no"> No record found</div>
                                @endif
                            </div>                           
                        
                        @endforeach 


                        @if(isset($arr_final_data) && count($arr_final_data)>0 && $bag_count!=0)    

                        <div class="row">
                            <div class="col-md-12">                                
                                <a class="gt-button mrtp pull-right" id=empty_cart>Empty Cart</a>                                 
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        @else
                        <div class="not-found-data whitebg-no">Your shopping cart is empty.</div>
                        @endif
                       
                        @else
                        <div class="not-found-data whitebg-no">Your shopping cart is empty.</div>
                        @endif 



                       
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-4 rightbagsidebar">
                   
                    <div class="theiaStickySidebar">
                        
                        @if(isset($arr_final_data) && count($arr_final_data)>0 && $bag_count!=0)

                         <div class="bag-details">

                             <h2>Product Summary</h2>
                            <div class="innerbag-details">

                               <?php

                               $shipping_chargs = $wholesale_total = $total_ship_discount_value = $total_product_dis_value = $total_ship_discount_value_perc = $total_ship_discount_value_dollar = $total_product_dis_value_perc = $total_product_dis_value_dollar = 0;

                                $total_ship_percent = $total_ship_dollar = $prod_dis_value_dollar = $prod_dis_value_percent = $total_ship_percent = $total_ship_dollar =
                                    $prod_dis_value_dollar = $prod_dis_value_percent = $ship_dis_val = $ship_dis_val = $prod_dis_value = $prod_dis_value = '';

                               ?>
                                  @foreach($arr_final_data as $key => $product_details)
                                    

                                     @if(isset($product_details['product_details']) && count($product_details['product_details'])>0) 
                                       @foreach($product_details['product_details'] as $arr_product_data)
                                            
                                            @php
                                                /*Promotion calculation by maker*/
                                                if(is_array(Session::get('customer_promotion_data')) && Session::get('customer_promotion_data') != ""){

                                                    $session_promotion_data = Session::get('customer_promotion_data');

                                                    /*$promo_shipping_charges = isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges'])?$session_promotion_data[$key]['final_total'][$key]['shipping_charges']:1;*/

                                                }


                                                $product_shipping_charg = isset($arr_product_data['shipping_charges'])?num_format($arr_product_data['shipping_charges']):'0.00';


                                               /* if (isset($promo_shipping_charges) && $promo_shipping_charges == 0) {

                                                    $shipping_charg = 0;
                                                    $ship_discount  = 0; 
                                                    $arr_product_data['shipping_discount'] = 0;
                                                }
                                                else{*/

                                                    $ship_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):'0.00';
                                                    //$shipping_charg = $product_shipping_charg + $ship_discount;
                                                    $shipping_charg = $product_shipping_charg;

                                                // } 


                                                $prod_discount = isset($arr_product_data['product_dis amount'])?num_format($arr_product_data['product_dis amount']):'0.00';

                                                $item_qty = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:0;
                                                if($login_user->inRole('customer'))
                                                {
                                                    $unit_price = isset($arr_product_data['unit_retail_price'])?$arr_product_data['unit_retail_price']:0;
                                                }
                                                else
                                                {
                                                    $unit_price = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:0;
                                                }
                                                $sub_total = $item_qty*$unit_price;

                                                $total_amt = ($sub_total)+($shipping_charg)-($prod_discount)-($ship_discount);

                                                $shipping_chargs += $shipping_charg;

                                            @endphp

                                             <div class="clone-bag-detials">
                                               
                                                <div class="bag-detailstitle">Product Name : {{isset($arr_product_data['product_name'])?$arr_product_data['product_name']:'N/A'}}</div>

                                                <div class="baglist-sub">SKU No : <span>{{isset($arr_product_data['sku_no'])?$arr_product_data['sku_no']:'-'}}</span></div>
                                                <div class="baglist-sub">Sub


                                                 Total : <span>${{isset($sub_total)?num_format($sub_total):0.00}}</span></div>

                                                @php $none = "display:block"; @endphp
                                                @if($shipping_charg==0)
                                                    @php $none = "display:none"; @endphp
                                                @endif 
                                                <span style="{{$none}}"><div class="baglist-sub">Shipping Charges : <span> 

                                                    @if($shipping_charg > 0) +@endif${{isset($shipping_charg)?num_format($shipping_charg):0.00}}</div></span>



                                                @php $none = "display:block"; @endphp
                                                @if($ship_discount==0)
                                                    @php $none = "display:none"; @endphp

                                                @endif 

                                                @php

                                               /* if($ship_discount!=0)
                                                {
                                                    $total_ship_discount_value += isset($arr_product_data['off_type_amount']) && $arr_product_data['off_type_amount']!="" ?$arr_product_data['off_type_amount']:0.00;
                                                } 

                                                if($prod_discount!=0)
                                                {
                                                    $total_product_dis_value +=isset($arr_product_data['product_discount_value']) &&$arr_product_data['product_discount_value']!="" ?$arr_product_data['product_discount_value']:0.00; 
                                                }
                                                  
*/

                                                   /*------------total discounts ------------------------*/
                                                

                                                if(($ship_discount==0 || $ship_discount!=0) && ($arr_product_data['off_type_amount']!='?' && $arr_product_data['off_type_amount']!=""))
                                                {
                                                    if($arr_product_data['shipping_type'] == 2)
                                                    {
                                                        $total_ship_discount_value_perc += isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:0.00;

                                                        $total_ship_percent = '('.$total_ship_discount_value_perc.'%)';
                                                    }
                                                    elseif ($arr_product_data['shipping_type'] == 3)
                                                    {
                                                        $total_ship_discount_value_dollar += isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:0.00;

                                                        $total_ship_dollar = '($'.$total_ship_discount_value_dollar.')';
                                                    }
                                                    else
                                                    {
                                                         $total_ship_dollar = $total_ship_percent = '';
                                                    }
                                                    
                                                }
                                               
                                                 

                                                if(($prod_discount==0 || $prod_discount!=0) && ($arr_product_data['product_discount_value']!=0))
                                                {
                                                    if($arr_product_data['prodduct_dis_type'] == 2)
                                                    {
                                                      $total_product_dis_value_perc += isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:0.00;

                                                      $prod_dis_value_dollar = '($'.$total_product_dis_value_perc.')';
                                                    }
                                                    elseif ($arr_product_data['prodduct_dis_type'] == 1) 
                                                    {
                                                      $total_product_dis_value_dollar +=isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:0.00;

                                                      $prod_dis_value_percent = '('.$total_product_dis_value_dollar.'%)';
                                                    }
                                                    else
                                                    {
                                                        $prod_dis_value_percent = $prod_dis_value_dollar = '';
                                                    }
                                                  
                                                }
                                                
                                              /*--------------------------------------------------------*/

                                                if(isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 2)
                                                {
                                                   $ship_dis_val = '('.$arr_product_data['off_type_amount'].'%)';
                                                }
                                                elseif(isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 3)
                                                {
                                                    $ship_dis_val = '($'.$arr_product_data['off_type_amount'].')';
                                                }
                                                else
                                                {
                                                    $ship_dis_val = '';
                                                }
                                                  
    
                                                @endphp


                                                <span style="{{$none}}"><div class="baglist-sub">Shipping Discount {{$ship_dis_val or ''}} : <span>@if($ship_discount>0)-@endif${{isset($ship_discount)?num_format($ship_discount):0.00}}</div></span>
                                            

                                                @php $none = "display:block"; @endphp
                                                @if($prod_discount==0)
                                                    @php $none = "display:none"; @endphp
                                                @endif 

                                                 @php
                                                    $prod_dis_value = '';
                                                    if(isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 1)
                                                    {
                                                       $prod_dis_value = '('.$arr_product_data['product_discount_value'].'%)';
                                                    }
                                                    elseif(isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 2)
                                                    {
                                                       $prod_dis_value = '($'.$arr_product_data['product_discount_value'].')';
                                                    }
                                                    else
                                                    {
                                                        $prod_dis_value = '';
                                                    }
                                                @endphp


                                                <span style ="{{$none}}"><div class="baglist-sub">Product Discount {{$prod_dis_value or ''}} : <span>@if($prod_discount>0)-@endif${{isset($prod_discount)?num_format($prod_discount):0.00}}</div></span>

                                                <strong><div class="baglist-sub">Total : <span>${{isset($total_amt)?num_format($total_amt):0.00}}</span></div></strong>

                                                <?php
                                                $wholesale_total += $sub_total;
                                                $retail_subtotal += $total_amt;
                                                ?>

                                            </div>
                                       @endforeach
                                     @endif
                                  @endforeach
                            </div>
      
                        </div>
                    @endif

                                                                       
                        <div class="price-det-block">
                            <h2>Order Summary</h2>
                            <div class="order-sum">
                            @if($login_user==true && $login_user->inRole('customer'))
                                        <h5>Subtotal 
                                  <span id="retail_subtotal" class = "subtotal">{{ isset($wholesale_total)?num_format($wholesale_total) : 00 }}</span>
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
                                     <span style="{{$none}}"><div class="mybag-list-left">Product Discount {{-- {{isset($prod_dis_value_percent)?$prod_dis_value_percent:''}} {{isset($prod_dis_value_dollar)?$prod_dis_value_dollar:''}} --}} </div></span>
                                     <span style="{{$none}}"><div class="mybag-list-right"></div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($product_discount>0)-@endif${{isset($product_discount)?num_format($product_discount):0.00}}</div></span>

                                    <input type="hidden" name="product_dis_amt" id="product_dis_amt" value="{{isset($product_discount)?num_format($product_discount):0.00}}">

                                    <div class="clearfix"></div>
                                </div>
                                <div class="mybag-list">
                                    
                                      @php 

                                        if(isset($arr_final_data) && count($arr_final_data)>0)
                                        {
                                          $none ="display:block";                                                                                                                                                                                                                                                           
                                        }
                                        else
                                        {
                                            $none ="display:none";
                                        }

                                    @endphp


                                    @if(isset($shipping_chargs) && $shipping_chargs == 0)
                                     @php $none ="display:none"; @endphp
                                    @if($shipping_charges==0 )
                                      
                                       @php $none ="display:none"; @endphp
                                       
                                    @endif
                                    @endif
                                    <span style="{{$none}}"><div class="mybag-list-left">Shipping Charges</div></span>

                                    @if(Request::segment(2) == "")
                                        <span style="{{$none}}"><div class="mybag-list-right">
                                            <?php
                                                if(isset($shipping_chargs) && $shipping_chargs > 0)
                                                {
                                                    echo '+$'.num_format($shipping_chargs);
                                                }
                                                ?>

                                        </div></span>
                                        <input type="hidden" name="ship_charges" id="ship_charges" value="{{isset($shipping_chargs)?num_format($shipping_chargs):0.00}}">
                                    @else
                                        <span style="{{$none}}"><div class="mybag-list-right"
                                            >
                                            <?php 
                                            if($shipping_charges>0)
                                            {
                                                echo '+$'.num_format($shipping_charges+$shipping_discount);
                                            }
                                            ?>
                                            </div></span>
                                        <input type="hidden" name="ship_charges" id="ship_charges" value="{{isset($shipping_charges)?num_format($shipping_charges+$shipping_discount):0.00}}">
                                    @endif    

                                    
                                    {{-- @if($shipping_charges==0)                                           
                                        {{Session::put('promo_shipping_charges',$shipping_charges)}}
                                    @else    
                                        {{Session::put('promo_shipping_charges',$shipping_charges+$shipping_discount)}}
                                    @endif --}}
                                    <div class="clearfix"></div>
                                </div>
                                <div class="mybag-list">
                                    @php $none ="display:block"; @endphp
                                     @if($shipping_discount==0)
                                       @php $none ="display:none"; @endphp
                                    @endif
                                    <span style="{{$none}}"><div class="mybag-list-left"> Discount On Shipping {{-- {{isset($total_ship_percent)?$total_ship_percent:''}}{{isset($total_ship_dollar)?$total_ship_dollar:''}} --}} </div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($shipping_discount>0)-@endif${{isset($shipping_discount)?num_format($shipping_discount):0.00}}</span>

                                     <input type="hidden" name="ship_dis_amount" id="ship_dis_amount" value="{{isset($shipping_discount)?num_format($shipping_discount):0.00}}">
                                        
                                    {{Session::put('shipping_discount',$shipping_discount)}}
                                    {{Session::put('product_discount',$product_discount)}}
                                </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="mybag-list">
                                    @php $none ="display:block"; @endphp
                                     @if($total_promotion_discount_amt ==0)
                                       @php $none ="display:none"; @endphp
                                    @endif
                                    <span style="{{$none}}"><div class="mybag-list-left"> Promotion Discount</div></span>
                                    <span style="{{$none}}"><div class="mybag-list-right">@if($total_promotion_discount_amt >0)-@endif${{isset($total_promotion_discount_amt )?num_format($total_promotion_discount_amt ):0.00}}</span>

                                    {{Session::put('promotion_discount_amt',$total_promotion_discount_amt)}}
                                   
                                </div>
                                    <div class="clearfix"></div>
                                </div>
                                <hr>
                                <h5>Order Total 

                                    @php
                                    if($login_user==true &&  $login_user->inRole('retailer'))
                                    {

                                        $total_amount = $subtotal - $total_promotion_discount_amt;
                                    }
                                    else{
                                         $total_amount = $retail_subtotal - $total_promotion_discount_amt;
                                    }
                                    @endphp

                                    @if(is_array(Session::get('customer_promotion_data')) && Session::get('customer_promotion_data') != "")
                                        
                                            {{Session::put('total_order_amout',$total_amount)}}
                                    @endif

                                      @if($login_user==true &&  $login_user->inRole('customer'))
                                        

                                        <span id="wholesale_total">{{ isset($total_amount)?num_format($total_amount) : 00 }}</span> 

                                        <input type="hidden" name="total_amt" id="total_amt" value="{{$total_amount or 0.00}}">

                                        
                                        <span>$</span>
                                      @else
                                        <span id="cart_total">{{ isset($total_amount)?num_format($total_amount) : 00 }}</span>
                                        <span>$</span>
                                      @endif  
                               </h5> 
                                <div class="clearfix"></div>
                            </div>

                        </div>


                        <form id="frm-apply-quotes">
                            {{ csrf_field() }}
                           
                            @if(isset($product_data) && sizeof($product_data)>0)
                              <input type="hidden" name="product_data" value="{{$product_data}}"> 
                              <input type="hidden" name="bag_id" value="{{ $bag_id or 0 }}">
                            @endif
                            @if(isset($arr_final_data) && count($arr_final_data)>0  && $bag_count!=0)
                            <div class="button-login-pb">
                                
                                @if(Sentinel::check() == true)
                                <a href="javascript:void(0)" onclick="save_bag();" class="gt-button apply-quotes">Buy Now</a> 
                                @else
                                <a href="javascript:void(0)" onclick="save_bag();" class="gt-button apply-quotes">Buy Now</a> 
                                @endif
                                
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
    </div>



    <script type="text/javascript" src="{{url('/')}}/assets/front/js/theia-sticky-sidebar.js"></script>
    <!-- <script type="text/javascript">
        $(document).ready(function() {
            $('.leftbagsidebar, .rightbagsidebar')
                .theiaStickySidebar({
                    additionalMarginTop: 130
                });
        });
    </script> -->
    <script type="text/javascript">
        $('.pro_qty').on('change', function() 
        {
            var qty = this.value;
            var pro_id = $(this).attr("data-pro-id");
            var sku_no = $(this).attr("data-sku-no");

            var url = '{{url('/')}}/customer_my_bag/update_qty';

            
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
                   
                },
                success: function(response) {
                    hideProcessingOverlay();
                    if (response.status == "SUCCESS") {
                        // console.log(response);
                        var total_price = response.total_price.toFixed(2);
                        
                        var total_wholesale_price = response.total_wholesale_price.toFixed(2);
                        var subtotal = response.subtotal.toFixed(2);
                        var wholesale_subtotal = response.wholesale_subtotal.toFixed(2);

                        $("#product_total_" + atob(sku_no)).text(total_price);
                        $("#subtotal").text(subtotal);
                        $("#product_wholesale_total" + atob(sku_no)).text(total_wholesale_price);
                        $("#retail_subtotal").text(wholesale_subtotal);

                        $("#cart_total").text(subtotal);
                        $("#wholesale_total").text(subtotal);

                        window.location.reload();
                    }
                    else if(response.status == "warning")
                    {
                        swal({ 
                                title: 'Warning',
                                text: response.description,
                                type: 'warning'
                            },
                            function(){
                                window.location.reload();
                            });

                        return;

                    }
                    else
                    {
                        swal('Error',"Something went wrong,please try again.",'error');
                        return
                    }

                }
            })

        });

        $('#empty_cart').on('click', function() {

            var url = '{{url('/')}}/customer_my_bag/empty_cart';

            swal({
                    title: "Need Confirmation",
                    text: "Are you sure? Do you want to delete all products from cart.",
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
                        beforeSend: function() 
                              {
                                 showProcessingOverlay();                 
                              },

                        success: function(response) {
                            hideProcessingOverlay();
                            if (response.status == "success") {

                                swal({ 
                                        title: 'Success',
                                        text: "Your cart is empty.",
                                        type: 'success'
                                    },
                                      function(){
                                        window.location = response.next_url;
                                    });

                              
                            } else {
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

        var _tmpTotal = parseFloat($(_elem).text());

        var _tmpBrandId = $(_elem).attr("data-brand-id");

        if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
            _brandIdWiseSubTotal[_tmpBrandId] = 0;
        } 

        _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
    });

    
        /*_minBrandMsg+='<ul>';  

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
                    text: "Please make sure that cart total for following brands satisfies with minimum amount<br>"+_minBrandMsg,
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
            return; 
        }*/


    if($('#frm-apply-quotes').parsley().validate()==false) return;

        var bag_data = $("#frm-apply-quotes").serialize();
          
        var bag_form_data = $("#frm-apply-quotes").val();

        $.ajax({
                url: SITE_URL + '/customer_my_bag/save_bag',
                data: bag_data,
                method: 'POST',
                beforeSend: function() {
                     showProcessingOverlay();
                    $('.apply-quotes').prop('disabled', true);
                    $('.apply-quotes').html('Please wait <i class="fa fa-spinner fa-pulse fa-fw"></i>');
                },
                success: function(response) {
                    hideProcessingOverlay();
                    $('.apply-quotes').prop('disabled', false);
                    $('.apply-quotes').html('Buy Now');

                    if (typeof response == 'object') {
                        if (response.status == "SUCCESS") {
                            window.location.href = response.next_url;
                        } else if(response.status == "Out Of Stock") {
                            
                           swal({
                    title: "Products out of stock",
                    text:  "Please remove out of stock products from cart to proceed.",
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                    });
                      return; 
                        }
                    }
                }
            });
        }




    function tempSaveBag(ref) {

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
                    text: "Please make sure cart total for following brands satisfies with minimum amount<br>"+_minBrandMsg,
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
            return; 
        }


           var bag_data = $("#frm-apply-quotes").serialize();
          
           var bag_form_data = $("#frm-apply-quotes").val();

            $.ajax({
                url: SITE_URL + '/customer_my_bag/temp_save_bag',
                data: bag_data,
                method: 'POST',
                beforeSend: function() {
                    // showProcessingOverlay();
                    $('.apply-quotes').prop('disabled', true);
                    $('.apply-quotes').html('Please wait <i class="fa fa-spinner fa-pulse fa-fw"></i>');
                },
                success: function(response) {
                    // hideProcessingOverlay();
                    $('.apply-quotes').prop('disabled', false);
                    $('.apply-quotes').html('Apply to quotes');

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
            let url = SITE_URL+'/customer/set_session';
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
                    $('#buy_now').html('Please wait...');
                },
                success: function(response) {
                    hideProcessingOverlay();
                    $('#buy_now').prop('disabled', false);
                    $('#buy_now').html('Pay');

                    if (typeof response == 'object') 
                    {
                        if (response.status == "SUCCESS") 
                        {  
                            location.href = SITE_URL+'/customer/checkout';
                        }
                    }
                }
            });   
        }



/*apply promo code*/

function applyPromoCode()
{
    /*var _arrProductRef = [];
    var _brandIdWiseSubTotal = {};

    if($('[id^=product_total_]').length > 0)
    {
        _arrProductRef = $('[id^=product_total_]');
    }
    else if($('[id^=product_wholesale_total]').length > 0)
    {
        _arrProductRef = $('[id^=product_wholesale_total]');   
    }
    $.each(_arrProductRef,(_index,_elem) => {

            var _tmpTotal = parseFloat($(_elem).text());

            var _tmpBrandId = $(_elem).attr("data-maker-id");

            if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
                _brandIdWiseSubTotal[_tmpBrandId] = 0;
            } 

            _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
    });*/


    // total_amt = _brandIdWiseSubTotal[maker_id];

    // var promo_code            = $('#promo_code_'+maker_id).val();
    // var total_amt             = _brandIdWiseSubTotal[maker_id];
    var tot_product_dis_amt   = $('#product_dis_amt').val();
    var tot_ship_charges      = $('#ship_charges').val();
    var tot_shipping_discount = $('#ship_dis_amount').val();
    var subtotal_amt          = $('#retail_subtotal').text().trim();
    
    var promo_code            = $('#promo_code').val();
  
    let url = SITE_URL+'/customer_my_bag/apply_promo_code';

    $.ajax({
        url:url,
        data:{promo_code:promo_code,subtotal_amt:subtotal_amt,tot_product_dis_amt:tot_product_dis_amt,tot_ship_charges:tot_ship_charges,tot_shipping_discount:tot_shipping_discount,_token:"{{ csrf_token() }}"},
        method:'POST',
        beforeSend: function() 
        {
            showProcessingOverlay();                 
        },
        success: function(response){
            
            hideProcessingOverlay();
        
            if (response.status == 'error') 
            {

                $('#promo_error').text(response.description);
            }

            if (response.status == 'success') 
            {
             
                location.href = SITE_URL+'/customer_my_bag';
            }
        }
        
    });  

}

function clearPromoCode() 
{
    let url = SITE_URL+'/customer_my_bag/clear_promo_code';

    swal({
            title: "Need Confirmation",
            text: "Are you sure? Do you want to remove applied promo code.",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "OK",
            closeOnConfirm: false
        },
        function() 
        {
          
            $.ajax({
                url:url,
                data:{},
                method:'get',
                beforeSend: function() 
                {
                    showProcessingOverlay();                 
                },
                success: function(response){
                    
                    hideProcessingOverlay();
                
                    if (response.status == 'error') 
                    {

                        swal({
                                title: "Error",
                                text: response.description,
                                type: response.status,
                                confirmButtonText: "OK",
                                closeOnConfirm: false
                            },
                        function(isConfirm, tmp) 
                        {
                            if (isConfirm == true) 
                            {
                                location.href = SITE_URL+'/customer_my_bag';
                            }
                        });
                       
                    }

                    if (response.status == 'success') 
                    {
                        swal({ 
                            title: 'Success',
                            text: response.description,
                            type: 'success'
                        },
                          function(){
                            location.href = SITE_URL+'/customer_my_bag';
                        });
                       
                    }
              
                }
                
            })

        });
}

    
</script>

@stop