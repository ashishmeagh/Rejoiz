@extends('admin.layout.master')                
@section('main_content')

<style>
  .btn-primary, .btn-primary.disabled{
    /*width: 180px;*/
    padding: 5px 10px 3px;
    display: inline-block; border-radius: 3px;
  }

.select2-search--inline {
    display: contents; /*this will make the container disappear, making the child the one who sets the width of the element*/
}

.select2-search__field:placeholder-shown {
    width: 100% !important; /*makes the placeholder to be 100% of the width while there are no options selected*/
}
  
th {
    white-space: nowrap;
}

.vendorSequenceModel  .form-inline .form-control {width:100%;}

.input-group-btn{display: none;}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
    color: #564126;
    text-decoration: none;
    outline: 0;
    background-color: #ffe8ca;
    border-bottom: 1px solid #fff5e8;
}

.dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}

</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<!-- For multiselect with search -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>





<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4> </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">

                    <div class="col-sm-12">

                        <div class="white-box">
                        @include('admin.layout._operation_status')
                         <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
                          {{ csrf_field() }}
                         <div class=" pull-right top_small_icon">
                         <!--  data-target="#vendorSequenceModel" -->
                          <a data-toggle="modal" onclick="get_vendor_data_in_modal()"  href="javascript:void()" class="btn btn-outline btn-info btn-circle show-tooltip" title="Vendor Sequencing"><i class="fa fa-arrows-v"></i></a>

                          <a  href="{{url('/')}}/admin/vendor/report_generator/csv" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a CSV"><i class="fa fa-file-text"></i></a>

                          <a  href="{{url('/')}}/admin/vendor/report_generator/xlsx" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a xlsx "><i class="fa fa-file-excel-o"></i></a>  

                          <a  href="{{url('/')}}/admin/vendor/report_generator/pdf" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a PDF"><i class="fa fa-file-pdf-o"></i></a> 

                          <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
                          
                          <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

                          <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 

                          </div>
                           <div class="clearfix"></div>
                            <div class="">
                            <input type="hidden" name="multi_action" value="" />
                                <table id="table_module" class="table table-striped table-responsive">
                                  <thead>
                                    <tr>
                                    <th>
                                        <div class="checkbox checkbox-success"><input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                                        </div>
                                    </th>
                                            <th>Rejoiz Company Name</th>
                                            <th>Company Name</th>
                                            <th>User</th>
                                            <th>Email Id</th>
                                            <th>Contact No.</th>
                                            <th>Registration Date</th>
                                            <th>User Email Status</th>
                                            <th>Admin Approval Status</th>
                                            <th>Direct Payment</th>
                                            <th>Get a Quote</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
          <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog update-commission-modal">
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Commission</h4>
                </div>
            
              <div class="modal-body">
                <p class="mkr-nm-commsn" id="maker_name"></p>
                <div class="mkr-cmmn-main-hm">
                <div class="mkr-cmmn-main">Commission:</div> 
                <div class="mkr-cmmn-input">
                  <input type="text" name="maker_commission" id="maker_commission" data-parsley-type="number" data-parsley-trigger="keyup" data-parsley-required="true" data-parsley-maxlength="5" placeholder="Please Enter Commission" data-parsley-required-message="Please Enter Commission"/>
                  <input type="hidden" name="maker_id" id="maker_id" >
                </div>
                <div class="clearfix"></div>
                </div>

              </div>

        
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-default submit-btn" id="submit_commission">Submit</button>
            </div>
          </div>
        </div>
        </form>
      </div>


<!-- multiple select dropdown modal -->
<div id="ModalRepresentative" data-controls-modal="ModalRepresentative" data-backdrop="static" data-keyboard="false" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog md-size-mdl">
        <!-- Modal content-->
        <div class="modal-content sign-up-popup">
            <div class="modal-body">
                    <div class="login-form-block">
                        <div class="login-content-block">

                          <div class="categorymodalslst">
                      

                            <div class="row">
                              <div class="col-md-12">
                                <label>Assign Representatives</label><br>
                                <input type="hidden" name="hid_row_id" id="hid_row_id">
                            <select class ="select2 form-group" id="boot-multiselect-demo" name="representative_id[]" multiple="multiple" data-placeholder="Select Representatives" style="width: 100%" >
                              
                                 @if(isset($representative_arr) && count($representative_arr)>0)
                                    @foreach($representative_arr as $key=>$representative)
                                     @if(isset($representative['get_user_details']) && count($representative['get_user_details']))
                                     <option value="{{$representative['user_id']}}">{{$representative['get_user_details']['first_name'].' '.$representative['get_user_details']['last_name']}}</option>
                                     @endif
                                    @endforeach

                                @endif
                        </select>
                          <span id="error_container" class="red"></span>

                              </div>
                            </div>

                          </div>
                           <div class="modal-footer space-top-inx">
                          <a class="logi-link-block btn-primary" data-toggle="modal" id="btn_submit" is_button="submit">Save</a>

                          <button id="close_submit" type="button" class="btn logi-link-block btn-primary" data-dismiss="modal" is_button="cancel" onclick="ResetPopup()" >Skip</button>
                      </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div> 

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Admin Commission (%)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="col-md-12 p-0">          
        <form method="post" data-parsley-validate="true" action="{{$module_url_path}}/update_admin_commission">

          {{csrf_field()}}
          
           <input type="text" class="form-control" name="admin_commission" id="admin_commission" value="{{isset($admin_commission)?$admin_commission:0}}" min="1" max="100" data-parsley-type='number' data-parsley-required="true">

           <input type="hidden" class="form-control" id="vendor_id" name="vendor_id" value="{{isset($admin_commission)?$admin_commission:0}}">

        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-secondary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
        </form>
    </div>
  </div>
</div> 

<input type="hidden" name="vendor_id" id="vendor_id" value="">
<!-- Get a Quote Modal -->
<div class="modal fade vendor-Modal" id="get_a_Quote" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Get A Quote Settings</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>     
      <div class="modal-body">
      <div class="geta-quote-main-bx">
        <div class="geta-quote-left-bx">Do you want to show get a quote button for this vendor?</div>
        <div class="switchbx">
          <div id="div_getaquote"></div>            
        </div>
      </div>
      <div class="geta-quote-main-bx">
        <div class="geta-quote-left-bx">Do you want to show add to cart button for this vendor?</div>
        <div class="switchbx">
          <div id="div_quoteAddToBag"></div>            
        </div>
      </div>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>     
      </div>
    </div>
  </div>
</div>




<!-- Vendor Sequence Model -->
<div class="modal fade" id="vendorSequenceModel" tabindex="-1" role="dialog" aria-labelledby="vendorSequenceModelCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-vendor-sequence" role="document">
    <div class="modal-content" >
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Vendor Sequence</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" data-parsley-validate="true" id="vendor-sequence-form" action="">
        <div class="modal-body">                 

            {{csrf_field()}}
            
            <div class="col-sm-12 table-responsive">
              <table class="table table-bordered" id="get_vendor_data_in_modal_table">
              <thead>
                <tr>
                 <!--  <th>Sr. No.</th> -->
                  <th>Status</th>
                  <th>Company Name</th>
                  <th>Vendor Name</th>
                  <th>Sequence</th>
                </tr>
                <tr>
                    <!-- <td></td> -->
                    <td>
                       <select class="search-block-new-table column_filter_in_modal form-control-small" name="q_modal_status" id="q_modal_status" onchange="filter_data_in_modal();">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Deactivate</option>
                        </select>
                    </td>         
                    <td><input type="text" name="q_modal_company_name" id="q_modal_company_name" placeholder="Search" class="search-block-new-table column_filter_in_modal form-control-small" /></td>
                    <td><input type="text" name="q_modal_username" id="q_modal_username" placeholder="Search" class="search-block-new-table column_filter_in_modal form-control-small" /></td>
                              
                    <td><input type="text" name="q_modal_sequence_no" id="q_modal_sequence_no" placeholder="Search" class="search-block-new-table column_filter_in_modal form-control-small" /></td>
                </tr>
              </thead>
            </table> 
            </div>     
        </div>
        <div class="modal-footer vendor-sequence-modal-footer">
          @php
          if(count($vendor_list)>0)
          {
          @endphp          
            <button  onclick="saveVendorSequence()" type="button" id="save_sequence" class="btn btn-secondary">Save changes</button>

            <button type="button" id="set_default_sequence" class="btn btn-secondary set_default_sequence">Set default sequence</button>
          @php
          }
          @endphp
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
        </form>
    </div>
  </div>
</div>   

<!-- End Vendor Sequence Model -->


<script type="text/javascript">
  $(document).ready(function() {
  //     $('#boot-multiselect-demo').multiselect({
  //     includeSelectAllOption:false,
  //     enableFiltering: false,
  //     nonSelectedText: 'Select Representatives'
  // });
        });
</script>

<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";

  var table_module = false;

  $(document).ready(function()
  { 
     $(".select2").select2();
         
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      stateSave: true,
     
      bFilter: false ,
      ajax: {
      'url':'{{ $module_url_path.'/get_makers'}}',
      'data': function(d)
        {
          d['column_filter[q_email]']        = $("input[name='q_email']").val()
          d['column_filter[q_company_name]'] = $("input[name='q_company_name']").val()
           d['column_filter[q_real_company_name]'] = $("input[name='q_real_company_name']").val()

         /* d['column_filter[q_brand_name]']= $("input[name='q_brand_name']").val()*/
          d['column_filter[q_username]']   = $("input[name='q_username']").val()
          d['column_filter[q_status]']     = $("select[name='q_status']").val()       
          d['column_filter[q_is_approved]']= $("select[name='q_is_approved']").val()
          d['column_filter[q_contact_no]'] = $("input[name='q_contact_no']").val()
          d['column_filter[q_commission]'] = $("input[name='q_commission']").val()
          d['column_filter[q_get_a_quote]'] = $("select[name='q_get_a_quote']").val()
          d['column_filter[q_action]']     = $("select[name='action']").val()
          d['column_filter[q_date]']       = $("input[name='q_date']").val()

        }
      },

      columns: [
      {
      render: function(data, type, row, meta)
      {
        return '<div class="checkbox checkbox-success"><input type="checkbox" id="checked_record" name="checked_record[]" value="'+row.enc_id+'" class="checkItem case"><label style="text-decoration: none"></label></div>';
      },
        "orderable": false, "searchable":false
      },
      {data: 'company_name',"orderable":false,"searchable":false},
      {data: 'real_company_name',"orderable":false,"searchable":false},

      // {data: 'user_name', "orderable": false, "searchable":false},
      {
        render: function(data, type, row, meta)
        {
         return `<a href="`+module_url_path+`/view/`+row.enc_id+`" class="link_v">`+row.user_name+`</a>`;
        },
          "orderable": false, "searchable":false
      },
      {data: 'email',     "orderable": false, "searchable":false},
      /*{data: 'brand_name', "orderable": true, "searchable":true},*/
      {data: 'contact_no', "orderable": false, "searchable":false},

      {data: 'registration_date', "orderable": true, "searchable":false},
  
      {
        render : function(data, type, row, meta) 
        {
          return row.build_status_btn;
        },
        "orderable": false, "searchable":false
      }, 
      {
         data : 'is_approved',  
         render : function(data, type, row, meta) 
         { 
            if(row.is_approved == '1')
            {
               return `<input type="checkbox" checked data-size="small" class="js-switch" onchange='changeAprovalStatus(`+row.id+`,$(this))' data-color="#99D683" data-secondary-color="#F96262" action="deactivate" id="approve_status`+row.id+`" data-type="deactivate"/>`
            }
            else
            {
              return `<input type="checkbox" data-size="small" class="js-switch" onchange='changeAprovalStatus(`+row.id+`,$(this))' data-color="#99D683" data-secondary-color="#F96262" action="activate" id="approve_status`+row.id+`" data-type="activate"/>`
            }
         },
         "orderable": false,
         "searchable":false
       },  

       {
         data : 'is_direct_payment',  
         render : function(data, type, row, meta) 
         { 
           if(row.is_direct_payment == '1')
           {
             return `<input type="checkbox" checked data-size="small" class="js-switch" onchange='changePaymentStatus(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" action="deactivate"/>`
           }
           else
           {
             return `<input type="checkbox" data-size="small" class="js-switch" onchange='changePaymentStatus(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" action="activate"/>`
           }
         },
         "orderable": false,
         "searchable":false
       },   
       {
        render : function(data, type, row, meta) 
        {
          return row.get_a_quote;
        },
        "orderable": false, "searchable":false
      },  
      {
        render : function(data, type, row, meta) 
        {
          return row.build_action_btn;
        },
        "orderable": false, "searchable":false
      }
      ]
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

    $('#table_module').on('draw.dt',function(event)
    {
      var oTable = $('#table_module').dataTable();
      var recordLength = oTable.fnGetData().length;
      $('#record_count').html(recordLength);

      var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
           new Switchery($(this)[0], $(this).data());
        });

      $("input.toggleSwitch").change(function(){
          statusChange($(this));
       }); 

        toggleSelect();
    });



    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td></td>
                    <td><input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_real_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>


                     <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                      <td><input type="text" name="q_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>

                 

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Deactivate</option>
                        </select>
                    </td>

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_is_approved" id="q_is_approved" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Disapproved</option>
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                </tr>`);
        $('input.column_filter').on( 'keyup click', function () 
        {
            filterData();
        });


        $("#table_module").on('page.dt', function (){

          var info = table_module.page.info();
         
          $("input.checkItemAll").prop('checked',false);
      
        });
  });

  function filterData()
  {
    table_module.draw();
  }

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
  }
  
  function statusChange(data)
  {

      var ref     = data; 
      var type    = data.attr('data-type');
      var enc_id  = data.attr('data-enc_id');
      var id      = data.attr('data-id');

     // alert(type);

      var msg = 'Are you sure? Do you want to '+ type +' this vendor email status.';

    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },

     function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {

            $.ajax({
                url:module_url_path+'/'+type,
                type:'GET',
                data:{id:enc_id},
                dataType:'json',
                beforeSend : function()
                {
                  showProcessingOverlay();
                },
                success: function(response)
                { 
                    hideProcessingOverlay();
                    if(response.status=='SUCCESS')
                    {
                      if(response.data=='ACTIVE')
                      {
                        $(ref)[0].checked = true;  
                        $(ref).attr('data-type','deactivate');

                        // swal('Success','Vendor has been activated.','success');
                         swal({
                                  title:"Success",
                                  text: "Vendor email status has been activated.",
                                  type: "success",
                                  showCancelButton: false,                
                                  confirmButtonColor: "#8CD4F5",
                                  confirmButtonText: "OK",
                                  closeOnConfirm: false
                            },
                            function(isConfirm,tmp)
                            {
                              if(isConfirm==true)
                              {
                                location.reload();
                              }
                            });
    
                      }
                      else
                      {
                        $(ref)[0].checked = false;  
                        $(ref).attr('data-type','activate');

                        swal('Success','Vendor has been deactivated.','success');
                        location.reload();
                      }

                      
                    }
                    else
                    {
                      sweetAlert('Error','Something went wrong,please try again.','error');
                    }  
                }
            }); 
        }  
        else
        {
          $(data).trigger('click');
        }

      });
   } 

   // Open get a quote modal
   function viewGetAQuoteSettings(get_a_quote, add_to_bag, user_id)
   { 
     if(get_a_quote == 1)
     {
        jQuery("#div_getaquote").html('<input type="checkbox" id="changeGetaQuote" data-size="small" data-userid="'+user_id+'" data-getqoutevalue="'+get_a_quote+'" class="js-switch" onchange="setGetaQuote()" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" Checked />');
        var elem = document.querySelector('#changeGetaQuote');
        var init = new Switchery(elem, { size : 'small', color: '#99CC66'});
     }
     else
     {
        jQuery("#div_getaquote").html('<input type="checkbox" id="changeGetaQuote" data-size="small" data-userid="'+user_id+'" data-getqoutevalue="'+get_a_quote+'" class="js-switch" onchange="setGetaQuote()" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />');
        var elem = document.querySelector('#changeGetaQuote');
        var init = new Switchery(elem, { size : 'small', secondaryColor: '#FF6666'});
     }

     if(add_to_bag == 1)
     { 
        jQuery("#div_quoteAddToBag").html('<input type="checkbox" onchange="updateAddToBag()" id="changeAddToBag" data-size="small" data-userid="'+user_id+'" data-addtobagvalue="'+add_to_bag+'" class="js-switch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" Checked />');
        var elem = document.querySelector('#changeAddToBag');
        var init = new Switchery(elem, { size : 'small', color: '#99CC66'});
     }
     else
     {
        jQuery("#div_quoteAddToBag").html('<input type="checkbox" onchange="updateAddToBag()" id="changeAddToBag" data-size="small" data-userid="'+user_id+'" data-addtobagvalue="'+add_to_bag+'" class="js-switch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />');
        var elem = document.querySelector('#changeAddToBag');
        var init = new Switchery(elem, { size : 'small', secondaryColor: '#FF6666'});
     }

     jQuery('#get_a_Quote').modal('toggle');
   }

  // mark as a get a quote
  function setGetaQuote(data)
  {
    var updateQuoteStatus = 0;
    var user_id  = jQuery("#changeGetaQuote").attr('data-userid');
    var quotestatus = jQuery("#changeGetaQuote").attr('data-getqoutevalue');

    var msg = 'Are you sure you want to continue?';

    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },

     function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {

            $.ajax({
                url:module_url_path+'/update_status_get_a_quote',
                type:'GET',
                data:{id:user_id, quotestatus: quotestatus},
                dataType:'json',
                beforeSend : function()
                {
                  showProcessingOverlay();
                },
                success: function(response)
                { 
                  $('#table_module').DataTable().ajax.reload();
                    hideProcessingOverlay();
                    if(response.status=='SUCCESS')
                    {

                      if(quotestatus == '1'){
                        updateQuoteStatus = '0';
                      } else {
                        updateQuoteStatus = '1';
                      }
                      jQuery("#changeGetaQuote").attr('data-getqoutevalue',updateQuoteStatus);
                      swal('Success',response.message,'success');                      
                    }
                    else
                    {
                      sweetAlert('Error','Something went wrong,please try again.','error');
                    }  
                }
            }); 
        }  
        else
        {
          var element = $('#changeGetaQuote');
          if(quotestatus == 1)
          {
            changeSwitchery(element, true);
          }
          else
          {
            changeSwitchery(element, false);
          }          
          $(data).trigger('click');
        }

      });
   }

  function changeSwitchery(element, checked) {
    if ( ( element.is(':checked') && checked == false ) || ( !element.is(':checked') && checked == true ) ) {
      element.parent().find('.switchery').trigger('click');
    }
  }

   // update status of add to bag button
  function updateAddToBag(data)
  {
    
    var updateQuoteStatus = 0;
    var user_id  = jQuery("#changeAddToBag").attr('data-userid');
    var quotestatus = jQuery("#changeAddToBag").attr('data-addtobagvalue');

    var msg = 'Are you sure you want to continue?';

    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },

     function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {

            $.ajax({
                url:module_url_path+'/update_status_add_to_bag',
                type:'GET',
                data:{id:user_id, quotestatus: quotestatus},
                dataType:'json',
                beforeSend : function()
                {
                  showProcessingOverlay();
                },
                success: function(response)
                { 
                    $('#table_module').DataTable().ajax.reload();
                    hideProcessingOverlay();
                    if(response.status=='SUCCESS')
                    {
                      if(quotestatus == '1'){
                        updateQuoteStatus = '0';
                      } else {
                        updateQuoteStatus = '1';
                      }
                      jQuery("#changeAddToBag").attr('data-addtobagvalue',updateQuoteStatus);

                      swal('Success',response.message,'success');
                    }
                    else
                    {
                      var element = $('#changeAddToBag');
                      if(quotestatus == 1)
                      {
                        changeSwitchery(element, true);
                      }
                      else
                      {
                        changeSwitchery(element, false);
                      }
                      $(data).trigger('click');
                      sweetAlert('Error',response.message,'error');

                    }  
                }
            }); 
        }  
        else
        {
          var element = $('#changeAddToBag');
          if(quotestatus == 1)
          {
            changeSwitchery(element, true);
          }
          else
          {
            changeSwitchery(element, false);
          }
          $(data).trigger('click');
        }

      });
   
  }
  
function is_approvedChange(data)
{ 

    var msg = 'Are you sure? Do you want to perform this action.';

    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },
    function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {
          var ref = data; 
          var type = data.attr('data-type');
          var enc_id = data.attr('data-enc_id');
          var id = data.attr('data-id');

          $.ajax({
              url:module_url_path+'/'+type,
              type:'GET',
              data:{id:enc_id},
              dataType:'json',
              success: function(response)
              {
                if(response.status=='SUCCESS')
                {
                  if(response.data=='ACTIVE')
                  {
                    $(ref)[0].checked = true;  
                    $(ref).attr('data-type','deactivate');

                  }else
                  {
                    $(ref)[0].checked = false;  
                    $(ref).attr('data-type','activate');
                  }

                  swal('Success','Status has been changed.','success');
                }
                else
                {
                  sweetAlert('Error','Something went wrong,please try again.','error');
                }  
              }
          }); 
        }
        else
        {
          $(data).trigger('click');
        }
    });
} 


function changeAprovalStatus(maker_id,ref,row_id)
{   

    action = $(ref).attr('action');
    msg = '';
    var type    = $(ref).attr('data-type');

    var row_id = maker_id;

    if(type == 'activate'){ 
      var msg = 'Are you sure? Do you want to approve this vendor.'; 
    } else {
      var msg = 'Are you sure? Do you want to disapprove this vendor.'; 
    }
    // if(action == 'activate')
    // {
    //    var msg = 'Are you sure? Do you want to approve this vendor.';
    // }
    // else if(action == 'deactivate')
    // {
    //   var msg = 'Are you sure? Do you want to disapprove this vendor.'; 
    // }


    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },
    function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {
            maker_id = btoa(maker_id);
            $('#vendor_id').val(maker_id);
            representative_arr = [];
            $.ajax({
                url      : module_url_path+'/get_count',
                method   : 'GET',
                dataType : 'JSON',
                data     : {maker_id:maker_id},
                
                success  : function(response)
                {                 
                      
                    if(response.count == 0 && (type == 'activate'))
                    {
                       $("#hid_row_id").val(row_id);
                       $('#ModalRepresentative').modal('show');
                    }
                    else
                    {
                        if($(ref).is(":checked"))
                        {
                          makerAprovalStatus = '1';
                        }
                        else
                        {
                          makerAprovalStatus = '0';
                        }

                              
                        $.ajax({
                                method   : 'GET',
                                dataType : 'JSON',
                                data     : {maker_id:maker_id,representative_arr:representative_arr,makerAprovalStatus:makerAprovalStatus,type : type},
                                url      : module_url_path+'/changeAprovalStatus',
                                beforeSend : function()
                                {
                                  showProcessingOverlay();
                                },
                                success  : function(response)
                                {  
                                      hideProcessingOverlay();                       
                                      if(typeof response == 'object' && response.status == 'SUCCESS')
                                      {
                                          //swal('Success', response.message, 'success');
                                          if(response.data=='ACTIVE')
                                          {
                                              $(ref)[0].checked = true;  
                                              $(ref).attr('data-type','deactivate');
                                              swal('Success',response.message, 'success');

                                          }
                                          else
                                          {
                                              $(ref)[0].checked = false;  
                                              $(ref).attr('data-type','activate');
                                              swal('Success',response.message, 'success');
                                          }
                                      }
                                      else
                                      {
                                        swal('Error', response.message, 'error');
                                      }  
         
                                }

                        });

                    }
                  

                },
                error:function(response)
                {
                  swal('Error','Something went wrong,please try again.','error');
                }


            });

        }
        else
        {
         $(ref).trigger('click');
        }

    });
  
 
}

/* Direct pay payment to vendor  */

function changePaymentStatus(maker_id,ref)
{   
    action = $(ref).attr('action');
    msg = '';
   
    if(action == 'activate')
    {
       var msg = 'Are you sure? Do you want to allowed direct payment.';
    }
    else if(action == 'deactivate')
    {
      var msg = 'Are you sure? Do you want to block direct payment.'; 
    }

    if($(ref).is(":checked"))
    {
      makerAprovalStatus = '1';
    }
    else
    {
      makerAprovalStatus = '0';
    }

    swal({
          title: "Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },
    function(isConfirm,tmp)
    {
        if(isConfirm==true)
        {
            maker_id = btoa(maker_id);
            $('#vendor_id').val(maker_id);
            representative_arr = [];
           
            $.ajax({
                      method   : 'POST',
                      data     : {maker_id:maker_id,makerAprovalStatus:makerAprovalStatus, "_token": "{{ csrf_token() }}"},
                      url      : module_url_path+'/change_payment_status',
                      success  : function(response)
                      {          
                        if(typeof response == 'object' && response.status == 'SUCCESS')
                        {
                          swal('Success', response.message, 'success');
                        }
                        else
                        {
                          swal('Error', response.message, 'error');
                        }    
                      }

                    });

        }
        else
        {
          $(ref).trigger('click');
        }

    });
  
 
}





$('#btn_submit').click(function(){

    var makerAprovalStatus = '';

    var maker_id = $('#vendor_id').val();

    var row_id = $("#hid_row_id").val();
   
    var representative_arr = $("#boot-multiselect-demo").val();
     
    var text = $('#boot-multiselect-demo').val();

    if(text == '')
    {
        $('#error_container').text('This value is required.');
        return false;
    }

    $('#ModalRepresentative').modal('hide');

    $.ajax({
         method   : 'GET',
         dataType : 'JSON',
         data     : {maker_id:maker_id,representative_arr:representative_arr},
         url      : module_url_path+'/changeAprovalStatus',
         success  : function(response)
         {                         
          if(typeof response == 'object' && response.status == 'SUCCESS')
          {
            //swal('Done', response.message, 'success');
            if(response.data=='ACTIVE')
            {
              $("#approve_status"+row_id).attr('data-type','deactivate');
              swal('Done',response.message, 'success');

            }
            else
            {
              $("#approve_status"+row_id).attr('data-type','activate');
              swal('Done',response.message, 'success');
            }
          }
          else
          {
            swal('Oops...', response.message, 'error');
          }               
         }
    });

});

$('#close_submit').click(function(){

    var makerAprovalStatus = '';

    var maker_id = $('#vendor_id').val();

    var row_id = $("#hid_row_id").val();
   
    var representative_arr = '';

    var type = 'activate';
     
    var text = $('#boot-multiselect-demo').val();

    // if(text == '')
    // {
    //     $('#error_container').text('This value is required.');
    //     return false;
    // }

    $('#ModalRepresentative').modal('hide');

    $.ajax({
         method   : 'GET',
         dataType : 'JSON',
         data     : {maker_id:maker_id,representative_arr:representative_arr,type:type},
         url      : module_url_path+'/changeAprovalStatus',
         success  : function(response)
         {                         
          if(typeof response == 'object' && response.status == 'SUCCESS')
          {
            //swal('Done', response.message, 'success');
            if(response.data=='ACTIVE')
            {
              $("#approve_status"+row_id).attr('data-type','deactivate');
              swal('Done',response.message, 'success');

            }
            else
            {
              $("#approve_status"+row_id).attr('data-type','activate');
              swal('Done',response.message, 'success');
            }
          }
          else
          {
            swal('Oops...', response.message, 'error');
          }               
         }
    });

});

/*function changeAprovalStatus(maker_id,ref)
{   

    var makerAprovalStatus = '';

    if($(ref).is(":checked"))
    {
      makerAprovalStatus = '1';
    }
    else
    {
      makerAprovalStatus = '0';
    }
     
    $.ajax({
         method   : 'GET',
         dataType : 'JSON',
         data     : {maker_id:maker_id,makerAprovalStatus:makerAprovalStatus},
         url      : module_url_path+'/changeAprovalStatus/',
         success  : function(response)
         {                         
          if(typeof response == 'object' && response.status == 'SUCCESS')
          {
            swal('Done', response.message, 'success');
          }
          else
          {
            swal('Oops...', response.message, 'error');
          }               
         }
    });

  }*/


function setcom(reff)
{ 

    $('#maker_commission').parsley().refresh();
    var maker_name = $(reff).attr('data-name');
    var maker_id = $(reff).attr('data-id');
    var maker_commission = $(reff).attr('data-commission');
    var commission = parseFloat(maker_commission).toFixed(2);
    if(commission=="NaN")
    {var commission ='';
     $('#maker_commission').val(commission);
    }
    $('#maker_commission').val(commission);

    $("#maker_name").text("Vendor Name:"+maker_name);
    $("#maker_id").text(maker_id);


    $("#submit_commission").click(function() {
    $('#maker_commission').parsley().validate();
     var maker_commission = $('#maker_commission').val();

    if($('#maker_commission').parsley().isValid()==false)
      {
        return;
      }
     $.ajax({
                   method   : 'GET',
                   dataType : 'JSON',
                   data     : {maker_id:maker_id,makerCommission:maker_commission},
                   url      : module_url_path+'/updateCommission/',
                   success  : function(response)
                   {                         
                    if(typeof response == 'object' && response.status == 'SUCCESS')
                    {
                     /* swal('Done', response.message, 'success');*/
                      swal({title: "Done", text:response.message, type: "success"},
                          function()
                          { 
                            location.reload();
                           });
                    }
                    else
                    {
                      swal('Oops...', response.message, 'error');

                       swal({title: "Oops...", text:response.message, type: "error"},
                          function()
                          { 
                            location.reload();
                           });
                    }               
                   }
               });
            });

}


/*-------------------auther priyanka date 28 Aug----------------------------*/
$(function(){

    $("input.checkItemAll").click(function(){
        
        if($(this). prop("checked") == true){
          $("input.checkItem").prop('checked',true);
        }
        else{
          $("input.checkItem").prop('checked',false);
        }

    });

});


function toggleSelect()
{
    $("input.checkItem").click(function()
    {  

        var checked_checkbox_length = $('input:checked[name="checked_record[]"]').map(function (){ return $(this).val(); } ).get();

        if(checked_checkbox_length.length < 10){
             
            $("input.checkItemAll").prop('checked',false);
        }
        else
        {
           $("input.checkItemAll").prop('checked',true);
        }

    });

}

/* set admin commission data for selected vendor for updation */
function setAdminCommission(vendor_id,admin_commission)
{
  $('#exampleModalCenter').modal('show'); 
  $('#vendor_id').val(vendor_id); 
  $('#admin_commission').val(admin_commission); 
}



/* set admin commission data for selected vendor for updation */
function setVendorSequence()
{
  $('#vendorSequenceModel').modal('show'); 
}

// Get vendor data to update sequence by Harshada on date 09 Nov 2020

$(document).ready(function(){
   table_module_in_modal = $('#get_vendor_data_in_modal_table').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      stateSave: true,
     
      bFilter: false ,
      ajax: {
      'url':'{{ $module_url_path.'/get_makers_in_modal'}}',
      'data': function(d)
        {        
          d['column_filter_in_modal[q_modal_company_name]']   = $("input[name='q_modal_company_name']").val()
          d['column_filter_in_modal[q_modal_username]']       = $("input[name='q_modal_username']").val()
          d['column_filter_in_modal[q_modal_status]']         = $("select[name='q_modal_status']").val() 
          d['column_filter_in_modal[q_modal_sequence_no]']    = $("input[name='q_modal_sequence_no']").val()          
          
        }
      },

      columns: [
      // {
      //   render : function(data, type, row, meta) 
      //   {
      //     return row.enc_id;
      //   },
      //   "orderable": false, "searchable":false
      // }, 
       {
        render : function(data, type, row, meta) 
        {
          return row.modal_build_status_btn;
        },
        "orderable": false, "searchable":false
      }, 
      {data: 'modal_company_name',"orderable":false,"searchable":false},


      // {data: 'user_name', "orderable": false, "searchable":false},
      {
        render: function(data, type, row, meta)
        {
         return row.modal_user_name;
        },
          "orderable": false, "searchable":false
      },
      {
        render: function(data, type, row, meta)
        {
         return row.modal_sequence_no;
        },
          "orderable": false, "searchable":false
      },
  
     
      
      ]
      });
 

    /*search box*/
    
        $('input.column_filter_in_modal').on( 'keyup click', function () 
        {
            if($("#q_modal_status").val() != "" || $("#q_modal_company_name").val() != "" || $("#q_modal_username").val() != "" || $("#q_modal_sequence_no").val() != "") {
            $(".set_default_sequence").hide();
          } else {
            $(".set_default_sequence").show();
          }
            filter_data_in_modal();
          
        });
});
function get_vendor_data_in_modal(){
  $(".column_filter_in_modal").val("");
  table_module_in_modal.draw();
  $('#vendorSequenceModel').modal('show');   
  $(".set_default_sequence").show();     
}

function filter_data_in_modal(){  
  table_module_in_modal.draw();

}
/*----------------------------------------------------------------------------------------------*/

$("#set_default_sequence").click(function(){
  var seqNo = 1;
  showProcessingOverlay();
  $(".vendor-sequence_no").each(function(){    
    $(this).val(seqNo);
    seqNo++;
  });
   hideProcessingOverlay();
  //saveVendorSequence();

});

function saveVendorSequence(){
  if($('#vendor-sequence-form').parsley().validate()==false) return;   

  if($('#vendor-sequence-form').parsley().isValid() == true )
  {
    var form_data = $('#vendor-sequence-form').serialize();   
    var url       = module_url_path +'/save_vendor_sequence';
     
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
          if(response.status && response.status=="success")
          {           

            swal({
                 title: "Success",
                 text: response.message,
                 type: "success",
                 showCancelButton: false,
                 confirmButtonClass: "btn-success",
                 confirmButtonText: "OK",
                 closeOnConfirm: false,
                 closeOnCancel: false
               },
               function(isConfirm) {
                 if (isConfirm) {                  
                   location.reload();
                 } 
               });
           
          }
          else
          {
            swal('Warning',response.message,'warning');

          }          
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) 
        {
          swal('Warning','Unable to get response from server, please try after sometimes','warning');
        }
    });
  }
}


function checkDuplicateSequence(instance) {
  $('.er_msg').remove();
  var repeatedUser = '';
  $(".vendor-sequence_no").each(function(){    
    if($(this).data('sr_no') == $(instance).data('sr_no'))
    {   

      return;
    }
    if($(this).val() == $(instance).val())
    {    
      repeatedUser += $(this).data('user_name') +" ( Company name : "+ $(this).data('company_name') +" ). ";     
    }    
    
  });

  if($(instance).val() == 0)
  {

    $("<span class='er_msg d-block' style='color:red;'>You can't assign 0 value. </span>").insertAfter(instance);
    $("#save_sequence").hide();
    return; 
  }
  else
  {
    checkAlreadySequence(instance,$(instance).val());
  }


  
  // if(repeatedUser !='')
  // {
  //   $("<span class='er_msg' style='color:red;'>Sequence already alloted to " + repeatedUser +"</span>").insertAfter(instance); 

  //   $("#save_sequence").hide();
  // }
  // else
  // {
  //   $("#save_sequence").show();


  // }
}

function checkAlreadySequence(instance,sequence_no) {
  // console.log('erg',erg);

  var prev_sequence_no = $(instance).attr('data-prev_sequence');
  var changed_maker_id = $(instance).attr('data-sr_no');

  // console.log('sequence_no',sequence_no,instance,prev_sequence_no);
  $.ajax({
          url: module_url_path+'/check_sequence_no_present',
          data: {sequence_no:sequence_no,prev_sequence_no:prev_sequence_no,changed_maker_id:changed_maker_id},
        
          type:"get",
          success:function(data)
          {
            if(data.status != null && data.status == 'error')
            {
              $("<span class='er_msg d-block' style='color:red;'>Sequence already alloted to " + data.message + "</span>").insertAfter(instance); 

              $("#save_sequence").hide();
            }
            else
            {
              $("#save_sequence").show();
            }
          }
        });
}


function ResetPopup(){
  $(".select2").select2('val','');

}

</script>

<style type="text/css">
  .my_div_active{
    height: 20px;
    width: 20px;
    background-color: rgb(153, 214, 131);
    text-align: center;
    padding: 3px;
    border-radius: 50%;
  }
  .er_msg{
    font-size: 11px;
  }
  .my_div_deactive{
    height: 20px;
    width: 20px;
    background-color: rgb(249, 98, 98);
    text-align: center;
    padding: 3px;
    border-radius: 50%;
  }
  .modal-vendor-sequence {
    max-width: 800px !important;
  }
  .modal-vendor-sequence .table>tbody>tr>td {padding:5px;}
</style>


@stop                    
