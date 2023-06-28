@extends('maker.layout.master')                
@section('main_content')

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #fb3b62;
    background-color: transparent;
    color: #fb3b62;
}
.hidden_field_clone{
  width: 100%;
    margin-top: 15px;
}
</style>
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path or ''}}">Manage {{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12 manage_promotion_page">
      <div class="white-box">
           @include('admin.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>

               <div  class="tab-content">
                 
                <input type="hidden" name="enc_id" id="enc_id" value="{{isset($promotions_arr['id'])? base64_encode($promotions_arr['id']):0}}">

                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Title<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="title" placeholder="Promotion Title" id="title" data-parsley-required ="true" data-parsley-required-message ="Please enter title" class="form-control" value="{{$promotions_arr['title'] or ''}}" />
                      <div id ="err_container"></div>
                   </div>
                  </div>


                       <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">From Date<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10 promotion_from_to_date_row">
                      <div class="row">
                     <div class="col-sm-12 col-md-12 col-lg-5">
                      <input type="text" class="form-control datepicker input-float" name="from_date" id="from_date" placeholder="From Date"
                      data-parsley-required="true"  data-parsley-required-message="Please enter start date of promotion" value="{{$promotions_arr['from_date'] or ''}}" readonly="true"/>

                      <div id ="err_container"></div>
                     </div>
                     <div class="col-sm-12 col-md-12 col-lg-7 todate_new">
                      <div class="form-group row">
                        <label class="col-sm-12 col-md-12 col-lg-3 col-form-label" for="brand_image">To Date<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-9 todate-input pr-0">
                     <input type="text" class="form-control datepicker input-float" name="to_date" id="to_date" placeholder="To Date"
                      data-parsley-required="true"  data-parsley-required-message="Please enter end date of promotion" value="{{$promotions_arr['to_date'] or ''}}" readonly="true"/>
                      <div id ="err_container"></div>
                   </div>
                      </div>
                  </div>
                </div>
                   </div>
                  
                  
                     
                </div>

                {{--   <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Promo Code</label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="promo_code" placeholder="Promotion Code" id="promo_code"  class="form-control" value="{{$promotions_arr['promo_code'] or ''}}" />
                      <div id ="error_container"></div>
                   </div>
                  </div> --}}

                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Promo Code<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                  
                     <select name="promo_code" id="promo_code" data-parsley-required="true" data-parsley-required-message="Please enter promo code" class="form-control">
                       <option value="">Select Promo Code</option>
                       @if(isset($promo_code_arr) && count($promo_code_arr)>0)
                          @foreach($promo_code_arr as $key=>$promo_code)

                            <option value="{{$promo_code['id']}}" @if($promotions_arr['promo_code'] == $promo_code['id']) selected @endif>{{$promo_code['promo_code_name']}}</option>
                          @endforeach

                       @endif
                     </select>
                      <div id ="err_container"></div>
                   </div>
                  </div>


                   <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="description">Description</label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <textarea class="form-control" id="description" name="description" placeholder="Description">{{$promotions_arr['description'] or ''}}</textarea>
                      <div id ="err_container"></div>
                    </div>
                  </div>


              
              @if(isset($promotions_arr['get_promotions_offer_details']) && count($promotions_arr['get_promotions_offer_details'])>0)

                @foreach($promotions_arr['get_promotions_offer_details'] as $key=>$promotions_offer)
           
                <input type="hidden" name="enc_promotion_offer_id[]" id="enc_promotion_offer_id" value="{{$promotions_offer['id']}}">


                  <div class="form-group row clone-main-add">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image">Promotions Type<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10 promotions_type">

                    <div class="left">
                      <input type="hidden" name="promotion_count" id="promotion_count" value="1">  

                     <select class="form-control class_promotion_type" id="promotion_type" name="promotion_type[]" data-parsley-required="true" data-parsley-required-message="Please select promotion type" onchange="showFields($(this));" > 
                       <option value="">Select Type</option>

                       @if(isset($promotions_type_arr) && count($promotions_type_arr) >0)
                         @foreach($promotions_type_arr as $key1=>$promotions)

                           <option value="{{$promotions['id']}}" @if($promotions_offer['get_prmotion_type']['id'] == $promotions['id']) selected @endif>{{$promotions['promotion_type_name']}}</option>
                        
                         @endforeach

                       @endif
                     </select>
                    </div>

                    <div class="right">
                      <span class="action-placeholder"> 

                    @if($key == 0)
                     
                      <a href="javascript:void(0);" class="add-plus-btn" id="addMore"><i class="fa fa-plus"></i></a>
                       
                    @else
                         <a href="javascript:void(0);" class="add-minus-btn" data-id="{{$promotions_offer['id']}}" onclick="javascript: removeRows(this);"><i class="fa fa-minus"></i></a>
                     @endif

                   </span>
                    </div>

                   </div>

                  <!-- hidden -->
                  <div class="hidden_field_clone">

                     @if(isset($promotions_offer['minimum_ammount']) && $promotions_offer['minimum_ammount']!='')

                      <div class="form-group row class_min_amount" id="minimum_amt">
                        <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="min_ammount">Minimum Amount(<i class="fa fa-dollar"></i>)<i class="red">*</i></label>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                         <input type="text" name="min_ammount[]" id="min_ammount" data-parsley-type="number" data-parsley-required="true" data-parsley-required-message="Please enter minimum amount"  class="form-control" value="{{$promotions_offer['minimum_ammount'] or ''}}" data-parsley-type-message="Please enter valid minimum amount" data-parsley-maxlength="10" data-parsley-maxlength-message="minimum amount should be maximum 10 digits long"/>
                       </div>
                      </div>

                      @else

                        <div class="form-group row class_min_amount" style="display:none;" id="minimum_amt">
                        <label class="col-2 col-form-label" for="min_ammount">Minimum Ammount(<i class="fa fa-dollar"></i>)<i class="red">*</i></label>
                        <div class="col-10">
                         <input type="text" name="min_ammount[]" id="min_ammount" data-parsley-type="number" class="form-control"  data-parsley-required="true" data-parsley-required-message="Please enter minimum amount"
                         data-parsley-type-message="Please enter valid minimum amount" data-parsley-maxlength="10" data-parsley-maxlength-message="minimum amount should be maximum 10 digits long"/>
                       </div>
                      </div>

                      @endif


                     @if(isset($promotions_offer['discount']) && $promotions_offer['discount']!='')

                      <div class="form-group row clone-form-minus class_discount" id="off_discount">
                        <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="discount">% Off<i class="red">*</i></label>
                        <div class="col-sm-12 col-md-12 col-lg-10">
                         <input type="text" name="discount[]" id="discount" data-parsley-required="true" data-parsley-required-message="Please enter % off" class="form-control" data-parsley-type="digits" data-parsley-type-message="Please enter valid % off" value="{{$promotions_offer['discount'] or ''}}" data-parsley-maxlength="10" data-parsley-maxlength-message="% off should be maximum 10 digits long"/>
                         
                       </div>
                      </div>

                      @else

                        <div class="form-group row clone-form-minus class_discount" style="display:none;" id="off_discount">
                        <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="discount">% Off<i class="red">*</i></label>
                        <div class="col-sm-12 col-md-12 col-lg-10">
                         <input type="text" name="discount[]" id="discount" data-parsley-type="digits" data-parsley-type-message="Please enter valid % off" class="form-control" data-parsley-maxlength="10" data-parsley-maxlength-message="% off should be maximum 10 digits long"/>
                         
                        </div>
                        </div>

                      @endif

                    </div>
                  <!-- end -->

                  </div>  

                    

                  @endforeach

                @endif  

                @php

                  $status = '';

                  if(isset($promotions_arr['is_active']) && $promotions_arr['is_active']!='')
                  {
                    $status = $promotions_arr['is_active'];
                  }
                  else 
                  {
                    $status = '';
                  }     
                 
             
                @endphp

                <div class="form-group row clone-form-minus">
                    <label class="col-md-2 col-form-label">Status</label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " @if($status == 1) checked @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggleStatus();" />
                      </div>    
                </div> 

               </div>
               <br>

               <div class="form-group">
                  <div class="col-md-12 text-left cancel_back_flex_btn">
                  <a class="btn btn-inverse waves-effect waves-light backbtn" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true" onclick="saveTinyMceContent();"> Save</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">

  const module_url_path = "{{ $module_url_path or ''}}";
  var count = 0;

  function saveTinyMceContent()
  {
    tinyMCE.triggerSave();
  }


  $(document).ready(function(){


  /*  $( function(){
      $(".datepicker").datepicker();
      
      $('#from_date').datepicker('setEndDate', '+0d');
      $('#to_date').datepicker('setEndDate', '+0d');

      $("#from_date").datepicker({
        todayBtn:  1,
        autoclose: true,

      }).on('changeDate', function (selected) {
        var minDate = new Date(selected.date.valueOf());
        $('#to_date').datepicker('setStartDate', minDate);
      });
    
      $("#to_date").datepicker()
        .on('changeDate', function (selected) {
            var minDate = new Date(selected.date.valueOf());
            $('#from_date').datepicker('setEndDate', minDate);
        });
  
      });*/


      $(".datepicker").datepicker({
       // dateFormat:'mm-dd-yy',
       dateFormat:'yy-mm-dd',

       minDate: 0 });


      $("#to_date").change(function(){

        var from_date = $("#from_date").val();
        var to_date   = $("#to_date").val();

         if ((Date.parse(to_date) <= Date.parse(from_date))) {
         swal("Error", "To date should be greater than from date", "error");
         $("#to_date").val('');
    }

    });

    $('#validation-form').parsley();


       $('#btn_add').click(function(){
      
        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        
       
          $.ajax({
            url: module_url_path+'/store',
            type:"POST",
            data: formdata,
            contentType:false,
            processData:false,
            dataType:'json',
            beforeSend: function() 
            {
              showProcessingOverlay();                 
            },
            success:function(data)
            {
               hideProcessingOverlay(); 
               if('success' == data.status)
               {
                   $('#validation-form')[0].reset();

                   swal({
                           title: "Success",
                           text: data.description,
                           type: data.status,
                           confirmButtonText: "OK",
                           closeOnConfirm: false
                        },
                       function(isConfirm,tmp)
                       {
                         if(isConfirm==true)
                         {
                            window.location = '{{$module_url_path or ''}}';
                         }
                       });
                }
                else
                {
                    var status = data.status;
                        status = status.charAt(0).toUpperCase() + status.slice(1);
                      
                    swal(status,data.description,data.status);
                }  
            }
            
          });   
      
      });

   });

    
  function toggleStatus()
  {
      var status = $('#status').val();
      if(status=='1')
      {
        $('#status').val('1');
      }
      else
      {
        $('#status').val('0');
      }
  }

  $('#promotion_type').change(function() {

  if($('.clone-main-add').length > 1)
  {
    var parentDiv = $(".clone-main-add:last");

    /*remove child clone*/
    $(parentDiv[0]).remove();
  }
});

  //add more rows to the table
  $('#addMore').click(function()
  {   
    if($('#validation-form').parsley().validate()==false) return; 

    let selectedPromotionType = $('#promotion_type').val();

    if($('.clone-main-add').length > 1)
    {
      return
    }

    /*get all main div html design*/
    var parentDiv = $(".clone-main-add:last");

    /*clone into one variable*/
    var clonedParentDiv = $(parentDiv[0]).clone();

    $(clonedParentDiv).find("option[value='"+selectedPromotionType+"']").remove();

    /* Replace Add with minux */  
    $(clonedParentDiv).find("span.action-placeholder").empty();
    $(clonedParentDiv).find("span.action-placeholder").html('<a href="javascript:void(0);" class="add-minus-btn" onclick="javascript: removeRows(this);"><i class="fa fa-minus"></i></a>');
    
    $(clonedParentDiv).insertAfter(parentDiv[0]);

    

    /* Reset All Inputs - means when clone next html then previous value will be wanish new html will build*/  
    var tmpRef = $(".clone-main-add:last").find(".class_promotion_type");
    $(tmpRef[0]).val("");
    $(tmpRef[0]).trigger("change");

    $.each($(".clone-main-add:last").find("input"),function(i,elem){
      $(elem).val("");
    });

           
  });

  function removeRows(ref)
  {   
    
      $(ref).parent().parent().parent().parent().remove();
     
      var id = $(ref).data('id');
          id = btoa(id);

      $.ajax({
          url: module_url_path+'/delete_row',
          type:"GET",
          data: {id:id},
          // contentType:false,
          // processData:false,
          dataType:'json',
          success:function(data)
          {
              if('success' == data.status)
              {
                
              }
              else
              {
                
              }  
          }
      
      });  
    
  };



  function showFields(ref)
  { 
     // var parentDivFirst = $(".clone-main-add:first").find('select').each(function() {
     //                var $elem1 = $(this);
     //                var selectedDivFirst = $elem1.val();
     //                // alert(value);

     //                var parentDivLast = $(".clone-main-add:last").find('select').each(function() {
     //                      var $elem2 = $(this);
     //                      var selectedDivLast = $elem2.val();
                    
     //                if(selectedDivFirst == selectedDivLast)
     //                {
     //                   swal("Error", "Already Selected", "error");
     //                    $($elem1).val($elem.val());
     //                    $($elem2).val($elem2.val());
     //                }    

     //            });
     //          });

    
    // console.log(parentDivFirst);
    // return; 


      var selected_option = $(ref).children("option:selected").val();
      var parentRef = $(ref).closest(".clone-main-add");


      if(selected_option == 1)
      {
        $(parentRef).find(".class_min_amount").show();
        $(parentRef).find(".class_discount").hide();

         $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','true');

         $(parentRef).find(".class_discount").find("input[name='discount[]']").attr('data-parsley-required','false');
         
      }
      else if(selected_option == 2)
      { 
    
        $(parentRef).find(".class_min_amount").show();
        $(parentRef).find(".class_discount").show();

        $(parentRef).find(".class_discount").find("input[name='discount[]']").attr('data-parsley-required','true');
        $(parentRef).find(".class_discount").find("input[name='discount[]']").attr('data-parsley-required-message',"Please enter % off");
        $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','true');

       
      }
      else
      {
        $(parentRef).find(".class_min_amount").hide();
        $(parentRef).find(".class_discount").hide();   

        $(parentRef).find(".class_discount").find("input[name='discount[]']").attr('data-parsley-required','false');
        $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','false');      
      }

  
  } 

  
</script>
@stop