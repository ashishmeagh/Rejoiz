@extends('admin.layout.master')    
@section('main_content')
<script src="{{url('/assets/js/card/jquery.card.js')}}"></script>
<style>
  .jp-card {min-width:auto !important;}
.jp-card .jp-card-front, .jp-card .jp-card-back{
        background: #2b2b2b;
}
.jp-card .jp-card-front .jp-card-shiny, .jp-card .jp-card-back .jp-card-shiny {top:-30px;}
.jp-card .jp-card-front .jp-card-lower .jp-card-number {font-size:22px;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{str_singular($module_title)}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
               <li><a href="{{$module_url_path or '' }}">Cards</a></li>
               <li class="active">{{str_singular($module_title)}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- BEGIN Main Content -->
      <div class="row">
         <div class="col-md-12">
            <div class="white-box">
               @include('retailer.layout._operation_status')
              {{--  <div class="box-title">
                  <h3>Add Card Details</h3>
               </div> --}}
               <div class="row">
                  <div class="col-sm-12 col-xs-12 manage_card_box">
                     <style>
                        .demo-container {
                        width: 100%;
                        max-width: 350px;
                        /*margin: 50px auto;*/
                        }
                        input {
                        width: 200px;
                        margin: 10px auto;
                        display: block;
                        }
                     </style>
                     <div class="demo-container">
                        <div class="card-wrapper" ></div>
                        <div class="form-container active" >
                           <!-- CSS is included via this JavaScript file -->
                           <form id="frm-card" method="post">
                              {{csrf_field()}}
                              <input type="text" name="number" data-parsley-required="true" placeholder="Card Number"  id="number" class="form-control" onkeypress="return isNumber(event)"  data-parsley-required-message="Please enter card number.">

                            <!--  <input type="text" name="expiry" data-parsley-required="true" placeholder="MM / YYYY"  id="expiry" class="form-control"/> 

                              <input type="text" name="name" data-parsley-required="true" placeholder="Card holder name" data-parsley-pattern="^[a-zA-Z ]+$"  id="name" class="form-control"/> -->
                              
                              <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="expiry" data-parsley-required="true" data-parsley-required-message="Please enter card expiry date." class="datepicker form-control" data-date-format="mm/dd/yyyy" id="expiry" placeholder="MM / YYYY" ">
                                </div>
                                <div class="col-md-6">
                                  <input type="text" name="cvc" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter CVV number." placeholder="CVV" maxlength="4" class="form-control" onkeypress="return isNumber(event)"/>
                                </div>
                              </div>                       

                              <input type="button" name="btn_add" id="btn_add" class="btn btn-success form-control m-b-0" value="Add Card" />
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   $('#frm-card').card({
     container: '.card-wrapper', // *required*
    });
   
   $( document ).ready(function() {
       $('#frm-card').parsley();

       $('.datepicker').datepicker({
          format: 'mm / yyyy',
          startDate: '-1d',
          viewMode: "months", 
          minViewMode: "months"
       });

   });   
   $('#btn_add').click(function()
   {
     if($('#frm-card').parsley().validate()==false) return;     
     var form_data = $('#frm-card').serialize();   
     var url       = "{{url('/'.config('app.project.admin_panel_slug'))}}/card/store";    
   
     if($('#frm-card').parsley().isValid() == true )
     {
         
        $.ajax({
           url:url,
           data:form_data,
           method:'POST',
           dataType:'JSON',
           beforeSend : function()
           {
             showProcessingOverlay();
             
           },
           success:function(response)
           {
              hideProcessingOverlay();
             
   
             if(typeof response =='object')
             {
               if(response.status && response.status=="success")
               {
                 $("#frm-card")[0].reset();
   
                swal({
                     title: "Success",
                     text: response.description,
                     type: "success",
                     showCancelButton: false,
                     confirmButtonClass: "btn-success",
                     confirmButtonText: "OK",
                     closeOnConfirm: false,
                     closeOnCancel: false
                   },
                   function(isConfirm) {
                     if (isConfirm) {
                      
                       location.href=response.link;
                     } 
                   });
                 
               }
               else
               {
                 swal('Warning',response.description,'warning');
   
               }
               
             }
           },
           error: function(XMLHttpRequest, textStatus, errorThrown) 
           {
              
           }
        });
     }
   
   
   });
   
</script>
@endsection