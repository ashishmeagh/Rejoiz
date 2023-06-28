@extends('front.layout.master') 
@section('main_content')

<style type="text/css">
  .atoz-link a.allvendors-txt{
       display: inline-block;
    width: auto;
    line-height: 23px;
    font-size: 12px;
  }
</style>

<div class="listing-main-div">
    <div class="container">        
              
 
   @include('admin.layout._operation_status')
    <!-- breadcrumbs -->
    <div class="pagename-main pg-flots front_breadcrumb">
       <div class="atoz-title">American Best Sellers</div>
        <div class="pagename-right">
          <a href="{{url('/')}}">Home</a> 
            <span class="slash">/</span>
            <span>{{isset($search_value['search_term'])?$search_value['search_term']:""}}</span>
            <span class="slash last-beadcrum">American Best Sellers</span>
        </div>
    </div>  

                <div class="product-section vendor-logo-listing top-spacej-one">
                    <div class="atoz-main">
                       

                       <div class="atoz-link">
                         <a href="{{url('/search_vendor')}}" class="btn btn-default allvendors-txt @if(Request::segment(1)=='search_vendor' && Request::segment(2)=='') active
                        @endif">American Best Sellers</a>
                           <a href="{{url('/search_vendor/')}}/a" data-id="a"
                              @if(Request::segment(2) == 'a') class="active" @endif
                             onclick="setActiveClass($(this));">a</a>
                           <a href="{{url('/search_vendor/')}}/b"  @if(Request::segment(2) == 'b') class="active" @endif>b</a>

                           <a href="{{url('/search_vendor/')}}/c" @if(Request::segment(2) == 'c') class="active" @endif>c</a>

                           <a href="{{url('/search_vendor/')}}/d" @if(Request::segment(2) == 'd') class="active" @endif>d</a>

                           <a href="{{url('/search_vendor/')}}/e" @if(Request::segment(2) == 'e') class="active" @endif>e</a>
                           <a href="{{url('/search_vendor/')}}/f"  @if(Request::segment(2) == 'f') class="active" @endif>f</a>
                           <a href="{{url('/search_vendor/')}}/g" @if(Request::segment(2) == 'g') class="active" @endif>g</a>

                           <a href="{{url('/search_vendor/')}}/h" @if(Request::segment(2) == 'h') class="active" @endif>h</a>

                           <a href="{{url('/search_vendor/')}}/i" @if(Request::segment(2) == 'i') class="active" @endif>i</a>

                           <a href="{{url('/search_vendor/')}}/j" @if(Request::segment(2) == 'j') class="active" @endif>j</a>

                           <a href="{{url('/search_vendor/')}}/k" @if(Request::segment(2) == 'k') class="active" @endif>k</a>

                           <a href="{{url('/search_vendor/')}}/l" @if(Request::segment(2) == 'l') class="active" @endif>l</a>

                           <a href="{{url('/search_vendor/')}}/m"  @if(Request::segment(2) == 'm') class="active" @endif>m</a>

                           <a href="{{url('/search_vendor/')}}/n" @if(Request::segment(2) == 'n') class="active" @endif>n</a>

                           <a href="{{url('/search_vendor/')}}/o" @if(Request::segment(2) == 'o') class="active" @endif>o</a>

                           <a href="{{url('/search_vendor/')}}/p" @if(Request::segment(2) == 'p') class="active" @endif>p</a>
                           <a href="{{url('/search_vendor/')}}/q" @if(Request::segment(2) == 'q') class="active" @endif>q</a>
                           <a href="{{url('/search_vendor/')}}/r" @if(Request::segment(2) == 'r') class="active" @endif>r</a>
                           <a href="{{url('/search_vendor/')}}/s" @if(Request::segment(2) == 's') class="active" @endif>s</a>
                           <a href="{{url('/search_vendor/')}}/t" @if(Request::segment(2) == 't') class="active" @endif>t</a>
                           <a href="{{url('/search_vendor/')}}/u" @if(Request::segment(2) == 'u') class="active" @endif>u</a>
                           <a href="{{url('/search_vendor/')}}/v" @if(Request::segment(2) == 'v') class="active" @endif>v</a>
                           <a href="{{url('/search_vendor/')}}/w"  @if(Request::segment(2) == 'w') class="active" @endif>w</a>
                           <a href="{{url('/search_vendor/')}}/x" @if(Request::segment(2) == 'x') class="active" @endif>x</a>
                           <a href="{{url('/search_vendor/')}}/y" @if(Request::segment(2) == 'y') class="active" @endif>y</a>
                           <a href="{{url('/search_vendor/')}}/z" @if(Request::segment(2) == 'z') class="active" @endif>z</a>

                           <a href="{{url('/search_vendor/')}}/&" @if(Request::segment(2) == '&') class="active" @endif>#</a>
                       </div>
                    </div>
                    <div class="row">
                        @if(isset($vendors_arr['data']) && sizeof($vendors_arr['data'])>0)

                            @foreach($vendors_arr['data'] as $maker)
                           
                              
                              <div class="col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                  <a href="{{url('/')}}/vendor-details?vendor_id={{base64_encode($maker->id)}}">
                             
                                    <div class="product-list-pro">

                                        <div class="pro-img-list">
                                        
                                          @php
                                         
                                            if(isset($maker->store_profile_image) && $maker->store_profile_image!='' && file_exists(base_path().'/storage/app/'.$maker->store_profile_image))
                                            {
                                           
                                              $shop_img = url('/storage/app/'.$maker->store_profile_image);
                                            }
                                            else
                                            { 

                                              $shop_img = url('/assets/images/no-product-img-found.jpg');
                                            }
                                            @endphp

                                            <img class="potrait" src="{{$shop_img or ''}}" alt="" />
                                        </div>

                                        <div class="pro-content-list">
                                        <div class="pro-sub-title-list"> {{isset($maker->company_name)?strtoupper($maker->company_name):'N/A'}}</div>
                                          
                                        </div>
                                     </div>
                               
                                    </a>
                                </div>
                            @endforeach  
                        @else    
                           <div class="col-md-12"><div class="not-found-data">Your search did not match any vendor.</div></div>
                        @endif     
                        </div>
                    </div>

              
                <div class="pagination-bar">
                   @if(!empty($vendor_pagination))
                      {{$vendor_pagination->render()}}      
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


@endsection