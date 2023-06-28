<style>
  /*.filter-selected {
    background-color: #F8BBD0 !important;
  }*/

</style>





@php
  $is_price_filter_selected = isset($search_value['price:low']) && isset($search_value['price:high']);

  $is_vendor_min_filter_selected = isset($search_value['vendor_minimum_low']) && isset($search_value['vendor_minimum_high']);

  $is_vendor_special_filter_selected = isset($search_value['free_shipping']) || isset($search_value['percent_of'])||isset($search_value['doller_of']);

  $is_lead_time_filter_selected = isset($search_value['lead_time_min']) && isset($search_value['lead_time_max']);

  $is_brand_filter_selected = isset($search_value['brands'][0]);

//dd("ok",$brands_arr);
  //dd($is_brand_filter_selected);

@endphp

<div class ="filter_container newclass-filter">
    <div class="su-menu res-su-menu style-4 dropdown mm-view">
      
      <button class="button-ff-year dropdown-toggle {{ $is_price_filter_selected ? 'filter-selected' : '' }}" type="button" data-toggle="dropdown">Price

      
        @if($is_price_filter_selected)
          
          @if($search_value['price:low']=="100")
            <div class="title-mdl-filter">$100 plus</div>
          @else
          <div class="title-mdl-filter">${{$search_value['price:low']}}-${{$search_value['price:high']}}</div>
          @endif
           &nbsp<span class="close" id="price_clear">&times;</span>

        @else
          <span class="caret-arrow"><img class="fa fa-plus" src="{{url('/assets/front/images/select-arrow.png')}}"></span>
        @endif
       
      </li></button>

      <ul class="dropdown-menu dropdown-filter-menu ">
        <div class="header-dropdn">
         <div class="closedrop-left">
           <img src="{{url('/assets/front/images/popup-close-btn.png')}}">
         </div> 
         <div class="title-mdl-filter">Price</div>

         <div class="clear-class-dv"><button width="100px" class="btn clearbtn mr-2" id = "price_unset">Clear</button></div>
         <div class="clearfix"></div>
       </div>
       <li><input type="radio" id="price1"  name="price" value="0-10" class =" price_btn" {{ ((isset($search_value['price:low']) && ($search_value['price:low'])=="0"))? "checked" : "" }}><label for ="price1">$0 - $10</label></li>

       <li><input type="radio" id="price2" name="price" value="11-30" class="price_btn" {{ ((isset($search_value['price:low']) && ($search_value['price:low'])=="11"))? "checked" : "" }}><label for ="price2">$11 - $30</label></li>

       <li><input type="radio" id="price3" name="price" value="31-50" class = " price_btn" {{ ((isset($search_value['price:low']) && ($search_value['price:low'])=="31"))? "checked" : "" }}><label for ="price3">$31 - $50</label></li>

       <li><input type="radio" id="price4" name="price" value="51-100" class="price_btn" {{ ((isset($search_value['price:low']) && ($search_value['price:low'])=="51"))? "checked" : "" }}><label for ="price4">$51 - $100</label></li>
       
       <li><input type="radio" id="price5" name="price" value="100-1000"class =" price_btn" {{ ((isset($search_value['price:low']) && ($search_value['price:low'])=="100"))? "checked" : "" }}><label for ="price5">$100 plus</label></li>
       
       <div class="applydiv text-right">
        <button width="100px" class="btn applybtn" onclick="applyFilter()">Apply</button>
       </div>
        
      </ul>
    </div>
@if(!isset($search_value['vendor_id']))
    <div class="su-menu res-su-menu style-4 dropdown mm-view">
      <button class="button-ff-year dropdown-toggle {{ $is_vendor_min_filter_selected ? 'filter-selected' : '' }}" type="button" data-toggle="dropdown">Minimum


        @if($is_vendor_min_filter_selected)
            
            @if($search_value['vendor_minimum_low'] =="NaN" && $search_value['vendor_minimum_high']=="NaN")
            
            <div class="title-mdl-filter">No Minimum</div>
            
            @elseif($search_value['vendor_minimum_low'] !="301")      
            
            <div class="title-mdl-filter">${{$search_value['vendor_minimum_low']}}-${{$search_value['vendor_minimum_high']}}</div>
            
            

            @else($search_value['vendor_minimum_low'] =="301")
            
            <div class="title-mdl-filter">${{$search_value['vendor_minimum_low']}} or more</div>
            
            @endif     


            &nbsp<span class="close" id = "vendor_minimum_clear">&times;</span>
        @else
          <span class="caret-arrow"><img class="fa fa-plus" src="{{url('/assets/front/images/select-arrow.png')}}"></span>
        @endif

      </li></button>

       
      
      <ul class="dropdown-menu dropdown-filter-menu">
          <div class="header-dropdn">
            <div class="closedrop-left">
              <img src="{{url('/assets/front/images/popup-close-btn.png')}}">
            </div> 
          <div class="title-mdl-filter">Vendor Minimum</div>
          <div class="clear-class-dv"><button width="100px" class="btn clearbtn mr-2" id = "vendor_minimum_unset">Clear</button></div>
          <div class="clearfix"></div>
        </div>
       {{-- <span class ="results-txt" >Vendor Minimum</span> --}}
       
       <li><input type="radio" id="vendor_min_one" name="vendor_minimum" value="" class="vendor_minimum_btn" {{ ((isset($search_value['vendor_minimum_low']) && ($search_value['vendor_minimum_low'])=="NaN"))? "checked" : "" }} ><label for ="vendor_min_one">No minimum</label></li>
       
       <li><input type="radio" id="vendor_min_two" name="vendor_minimum" value="1-100" class ="vendor_minimum_btn" {{ ((isset($search_value['vendor_minimum_low']) && ($search_value['vendor_minimum_low'])=="1"))? "checked" : "0" }}><label for ="vendor_min_two">$1 - $100</label></li>
       
       <li><input type="radio" id = "vendor_min_three" name="vendor_minimum" value="101-200" class = "vendor_minimum_btn" {{ ((isset($search_value['vendor_minimum_low']) && ($search_value['vendor_minimum_low'])=="101"))? "checked" : "" }}><label for ="vendor_min_three">$101 - $200</label></li>
       
       <li><input type="radio" id = "vendor_min_four" name="vendor_minimum" value="201-300" class = "vendor_minimum_btn" {{ ((isset($search_value['vendor_minimum_low']) && ($search_value['vendor_minimum_low'])=="201"))? "checked" : "" }}><label for ="vendor_min_four">$201 - $300</label></li>

       <li><input type="radio" id = "vendor_min_five" name="vendor_minimum" value="301" class = "vendor_minimum_btn" {{ ((isset($search_value['vendor_minimum_low']) && ($search_value['vendor_minimum_low'])=="301"))? "checked" : "" }}><label for ="vendor_min_five">$301 or more</label></li>
       
        <div class="applydiv text-right">
        <button width="100px" class="btn applybtn" onclick="applyFilter()">Apply</button>
        </div>
      </ul>
    </div>
    @endif

    <div class="su-menu res-su-menu style-4 dropdown mm-view">
      <button class="button-ff-year dropdown-toggle {{ $is_vendor_special_filter_selected ? 'filter-selected' : '' }}" type="button" data-toggle="dropdown">Specials


      @if(isset($search_value['free_shipping']) || isset($search_value['percent_of'])||isset($search_value['doller_of']))
        
        
       

        
        @php
        $vendor_special =[];

        if(isset($search_value['free_shipping']))
        array_push($vendor_special,"Frre Shipping");
        
        if(isset($search_value['percent_of']))
        array_push($vendor_special,"% off");
        
        if(isset($search_value['doller_of']))
        array_push($vendor_special,"$ off");
       
        @endphp
       <div class="title-mdl-filter" >({{sizeof($vendor_special)}})</div>

       &nbsp<span class="close" id = "vendor_special_clear">&times;</span>
        
        @else
        <span class="caret-arrow"><img class="fa fa-plus" src="{{url('/assets/front/images/select-arrow.png')}}"></span>
        @endif

      </li></button>
      <ul class="dropdown-menu dropdown-filter-menu">
          <div class="header-dropdn">
     <div class="closedrop-left">
       <img src="{{url('/assets/front/images/popup-close-btn.png')}}">
     </div> 
     <div class="title-mdl-filter">Vendor specials</div>
     <div class="clear-class-dv"><button width="100px" class="btn clearbtn mr-2" id = "vendor_special_unset">Clear</button></div>
     <div class="clearfix"></div>
   </div>
        {{-- <span class ="results-txt" >Vendor specials</span> --}}
      
       <li><input type="checkbox" id = "free_shipping" class="filled-in vendor_special_btn"  name="free_shipping" value="1" {{ ((isset($search_value['free_shipping']) && ($search_value['free_shipping'])=="1"))? "checked" : "" }}><label for ="free_shipping">Free Shipping</label></li>
      
       <li><input type="checkbox" id="percent_of" class = "filled-in vendor_special_btn" name="percent_of" value="1" {{ ((isset($search_value['percent_of']) && ($search_value['percent_of'])=="1"))? "checked" : "" }}><label for ="percent_of">% Off</label></li>
      
       <li><input type="checkbox" class = "filled-in vendor_special_btn" name="doller_of" id = "doller_of" value="1"  {{ ((isset($search_value['doller_of']) && ($search_value['doller_of'])=="1"))? "checked" : "" }}><label for ="doller_of">$ Off</label></li>
      
       <div class="applydiv text-right">
        <button width="100px" class="btn applybtn" onclick="applyFilter()">Apply</button>
       </div>
      </ul>
    </div>

    @if(!isset($search_value['vendor_id']))
   <div class="su-menu res-su-menu style-4 dropdown mm-view">
      
      <button class="button-ff-year dropdown-toggle {{ $is_lead_time_filter_selected ? 'filter-selected' : '' }}" type="button" data-toggle="dropdown">Lead Time
      
        @if($is_lead_time_filter_selected)
         
          <div class="title-mdl-filter" >{{$search_value['lead_time_min']}}-{{$search_value['lead_time_max']}}days</div>

           &nbsp<span class="close" id="lead_time_clear">&times;</span>
        @else
          <span class="caret-arrow"><img class="fa fa-plus" src="{{url('/assets/front/images/select-arrow.png')}}"></span>
        @endif
      
      </li></button>
      
      
      <ul class="dropdown-menu dropdown-filter-menu ">
        <div class="header-dropdn">
          <div class="closedrop-left">
            <img src="{{url('/assets/front/images/popup-close-btn.png')}}">
          </div> 
          <div class="title-mdl-filter">Lead Time</div>
          <div class="clear-class-dv">
            <button width="100px" class="btn clearbtn mr-2" id = "lead_time_unset">Clear</button>
          </div>
          <div class="clearfix"></div>
        </div>
        
       <li><input type="radio" id="lead_time_one"  name="lead_time" value="2-4" class =" lead_time_btn" {{ ((isset($search_value['lead_time_min']) && ($search_value['lead_time_min'])=="2" && ($search_value['lead_time_max'])=="4"))? "checked" : "" }}><label for ="lead_time_one">2 - 4  Days</label></li>

        <li><input type="radio" id="lead_time_two"  name="lead_time" value="4-5" class =" lead_time_btn" {{ ((isset($search_value['lead_time_min']) && ($search_value['lead_time_min'])=="4" && ($search_value['lead_time_max'])=="5"))? "checked" : "" }}><label for ="lead_time_two">4 - 5  Days</label></li>

        <li><input type="radio" id="lead_time_three"  name="lead_time" value="8-10" class =" lead_time_btn" {{ ((isset($search_value['lead_time_min']) && ($search_value['lead_time_min'])=="8" && ($search_value['lead_time_max'])=="10"))? "checked" : "" }}><label for ="lead_time_three">8 - 10  Days</label></li>

       <li><input type="radio" id="lead_time_four"  name="lead_time" value="13-15" class =" lead_time_btn" {{ ((isset($search_value['lead_time_min']) && ($search_value['lead_time_min'])=="13" && ($search_value['lead_time_max'])=="15"))? "checked" : "" }}><label for ="lead_time_four">13 - 15  Days</label></li>
       
        <li><input type="radio" id="lead_time_five"  name="lead_time" value="15-30" class =" lead_time_btn" {{ ((isset($search_value['lead_time_min']) && ($search_value['lead_time_min'])=="15" && ($search_value['lead_time_max'])=="30"))? "checked" : "" }}><label for ="lead_time_five">15 - 30  Days</label></li>
       
       <div class="applydiv text-right">
         
        <button class="btn applybtn" onclick="applyFilter()">Apply</button>
       </div>
        
      </ul>
    </div>
@endif
    @if(!isset($search_value['vendor_id']))
    @php
    //$brands_arr = get_all_brands();

    @endphp

     <div class="su-menu res-su-menu style-4 dropdown mm-view firstdropdowns">
      
      <button class="button-ff-year dropdown-toggle {{ $is_brand_filter_selected ? 'filter-selected' : '' }}" type="button" data-toggle="dropdown">Stores
      
        @if($is_brand_filter_selected)
          @php
            $show_brands = [];
              if(isset($brands_arr) && sizeof($brands_arr)>0)
              { 
                foreach($brands_arr as $brand)
                {
                  $data = preg_replace('/[^A-Za-z0-9]/', "", $brand);
                  $data_id = get_brand_id($brand);
                 
                 if(isset($search_value['brands']))
                 {
                    $brands = explode(',', $search_value['brands'][0]);
                    $result = in_array($data_id,$brands);
                    if($result==true)
                    {
                      array_push($show_brands,$brand);
                    }
                  }
                  else
                  {
                    $result = false;
                  }
                }
              }
          @endphp

         
           <div class="title-mdl-filter">({{sizeof($show_brands)}})</div>
            &nbsp<span class="close" id="brand_clear">&times;</span>

        @else
          <span class="caret-arrow"><img class="fa fa-plus" src="{{url('/assets/front/images/select-arrow.png')}}"></span>
        @endif
      
      </li></button>
      <ul class="dropdown-menu dropdown-filter-menu scrolladd-brands" id ="brand_list">
        
    <div class="header-dropdn">
         <div class="closedrop-left">
           <img src="{{url('/assets/front/images/popup-close-btn.png')}}">
         </div> 
         <div class="title-mdl-filter">Stores</div>

         <div class="clear-class-dv"><button width="100px" class="btn clearbtn mr-2" id = "brand_unset">Clear</button></div>
         <div class="clearfix"></div>
    </div>
  

        <input title="Search for stores" type="text" class="form-control ui-autocomplete-input" id="brand_search_term" placeholder="Search for stores" autocomplete="off" maxlength="80" name="brand_search_term" value="">

       @if(isset($brands_arr) && sizeof($brands_arr)>0)
            
          @foreach($brands_arr as $brand)
            @php
            $data = preg_replace('/[^A-Za-z0-9]/', "", $brand);
            $data_id = get_brand_id($brand);



            if(isset($search_value['brands']))
            {
               $brands = explode(',', $search_value['brands'][0]);
              $result = in_array($data_id,$brands);

            }
            else
            {
              $result = false;
            }
            @endphp  
              
              <li id = {{$data.'li'}} ><input type="checkbox" class = "filled-in vendor_special_btn brand_name" name="brands" id = "{{$brand}}" value="{{$brand}}" data_id = {{$data_id}} {{ ((isset($search_value['brands']) && ($result)=="true"))? "checked" : "" }} ><label for ="{{$brand}}">{{$brand}}</label></li>

          @endforeach

        @endif


       <div class="applydiv text-right fixed-p">
        <button width="100px" class="btn applybtn" onclick="applyFilter()">Apply</button>
       </div>
        
      </ul>
    </div>
    @endif 
</div>
  <script>
         

  $("#brand_search_term").keyup(function(){

    var brand_search_term = $("#brand_search_term").val();
    
    $('.brand_name').each(function(){
    
    var brand_name = $(this).val();

    brand_name = brand_name.replace(/[_\W]+/g, "").toLowerCase();
    brand_search_term = brand_search_term.replace(/[_\W]+/g, "").toLowerCase();

    if(brand_name.indexOf(brand_search_term)!='-1')
    {
      var b_name = $(this).val().replace(/[_\W]+/g, "");
       $("#"+b_name+"li").show();

    }
    else
    {
      var b_name = $(this).val().replace(/[_\W]+/g, "");
       $("#" +b_name +"li").hide();

    }
    
  });
    

});


  
  </script>
  <style type="text/css">
    #brand_list {
          overflow-y: scroll;
    }
  </style>