@extends('front.layout.master') 
@section('main_content')

<div class="container">
    <div class="brand-dtls-got">
       <div class="row">
           <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
               <div class="img-jst-hv-it">
                @php
                    $profile_image = getProfileImage($arr_data['profile_image']);
                @endphp

                   <img src="{{$profile_image}}" alt="" />
               </div>
           </div>
           <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="details-just-got-to-hv-it">
                    <div class="title-user-details">{{isset($arr_data['first_name'])?ucfirst($arr_data['first_name']):""}} {{isset($arr_data['last_name'])?$arr_data['last_name']:""}}</div>
                    <div class="subtitle-brand">{{isset($arr_data['nationality'])?ucfirst($arr_data['nationality']):""}}</div>
                    <div class="mobile-details-brnd"><span>Mobile:</span>{{isset($arr_data['contact_no'])?ucfirst($arr_data['contact_no']):""}}</div>
                    <div class="email-brd-dts"><span>Email:</span>{{isset($arr_data['email'])?$arr_data['email']:""}}</div>
                   {{--  <div class="email-brd-dts"><span>Post Code:</span>{{isset($arr_data['post_code'])?ucfirst($arr_data['post_code']):""}}</div> --}}
                </div>
                <div class="title-descrip">
                    {{$arr_data['representative_details']['description'] or ''}}
                </div>
           </div>
       </div>
   </div> 
   <div class="lines-represented-div">
       <div class="lines-represented-title">Lines Represented</div>
       <div class="list-of-pro-sb">
           <div class="row">
            @if(isset($arr_maker) && sizeof($arr_maker)>0)
               @foreach($arr_maker as $maker)
               <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                   <div class="jst-got-hv-box-brand">
                       <div class="jst-got-hv-box-brand-img">
                        @php
                        if(isset($maker['store_details']['store_profile_image']) && $maker['store_details']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$maker['store_details']['store_profile_image']))
                        {
                          $shop_img = url('/storage/app/'.$maker['store_details']['store_profile_image']);
                        }
                        else
                        {                  
                          $shop_img = url('/assets/images/no-product-img-found.jpg');
                        }
                        @endphp
                           <img class="portrait" src="{{$shop_img}}" alt="" />
                       </div>
                       <div class="jst-got-hv-brand-txt">
                          {{isset($maker['maker_details']['brand_name'])?ucfirst($maker['maker_details']['brand_name']):""}}
                       </div>
                       {{-- <div class="jst-got-hv-brand-txt">
                           We’re redefining and bringing self-care back down to earth.
                       </div> --}}
                   </div>
               </div>
            @endforeach
            @else
                 <div class="col-md-12"><div class="not-found-data">No records found</div></div>
            @endif 
           </div>
           
             <div class="pagination-bar">
                @if(!empty($arr_pagination))
                   {{$arr_pagination->render()}}      
                @endif   
            </div>

   </div>  
 </div>

@endsection