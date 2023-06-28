@extends('retailer.layout.master') 
@section('main_content')

<link href="{{url('/')}}/assets/css/gallery.css" rel="stylesheet" type="text/css" />
 <script type="text/javascript" src="{{url('/')}}/assets/js/resizesensor.js"></script>
  <script type="text/javascript" src="{{url('/')}}/assets/js/theia-sticky-sidebar.js"></script>
<style type="text/css">
  .btn-blk{
    color: #666 !important;
  }
  .btn-blk:hover{color: #fff !important;}
  .btns-right {
    float: right;
}
.price-bnd-dtls{     font-family: 'open_sanssemibold';}
.price-bnd-dtls span{
      font-family: 'open_sansregular';
}
.save-it-main {
    margin-bottom: 20px;
}
.custom-control-input:focus~.custom-control-indicator {
    -webkit-box-shadow: 0 0 0 1px #fff, 0 0 0 3px #666;
    box-shadow: 0 0 0 1px #fff, 0 0 0 3px #666;
}
.custom-control-input:checked~.custom-control-indicator {
    color: #fff;
    background-color: #666;
}
.ctm-txs {
    float: left;
    margin-top: 10px;
}
/*.ctomr-shipping-details{
  height: 60px;
}*/
.ctomr-shipping-details {margin-top:0px;}
.space-retailer{margin-top:0px;}

.save-it-later-btn.clearinup{
    position: relative; padding-right: 76px; max-width: 100%;
}
.save-it-later-btn.clearinup input {
    height: 35px; width: 100%;
    padding: 5px 10px; border: 1px solid #ccc;
}
.save-it-later-btn.clearinup button{
    height: 35px;
    border: none; position: absolute;right: 0px; top: 0px;
    padding: 5px 10px;
    background-color: #505050;
    color: #fff;
}
.save-it-later-btn.clearinup button.cpn-buttons.cpn-clrs-btn{
        background-color: #333;
    width: 81px;
}
.cnt-bg-fnt.freeshipping {
    font-size: 17px;
    font-weight: 600;
    color: #333;
}
.promotiondiscount {
    font-size: 17px;
    margin-bottom: 20px;
    color: #333;
}
.promotiondiscount span{
    font-weight: 600;
    font-size: 19px; display: inline-block;
}

.postion-re.spacetopmargn {
    margin-top:0px;
} 

.err_zip_code{
    margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }
  /* #reason_error{
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  } */
</style>
<div id="page-wrapper">
    <div class="container-fluid">

    
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">{{$module_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url(config('app.project.retailer_panel_slug').'/my_orders/order_from_representative') }}">Orders by Reps / Sales</a></li>
                    <li class="active">Order Summary</li>
                </ol>
            </div>
         
        </div>
        <!-- .row -->
        <div class="row">
            <div class="col-lg-12">
            @include('admin.layout._operation_status')
                <div class="white-box">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="title-billing-address">
                                Order Summary
                                <br> 

                                <div class="order-numbr-summry">Order No : <strong>{{$arr_data['order_no'] or ''}}</strong></div>
                              </div>

                             @php
                              $maker_details = [];
                              $maker_min_order_amount = $total_product_discount_value = $total_shipping_discount_value = 0;
                              $maker_details = get_maker_shop_setting(isset($enc_maker_id)?base64_decode($enc_maker_id):0);


                              if (isset($maker_details) && count($maker_details)>0) 
                              {
                                 $maker_min_order_amount = $maker_details['first_order_minimum'];
                              }

                            @endphp

                            <div class="bag-clone-group">
                              <div class="jst-mybg">
                                <div class="row">
                                  <div class="col-md-6">
                                    <div class="brand-name-title spc-top" data-brand="{{isset($enc_maker_id)?base64_decode($enc_maker_id):0}}" data-brand-min="{{num_format($maker_min_order_amount)}}">{{get_maker_company_name(isset($enc_maker_id)?base64_decode($enc_maker_id):0)}}</div>
                                    @if(isset($maker_min_order_amount) && $maker_min_order_amount > 0)
                                        <div class="save-it-main">{{-- <b>Minimum Order Amount: ${{num_format($maker_min_order_amount)}}</b> --}}</div>

                                        
                                    @else
                                        <div class="save-it-main">No Minimum</div>
                                    @endif   
                                  
                                  </div>
                                </div>                                
                              </div>
                            </div>
                            @php
                                /* Promotion Data in Session */
                                $session_promotion_data = [];
                                $total_promotion_discount_amt = 0;
                                $promo_shipping_charges = 1;

                                $total_ship_discount_value_perc = $total_ship_discount_value_dollar = 
                                $total_product_dis_value_perc = $total_product_dis_value_dollar = 0;

                                $total_ship_percent = $total_ship_dollar = $prod_dis_value_dollar = 
                                $prod_dis_value_percent = $total_ship_percent = $total_ship_dollar =
                                $prod_dis_value_dollar = $prod_dis_value_percent = $ship_dis_val = 
                                $ship_dis_val = $prod_dis_value = $prod_dis_value = '';


                                $session_promotion_data = Session::get('promotion_data');


                                if(is_array(Session::get('promotion_data')) && Session::get('promotion_data') != ""){
                             
                                    $session_promotion_data = Session::get('promotion_data');
                                    
                                    $promo_shipping_charges = isset($session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['shipping_charges'])?$session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['shipping_charges']:1;

                                    $total_wholesale_price = isset($session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['total_wholesale_price'])?$session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['total_wholesale_price']:0;

                                    $promotion_discount_amt = isset($session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['discount_amt'])?$session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['discount_amt']:0;

                                    $promotion_discount_percent = isset($session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['discount_percent'])?$session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['discount_percent']:0;

                                    $total_promotion_discount_amt += $promotion_discount_amt;


                                }

                            @endphp


                            <div class="row">
                                <div class="col-md-8 content rep_order_summary_left">
                                        <div class="theiaStickySidebar">
                                            <div class="main-order-brand-blue">
                           
                                  @if(isset($arr_data['maker_id']) && $arr_data['maker_id']!='')
                                        <div class="save-it-later-btn clearinup">
                                  
                                            @if(isset($arr_data['maker_id']) && isset($session_promotion_data[$arr_data['maker_id']]))

                                                <input type="text" placeholder="Enter Promo Code" name="promo_code" readonly="" value="{{ $session_promotion_data[$arr_data['maker_id']]['promo_code'] or ''}}" />
                                      
                                                <button type="button" class="cpn-buttons cpn-clrs-btn" id="btn_apply_code" name="btn_apply_code" onclick="clearPromoCode({{$arr_data['maker_id']}});"><i class="fa fa-times"> </i> Clear</button>
        
                                            @else
                                                <input type="text" placeholder="Enter Promo Code" id="promo_code_{{isset($arr_data['maker_id'])?$arr_data['maker_id']:''}}" name="promo_code" data-parsley-required="true"/>
                                          
                                                <button type="button" class="cpn-buttons" id="btn_apply_code" name="btn_apply_code" onclick="applyPromoCode({{isset($arr_data['maker_id'])?$arr_data['maker_id']:''}});"><i class="fa fa-check"> </i> Apply</button>
                                                <span class="red" id="{{isset($arr_data['maker_id'])?$arr_data['maker_id']:''}}_promo_error"></span>
                                         
                                            @endif 
                                        </div>
                                    @endif    

                                        <div class="newss-bag">
                                            @if($promo_shipping_charges == 0)
                                                <div class="cnt-bg-fnt freeshipping">Free Shipping</div>
                                            @endif

                                            @if(isset($arr_data['maker_id']) && isset($session_promotion_data[$arr_data['maker_id']]['final_total'][$arr_data['maker_id']]['total_wholesale_price']) && isset($total_wholesale_price))
                                                <div class="promotiondiscount">Promotion Discount({{$promotion_discount_percent}}%):<span> ${{num_format($promotion_discount_amt)}}</span> </div>
                                            @endif
                                        </div>

                                        

                                        @if(isset($arr_data['order_details']) && sizeof($arr_data['order_details'])>0)  

                                            @foreach($arr_data['order_details'] as $product)

                                                @if(isset($product['maker_id']) && $product['maker_id'] == base64_decode($enc_maker_id))
                                                    <div class="brand-dtls-nw save-itlater-bx retailer-order-summary-brand-dtls-nw">
                                                        <div class="img-brnd">
                                                            @php

                                                                $sku = isset($product['sku'])?$product['sku']:"";
                                                                $product_sku_image = get_sku_image($sku);
                                   
                                                            @endphp
                                                            <img src="{{ $product_sku_image or ''}}" alt="">
                                                        </div>


                                                        <div class="brnd-dtls-mn">
                                                            <div class="product-name-bnd">{{$product['product_details']['product_name'] or ''}}</div>
                                                           
                                                            <div class="inpt-selects">
                                                              <div class="price-bnd-dtls">Qty: 
                                                                <span class="bold-tx-ordr">{{$product['qty']}}</span>
                                                              </div>

                                                              <div data-brand="{{ isset($product['maker_id'])?$product['maker_id']:'' }}" data-brand-min="{{ $product['shop_settings']['first_order_minimum'] or '' }}" data-name="{{$product['maker_details']['company_name'] or ''}}"></div>

                                                              
                                                              <div class="price-bnd-dtls"> Price: 
                                                                <span >${{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : 0.00}}</span>
                                                              </div>
                                                              
                                                              <div class="price-bnd-dtls">Sub Total: 
                                                                <span  id="product_total_{{$product['sku']}}" data-brand-id="{{ isset($product['maker_id'])?$product['maker_id']:'' }}">${{isset($product['wholesale_price'])?num_format($product['wholesale_price']) : 0.00}}</span>
                                                              </div>
                                                                
                                                            
                                                               <div class="clearfix"></div>
                                                            </div>


                                               @php


                                                
                              $dis_type = $product['product_details']['prodduct_dis_type'];

                          
                              if($dis_type == 1)
                              {
                                $type = '('.$product['product_details']['product_discount'].'%)';
                              }
                              else if($dis_type == 2)
                              {
                                 $type = '($'.$product['product_details']['product_discount'].')';
                              }
                              else
                              {
                                $type = '';
                              }


                              $shipping_dis_type = $product['product_details']['shipping_type'];

                            
                              if($shipping_dis_type == 2)
                              {
                                $ship_type = '('.$product['product_details']['off_type_amount'].'%)';
                              }
                              else if($shipping_dis_type == 3)
                              {
                                $ship_type = '($'.$product['product_details']['off_type_amount'].')';
                              }
                              else
                              {
                                $ship_type = '';
                              }

                         /*   if(isset($product['product_details']['product_discount']) && $product['product_details']['product_discount']!=0)
                            {
                               $total_product_discount_value += isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:'';
                            }

                            if(isset($product['product_details']['off_type_amount']) && $product['product_details']['off_type_amount']!='?' && $product['product_details']['off_type_amount']!="")
                            {
                               $total_shipping_discount_value += isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';
                            }
                               */

                              /*---------------------------total discount ------------------------*/  

                            if(isset($product['product_details']['product_discount']) && $product['product_details']['product_discount']!=0)

                            {
                                 /*$total_product_discount_value += isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:'';
  */
                                if($product['product_details']['prodduct_dis_type'] == 2)
                                {
                                    $total_product_dis_value_perc += isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:0.00;

                                    $prod_dis_value_dollar = '($'.$total_product_dis_value_perc.')';
                                }
                                elseif ($product['product_details']['prodduct_dis_type'] == 1) 
                                {
                                    $total_product_dis_value_dollar +=isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:0.00;

                                    $prod_dis_value_percent = '('.$total_product_dis_value_dollar.'%)';
                                }
                                else
                                {
                                    $prod_dis_value_percent = $prod_dis_value_dollar = '';
                                }


                            }
                           /* else
                            {
                                $prod_dis_value_percent = $prod_dis_value_dollar = '';
                            }*/
                               

                            if(isset($product['product_details']['off_type_amount']) && 
                                    $product['product_details']['off_type_amount']!='?' && 
                                    $product['product_details']['off_type_amount']!="")
                            {
                                /*$total_shipping_discount_value += isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';*/

                                if($product['product_details']['shipping_type'] == 2)
                                {
                                    $total_ship_discount_value_perc += isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:0.00;

                                    $total_ship_percent = '('.$total_ship_discount_value_perc.'%)';
                                }
                                elseif ($product['product_details']['shipping_type'] == 3)
                                {
                                    $total_ship_discount_value_dollar += isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:0.00;

                                    $total_ship_dollar = '($'.$total_ship_discount_value_dollar.')';
                                }
                                else
                                {
                                    $total_ship_dollar = $total_ship_percent = '';
                                }

                            }
                            /*else
                            {
                                  $total_ship_dollar = $total_ship_percent = '';
                            } */  

                            /*-------------------------------------------------------------------------*/ 


                              

                              @endphp  


                                {{--  @if(isset($product['product_discount']) && $product['product_discount'] != 0) --}}

                                  @if(isset($product['product_details']['product_dis_min_amt']) && $product['product_details']['product_dis_min_amt']!=0)
                                       {{--  <div>Min amount to get discount on the product : $<span id="product_total_{{$product['sku']}}" data-brand-id="{{$product['user_id'] or '' }}">{{isset($product['product_details']['product_dis_min_amt'])?num_format($product['product_details']['product_dis_min_amt']):0}}</span></div> --}}
                                  @endif


                                  @if(isset($product['product_details']['product_discount']) && $product['product_details']['product_discount']!=0)

                                       <div class="inpt-selects">
                                         
                                           {{-- <div>Product Discount {{$type}}:
                                             <span class="price-bnd-dtls"> ${{isset($product['product_discount'])?num_format($product['product_discount']) : 0.00}}</span>
                                           </div> --}}

                                         <div class="clearfix"></div>
                                       </div>
                                  @endif

        

                                    @if($product['product_details']['shipping_type'] == 1)

                                        @if(isset($product['product_details']['minimum_amount_off']) && $product['product_details']['minimum_amount_off'] > 0)
                                           {{--  <div>Min Order Amount to get free shipping : $<span id="product_total_{{$product['sku']}}" data-brand-id="{{ $product['user_id'] or '' }}">{{num_format($product['product_details']['minimum_amount_off'])}}</span></div> --}}
                                           
                                        @endif

                                    @endif

 
                                    @if($product['product_details']['shipping_type'] == 2 || $product['product_details']['shipping_type'] == 3)
                                      
                                        @if(isset($product['product_details']['minimum_amount_off']) && $product['product_details']['minimum_amount_off']!=0)
                                            {{-- <div>Min Order Amount to get shipping discount : $<span id="product_total_{{$product['sku']}}" data-brand-id="{{ $product['user_id'] or '' }}">{{num_format($product['product_details']['minimum_amount_off'])}}</span></div> --}}
                                        @endif

                                    @endif   


                                    @if(isset($product['product_shipping_charge']) && $product['product_shipping_charge'] != 0)
                                        <div class="inpt-selects">
                                           {{-- <div>Shipping Charges:
                                             <span class="price-bnd-dtls">${{isset($product['product_shipping_charge'])?num_format($product['product_shipping_charge']) : 0.00}}</span>
                                           </div> --}}
     
                                            <div class="clearfix"></div>
                                        </div>
                                    @endif

                                          {{--  @if(isset($product['shipping_charges_discount']) && $product['shipping_charges_discount'] != 0) --}}

                                       @if($shipping_dis_type!=1)
                                          
                                          @if(isset($product['product_details']['off_type_amount']) && $product['product_details']['off_type_amount']!='?' && $product['product_details']['off_type_amount']!="")

                                               <div class="inpt-selects">
                                                  {{--  <div>Shipping Discount {{$ship_type}}:
                                                     <span class="price-bnd-dtls">${{isset($product['shipping_charges_discount'])?num_format($product['shipping_charges_discount']) : 0.00}}</span>
                                                   </div> --}}

                                                 <div class="clearfix"></div>
                                               </div>
                                          @endif

                                        @endif  


                                             @php
                                               $total_amount = 0;
                                               $total_amount = $product['wholesale_price']+$product['product_shipping_charge']-$product['shipping_charges_discount']-$product['product_discount'];
                                             @endphp

                                           @if(isset($total_amount) && $total_amount != 0)
                                             <div class="inpt-selects">
                                               
                                                 <div class="price-bnd-dtls">Total:
                                                   <span>${{isset($total_amount)?num_format($total_amount) : 0.00}}</span>
                                                 </div>

                                                 
                                               
                                               <div class="clearfix"></div>
                                             </div>
                                           @endif

                                        </div>
                                        @php 
                                            $event = 'none';
                                            if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 2){
                                                $event = '';
                                            }
                                        @endphp

                                        <div class="main-qnt-bottom">
                                            <div class="qty-shop-bg">Qty : </div>
                                            <select name="pro_qty" class="pro_qty" style="pointer-events:{{$event}}"  data-pro-id="{{base64_encode($product['product_id'])}}" data-sku-id="{{base64_encode($product['sku'])}}">

                                            @php
                                              $product_min_qty = isset($product['get_product_min_qty']['product_min_qty']) && $product['get_product_min_qty']['product_min_qty']!=''?$product['get_product_min_qty']['product_min_qty']:1;
                                            @endphp
                                    
                                            @for($i=$product_min_qty;$i<=1000;$i++) 
                                              <option @if(isset($product[ 'qty']) && $product[ 'qty']==$i) selected="" @endif value="{{$i}}">{{$i}}</option>
                                            @endfor
                                            </select>   
                                        </div>
                                        <div class="clearfix"></div>
                                        @php 
                                            $event = 'none';
                                            if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 2){
                                                $event = '';
                                            }
                                        @endphp

                                        @if(isset($arr_data['order_details']) && count($arr_data['order_details']) > 1)
                                       
                                        <a style="pointer-events: {{$event}}" href="{{$module_url_path.'/delete_product_from_bucket/'.base64_encode($order_no).'/'.base64_encode($product['sku']) }}" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');" class="close-brnd"><i class="fa fa-times"></i></a>

                                        @else
                                          <a style="pointer-events: {{$event}}" href="javascript::void(0)" onclick="swal('Warning','You can not delete all products. Please reject order.','warning')" class="close-brnd"><i class="fa fa-times"></i></a>

                                        @endif

                                    </div>
                                @endif
                              @endforeach     
                          @else
                              No Record Found
                          @endif
                      </div>

                                    <div class="row">
                              
                                        <div class="col-xs-12 col-sm-12 col-md- col-lg-6 order_summary">
                                            @php 
                                                $display = 'none';
                                                if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 2){
                                                    $display = '';
                                                }
                                             
                                              $first_name = isset($arr_data['address_details']['bill_first_name'])?$arr_data['address_details']['bill_first_name']:'';

                                              $last_name = isset($arr_data['address_details']['bill_last_name'])?$arr_data['address_details']['bill_last_name']:''; 

                                              $user_name = $first_name.' '.$last_name;
                                                       

                                            @endphp
                                            <form id="validation-form">
                                              <div class="row minusrow">  

                                              </div>
                                                <div class="ctomr-shipping-details space-retailer"> Customer Shipping Details:</div>

                                                 <div class="form-group">
                                              <div class="">
                                                  <label class="">
                                                 
                                                  <span class="custom-control"></span>
                                                  <span class="custom-control-description"></span>
                                                  </label>
                                              </div>
                                          </div>
                                        

                                                  <div class="postion-re">
                                                    <div class="postion-re-left">Name</div>
                                                    <div class="postion-reright"><input type="input" name="ship_name" id="ship_name" class="form-control"  {{-- data-parsley-required-true="true" --}} required readonly=""data-parsley-required-message="Please enter name." value="{{$user_name or ''}}"></div>
                                                    <div class="clearfix"> </div>
                                                  </div>

                                                  <div class="postion-re">
                                                      <div class="postion-re-left">Email</div>
                                                      <div class="postion-reright">
                                                      <input type="input" name="ship_email" readonly="" id="ship_email" class="form-control" {{-- data-parsley-required-true="true"  --}} required data-parsley-required-message="Please enter email." value="{{isset($arr_data['address_details']['ship_email'])?$arr_data['address_details']['ship_email']:'N/A'}}"></div>
                                                      <div class="clearfix"> </div>
                                                  </div>

                                                  <div class="postion-re">
                                                    <div class="postion-re-left">Street Address</div>
                                                      <div class="postion-reright">
                                                         <input type="text" name="shipping_street_address"  class="form-control" placeholder="Street Address" id="shipping_street_address" required row="5" data-parsley-required-message="Please enter street address." value="{{isset($arr_data['address_details']['ship_street_address'])?$arr_data['address_details']['ship_street_address']:''}}">
                                                      </div>
                                                    <div class="clearfix"> </div>
                                                  </div>

                                                      <div class="postion-re">
                                                        <div class="postion-re-left">Suite/Apt</div>
                                                          <div class="postion-reright">
                                                             <input type="text" name="shipping_suite_apt"  class="form-control" placeholder="Suite/Apt" id="shipping_suite_apt" row="5" value="{{isset($arr_data['address_details']['ship_suit_apt'])?$arr_data['address_details']['ship_suit_apt']:''}}">
                                                          </div>
                                                          <div class="clearfix"> </div>
                                                      </div>

                                                      <div class="postion-re">
                                                          <div class="postion-re-left"> City</div>
                                                          <div class="postion-reright">
                                                          <input type="input" name="ship_city" id="ship_city" {{-- data-parsley-required-true="true" --}} required class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_city'])?$arr_data['address_details']['ship_city']:'N/A'}}" data-parsley-required-message="Please enter city.">
                                                          </div>
                                                          <div class="clearfix"> </div>
                                                      </div>

                                                       <div class="postion-re">
                                                        
                                                          <div class="postion-re-left"> State</div>
                                                          <div class="postion-reright">
                                                          <input type="input" name="ship_state" id="ship_state" {{-- data-parsley-required-true="true"  --}} required data-parsley-required-message="Please enter state." class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_state'])?$arr_data['address_details']['ship_state']:'N/A'}}"></div>
                                                          <div class="clearfix"> </div>
                                                      </div>

                                                    <div class="postion-re">
                                                      <div class="postion-re-left">Country</div>
                                                        <div class="postion-reright">
                                                          <select class="form-control" id="ship_country" name="ship_country" required {{-- data-parsley-required="true"  --}}data-parsley-required-message="Please select country." placeholder="Select Country">
                                                            <option value="">Select Country</option>
                                                              @if(isset($country_arr) && count($country_arr)>0)
                                                                @foreach($country_arr as $key=>$country)

                                                                <option @if(isset($arr_data['address_details']['ship_country']) && $arr_data['address_details']['ship_country']==$country['id']) selected="selected" @endif value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>

                                                                @endforeach

                                                              @endif  
                                                          </select>
                                                          <input type="hidden" name="hide_ship_country" id="hide_ship_country" value="">
                                                        </div>
                                                        <div class="clearfix"> </div>
                                                    </div>

                                                    <div class="postion-re">
                                                      <div class="postion-re-left">Mobile No</div>
                                                      <div class="postion-reright">
                                                      <input type="input" name="ship_mobile_no" id="ship_mobile_no" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number should be 18 digits" 
                                                      data-parsley-required-message="Please enter mobile number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_mobile_no'])?$arr_data['address_details']['ship_mobile_no']:'N/A'}}" required></div>
                                                      <input type="hidden" name="hid_ship_mobile_no_country_code" id="hid_ship_mobile_no_country_code">
                                                      <div class="clearfix"> </div>
                                                  </div>


                                                      <div class="postion-re">
                                                          <div class="postion-re-left"> Zip Code</div>
                                                          <div class="postion-reright">
                                                          <input oninput="this.value = this.value.toUpperCase()" type="input" name="ship_zip_code" id="ship_zip_code" class="form-control is-read-only" {{-- data-parsley-required-true="true" --}} required data-parsley-required-message = "Please enter zip/postal code." value="{{isset($arr_data['address_details']['ship_zip_code'])?$arr_data['address_details']['ship_zip_code']:'N/A'}}">
                                                          <div id="err_shipping_zip_code" class="err_zip_code"></div>
                                                        </div>
                                                          
                                                          <div class="clearfix"> </div>
                                                      </div>
                                                      
                                             
                                          </div>

                                        </form>  


                                        <div class="col-xs-12 col-sm-12 col-md- col-lg-6 order_summary">
                                          <div class="ctomr-shipping-details">
                                            Customer Billing Details:
                                          </div>
                                          <div class="form-group">
                                              <div class="form-check bd-example-indeterminate">
                                                  <label class="custom-control custom-checkbox">
                                                  <input type="checkbox" class="custom-control-input" id="same-as-billing" name="same_as_billing" value="1" @if(isset($arr_data['is_as_below']) && $arr_data['is_as_below']=="1") checked="" @endif>
                                                  <span class="custom-control-indicator"></span>
                                                  <span class="custom-control-description">Same as Shipping Details</span>
                                                  </label>
                                              </div>
                                          </div>
                                        
                                        <form id="validation-form1">
                                          <div class="postion-re spacetopmargn">
                                              <div class="postion-re-left">Name</div>
                                              <div class="postion-reright">
                                                  @php
                                                      $first_name = isset($arr_data['address_details']['bill_first_name'])?$arr_data['address_details']['bill_first_name']:'';

                                                      $last_name = isset($arr_data['address_details']['bill_last_name'])?$arr_data['address_details']['bill_last_name']:''; 

                                                      $user_name = $first_name.' '.$last_name;
                                                  @endphp

                                                  <input type="input" name="bill_name" id="bill_name" class="form-control" readonly="" {{-- data-parsley-required-true="true" --}}  data-parsley-required-message="Please enter name." value="{{isset($user_name)?$user_name:''}}" required>
                                              </div>
                                              <div class="clearfix"> </div>
                                          </div>

                                          <div class="postion-re">
                                              <div class="postion-re-left">Email</div>
                                              <div class="postion-reright">
                                              <input type="input" name="bill_email" id="bill_email" readonly="" {{-- data-parsley-required-true="true" --}}  data-parsley-required-message="Please enter email." class="form-control" value="{{isset($arr_data['address_details']['bill_email'])?$arr_data['address_details']['bill_email']:'N/A'}}" required></div>
                                              <div class="clearfix"> </div>
                                          </div>

                                          <div class="postion-re">                                              

                                            <div class="postion-re-left">Street Address</div>
                                                <div class="postion-reright">
                                                   <input type="text" name="billing_street_address"  class="form-control" {{-- data-parsley-required="true" --}}   placeholder="Street Address" id="billing_street_address" row="5" data-parsley-required-message="Please enter street address." value="{{isset($arr_data['address_details']['bill_street_address'])?$arr_data['address_details']['bill_street_address']:''}}" required>
                                                </div>
                                                <div class="clearfix"> </div>
                                            </div>

                                            <div class="postion-re">
                                              <div class="postion-re-left">Suite/Apt</div>
                                                <div class="postion-reright">
                                                   <input type="text" name="billing_suite_apt"  class="form-control" placeholder="Suite/Apt" id="billing_suite_apt" row="5" value="{{isset($arr_data['address_details']['bill_suit_apt'])?$arr_data['address_details']['bill_suit_apt']:''}}">
                                                </div>
                                                <div class="clearfix"> </div>
                                            </div>

                                          <div class="postion-re">
                                              <div class="postion-re-left"> City</div>
                                              <div class="postion-reright">
                                              <input type="input" {{-- data-parsley-required="true"  --}}  data-parsley-required-message="Please enter city." name="bill_city" id="bill_city" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_city'])?$arr_data['address_details']['bill_city']:'N/A'}}" required></div>
                                              <div class="clearfix"> </div>
                                          </div>

                                          <div class="postion-re">
                                              <div class="postion-re-left"> State</div>
                                              <div class="postion-reright">
                                                  <input type="input" {{-- data-parsley-required="true" --}}  data-parsley-required-message="Please enter state." name="bill_state" id="bill_state" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_state'])?$arr_data['address_details']['bill_state']:'N/A'}}" required>
                                              </div>
                                              <div class="clearfix"> </div>
                                          </div>

                                           <div class="postion-re">
                                              <div class="postion-re-left">Country</div>
                                                  <div class="postion-reright">
                                                      <select class="form-control" id="bill_country" name="bill_country" {{-- data-parsley-required="true" --}}  data-parsley-required-message="Please select country." placeholder="Select Country" required>

                                                          <option value="">Select Country</option>
                                                          @if(isset($country_arr) && count($country_arr)>0)
                                                              @foreach($country_arr as $key=>$country)

                                                              <option @if(isset($arr_data['address_details']['bill_country']) && $arr_data['address_details']['bill_country']==$country['id']) selected="selected" @endif value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>

                                                              @endforeach

                                                          @endif  
                                                      </select>
                                                      <input type="hidden" name="hide_bill_country" id="hide_bill_country" value="">
                                                  </div>
                                              <div class="clearfix"> </div>
                                          </div>

                                          <div class="postion-re">
                                              <div class="postion-re-left">Mobile No</div>
                                              <div class="postion-reright">
                                              <input type="input" name="bill_mobile_no" id="bill_mobile_no" {{-- data-parsley-required="true" --}} data-parsley-required-message="Please enter mobile number." class="form-control is-read-only" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number should be 18 digits" data-parsley-minlength-message="Mobile number should be 10 digits" value="{{isset($arr_data['address_details']['bill_mobile_no'])?$arr_data['address_details']['bill_mobile_no']:'N/A'}}" required >
                                              <input type="hidden" name="hid_bill_mobile_no_country_code" id="hid_bill_mobile_no_country_code">
                                              </div>
                                              <div class="clearfix"> </div>
                                          </div>

                                          <div class="postion-re">
                                              <div class="postion-re-left"> Zip Code</div>
                                              <div class="postion-reright">
                                                  <input oninput="this.value = this.value.toUpperCase()" type="input" {{-- data-parsley-required="true" --}}  data-parsley-required-message="Please enter zip/postal code." name="bill_zip" id="bill_zip" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_zip_code'])?$arr_data['address_details']['bill_zip_code']:'N/A'}}" required>

                                                  <div id="err_billing_zip_code" class="err_zip_code"></div>
                                              </div>
                                              

                                              <div class="clearfix"> </div>
                                              
                                          </div>

                                        </div>

                                        <div class="col-md-12">
                                            <input type="hidden" name="order_no" value="{{isset($arr_data['order_no'])?$arr_data['order_no']:''}}">
                                        </form>
                                     </div>
                                   </div>
                       
                                </div>
                            </div>


                                <div class="col-md-4 rightSidebar rep_order_summary_right">
                                    <div class="theiaStickySidebar">
                                    <div class="order-summary-main-dv-cs">
                                        <div class="order-summary-main-smr">Order Summary</div>
                                        <hr>

                                        @php

                                            /*calculate total shipping charges*/

                                            $total_shipping_charges = 0;
                                      
                                            $Wholsale_sub_total = $total_shipping_charges = $total_shipping_discount = $total_product_discount = 0;

                            


                                            if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)
                                            {

                                                foreach($arr_data['order_details'] as $key => $product)
                                                {
                                                   if(isset($product['maker_id']) && $product['maker_id'] == base64_decode($enc_maker_id))
                                                   {
                            
                                                     $Wholsale_sub_total +=  isset($product['wholesale_price'])?$product['wholesale_price']:0.00;

                                                     if(isset($promo_shipping_charges) && $promo_shipping_charges == 0)
                                                     {
                                                        $total_shipping_charges += 0;
                                                        $total_shipping_discount += 0;
                                                     }

                                                     else{

                                                        $total_shipping_charges += isset($product['product_shipping_charge'])?$product['product_shipping_charge']:0;

                                                        $total_shipping_discount += isset($product['shipping_charges_discount'])?$product['shipping_charges_discount']:0;
                                                     }
                                                     

                                                     $total_product_discount += isset($product['product_discount'])?$product['product_discount']:0;
                                                   }
                                                   
                                                }
                                            }
                          
                                        @endphp


                                        <div class="ordr-total-ship">
                                            <div class="ordr-total-ship-left"> Sub Total :</div>
                                            <div class="ordr-total-ship-right"><span class="fa fa-dollar"></span>{{isset($Wholsale_sub_total)?num_format($Wholsale_sub_total):0.00}}
                                         
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>

                                          @if(isset($total_shipping_charges) && $total_shipping_charges!=0)
                                            @php $tot_shipping_charges = num_format($total_shipping_charges); @endphp   
                                           <div class="ordr-total-ship">
                                             {{-- <div class="ordr-total-ship-left">Total Shipping Amount :</div>
                                             <div class="ordr-total-ship-right">@if(num_format($total_shipping_charges)>0)+ @endif <span class="fa fa-dollar"></span>{{isset($tot_shipping_charges)?$tot_shipping_charges:0.00}}
                                            </div> --}}
                                             <div class="clearfix"></div>
                                            </div>
                                          @endif

                                          @if(isset($total_shipping_discount) && $total_shipping_discount!=0)
                                             <div class="ordr-total-ship">
                                              {{-- <div class="ordr-total-ship-left">Total Shipping Discount  --}}{{-- {{isset($total_ship_percent)?$total_ship_percent:''}}{{isset($total_ship_dollar)?$total_ship_dollar:''}} --}} : {{-- </div> --}}
                                              {{-- <div class="ordr-total-ship-right">@if($total_shipping_discount>0)-@endif<span class="fa fa-dollar"></span>{{isset($total_shipping_discount)?num_format($total_shipping_discount):0.00}}
                                              </div> --}}
                                                <div class="clearfix"></div>
                                              </div>
                                          @endif

                                            @if(isset($total_product_discount) && $total_product_discount!=0)
                                            @php $prod_discount = num_format($total_product_discount); @endphp 
                                             <div class="ordr-total-ship">
                                             {{-- <div class="ordr-total-ship-left">Total Product Discount --}} {{-- {{isset($prod_dis_value_percent)?$prod_dis_value_percent:''}} {{isset($prod_dis_value_dollar)?$prod_dis_value_dollar:''}} --}} {{-- : </div>
                                             <div class="ordr-total-ship-right">@if(num_format($total_product_discount)>0)-@endif <span class="fa fa-dollar"></span>{{isset($prod_discount)?$prod_discount:0.00}}
                                             
                                              </div> --}}
                                                <div class="clearfix"></div>
                                              </div>
                                            @endif

                                             @if(isset($promotion_discount_amt) && $promotion_discount_amt!=0)
                                             <div class="ordr-total-ship">
                                            {{--  <div class="ordr-total-ship-left">Promotion Discount :</div>
                                             <div class="ordr-total-ship-right">@if(num_format($promotion_discount_amt)>0)-@endif <span class="fa fa-dollar"></span>{{isset($promotion_discount_amt)?num_format($promotion_discount_amt):00}}
                                             
                                              </div> --}}
                                                <div class="clearfix"></div>
                                              </div>
                                            @endif

                                        <hr>
                                     @php

                                        $total_wholesale_amt = 0.00;


                                        if(isset($arr_data['order_details']) && count($arr_data['order_details']) > 0)
                                        {
                                            foreach($arr_data['order_details'] as $key => $product)
                                            {
                                                if(isset($product['maker_id']) && $product['maker_id'] == base64_decode($enc_maker_id))
                                                {
                                                    if(isset($promotion_discount_amt) && $promotion_discount_amt!=0)
                                                    {
                                                       
                                                       /* $total_wholesale_amt = array_sum(array_column($arr_data['order_details'],'wholesale_price'));*/

                                                        $total_wholesale_amt += $product['wholesale_price'];

                                                       /* $total_wholesale_amt = $total_wholesale_amt - $promotion_discount_amt;*/

                                                    }
                                                    else
                                                    {
                                                        /*$total_wholesale_amt = array_sum(array_column($arr_data['order_details'],'wholesale_price'));*/

                                                        $total_wholesale_amt += $product['wholesale_price'];

                                                    }
                                            
                                                }
                                            }    

                                        }


                                     @endphp
                                     
                                      <div class="ordr-total-ship">
                                         <div class="ordr-total-ship-left"> Total Amount   :</div>
                                         @php 
                                           $total_amount = 0;
                                           $promotion_discount_amt = isset($promotion_discount_amt)?num_format($promotion_discount_amt):0;
                                           


                                           if($promotion_discount_amt > 0)
                                           {
                                             $total_wholesale_amt = $total_wholesale_amt - $promotion_discount_amt;
                                           }

                                           $total_amount = $total_wholesale_amt+$total_shipping_charges-$total_shipping_discount-$total_product_discount;
                                           // $total_amount = $arr_data['total_wholesale_price'] - $promotion_discount_amt;
                                          @endphp

                                         <div class="ordr-total-ship-right">
                                          <span class="fa fa-dollar"></span>{{isset($total_amount)?num_format($total_amount):0.00}}

                                        </div>
                                        <input type="hidden" name="total_amount" id="total_amount" value="{{$total_amount}}">
                                         <div class="clearfix"></div>
                                      </div>

                                      <input type="hidden" name="arr_makers" id="arr_makers" value="{{isset($maker_ids)?$maker_ids:''}}">

                                      <input type="hidden" name="order_no" id="order_no" value="{{isset($arr_data['order_no'])?$arr_data['order_no']:''}}">

                                   </div>

                                  @if(isset($arr_data['order_details']) && sizeof($arr_data['order_details'])>0)    
                                      @if($arr_data['is_confirm'] == 2)
                                  

                                       <a class="btn btn-block btn-outline btn-rounded btn-success btn-blk" onclick="return confirm_lead(this,event,'Are you sure? Do you want to save this order as confirm order, after confirming you will not able to modify anything.');"> <i class="fa fa-check-circle"></i> Confirm</a>


                                        <a id="rejectReason" class="btn btn-block btn-outline btn-rounded btn-danger " href="javascript:void(0)"> <i class="fa fa-times-circle" style="font-size:15px;color:red"></i> Reject</a>

                                       
                                      @endif
                                  @endif

                                  @if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 1)
                                      @php  
                                        Session::put('representative_order_id',$arr_data['id']);
                                       
                                        if($arr_data['payment_term'] == 'Online/Credit')
                                        {
                                           Session::put('payment_type','Online/Credit');
                                        }
                                        elseif($arr_data['payment_term'] == 'Net30')
                                        {
                                          Session::put('payment_type','Net30');
                                        }
                                        else
                                        {
                                           Session::put('payment_type','Online/Credit');
                                        }
                                      @endphp


                                      @php

                                      $eventCall = 'return notAllowedPayment()';
                                              
                                      if(isset($arr_data['maker_confirmation'])  && $arr_data['maker_confirmation'] == 1)
                                      {
                                       
                                        $eventCall = "return checkoutRedirect($(this))";
                                      } 

                                      @endphp
                                    
                                      @if($count == 0)  
                                        
                                        <div class="button-left-right"> 
                                            @if($arr_data['payment_term'] != 'Online/Credit')
                                            
                                              <a class="btn btn-success pull-left" href="javascript:void(0);" onclick="{{$eventCall}}">Pay Now</a>
                                            
                                            @endif
                                                
                                        </div>

                                      
                                      @endif

                                  @endif 
                                        
                                <input type="hidden" name="payment_type" id="payment_type" value="{{$arr_data['payment_term'] or ''}}">

                        </div>
                    </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>

<div id="RetailerReasonModal" class="modal fade" data-replace="true" style="display: none;">
  <div class="modal-dialog">
  
      <div class="modal-content sign-up-popup">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

          <div class="modal-body">
            <form id="OrderRejectionReason" name="OrderRejectionReason"> 
                <div class="login-form-block">
                    <div class="login-content-block">
                 
                        <div class="form-group">
                          <label for="reason" class="col-form-label">Order Rejection Reason<i class="red">*</i></label>
                          <div class="col-sm-12 col-md-12 col-lg-12 p-0">
                            <div class="form-group">
                              <textarea class="form-control" row="4" id="retailer_reject_reason" name="retailer_reject_reason" data-parsley-required="true" data-parsley-required-message="Please enter order rejection reason."></textarea>
                              <span class='red' id="reason_error">{{ $errors->first('reason') }}</span>
                            </div>
                          </div>
                        </div>
        
                         <div class="categorynote-main">  
                            
                          <div class="pull-right">
                         
                           <a class="btn btn-outline btn-rounded btn-success btn-blk" data-toggle="modal" id="btn_submit">Submit</a>
                          </div>
                   
                         <div class="clearfix"></div>
                         </div>
                        <div class="clearfix"></div>
                      </div>

                  </div>
              <div class="clr"></div>
            </form>
          </div>
      </div>
  </div>maker_id
</div>

<script src="https://cdn.tiny.cloud/1/{{$site_setting_arr['tinymce_api_key'] or ''}}/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript" src="{{url('/')}}/assets/js/tinyMCE.js"></script>

<script>

  var module_url_path = '{{$module_url_path or ''}}'; 

  function applyPromoCode(maker_id)
  {
      var promo_code            = $('#promo_code_'+maker_id).val();
      var total_amt             = $('#total_amount').val();
      
    
      let url = SITE_URL+'/my_bag/apply_promo_code';

      $.ajax({
          url:url,
          data:{promo_code:promo_code,total_amt:total_amt,maker_id:maker_id,_token:"{{ csrf_token() }}"},
          method:'POST',
          beforeSend: function() 
          {
              showProcessingOverlay();                 
          },
          success: function(response){
              
              hideProcessingOverlay();
          
              if (response.status == 'error') 
              {

                  $('#'+maker_id+'_promo_error').text(response.description);
              }

              if (response.status == 'success') 
              {
              
                  location.reload();
              }
        
          }
          
      });  

  }

  function notAllowedPayment()
  {
    swal('Warning','Please wait for order confirmation from the Vendor','warning');
  }

  function clearPromoCode(maker_id) 
  {
      let url = SITE_URL+'/my_bag/clear_promo_code';

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
                  data:{maker_id:maker_id},
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
                                location.reload();
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
                              location.reload();
                          });
                         
                      }
                
                  }
                  
              })

          });
  }

  $('.pro_qty').on('change', function() 
    {
        var qty     = this.value;
        var pro_id  = $(this).attr("data-pro-id");
        var pro_sku_id  = $(this).attr("data-sku-id");
        var order_no = "{{ base64_encode($order_no)}}";

        var url = '{{url('/')}}/retailer/my_orders/update_product_qty';

        $.ajax({
            url: url,
            data: {
                    pro_id: pro_id,
                    qty: qty,
                    order_no:order_no,
                    pro_sku_id:pro_sku_id
            },
            method: 'GET',
            beforeSend: function() {
                showProcessingOverlay();
            },
            success: function(response) {
                hideProcessingOverlay();

               window.location.reload(true);
            }
        })
    });


  function confirm_lead(ref,evt,msg)
  {

       $("#err_shipping_zip_code").html("");
       $("#err_billing_zip_code").html("");

       var ship_country  = $("#ship_country").val();
       var ship_zip      = $("#ship_zip_code").val();
       var bill_country  = $("#bill_country").val();
       var bill_zip      = $("#bill_zip").val();
          


    if($('#validation-form').parsley().validate() == false)
    {
       return;
    }

    if($("#validation-form1").parsley().validate() == false)
    {
       return;
    }

   
    var msg = msg || false;
  
    evt.preventDefault();  

    swal({
          title: "Need Confirmation",
          type: "warning",
          text: msg,
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "OK",
          closeOnConfirm: false
        },
        function(isConfirm,tmp)
        {
          if(isConfirm==true)
          {
            showProcessingOverlay();
            changeAddress();
          }
        });
  }

  function checkPriceSatisfaction()
  {
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
              var _tmpBrandName = $(_elem).attr('data-name');

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
      else
      {

        var ordId = '{{isset($arr_data['id'])?base64_encode($arr_data['id']):false}}';

        if(ordId)
        {  
           var order_id = '{{isset($arr_data['id'])?base64_encode($arr_data['id']):false}}';
 
           var url = module_url_path+'/finalize_lead/'+order_id+'?type=confirm';
         
           location.href=url;
        }
      }
  }

  function changeAddress()
  {
    
      var bill_name               = $("#bill_name").val();
      var bill_email              = $("#bill_email").val();
      var bill_mobile_no          = $("#bill_mobile_no").val();
      var bill_complete_addr      = $("#bill_complete_addr").val();
      var bill_city               = $("#bill_city").val();
      var bill_state              = $("#bill_state").val();
      var bill_country            = $("#bill_country").val();
      var bill_zip                = $("#bill_zip").val();
      var ship_name               = $("#ship_name").val();
      var ship_email              = $("#ship_email").val();
      var ship_mobile_no          = $("#ship_mobile_no").val();
      var ship_complete_addr      = $("#ship_complete_addr").val();
      var ship_city               = $("#ship_city").val();
      var ship_state              = $("#ship_state").val();
      var ship_country            = $('#ship_country').val();
      var ship_zip_code           = $("#ship_zip_code").val();
      var order_no                = $("#order_no").val();

      

      

      var ship_street_address     = $('#shipping_street_address').val();
      var ship_suit_apt           = $('#shipping_suite_apt').val();

      var bill_street_address     = $('#billing_street_address').val();
      var bill_suit_apt           = $('#billing_suite_apt').val();


      $.ajax({
          url : '{{url('/')}}/retailer/my_orders/save_address',
           method: 'POST',
          dataType:'JSON',
          data: {
            "_token": "{{ csrf_token() }}",
            "bill_name":bill_name,
            "bill_email":bill_email,
            "bill_mobile_no": bill_mobile_no,
            "bill_complete_addr" : bill_complete_addr,
            "bill_city" : bill_city,
            "bill_state": bill_state,
            "bill_zip": bill_zip,
            "ship_name":ship_name,
            "ship_email":ship_email,
            "ship_mobile_no": ship_mobile_no,
            "ship_complete_addr":ship_complete_addr,
            "ship_city":ship_city,
            "ship_state":ship_state,
            "ship_zip_code":ship_zip_code,
            "order_no":order_no,
            "bill_street_address":bill_street_address,
            "bill_suit_apt":bill_suit_apt,
            "ship_street_address":ship_street_address,
            "ship_suit_apt":ship_suit_apt,
            "bill_country":bill_country,
            "ship_country":ship_country

        },
        beforeSend: function() 
        {
           // showProcessingOverlay();                 
        },
        success: function(response) {
         checkPriceSatisfaction();
          
        }             
    });
  }



  $('#same-as-billing').click(function(){

  if($(this).prop("checked") == true)
  {
    /*let bill_first_name = $('#bill_first_name').val();
    let bill_last_name  = $('#bill_last_name').val();
    let bill_email      = $('#bill_email').val();
    let bill_mobile_no  = $('#bill_mobile_no').val();
    let bill_complete_addr = $('#bill_complete_addr').val();
    let bill_city       = $('#bill_city').val();
    let bill_state      = $('#bill_state').val();
    let bill_zip        = $('#bill_zip').val();*/


    let ship_first_name  = $('#ship_first_name').val();
    let ship_last_name   = $('#ship_last_name').val();
    let ship_email       = $('#ship_email').val();
    let ship_mobile_no   = $('#ship_mobile_no').val();
    let ship_city        = $('#ship_city').val();
    let ship_state       = $('#ship_state').val();
    let ship_country     = $('#ship_country').val();
    let ship_zip         = $('#ship_zip_code').val();
    let ship_street_addr = $('#shipping_street_address').val();
    let ship_suit_apt    = $('#shipping_suite_apt').val();

   /* $('#ship_first_name').val(bill_first_name).prop('readonly',true);
    $('#ship_last_name').val(bill_last_name).prop('readonly',true);
    $('#ship_email').val(bill_email).prop('readonly',true);
    $('#ship_mobile_no').val(bill_mobile_no).prop('readonly',true);
    //$('#ship_complete_addr').val(bill_complete_addr).prop('readonly',true);
    $('#ship_city').val(bill_city).prop('readonly',true);
    $('#ship_state').val(bill_state).prop('readonly',true);    
    $('#ship_zip_code').val(bill_zip).prop('readonly',true);*/

    $('#bill_first_name').val(ship_first_name).prop('readonly',true);
    $('#bill_last_name').val(ship_last_name).prop('readonly',true);
    $('#bill_email').val(ship_email).prop('readonly',true);
    $('#bill_mobile_no').val(ship_mobile_no).prop('readonly',true);
    $('#bill_city').val(ship_city).prop('readonly',true);
    $('#bill_state').val(ship_state).prop('readonly',true);    
    $('#bill_zip').val(ship_zip).prop('readonly',true);  
    $('#bill_country').val(ship_country).attr('disabled',true);
    $('#billing_street_address').val(ship_street_addr).prop('readonly',true);
    $('#billing_suite_apt').val(ship_suit_apt).prop('readonly',true);
  }
  else if($(this).prop("checked") == false)
  {
    /*$('#ship_first_name').val('').prop('readonly',false);
    $('#ship_last_name').val('').prop('readonly',false);
    $('#ship_email').val('').prop('readonly',false);
    $('#ship_mobile_no').val('').prop('readonly',false);
    $('#ship_complete_addr').val('').prop('readonly',false);
    $('#ship_city').val('').prop('readonly',false);
    $('#ship_state').val('').prop('readonly',false);
    // $('#ship_zip').val('').prop('readonly',false);
    $('#ship_zip_code').val('').prop('readonly',false);*/

    $('#bill_first_name').prop('readonly',false);
    $('#bill_last_name').prop('readonly',false);
    $('#bill_email').prop('readonly',false);
    $('#bill_mobile_no').prop('readonly',false);
    $('#bill_city').prop('readonly',false);
    $('#bill_state').prop('readonly',false);   
    $('#bill_zip').prop('readonly',false);
    $('#bill_country').attr('disabled',false);
    $('#billing_street_address').prop('readonly',false);
    $('#billing_suite_apt').prop('readonly',false);

  }
});


$("#ship_mobile_no").keydown(function(event) {
            var text_length = $("#ship_mobile_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#ship_mobile_no").keyup(function(event) {
            var text_length = ($("#ship_mobile_no").attr('code_length')) ? $("#ship_mobile_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

        $("#bill_mobile_no").keydown(function(event) {
            var text_length = $("#bill_mobile_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#bill_mobile_no").keyup(function(event) {
            var text_length = ($("#bill_mobile_no").attr('code_length')) ? $("#bill_mobile_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

 
  $("#ship_country").change(function(){
    var country_id    = $("#ship_country").val();
    var ship_zip_code = $("#ship_zip_code").val();
    
    if(country_id == "" && ship_zip_code !="")
    {
       $("#err_shipping_zip_code").html('Invalid zip/postal code.');
    } 

    var phone_code   = $('option:selected', this).attr('phone_code');
    var zipcode_length = $('option:selected', this).attr('zipcode_length');
    var countryName = $('option:selected', this).attr('country_name');

    if(phone_code){
        $("#ship_mobile_no").val("+"+phone_code);
        $("#ship_mobile_no").attr('code_length',phone_code.length+1);  
        $("#hid_ship_mobile_no_country_code").val('+'+phone_code);
       } else {
        $("#ship_mobile_no").val('');
        $("#ship_mobile_no").attr(0); 
        $("#hid_ship_mobile_no_country_code").val('');
     } 
    
    var codeLength = jQuery('#hid_ship_mobile_no_country_code').val();
    var minPhone = 10 + codeLength.length;  
    $('#ship_mobile_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
        $('#ship_zip_code').attr('parsley-maxlength', true);
        $('#ship_zip_code').removeAttr('data-parsley-length');
        $('#ship_zip_code').attr('data-parsley-length-message', "");
        $("#ship_zip_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters.',
        });
    }
    else{
        $('#ship_zip_code').attr('parsley-maxlength', false);
        $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
        $("#ship_zip_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits.'
        });
    }

    $('#validation-form').parsley();

    });

    $("#bill_country").change(function(){

       var country_id = $("#bill_country").val();
       var bill_zip   = $("#bill_zip").val();

       if(country_id=="" && bill_zip!="")
      {
         $("#err_billing_zip_code").html('Invalid zip/postal code.');
      }

      var phone_code   = $('option:selected', this).attr('phone_code');
      var zipcode_length = $('option:selected', this).attr('zipcode_length');
      var countryName = $('option:selected', this).attr('country_name');

      if(phone_code){
          $("#bill_mobile_no").val("+"+phone_code);
          $("#bill_mobile_no").attr('code_length',phone_code.length+1);  
          $("#hid_bill_mobile_no_country_code").val('+'+phone_code);
        } else {
          $("#bill_mobile_no").val('');
          $("#bill_mobile_no").attr(0); 
          $("#hid_bill_mobile_no_country_code").val('');
      } 
      
      var codeLength = jQuery('#hid_bill_mobile_no_country_code').val();
      var minPhone = 10 + codeLength.length;  
      $('#bill_mobile_no').attr('data-parsley-minlength', minPhone);

      if(zipcode_length == 8)
      {
          $('#bill_zip').attr('parsley-maxlength', true);
          $('#bill_zip').removeAttr('data-parsley-length');
          $('#bill_zip').attr('data-parsley-length-message', "");
          $("#bill_zip").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters.',
          });
      }
      else{
          $('#bill_zip').attr('parsley-maxlength', false);
          $('#bill_zip').attr('data-parsley-maxlength-message', "");
          $("#bill_zip").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits.'
          });
      }

      $('#validation-form1').parsley();

    });


 
function checkoutRedirect(ref)
{
      var payment_term = $('#payment_type').val(); 

      if(payment_term == 'Net30')
      {
        swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to make payment.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#DD6B55',
        confirmButtonText: 'OK',
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        closeOnCancel: true
      },
      function(isConfirm){

          if(isConfirm==true)
          {
            //window.location = $(ref).attr('href');
            window.location = '{{url('/checkout')}}';
          }
         
      });
    }
    else
    {
       window.location = '{{url('/checkout')}}';
    }
    
}  
</script>
<!-- Tinymce editor js-->

<script>
    $(document).ready(function() {

      /*Remove already init TinyMCE*/
      setTimeout(()=>{
        tinymce.remove();
      },200);
      /*------------------------*/

    $('.content, .rightSidebar')
      .theiaStickySidebar({
        additionalMarginTop: 30
      });

      $('#rejectReason').on("click", function(e){
        e.preventDefault();

        $('#RetailerReasonModal').modal('show');
        /*Reinitalized TinyMCE once pop is opened*/
          reInitTinymce();
        /*--------------------------------------*/
      });

      $('#btn_submit').on("click", function(e){
        e.preventDefault();  
        order_cancel_status = 0;
        var reason = tinyMCE.get('retailer_reject_reason').getContent();

        if(reason == '')
        {
            $('#reason_error').html('Please enter order rejection reason.');
            return false;
        }
        else
        {
          var order_id = '{{isset($arr_data['id'])?base64_encode($arr_data['id']):false}}';

          if(order_id)
          {  
            showProcessingOverlay();
            var url = module_url_path+'/finalize_lead/'+order_id+'?type=reject&reason='+reason;
            location.href=url;
          }
        }    
      });
  });

  function reInitTinymce(){
    tinymce.init({
       selector: 'textarea',
       relative_urls: false,
       remove_script_host:false,
       convert_urls:false,
       plugins: [
         'link',
         'fullscreen',
         'contextmenu '
       ],
       toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
       content_css: [
         // '//www.tinymce.com/css/codepen.min.css'
       ]
     });
  }  
</script>
@stop