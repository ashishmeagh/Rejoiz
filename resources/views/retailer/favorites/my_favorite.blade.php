@extends('front.layout.master')
@section('main_content')

<style type="text/css">
  .pagination-bar.myfavorite-br{
    
  }

  .fav-box {margin-bottom:30px;}
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
              <div class="pagename-left my_favourite_ttl">
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


                                    @if(isset($fav_product['product_details']) && count($fav_product['product_details'])>0)
                                    
 
                                     <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 fav-box">
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
                                           <a href="{{url('/')}}/vendor-details/product_detail?product_id={{base64_encode($fav_product['product_id'])}}&vendor_id={{base64_encode($fav_product['product_details']['user_id'])}}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                      </div>

                                      <div class="product-info">
                                        <a href="#" class="brandnametitle">{{isset($fav_product['product_details']['product_name'])?strtoupper($fav_product['product_details']['product_name']):'N/A'}}</a>


                                        <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($fav_product['product_details']['user_id'])}}" class="title-product">

                                          {{isset($fav_product['product_details']['brand_details']['brand_name'])?strtoupper($fav_product['product_details']['brand_details']['brand_name']):'N/A'}}
                                        </a>

                                        <div class="price-product">
                                          {{-- <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} --}}
                                          @php $maker_details = get_maker_all_details($fav_product['product_details']['user_id']); @endphp
                                                      @if($login_user == true)
                                                        @if((($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))   
                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} 
                                                        @elseif((($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || (($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}}    
                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker') || $login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)

                                                        @elseif(($login_user->inRole('retailer') || $login_user->inRole('sales_manager') || $login_user->inRole('representative')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        

                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} 

                                                        @elseif(($login_user->inRole('customer') || $login_user->inRole('maker')) && $maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                        
                                                        <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} 

                                                        @endif                                                       
                                                        @else
                                                          @if(($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 1) || ($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 0))
                                                         

                                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} 

                                                          @elseif($maker_details['is_get_a_quote'] == 1 && $maker_details['is_add_to_bag'] == 0)                                                            
                                                          @elseif($maker_details['is_get_a_quote'] == 0 && $maker_details['is_add_to_bag'] == 1)
                                                          
                                                          <i class="fa fa-usd" aria-hidden="true"></i>{{isset($fav_product['product_details']['unit_wholsale_price'])?num_format($fav_product['product_details']['unit_wholsale_price']):'0.00'}} 
                                                          
                                                          @endif
                                                        @endif
                                        </div>

                                   
                                      <!-- out of stock -->

                                         @php 
                                          $temp_product_id = isset($fav_product['product_id'])?$fav_product['product_id']:0;

                                          $sku = get_sku($fav_product['product_id']);
                                          
                                          $temp_all_sku = get_all_sku($temp_product_id);
                                         
                                          
                                          $temp_all_sku = array_column($temp_all_sku,'quantity');


                                          $product_inventory = array_sum($temp_all_sku);

                                       
                                          $is_in_stock = check_moq_inventory($fav_product['product_id']);
                                      

                                        @endphp

                                        @if(isset($product_inventory) && $product_inventory == 0 || ($login_user == true &&  ($login_user->inRole('retailer')) && $is_in_stock == false))
                                           <span class="red outofstock_listing">Out of stock</span>

                                        @endif


                                      <!--  -->


                                     <!-- Get a Quote button -->

                                      @if((isset($maker_details['is_get_a_quote']) && $maker_details['is_get_a_quote'] == 1) || (isset($fav_product['product_details']['retail_price']) && $fav_product['product_details']['retail_price'] <= 0))

                                            <div class="button-login-pb" >

                                              @php

                                               $vendor_email = get_user_email($fav_product['product_details']['user_id']);

                                               $vendor_name = isset($fav_product['product_details']['user_id'])?get_user_name($fav_product['product_details']['user_id']):'';
                                              @endphp

                                                  
                                                <a href="javascript:void(0)" 
                                                   data-product-name="{{$fav_product['product_details']['product_name'] or ''}}" 
                                                   data-product-dec="{{$fav_product['product_details']['description'] or ''}}" 
                                                   data-company-name="{{$maker_details['company_name'] or ''}}" 
                                                   data-product-id="{{$fav_product['product_id'] or ''}}" 

                                                   data-vendor-id = "{{$fav_product['product_details']['user_id'] or ''}}"

                                                   data-vendor-email = "{{$vendor_email or ''}}"
                                                   data-vendor-name = "{{$vendor_name or ''}}"

                                                   onclick="openGetAQuoteModal(this)"  class="gt-button" id="get-a-quote-modal" >Get A Quote
                                                </a>

                                                <div class="clearfix"></div>

                                            </div>

                                        @endif


                                    </div>

                                  </div>

                                </div>

                                @endif

                                @endforeach
                                  
                                @else
                               
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

                              
                                  @if(isset($fav_maker['maker_details']['user_details']) && count($fav_maker['maker_details']['user_details'])>0)
                                      
                                      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                                        <div class="product-box text-center">
                                          <div class="heart-products"> 

                                            <a href="javascript:void(0)" class="heart-active" data-id="{{isset($fav_maker['maker_id'])?base64_encode($fav_maker['maker_id']):0}}" data-type="maker" onclick="confirmAction($(this),'maker');"><i class="fa fa-heart" title="Remove from favorite"></i></a>                               
                                          </div>


                                      <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($fav_maker['maker_id'])}}">

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

                                @endif

                              @endforeach
                         
                            @else
                          
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



<!-- Get a Quote Modal -->
<div class="modal fade vendor-Modal" id="get_a_Quote" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Get A Quote</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     
      <div class="modal-body">
        <form method="post" id="getaQuote"> 
          {{csrf_field()}}
          <div id="page-wrapper">
            <div class="container-fluid">
              <div class="get-qoute-modal-body">
              <div class="get-qoute-row">              
                <div class="col-get-qoute">Vendor Name</div>

               {{--  @php
                 
                  $first_name = isset($maker_details['first_name'])?$maker_details['first_name']:'';
                  $last_name  = isset($maker_details['last_name'])?$maker_details['last_name']:'';

                  $full_name = $first_name.' '.$last_name;
                @endphp --}}

                <div class="col-get-qoute-right" id="vendor_company_name"></div>
              </div>
              <div class="get-qoute-row">
                <div class="col-get-qoute">Product Name</div>
                <div class="col-get-qoute-right" id="vendor_product_name"></div>
              </div>

              <div class="get-qoute-row">
                <div class="col-get-qoute">Description</div>
                <div class="col-get-qoute-right description-content" id="product_description"></div>
              </div>
              </div>              
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Product Quantity <span class="text-danger">*</span></label>
                      <div>
                        <input type="number" min="1" maxlength="9" pattern="^[0-9]*$" data-parsley-type="digits" class="form-control" placeholder="Enter Product Quantity" id="quote_quantity" name="quote_quantity" data-parsley-required="true" data-parsley-required-message="Please enter product quantity." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Name <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="100" class="form-control" placeholder="Enter name" id="quote_name" name="quote_name" data-parsley-required="true" data-parsley-required-message="Please enter name." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">                
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Email <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" class="form-control" placeholder="Enter email" id="quote_email" name="quote_email" data-parsley-required="true" data-parsley-required-message="Please enter email." data-parsley-type="email" data-parsley-type-message="please enter valid email." value="">
                      </div>
                    </div>                
                  </div>
                  <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Contact Number <span class="text-danger">*</span></label>
                      <div>
                      <input type="text" maxlength="20" class="form-control" placeholder="Enter contact number" id="quote_contact_no" name="quote_contact_no" data-parsley-required="true" data-parsley-required-message="Please contact number." data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number." data-parsley-minlength="10" data-parsley-minlength-message="Mobile No should be of 10 digits." data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits." value="">
                      </div>
                    </div>                
                  </div>
              </div>
              <div class="row">
                <div class="col-lg-6">  
                  <div class="form-group">              
                    <label>Expected Delivery Days <span class="text-danger">*</span></label>
                    <div>
                      <input type="number" min="0" maxlength="5" pattern="^[0-9]*$" data-parsley-type="digits" data-parsley-type-message="Please enter numbers" class="form-control" placeholder="Enter expected delivery days" id="quote_no_of_days" name="quote_no_of_days" data-parsley-required="true" data-parsley-required-message="Please enter number of days to expect delivery." value="">
                    </div>
                  </div>                
                </div>
              </div> 
              <div class="row">
                <div class="col-lg-12">  
                  <div class="form-group">              
                    <label>Additional Notes</label>
                      <div>
                      <textarea class="form-control" name="quote_additional_notes" id="quote_additional_notes" placeholder="Add additional notes" data-parsley-maxlength="500" data-parsley-maxlength-message="Only a maximum of 500 characters is allowed." ></textarea>
                      </div>
                    </div>                
                  </div>
              </div>
            </div>
          </div>

          <input type="hidden" name="vendor_id" id="vendor_id" value="">
          <input type="hidden" name="company_name" id="company_name" value="">

          <input type="hidden" name="get_quote_product_id" id="get_quote_product_id" value="">
          <input type="hidden" name="vendor_email" id="vendor_email" value="">
          <input type="hidden" name="vendor_name" id="vendor_name" value="">

        </form>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">        
        <button type="button" id="sendGetaQuote" class="btn btn-submit-get">Submit</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>     
      </div>
    </div>
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
              url: SITE_URL+'/vendor-details/remove_from_favorite',
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



function openGetAQuoteModal(ref){

  $("#get_a_Quote").modal('show');

  $("#vendor_company_name").html($(ref).attr('data-company-name'));
  $("#vendor_product_name").html($(ref).attr('data-product-name'));
  $("#product_description").html($(ref).attr('data-product-dec'));


  $("#vendor_id").val($(ref).attr('data-vendor-id'));
  $("#vendor_name").val($(ref).attr('data-vendor-name'));
  $("#vendor_email").val($(ref).attr('data-vendor-email'));
  $("#get_quote_product_id").val($(ref).attr('data-product-id'));
  $("#company_name").val($(ref).attr('data-company-name'));


}

// Submit get quote

jQuery("#sendGetaQuote").bind("click touchstart", function(e){

    e.preventDefault();

    if ($('#getaQuote').parsley().validate() == false) {
          return;
        }
        var formData = $('#getaQuote').serialize();   
        
        $.ajax({
          url: '{{url("vendor-details/send_get_a_quote")}}',
          method: 'POST',
          dataType: 'JSON',
          data: formData,
          beforeSend: function() {
            showProcessingOverlay();
          },
          success: function(response) {
            hideProcessingOverlay();
            
            if (response.status == 'SUCCESS') {              
              swal({
                  title: "Success",
                  text: response.description,
                  type: 'success',
                  showCancelButton: false,
                  confirmButtonClass: "btn-success",
                  confirmButtonText: "OK",
                  closeOnConfirm: true
                },
                function() {                  
                  $('#get_a_Quote').modal('hide');  
                  $('#getaQuote').find("input[type=text], textarea, input[type=number]").val("");                
                });
            } else {    
              var status = response.status;
              status = status.charAt(0).toUpperCase() + status.slice(1);
              swal(status, response.description, response.status);
            }
          }
    
        });
  })

</script>

@stop