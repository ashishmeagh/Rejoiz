@extends('front.layout.master') 
@section('main_content')

<div class="listing-main-div">
    <div class="container-fluid">        
        <div class="row">
            @include('front.search._front_sidebar')
            <div class="col-sm-8 col-md-8 col-lg-9">
                @include('front.search._search_readcrum_header')
                <div class="product-section">
                    <div class="row">
                        @if(isset($arr_data) && sizeof($arr_data)>0)
                        @foreach($arr_data as $maker)
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                            <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($maker->id)}}">
                            <div class="product-list-pro">
                                <div class="pro-img-list">
                                    {{-- <div class="new-label-pro">New</div> --}}
                                    {{-- <div class="wishlist-right">
                                        <a href="#" class="wishlisticon"><i class="fa fa-heart-o"></i> </a>
                                    </div> --}}
                                    
                                    @php
                                    // dd($arr_data);
                                    if(isset($maker->store_profile_image) && $maker->store_profile_image!='' && file_exists(base_path().'/storage/app/'.$maker->store_profile_image))
                                    {
                                      $shop_img = url('/storage/app/'.$maker->store_profile_image);
                                    }
                                    else
                                    {                  
                                      $shop_img = url('/assets/images/no-product-img-found.jpg');
                                    }
                                    @endphp
                                    <img class="potrait" src="{{$shop_img}}" alt="" />
                                </div>
                                <div class="pro-content-list">
                                <div class="pro-sub-title-list">{{strtoupper($maker->brand_name)}}</div>
                                <?php 
                                    $get_minimum_order = get_maker_shop_setting($maker->id);
                                 ?>
                                    <div class="pro-title-list">{{isset($get_minimum_order['first_order_minimum'])?'$'.$get_minimum_order['first_order_minimum'].' Minimum':""}} </div>
                                 
                                </div>
                            </div>
                            </a>
                        </div>
                        @endforeach  
                        @else    
                           <div class="col-md-12"><div class="not-found-data">Your search did not match any maker.</div></div>
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

@endsection