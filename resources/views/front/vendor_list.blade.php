@extends('front.layout.master')
@section('main_content')

<style type="text/css">
  .rep-details-just.space-top-none{padding-top: 0px;}
</style>
<div class="listing-main-div">
  <div class="container-fluid">
        {{Session::forget('guest_back_url')}}

         @php

         /*get category name and area name from there id*/

          $area_name     = get_area_name($area_id);

          $this->arr_view_data['category_name']   = isset($category_name)?$category_name:'';
          $this->arr_view_data['area_name']       = isset($area_name)?$area_name:'';
          $this->arr_view_data['page_title']      = 'Find Your Rep';

       
         $area_name     = isset($area_name)?$area_name:'';
         $category_name = isset($category_name)?$category_name:$category_div_name;
         $area_type     = isset($area_type)?$area_type:'';
  
         $category_div_id = $category_id; 



        @endphp

        <input type="hidden" name="category_div_id" id="category_div_id" value="{{$category_id}}">

        <div class="border-titlesfind space-bottom">
          <div class="row">
            <div class="col-md-6"> 
              <div class="the-representatives">

                 {{$area_name.' '.$category_name.' '.$area_type}} 
                </div>
            </div> 
            <div class="col-md-6">
               <div class="pagename-main">
               
                <div class="pagename-right"><a href="{{url('/')}}">Home</a>
                  <span class="slash">/</span>
                  <a href="{{url('/find_rep')}}">Find Your Rep</a> 
              
                 <span class="slash">/</span>
                 <span >{{$area_name.' '.$category_name.' '.$area_type}} Vendors</span>

                </div>
         
                <div class="clearfix"></div>
                </div>
            </div> 
          </div>
        </div> 


        <div class="row">
     
         <div class="clearfix"></div>
    
          @include('front.search._sidebar_for_search_vendor')
    
          <div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">
             
          <div class="rep-details-just space-top-none">

          <div class="button-of-sales-mgr">
             
            @if(Request::segment(4))
             <a href="{{url('/')}}/find_rep/{{Request::segment(2)}}/{{Request::segment(4)}}">View the {{$area_name.' '.$category_name.' '.$area_type}} Team</a>

            @elseif(Request::segment(3))
              <a href="{{url('/')}}/find_rep/{{Request::segment(2)}}/{{Request::segment(3)}}">View the {{$area_name.' '.$category_name.' '.$area_type}} Team</a>
            @else
              <a href="{{url('/')}}/find_rep/{{Request::segment(2)}}}}">View the {{$area_name or ''}} Team</a>
            @endif
            
              <div class="clearfix"></div>


            </div>

            <div class="state-name">
               
                 @if(isset($state_details_arr) && count($state_details_arr)>0)
                 @foreach($state_details_arr as $key=>$state)
                  @php
                   
                  if(isset($state['name']) && $state['name']!='')
                  {
                      $state_name = $state['name'];
                  } 
                  
                  @endphp 


              <div class="inline-p">  {{$state_name or ''}} </div>
                 
              @endforeach
              @endif
              </div>

      
  <div class="product-section vendor-logo-listing ">
                  
    <div class="row">

          @if(isset($vendor_rep_details_arr['data']) && count($vendor_rep_details_arr['data'])>0)

              @foreach($vendor_rep_details_arr['data'] as $vendor)

                  <div class="col-xs-6 col-sm-4 col-md-4 col-lg-3">
                      <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($vendor->maker_id)}}">
                          <div class="product-list-pro find-the-nw">
                              <div class="pro-img-list">
                                   
                                  @php
                                
                                  if(isset($vendor->store_profile_image) && $vendor->store_profile_image!='' && file_exists(base_path().'/storage/app/'.$vendor->store_profile_image))
                                  {
                                      $rep_img = url('/storage/app/'.$vendor->store_profile_image);
                                  }
                                  else
                                  {                  
                                     $rep_img = url('/assets/images/no-product-img-found.jpg');
                                  }

                                  @endphp

                                  <img class="potrait" src="{{$rep_img or ''}}" alt="" />

                              </div>

                              <div class="pro-content-list">
                              
                                <div class="pro-sub-title-list"> 
                                {{$vendor->company_name}}
                                </div>
                              </div>

                          </div>
                      </a>
                  </div>


                @endforeach  
          @else    
              <div class="col-md-12"><div class="not-found-data">This area did not match any vendor.</div></div>
          @endif     
        </div>
      </div>
    </div>

    <div class="pagination-bar">
         @if(!empty($arr_vendor_pagination))   
          
          {{$arr_vendor_pagination->render()}}      
         @endif 

    </div>

            </div>
        </div>
    </div>
</div>

@stop