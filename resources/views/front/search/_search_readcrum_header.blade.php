
@php
$search_term = Request::input('search_term');
@endphp
@if(isset($search_value['search_term']) && $search_value['search_term']!="")

<div class="pagename-main">
    <div class="pagename-left">
       
     Search result for <span>"{{isset($search_value['search_term'])?$search_value['search_term']:""}}"</span>
        
    </div>

    <div class="pagename-right"><a href="{{url('/')}}">Home</a> 
        <span class="slash">/</span>
        <span  class="slash last-beadcrum">Products</span>
         <span class="slash">/</span> 
        <span>{{isset($search_value['search_term'])?$search_value['search_term']:""}}</span>
    </div>

    @if(isset($total_results)) 
        <div class="clearfix"></div>
        <div class="results-txt">{{ $total_results or 0 }} results</div>
    @endif
    
    <div class="clearfix"></div>
</div>
<hr> 
@elseif(isset($search_value['category_id']) && $search_value['category_id']!="")
<div class="pagename-main">
    <?php $category_name = get_category(isset($search_value['category_id'])?base64_decode($search_value['category_id']):'') ?>
    <?php $sub_category_name = get_sub_category(isset($search_value['subcategory'])?base64_decode($search_value['subcategory']):'') ?>
    <?php $sec_sub_category_name = get_second_subcategory_name(isset($search_value['thirdsubcategory'])?base64_decode($search_value['thirdsubcategory']):'') ?>
    <?php $third_sub_category_name = get_third_subcategory_name(isset($search_value['fourthsubcategory'])?base64_decode($search_value['fourthsubcategory']):'') ?>

        <div class="pagename-left">{{isset($category_name['category_name'])?ucfirst($category_name['category_name']):""}}
         @if(isset($search_value['subcategory']) || isset($search_value['thirdsubcategory']) || isset($search_value['fourthsubcategory'])) 
            <span class="slash">/</span> 
            @if(isset($search_value['thirdsubcategory']))
                {{isset($sub_category_name['subcategory_name'])?ucfirst($sub_category_name['subcategory_name']):""}}
            @else
            <span>{{isset($sub_category_name['subcategory_name'])?ucfirst($sub_category_name['subcategory_name']):""}}</span>
            @endif
            @if(isset($search_value['thirdsubcategory']) || isset($search_value['fourthsubcategory']))
                <span class="slash">/</span> 
                @if(isset($search_value['fourthsubcategory']))
                    {{isset($sec_sub_category_name)?ucfirst($sec_sub_category_name):""}}
                @else
                <span>{{isset($sec_sub_category_name)?ucfirst($sec_sub_category_name):""}}</span>
                @endif
                @if(isset($search_value['fourthsubcategory']))
                    <span class="slash">/</span> 
                    <span>{{isset($third_sub_category_name)?ucfirst($third_sub_category_name):""}}</span>
                @endif
            @endif
         @endif

        </div>

         <div class="pagename-right">
            <a href="{{url('/')}}">Home /</a>
            <a href="{{url('/')}}/search"><span class="slash">Products /</span> </a>
            <span>
                @if(isset($search_value['fourthsubcategory']))
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}">
                        {{isset($category_name['category_name'])?ucfirst($category_name['category_name']):""}}
                    </a>
                    <span class="slash">/</span> 
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}&subcategory={{$search_value['subcategory'] or ''}}">
                    {{isset($sub_category_name['subcategory_name'])?ucfirst($sub_category_name['subcategory_name']):""}}
                    </a>
                    <span class="slash">/</span> 
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}&subcategory={{$search_value['subcategory'] or ''}}&thirdsubcategory={{$search_value['thirdsubcategory'] or ''}}">
                    {{isset($sec_sub_category_name)?ucfirst($sec_sub_category_name):""}}
                    </a>
                    <span class="slash">/</span> 
                    <span>{{isset($third_sub_category_name)?ucfirst($third_sub_category_name):""}}
                @elseif(isset($search_value['thirdsubcategory']))
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}">
                        {{isset($category_name['category_name'])?ucfirst($category_name['category_name']):""}}
                    </a>
                    <span class="slash">/</span> 
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}&subcategory={{$search_value['subcategory'] or ''}}">
                        {{isset($sub_category_name['subcategory_name'])?ucfirst($sub_category_name['subcategory_name']):""}}
                    </a>
                    <span class="slash">/</span> 
                    <span>{{isset($sec_sub_category_name)?ucfirst($sec_sub_category_name):""}}
                @elseif(isset($search_value['subcategory']))
                    <a href="{{url('/search?&category_id=')}}{{$search_value['category_id'] or ''}}">
                        {{isset($category_name['category_name'])?ucfirst($category_name['category_name']):""}}
                    </a>
                    <span class="slash">/</span> <span>{{isset($sub_category_name['subcategory_name'])?ucfirst($sub_category_name['subcategory_name']):""}}
                @else
                    {{isset($category_name['category_name'])?ucfirst($category_name['category_name']):""}}
                @endif

            </span>
        </div>

        @if(isset($total_results)) 
            <div class="clearfix"></div>
            <div class="results-txt">{{ $total_results or 0 }} results</div>
        @else
            <div class="clearfix"></div>
            <div class="results-txt">0 results</div>
        @endif

       

        <div class="clearfix"></div>
</div>
<hr> 
@elseif(isset($search_value['search_type']) && $search_value['search_type']!="")

<div class="pagename-main">

    @if($search_value['search_type'] == 'brand')
        <div class="pagename-left">All Products
        </div> 

        <div class="pagename-right">
            <a href="{{url('/')}}">Home</a> 
            <span class="slash">/</span>
            <span>All Products</span>
        </div>

    @elseif($search_value['search_type'] == 'maker')
        <div class="pagename-left">All Vendors
        </div> 

        <div class="pagename-right">
            <a href="{{url('/')}}">Home</a> 
            <span class="slash">/</span>
            <span>All Vendors</span>
        </div>
    @elseif($search_value['search_type'] == 'category')
        <div class="pagename-left">All Categories
        </div> 

        <div class="pagename-right">
            <a href="{{url('/')}}">Home</a> 
            <span class="slash">/</span>
            <span>All Categories</span>
        </div>    
    
    @else
        <div class="pagename-left">{{isset($search_value['search_type'])?'All '.ucfirst($search_value['search_type'])."s":""}}
        </div> 

        <div class="pagename-right">
            <a href="{{url('/')}}">Home</a> 
            <span class="slash">/</span>
            <span>{{isset($search_value['search_type'])?'All '.ucfirst($search_value['search_type']).'s':""}}</span>
        </div>
    @endif

    @if(isset($total_results)) 
        <div class="clearfix"></div>
        <div class="results-txt">{{ $total_results or 0 }} results</div>
    @endif

   

    <div class="clearfix"></div>
</div>


<hr> 

@elseif(isset($search_value['country_id']) && $search_value['country_id']!="")

  <div class="pagename-main pro_lisiting_breadcrumb_class">
   <div class="pagename-left">American Best Sellers
  
    @if(isset($total_results))
     <div class="clearfix"></div>
     <div class="results-txt">{{ $total_results or 0 }} results</div>
     @else
       <div class="clearfix"></div>
     <div class="results-txt"> 0 results</div>
     @endif
   </div>

   <div class="pagename-right">
      <a href="{{url('/')}}">Home</a> 
      <span class="slash">/</span>

      @if($search_term=='')
      <span class="active">American Best Sellers</span>
      @endif
      
      @if($search_term!=null || $search_term!='')
      <span class="slash">American Best Sellers/</span>
      <span class="active">{{$search_term}}</span>
      @endif
   
   </div>

   
   <div class="clearfix"></div>
</div>
@else 

<div class="pagename-main pro_lisiting_breadcrumb_class">
   <div class="pagename-left">All Products
  
    @if(isset($total_results))
     <div class="clearfix"></div>
     <div class="results-txt">{{ $total_results or 0 }} results</div>
     @else
       <div class="clearfix"></div>
     <div class="results-txt"> 0 results</div>
     @endif
   </div>



   <div class="pagename-right">
      <a href="{{url('/')}}">Home</a> 
      <span class="slash">/</span>

      @if($search_term=='')
      <span class="active">Products</span>
      @endif
      
      @if($search_term!=null || $search_term!='')
      <span class="slash">Products/</span>
      <span class="active">{{$search_term}}</span>
      @endif
   
   </div>

   
   <div class="clearfix"></div>
</div>
<hr> 
@endif
