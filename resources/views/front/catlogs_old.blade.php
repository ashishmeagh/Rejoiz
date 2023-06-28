@extends('front.layout.master')
@section('main_content')

<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/flip_css/jquery.jscrollpane.custom.css" />
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/flip_css/bookblock.css" />
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/flip_css/custom.css" />
<script src="{{url('/')}}/assets/flip_js/modernizr.custom.79639.js"></script>




<div id="container" class="booklet">  
  <div class="bb-custom-wrapper">
    <h4 class="text-center flipbook_ttl">{{$catalog_name or ''}}</h4>
    <div id="bb-bookblock" class="bb-bookblock">
        @if(isset($catalog_data) && count($catalog_data)>0)
      
            @foreach($catalog_data  as $key => $catalog)

                <div class="bb-item" id="item{{$key}}">       
                    <div class="content">
                        <div class="scroller">

                            @foreach($catalog  as $key1 => $catalogPage)

                                @php
                 
                                    $img = isset($catalogPage['get_catalog_image_data'][0]['image'])?$catalogPage['get_catalog_image_data'][0]['image']:'';

                                @endphp
                                @php
                                    $pageSequence = $key1%2;
                                @endphp
                                @if($pageSequence == '0')

                                    <div class="left-page bb-nav-prev">
                                       
                                        <div class="contentbox">
                                            @if(isset($catalogPage['page_type']) && $catalogPage['page_type']!='' && $catalogPage['page_type'] == 'single_image')  
                                                <img src="{{url('/storage/app/')}}/{{$img}}" alt="">

                                               
                                            @else
                                                @if(isset($catalogPage['get_catalog_image_data']) && count($catalogPage['get_catalog_image_data'])>0) 
                                                <div class="row">
                                                    @foreach($catalogPage['get_catalog_image_data'] as $catalog_image)
                                                    
                                                    <div class="col-sm-6 probox text-center">

                                          @php
                                             
                                

                                           $product_id = isset($catalog_image['product_deta']['product_id'])?base64_encode($catalog_image['product_deta']['product_id']):false;

                                           $vendor_id = isset($catalog_image['product_deta']['product_details']['user_id'])?base64_encode($catalog_image['product_deta']['product_details']['user_id']):false;

                                           $brand_id = isset($catalog_image['product_deta']['product_details']['brand'])?base64_encode($catalog_image['product_deta']['product_details']['brand']):false;

                                           $sku = isset($catalog_image['sku'])?base64_encode($catalog_image['sku']):false;


                                            $product_details_url =  url('/').'/'.'vendor-details?product_id='.$product_id.'&vendor_id='.$vendor_id.'&brand_id='.$brand_id.'&sku='.$sku;

                                          @endphp              
                                                
                                                      <a href="{{isset($product_details_url)?$product_details_url:''}}" target="_blank">
                                                       
                                                            <div class="imgbox">
                                                                <img src="{{url('/storage/app/')}}/{{$catalog_image['image']}}" alt="" title="{{isset($catalog_image['product_deta']['product_details']['product_name'])?$catalog_image['product_deta']['product_details']['product_name']:''}}">
                                                               
                                                            </div>
                                                       
                                                         <div class="contentprobox">
                                                            <h4>{{isset($catalog_image['product_deta']['product_details']['product_name'])?$catalog_image['product_deta']['product_details']['product_name']:'N/A'}}</h4>


                                                            <h5><span class="fa fa-dollar"></span>{{isset($catalog_image['product_deta']['product_details']['unit_wholsale_price'])?$catalog_image['product_deta']['product_details']['unit_wholsale_price']:0.00}}</h5>
                                                         </div>
                                                      </a>
                                                    </div>
     
                                                   
                                                    @endforeach
                                                     </div>
                                                 @endif       

                                             

                                            @endif    
                                        </div>
                                    </div>
                                @elseif($pageSequence == '1')
                                    <div class="right-page bb-nav-next">
                                    
                                        <div class="contentbox">
                                           @if(isset($catalogPage['page_type']) && $catalogPage['page_type']!='' && $catalogPage['page_type'] == 'single_image')  
                                               <img src="{{url('/storage/app/')}}/{{$img}}" alt="">
                                               
                                           @else
                                              
                                                @if(isset($catalogPage['get_catalog_image_data']) && count($catalogPage['get_catalog_image_data'])>0) 
                                                <div class="row">
                                                    @foreach($catalogPage['get_catalog_image_data'] as $catalog_image)
                                                    
                                                    <div class="col-sm-6 probox text-center">

                                                @php
                                             
                                           

                                           $product_id = isset($catalog_image['product_deta']['product_id'])?base64_encode($catalog_image['product_deta']['product_id']):false;

                                           $vendor_id = isset($catalog_image['product_deta']['product_details']['user_id'])?base64_encode($catalog_image['product_deta']['product_details']['user_id']):false;

                                           $brand_id = isset($catalog_image['product_deta']['product_details']['brand'])?base64_encode($catalog_image['product_deta']['product_details']['brand']):false;

                                           $sku = isset($catalog_image['sku'])?base64_encode($catalog_image['sku']):false;

                                            $product_details_url =  url('/').'/'.'vendor-details?product_id='.$product_id.'&vendor_id='.$vendor_id.'&brand_id='.$brand_id.'&sku='.$sku;


                                                @endphp     
                                                
                                                      <a href="{{isset($product_details_url)?$product_details_url:''}}" target="_blank">
                                                        
                                                            <div class="imgbox">
                                                                <img src="{{url('/storage/app/')}}/{{$catalog_image['image']}}" alt="" title="{{isset($catalog_image['product_deta']['product_details']['product_name'])?$catalog_image['product_deta']['product_details']['product_name']:''}}">
                                                                
                                                            </div>
                                                       <div class="contentprobox">

                                                         <h4>{{isset($catalog_image['product_deta']['product_details']['product_name'])?$catalog_image['product_deta']['product_details']['product_name']:'N/A'}}</h4>

                                                          
                                                            <h5><span class="fa fa-dollar"></span>{{isset($catalog_image['product_deta']['product_details']['unit_wholsale_price'])?$catalog_image['product_deta']['product_details']['unit_wholsale_price']:0.00}}</h5>

                                                     </div>
                                                      </a>
                                                    </div>

                                                  
                                                    @endforeach
                                                      </div>
                                                 @endif       

                                               

                                           @endif    
                                       </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="clearfix"></div>                        

                        </div>
                    </div>
                </div>
            @endforeach
        @endif    

    </div>

  
  @if(isset($catalog_data) && count($catalog_data)>0)
    <nav>
        <span id="bb-nav-prev">&larr;</span>
        <span id="bb-nav-next">&rarr;</span>
    </nav>
    @endif
  </div>

    
        
</div>

  <!-- Script -->
    <script src="{{url('/')}}/assets/flip_js/jquery.jscrollpane.min.js"></script>
    <script src="{{url('/')}}/assets/flip_js/jquery.bookblock.js"></script>
    <script src="{{url('/')}}/assets/flip_js/page.js"></script>
    <script>

        
    $(function()
    {
      Page.init();

    });

    </script>



@stop