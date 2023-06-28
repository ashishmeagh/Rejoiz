@extends('front.layout.master')
@section('main_content')

<div class="mainfront-sales-mngr-dtls sl-mngr-dtls">
    <div class="container-fluid">
 <div class="border-titlesfind mngr-bottom-spc">
 	 <div class="row">
 	 	<div class="col-sm-12 col-md-6 col-lg-6">
 	 		 <div class="the-representatives">
		         <div class="the-representatives" class="center">Sales Manager Details</div>
		     </div>
 	 	</div>
 	 	<div class="col-sm-12 col-md-6 col-lg-6">
 	 		<div class="pagename-main">
		       <div class="pagename-right"><a href="{{url('/')}}">Home</a>
		        <span class="slash">/</span>

		        <span>Sales Manager: {{$sales_manager_data['first_name'].' '.$sales_manager_data['last_name']}}</span>
		        </div>
		          <div class="clearfix"></div>
		    </div>	
 	 	</div>
 	 </div>
 </div>
   

    
    </div>


	<div class="mainfrontsales">
		<div class="main-frnt-mgr">
			<div class="mainfrontsales-left">
	        
            @php
              if(isset($sales_manager_data['profile_image']) && $sales_manager_data['profile_image']!='' && file_exists(base_path().'/storage/app/'.$sales_manager_data['profile_image']))
              {
                
                $sales_img = url('/storage/app/'.$sales_manager_data['profile_image']);
                
              }
              else
              {
                $sales_img = url('/assets/images/default.png');
              }

              if (isset($sales_manager_data['contact_no'])) {

                $contact_no = get_contact_no($sales_manager_data['contact_no']);

                if (isset($contact_no) && $contact_no == "") {

                  $contact_no = $sales_manager_data['contact_no'];
                }
                else{

                  $contact_no = get_contact_no($sales_manager_data['contact_no']);
                
                }
                 
              }
            @endphp

			<img src="{{$sales_img or ''}}" alt="">
				
			</div>

			<div class="main-rights-mgrs">
				<div class="title-ofsale-mgrs">{{$sales_manager_data['first_name'].' '.$sales_manager_data['last_name']}}</div>
				
				@if(isset($area_details) && count($area_details) > 0)
					@php

						foreach($area_details as $area){

							if (isset($area['area_name']) && isset($area['category_name'])) {

								$all_area[] = $area['area_name'].' '.$area['category_name'];
							}
							else{
								$all_area[] = $area['area_name'];
							}

							
						}
						
						$area = implode(', ', $all_area);
					@endphp

				@endif
			
				<div class="sls-mgr">Sales Manager : {{$area or ''}}</div>
 
                @if(isset($sales_manager_data['contact_no']) && isset($sales_manager_data['contact_no'])!='')
				<div class="moline-sales"><span><i class="fa fa-phone-square"></i></span>{{$contact_no or ''}}</div>
				@endif
                 
                @if(isset($sales_manager_data['email']) && $sales_manager_data['email']!='') 
				<div class="email-adress"><span><i class="fa fa-envelope"></i></span>{{$sales_manager_data['email'] or ''}}</div>

				@endif
				
				@if(isset($description) && $description!='')
				<input type="hidden" name="more_less_text_value" id="more_less_text_value" value="700">
				<div class="mainfrontsales-right">
					<div class="title-descipt">
					

						<div class="truncate">{{$description or ''}}</div>
					</div>
					{{--  <a class="moreless-button" href="javascript:void(0);">Read more</a> --}}
				</div>

				@endif
				
			</div>
			<div class="clearfix"></div>
		   </div>
		   <div class="button-of-sales-mgr front-sale-manager-dls">
				@if(isset($area_details) && count($area_details) > 0)
				
					@foreach($area_details as $area)
						@if (isset($area['area_name']) && isset($area['category_name']))

							<a class="view-md-atc" href="{{url('/')}}/find_rep/{{base64_encode($area['area_id'])}}/{{base64_encode($area['category_id'])}}" class="view-md-atc"> View the {{$area['area_name'] or ''}} {{$area['category_name'] or ''}} Team </a>


						@else

							<a class="view-md-atc" href="{{url('/')}}/find_rep/{{base64_encode($area['area_id'])}}" class="view-md-atc"> View the {{$area['area_name'] or ''}} Team </a>

						@endif
						
						
							{{-- @else
								<a class="view-md-atc" href="{{url('/')}}/find_rep/{{base64_encode($area['area_id'])}}" class="view-md-atc"> View the {{$area['area_name'] or ''}} Team </a>
						@endif --}}

					@endforeach

				@endif	
				
				{{-- <a href="#" class="view-md-atc">View the Mid-Atlantic Lifestyle Team </a> --}}
				<div class="clearfix"></div>
			</div>
		
		<div class="clearfix"></div>
	
	</div>
</div>

<script>
$('.moreless-button').click(function() {
  $('.moretext').toggleClass('mores');
  if ($('.moreless-button').text() == "Read more") {
    $(this).text("Read less")
  } else {
    $(this).text("Read more")
  }
});
</script>


@stop