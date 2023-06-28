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
   .font-note{
   font-weight: 300;
   color: #9c9c9c;
   }
   .active{
   text-decoration: underline;
   color: #333;
   }
   ul.promotions-ul {
      margin: 11px 0;
  }
  .main-all-current-promotions-left{
    height : auto !important;
  }
  .div-company-name {
        font-weight: bold;
        font-size: 15px;
  }
</style>
<div class="main-promotion-div space-o-promt promotions-left-right-padding">
   <div class="container-fluid">
      <div class="row">
         <div class="col-md-12 top-breadcrumb">
              <div>
               <div class="start-title textlefts-start-title">{{$area_name.' '.$category_name}} Promotions</div>
            
               <div class="font-note"><i>* Prices and offers are subject to change.</i></div>
              </div>

            <div class="col-md-6">
            <div class="pagename-right"><a href="{{url('/')}}">Home</a> 
                  <span class="slash">/</span>

                  @if(Request::segment(2) !='' && Request::segment(3) !='')
                    <a href="{{url('/promotions')}}">All Offers</a> 
                     <span class="slash">/</span>
                      <span class="slash last-beadcrum">{{$area_name.' '.$category_name}}</span>
                    

                  @elseif(Request::segment(2) !='' && Request::segment(3) =='')
                    <a href="{{url('/promotions')}}">All Offers</a> 
                     <span class="slash">/</span>
                      <span class="slash last-beadcrum">{{$area_name}}</span>
                    
                  @else
                    <span class="slash last-beadcrum">All Offers</span>
                  @endif
                  
               <div class="clearfix"></div>
            </div>
         </div>
         </div>
         <div class="col-md-9">
            @php 
              $date = date('Y-m-d');
            @endphp
            <!-- breadcrumbs -->
           
          
      <div class="pagename-main-change">
            <br>
        @if(isset($promotion_arr) && count($promotion_arr)>0)
          @foreach($promotion_arr as $key=>$promotions)

            @if($promotions['to_date'] >= $date && $promotions['is_active'] == 1)
            
              <div class="main-all-current-promotions">
                <div class="main-all-current-promotions-left">
                  <a @if(!in_array('vendors',$menu_settings)) style="cursor: auto !important;" href="#" @else href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($promotions['maker_id'])}}&promotion_id={{base64_encode($promotions['id'])}}" @endif>
             
                @if(isset($promotions['get_maker_details']['shop_store_images']['store_profile_image']) && $promotions['get_maker_details']['shop_store_images']['store_profile_image']!='' &&  file_exists(base_path().'/storage/app/'.$promotions['get_maker_details']['shop_store_images']['store_profile_image']))

                  <img src="{{url('/')}}/storage/app/{{$promotions['get_maker_details']['shop_store_images']['store_profile_image']}}"  class="potrait" alt="" /> 

                @else
                  <img src="{{url('/assets/images/no-product-img-found.jpg')}}"  class="potrait" alt="" /> 
                @endif  
                  </a>
               </div>
               <div class="main-all-current-promotions-right">
                 <div class="div-company-name">{{isset($promotions['get_maker_details']['company_name'])?$promotions['get_maker_details']['company_name']:'-'}}</div>
                  <div class="title-promotions-txt">{{$promotions['title'] or ''}}   </div>
                  <div class="date-tm">{{isset($promotions['from_date'])?date('d M Y',strtotime($promotions['from_date'])):''}}  To  {{isset($promotions['to_date'])?date('d M Y',strtotime($promotions['to_date'])):''}}</div>
                  <div class="clearfix"></div>
                  <ul class="promotions-ul">
                     @if(isset($promotions['get_promotions_offer_details']) && count($promotions['get_promotions_offer_details'])>0)
                     @foreach($promotions['get_promotions_offer_details'] as $key=>$promotions_offer)
                     @if($promotions_offer['promotion_type_id'] == 1)
                     <li>Orders of ${{$promotions_offer['minimum_ammount']}} and above receive free shipping</li>
                     @elseif($promotions_offer['promotion_type_id'] == 2)
                     <li>Orders of ${{$promotions_offer['minimum_ammount']}} and above receive {{$promotions_offer['discount']}}% off</li>
                     @endif
                     @endforeach
                     @endif
                  </ul>
                 
                  @if(isset($promotions['get_promo_code_details']['promo_code_name']) && $promotions['get_promo_code_details']['promo_code_name']!='')
                  <div class="button-ff-year">{{$promotions['get_promo_code_details']['promo_code_name'] or ''}}</div>
                  @endif 

                  @if(isset($promotions['description']) && $promotions['description']!='')
                    <div class="description-txt-p-pro">{!!$promotions['description'] or ''!!}</div>
                  @endif 

                 {{--  @if(isset($promotions['get_maker_details']['company_name']) && $promotions['get_maker_details']['company_name']!='')
                    <div class="description-txt-p-pro">{!!$promotions['get_maker_details']['company_name'] or ''!!}</div>
                  @endif  --}}
                 
                   
               </div>
               <div class="clearfix"></div>
            </div>
          @endif
        @endforeach
      @else
        <div class="not-found-data whitebg-no">No promotions are available now.</div>
    @endif
  </div>

         </div>
         <div class="col-md-3">
            <div class="sidebar-main-lisitng topspaceview-division">
               <div class="title-categorynm click-category"><span class="cat-none">View by Division</span> 
                <img src="{{url('/')}}/assets/front/images/chevron-down.svg" alt="">
               </div>
               <div id="cssmenu1" class="shows-category">
                  <ul class="su-menu offer-su-menu">
                     @if(isset($arr_area) && count($arr_area)>0)

                        <li @if(Request::segment(2) == '') class="active" @endif >

                           <a href="{{url('/promotions')}}">All Offers</a>
                        </li> 
                          
                     @foreach($area_category_arr as $key=>$area)

                           @if(isset($area['category_arr']) && count($area['category_arr'])>0)

                              @foreach($area['category_arr'] as $key=>$category)

                              <li @if(Request::segment(3) == base64_encode($category['id']) && Request::segment(2) == base64_encode($area['area_id'])) class="active" @endif><a href="{{url('/promotions')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):''}}/{{isset($category['id'])?base64_encode($category['id']):''}}">{{$area['area_name'].' '.$category['cat_division_name']}}</a></li>

                              @endforeach
                           @else
                              <li @if(Request::segment(2) == base64_encode($area['area_id'])) class="active" @endif><a href="{{url('/promotions')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):''}}">{{$area['area_name']}}</a></li>
                           @endif

                     @endforeach
                     @endif
                  </ul>
               </div>
            </div>
            </div>
            <!-- @if(isset($promotion_arr) && count($promotion_arr)>0)
               <div class="not-found-data whitebg-no col-sm-12 text-center">No promotions are available now.</div>
            @endif -->
      </div>
   </div>
</div>
</div>
</div>
<script>
$(document).ready(function(){
  $(".click-category").click(function(){
    
    $(".shows-category").toggleClass("showscategorydiv");
  });
});
</script>
{{-- <script type="text/javascript">
   $(document).ready(function () {
   var selector = '.testli';
   $(selector).on('click', function(){
       alert("njjjj");
   $(selector).removeClass('active1');
   $(this).addClass('active1');
   });
   });
</script> --}}
@endsection