@extends('admin.layout.master')  
@section('main_content')
<style type="text/css">

  .table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
  .pro-list-bg {
    position: relative;
  }
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
  .btn-danger.btn-outline {
    border: 1px solid #d0d0d0;
    background-color: #fff;
    color: #444;
}
   .filter_btn{
      margin-top: 20px;
      margin-right: 2px;
   }

.table > tbody > tr > td:first-child a{
  text-decoration: underline;
  text-underline-position: under;
}
.table > tbody > tr > td:first-child a:hover{
  text-decoration: none;
}
.send-email-content .box {margin-bottom:15px;}
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
              <li><a href="{{url('/')}}/{{$curr_panel_slug or ''}}/dashboard">Dashboard</a></li>
              <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            <div class="white-box">  

              <div class="pull-right top_small_icon">
           
                 <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
              </div>
              

               <div class="table-responsive">                
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Sr No.</th>                           
                           <th> Vendor</th>
                           <th> Quote Generate Date</th>
                           <th> Expected Delivery Date</th>
                           <th> Status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="6" align="right"> &nbsp;</th> 
                            </tr>
                      </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->
<!-- Get a Quote Modal -->
<div class="modal fade vendor-Modal" id="sendEmailToVendor" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Send Email To Vendor</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
     
      <div class="modal-body">
        <form method="post" id="sendQuoteEmail"> 
          {{csrf_field()}}


          <!-- <div class="sendquote-details-mdl">
          <div class="row">
            <div class="col-md-6">
            <div class="vendor-email-ttl">Mail To : <span id="vendorEmail"></span></div>
            <div class="vendor-email-ttl">Product Name : <span id="productname"></span></div>
            <div class="vendor-email-ttl">Brand Name : <span id="brandname"></span></div>
            </div>
            <div class="col-md-6">
              <div class="vendor-email-ttl">Product Quantity : <span id="quotequantity"></span></div>
              <div class="vendor-email-ttl">Expected Delivery in Days : <span id="expecteddeliverydays"></span></div>
            </div>
          </div>
          </div> -->


          <div class="send-email-content">

            <div class="row mb-2">

              <div class="col-sm-12 col-md-6 col-lg-6 box">
                <div class="col-sm-12 col-md-12 col-lg-12">
                <label>Mail To</label>
                </div>
              
                
                <div class="col-sm-12 col-md-8 col-lg-8">
                <span id="vendorEmail"></span>
                </div>
              </div>
               
               <div class="col-sm-12 col-md-6 col-lg-6 box">
                <div class="col-sm-12 col-md-12 col-lg-12">
                <label>Expected Delivery in Days</label>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12">
                <span id="expecteddeliverydays"></span>
                </div>
              </div>
              
              <div class="col-sm-12 col-md-6 col-lg-6 box">
                <div class="col-sm-12 col-md-12 col-lg-12">
                <label>Product Name</label>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12">
                <span id ="productname"></span>
                </div>
              </div>

              <div class="col-sm-12 col-md-6 col-lg-6 box">
                <div class="col-sm-12 col-md-12 col-lg-12">
                <label>Product Quantity</label>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12">
                <span id ="quotequantity"></span>
                </div>
              </div>

               <div class="col-sm-12 col-md-6 col-lg-6 box">
                <div class="col-sm-12 col-md-12 col-lg-12">
                <label>Brand Name</label>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12">
                <span id ="brandname"></span>
                </div>
              </div>

            </div>

          </div>


          <div class="col-sm-12">
            <label class="addition-font">Additional Notes</label>
            <textarea name="email_body" id="email_body" data-parsley-maxlength="500" data-parsley-maxlength-message="Only a maximum of 500 characters is allowed."></textarea>
            <span id="emailBody_error_message"></span>
          </div>
          <input type="hidden" name="vendor_email" id="vendor_email" value="">
          <input type="hidden" name="vendor_name" id="vendor_name" value="">
          <input type="hidden" name="product_name" id="product_name" value="">
          <input type="hidden" name="brand_name" id="brand_name" value="">
          <input type="hidden" name="quote_quantity" id="quote_quantity" value="">
          <input type="hidden" name="expected_delivery_days" id="expected_delivery_days" value="">
          <input type="hidden" name="company_name" id="company_name" value="">
          <input type="hidden" name="quote_id" id="quote_id" value="">
        </form>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">        
        <div class="col-sm-12 text-right">
          <button type="button" class="btn btn-primary" onclick="sendEmailToVendor()" >Send</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> 
        </div>    
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
var module_url_path  = "{{ $module_url_path or '' }}";  </script>

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
      "order":[3,'Asc'],

      ajax: {
      'url': module_url_path+'/get_all_get_quote_requests',
      'data': function(d)
       { 
         d['column_filter[q_vendorname]']    = $("input[name='q_vendorname']").val()
         d['column_filter[q_generate_date]']    = $("input[name='q_generate_date']").val()
         d['column_filter[q_delivery_date]']    = $("input[name='q_delivery_date']").val()
         d['column_filter[q_status]']    = $("select[name='q_status']").val()          
       }
      },
      drawCallback:function(settings)
      {
       
      },
      columns: [
      {
        render(data, type, row, meta)
        {
          return meta.row + meta.settings._iDisplayStart + 1;
        },
        "orderable": false, "searchable":false
      }, 
      {
        render(data, type, row, meta)
        {
          
          return row.company_name+`</br>`+`<small>`+row.vendor_email+`</small>`;

        },
        "orderable": false, "searchable":false
      },
      {data:'generate_date',"orderable": false, "searchable":false},
      {data:'expected_delivery_date',"orderable": false, "searchable":false},
      {data:'status',"orderable": false, "searchable":false},
      {data:'action',"orderable": false, "searchable":false},
              
      
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });
 

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td></td>  
          <td><input type="text" name="q_vendorname" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>    
          <td><input type="text" name="q_generate_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>
          <td><input type="text" name="q_delivery_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>
          <td><select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Email Sent</option>
                  <option value="0">Pending</option>
                  
            </select></td>
        </tr>`);

  $('input.column_filter').on( 'keyup click', function () 
  {
       filterData();
  });
  });

  function filterData()
  {
    table_module.draw();
  }  

  function viewQuoteDetails()
  { 
    var productname = jQuery('#viewQuote').attr('data-productname');
    var description = jQuery('#viewQuote').attr('data-description');
    var user_name = jQuery('#viewQuote').attr('data-name');
    var user_email = jQuery('#viewQuote').attr('data-email');
    var contact_number = jQuery('#viewQuote').attr('data-number');
    var additional_notes = jQuery('#viewQuote').attr('data-notes');
    var quote_quantity = jQuery('#viewQuote').attr('data-quantity');
    var no_of_days = jQuery('#viewQuote').attr('data-days');
    var expected_date = jQuery('#viewQuote').attr('data-date');
    
    jQuery('#productname').text(productname);
    jQuery('#description').html(description);
    jQuery('#user_name').text(user_name);
    jQuery('#user_email').text(user_email);
    jQuery('#contact_number').text(contact_number);
    jQuery('#additional_notes').text(additional_notes);
    jQuery('#quote_quantity').text(quote_quantity);
    jQuery('#no_of_days').text(no_of_days);
    jQuery('#expected_date').text(expected_date);

    jQuery('#quote_details').modal('show');
  }

  function sendVendorEmail(quote_id)
  {
    var vendor_email = jQuery('#buttonSendEmail_'+quote_id).attr('data-vendoremail');
    var vendorname = jQuery('#buttonSendEmail_'+quote_id).attr('data-vendorname');
    var productname = jQuery('#buttonSendEmail_'+quote_id).attr('data-productname');
    var newbrandname = jQuery('#buttonSendEmail_'+quote_id).attr('data-newbrandname');
    
    var quotequantity = jQuery('#buttonSendEmail_'+quote_id).attr('data-quotequantity');
    var expecteddeliverydays = jQuery('#buttonSendEmail_'+quote_id).attr('data-expecteddeliverydays');
    var companyname = jQuery('#buttonSendEmail_'+quote_id).attr('data-companyname');
    var quote_id = jQuery('#buttonSendEmail_'+quote_id).attr('data-quoteid');

    jQuery('#vendorEmail').text(vendor_email);
    jQuery('#productname').text(productname);
    jQuery('#brandname').text(newbrandname);
    jQuery('#quotequantity').text(quotequantity);
    jQuery('#expecteddeliverydays').text(expecteddeliverydays+' Days');

    jQuery('#vendor_email').val(vendor_email);
    jQuery('#vendor_name').val(vendorname);
    jQuery('#product_name').val(productname);
    jQuery('#brand_name').val(newbrandname);
    jQuery('#quote_quantity').val(quotequantity);
    jQuery('#expected_delivery_days').val(expecteddeliverydays);
    jQuery('#company_name').val(companyname);
    jQuery('#quote_id').val(quote_id);
    jQuery('#sendEmailToVendor').modal('show');
  }

  function sendEmailToVendor()
  {
    tinymce.get('email_body').save();

    if ($('#sendQuoteEmail').parsley().validate() == false) {
      return;
    }
    
    var formData = $('#sendQuoteEmail').serialize();   
    
    $.ajax({
      url: '{{url("/admin/quote_requests/send_quote_email_to_vendor")}}',
      method: 'POST',
      dataType: 'JSON',
      data: formData,
      beforeSend: function() {
        showProcessingOverlay();
      },
      success: function(response) {
        hideProcessingOverlay();
        
        if (response.status == 'SUCCESS') {              
          swal({
              title: "Success",
              text: response.description,
              type: 'success',
              showCancelButton: false,
              confirmButtonClass: "btn-success",
              confirmButtonText: "OK",
              closeOnConfirm: true
            },
            function() {                  
              $('#sendEmailToVendor').modal('hide');  
              $('#sendQuoteEmail').find("input[type=text], textarea, input[type=number]").val("");  
              tinyMCE.activeEditor.setContent(''); 
              $('#table_module').DataTable().ajax.reload();             
            });
        } else {    
          var status = response.status;
          status = status.charAt(0).toUpperCase() + status.slice(1);
          swal(status, response.description, response.status);
        }
      }

    });
  }
</script>
@stop