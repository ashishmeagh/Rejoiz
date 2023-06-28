@extends('front.layout.master')
@section('main_content')
<style type="text/css">
  .gallery-img-list {height:320px;}
.btn-space-top
{
margin:250px 0 0;
}
/*header{display: none;}*/
</style>

<div class="breadcrum-faq">
  <a href="{{url('/')}}">Home</a> <span>></span> <div class="acrivepage">FAQ</div>
</div>


<div class="container">
  <div class="gallery-img-list-main">
    <div class="start-title">Don’t know where to start?</div>
    <div class="gallery-img-list">
      <img src="{{url('/')}}/assets/front/images/maker.jpg" alt="" />
      <a href="{{url('/'.'faq/vendor')}}" class="maker-links-btn">Vendors</a>
    </div>
    <div class="gallery-img-list">
      <img src="{{url('/')}}/assets/front/images/retailer.jpg" alt="" />
      <a href="{{url('/'.'faq/retailer')}}" class="maker-links-btn">Customers</a>
    </div>
  </div>
</div>



<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
  
  
  
  <div >
    @php
    $banner_img = url('/storage/app/2OIyX8JrvsjDsFDXGECaP28UN6PZGGmKsCAMDGLG.jpeg');
    @endphp
    
    <div style="background: url({{$banner_img}})no-repeat ;background-position: center center; -webkit-background-size: cover;
      -moz-background-size: cover; -o-background-size: cover; background-size: cover; margin: 0px;padding: 0;">

      
    </div>
    
  </div>
@endsection