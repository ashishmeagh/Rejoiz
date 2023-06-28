@extends('admin.layout.master')                   
@section('main_content')

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #333;
    background-color: transparent;
    color: #333;
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
            <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
   <!-- BEGIN Main Content -->
   <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
         @include('admin.layout._operation_status')
          {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!} 

            {{ csrf_field() }}
   
          <div class="col-sm-12 p-0">
          
            <div class="pull-right top_small_icon">

              <a  href="{{url('/')}}/admin/influencer/report_generator/csv" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a CSV"><i class="fa fa-file-text"></i></a> 

              <a  href="{{url('/')}}/admin/influencer/report_generator/xlsx" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a xlsx"><i class="fa fa-file-excel-o"></i></a> 


              <a  href="{{url('/')}}/admin/influencer/report_generator/pdf" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a PDF"><i class="fa fa-file-pdf-o"></i></a> 

              <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
              
              <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

               {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> --}}
                
              <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            
          </div>
          <div class="col-sm-12 table-responsive p-0">
            <input type="hidden" name="multi_action" value="" />
            
            <table class="table table-striped"  id="table_module" >
              <thead>
                <tr>
                  <th>
                    <div class="checkbox checkbox-success">

                      <input class="checkItemAll" id="checkbox0" type="checkbox">
                      <label for="checkbox0">  </label>
                    </div>
                  </th>
                   
                   
                  <th style="width: 9%" >Influencer Name</th>
                  <th style="width: 11%">Influencer Code</th>
                  <th>Email</th>
                  <th>Contact No</th>
                  <th style="width: 8%;">Registration Date</th>
                  <th>Status</th>
                  <th>Is Stripe Connected</th>
                  <th style="width: 8%;">Action</th>
                  <th style="width: 15%;">Orders</th>
                  
                  

                </tr>
  
               </thead>
             </table>
          </div>
        <div> </div>
         
          {!! Form::close() !!}
      </div>
  </div>
   </div>
</div>

<!-- END Main Content -->
<script type="text/javascript">

  var module_url_path = "{{$module_url_path or ''}}";
  var table_module;

  $(document).ready(function() 
  { 
     
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
      processing: true,
      serverSide: true,
      responsive:true,
      bFilter: false ,
      stateSave: true,
            order : [[ 1, "desc" ]],
            
            ajax: {
                    url:module_url_path+'/get_influencer_list',

                   'data': function(d)
                        {
                          d['column_filter[q_user_name]']    = $("input[name='q_user_name']").val()
                          d['column_filter[q_influencer_code]']         = $("input[name='q_influencer_code']").val()
                          d['column_filter[q_email]']        = $("input[name='q_email']").val()
                          d['column_filter[q_contact_no]']   = $("input[name='q_contact_no']").val()
                          d['column_filter[q_status]']       = $("select[name='q_status']").val()
                          d['column_filter[q_date]']         = $("input[name='q_date']").val()
                          
                        }
                  },
            
            columns: [

                  {
                     render : function(data, type, row, meta) 
                     {
                          return '<div class="checkbox checkbox-success"><input type="checkbox" '+
                             ' name="checked_record[]" '+  
                             ' value="'+row.id+'" id="checkbox'+row.id+'" class="case checkboxInput"/><label for="checkbox'+row.id+'">  </label></div>';
                        
                     },
                     "orderable": false,
                     "searchable":false
                  },
      
                  
                  {data: 'user_name', "orderable": false, "searchable":false},
                  {data: 'influencer_code', "orderable": false, "searchable":false},
                  {data: 'email', "orderable": false, "searchable":false},
                  {data: 'contact_no', "orderable": false, "searchable":false},
                  {data: 'registration_date', "orderable": true, "searchable":false},
               

                 
                  {data: 'status',
                      orderable: false, 
                      searchable: false,
                      // responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        
                          if(row.status == 1)
                          {
                              return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this,'deactivate')"/>`
                          }
                          else
                          {
                              return `<input type="checkbox" data-size="small" data-enc_id="`+row.id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this,'activate')"/>`
                          }
                        
                        
                    }
                  },
                  {
                      render(data, type, row, meta)
                      {
                        if(row.is_stripe_connected == true)
                        {
                           return '<label class="label label-success">Yes</label>';
                        }
                        else
                        {
                          let stripe_connection_status = '<label class="label label-danger">No</label>';

                          let send_email_btn = `<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip editstyle" data-size="small" onclick="sendStripeAccountLink($(this))" data-influencer_id="`+row.id+`" title="Send Stripe Connection Request">Send Email</button>`;

                          return stripe_connection_status + send_email_btn;
                        }
                      },
                      "orderable": false, "searchable":false
                  },

                  {                  
                    render(data, type, row, meta)
                    {
                        return  `

                          <a href="`+module_url_path+`/view/`+row.id+`" data-toggle="tooltip"  data-size="small" title="View" class="btn btn-circle btn-success btn-outline show-tooltip editstyle">View</a>
                          
                          <a href="`+module_url_path+`/customer_orders/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Customer Orders" class="btn btn-circle btn-success btn-outline show-tooltip editstyle">Customer Orders</a>

                        `;

                         // <a href="`+module_url_path+`/delete/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>                        
                    },

                    "orderable": false, "searchable":false
                  },

                  //{data: 'orders', "orderable": false, "searchable":false},
                  {                  
                    render(data, type, row, meta)
                    {
                      
                        return  `

                        <table class="table table-sm table-bordered w-auto">
                          <tbody>
                            <tr>
                              <td>Completed</td>
                              <td>`+row.orders.completed+`</td>
                            </tr>
                            <tr>
                              <td scope="col">Pending</td>
                              <td>`+row.orders.pending+`</td>
                            </tr>
                            <tr>
                              <td scope="col">Cancelled</td>
                              <td>`+row.orders.cancelled+`</td>
                            </tr>
                          </tbody>
                          
                        </table>

                        `;

                         // <a href="`+module_url_path+`/delete/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>                        
                    },

                    "orderable": false, "searchable":false
                  },
                  

                
                
                ],
            
       });



        $('#table_module').on('draw.dt',function(event)
        {
            toggle_switch();
            toggleSelect();
        });

  

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

        $("#table_module").on('page.dt', function (){

        var info = table_module.page.info();
       
        $("input.checkItemAll").prop('checked',false);
    
        });


        /*search box*/
        $("#table_module").find("thead").append(`<tr>   
             
                <td></td>       
             

                <td><input type="text" name="q_user_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                <td><input type="text" name="q_influencer_code" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 
                 
                <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                <td><input type="text" name="q_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>
                

                <td>
                  <select name="q_status" class="search-block-new-table column_filter form-control td-select-dropdown" id="q_status" onchange="filterData();">
                   <option value="">All</option>
                   <option value="1">Active</option>
                   <option value="0">Deactivate</option>
                  </select>
                </td>

                <td></td>
      
            </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

  });

    
  function toggle_switch()
  {
    // Switchery
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
    $('.js-switch').each(function() {
      new Switchery($(this)[0], $(this).data());
    });  
  }

  function filterData()
  {
    table_module.draw();
  }


  function show_details(url)
  { 
    window.location.href = url;
  } 

  
  function change_status(reference,type)
  {
     /* var type   = reference.attr('data-type');*/
      var msg    = '';
      if(type ==  'activate')
      {
        msg        = "Are you sure? Do you want to activate this Influencer status.";
      }
      else if(type == 'deactivate')
      {
        msg       = "Are you sure? Do you want to deactivate this Influencer status. "; 
      }

      swal({
              title: "Need Confirmation",
             /* text: 'Are you sure? Do you really want to change the status?',*/
             text : msg,
              type: 'warning',
              confirmButtonText: "Ok",
              showCancelButton: true,
              closeOnConfirm: false
          },
          function(isConfirm) {
            if (isConfirm) 
            {
               perform_change_status_action(reference);
            } 
            else
            {
              $(reference).trigger('click');
            }
      });
  }

  function perform_change_status_action(reference)
  {

      var enc_id = reference.getAttribute('data-enc_id');
      var status = reference.getAttribute('data-type');
          
         $.ajax({
             url:module_url_path+'/change_status',
             type: 'GET',
             dataType:'json',
             data:{user_id:enc_id,status:status},
             beforeSend: function() 
             {
              showProcessingOverlay();                 
             },
             success:function(data)
             {
                 hideProcessingOverlay();  
                 if(data.status =='success')
                 {   
                    swal({
                        title: "Success",
                        text: data.description,
                        type: data.status,
                        confirmButtonText: "Ok",
                        closeOnConfirm: false
                    },
                    function(isConfirm) 
                    {
                      if (isConfirm) 
                      {
                          location.reload();
                      } 
                    });
                 }
                 else
                 {
                     swal("Error", data.description, data.status);
                 }
             }
         });
    }

  
  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you really want to delete this promo code record?');
  }

  $(function(){
    $("input.checkItemAll").click(function(){
        if($(this). prop("checked") == true){
          $("input.checkboxInput").prop('checked',true);
        }
        else{
          $("input.checkboxInput").prop('checked',false);
        }
    });
  });

  function sendStripeAccountLink(ref)
  {

    let user_id = ref.attr('data-influencer_id');
  
    let token   = "{{csrf_token()}}";

     $.ajax({
            url: module_url_path+'/send_stripe_acc_creation_link',
            type:"POST",
            data: {
                    "_token":token,
                    "user_id":user_id
                  },
            beforeSend : function()
            {
              showProcessingOverlay();
             
            },
            success:function(data)
            { 
                hideProcessingOverlay();

                if(data.status == 'success')
                {
                  swal("Success",data.description,data.status);
                }
                else
                {
                  swal("Error",data.description,data.status);
                }
            }
          }); 
  }

</script>
@stop                    


