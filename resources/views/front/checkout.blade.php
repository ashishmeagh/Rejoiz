@extends('front.layout.master')
@section('main_content')  


<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.2.0/css/datepicker.min.css" rel="stylesheet"> -->

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> -->


<!-- Datepicker css-->
<script src="{{url('/')}}/assets/front/js/theia-sticky-sidebar.js"></script>
{{-- 
<script src="{{url('/assets/js/card/jquery.card.js')}}"></script> --}}

<style>
  .dropdown-menu {
    position: static;
  }

  .jp-card .jp-card-front .jp-card-lower {
    width: auto;
  }

  .point-event-d {
    pointer-events: none;
  }

  .point-event-d input {
    background-color: #ccc !important;
  }

  .point-event-d select {
    background-color: #ccc !important;
  }

  .net-dv-mn.samesize-min .buttonshopfull {
    padding: 10px 30px;
    margin: 0 5px;
  }

  .bag-detailstitle {
    font-size: 13px;
    font-weight: 600;
  }

  .baglist-sub {
    margin-bottom: 2px;
    font-size: 12px;
    color: #555;
  }

  .clone-bag-detials {
    background-color: #E0E4E7;
    padding: 10px;
    border-radius: 3px;
  }

  .same_as_shipping_check {
    position: absolute !important;
    left: unset !important;
    visibility: visible !important;
  }

  .btn-place-order{
    margin-top:10px;
  }

  .buttonshopfull{
    padding : 6px 30px !important;
  }
  .checkout-bx{
    background-color: white;
  }
  .influencer-box{
    margin-top: 35px;
  }

  /*.ui-datepicker-calendar {
  display: none;
}
button.ui-datepicker-current { display: none; }*/
</style>

<div class="main-checkout-divs">
  <div class="container">
    <div class="checkout-main-left-right">
      <div class="amt-totl"><span>Order Checkout</div>
      <div class="totle-amout">

        @php
        $promo_shipping_charges = 1;
        /*get session data*/
        $session_promotion_data = [];
        $shipping_charges = isset($shipping_charges)?$shipping_charges:0;
        $promotion_discount = 0;

        $userSegment = Request::segment(2);


        @endphp

        {{-- If $oderCaculationData is empty then total amount below is calculated through javascript function below --}}
        <span>Total Amount:</span> <span id="_totalAmt">${{isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : ''}}</span>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="row">
      @php
      if(isset($status_net_30) && $status_net_30 == '0')
      {
      $display = 'none';
      $cols = '3';
      }
      @endphp

      <div class="col-md-8 content">
        <form id="frm-card" method="post">
          <div class="checkout-main-div-nw mg-front-mgt checkout-bx">
            <div class="demo-container">
              <div class="row">
                <div class="col-md-12">
                  <div class="user-box space-b-o influencer-box">
                    <label class="form-lable" for="name">Influencer Code</label>
                    <input type="text" placeholder="Influencer Code" class="cont-frm text-checkout" name="influencer_code" id="influencer_code" value="">
                  </div>
                  <div class="clearfix"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="checkout-main-div-nw mg-front-mgt checkout-bx-white">
            <div class="demo-container">
              <div class="row">
                <div class="col-md-12">
                  <h2>Address</h2>

                  <div class="clearfix"></div>
                </div>
                <div class="col-md-12">

                  <div @if(Session::has('representative_order_id')==true) class="point-event-on point-event-d" @else class="point-event-on" @endif>
                    <div class="mybag-list-left checkout-adrs">
                      <h5>Shipping Address</h5>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">Street Address<i class="red">*</i></label>
                          <input type="text" placeholder="Street Address" class="cont-frm text-checkout" name="shipping_addr" id="shipping_addr" value="{{$retailer_shipping_addr['shipping_addr'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter street address.">
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="shipping_suit_apt">Suit/Apt</label>
                          <input type="text" placeholder="Suit/Apt" class="cont-frm text-checkout" name="shipping_suit_apt" id="shipping_suit_apt" value="{{$retailer_shipping_addr['shipping_suit_apt'] or ''}}">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">City<i class="red">*</i></label>
                          <input type="text" placeholder="City" class="cont-frm" name="shipping_city" id="shipping_city" value="{{$retailer_shipping_addr['shipping_city'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter city.">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">State<i class="red">*</i></label>
                          <input type="text" placeholder="State" class="cont-frm" name="shipping_state" id="shipping_state" value="{{$retailer_shipping_addr['shipping_state'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter state.">
                        </div>
                      </div>


                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">Country<i class="red">*</i></label>
                          <select class="form-control" id="shipping_country" name="shipping_country" data-parsley-required="true" data-parsley-required-message="Please select country.">
                            <option value="">Select Country</option>

                            @if(isset($country_data) && count($country_data)>0)
                            @foreach($country_data as $key=>$country)
                            <option value="{{$country['id']}}" @if($country['id']==$retailer_shipping_addr['shipping_country']) selected @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>
                            @endforeach
                            @endif
                          </select>
                        </div>
                        <span class='red'>{{ $errors->first('shipping_country') }}</span>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="ship_contact_no">Mobile No.<i class="red">*</i></label>
                          <input type="text" name="ship_contact_no" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter mobile number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." placeholder="Mobile No." id="ship_contact_no" value="{{$retailer_shipping_addr['ship_contact_no'] or ''}}" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits.">
                          <input type="hidden" name="hid_ship_contact_no_country_code" id="hid_ship_contact_no_country_code">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">                        
                          <label class="form-lable" for="name">Zip/Postal Code<i class="red">*</i></label>                          
                          <input oninput="this.value = this.value.toUpperCase()" type="text" name="shipping_zip_postal_code" class="cont-frm" placeholder="Zip Code" id="shipping_zip_postal_code" value="{{ $retailer_shipping_addr['shipping_zip_postal_code'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code." data-parsley-trigger="change">
                          <span class='red'>{{ $errors->first('shipping_zip_postal_code') }}</span>
                        </div>
                      </div>
                    </div>

                    <div class="mybag-list-left checkout-adrs">
                      <h5>Billing Address</h5>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">

                      <div class="col-md-12">
                        <div class="user-box space-b-o same_shipping_address" id="checkbox_same_ship_address">
                          <label class="form-lable" for="same_as_shipping_check"><input type="checkbox" name="same_as_shipping_check" id="same_as_shipping_check" class="same_as_shipping_check">
                            <span>Same as Shipping Address</span>
                          </label>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">Street Address<i class="red">*</i></label>
                          <input type="text" placeholder="Street Address" class="cont-frm text-checkout" name="billing_addr" value="{{$retailer_billing_addr['billing_addr'] or ''}}" id="billing_addr" data-parsley-required="true" data-parsley-required-message="Please enter street address.">
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="billing_suit_apt">Suit/Apt</label>
                          <input type="text" placeholder="Suit/Apt" class="cont-frm text-checkout" name="billing_suit_apt" id="billing_suit_apt" value="{{$retailer_billing_addr['billing_suit_apt'] or ''}}">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">City<i class="red">*</i></label>
                          <input type="text" placeholder="City" class="cont-frm" name="billing_city" id="billing_city" value="{{ $retailer_billing_addr['billing_city'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter city.">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">State<i class="red">*</i></label>
                          <input type="text" placeholder="State" class="cont-frm" name="billing_state" id="billing_state" value="{{ $retailer_billing_addr['billing_state'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter state.">
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">Country<i class="red">*</i></label>
                          <select class="form-control" id="billing_country" name="billing_country" data-parsley-required="true" data-parsley-required-message="Please select country.">
                            <option value="">Select Country</option>
                            @if(isset($country_data) && count($country_data)>0)
                            @foreach($country_data as $key=>$country)
                            <option value="{{$country['id']}}" @if($country['id']==$retailer_billing_addr['billing_country']) selected @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>
                            @endforeach
                            @endif
                          </select>
                        </div>
                        <span class='red'>{{ $errors->first('billing_country') }}</span>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="bill_contact_no">Mobile No.<i class="red">*</i></label>
                          <input type="text" name="bill_contact_no" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter mobile number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." placeholder="Mobile No." id="bill_contact_no" value="{{$retailer_billing_addr['bill_contact_no'] or ''}}" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits.">
                          <input type="hidden" name="hid_bill_contact_no_country_code" id="hid_bill_contact_no_country_code">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="user-box space-b-o">
                          <label class="form-lable" for="name">Zip/Postal Code<i class="red">*</i></label>
                          <input oninput="this.value = this.value.toUpperCase()" type="text" name="billing_zip_postal_code" id="billing_zip_postal_code" class="form-control" placeholder="Zip Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code." data-parsley-trigger="change" value="{{$retailer_billing_addr['billing_zip_postal_code'] or ''}}">
                        </div>
                        <span class='red'>{{ $errors->first('post_code') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                 {{--  <div class="demo-container checkoutside"> --}}
                   <div class="">
                    {{-- <div class="card-wrapper checkout-lft-fift"></div> --}}
                    <div class="form-container active checkout-forms">
                     
                     {{--  @include('front.layout.flash_messages')
                      <div class="user-box">
                        <div class="select-style">
                          <select onchange=" return setCard(this); " class="form-control" @php $disabled='none' ; if(isset($arr_card) && count($arr_card)> 0 )
                            {
                            $disabled = 'block';
                            }
                            @endphp

                            style="display:{{$disabled}}" >
                            <option value="">-- Select Card --</option>

                            @if(isset($arr_card) && count($arr_card) > 0)

                            @foreach($arr_card as $card)
                            @php
                            $card_expiry_month = isset($card['exp_month'])?$card['exp_month']:'';
                            $card_expiry_year = isset($card['exp_year'])?$card['exp_year']:'';
                            $stripe_card_id = isset($card['stripe_card_id'])?$card['stripe_card_id']:'';
                            $customer_id = isset($card['customer_id'])?$card['customer_id']:'';
                            $card_no = isset($card['card_no'])?str_pad($card['card_no'], 16, "X", STR_PAD_LEFT):'';
                            $card_expiry = $card_expiry_month.' / '.$card_expiry_year;

                            @endphp

                            <option data-card-id="{{$stripe_card_id or ''}}" data-customer-id="{{$customer_id or ''}}">{{$card_no or ''}}</option>
                            @endforeach
                            @endif

                          </select>
                        </div>
                      </div>


                      <div class="user-box">

                        {{csrf_field()}}


                        <input autocomplete='off' class='form-control card-number' type='tel' name="number" data-parsley-required="true" data-parsley-required-message="Please enter card number." data-parsley-creditcard="true" id="card_no" placeholder="Enter card number">

                      </div>


                      <div class="row">

                        <div class="col-md-6">
                          <div class="user-box">
                            <input type="text" name="expiry" data-parsley-required="true" data-parsley-required-message="Please enter card expiry date." class=" form-control" data-date-format="MM/YYYY" id="expiry" placeholder="MM / YYYY"> 
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="user-box">
                            <input type="password" name="cvc" maxlength="4" id="cvv" placeholder="CVV" data-parsley-required="true" data-parsley-required-message="Please enter CVV." class="form-control" />
                          </div>
                        </div>

                      </div>
                      <input type="hidden" name="is_exists" id="is_exists" value="">
                      <input type="hidden" name="payment_type" id="payment_type" value="1">

                      <input type="button" name="btn_pay" id="btn_pay" class="btn buttonshopfull form-control" value="Pay" />
                      <div class="clearfix"></div> --}}
       
      </div>
      <div class="clearfix"></div>
    </div>
  </div>

</div>
</div>

<div class="clearfix"></div>
</div>
<!--<div class="or-txtmain"><span>OR</span></div>
<div class="net-dv-mn samesize-min">
  <div class="text-center">

    @php
    $user = Sentinel::getUser();

    $check_net30_status = $user->status_net_30;



    @endphp
    @if($check_net30_status == 1)

    @if(isset($payment_term) && $payment_term == 'Net30')

    <button name="btn_net" id="btn_net" class="buttonshopfull checkout-btn" value="Net30" style="display: none;">Net30</button>

    @else

    <button name="btn_net" id="btn_net" class="buttonshopfull checkout-btn" value="Net30">Net30</button>

    @endif

    @endif

    <button class="buttonshopfull checkout-btn" onclick="swal('Warning',' Account not set with fundbox yet, for details please reach out our representatives.','warning')">Fundbox Pay</button>
    <button class="buttonshopfull checkout-btn" onclick="swal('Warning','Account not set on webbank yet, for details please reach out our representatives.','warning')">WebBank</button>

  </div>
</div> -->
</div>
<div class="col-md-4 rightbagsidebar">
  <div class="theiaStickySidebar">

    <div class="bag-details">
      <h2>Product Summary</h2>
      <div class="innerbag-details">
        @php
        $shipping_charges = $repPanelShipDiscount = $_grandAmount = $shipping_charg = $ship_discount = $total_ship_discount_value = $total_product_dis_value = $total_ship_discount_value_perc = $total_ship_discount_value_dollar = $total_product_dis_value_perc = $total_product_dis_value_dollar = 0;

        $total_ship_percent = $total_ship_dollar = $prod_dis_value_dollar = $prod_dis_value_percent = $total_ship_percent = $total_ship_dollar =
        $prod_dis_value_dollar = $prod_dis_value_percent = $ship_dis_val = $ship_dis_val = $prod_dis_value = $prod_dis_value ='';

        $_subTotal = $_prodDiscount = $_shippingCharges = $_shipDiscount = $is_shipping_charg = 0;

        @endphp
        @if(isset($arr_final_data) && count($arr_final_data)>0)

        @foreach($arr_final_data as $key => $product_details)

        @if(isset($product_details['product_details']) && count($product_details['product_details'])>0)

        @foreach($product_details['product_details'] as $arr_product_data)

        @php
        //dd($arr_final_data);;
//dd($arr_product_data);
        $product_shipping_charg = isset($arr_product_data['shipping_charges'])?num_format($arr_product_data['shipping_charges']):'0.00';


        $repPanelShipDiscount += isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):'0.00';


        if(is_array(Session::get('promotion_data')) && Session::get('promotion_data') != "" && count(Session::get('promotion_data')) > 0)
        {

        $session_promotion_data = Session::get('promotion_data');

        $promo_shipping_charges = isset($session_promotion_data[$key]['final_total'][$key]['shipping_charges'])?$session_promotion_data[$key]['final_total'][$key]['shipping_charges']:false;


        $promo_discount_amt = isset($session_promotion_data[$key]['final_total'][$key]['discount_amt'])?$session_promotion_data[$key]['final_total'][$key]['discount_amt']:0.00;

        /* if promo code type is discount off */
        if($promo_shipping_charges == false)
        {
        $shipping_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):'0.00';

        if($role == 'representative' || $role == 'sale_manager')
        {
        $shipping_charg = $product_shipping_charg;
        }
        else
        {
          //$shipping_charg = $product_shipping_charg + $shipping_discount;
          $shipping_charg = $product_shipping_charg;
        }

          $ship_discount = $shipping_discount;
          $shipping_charges += $shipping_charg;
        }
        /*------------------------------------------------------*/

        /* if promo code type is Free shipping */
        if($promo_shipping_charges == 0)
        {
           $shipping_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):'0.00';

        if($role == 'representative' || $role == 'sale_manager')
        {
          $is_shipping_charg = $product_shipping_charg;
        }
        else
        {
          // $is_shipping_charg = $product_shipping_charg + $shipping_discount;
           $is_shipping_charg = $product_shipping_charg;
        }

          $shipping_charges += $is_shipping_charg;
        }
        /*------------------------------------------------------*/

        /* if promo code type have free shipping and % off */
        if($promo_shipping_charges === 0 && $promo_discount_amt !== 0 )
        {
           
          /* $shipping_charg = $shipping_charg - $product_shipping_charg - $shipping_discount;

           $ship_discount = $shipping_discount - $shipping_discount;*/

            $shipping_charg = 0;
            $ship_discount  = 0;

        }

        }
        else
        {
          $ship_discount = isset($arr_product_data['shipping_discount'])?num_format($arr_product_data['shipping_discount']):0.00;


          if($role == 'representative' || $role == 'sale_manager')
          {
            $shipping_charg = $product_shipping_charg;
          }
          else
          {
            //$shipping_charg = $product_shipping_charg + $ship_discount;
            
            $shipping_charg = $product_shipping_charg;
          }

          $shipping_charges += $is_shipping_charg;

        }


        $prod_discount = isset($arr_product_data['product_dis amount'])?num_format($arr_product_data['product_dis amount']):'0.00';

        $item_qty = isset($arr_product_data['item_qty'])?$arr_product_data['item_qty']:0;

        $unit_price = isset($arr_product_data['wholesale_price'])?$arr_product_data['wholesale_price']:0;


        $sub_total = $item_qty*$unit_price;

        $total_amt = ($sub_total)+($shipping_charg)-($prod_discount)-($ship_discount);

        $_subTotal += $sub_total;
        $_prodDiscount += $prod_discount;
        $_shippingCharges += $shipping_charg;
        $_shipDiscount += $ship_discount;
        $_grandAmount += $total_amt;

        @endphp

        <div class="clone-bag-detials">

          <div class="bag-detailstitle">Product Name : {{isset($arr_product_data['product_name'])?$arr_product_data['product_name']:'N/A'}}</div>

          @if(isset($arr_product_data['color']) && $arr_product_data['color'] != "")
          <div class="baglist-sub">Color : <span>{{isset($arr_product_data['color'])?$arr_product_data['color']:'-'}}</span></div>
          @endif
          @if(isset($arr_product_data['size_id']) && $arr_product_data['size_id'] != "")
          @php
              $size = get_size_from_id($arr_product_data['size_id']); 
          @endphp
          <div class="baglist-sub">Size : <span>{{$size}}</span></div>
          @endif

          <div class="baglist-sub">SKU No: <span>{{isset($arr_product_data['sku_no'])?$arr_product_data['sku_no']:'-'}}</span></div>

          <div class="baglist-sub">Sub Total : <span>${{isset($sub_total)?num_format($sub_total):'0.00'}}</span></div>



          {{-- @if(isset($shipping_charg) && $shipping_charg != 0) --}}
          @if(($shipping_charg != 0 || $shipping_charg == 0)
          &&(isset($arr_product_data['shipping_charges']) && ($arr_product_data['shipping_charges']!=0)))

          {{-- <div class="baglist-sub">Shipping Charges : <span>
              @if($shipping_charg > 0) +@endif${{isset($shipping_charg)?num_format($shipping_charg):'0.00'}}</span></div> --}}

          @endif

          {{-- @if(isset($ship_discount) && $ship_discount != 0) --}}
          @php
          $ship_off_type = isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:$arr_product_data['product_details']['off_type_amount'];

          $product_discount_value = isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:$arr_product_data['product_details']['product_discount'];


          /*-----------------total discounts --------------------*/
          if(($ship_discount==0 || $ship_discount!=0) && ($ship_off_type!='?' && $ship_off_type!=""))
          {
          /* $total_ship_discount_value += isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:$arr_product_data['product_details']['off_type_amount'];*/

          if((isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 2) || ((isset($arr_product_data['product_details']['shipping_type']) && $arr_product_data['product_details']['shipping_type'] == 2)))
          {
          $total_ship_discount_value_perc += isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:$arr_product_data['product_details']['off_type_amount'];

          $total_ship_percent = '('.$total_ship_discount_value_perc.'%)';
          }
          elseif((isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 3) || (isset($arr_product_data['product_details']['shipping_type']) && $arr_product_data['product_details']['shipping_type'] == 3))
          {
          $total_ship_discount_value_dollar += isset($arr_product_data['off_type_amount'])?$arr_product_data['off_type_amount']:$arr_product_data['product_details']['off_type_amount'];

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
          }*/


          if(($prod_discount==0 || $prod_discount!=0) && ($product_discount_value!=0))
          {

          /*$total_product_dis_value += isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:$arr_product_data['product_details']['product_discount'];*/

          if((isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 2) || (isset($arr_product_data['product_details']['prodduct_dis_type']) && $arr_product_data['product_details']['prodduct_dis_type'] == 2))
          {
          $total_product_dis_value_perc += isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:$arr_product_data['product_details']['product_discount'];

          $prod_dis_value_dollar = '($'.$total_product_dis_value_perc.')';
          }
          elseif ((isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 1) || (isset($arr_product_data['product_details']['prodduct_dis_type']) && $arr_product_data['product_details']['prodduct_dis_type'] == 1))
          {
          $total_product_dis_value_dollar += isset($arr_product_data['product_discount_value'])?$arr_product_data['product_discount_value']:$arr_product_data['product_details']['product_discount'];

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

          /*---------------------------------------------------------*/

          if((isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 2) || (isset($arr_product_data['product_details']['shipping_type']) && $arr_product_data['product_details']['shipping_type'] == 2))
          {
          if(isset($arr_product_data['off_type_amount']))
          {
          $ship_dis_val = '('.$arr_product_data['off_type_amount'].'%)';
          }
          else
          {
          $ship_dis_val = '('.$arr_product_data['product_details']['off_type_amount'].'%)';
          }

          }
          elseif((isset($arr_product_data['shipping_type']) && $arr_product_data['shipping_type'] == 3) || (isset($arr_product_data['product_details']['shipping_type']) && $arr_product_data['product_details']['shipping_type'] == 3))
          {
          if(isset($arr_product_data['off_type_amount']))
          {
          $ship_dis_val = '($'.$arr_product_data['off_type_amount'].')';
          }
          else
          {
          $ship_dis_val = '($'.$arr_product_data['product_details']['off_type_amount'].')';
          }

          }
          else
          {
          $ship_dis_val = '';
          }

          @endphp

          @if(($ship_discount==0 || $ship_discount!=0) && ($ship_off_type!='?' && $ship_off_type!=""))

          {{-- <div class="baglist-sub">Shipping Discount {{$ship_dis_val or ''}} : <span>@if($ship_discount>0)-@endif${{isset($ship_discount)?$ship_discount:'0.00'}}</span></div> --}}

          @endif

          {{-- @endif
 --}}

          {{-- @if(isset($prod_discount) && $prod_discount != 0) --}}

          @php
          $prod_dis_value = '';

          if((isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 1) || (isset($arr_product_data['product_details']['prodduct_dis_type']) && $arr_product_data['product_details']['prodduct_dis_type'] == 1))
          {
          if(isset($arr_product_data['product_discount_value']))
          {
          $prod_dis_value = '('.$arr_product_data['product_discount_value'].'%)';
          }
          else
          {
          $prod_dis_value = '('.$arr_product_data['product_details']['product_discount'].'%)';
          }

          }
          elseif((isset($arr_product_data['prodduct_dis_type']) && $arr_product_data['prodduct_dis_type'] == 2) || (isset($arr_product_data['product_details']['prodduct_dis_type']) && $arr_product_data['product_details']['prodduct_dis_type'] == 2))
          {
          if(isset($arr_product_data['product_discount_value']))
          {
          $prod_dis_value = '($'.$arr_product_data['product_discount_value'].')';
          }
          else
          {
          $prod_dis_value = '($'.$arr_product_data['product_details']['product_discount'].')';
          }

          }
          else
          {
          $prod_dis_value = '';
          }

          @endphp

          @if(($prod_discount==0 || $prod_discount!=0) && ($product_discount_value!=0))

          {{-- <div class="baglist-sub">Product Discount {{$prod_dis_value or ''}} : <span>@if($prod_discount>0)-@endif${{isset($prod_discount)?$prod_discount:0.00}}</span></div> --}}

          @endif

          {{-- @endif --}}



          <strong>
            <div class="baglist-sub">Total : <span>${{isset($total_amt)?num_format($total_amt):'0.00'}}</span></div>
          </strong>
        </div>

        <?php
        if ($role == 'representative' || $role == 'sale_manager') {
          $ship_discount += $repPanelShipDiscount;
        }
        ?>
        @endforeach
        @endif

        @endforeach
        @endif

      </div>
    </div>


    <div class="price-det-block">
      <?php
      if (isset($orderCalculationData) && count($orderCalculationData) == 0) {
        $orderCalculationData['sub_total'] =  $_subTotal;
        $orderCalculationData['product_discount'] = $_prodDiscount;
        $orderCalculationData['ship_charges'] =  $_shippingCharges;
        $orderCalculationData['ship_discount'] =  $_shipDiscount;
        $orderCalculationData['discount_amt'] =  0;
        $orderCalculationData['grand_total'] =  $_grandAmount;

        if (is_array(Session::get('promotion_data')) && Session::get('promotion_data') != "" && count(Session::get('promotion_data')) > 0) {

          $shipDiff = $_shippingCharges - $_shipDiscount;

          $session_promotion_data = Session::get('promotion_data');

          $promo_discount_amt = 0.00;

          if (isset($session_promotion_data) && count($session_promotion_data) > 0) {
            foreach ($session_promotion_data as $promoKey => $promotion) {
              $promo_shipping_charges = isset($promotion[$promoKey]['final_total'][$promoKey]['shipping_charges']) ? $promotion[$promoKey]['final_total'][$promoKey]['shipping_charges'] : 1;

              $promo_discount_amt += isset($promotion['final_total'][$promoKey]['discount_amt']) ? $promotion['final_total'][$promoKey]['discount_amt'] : 0;

              $orderCalculationData['discount_amt'] =  $promo_discount_amt;
            }
          }



          if ($promo_shipping_charges == 0 && $promo_discount_amt) {

            /* if promotion discount type is freeshipping and  % off */
            $orderCalculationData['grand_total'] = $_subTotal - $_prodDiscount - $promo_discount_amt;

            $orderCalculationData['promotion_shipping_charges'] = $shipDiff;
          } elseif ($promo_shipping_charges == 0) {

            /* $promo_shipping_charges == 0 then order type is free shipping */
            $shipDiff = $_shippingCharges - $_shipDiscount;

            $orderCalculationData['grand_total'] = $orderCalculationData['grand_total'] - $shipDiff;

            $orderCalculationData['promotion_shipping_charges'] = $shipDiff;
          } else {

            $orderCalculationData['grand_total'] = $_subTotal - $_prodDiscount + $_shippingCharges - $_shipDiscount - $promo_discount_amt;
          }
        }
      }

      ?>

      <h2>Order Details</h2>
      <div class="order-sum">
        @if(isset($promo_code_slug) && $promo_code_slug != '')
            <h5>Promo code : {{$promo_code_slug or ''}}</h5>
        @endif
        <h5>Subtotal

          <span id="wholesale_subtotal" class="subtotal">{{isset($orderCalculationData['sub_total'])?num_format($orderCalculationData['sub_total']) : 0.00}}</span>
          <span>$</span></h5>

        @php
        $user = Sentinel::check();

        if($user)
        {
        $user_data = $user;

        $shipping_addr = $user->shipping_addr;
        $shipping_zip_code =$user->shipping_addr_zip_code;

        $billing_addr = $user->billing_addr;
        $billing_zip_code =$user->billing_addr_zip_code;
        }

        $product_discount = isset($product_discount)?$product_discount:0.00;
        $promo_shipping_charges = Session::get('promo_shipping_charges');
        $promo_shipping_discount = Session::get('shipping_discount');


        @endphp

        @if(isset($orderCalculationData['product_discount']) && $orderCalculationData['product_discount'] != 0)
        <div class="mybag-list">
          <div class="mybag-list-left">{{-- Product Discount {{isset($prod_dis_value_percent)?$prod_dis_value_percent:''}} {{isset($prod_dis_value_dollar)?$prod_dis_value_dollar:''}}--}}</div>

          <div class="mybag-list-right">{{-- @if($product_discount>0)-@endif${{isset($orderCalculationData['product_discount'])?num_format($orderCalculationData['product_discount']):0.00}} --}}</div>
          <div class="clearfix"></div> 
        </div>
        @endif



        <!-- if promotion shipping is available  -->
        @if(!isset($orderCalculationData['promotion_shipping_charges']))

        @if(isset($orderCalculationData['ship_charges']) && $orderCalculationData['ship_charges'] != 0)
        <div class="mybag-list">
          <div class="mybag-list-left">{{-- Shipping Charges --}}</div>
          <div class="mybag-list-right">{{-- @if($orderCalculationData['ship_charges']>0)+@endif${{isset($orderCalculationData['ship_charges'])?num_format($orderCalculationData['ship_charges']):0}} --}}</div>
          <div class="clearfix"></div>
        </div>
        @endif

        @if(isset($orderCalculationData['ship_discount']) && $orderCalculationData['ship_discount'] != 0)

        <div class="mybag-list">
          <div class="mybag-list-left">{{-- Shipping Discount {{isset($total_ship_percent)?$total_ship_percent:''}}{{isset($total_ship_dollar)?$total_ship_dollar:''}} --}} </div>
          <div class="mybag-list-right">{{-- @if($orderCalculationData['ship_discount']>0)-@endif${{isset($orderCalculationData['ship_discount'])?num_format($orderCalculationData['ship_discount']):0}} --}}</div>

          <div class="clearfix"></div>
        </div>
        @endif
        @endif


        @if(isset($orderCalculationData['discount_amt']) && $orderCalculationData['discount_amt'] != 0)

        <div class="mybag-list">
          <div class="mybag-list-left">{{-- Promotion Discount --}}</div>
          <div class="mybag-list-right">{{-- @if($orderCalculationData['discount_amt']>0)-@endif${{num_format($orderCalculationData['discount_amt'])}} --}}</div>

          <div class="clearfix"></div>
        </div>

        @endif


        <hr>
        <h5>Order Total
          <span id="wholesale_total">{{isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : ''}}</span>
          <span>$</span>
        </h5>
        {{Session::put('amount',$amount)}}
        <div class="clearfix"></div>
      </div>

     

    </div>
     <div class="btn-place-order">
         <input type="button" name="btn_pay" id="btn_place_order" class="btn buttonshopfull form-control" value="Place Order" />
      </div>
  </div>

</div>

<input type="hidden" name="representative_order_id" id="representative_order_id" value="{{Session::get('representative_order_id')}}">

 </form>
</div>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.2.0/js/bootstrap-datepicker.min.js"></script>
<script>
  var startDate = new Date();

/*  $('#frm-card').card({
    container: '.card-wrapper', 
  });
*/
  var representative_order_id = $('#representative_order_id').val();
  var address_arr = <?php echo json_encode($address_arr); ?>;

  $(document).ready(function() {
    // check whether the shipping and billing address are same or not
    var get_shipping_addr = $("#shipping_addr").val();
    var get_shipping_city = $("#shipping_city").val();
    var get_shipping_state = $("#shipping_state").val();
    var get_shipping_zip_code = $("#shipping_zip_postal_code").val();
    var get_shipping_country = $("#shipping_country").val();
    var get_shipping_suit_apt = $("#shipping_suit_apt").val();
    var get_ship_contact_no = $("#ship_contact_no").val();


    var get_billing_addr = $("#billing_addr").val();
    var get_billing_city = $("#billing_city").val();
    var get_billing_state = $("#billing_state").val();
    var get_billing_zip_code = $("#billing_zip_postal_code").val();
    var get_billing_country = $("#billing_country").val();
    var get_billing_suit_apt = $("#billing_suit_apt").val();
    var get_bill_contact_no = $("#bill_contact_no").val();
    var influencer_code = $("#influencer_code").val();


    // if ((get_shipping_addr === get_billing_addr) &&
    //   (get_shipping_city === get_billing_city) &&
    //   (get_shipping_state === get_billing_state) &&
    //   (get_shipping_zip_code === get_billing_zip_code) &&
    //   (get_shipping_country === get_billing_country) &&
    //   (get_shipping_suit_apt === get_billing_suit_apt) &&
    //   (get_ship_contact_no === get_bill_contact_no)
    // ) {
    //   $("#same_as_shipping_check").prop("checked", true)
    // }

    // Check box code for coping the shipping address to billing address.
    $("#same_as_shipping_check").change(function() {
      var ischecked = $(this).is(':checked');
      var shipping_addr = $("#shipping_addr").val();

      var shipping_city = $("#shipping_city").val();
      var shipping_state = $("#shipping_state").val();
      var shipping_zip_code = $("#shipping_zip_postal_code").val();
      var shipping_country = $("#shipping_country").val();
      var shipping_suit_apt = $("#shipping_suit_apt").val();
      var ship_contact_no = $("#ship_contact_no").val();

      if (ischecked) {
        $("#billing_addr").val(shipping_addr);
        $("#billing_city").val(shipping_city);
        $("#billing_state").val(shipping_state);
        $("#billing_zip_postal_code").val(shipping_zip_code);
        $("#billing_country").val(shipping_country);
        $("#billing_suit_apt").val(shipping_suit_apt);
        $("#bill_contact_no").val(ship_contact_no);
      } else {

        $("#billing_addr").val('');
        $("#billing_city").val('');
        $("#billing_state").val('');
        $("#billing_zip_postal_code").val('');
        $("#billing_country").val('');
        $("#billing_suit_apt").val('');
        $("#bill_contact_no").val('');
      }

    });

    $('#_totalAmt').html("$" + '{{isset($orderCalculationData['grand_total'])?num_format($orderCalculationData['grand_total']) : 0.00}}');

    // $('.datepicker').datepicker({
    //    minDate : 0,
    //    changeMonth: true,
    //    changeYear: true,
    //    dateFormat: 'mm / yy',
    //    onClose: function(dateText, inst) { 
    //        $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
    //    }
    // });


    /*$('#expiry').datepicker({
       minDate : 0,
       showMonthAfterYear: true,
       changeMonth: true,
       changeYear: true,
       dateFormat: 'mm / yy',
       showButtonPanel: true,
       onClose: function(dateText, inst) {


           function isDonePressed(){
               return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
           }

           if (isDonePressed()){
               var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
               var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
               // alert(new Date(year, month, 1));
               // $(this).datepicker('setDate', $.datepicker.formatDate('dd M yy', new Date(year, month-1, 1)));
               $(this).val($.datepicker.formatDate('mm / yy', new Date(year, month, 1)));
                $('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
           }
       },
       beforeShow : function(input, inst) {

           inst.dpDiv.addClass('month_year_datepicker')

           if ((datestr = $(this).val()).length > 0) {
               year = datestr.substring(datestr.length-4, datestr.length);
               month = datestr.substring(0, 2);
               $(this).datepicker('option', 'defaultDate', new Date(year, month, 1));
               $(this).datepicker('setDate', new Date(year, month-1, 1));
               $(".ui-datepicker-calendar").hide();
           }
       }
    });*/

    $('#btn_place_order').click(function() {

      //$('#frm-card').parsley();
      /*check shipping address and billing are there then retailer can purchase the order other wise no*/

      var ship_streeAddr      = $('#shipping_addr').val();
      //console.log(ship_streeAddr);
      var ship_city           = $('#shipping_city').val();
      var ship_state          = $('#shipping_state').val();
      var ship_country        = $('#shipping_country').val();
      var ship_zipcode        = $('#shipping_zip_postal_code').val();
      var ship_contact_no     = $("#ship_contact_no").val();
      var influencer_code = $("#influencer_code").val();
      
      var bill_streeAddr      = $('#billing_addr').val();
      var bill_city           = $('#billing_city').val();
      var bill_state          = $('#billing_state').val();
      var bill_country        = $('#billing_country').val();
      var bill_zipcode        = $('#billing_zip_postal_code').val();
      var bill_contact_no     = $("#bill_contact_no").val();      


      if (ship_streeAddr == '' && ship_city == '' &&
        ship_state == '' && ship_country == '' &&
        ship_zipcode == '' && ship_contact_no == '' &&
        bill_streeAddr == '' && bill_city == '' &&
        bill_state == '' && bill_country == '' &&
        bill_zipcode == '' && bill_contact_no == '') {
        swal('Warning', 'Please fill your shipping and billing address then try to place the order.', 'warning');
      } else {
        /*alert(55555);
        buyCartItems();*/

        if ($('#frm-card').parsley().validate() == false) {
          return;
        } else { 

         swal({
            title: "Need Confirmation",
            text: "Are you sure? Do you want to place this order.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#8CD4F5",
            confirmButtonText: "OK",
            closeOnConfirm: true
          },
          function(isConfirm, tmp) {
            if (isConfirm == true) {
                buyCartItems();
            }
          });
      }
    }

    });

    $('#btn_net_30').click(function() {
      $('#payment_type').val('2');

    });

    /*onclick of shipping country dropdown validate shipping zip/postal code field*/
    $("#shipping_country").change(function() {
      var shipping_country = $("#shipping_country").val();
      var post_code = $("#shipping_zip_postal_code").val();

      var phone_code = $('option:selected', this).attr('phone_code');
      var zipcode_length = $('option:selected', this).attr('zipcode_length');
      var countryName = $('option:selected', this).attr('country_name');

      if (phone_code) {
        $("#ship_contact_no").val('');
        $("#ship_contact_no").val('+' + phone_code);
        $("#ship_contact_no").attr('code_length', phone_code.length + 1);
        $("#hid_ship_contact_no_country_code").val('+' + phone_code);
      } else {
        $("#ship_contact_no").val('');
        $("#ship_contact_no").attr(0);
        $("#hid_ship_contact_no_country_code").val('');
      }

      if (shipping_country == "" && post_code != "") {
        $("#err_post_code").html('Invalid zip/postal code.');
      }

      var codeLength = jQuery('#hid_ship_contact_no_country_code').val();
      var minPhone = 10 + codeLength.length;            
      $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

      if(zipcode_length == 8)
      {
        $('#shipping_zip_postal_code').attr('parsley-maxlength', true);
        $('#shipping_zip_postal_code').removeAttr('data-parsley-length');
        $('#shipping_zip_postal_code').attr('data-parsley-length-message', "");
        $("#shipping_zip_postal_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters.',
        });
        }
        else{
        $('#shipping_zip_postal_code').attr('parsley-maxlength', false);
        $('#shipping_zip_postal_code').attr('data-parsley-maxlength-message', "");
        $("#shipping_zip_postal_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits.'
        });
      }
      
      $('#frm-card').parsley();
    });
    /*onclick of billing country validate billing zip/postal code field*/

    $("#billing_country").change(function() {

      var billing_country = $("#billing_country").val();
      var post_code = $("#billing_zip_postal_code").val();

      var phone_code = $('option:selected', this).attr('phone_code');
      var zipcode_length = $('option:selected', this).attr('zipcode_length');
      var countryName = $('option:selected', this).attr('country_name');

      if (phone_code) {
        $("#bill_contact_no").val('');
        $("#bill_contact_no").val('+' + phone_code);
        $("#bill_contact_no").attr('code_length', phone_code.length + 1);
        $("#hid_bill_contact_no_country_code").val('+' + phone_code);
      } else {
        $("#bill_contact_no").val('');
        $("#bill_contact_no").attr(0);
        $("#hid_bill_contact_no_country_code").val('');
      }

      if (billing_country == "" && post_code != "") {
        $("#err_post_code").html('Invalid zip/postal code.');
      }

      var codeLength = jQuery('#hid_bill_contact_no_country_code').val();
      var minPhone = 10 + codeLength.length;            
      $('#bill_contact_no').attr('data-parsley-minlength', minPhone);

      if(zipcode_length == 8)
      {
        $('#billing_zip_postal_code').attr('parsley-maxlength', true);
        $('#billing_zip_postal_code').removeAttr('data-parsley-length');
        $('#billing_zip_postal_code').attr('data-parsley-length-message', "");
        $("#billing_zip_postal_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters.',
        });
        }
        else{
        $('#billing_zip_postal_code').attr('parsley-maxlength', false);
        $('#billing_zip_postal_code').attr('data-parsley-maxlength-message', "");
        $("#billing_zip_postal_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits.'
        });
      }

      $('#frm-card').parsley();
    });



    if (representative_order_id && representative_order_id != '') {
      if (address_arr != '' && address_arr != undefined) {
        $('#checkbox_same_ship_address').hide();

        $("#shipping_addr").val(address_arr.ship_street_address);
        $("#shipping_city").val(address_arr.ship_city);
        $("#shipping_state").val(address_arr.ship_state);
        $("#shipping_country").val(address_arr.ship_country);
        $("#shipping_zip_postal_code").val(address_arr.ship_zip_code);
        $("#shipping_suit_apt").val(address_arr.ship_suit_apt);
        $("#ship_contact_no").val(address_arr.ship_mobile_no);

        $("#billing_addr").val(address_arr.bill_street_address);
        $("#billing_city").val(address_arr.bill_city);
        $("#billing_state").val(address_arr.bill_state);
        $("#billing_country").val(address_arr.bill_country);
        $("#billing_zip_postal_code").val(address_arr.bill_zip_code);
        $("#billing_suit_apt").val(address_arr.bill_suit_apt);
        $("#bill_contact_no").val(address_arr.bill_mobile_no);

        $("#shipping_addr").attr('readonly', true);
        $("#shipping_city").attr('readonly', true);
        $("#shipping_state").attr('readonly', true);
        $("#shipping_country").attr('readonly', true);
        $("#shipping_zip_postal_code").attr('readonly', true);
        $("#shipping_suit_apt").attr('readonly', true);

        $("#billing_addr").attr('readonly', true);
        $("#billing_city").attr('readonly', true);
        $("#billing_state").attr('readonly', true);
        $("#billing_country").attr('readonly', true);
        $("#billing_zip_postal_code").attr('readonly', true);
        $("#billing_suit_apt").attr('readonly', true);


        /*$("#billing_country").trigger('change');
        $("#shipping_country").trigger('change');
        */

      }
    }

    $("#ship_contact_no").keydown(function(event) {
        var text_length = $("#ship_contact_no").attr('code_length');
        if (event.keyCode == 8) {
            this.selectionStart--;
        }
        if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
        }
    });
    $("#ship_contact_no").keyup(function(event) {
        var text_length = ($("#ship_contact_no").attr('code_length')) ? $("#ship_contact_no").attr(
            'code_length') : "";
        if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
        }
    });

    $("#bill_contact_no").keydown(function(event) {
        var text_length = $("#bill_contact_no").attr('code_length');
        if (event.keyCode == 8) {
            this.selectionStart--;
        }
        if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
        }
    });
    $("#bill_contact_no").keyup(function(event) {
        var text_length = ($("#bill_contact_no").attr('code_length')) ? $("#bill_contact_no").attr(
            'code_length') : "";
        if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
        }
    });

  });


  function setCard(ref) {

    /*on the card selection remove required class and required validation.*/
    $('#cvv').hide();

    var card_id = $('option:selected', $(ref)).attr('data-card-id');
    var customer_id = $('option:selected', $(ref)).attr('data-customer-id');
    var card_data = $(ref).val();

    var url = '{{$module_url_path or '
    '}}/get_card';

    if (card_data == '') {
      location.reload();
      $('#card_no').val('');
      $('#expiry').val('');
      $('#cvv').show();

      $('.parsley-errors-list').show();
      $('.parsley-error').addClass('parsley-error');
      $('li').addClass('parsley-required');
      $('#cvv').attr('data-parsley-required', true);
      $('#card_no').attr('readonly', false);
      $('#name').attr('readonly', false);
      $('#expiry').attr('readonly', false);
      $('#expiry').css('pointer-events', '');

    } else {

      $('#expiry').css('pointer-events', 'none');
      $('.parsley-errors-list').hide();
      $('.parsley-error').removeClass('parsley-error');
      $('li').removeClass('parsley-required');
      $('#cvv').hide();
      $('#cvv').attr('data-parsley-required', false);

    }

    $.ajax({
      url: url,
      method: 'POST',
      dataType: 'JSON',
      data: {
        "_token": "{{ csrf_token() }}",
        "card_id": card_id,
        "customer_id": customer_id
      },
      beforeSend: function() {
        showProcessingOverlay();
      },
      success: function(response) {
        hideProcessingOverlay();

        if (response != '' && response != null) {

          $('.jp-card-number').text(response.number);
          $('.jp-card-expiry').text(response.expiry);

          $('#card_no').val(response.number);
          $('#expiry').val(response.expiry);
          $('#is_exists').val(card_id);

          $('#card_no').attr('readonly', true);
          $('#name').attr('readonly', true);
          $('#expiry').attr('readonly', true);
        }
      }

    });
  }

  function buyCartItems() {
    /*var is_exists = $('#is_exists').val();

    if(is_exists == '')
    {
      $('#card_no').attr('data-parsley-required', true);
      $('#expiry').attr('data-parsley-required', true);
      $('#cvv').attr('data-parsley-required', true);

    }

    if ($('#frm-card').parsley().validate() == false) {
      return;
    }*/

    if ($('#frm-card').parsley().validate() == false) {
      return;
    }

    var representative_order_id = $('#representative_order_id').val();

    // Validate shiping details
    var phone_code = $('#shipping_country option:selected').attr('phone_code');
    var zipcode_length = $('#shipping_country option:selected').attr('zipcode_length');
    var countryName = $('#shipping_country option:selected').attr('country_name');

    var codeLength = jQuery('#hid_ship_contact_no_country_code').val();
    var minPhone = 10 + codeLength.length;            
    $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
      $('#shipping_zip_postal_code').attr('parsley-maxlength', true);
      $('#shipping_zip_postal_code').removeAttr('data-parsley-length');
      $('#shipping_zip_postal_code').attr('data-parsley-length-message', "");
      $("#shipping_zip_postal_code").attr({
        "data-parsley-maxlength": zipcode_length,
        "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
          zipcode_length +
          '  characters.',
      });
      }
      else{
      $('#shipping_zip_postal_code').attr('parsley-maxlength', false);
      $('#shipping_zip_postal_code').attr('data-parsley-maxlength-message', "");
      $("#shipping_zip_postal_code").attr({
      "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
      "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
          zipcode_length +
          '  digits.'
      });
    }

    // Validate billing details
    var phone_code = $('#billing_country option:selected').attr('phone_code');
    var zipcode_length = $('#billing_country option:selected').attr('zipcode_length');
    var countryName = $('#billing_country option:selected').attr('country_name');

    var codeLength = jQuery('#hid_bill_contact_no_country_code').val();
    var minPhone = 10 + codeLength.length;            
    $('#bill_contact_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
      $('#billing_zip_postal_code').attr('parsley-maxlength', true);
      $('#billing_zip_postal_code').removeAttr('data-parsley-length');
      $('#billing_zip_postal_code').attr('data-parsley-length-message', "");
      $("#billing_zip_postal_code").attr({
        "data-parsley-maxlength": zipcode_length,
        "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
          zipcode_length +
          '  characters.',
      });
      }
      else{
      $('#billing_zip_postal_code').attr('parsley-maxlength', false);
      $('#billing_zip_postal_code').attr('data-parsley-maxlength-message', "");
      $("#billing_zip_postal_code").attr({
      "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
      "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
          zipcode_length +
          '  digits.'
      });
    }

   /* if ($('#frm-card').parsley().validate() == false) {
      return;
    }
    var formData = $('#frm-card').serialize();*/

    /*var number = $('#card_no').val();
    var expiry = $('#expiry').val();
    var cvv = $('#cvv').val();*/

    $('#payment_type').val('3');
    var payment_type = $('#payment_type').val();

    var shipping_addr = $("#shipping_addr").val();
    var shipping_city = $("#shipping_city").val();
    var shipping_state = $("#shipping_state").val();
    var shipping_country = $("#shipping_country").val();
    var shipping_zip_code = $("#shipping_zip_postal_code").val();
    var shipping_suit_apt = $("#shipping_suit_apt").val();
    var ship_contact_no = $("#ship_contact_no").val();
    var influencer_code = $("#influencer_code").val();

    var billing_addr = $("#billing_addr").val();
    var billing_city = $("#billing_city").val();
    var billing_state = $("#billing_state").val();
    var billing_country = $("#billing_country").val();
    var billing_zip_code = $("#billing_zip_postal_code").val();
    var billing_suit_apt = $("#billing_suit_apt").val();
    var bill_contact_no = $("#bill_contact_no").val();



    $.ajax({
      url: '{{url('/buy')}}',
      method: 'POST',
      dataType: 'JSON',
      data: {
        "_token": "{{ csrf_token() }}",
        /*"number": number,
        "expiry": expiry,
        "cvc": cvv,
        "is_exists": is_exists,*/
        "payment_type": payment_type,
        "shipping_addr": shipping_addr,
        "shipping_city": shipping_city,
        "shipping_state": shipping_state,
        "shipping_country": shipping_country,
        "shipping_zip_postal_code": shipping_zip_code,
        "shipping_suit_apt": shipping_suit_apt,
        "ship_contact_no": ship_contact_no,
        "billing_addr": billing_addr,
        "billing_city": billing_city,
        "billing_state": billing_state,
        "billing_country": billing_country,
        "billing_zip_postal_code": billing_zip_code,
        "billing_suit_apt": billing_suit_apt,
        "bill_contact_no": bill_contact_no,
        "influencer_code": influencer_code
      },
      beforeSend: function() {
        showProcessingOverlay();
      },
      success: function(response) {
        hideProcessingOverlay();

        if (response.status == 'success') {
          swal({
              title: "Success",
              text: response.description,
              type: response.status,
              showCancelButton: false,
              confirmButtonClass: "btn-success",
              confirmButtonText: "OK",
              closeOnConfirm: false
            },
            function() {
              if (representative_order_id != '') {
                location.href = '{{url('/retailer/my_orders/order_from_representative')}}';
              } else {
                location.href = '{{url('/retailer/my_orders')}}';
              }

            });
        } else {

          var status = response.status;
          status = status.charAt(0).toUpperCase() + status.slice(1);
          swal(status, response.description, response.status);
        }
      }

    });

  }

  function net30Payment() {
    var payment_type = $('#payment_type').val();

    $.ajax({
      url: '{{url('/net_30_payment')}}',
      method: 'POST',
      dataType: 'JSON',
      data: {
        "_token": "{{ csrf_token() }}",
        "payment_type": payment_type
      },
      beforeSend: function() {
        showProcessingOverlay();
      },
      success: function(response) {
        hideProcessingOverlay();

        if (response.status == 'success') {
          swal({
              title: "Success",
              text: response.description,
              type: response.status,
              showCancelButton: false,
              confirmButtonClass: "btn-success",
              confirmButtonText: "OK",
              closeOnConfirm: false
            },
            function() {

              location.href = '{{url('/retailer/my_orders')}}';
            });
        } else {
          var status = response.status;
          status = status.charAt(0).toUpperCase() + status.slice(1);
          swal(status, response.description, response.status);
        }
      }

    });
  }



  $('#btn_net').click(function() {

    $('#card_no').attr('data-parsley-required', false);
    $('#expiry').attr('data-parsley-required', false);
    $('#cvv').attr('data-parsley-required', false);

    if ($('#frm-card').parsley().validate() == false) {
      return;
    }

    // Validate shiping details
    var phone_code = $('#shipping_country option:selected').attr('phone_code');
    var zipcode_length = $('#shipping_country option:selected').attr('zipcode_length');
    var countryName = $('#shipping_country option:selected').attr('country_name');

    var codeLength = jQuery('#hid_ship_contact_no_country_code').val();
    var minPhone = 10 + codeLength.length;            
    $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
      $('#shipping_zip_postal_code').attr('parsley-maxlength', true);
      $('#shipping_zip_postal_code').removeAttr('data-parsley-length');
      $('#shipping_zip_postal_code').attr('data-parsley-length-message', "");
      $("#shipping_zip_postal_code").attr({
        "data-parsley-maxlength": zipcode_length,
        "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
          zipcode_length +
          '  characters.',
      });
      }
      else{
      $('#shipping_zip_postal_code').attr('parsley-maxlength', false);
      $('#shipping_zip_postal_code').attr('data-parsley-maxlength-message', "");
      $("#shipping_zip_postal_code").attr({
      "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
      "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
          zipcode_length +
          '  digits.'
      });
    }

    // Validate billing details
    var phone_code = $('#billing_country option:selected').attr('phone_code');
    var zipcode_length = $('#billing_country option:selected').attr('zipcode_length');
    var countryName = $('#billing_country option:selected').attr('country_name');

    var codeLength = jQuery('#hid_bill_contact_no_country_code').val();
    var minPhone = 10 + codeLength.length;            
    $('#bill_contact_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
      $('#billing_zip_postal_code').attr('parsley-maxlength', true);
      $('#billing_zip_postal_code').removeAttr('data-parsley-length');
      $('#billing_zip_postal_code').attr('data-parsley-length-message', "");
      $("#billing_zip_postal_code").attr({
        "data-parsley-maxlength": zipcode_length,
        "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
          zipcode_length +
          '  characters.',
      });
      }
      else{
      $('#billing_zip_postal_code').attr('parsley-maxlength', false);
      $('#billing_zip_postal_code').attr('data-parsley-maxlength-message', "");
      $("#billing_zip_postal_code").attr({
      "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
      "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
          zipcode_length +
          '  digits.'
      });
    }


    swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to make Net30 payment.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#8CD4F5",
        confirmButtonText: "OK",
        closeOnConfirm: true
      },
      function(isConfirm, tmp) {
        if (isConfirm == true) {

          $('#payment_type').val('2');
          var payment_type = $('#payment_type').val();

          var payment_type = 'Net30';
          var shipping_addr = $("#shipping_addr").val();
          var shipping_city = $("#shipping_city").val();
          var shipping_state = $("#shipping_state").val();
          var shipping_country = $("#shipping_country").val();
          var shipping_zip_code = $("#shipping_zip_postal_code").val();

          var billing_addr = $("#billing_addr").val();
          var billing_city = $("#billing_city").val();
          var billing_state = $("#billing_state").val();
          var billing_country = $("#billing_country").val();
          var billing_zip_code = $("#billing_zip_postal_code").val();


          /*check shipping address and billing are there then retailer can purchase the order other wise no*/

          if (shipping_addr == '' && shipping_city == '' && shipping_state == '' && shipping_country == '' &&
            shipping_zip_code == '' && billing_addr == '' && billing_city == '' && billing_state == '' && billing_country == '' && billing_zip_code == '') {
            swal('Warning', 'Please fill your shipping and billing address then try to place the order.', 'warning');
          } else {

            $.ajax({
              url: '{{url('/net_payment')}}',
              method: 'POST',
              dataType: 'JSON',
              data: {
                "_token": "{{ csrf_token() }}",
                "payment_type": payment_type,
                "shipping_addr": shipping_addr,
                "shipping_city": shipping_city,
                "shipping_state": shipping_state,
                "shipping_country": shipping_country,
                "shipping_zip_postal_code": shipping_zip_code,
                "billing_addr": billing_addr,
                "billing_city": billing_city,
                "billing_state": billing_state,
                "billing_country": billing_country,
                "billing_zip_postal_code": billing_zip_code
              },
              beforeSend: function() {
                showProcessingOverlay();
              },
              success: function(response) {
                hideProcessingOverlay();

                if (response.status == 'success') {
                  swal({
                      title: "Success",
                      text: response.description,
                      type: response.status,
                      showCancelButton: false,
                      confirmButtonClass: "btn-success",
                      confirmButtonText: "OK",
                      closeOnConfirm: false
                    },
                    function() {
                      location.href = '{{url('/retailer/my_orders')}}';
                    });
                } else {
                  var status = response.status;
                  status = status.charAt(0).toUpperCase() + status.slice(1);
                  swal(status, response.description, response.status);
                }

              }

            });

          }

        }

      });

  });

  var dp = $("#expiry").datepicker({
    format: 'mm / yyyy',
    startDate: '-1d',
    startView: "months",
    minViewMode: "months"
  });

  dp.on('changeMonth', function(e) {
    //do something here
    $("#expiry").datepicker('hide');
  });
</script>

<script type="text/javascript">
  $(document).ready(function() {
    $('.leftSidebar, .content')
      .theiaStickySidebar({
        additionalMarginTop: 100
      });
  });
</script>
@endsection