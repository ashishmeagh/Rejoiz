@extends('front.layout.master')
@section('main_content')

<style type="text/css">
  .product-list-pro{padding: 10px;}
</style>
<div class="listing-main-div rep-details-page">
    <div class="container-fluid">
        {{Session::forget('guest_back_url')}}

         <div class="pagename-main">
       
        <div class="pagename-right"><a href="{{url('/')}}">Home</a>
          <span class="slash">/</span>
        <span class="slash last-beadcrum"><a href="{{url('/find_rep')}}"> Find Your Rep </a></span>
        <span class="slash">/</span>
        <span>Representative Details</span>
 
        </div>
 
        <div class="clearfix"></div>
        </div>
        <div class="row">            
           <div class="col-sm-12 col-md-3 col-lg-3 ">
            <div class="left-profile fnd-img-rp">
              <div class="profile-image">
          
              @if(isset($representative_details_arr) && sizeof($representative_details_arr)>0)
              
                @php
                  if(isset($representative_details_arr->profile_image) && $representative_details_arr->profile_image!='' && file_exists(base_path().'/storage/app/'.$representative_details_arr->profile_image))
                  {
                    $sales_img = url('/storage/app/'.$representative_details_arr->profile_image);
                  }
                  else
                  {
                    $sales_img = url('/assets/images/no-product-img-found.jpg');
                  }
                @endphp
                <img src="{{$sales_img or ''}}">
               
              </div>
               <div class="d-sales">{{$representative_details_arr->name or ''}}</div>
              <div class="profile-name">{{$representative_details_arr->user_name or ''}}</div>
            </div>
            @else    
            <div class="col-md-12"><div class="not-found-data">This division did not match any representative.</div></div>
          @endif
           </div>
         
            <div class="col-xs-12  col-sm-12 col-md-9 col-lg-9">
             
      <div class="rep-details-just">

          <div class="rep-details-team-descp-mn">
           <div class="user-tm-text">{{$representative_details_arr->user_name or ''}}</div>
           @php 
              $area_name = '';
              $area_name = isset($representative_details_arr->area_id)?get_area_name($representative_details_arr->area_id):'';

              $area_type = isset($area_type)?$area_type:'';

              if (isset($representative_details_arr->contact_no)) {

                $contact_no = get_contact_no($representative_details_arr->contact_no);

                if (isset($contact_no) && $contact_no == "") {

                  $contact_no = $representative_details_arr->contact_no;
                }
                else{

                  $contact_no = get_contact_no($representative_details_arr->contact_no);
                
                }
                 
              }
           @endphp

           <div class="position-jst">{{$area_name.' '.$area_type}}</div>

           <div class="persnl-delts"><span><i class="fa fa-phone"></i></span>{{$contact_no or ''}}</div>
          
           <div class="persnl-delts"><span><i class="fa fa-envelope"></i></span> {{$representative_details_arr->email or ''}}</div>

         @if(isset($representative_details_arr->description) && $representative_details_arr->description!='')
          <div class="mainfrontsales-right">
            <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="500">

            <div class="direct-txt moretext truncate">{{$representative_details_arr->description or ''}}</div>
           {{--  <a class="moreless-button" href="javascript:void(0);">Read more</a> --}}
          </div>
        @endif
       
     </div>


   </div>


  <div class="product-section">
        <div class="row">
            @if(isset($vendor_details_arr) && sizeof($vendor_details_arr)>0)
               @foreach($vendor_details_arr as $vendor)
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                           <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($vendor['get_maker_details']['user_id'])}}">
                            <div class="product-list-pro rep-details-product-list-pro">
                                
                                    @php
                                
                                    if(isset($vendor['get_maker_details']['shop_store_images']['store_profile_image']) && $vendor['get_maker_details']['shop_store_images']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$vendor['get_maker_details']['shop_store_images']['store_profile_image']))
                                    {
                                      $rep_img = url('/storage/app/'.$vendor['get_maker_details']['shop_store_images']['store_profile_image']);
                                    }
                                    else
                                    {                  
                                      $rep_img = url('/assets/images/no-product-img-found.jpg');
                                    }

                                    @endphp

                                    <img class="potrait" src="{{$rep_img}}" alt="" />
                                
                                <div class="pro-content-list">
                              
                                  <div class="pro-sub-title-list"> 
                                  {{$vendor['get_maker_details']['company_name']}}
                                     </div>
                                </div>
                            </div>
                            </a>
                        </div>
                  @endforeach  
                        @else    
                           <div class="col-md-12"><div class="not-found-data">This representative doesn't represent any vendor.</div></div>
                        @endif     
                        </div>
                </div>
                </div>

                <div class="pagination-bar">
                   @if(!empty($arr_pagination))   
                    
                    {{$arr_pagination->render()}}      
                   @endif 

                </div>

            </div>
        </div>
    </div>
</div>

<script>
$('.moreless-button').click(function() {
  $('.moretext').toggleClass('mores');
  if ($('.moreless-button').text() == "Read more") {
    $(this).text("Read less")
  } else {
    $(this).text("Read more")
  }
});
</script>

@stop