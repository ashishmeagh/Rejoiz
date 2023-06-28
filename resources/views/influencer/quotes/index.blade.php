@extends('influencer.layout.master')   
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

</script>
@stop