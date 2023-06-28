@extends('front.layout.master')
@section('main_content')

<style type="text/css">
  .select2-container {padding:0px !important;}
  .find_rep_page .product-list-pro.find-the-nw .pro-img-list {
    padding: 0;
}
.btn-primary, .btn-primary.disabled{
        width: 180px;
            padding: 8px 15px 6px;
    display: inline-block; border-radius: 3px;
  }
th {
    white-space: nowrap;
}
.multiselect-container>li .checkbox input[type=checkbox]{
  opacity: 1;
}
.input-group-btn{display: none;}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
    color: #564126;
    text-decoration: none;
    outline: 0;
    background-color: #ffe8ca;
    border-bottom: 1px solid #fff5e8;
}
ul.multiselect-container.dropdown-menu {
    max-height: 290px;
    overflow: auto;
}
.frms-slt {
       display: block;
    position: relative;
    margin-bottom: 13px;
    margin-top: -20px;
}
.frms-slt .parsley-errors-list{
    position: relative;
    bottom: -63px;
    z-index: 9;
    width: 100%;
    display: block;
}

.red.space-left-error{
    font-size: 12px;
    margin-left: 8px;
}


.select2-container{
   display: block;
       background-color: #fff;
    border: 1px solid #ccc;
    border-radius:4px;
    box-shadow: none;
    color: #565656;
    height: 34px;
    max-width: 100%;
    padding: 7px 12px; position: relative;
}

.select2-container--default .select2-selection--single {background:none !important;}
.select2-container .select2-choice .select2-arrow{
   background: transparent; background-image: none; border-left: none;
} 
}


</style>


<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.css" rel="stylesheet"/>

<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.js"></script>

<div class="listing-main-div find_rep_page new-space-leftrights">
  <div class="container-fluid">
    <div class="border-titlesfind">
    <div class="row">
       <div class="col-sm-12 col-md-12 col-lg-12 top-breadcrumb">
        <div class="the-representatives">
          @php
     
          $area_name     = isset($area_name)?$area_name:'';
          $category_name = isset($category_name)?$category_name:'';
          $area_type     = isset($area_type)?$area_type:'';
          
          @endphp

          @if((isset($area_name) && $area_name!='')|| (isset($category_name) && $category_name!=''))
             
            {{$area_name.' '.$category_name.' '.$area_type}}
         
          @else
            <div class="the-representatives" class="center"> All Representatives</div>

          @endif
          
             
        </div>
      
        {{Session::forget('guest_back_url')}}
        <div class="pagename-main">
       
        <div class="pagename-right"><a href="{{url('/')}}">Home</a> 
          <span class="slash">/</span>
        @if(Request::segment(2) == null && Request::segment(3) == null)    
        <span class="slash last-beadcrum">Find Your Rep</span>
        @else
          <span class="slash last-beadcrum"><a href="{{url('/find_rep')}}"> Find Your Rep </a></span>
        @endif

        @if(Request::segment(2) != null || Request::segment(3) != null)  
        <span class="slash">/</span> 
         

           <span class="slash last-beadcrum"> {{$area_name.' '.$category_name.' '.$area_type}}</span>
      
        @endif

        </div>

      </div>
     </div>
    </div>
    </div>

   <form id="search_rep" method="POST" action="{{url('/search_representative')}}"> 
    {{csrf_field()}}
    <div class="white-box find_rep_filter_white_box_bg">
        @include('admin.layout._operation_status')
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 box user-box-new">
            <label>Division</label>

            @php
              $area     = Request::segment(2);
              $division = Request::segment(3);
             
              $area = isset($area)?base64_decode($area):'';
              $division = isset($division)?base64_decode($division):'';


              $area_id = isset($area_id)?$area_id:$area;
              $category_division = isset($category_division)?$category_division:$division;

            @endphp

            @if(isset($area_arr) && count($area_arr)>0)

            <select name="area" id="area" class="form-control" placeholder="Select Division" onchange="getCategoryDivision();">
              <option value="">Select Division</option>


          <!--   <select name="area" id="area" class="form-control">
              <option value="">Select Area/Region</option> -->

              @foreach($area_arr as $key=>$area)
               <option value="{{$area['id']}}" @if( isset($area_id) && $area_id == $area['id']) selected @endif>{{$area['area_name']}}</option>
              @endforeach
            </select>
            @endif
            <span class="red" id="error_area_id"></span>
          </div>
         <!--  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 box" >
            <label>Lines</label>
             <select class="form-control" id="category_division" name="category_division">
               <option value="" >Select Lines</option>

                @if(isset($category_division_arr) && count($category_division_arr)>0)
                  @foreach($category_division_arr as $key=>$category_div)
                     <option value="{{$category_div['id']}}" @if($category_div['id'] == $category_division) selected @endif>{{$category_div['cat_division_name']}}</option>
                  @endforeach
                @endif
             </select>
              <span class="red" id="error_category_division"></span>
          </div> -->
           <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 box">
            <label>Zip/Postal Code</label>
          <input type="text" name="zip_code" id="zip_code" class="form-control" placeholder="Zip/Postal Code" value="{{$zip_code or ''}}" data-parsley-type="alphanum" data-parsley-type-message="Please enter valid zip/postal code.">

          <span class="red" id="error_zip_code"></span>

          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 box">
            <label>Representative</label>
            <input type="text" name="rep_name" id="rep_name" class="form-control" placeholder="Representative Name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid representative name." value="{{$representative_name or ''}}">
          </div>

          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 box find_rep_select_vendor_dropdown">
              <label>Vendor</label>
     
              <select class="select2" style="width: 100%" id="search-customer" name="vendor">
                  
                <option class="li" value="">Select Vendor</option>
                @if(isset($vendor_arr) && count($vendor_arr)>0)
                 
                  @foreach($vendor_arr as $key=>$vendor)
          
                  <option class="l1" {{ isset($vendor_id) && $vendor_id == $vendor->id? 'selected':''}} value="{{$vendor->id}}">{{$vendor->company_name}}</option>
              
                   @endforeach

                @endif 
       
            </select>
                
           <span class="red" id="error_retailer_name"></span>
       </div>
       <div class="clearfix"></div>
       <!-- searchbtnfilterdiv -->
        <div class="col-sm-12 search_clar_btn_find_rep">
           
          <button class="btn filter_search-btn" type="button" name="btn_search"  id="btn_search" value="Search">Search</button>

          <button class="btn filter_btn filter_clear-btn" type="button" name="reset" id="reset" value="Reset" title="clear">Clear</button>

        </div>
        
      </div>
     </div>
    </form>

    
    <div class="row">
 
    <div class="clearfix"></div>

      @if(isset($sales_manager_arr) && sizeof($sales_manager_arr)>0)
        
      <div class="col-xs-3 col-sm-3 col-md-4 col-lg-3 rw-filter-left">
      
            <div class="left-profile fnd-img-rp">
              <div class="profile-image rw-profile-image">
                <!-- $profile_default_image = url('https://via.placeholder.com/160x53.png?text='.$sales_manager_arr->name); -->
                @php
                    $sales_img = false;

                    $profile_base_image = isset($sales_manager_arr->profile_image) ? $sales_manager_arr->profile_image : false;

                    $profile_base_image_path = base_path('storage/app/'.$profile_base_image);

                    
                    $profile_default_image = url('/assets/images/no-product-img-found.jpg');
                    $sales_img = image_resize($profile_base_image_path,230,230,$profile_default_image);
                @endphp

               
                
                <a href="{{url('/')}}/sales_manager_details/{{base64_encode($sales_manager_arr->user_id)}}"><img src="{{$sales_img or ''}}"></a>
                
              </div>

               @php  
                  $area_name = isset($sales_manager_arr->area_id)?get_area_name($sales_manager_arr->area_id):'';

              @endphp

               <div class="d-sales">{{$sales_manager_arr->name.', '.$area_name}}</div>

              <a href="{{url('/')}}/sales_manager_details/{{base64_encode($sales_manager_arr->user_id)}}" class="profile-name">{{$sales_manager_arr->user_name or ''}}</a>
            </div>
            <br>
  
           </div>
           @else
            <div class="col-md-4 col-lg-3 " style="display: none;"></div>
          @endif


          @if(isset($sales_manager_arr) && sizeof($sales_manager_arr)>0)

            <div class="col-xs-12 col-sm-9 col-md-8 col-lg-9 rw-filter-right">
              <div class="rep-details-just space-top-none">
          @else
            <div class="col-sm-12 col-md-12 col-lg-12">

          @endif
            
               
              <div class="button-of-sales-mgr">

            @if($area_name !='')
             
              @if(Request::segment(2) && Request::segment(3))
              <a href="{{url('/')}}/get_all_vendors/{{Request::segment(2)}}/{{Request::segment(3)}}">View the {{$area_name.' '.$category_name.' '.$area_type}} Vendors</a>
              @else
                <a href="{{url('/')}}/get_all_vendors/{{Request::segment(2)}}">View the {{$area_name.' '.$category_name.' '.$area_type}} Vendors</a>
              @endif 
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
                <div class="product-section vendor-logo-listing rep-list find_rep_list_new">
                      
                  <div class="row">

                  @if(isset($rep_details_arr['data']) && sizeof($rep_details_arr['data'])>0)
                          
                    @foreach($rep_details_arr['data'] as $rep)
                       
                            <div class="col-xs-3 col-sm-4 col-md-4 col-lg-2 custombox">
                                <a href="{{url('/rep_details')}}/{{base64_encode($rep->user_id)}}">
                                <div class="product-list-pro find-the-nw">
                                    <div class="pro-img-list find-pro-img-list allareas_pro-img-list">
                                        <!-- $profile_default_image = url('https://via.placeholder.com/160x53.png?text='.$rep->name); -->
                                         @php
                                            $rep_img = false;

                                            $profile_base_image = isset($rep->profile_image) ? $rep->profile_image : false;

                                            $profile_base_image_path = base_path('storage/app/'.$profile_base_image);

                                          
                                            $profile_default_image = url('/assets/images/no-product-img-found.jpg');
                                            $rep_img = image_resize($profile_base_image_path,230,230,$profile_default_image);
                                        @endphp  
                                      
                                        <img class="potrait" src="{{$rep_img}}" alt="" />
                                       
                                    </div>
                                    <div class="pro-content-list">
                                     
                                    <div class="pro-sub-title-list"> 

                                      {{$rep->user_name or 'NA'}}

                                  </div> 
                                  @if(isset($sales_manager_arr) && count($sales_manager_arr) ==0)
                                  <div class="pro-sub-title-list subtitlejusts">

                                    @php  
                                      $area_name = get_area_name($rep->area_id);
                                    @endphp

                                    {{$area_name or ''}}
                                  
                                  </div>
                                   @endif
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

            <div class="pagination-bar">
               @if(!empty($representative_pagination))   

               <!-- if we want to send the form request per page  -->
                {{$representative_pagination->appends(request()->input())->links()}}


               @endif 

            </div>

            </div>
        </div>
        </div>
    </div>
</div>
   </div>
    </div>
</div>
<script type="text/javascript">
  
 //var categoryDivision = '{{$category_division or ''}}';

/*function getCategoryDivision()
{   
    var area = $('#area').val();

    $.ajax({
              url: '{{url('/')}}/getCategoryDivision',
              data:{area_id:area},
              method:'GET',
              
              beforeSend : function()
              {
                showProcessingOverlay();
              },
              success:function(response)
              { 
                  hideProcessingOverlay();
                
                  if(response.length !=0)
                  {  
                      $('#category_division').empty().append('<option value="">Select Lines</option>');

                      if(response.length>0)
                      {
                        jQuery.each(response, function(i,val)
                        {
                          if(val.id == categoryDivision)
                          {
                            $('#category_division').append('<option value="'+val.id+'" selected>'+val.cat_division_name+'</option>');
                          }
                          else
                          {
                           
                            $('#category_division').append('<option value="'+val.id+'">'+val.cat_division_name+'</option>');
                          }
                          
                        });
                      }
                  }
                  else
                  {
                     $('#category_division').empty().append('<option value="" >Select Lines</option>');
                  }
                         
              }

        });
}*/

$('#btn_search').click(function()
{
    if($('#search_rep').parsley().validate() == false) return;

    var category_division  = $('#category_division').val();
    var zip_code           = $('#zip_code').val();
    var vendor             = $('#search-customer').val();
    var csrf_token         = "{{ csrf_token() }}";
    var area               = $('#area').val();
    var rep_name           = $('#rep_name').val();

    if((area =='' || area == undefined) && (category_division=='' || category_division==undefined) && (zip_code =='' || zip_code== undefined) && (vendor =='' || vendor==undefined) && (rep_name ==''||rep_name==undefined ))
    { 
        swal("Warning","Please select atleast one filter.","warning"); 
        return false;
    }
    else
    {
        showProcessingOverlay();
        $('#search_rep').submit();
    }

});

  $('.select2').select2({
     templateResult: function (data) {    
       // We only really care if there is an element to pull classes from
       if (!data.element) {
         return data.text;
       }

       var $element = $(data.element);

       var $wrapper = $('<span></span>');
       $wrapper.addClass($element[0].className);

       $wrapper.text(data.text);

       return $wrapper;
     }
  });


  $('#reset').click(function(){

    var category_division = '{{$category_division or ''}}';
    var retailer_id       = '{{$retailer_id or ''}}';


    //$("#category_division").val('Select Lines');
    $("#area").val('');
    $("#area").val("Select Division");
    $("#zip_code").val('');
    $(".select2").select2('');
    $("#search-customer").val("Select Retailer");
    $('#rep_name').val('');

    category_division = 0;
    retailer_id = 0;

    //$('#search_rep').submit();

    location.href = SITE_URL+'/find_rep';
      
  });


</script>
@stop