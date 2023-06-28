@extends('front.layout.master') 
@section('main_content')
{{-- {{dd($arr_data)}}
 --}}<div class="listing-main-div">
    <div class="container-fluid">
        @include('front.search._search_readcrum_header')
        <div class="row">
            <!-- @include('front.search._front_sidebar') -->
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="product-section">
                    <div class="row">
                        @if(isset($arr_data) && sizeof($arr_data)>0)
                        @foreach($arr_data as $representative)
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                            <a href="{{url('/')}}/representative-details?representative_id={{base64_encode($representative->id)}}">
                            <div class="product-list-pro">
                                <div class="pro-img-list">
                                    @php
                                        $profile_image = getProfileImage($representative->profile_image);
                                    @endphp 
                                    <img class="portrait" src="{{$profile_image}}" alt="" />
                                </div>
                                <div class="pro-content-list">
                                    <div class="pro-sub-title-list">{{isset($representative->user_name)?ucfirst($representative->user_name):""}}</div>
                                    <div class="pro-title-list">{{isset($representative->nationality)?ucfirst($representative->nationality):""}}</div>
                                </div>
                            </div>
                            </a>
                        </div>
                        @endforeach  
                        @else    
                           <div class="col-md-12"><div class="not-found-data">This division did not match any representative.</div></div>
                        @endif     
                        </div>
                    </div>

                </div>
                <div class="pagination-bar">
                   {{$arr_pagination->render()}}      
                </div>

            </div>
        </div>
    </div>
</div>

@endsection