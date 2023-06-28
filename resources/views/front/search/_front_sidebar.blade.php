
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/fontawesome.min.css">
<script type='text/javascript' src='{{url('/')}}/assets/front/js/menu_jquery.js'></script>
<div class="col-md-3 side-bar-width side-bar-width-new">
    <div class="sidebar-main-lisitng">
        <div class="title-categorynm click-category"><span class="cat-none">Category</span> 
            <img src="{{url('/')}}/assets/front/images/chevron-down.svg" alt="">
        </div>
        <style>
        .relative{
            position: relative;
        }
        .submenu{text-indent: 0 !important;padding-left: 15px;}
        .submenu.pl-0{padding-left:0;}
    </style>
    <div id="cssmenu1" class="shows-category scrollbar">
        <ul>
            @php


            $preset_search_type =  isset($search_value['search_type']) ? $search_value['search_type']:'';
            $preset_search_term = isset($search_value['search_term'])? $search_value['search_term']:'';
            $preset_category_id =  isset($search_value['category_id']) ? base64_decode($search_value['category_id']) : false;
            $preset_subcategory_id =  isset($search_value['subcategory']) ? base64_decode($search_value['subcategory']) : false;

            $preset_thirdsubcategory_id =  isset($search_value['thirdsubcategory']) ? base64_decode($search_value['thirdsubcategory']) : false;


            $preset_fourthsubcategory_id =  isset($search_value['fourthsubcategory']) ? base64_decode($search_value['fourthsubcategory']) : false;

            $temp_preset_category_id_arr = isset($search_value['category_id_arr'])?$search_value['category_id_arr']: false;

            $preset_category_id_arr = array();
            if($temp_preset_category_id_arr!=false)
            {
                $temp_preset_category_id_arr = explode(',',$temp_preset_category_id_arr);
                $temp_preset_category_id_arr = str_replace('"','',$temp_preset_category_id_arr);
                $temp_preset_category_id_arr = str_replace(']','',$temp_preset_category_id_arr);
                $temp_preset_category_id_arr = str_replace('[','',$temp_preset_category_id_arr);
                $preset_category_id_arr = $temp_preset_category_id_arr;
            }

            if($preset_category_id!=false)
            {
               $preset_category_id_arr = array();
           }

           $label = 'Products';
           $search_href = url('/search');
           $built_search_value = $search_value;

           // dd($preset_category_id,$arr_category);
           @endphp


           @if(isset($arr_category) && sizeof($arr_category)>0)

           @foreach($arr_category as $category)
           @php
           //dd($arr_category,$preset_category_id_arr);
           $category['subcategory_details'] = array_column($category['subcategory_details'], null,'id');

           if(isset($built_search_value) && count($built_search_value)>0)
           {
            $built_search_value['category_id'] = base64_encode($category['id']);
        }



        unset($built_search_value['subcategory']);
        unset($built_search_value['search_term']);

        @endphp

        <li class='has-sub'>

            <a class="{{ $preset_category_id == $category['id'] ? 'active' : '' }}
            {{ in_array($category['id'],$preset_category_id_arr)? 'active' : '' }}"
            href="{{ $search_href."?".http_build_query($built_search_value)}}" >
            <span class="submenu1">
                {{isset($category['category_name'])?ucfirst($category['category_name']):""}}
            </span>

        </a>

        @if(isset($category['subcategory_details']) && sizeof($category['subcategory_details'])>0)

        @if(isset($category['subcategory_details'][$preset_subcategory_id]))
        <!-- <span class="plus-icon lnkclk"> <i class="fa fa-minus" ></i> -->
            <span class="plus-icon lnkclk"> <i class="fa fa-minus"></i></span>
            <!-- <img class="fa fa-minus" src="{{url('/assets/front/images/minus.svg')}}"> -->
        </span>
        @else
        <span class="plus-icon lnkclk"> <i class="fa fa-plus" ></i>
            <!-- <img class="fa fa-plus" src="{{url('/assets/front/images/plus.svg')}}"> -->
        </span>
        @endif



        <ul class="{{ isset($category['subcategory_details'][$preset_subcategory_id]) ? "sub_menu submenu link-act" : "sub_menu submenu"}}" style="{{ isset($category['subcategory_details'][$preset_subcategory_id]) ? "display:block" : "display:none"}}">

            @foreach($category['subcategory_details'] as $sub_category)
            @php
            $built_search_value['subcategory'] = base64_encode($sub_category['id']);

            unset($built_search_value['fourthsubcategory']);
            unset($built_search_value['thirdsubcategory']);
            unset($built_search_value['search_term']);


            @endphp
            <li class="has-sub">
                <a class="{{ $preset_subcategory_id == $sub_category['id'] ? "active" : "" }}"  
                href="{{ $search_href."?".http_build_query($built_search_value)}}"
                >
                <span class="submenu1">{{ucfirst($sub_category['subcategory_name'])}} </span>




            </a>



            <!-- start third category show -->
            @if(isset($thirdsub_categories_arr) && sizeof($thirdsub_categories_arr)>0 )
            @foreach($thirdsub_categories_arr as $thirdsub_category)

            @if(isset($thirdsub_category['sub_category_id']) && $thirdsub_category['sub_category_id'] == $sub_category['id'])

            @if(isset($preset_thirdsubcategory_id)  && $preset_thirdsubcategory_id !="" && $thirdsub_category['id'] == $preset_thirdsubcategory_id )

            <span class="plus-icon lnkclk "> 

                <i class="fa fa-minus" ></i>

            </span>
            @endif

            @endif 

            @endforeach

            @if($preset_thirdsubcategory_id == "")
            @if(isset($unique_sub_id))

            @foreach($unique_sub_id as $val)

            @if($val == $sub_category['id'])


            <span class="plus-icon lnkclk"> 

                <i class="fa fa-plus" ></i>
                <i class="fa fa-minus " style="display: none;"></i>

            </span>

            @endif

            @endforeach

            @endif
            @endif













<!-- <ul class="sub-me sub_menu">
-->
<ul class="{{ isset($preset_thirdsubcategory_id) && $preset_thirdsubcategory_id !="" && $sub_category['id'] == $preset_thirdsubcategory_id  && $preset_subcategory_id == $sub_category['id'] ? "sub_menu submenu " : "sub_menu submenu"}}" style="{{ isset($preset_thirdsubcategory_id) && $preset_thirdsubcategory_id !=""  && $preset_subcategory_id == $sub_category['id'] ? "display:block" : "display:none"}} ">




 @foreach($thirdsub_categories_arr as $thirdsub_category)

 @if(isset($thirdsub_category['sub_category_id']) && $thirdsub_category['sub_category_id'] == $sub_category['id'])

 @php
 $built_search_value['thirdsubcategory'] = base64_encode($thirdsub_category['id']);


 unset($built_search_value['fourthsubcategory']);
 unset($built_search_value['search_term']);


 @endphp

 <li class="relative">
   <a class="{{ $preset_thirdsubcategory_id == $thirdsub_category['id'] ? "active" : "" }}"  
   href="{{ $search_href."?".http_build_query($built_search_value)}}"
   > 
   <span class="submenu1">{{$thirdsub_category['third_sub_category_name']}} </span>
</a> 


@if(isset($fourthsub_categories_arr))

@foreach($fourthsub_categories_arr as $fourthsub_category)


@if(isset($fourthsub_category['third_sub_category_id']) && $fourthsub_category['third_sub_category_id'] == $thirdsub_category['id'])

@if(isset($preset_fourthsubcategory_id) && $preset_fourthsubcategory_id != "" && $preset_thirdsubcategory_id == $thirdsub_category['id'] )

<span class="plus-icon lnkclk fourth_cat_plus" id="fourth_cat_plus_{{ $thirdsub_category['id'] }}"> 
    <i class="fa fa-minus" ></i>

</span>




@endif
@endif

@endforeach
@if($preset_fourthsubcategory_id == "")
            @if(isset($unique_thirdsub_id))

            @foreach($unique_thirdsub_id as $res)

            @if($res == $thirdsub_category['id'])


            <span class="plus-icon lnkclk fourth_cat_plus" id="fourth_cat_plus_{{ $thirdsub_category['id'] }}"> 
    <i class="fa fa-plus" ></i>

</span> 

            @endif

            @endforeach

            @endif
            @endif


</li> 


<!-- start fourth category show -->


<!--  <ul class="sub-me sub_menu fourth_ul"  id="fourth_ul_{{ $thirdsub_category['id'] }}" > -->

   <ul class="{{ isset($preset_fourthsubcategory_id) && $preset_fourthsubcategory_id !="" && $preset_thirdsubcategory_id == $thirdsub_category['id']  ? "sub-me sub_menu fourth_ul " : "sub-me sub_menu fourth_ul"}}" style="{{ isset($preset_fourthsubcategory_id) && $preset_fourthsubcategory_id !="" && $preset_thirdsubcategory_id == $thirdsub_category['id'] ? "display:block" : "display:none"}} " id="fourth_ul_{{ $thirdsub_category['id'] }}">

     @foreach($fourthsub_categories_arr as $fourthsub_category)

     @if(isset($fourthsub_category['third_sub_category_id']) && $fourthsub_category['third_sub_category_id'] == $thirdsub_category['id'])

     @php
     $built_search_value['fourthsubcategory'] = base64_encode($fourthsub_category['id']);


     @endphp



     <li class="has-sub">
       <a class="{{ $preset_fourthsubcategory_id == $fourthsub_category['id'] ? "active" : "" }}"  
       href="{{ $search_href."?".http_build_query($built_search_value)}}"
       > 
       <span class="submenu1">{{$fourthsub_category['fourth_sub_category_name']}}  </span>
   </a> 

</li> 




@endif

@endforeach
</ul>
@endif

<!-- end fourth category show -->

@endif

@endforeach
</ul>

@endif


</li> 
<!-- end third category show -->
@endforeach

<!--  <i class="fa fa-plus" ></i> -->
</ul> <!-- //second category -->

@endif    
</li>
@endforeach
@endif

</ul>
</div>



{{--   <div class="title-categorynm border-top-bottom">Filters</div>
<div class="maker-value-title">Maker Values</div>
<div class="checkbox-listing-pg">
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check1" />
        <label for="check1">Not Sold On Amazon</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check2" />
        <label for="check2">Made in USA</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check3" />
        <label for="check3">Handmade</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check4" />
        <label for="check4">Eco-friendly</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check5" />
        <label for="check35">Charitable</label>
    </div>
</div>
<hr> --}}
{{--  <div class="maker-value-title">Maker Minimum</div>
<div class="checkbox-listing-pg">
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check6" />
        <label for="check6">No Minimum</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check7" />
        <label for="check7">$100 & Below</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check8" />
        <label for="check8">$200 & Below</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check9" />
        <label for="check9">$300 & Below</label>
    </div>
    <div class="checkbx">
        <input type="checkbox" class="filled-in" id="check10" />
        <label for="check10">$400 & Below</label>
    </div>
</div>
--}}
{{-- <hr>
<div class="maker-value-title">Location</div>
<div class="searchbox-main">
    <input type="text" placeholder="Search Location" />
</div> --}}
</div>
</div>
<script>
    $(document).ready(function(){
        $(".click-category").click(function(){

            $(".shows-category").toggleClass("showscategorydiv");
        });
    });




</script>