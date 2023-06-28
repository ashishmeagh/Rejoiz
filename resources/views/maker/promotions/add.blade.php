@extends('maker.layout.master')                
@section('main_content')


<style>
  .add-plus-btn{
        right: 14px;
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
         <li><a href="{{$module_url_path or ''}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12">
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
                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Title<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="title" placeholder="Promotion Title" id="title" data-parsley-required ="true" data-parsley-required-message ="Please enter title" class="form-control" />
                      <div id ="err_container"></div>
                   </div>
                  </div>


                       <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">From Date<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10 promotion_from_to_date_row">
                     <div class="row">
                      <div class="col-sm-12 col-md-12 col-lg-5">
                        <input type="text" class="form-control datepicker" name="from_date" id="from_date" placeholder="From Date"
                      data-parsley-required="true"  data-parsley-required-message="Please enter start date of promotion"   readonly="true" />
                      <div id ="err_container"></div>
                      </div>
                      <div class="col-sm-12 col-md-12 col-lg-7 todate_new">
                     <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-3 col-form-label" for="brand_image">To Date<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-9 todate-input pr-0">
                     <input type="text" class="form-control datepicker" name="to_date" id="to_date" placeholder="To Date"
                      data-parsley-required="true"  data-parsley-required-message="Please enter end date of promotion"   readonly="true"/>
                      <div id ="err_container"></div>
                   </div>
                  </div>
                </div>
                     </div>
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Promo Code<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                  
                     <select name="promo_code" id="promo_code" data-parsley-required-message="Please enter promo code" data-parsley-required="true" class="form-control">
                       <option value="">Select Promo Code</option>
                       @if(isset($promo_code_arr) && count($promo_code_arr)>0)
                          @foreach($promo_code_arr as $key=>$promo_code)

                            <option value="{{$promo_code['id']}}">{{$promo_code['promo_code_name']}}</option>
                          @endforeach

                       @endif
                     </select>
                      <div id ="err_container"></div>
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="description">Description</label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                    <textarea class="form-control" id="description" name="description" placeholder="Description"></textarea>
                      <div id ="err_container"></div>
                    </div>
                  </div>



                <div class="clone-main-add">
                  <div class="form-group row clone-form">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image">Promotions Type<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10 rw-promotion-type-vendor-add">

                    <input type="hidden" name="promotion_count" id="promotion_count" value="1">  

                     <select class="form-control class_promotion_type" id="promotion_type" name="promotion_type[]" data-parsley-required="true" data-parsley-required-message="Please select promotion type" onchange="showFields($(this));"> 
                       <option value="">Select Type</option>

                       @if(isset($promotions_type_arr) && count($promotions_type_arr) >0)
                         @foreach($promotions_type_arr as $key=>$promotions)

                           <option value="{{$promotions['id']}}">{{$promotions['promotion_type_name']}}</option>
                        
                         @endforeach

                       @endif
                     </select>
                   </div>
                   <span class="action-placeholder"> 
                    <a href="javascript:void(0);" class="add-plus-btn" id="addMore"><i class="fa fa-plus"></i></a>
                   </span>
                  </div>  

                    <div class="hidden_field_clone">

                      <div class="form-group row class_min_amount" style="display:none;" id="minimum_amt">
                        <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="min_ammount">Minimum Amount(<i class="fa fa-dollar"></i>)<i class="red">*</i></label>
                        <div class="col-sm-12 col-md-12 col-lg-10">
                         <input type="text" name="min_ammount[]" id="min_ammount" class="form-control" data-parsley-type="number" data-parsley-required="true" data-parsley-required-message="Please enter minimum amount" data-parsley-type-message="Please enter valid minimum amount" data-parsley-maxlength="10" data-parsley-maxlength-message="minimum amount should be maximum 10 digits long"/>
                       </div>
                    
                      </div>

                      <div class="form-group row class_discount" style="display:none;" id="off_discount">
                        <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="discount">% Off<i class="red">*</i></label>
                        <div class="col-sm-12 col-md-12 col-lg-10">
                         <input type="text" name="discount[]" id="discount"  class="form-control" data-parsley-type="digits" data-parsley-required="true" data-parsley-required-message="Please enter % off" data-parsley-type-message="Please enter valid % off" data-parsley-maxlength="10" data-parsley-maxlength-message="% off should be maximum 10 digits long"/>
                         
                       </div>
                    
                      </div>

                    </div>

                 </div> 

                  <div class="form-group row clone-form-minus">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label">Status</label>
                      <div class="col-sm-12 col-md-12 col-lg-10 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggleStatus();" />
                      </div>    
                  </div> 

               </div>
               <br>

               <div class="form-group row">
                  <div class="col-lg-12 common_back_save_btn">
                    <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
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

      $(".datepicker").datepicker({
       // dateFormat:'mm-dd-yy', 
       dateFormat:'yy-mm-dd',
       minDate: 0 });


      $("#to_date").change(function(){

        var from_date = $("#from_date").val();
        var to_date   = $("#to_date").val();

    if ((Date.parse(to_date) < Date.parse(from_date))) {
         swal("Error", "To date should be greater than from date", "error");
     $("#to_date").val('');
    }

    });
      
   

       $('#btn_add').click(function()
       {
      
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

  function removeRows(ref){   
     $(ref).parent().parent().parent().remove();
  };



  function showFields(ref)
  {  
      var selected_option = $(ref).children("option:selected").val();
      var parentRef = $(ref).closest(".clone-main-add");
      
      $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','true');

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

          $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','true');

      }
      else
      {

        $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','true');

        $(parentRef).find(".class_min_amount").hide();
        $(parentRef).find(".class_discount").hide();   

        $(parentRef).find(".class_discount").find("input[name='discount[]']").attr('data-parsley-required','false');

        $(parentRef).find(".class_min_amount").find("input[name='min_ammount[]']").attr('data-parsley-required','false');     
      }
    

  } 

  
</script>
@stop