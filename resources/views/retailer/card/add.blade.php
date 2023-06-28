@extends('retailer.layout.master')    
@section('main_content')
<script src="{{url('/assets/js/card/jquery.card.js')}}"></script>

<style>
  .jp-card .jp-card-front, .jp-card .jp-card-back{
      background: #383838;
  }
</style>

<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/retailer/dashboard">Dashboard</a></li>
               <li><a href="{{$module_url_path or '' }}">Manage Cards</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- BEGIN Main Content -->
      <div class="row">
         <div class="col-md-12">
            <div class="white-box">
               @include('retailer.layout._operation_status')
               <!-- <div class="box-title">
                  <h3>Add Card Details</h3>
               </div> -->
               <div class="row">
                  <div class="col-sm-12 col-xs-12 manage_card_box">
                     <style>
                        .demo-container {
                        width: 100%;
                        max-width: 350px;
                        /*margin: 50px auto;*/
                        }
                        form {
                        margin:0px;
                        }
                        input {
                        width: 200px;
                        margin: 10px auto;
                        display: block;
                        }
                        @media all and (max-width:450px){
                      .jp-card-container{
                            width: 100% !important;
                      }
                      .jp-card{
                        min-width: 200px !important;
                      }
                      .jp-card .jp-card-front .jp-card-lower .jp-card-number{font-size: 21px;}
                      .form-container form {
                          margin: 0px 0 0 !important;
                      }
                      }
                     </style>
                     <div class="demo-container">
                        <div class="card-wrapper" ></div>
                        <div class="form-container active" >
                           <!-- CSS is included via this JavaScript file -->
                           <form id="frm-card" method="post">
                              {{csrf_field()}}
                              <input type="text" name="number" data-parsley-required="true" data-parsley-required-message="Please enter card number" placeholder="Card Number"  id="number" class="form-control" onkeypress="return isNumber(event)">

                            <!--  <input type="text" name="expiry" data-parsley-required="true" placeholder="MM / YYYY"  id="expiry" class="form-control"/> 

                              <input type="text" name="name" data-parsley-required="true" placeholder="Card holder name" data-parsley-pattern="^[a-zA-Z ]+$"  id="name" class="form-control"/> -->
                              
                              <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="expiry" data-parsley-required="true" data-parsley-required-message="Please enter card expiry date" class="form-control" data-date-format="mm/dd/yyyy" id="expiry" placeholder="MM / YYYY">
                                </div>
                                <div class="col-md-6">
                                  <input type="text" name="cvc" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter CVV number" placeholder="CVV" maxlength="4" class="form-control" onkeypress="return isNumber(event)"/>
                                </div>
                              </div>                       

                              <input type="button" name="btn_add" id="btn_add" class="btn btn-success form-control" value="Add Card" />

                              <a href="{{$module_url_path or '' }}" class="btn btn-default d-block">Back</a>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.2.0/js/bootstrap-datepicker.min.js"></script>
<script>
   $('#frm-card').card({
     container: '.card-wrapper', // *required*
    });
   
   $( document ).ready(function() {
       $('#frm-card').parsley();

       /*$('.datepicker').datepicker({
          format: 'mm / yyyy',
          startDate: '-1d',
          viewMode: "months", 
          minViewMode: "months"
       });*/

        var dp=$("#expiry").datepicker({
              format: 'mm / yyyy',
              startDate: '-1d',
              startView: "months", 
              minViewMode: "months"
          });

          dp.on('changeMonth', function (e) {    
             //do something here
             $("#expiry").datepicker('hide');
          });

   });   
   $('#btn_add').click(function()
   {
     if($('#frm-card').parsley().validate()==false) return;     
     var form_data = $('#frm-card').serialize();   
     var url       = "{{url('/'.config('app.project.retailer_panel_slug'))}}/card/store";    
   
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


               swal('Error',response.description,response.status);
   
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