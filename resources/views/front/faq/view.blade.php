@extends('front.layout.master')
@section('main_content')
<style type="text/css">
.btn-space-top
{
   margin:250px 0 0; 
}
li .active{
	background-color: #f58326;
    color: #fff;
}

.dropdown-menu li{padding: 7px 10px; border-bottom: 1px solid #ececec;}
li.tablinks:hover {
    background-color: #666;
    color: #fff;
}
/*header{display: none;}*/

</style>

<div class="breadcrum-faq">
	<a href="{{url('/')}}">Home</a> <span> > </span>
	<a href="{{url('/'.'faq')}}">FAQs</a> <span> > </span>
	<div class="acrivepage">{{ucfirst($slug)}}</div>

  	<div class="col-md-3 pull-right">
      
	    <div class="search-header-mn search-bxsearch faqsearch">
	      	<div class="user-box">   
	        	<input type="text" class="form-control" id="search_faqs"  placeholder="Search for FAQ ..." name="search_term" autocomplete="off">
		        <div id="questionList"></div>
		    	{{ csrf_field() }}
		    	<div class="clearfix"></div>
	    	</div>
	    </div>
  	</div>
  	<div class="clearfix"></div>

</div>

<div class="clearfix"></div>
<div class="container-fluid">
	@if($faq_count == 0)
	<div class="not-found-data whitebg-no">No FAQ found here,please try searching with another keyword</div>
	@endif
	@if(isset($faq_data) && count($faq_data) > 0)
		<div class="tab-hm" id="question">
		        <div class="tab" >
			        @foreach($faq_data as $key => $faq)
					    
					    <button class="tablinks {{$key}}" id="question_{{$faq['id']}}" onclick="get_question_details(this)" data-enc_id="{{base64_encode($faq['id'])}}" data-slug="{{$slug}}">{{$faq['question']}}</button> 

					@endforeach
		      		<br>
		        </div>

				<div class="tabcontent" >


			        <div class="titlefaq-sub" id="prospective_section">FAQ for prospective {{$slug}}</div>
			        
			        <div class="titleof-faq" id="question_section"></div>
		    		<p id="answer_section"></p>
					
					
				</div>
	    </div>

	    <p class ="titlefaq-sub p-tab-center" id="no_record_found"></p>
	
	@endif



</div>
<div class="contact-faq-txt">
		<div class="container">

		</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script>

$(document).ready(function(){

	var find_class = ($('.tab').find('.0').length);
	if (find_class == 1) {
		$(".not-found-data").hide();
		$('.tablinks').removeClass('active');
		$('.0').addClass('active');
		var enc_id = $(".0").attr('data-enc_id');
		var slug = $(".0").attr('data-slug');
		
		$.ajax({

	        url:module_url_path+'/faq/'+slug+'/'+enc_id,
	        type: 'GET',
	        dataType:'json',
	        data:{faq_id:enc_id,slug:slug},
	        
	        success:function(data)
	        {
	        	
	        	$('#question_section').html(data['question_details']['question']);

	        	$('#answer_section').html(data['question_details']['answer']);
	        }
	   	});
	}
	else{
		// $(".not-found-data").show();
		$(".tabcontent").hide();
		
	}
});

	var slug = "{{$slug}}"
 
    $('#search_faqs').keyup(function(e){ 
    	e.preventDefault();
        var query = $(this).val();
        if(query != '')
        {
         var _token = $('input[name="_token"]').val();
         $.ajax({
          url:module_url_path+'/faq/search_faq',
          method:"POST",
          data:{query:query, _token:_token, slug : slug},
          
          success:function(data){
           
            if(data.length>0)
            {
                $('#question_section').html(data['question_details']['question']);

	        	$('#answer_section').html(data['question_details']['answer']);
            }
            else
            { 

                $("#no_record_found").html('No FAQ found here,please try searching with another keyword');

	        	$("#question").hide();
            }
          }
         });
        }


	       /* if(e.keyCode == 8)
	        {

	           window.location.reload();
	        	
	        }*/
	        
    });




/*var module_url_path = "{{$module_url_path}}";
  function openCity(evt, cityName) {

  	var enc_id = ref.getAttribute('data-enc_id');
	var slug = ref.getAttribute('data-slug');


  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the link that opened the tab
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}*/


var module_url_path = "{{$module_url_path}}";

function get_question_details(ref) {
	$("#questionList").hide();
	$('input[name=search_term').val('');
	var enc_id = ref.getAttribute('data-enc_id');
	var slug = ref.getAttribute('data-slug');

	//alert(ref.getAttribute('id'));
	$('.tablinks').removeClass('active');
	$('#'+ref.getAttribute('id')).addClass('active');
	
	$.ajax({
        url:module_url_path+'/faq/'+slug+'/'+enc_id,
        type: 'GET',
        dataType:'json',
        data:{faq_id:enc_id,slug:slug},
        beforeSend: function() 
        {
            showProcessingOverlay();                
        },
        success:function(data)
        {
        	hideProcessingOverlay();
        	
        	$('#question_section').html(data['question_details']['question']);

        	$('#answer_section').html(data['question_details']['answer']);
        }
   });
}


   $("#search_faqs").on('keyup',function(e){
	
    var key = e.keyCode || e.charCode;
   
    var input = $("#search_faqs").val();
    
    if(key == 8 || key == 46)
    {
    	if(input == '')
    	{ 
           $("#question").show();
           $("#no_record_found").html('');
    	}

    }

   

  });  

</script>


@endsection