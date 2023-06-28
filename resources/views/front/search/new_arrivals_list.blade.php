@extends('front.layout.master')
@section('main_content')

<!-- <link href="{{url('/')}}/assets/front/css/sample.css" rel="stylesheet" type="text/css" /> -->
<div class="gallery-of-photo">
<div class="container">
    <div class="arivl-cont producthomk-list-pro mobileviewset shop_now_page">
        <div class="title-arrvls-heading">{{$page_title or 'Category List'}}</div>
        <div class="row">
            @if(isset($arr_category) && sizeof($arr_category)>0)        
            @foreach ($arr_category as $key =>$category)

             @php

                  $category_img = false;

                  $category_base_img = isset($category['category_image']) ? $category['category_image'] : false;
                  $category_image_base_path  = base_path('storage/app/'.$category_base_img);
                  $category_default_image =  url('/assets/images/no-product-img-found.jpg');
                  $category_img = image_resize($category_image_base_path,442,320,$category_default_image);


               @endphp

              <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
                  <figure class="effect-lily">
                         <img src="{{$category_img or ''}}" alt="img12">
                         <figcaption>
                            <div class="main-imghr">
                               <div class="hovercontent">
                                  <h2>{{ucfirst($category['category_name'])}}</h2>
                               <div class="shop-now">
                                  <a class="full-a" href="{{url('/')}}/search?category_id={{ base64_encode($category['id'])}}">Shop Now</a>
                               </div>
                               </div>
                            </div>
                            <a class="full-a" href="{{url('/')}}/search">View more</a>
                         </figcaption>
                  </figure>
              </div>
            @endforeach
            @endif
            
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
  (function () {
        window.onpageshow = function(event) {
          if (event.persisted) {
            window.location.reload();
          }
        };
      })();
</script>
@stop