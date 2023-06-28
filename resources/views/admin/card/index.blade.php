@extends('admin.layout.master')  
@section('main_content')

<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{isset($module_title)?ucfirst($module_title) : ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
              <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
              <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
        
         <div class="col-lg-12">
            @include('admin.layout._operation_status')
            <div class="white-box">
              <div class="pull-right top_small_icon">            
                <a href="{{$module_url_path}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>   

               
                <a href="javascript:void(0)" id='add_new_card'  class="btn btn-outline btn-info btn-circle show-tooltip" title="Add Card Details"><i class="fa fa-plus"></i> </a>             
              </div> 
               <div >
                 <div class="table-responsive">
                  <table class="table table-striped" id="table_module">
                     <thead>
                        <tr>
                           <th> Card No.</th>
                           <th> Card type</th>
                           <th> Expiry Date</th>
                           <th  width="200px">Action</th>
                           
                        </tr>
                     </thead>
                     <tbody>
                        
                       @if(isset($arr_card_details) && count($arr_card_details) > 0)
                          @foreach($arr_card_details as $card)
                          
                          @php
                            $card_expiry_month = isset($card['exp_month'])?$card['exp_month']:'';
                            $card_expiry_year  = isset($card['exp_year'])?$card['exp_year']:'';
                            $card_no = str_pad($card['card_no'], 16, "X", STR_PAD_LEFT);

                            $card_expiry = $card_expiry_month.' / '.$card_expiry_year;
                          @endphp

                          <tr>
                            <td>{{isset($card_no)?$card_no:''}}</td>
                            <td>{{isset($card['card_type'])?$card['card_type']:''}}</td>
                            <td>{{$card_expiry or ''}}</td>
                            <td>
                              <a href="{{$module_url_path}}/edit/{{base64_encode($card['stripe_card_id'])}}/{{base64_encode($card['customer_id'])}}"  class="btn btn-circle btn-success btn-outline show-tooltip" title="Edit Card Details">Edit </a>

                             <a href="javascript:void(0)" attr-href="{{$module_url_path}}/delete/{{base64_encode($card['stripe_card_id'])}}/{{base64_encode($card['customer_id'])}}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Delete Card Details" onclick=" return deleteCard(this);">Delete</i> </a>

                            </td>
                            
                          </tr>
                          @endforeach
                          @else
                          <tr>
                            <td class="text-center" colspan="4">
                              No any card details
                            </td>
                          </tr>
                       @endif
                     </tbody>
                  </table>
                </div>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->
<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">
  var table_module = false;
  var retailer_id      = "{{$retailer_id or 0}}";


  $(document).ready(function()
  {
      $('#add_new_card').click(function(){

        var count = {{count($arr_card_details)}};

        if(count >= 5){
          swal('Warning',' You can add only five cards.','warning');
          return;
        }
        else
        {
          location.href='{{ $module_url_path or '' }}/add';
        }

      });

  });

  
  function deleteCard(ref)
  {
    var url = $(ref).attr('attr-href');

    swal({
      title: "Need Confirmation",
      text:  "Are you sure? Do you want to remove this card from cards list.",
      type:  "warning",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "OK",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
    },
    function(isConfirm) 
    {
      if (isConfirm) 
      {
            $.ajax({
              url:url,
              method:'GET',
              beforeSend : function()
              {
                showProcessingOverlay();
               
              },
              success:function(response)
              {
                  hideProcessingOverlay();
                 
                  //swal(response.status,response.description,response.status);

                  swal({
                  title:'Success',
                  text: response.description,
                  type: response.status,
                  showCancelButton: false,
                  confirmButtonClass: "btn-success",
                  confirmButtonText: "OK",
                  cancelButtonText: "Cancel",
                  closeOnConfirm: true,
                  closeOnCancel: true
                },
                function(isConfirm) 
                {
                  location.reload(true);
                })
              }
           });
        }
     
    });
}
  
</script>
@stop