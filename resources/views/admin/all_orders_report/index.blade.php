@extends('admin.layout.master')  
@section('main_content')

<style type="text/css">
.table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
.pro-list-bg {position: relative;}
  .pro-list-bg span{
       display: inline-block;
    font-weight: 600;
    color: #333;
    width: 18px;
    height: 18px;
    text-align: center;
    background-color: #ececec;
    margin-left: 10px;
    border-radius: 50%;
    line-height: 18px;
    font-size: 10px;
  }
  .butns-right .btn-circle{
        width: 36px;
    height: 36px;    padding: 8px 0;
  }
.downloadbtns-btn{padding: 9px 30px !important; margin-left: 6px;display: inline-block; background:none; border: 1px solid #666;color: #fff;}
.downloadbtns-btn:hover{background-color:none; border: 1px solid #666;color: #666;}
th {white-space: nowrap;}
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
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
           <div class="white-box">
            @include('admin.layout._operation_status')
           
              <div class="butns-right">            
                 <form class="form-horizontal" id="report_form">
              
          </form>
         </div> 

         <div class="col-md-3 mb-4"> 
          <label>Commission Payment Status</label>
          <select class="form-control" name="commission_status">
              <option value="">Commission Payment Status</option>
              <option value="1">Pending</option>
              <option value="2">Paid</option>
              
          </select>

          </div>
           <div class="col-md-3 mb-4">

            <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="Search" id="update">Search</button>                              
          </div>

          <div class="col-md-6 mb-4"></div>
           
            <div class="col-md-12">
              <div class="col-sm-6">
                <h3 class="mb-5">
                  <span id="commission_text">Total Commissions Payable</span>:
                  <b>$<span id="commi">0.00</span></b>
                </h3>
              </div> 
            </div>  

              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th> 
                           <th> Order Date</th>
                           <th> Vendor Commission Amount</th>                          
                           <th> Order Placed By</th>
                           <th> Vendor</th>
                           <th> Total Amount </th>
                           <th> Payment Status</th>
                          
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
                  </div>
            </div>
             </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>


<!-- Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Vendor Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="vendorPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="maker_id"   id="maker_id">
          <input type="hidden" name="order_id"   id="orderId" >
          <input type="hidden" name="amount"     id="amount">

          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission(%) :</div>
              <div class="admin-commission-lnk-right">{{isset($site_setting_arr['commission'])?num_format($site_setting_arr['commission']):0}}%</div>
              </div>
            </div>
             <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission($) :</div>
              <div class="admin-commission-lnk-right">$<span id="actual_amount"></span>
              </div>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Total Order Amount :</div>
              <div class="admin-commission-lnk-right">$<span id="order_amount"></span>
              </div>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Amount Payable to Vendor :</div>
              <div class="admin-commission-lnk-right">$<span id="pay_amount" class="pay_amount"></span>
              </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">
    
<!-- Stripe Connection Modal -->
<div class="modal fade " id="sendStripeLinkModel" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="">Stripe Connection Request</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {{--  This user is not connected to {{$site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Admin'}} stripe account , send account creation link . --}}

      Currently this user is not associated with us on stripe, do you want to send email for stripe account association.

      </div>
      <div class="modal-footer">

        <input type="hidden" name="user_id" id="user_id" value="">

       
        <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>

         <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
         
      </div>
    </div>
  </div>
</div>

<!-- /#page-wrapper -->
<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">

  var table_module = false;
 
  $(document).ready(function()
  {
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_all_orders',
      'data': function(d)
       {        
          d['column_filter[commission_status]'] = $("select[name='commission_status']").val()
       }
      },

      columns: [
      {data: 'order_no', "orderable": false, "searchable":false}, 

      {data: 'order_date', "orderable": false, "searchable":false},   

      {
        render : function(data, type, row, meta) 
        {
         
          return '$'+row.vendor_commission_amount;
        },          
        "orderable": false, "searchable":false
      },

      
      {data: 'order_placed_by', "orderable": false, "searchable":false}, 


      {data:'vendor',"orderable": false, "searchable":false},
     
      {
        render(data, type, row, meta)
        {
            if(type == "display"){

            var total_commission_pending  = row.total_commission_pending;
            var total_commission_paid     = row.total_commission_paid;

          }
          else{
            var total_commission_pending  = '0.00';
            var total_commission_paid     = '0.00';

          }
          
          
          $('#commi').html(total_commission_pending);

          return '<i class="fa fa-dollar"></i> '+(+row.total_amount).toFixed(2);
        },
        "orderable": false, "searchable":false
      },
    
      {data: 'payment_status', "orderable": false, "searchable":false}, 
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

 
  
  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

  });

  function filterData()
  {
    table_module.draw();
  }

function show_product_list(ref)
{
  
  let tbl_id = $(ref).attr('data-tbl-id');
  let id = $('#'+tbl_id);
  
  if(id.is(":visible"))
  { 
    id.slideUp();
  }
  else
  {      
    id.slideDown();
  } 

};


 $('#update').click(function(){
    maker_name = '';
    var commissionStatus  = $("select[name='commission_status']").val();

    if(commissionStatus == 1)
    {
       $('#commission_text').html('Total Commission Payable to Vendor <b>'+maker_name+'</b> [Pending]');
    }
    else if(commissionStatus == 2)
    {
        $('#commission_text').html('Total Commission Paid to Vendor <b>'+maker_name+'</b> [Paid]');
    }

   filterData();


  }

</script>

<script type="text/javascript">
  
  $(document).ready(function()
  {
    $( function() {
        $(".datepicker" ).datepicker();
        $('#order_from_date').datepicker('setEndDate', '+0d');
        $('#order_to_date').datepicker('setEndDate', '+0d');

        $("#order_from_date").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#order_to_date').datepicker('setStartDate', minDate);
        });
      
        $("#order_to_date").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#order_from_date').datepicker('setEndDate', minDate);
          });
    
        });
     
  });


  function fillData(orderPrice,vendorAmount,adminCommissionAmount,makerId,orderId)
  {

    $('.vendor-Modal').modal('show');
    $('#order_amount').html(orderPrice.toFixed(2));    
    $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
    $('.pay_amount').html(vendorAmount.toFixed(2));    
    $('#maker_id').val(makerId);    
    $('#amount').val(vendorAmount.toFixed(2));    
    $('#orderId').val(orderId);    
  }



  
</script>
@stop