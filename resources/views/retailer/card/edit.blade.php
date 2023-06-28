@extends('retailer.layout.master')    
@section('main_content')
<script src="{{url('/assets/js/card/jquery.card.js')}}"></script>

<style>
    .jp-card .jp-card-front, .jp-card .jp-card-back{
      background: #383838;
  }
  .retailer_panel_manage_card_add_page .white-box .demo-container {margin-bottom:0px;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid retailer_panel_manage_card_add_page">
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
               <!-- div class="box-title">
                  <h3>Edit Card Details</h3>
               </div> -->
               <div class="row">
                  <div class="col-sm-12 col-xs-12 manage_card_box">
                     <style>
                        .demo-container {
                        width: 100%;
                        max-width: 350px;
                        /*margin: 50px auto*/;
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
                     @php 
                     $card_no = str_pad($card['last4'], 16, "X", STR_PAD_LEFT);
                     $card_expiry_month = isset($card['exp_month'])?$card['exp_month']:'';
                     $card_expiry_year  = isset($card['exp_year'])?$card['exp_year']:'';
                     $card_expiry = $card_expiry_month.' / '.$card_expiry_year;
                     $customer_id = isset($card['customer'])?$card['customer']:'';
                     $card_id = isset($card['id'])?$card['id']:'';
                     @endphp
                     <div class="demo-container">
                        <div class="card-wrapper" ></div>
                        <div class="form-container active" >
                           <form id="frm-card" method="post">
                              {{csrf_field()}}
                              <input type="text" name="number" data-parsley-required="true" data-parsley-required-message="Please enter card number" placeholder="Card Number" id="number" value="{{$card_no or ''}}" class="form-control" onkeypress="return isNumber(event)" readonly="">
                             
                              
                              <!--
                              <input type="text" name="name" data-parsley-required="true" value="{{$card_data->name or ''}}" placeholder="Card holder name"  id="name" class="form-control" data-parsley-pattern="^[a-zA-Z ]+$"/>
                            -->


                              <input type="text" name="expiry" data-parsley-required="true" data-parsley-required-message="Please enter card expiry date" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="expiry" placeholder="MM / YYYY" value="{{$card_expiry or ''}}">


                              <input type="hidden" name="card_id" value="{{$card_id or ''}}" />
                              <input type="hidden" name="customer_id" value="{{$customer_id or ''}}" />

                              <input type="button" name="btn_update" id="btn_update" class="btn btn-success form-control" value="Update Card" />

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
    
  $(document).ready(function()
  {
      var card_number = '{{isset($card_no)?$card_no:""}}';
      var exp_date    = '{{isset($card_expiry)?$card_expiry:""}}';  

      $('.jp-card-number').text(card_number);
      $('.jp-card-expiry').text(exp_date);
  });

  $('#frm-card').card({
     container: '.card-wrapper', // *required*
  });
   
   $( document ).ready(function() {
       $('#frm-card').parsley();

      /* $('.datepicker').datepicker({
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
   $('#btn_update').click(function()
   {
     
     if($('#frm-card').parsley().validate()==false) return;     
     var form_data = $('#frm-card').serialize();   
     var url       = "{{url('/'.config('app.project.retailer_panel_slug'))}}/card/update";    
        
     if($('#frm-card').parsley().isValid() == true )
     {
        $.ajax({
           url:url,
           data:form_data,
           method:'POST',
           
           beforeSend : function()
           {
             showProcessingOverlay();
             
           },
           success:function(response)
           {
              hideProcessingOverlay();
           
             if(typeof response =='object')
             {
               swal(response.status,response.description,response.status);
             }
             
             var title   =  response.status.charAt(0).toUpperCase() + response.status.slice(1);  
   
             swal({
                   title: title,
                   text : response.description,
                   type : response.status,
                   showCancelButton: false,
                   confirmButtonClass: "btn-success",
                   confirmButtonText: "OK",
                   closeOnConfirm: false,
                   closeOnCancel: false
                 },
                 function(isConfirm) {
                   if (isConfirm) {
                     location.href='{{$module_url_path}}';
                   } 
                 });
           }
        });
     }
   
   
   });
   
</script>
@endsection