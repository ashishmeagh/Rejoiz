@extends('sales_manager.layout.master') @section('main_content')
<link href="{{url('/')}}/assets/css/gallery.css" rel="stylesheet" type="text/css" />
 <script type="text/javascript" src="{{url('/')}}/assets/js/resizesensor.js"></script>
  <script type="text/javascript" src="{{url('/')}}/assets/js/theia-sticky-sidebar.js"></script>
<style type="text/css">
  .btn-blk{
    color: #00c292 !important;
  }
.btn-blk:hover{color: #fff !important;}
.space-retailer{margin-top: 20px;}
.postion-re.spacetopmargn{margin-top: 72px;}
.err_zip_code{
    margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }
  .ctomr-shipping-details.space-retailer.bottom-20{
    margin-bottom: 55px;
  }
  .postion-re.spacetopmargn {margin-top:0px;}
  .postion-re {margin-bottom:25px;}
  .parsley-errors-list {position:absolute;}

  ul {list-style-type:none !important;}

</style>


@php
  $product_max_qty_limit = isset($site_setting_arr['product_max_qty'])?$site_setting_arr['product_max_qty']:1000;
@endphp


<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Order Summary</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
          <li><a href="{{url('/')}}/sales_manager/leads">{{$module_title or ''}}</a></li>
          <li class="active">Order Summary</li>
        </ol>
      </div>
        
    </div>
      <!-- .row -->
    <div class="row">
      <div class="col-lg-12">
        @include('admin.layout._operation_status')
        <div class="white-box sales_order_summary_new_tabs">
          <div class="row">
            <div class="col-md-12">
              <ul class="tb-links tab-find-link">
                <li> <a href="{{ $module_url_path.'/create/'}}{{$order_no}}">Customer Details</a> </li>
                <li> <a href="{{$module_url_path.'/find_products/'.$order_no}}">Add Products</a> </li>
                <li class="active"> <a href="{{$module_url_path}}/order_summary/{{ base64_encode($order_no)}}">Order Summary</a> </li>
                     
              </ul>
            </div>

            <div class="col-md-12 order_summary_first_box">
              <div class="title-billing-address">
                Order Summary 
                <br>
                <div class="order-numbr-summry">Order No : <strong>{{$arr_data['order_no'] or ''}}</strong></div>
              </div>
              <div class="row">

                <div class="col-md-8 content leftsidebar rep_order_summary_left ">
                  <div class="theiaStickySidebar">
                    <div class="main-order-brand-blue">
                      {{-- <div class="save-it-later-btn"></div> --}}

                      @php
                        $total_shipping_charge = [];
                        $total_shipping_discount = [];
                        $total_product_discount_value = $total_shipping_discount_value =0;

                        $total_ship_discount_value_perc = $total_ship_discount_value_dollar = 
                        $total_product_dis_value_perc = $total_product_dis_value_dollar = 0;

                        $total_ship_percent = $total_ship_dollar = $prod_dis_value_dollar = 
                        $prod_dis_value_percent = $total_ship_percent = $total_ship_dollar =
                        $prod_dis_value_dollar = $prod_dis_value_percent = $ship_dis_val = 
                        $ship_dis_val = $prod_dis_value = $prod_dis_value = '';
                      @endphp

                      @if(isset($arr_data['order_details']) && sizeof($arr_data['order_details'])>0)

                        @foreach($product_data as $key=> $products)

                          @php
                            $maker_details = [];
                            $maker_min_order_amount = 0;
                            $maker_details = get_maker_shop_setting($key);


                            if (isset($maker_details)) {

                                $maker_min_order_amount = isset($maker_details['first_order_minimum']) ? $maker_details['first_order_minimum'] : 0;           
                            }

                          @endphp

                          <div class="bag-clone-group">
                            <div class="jst-mybg">
                                  <div class="creativebtdstitlte" data-brand="{{$key}}" data-brand-min="{{num_format($maker_min_order_amount)}}">{{get_maker_company_name($key)}}</div>
                                  @if(isset($maker_min_order_amount) && $maker_min_order_amount > 0)
                                      <div class="save-it-main">{{-- Minimum Order Amount: <span>${{num_format($maker_min_order_amount)}} --}}</span> </div>      
                                  @else
                                      <div class="save-it-main">No Minimum</div>
                                  @endif   
                                                              
                            </div>
                          </div>    
                     

                          @foreach($arr_data['order_details'] as $product)
                            @if($key == $product['maker_id'])
                         
                              <div class="brand-dtls-nw save-itlater-bx">

                                <div class="img-brnd">
                                  @php


                                    $sku = isset($product['sku'])?$product['sku']:"";
                                    $product_sku_image = get_sku_image($sku);
                                    $shipping_discount = $shipping_charge =0;

                                    /* shipping charge calculation*/
                                    $shipping_type = $product['product_details']['shipping_type'];

                                    $wholesale_price = $product['wholesale_price'];

                                    $minimum_amount_off = (float)$product['product_details']['minimum_amount_off'];

                                    $off_type_amount = (float)$product['product_details']['off_type_amount'];

                                    $shipping_charge = (float)$product['product_details']['shipping_charges'];

                                    if($shipping_type==1)
                                    {
                                      $shipping_discount = 0;
                                     
                                      if($wholesale_price < $minimum_amount_off)
                                      {
                                        $shipping_charge = $shipping_charge;
                                        array_push($total_shipping_charge,$shipping_charge);
                                        array_push($total_shipping_discount,$shipping_discount);
                                      }
                                      else
                                      {
                                        $shipping_charge = 0;
                                        array_push($total_shipping_charge,$shipping_charge);
                                        array_push($total_shipping_discount,$shipping_discount);
                                      }
                                    }
                                    elseif($shipping_type==2)
                                    {
                                      if($wholesale_price>=$minimum_amount_off)
                                      {
                                        /*Discount = Original Price x Discount % / 100*/
                                         $shipping_discount =  $shipping_charge * $off_type_amount/ 100;
                                         array_push($total_shipping_discount,$shipping_discount);
                                          // $shipping_charge = $shipping_charge- $shipping_discount;
                                        array_push($total_shipping_charge,$shipping_charge);

                                      }
                                      else
                                      {
                                        $shipping_charge = $shipping_charge;
                                        $shipping_discount = 0;
                                        array_push($total_shipping_charge,$shipping_charge);
                                        array_push($total_shipping_discount,$shipping_discount);
                                      }
                                    }

                                    elseif($shipping_type==3)
                                    {
                                      if($wholesale_price>=$minimum_amount_off)
                                      {
                                        
                                        $shipping_discount =  $off_type_amount;
                                         array_push($total_shipping_discount,$shipping_discount);
                                         array_push($total_shipping_charge,$shipping_charge);
                                      }
                                      else
                                      {
                                        $shipping_charge = $shipping_charge;
                                        $shipping_discount = 0;
                                        array_push($total_shipping_charge,$shipping_charge);
                                        array_push($total_shipping_discount,$shipping_discount);
                                      }
                                    }
                                
                              
                                  @endphp

                                  <img src="{{ $product_sku_image or ''}}" alt="">
                                </div>
                                <div class="brnd-dtls-mn">
                                
                                  <input type="hidden"  class = "shipping_charge" name="shipping_charge" value="{{$shipping_charge or 0}}">
                                  <input type="hidden"  class = "shipping_charge" name="shipping_discount" value="{{$shipping_discount or 0}}">
                                  <input type="hidden"  class = "shipping_charge" name="shipping_type" value="{{$shipping_type or ''}}">

                                  <div class="product-name-bnd">{{$product['product_details']['product_name'] or ''}}</div>

                                  <div class="price-bnd-dtls">
                                    SKU No : <span class="bold-tx-ordr">{{$product['sku'] or ''}}</span>

                                  </div>
                    
                                  <div class="inpt-selects">
                                    <div class="price-bnd-dtls">Qty: 
                                      <span class="bold-tx-ordr">{{$product['qty']}}</span>
                                    </div>

                                    <div data-brand="{{ $product['maker_id'] or '' }}" data-brand-min="{{ $product['shop_settings']['first_order_minimum'] or '' }}" data-name="{{$product['maker_details']['company_name'] or ''}}"></div>

                                    <div class="price-bnd-dtls"> Price: 
                                      <span class="price-bnd-dtls">${{isset($product['unit_wholsale_price'])?num_format($product['unit_wholsale_price']) : 0.00}}</span>
                                    </div>

                                    <div class="price-bnd-dtls">Sub Total: 
                                      <span class="price-bnd-dtls">${{isset($product['wholesale_price'])?num_format($product['wholesale_price']) : 0}}</span>
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
                                   
                           /* if(isset($product['product_details']['product_discount']) && $product['product_details']['product_discount']!=0)
                            {
                               $total_product_discount_value += isset($product['product_details']['product_discount'])?$product['product_details']['product_discount']:'';
                            }       
                              

                            if(isset($product['product_details']['off_type_amount']) && $product['product_details']['off_type_amount']!='?' && $product['product_details']['off_type_amount']!="")
                            {
                               $total_shipping_discount_value += isset($product['product_details']['off_type_amount'])?$product['product_details']['off_type_amount']:'';
                            }   */


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
                             

                            /*-------------------------------------------------------------------------*/ 

                              
                                   
                             @endphp  

                              {{-- @if(isset($product['product_discount']) && $product['product_discount'] != 0) --}}

                              @if(isset($product['product_details']['product_dis_min_amt']) && $product['product_details']['product_dis_min_amt']!=0)
                             
                                 {{-- <div class="price-bnd-dtls">Min amount to get discount on the product : <span>$</span><span id="product_total_{{$product['sku']}}" data-brand-id="{{ $product['user_id'] or '' }}">{{isset($product['product_details']['product_dis_min_amt'])?num_format($product['product_details']['product_dis_min_amt']):0}}</span></div> --}}
                              @endif

                                 
                              @if(isset($product['product_details']['product_discount']) && $product['product_details']['product_discount']!=0)

                                <div class="inpt-selects">
                                  
                                    {{-- <div class="price-bnd-dtls">Product Discount {{$type or ''}}:
                                      <span class="price-bnd-dtls">${{isset($product['product_discount'])?num_format($product['product_discount']) :0.00}}</span>
                                    </div> --}}

                                  <div class="clearfix"></div>
                                </div>
                              @endif
                                    
                             {{--  @endif --}}


                              @if($product['product_details']['shipping_type'] == 1)

                                  @if(isset($product['product_details']['minimum_amount_off']) && $product['product_details']['minimum_amount_off'] > 0)
                                      {{-- <div class="price-bnd-dtls">Min Order Amount to get free shipping : <span>$</span><span id="product_total_{{$product['sku']}}" data-brand-id="{{ $product['user_id'] or '' }}">{{num_format($product['product_details']['minimum_amount_off'])}}</span></div> --}}
                                        
                                  @endif
                              @endif

                              @if($product['product_details']['shipping_type'] == 2 || $product['product_details']['shipping_type'] == 3)

                                @if(isset($product['product_details']['minimum_amount_off']) && $product['product_details']['minimum_amount_off'] > 0)
                                    {{-- <div class="price-bnd-dtls">Min Order Amount to get shipping discount : <span>$</span><span id="product_total_{{$product['sku']}}" data-brand-id="{{$product['user_id'] or '' }}">{{num_format($product['product_details']['minimum_amount_off'])}}</span></div> --}}
                                @endif    
                                
                              @endif 

                              @if(isset($product['product_shipping_charge']) && $product['product_shipping_charge'] != 0)
                                  <div class="inpt-selects">
                                      {{-- <div class="price-bnd-dtls">Shipping Charges:
                                        <span class="price-bnd-dtls">${{isset($product['product_shipping_charge'])?num_format($product['product_shipping_charge']) : 0}}</span>
                                      </div> --}}

                                      
                                    <div class="clearfix"></div>
                                  </div>
                              @endif

                              {{-- @if(isset($product['shipping_charges_discount']) && $product['shipping_charges_discount'] != 0) --}}

                              @if(isset($product['product_details']['off_type_amount']) && $product['product_details']['off_type_amount']!='?' && $product['product_details']['off_type_amount']!="")

                                  <div class="inpt-selects">
                                      {{-- <div class="price-bnd-dtls">Shipping Discount {{$ship_type or ''}}:
                                        <span class="price-bnd-dtls">${{isset($product['shipping_charges_discount'])?num_format($product['shipping_charges_discount']) :0.00}}</span>
                                      </div> --}}

                                    <div class="clearfix"></div>
                                  </div>

                              @endif
                                  
                               {{--  @endif --}}

                               
                              @php


                                $total_amount = 0;
                                $shipping_discount = isset($product['shipping_charges_discount'])?num_format($product['shipping_charges_discount']):0.00;

                                $total_amount = $product['wholesale_price']+$product['product_shipping_charge']-$shipping_discount-$product['product_discount'];


                              @endphp

                              @if(isset($total_amount) && $total_amount != 0)
                                <div class="inpt-selects">
                                  
                                    <div class="price-bnd-dtls">Total:
                                      <span class="price-bnd-dtls" id="product_total_{{$product['sku']}}" data-brand-id="{{ $product['maker_id'] }}">${{isset($total_amount)?num_format($total_amount) : 0}}</span>
                                    </div>

                                    
                                  
                                  <div class="clearfix"></div>
                                </div>
                              @endif

                            </div>
                           
                              @php 
                               /* $event = 'none';
                                if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 0){
                                  $event = '';
                                }*/
                              @endphp

                                <div class="main-qnt-bottom">
                                  <div class="qty-shop-bg">Qty : </div>
                                  <select name="pro_qty" class="pro_qty" {{-- style="pointer-events:{{$event}}" --}}  data-pro-id="{{base64_encode($product['product_id'])}}" data-pro-sku="{{base64_encode($product['sku'])}}">

                                   @php
                                    $product_min_qty = isset($product['get_product_min_qty']['product_min_qty']) && $product['get_product_min_qty']['product_min_qty']!=''?$product['get_product_min_qty']['product_min_qty']:1;
                                  @endphp
                                    
                                  @for($i=$product_min_qty;$i<=$product_max_qty_limit;$i++) 
                                    <option @if(isset($product[ 'qty']) && $product[ 'qty']==$i) selected="" @endif value="{{$i}}">{{$i}}</option>
                                  @endfor

                                  </select>   
                                </div>
                                <div class="clearfix"></div>


                                @php
                                  $sku = '';
                                  $sku = isset($product['sku'])?base64_encode($product['sku']):'';
                                @endphp


                                <a href="{{$module_url_path.'/delete_product_from_bucket_no/'.$order_no.'/'.$sku}}"  onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');" class="close-brnd"><i class="fa fa-times"></i></a>


                              {{-- 
                                <a href="{{$module_url_path.'/delete_product_from_bucket/'.$order_no.'/'.base64_encode($product['sku']) }}" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');" class="close-brnd"><i class="fa fa-times"></i></a> --}}


                              </div>
                            @endif  
                          @endforeach  
                         @endforeach  
                      
                      @else
                        No Record Found
                      @endif
                    </div>
                  
                  
                    <div class="">
                      <form id="validation-form" style="width:100%;"> 
                      {{ csrf_field() }}
                        <div class="row minusrow">
                        <div class="col-xs-12 col-sm-12 col-md- col-lg-6 order_summary">
                          @php 
                            $display = 'none';
                            if(isset($arr_data['is_confirm']) && $arr_data['is_confirm'] == 2){
                              $display = '';
                            }
                          @endphp
                          
                          <div class="ctomr-shipping-details space-retailer bottom-20"> Customer Shipping Details:</div>
      
                          @php
                              $ship_user_name = '';
                              $first_name = isset($arr_data['address_details']['ship_first_name'])?$arr_data['address_details']['ship_first_name']:'';

                              $last_name = isset($arr_data['address_details']['ship_last_name'])?$arr_data['address_details']['ship_last_name']:''; 

                              $ship_user_name = $first_name.' '.$last_name;
                          @endphp

                          <div class="postion-re">
                            <div class="postion-re-left">Name</div>
                              <div class="postion-reright">
                                <input type="input" name="ship_name" id="ship_name" class="form-control"  value="{{$ship_user_name or ''}}" readonly="">
                              </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left">Email</div>
                              <div class="postion-reright">
                                <input type="input" name="ship_email" id="ship_email" readonly="" class="form-control" value="{{isset($arr_data['address_details']['ship_email'])?$arr_data['address_details']['ship_email']:'N/A'}}">
                              </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                             {{--  <div class="postion-re-left"> Address</div>
                              <div class="postion-reright">

                                <input type="input" name="ship_complete_addr" id="ship_complete_addr" class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_complete_address'])?$arr_data['address_details']['ship_complete_address']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter address">
                              </div>
                              <div class="clearfix"> </div> --}}

                              <div class="postion-re-left">Street Address</div>
                              <div class="postion-reright">
                                 <input type="text" name="shipping_street_address"  class="form-control" placeholder="Street Address" id="shipping_street_address" data-parsley-required="true" row="5" data-parsley-required-message="Please enter street address" value="{{isset($arr_data['address_details']['ship_street_address'])?$arr_data['address_details']['ship_street_address']:''}}">
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
                            <div class="postion-re-left">Country</div>
                            <div class="postion-reright">
                              <select class="form-control" id="ship_country" name="ship_country" data-parsley-required="true" data-parsley-required-message="Please select country" placeholder="Select Country">
                                <option value="">Select Country</option>
                                  @if(isset($country_arr) && count($country_arr)>0)
                                    @foreach($country_arr as $key=>$country)

                                      <option @if(isset($arr_data['address_details']['ship_country']) && $arr_data['address_details']['ship_country']==$country['id']) selected="selected" @endif value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}
                                </option>

                                    @endforeach

                                  @endif  
                              </select>
                                     
                                <input type="hidden" name="hide_ship_country" id="hide_ship_country" value="">
                            </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left"> City</div>
                              <div class="postion-reright">
                                  <input type="input" name="ship_city" id="ship_city" class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_city'])?$arr_data['address_details']['ship_city']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter city" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                              </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left"> State</div>
                            <div class="postion-reright">
                               <input type="input" name="ship_state" id="ship_state" class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_state'])?$arr_data['address_details']['ship_state']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter state" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                            </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left">Mobile No</div>
                              <div class="postion-reright">
                                 <input type="input" name="ship_mobile_no" id="ship_mobile_no" class="form-control is-read-only" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number must be less than 18 digits" value="{{isset($arr_data['address_details']['ship_mobile_no'])?$arr_data['address_details']['ship_mobile_no']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter mobile number" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number">
                                 <input type="hidden" name="hid_ship_mobile_no_country_code" id="hid_ship_mobile_no_country_code">
                                </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                              <div class="postion-re-left"> Zip/Postal Code</div>
                              <div class="postion-reright">
                                 <input oninput="this.value = this.value.toUpperCase()" type="input" name="ship_zip_code" id="ship_zip_code" class="form-control is-read-only" value="{{isset($arr_data['address_details']['ship_zip_code'])?$arr_data['address_details']['ship_zip_code']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code">
                               <div id="err_shipping_zip_code" class="err_zip_code"></div>
                              </div>
                                    
                              <div class="clearfix"> </div>
                          </div>

       
                        </div>


                      <div class="col-xs-12 col-sm-12 col-md- col-lg-6 order_summary">

                          <div class="ctomr-shipping-details">Customer Billing Details:</div>

                          <div class="form-group">
                            <div class="form-check bd-example-indeterminate sales_billing_address_checkbox_new">
                               <label class="custom-control custom-checkbox">
                               <input type="checkbox" class="custom-control-input" id="same-as-billing" name="same_as_billing" value="1" @if(isset($arr_data['is_as_below']) && $arr_data['is_as_below']=="1") checked="" @endif>
                               <span class="custom-control-indicator"></span>
                               <span class="custom-control-description">Same as Shipping Details</span>
                               </label>
                            </div>
                          </div>

                          <div class="postion-re spacetopmargn">
                            <div class="postion-re-left">Name</div>
                            <div class="postion-reright">
                              @php
                                $first_name = isset($arr_data['address_details']['bill_first_name'])?$arr_data['address_details']['bill_first_name']:'';

                                $last_name = isset($arr_data['address_details']['bill_last_name'])?$arr_data['address_details']['bill_last_name']:''; 

                                $user_name = $first_name.' '.$last_name;
                              @endphp
                              <input type="input" name="bill_name" id="bill_name" readonly="" class="form-control" value="{{isset($user_name)?$user_name:''}}">
                            </div>

                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left">Email</div>
                            <div class="postion-reright">
                            <input type="input" name="bill_email" id="bill_email" readonly="" class="form-control" value="{{isset($arr_data['address_details']['bill_email'])?$arr_data['address_details']['bill_email']:'N/A'}}"></div>
                          </div>
                          <div class="clearfix"> </div>

                          <div class="postion-re">
                            {{-- <div class="postion-re-left">Address</div>
                            <div class="postion-reright">
                              <input type="input" name="bill_complete_addr" id="bill_complete_addr" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_complete_address'])?$arr_data['address_details']['bill_complete_address']:'N/A'}}" data-parsley-required="true"  data-parsley-required-message="Please enter address">
                            </div>
                            <div class="clearfix"> </div> --}}

                          <div class="postion-re-left">Street Address</div>
                              <div class="postion-reright">
                                 <input type="text" name="billing_street_address"  class="form-control" placeholder="Street Address" id="billing_street_address" data-parsley-required="true" row="5" data-parsley-required-message="Please enter street address" value="{{isset($arr_data['address_details']['bill_street_address'])?$arr_data['address_details']['bill_street_address']:''}}">
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
                            <div class="postion-re-left">Country</div>
                              <div class="postion-reright">
                                <select class="form-control" id="bill_country" name="bill_country" data-parsley-required="true" data-parsley-required-message="Please select country" placeholder="Select Country">

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
                              <div class="postion-re-left"> City</div>
                              <div class="postion-reright">
                                <input type="input" name="bill_city" id="bill_city" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_city'])?$arr_data['address_details']['bill_city']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter city"  data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                              </div>
                              <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left">State</div>
                            <div class="postion-reright">
                               <input type="input" name="bill_state" id="bill_state" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_state'])?$arr_data['address_details']['bill_state']:'N/A'}}" data-parsley-required="true" data-parsley-required-message="Please enter state" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                            </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                            <div class="postion-re-left">Mobile No</div>
                            <div class="postion-reright">
                              <input type="input" name="bill_mobile_no" id="bill_mobile_no"  class="form-control is-read-only" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required-message="Please enter mobile number" data-parsley-pattern-message="Please enter valid mobile number" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number must be less than 18 digits" data-parsley-required="true" 
                              value="{{isset($arr_data['address_details']['bill_mobile_no'])?$arr_data['address_details']['bill_mobile_no']:'N/A'}}">
                              <input type="hidden" name="hid_bill_mobile_no_country_code" id="hid_bill_mobile_no_country_code">
                             </div>
                            <div class="clearfix"> </div>
                          </div>

                          <div class="postion-re">
                              <div class="postion-re-left">Zip/Postal Code</div>
                              <div class="postion-reright">
                                 <input oninput="this.value = this.value.toUpperCase()" type="input" name="bill_zip" id="bill_zip" class="form-control is-read-only" value="{{isset($arr_data['address_details']['bill_zip_code'])?$arr_data['address_details']['bill_zip_code']:'N/A'}}"
                                   data-parsley-required="true" data-parsley-required-message="Please enter Zip/Postal code">
                              <div id="err_billing_zip_code" class="err_zip_code"></div>
                              </div>
                              <div class="clearfix"> </div>
                          </div>
                        </div>
                      </div>
                    <div class="col-md-12"></div>

                    <input type="hidden" name="order_no" value="{{isset($arr_data['order_no'])?$arr_data['order_no']:false}}">
                    <input type="hidden" name="bill_first_name" value="{{isset($arr_data['address_details'])? $arr_data['address_details']['bill_first_name']:false}}">
                    <input type="hidden" name="bill_last_name" value="{{isset($arr_data['address_details'])? $arr_data['address_details']['bill_last_name']:false}}">
                    <input type="hidden" name="ship_first_name" value="{{isset($arr_data['address_details']) ?$arr_data['address_details']['ship_first_name']:false}}">
                    <input type="hidden" name="ship_last_name" value="{{isset($arr_data['address_details'])? $arr_data['address_details']['ship_last_name']:false}}">
                  </form>
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
                         $Wholsale_sub_total = 0.00;

                         if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)
                         {
                            foreach($arr_data['order_details'] as $key => $product)
                            {
                               $total_shipping_charges = array_sum($total_shipping_charge);
                               $Wholsale_sub_total += $product['product_details']['unit_wholsale_price']*$product['qty'];
                            }
                         }
                      @endphp

                      <div class="ordr-total-ship">
                        <div class="ordr-total-ship-left"> Sub Total :</div>
                        <div class="ordr-total-ship-right"><span class="fa fa-dollar"></span>{{isset($Wholsale_sub_total)?num_format($Wholsale_sub_total):0.00}}
                       
                        </div>
                        <div class="clearfix"></div>
                      </div>

                      @if(isset($total_shipping_charges) && $total_shipping_charges > 0)

                        <div class="ordr-total-ship">
                        {{-- <div class="ordr-total-ship-left">Total Shipping Amount :</div>
                        <div class="ordr-total-ship-right"> @if(num_format($total_shipping_charges)>0)+@endif<span class="fa fa-dollar"></span>{{isset($total_shipping_charges)?num_format($total_shipping_charges):0}}
                        </div> --}}
                          <div class="clearfix"></div>
                        </div>
                      @endif
{{-- 
                      @if(isset($data['total_product_shipping_charges']) && $data['total_product_shipping_charges'] > 0)
                      
                        <div class="ordr-total-ship">
                        <div class="ordr-total-ship-left">Total Shipping Amount :</div>
                        <div class="ordr-total-ship-right"> @if(num_format($data['total_product_shipping_charges'])>0)+@endif<span class="fa fa-dollar"></span>{{isset($data['total_product_shipping_charges'])?num_format($data['total_product_shipping_charges']):0}}
                        </div>
                          <div class="clearfix"></div>
                        </div>

                      @endif --}}
                            
                      @php
                       /*calculate total shipping charges*/
                       $total_shipping_charges_discount = 0;
                       $Wholsale_sub_total = 0.00;

                       if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)
                       {
                          foreach($arr_data['order_details'] as $key => $product)
                          {
                              $total_shipping_charges_discount+= $product['shipping_charges_discount'];
                             $Wholsale_sub_total += $product['product_details']['unit_wholsale_price']*$product['qty'];
                          }
                       }
                      @endphp


                      @if(isset($total_shipping_charges_discount) && $total_shipping_charges_discount > 0)

                          <div class="ordr-total-ship">
                            {{-- <div class="ordr-total-ship-left">Total Shipping Discount --}} {{-- {{isset($total_ship_percent)?$total_ship_percent:''}}{{isset($total_ship_dollar)?$total_ship_dollar:''}} --}} {{-- :</div>
                            <div class="ordr-total-ship-right">@if(num_format($total_shipping_charges_discount)>0)-@endif<span class="fa fa-dollar"></span>{{isset($total_shipping_charges_discount)?num_format($total_shipping_charges_discount):0}} --}}
                          </div>
                            <div class="clearfix"></div>
                          </div>
                      @endif

                      @php
                         /*calculate total product discount*/
                         $total_product_discount = 0;
                         $Wholsale_sub_total = 0.00;
                         $total_product_discount_arr = [];

                         if(isset($arr_data['order_details']) && count($arr_data['order_details'])>0)
                         {
                            foreach($arr_data['order_details'] as $key => $product)
                            {   
                               array_push($total_product_discount_arr, $product['product_discount']);
                               
                               $Wholsale_sub_total += $product['product_details']['unit_wholsale_price']*$product['qty'];
                            }

                            $total_product_discount = array_sum($total_product_discount_arr);
                         }

                      @endphp


                      @if(isset($total_product_discount) && $total_product_discount > 0)
                        <div class="ordr-total-ship">
                        {{-- <div class="ordr-total-ship-left">Total Product Discount --}} {{-- {{isset($prod_dis_value_percent)?$prod_dis_value_percent:''}} {{isset($prod_dis_value_dollar)?$prod_dis_value_dollar:''}} --}} {{-- :</div>
                        <div class="ordr-total-ship-right">@if(num_format($total_product_discount)>0)-@endif <span class="fa fa-dollar"></span>{{isset($total_product_discount)?num_format($total_product_discount):0}} --}}
                        </div>
                          <div class="clearfix"></div>
                        </div>
                      @endif
                      <hr>

                      <?php

                        $total_wholesale_amt = 0.00;
                        if($arr_data['order_details'] && count($arr_data['order_details']) > 0)
                        {
                          $total_wholesale_amt = array_sum(array_column($arr_data['order_details'],'wholesale_price'));
                        }

                      ?>
                        
                      <div class="ordr-total-ship">
                        <div class="ordr-total-ship-left"> Total Amount   :</div>
                        <div class="ordr-total-ship-right"> <span class="fa fa-dollar"></span>

                          @php 
                           
                           $total_amount = $Wholsale_sub_total+$total_shipping_charges-$total_product_discount-num_format($total_shipping_charges_discount);

                          @endphp

                          {{isset($total_amount)?num_format($total_amount):0.00}}

                                
                        </div>
                        <div class="clearfix"></div>
                      </div>
                      <input type="hidden" name="order_no" id="order_no" value="{{isset($arr_data['order_no'])?$arr_data['order_no']:''}}">

                    </div>
                    @if(isset($arr_data['order_details']) && sizeof($arr_data['order_details'])>0)    
                       @if(isset($arr_data['is_confirm']) && ($arr_data['is_confirm'] == 4 || $arr_data['is_confirm'] == 0 || $arr_data['is_confirm'] == 3))
                      
                        <a  id="btn_save" class="btn btn-block btn-outline btn-rounded btn-success " href="{{ $module_url_path.'/finalize_lead/'.base64_encode($order_no).'?type=quote' }}">
                          <i class="fa fa-check-circle"></i> Save</a>
                                 
                          <a class="btn btn-block btn-outline btn-rounded btn-success confirmbtns-style" id="btn_confirm" onclick="return save_address();"> <i class="fa fa-check-circle"></i> Confirm</a>

                          <a class="btn btn-block btn-outline btn-rounded btn-success previousbtn-blk" href="{{$module_url_path}}/find_products/{{$order_no }}"> <i class="fa fa-arrow-left"></i> Previous</a>
                        @endif
                    @endif
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<script>
let module_url_path = "{{$module_url_path or ''}}";
  $(document).ready(function(){    
    //ship mobile number
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

//bill mobile number
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


  /*validation for zip code*/

  $("#ship_country").change(function(){

          var country_id    = $("#ship_country").val();
          var post_code = $("#ship_zip_code").val();

          var phone_code = $('option:selected', this).attr('phone_code');
          var zipcode_length = $('option:selected', this).attr('zipcode_length');
          var countryName = $('option:selected', this).attr('country_name');

          if (phone_code) {
              $("#ship_mobile_no").val('');
              $("#ship_mobile_no").val('+' + phone_code);
              $("#ship_mobile_no").attr('code_length', phone_code.length + 1);
              $("#hid_ship_mobile_no_country_code").val('+' + phone_code);
          } else {
              $("#ship_mobile_no").val('');
              $("#ship_mobile_no").attr(0);
              $("#hid_ship_mobile_no_country_code").val('');
          }

          if (country_id == "" && post_code != "") {
              $("#err_post_code").html('Invalid zip/postal code.');
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
                  '  characters',
              });
          }
          else{
              $('#ship_zip_code').attr('parsley-maxlength', false);
              $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
              $("#ship_zip_code").attr({
              "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
              "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                  zipcode_length +
                  '  digits'
              });
          }
         
          $('#address-frm').parsley();        
    });

    $("#bill_country").change(function(){

        var country_id    = $("#bill_country").val();
        var post_code = $("#bill_zip").val();

        var phone_code = $('option:selected', this).attr('phone_code');
        var zipcode_length = $('option:selected', this).attr('zipcode_length');
        var countryName = $('option:selected', this).attr('country_name');

        if (phone_code) {
            $("#bill_mobile_no").val('');
            $("#bill_mobile_no").val('+' + phone_code);
            $("#bill_mobile_no").attr('code_length', phone_code.length + 1);
            $("#hid_bill_mobile_no_country_code").val('+' + phone_code);
        } else {
            $("#bill_mobile_no").val('');
            $("#bill_mobile_no").attr(0);
            $("#hid_bill_mobile_no_country_code").val('');
        }

        if (country_id == "" && post_code != "") {
            $("#err_post_code").html('Invalid zip/postal code.');
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
                '  characters',
            });
        }
        else{
            $('#bill_zip').attr('parsley-maxlength', false);
            $('#bill_zip').attr('data-parsley-maxlength-message', "");
            $("#bill_zip").attr({
            "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
            "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                zipcode_length +
                '  digits'
            });
        }
                   
        $('#address-frm').parsley();         
    });

    jQuery('#btn_save').on("click", function(e){
      e.preventDefault();
      // Validate ship phone code and zip code
      var phone_code = $('#ship_country option:selected').attr('phone_code');
        var zipcode_length = $('#ship_country option:selected').attr('zipcode_length');
        var countryName = $('#ship_country option:selected').attr('country_name');

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
                '  characters',
            });
        }
        else{
            $('#ship_zip_code').attr('parsley-maxlength', false);
            $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
            $("#ship_zip_code").attr({
            "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
            "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                zipcode_length +
                '  digits'
            });
        }

        // Validate bill phone code and zip code
        var phone_code = $('#bill_country option:selected').attr('phone_code');
        var zipcode_length = $('#bill_country option:selected').attr('zipcode_length');
        var countryName = $('#bill_country option:selected').attr('country_name');

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
              '  characters',
          });
      }
      else{
          $('#bill_zip').attr('parsley-maxlength', false);
          $('#bill_zip').attr('data-parsley-maxlength-message', "");
          $("#bill_zip").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
      }

      if($('#validation-form').parsley().validate() == false)
      {
          return;
      }

      swal({
            title:"Need Confirmation",
            text: "Are you sure? You want to save this order.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "OK",
            closeOnConfirm: false
          },
          function(isConfirm,tmp)
          {
            if(isConfirm==true)
            {
              // save address 
              saveOrderSummaryAddress();
              var href = $("#btn_save").attr('href');
              window.location.href = href;
            }
          });
    })
  });

   $('.pro_qty').on('change', function() 
   {
      var qty     = this.value;
      var pro_id  = $(this).attr("data-pro-id");
      var pro_sku_id  = $(this).attr("data-pro-sku");
      var order_no = "{{ $order_no or 0}}";

      var url = '{{url('/')}}/sales_manager/leads/update_product_qty';

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
      var msg = msg || false;
    
      evt.preventDefault();  
      swal({
            title: "Need Confirmation",
            text: msg,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "OK",
            closeOnConfirm: false
          },
          function(isConfirm,tmp)
          {
            if(isConfirm==true)
            {
              checkPriceSatisfaction();
            }
          });
  }


function save_address()
{  
    /*$('#btn_confirm').on('click',function(){*/
       
       $("#err_billing_zip_code").html("");

       $("#err_billing_zip_code").html(""); 

       var ship_country  = $("#ship_country").val();
       var ship_zip      = $("#ship_zip_code").val();
       var bill_country  = $("#bill_country").val();
       var bill_zip      = $("#bill_zip").val();

      
        if(ship_country=="" && ship_zip!="")
        {
          $("#err_shipping_zip_code").html("Invalid zip/postal code.");
        }

        if(bill_country=="" && bill_zip!="")
        {
          $("#err_billing_zip_code").html("Invalid zip/postal code.");
        }

        // Validate ship phone code and zip code
        var phone_code = $('#ship_country option:selected').attr('phone_code');
        var zipcode_length = $('#ship_country option:selected').attr('zipcode_length');
        var countryName = $('#ship_country option:selected').attr('country_name');

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
                '  characters',
            });
        }
        else{
            $('#ship_zip_code').attr('parsley-maxlength', false);
            $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
            $("#ship_zip_code").attr({
            "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
            "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                zipcode_length +
                '  digits'
            });
        }

        // Validate bill phone code and zip code
        var phone_code = $('#bill_country option:selected').attr('phone_code');
        var zipcode_length = $('#bill_country option:selected').attr('zipcode_length');
        var countryName = $('#bill_country option:selected').attr('country_name');

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
              '  characters',
          });
      }
      else{
          $('#bill_zip').attr('parsley-maxlength', false);
          $('#bill_zip').attr('data-parsley-maxlength-message', "");
          $("#bill_zip").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
      }

      if($('#validation-form').parsley().validate() == false)
      {
          return;
      }
  // check minimum order 
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
          var _tmpBrandName = $(_elem).attr("data-name");

          _brandIdWiseDetails[_tmpBrandId] = {};               
          _brandIdWiseDetails[_tmpBrandId]['min'] = _tmpBrandMin;               
          _brandIdWiseDetails[_tmpBrandId]['name'] = _tmpBrandName;               
      });    
  }   

  /* Brand ID wise SubTotal */
  $.each(_arrProductRef,(_index,_elem) => { 
      var _tmpTotal = $(_elem).text();
      _tmpTotal = parseFloat(_tmpTotal.replace("$", ""));

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
              var total = parseFloat(_brandIdWiseSubTotal[_tmpBrandId]).toFixed(2);
              _minBrandMsg+='<li> <b>'+_brandIdWiseDetails[_tmpBrandId].name+' </b> <p>Minimum Required: $'+_brandIdWiseDetails[_tmpBrandId].min+'</p> Current Total: $'+total+'  </li>'
          }
      }
  }
  _minBrandMsg+='</ul>';

  if(_isValidForQuotes == false)
  {
      swal({
              title: "Note",
              text: "Please make sure that cart total for following brands satisfies with minimum amount.<br>"+_minBrandMsg,
              type: "warning",
              confirmButtonText: "OK",
              closeOnConfirm: false
          });
      return; 
  }     

          
     swal({
      title: "Need Confirmation",
      text: "Are you sure? Do you want to save this order as confirm order, after confirming you will not able to modify anything.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: '#DD6B55',
      confirmButtonText: 'OK',
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
     },
     function(isConfirm){

        if (isConfirm)
        {

            var bill_name               = $("#bill_name").val();
            var bill_email              = $("#bill_email").val();
            var bill_mobile_no          = $("#bill_mobile_no").val();
            var bill_complete_addr      = $("#bill_complete_addr").val();
            var bill_city               = $("#bill_city").val();
            var bill_state              = $("#bill_state").val();
            var bill_zip                = $("#bill_zip").val();
            var ship_name               = $("#ship_name").val();
            var ship_email              = $("#ship_email").val();
            var ship_mobile_no          = $("#ship_mobile_no").val();
            var ship_complete_addr      = $("#ship_complete_addr").val();
            var ship_city               = $("#ship_city").val();
            var ship_state              = $("#ship_state").val();
            var ship_zip_code           = $("#ship_zip_code").val();
            var order_no                = $("#order_no").val();

            var ship_street_address     = $('#shipping_street_address').val();
            var ship_suit_apt           = $('#shipping_suite_apt').val();

            var bill_street_address     = $('#billing_street_address').val();
            var bill_suit_apt           = $('#billing_suite_apt').val();

            var ship_country            = $("#ship_country").val();
            var bill_country            = $("#bill_country").val();

           
            $.ajax({
                url : '{{url('/')}}/sales_manager/leads/save_address',
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
                  "ship_country":ship_country,
                  "bill_country":bill_country

                },
                beforeSend: function() 
                {
                    showProcessingOverlay();                 
                },
                success: function(response) {
                  var url = '{{ $module_url_path.'/finalize_lead/'.base64_encode($order_no).'?type=confirm_requested' }}';
                  location.href=url;
                }             
            });
       }

    });

   // });
}


  // function checkPriceSatisfaction()
  // {
  //    /* Iterate All Products */
  //     var _arrProductRef = [];
  //     var _brandIdWiseSubTotal = {};
  //     var _brandIdWiseDetails = {};
  //     var _isValidForQuotes = true;
  //     var _minBrandMsg = "";

  //     if($('[id^=product_total_]').length > 0)
  //     {
  //         _arrProductRef = $('[id^=product_total_]');
  //     }
  //     else if($('[id^=product_wholesale_total]').length > 0)
  //     {
  //         _arrProductRef = $('[id^=product_wholesale_total]');   
  //     }
  //    /* Get Brand Details */
  //     if($('[data-brand]').length > 0)
  //     {
  //         var _arrBrand  = $('[data-brand]');

  //         $.each(_arrBrand,(_index,_elem) => {
  //             var _tmpBrandId = $(_elem).attr("data-brand");                
  //             var _tmpBrandMin = $(_elem).attr("data-brand-min");                
  //             var _tmpBrandName = $(_elem).attr('data-name');

  //             _brandIdWiseDetails[_tmpBrandId] = {};               
  //             _brandIdWiseDetails[_tmpBrandId]['min'] = _tmpBrandMin;  
  //             _brandIdWiseDetails[_tmpBrandId]['name'] = _tmpBrandName;               
  //         });
  //     }

  //    /* Brand ID wise SubTotal */
  //     $.each(_arrProductRef,(_index,_elem) => {
  //         var _tmpTotal = parseFloat($(_elem).html());

  //         var _tmpBrandId = $(_elem).attr("data-brand-id");
  //         if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
  //             _brandIdWiseSubTotal[_tmpBrandId] = 0;
  //         } 

  //         _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
  //     });
  
  //     _minBrandMsg+='<ul>';     
  //     for(let _tmpBrandId in _brandIdWiseSubTotal)
  //     {
  //         if(_brandIdWiseDetails[_tmpBrandId] != undefined)
  //         {
          
  //            if(_brandIdWiseSubTotal[_tmpBrandId] < _brandIdWiseDetails[_tmpBrandId].min )   
  //            {
  //                 _isValidForQuotes = false;

  //                 _minBrandMsg+='<li> <b>'+_brandIdWiseDetails[_tmpBrandId].name+' </b>: Minimum Required $'+_brandIdWiseDetails[_tmpBrandId].min+', Current Subtotal: $ '+_brandIdWiseSubTotal[_tmpBrandId]+'  </li>'
  //            }
  //         }
  //     }
  //     _minBrandMsg+='</ul>';

  //     if(_isValidForQuotes == false)
  //     {
  //       swal({
  //               title: "Note",
  //               text: "Please make sure cart total for following brands satisfies with min. amount<br>"+_minBrandMsg,
  //               type: "warning",
  //               confirmButtonText: "OK",
  //               closeOnConfirm: false
  //           });
  //       return; 
  //     }
  //     else
  //     {
  //       var url = '{{ $module_url_path.'/finalize_lead/'.base64_encode($order_no).'?type=confirm_requested' }}';

  //       location.href=url;
  //     }
  // }

</script>
  <script type="text/javascript" src="{{url('assets/js/module_js/representative/leads.js')}}"></script>
  <script>
  $(document).ready(function() {
    $('.content, .rightSidebar')
      .theiaStickySidebar({
        additionalMarginTop: 30
      });
  });
  </script>
@stop