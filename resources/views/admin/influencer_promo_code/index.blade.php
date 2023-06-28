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
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
         <h4 class="page-title">{{$module_title or ''}}</h4>
      </div>
      <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
         <ol class="breadcrumb">
            {{-- <li><a href="{{$module_url_path or ''}}/dashboard">Dashboard</a></li> --}}
          
            <li><a href="{{url('/')}}/{{$admin_panel_slug or ''}}/dashboard">Dashboard</a></li>
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
   
          <div class="influencer_top_flex">
            
            <div class="pull-left">
             <div class="influencer_discount">
               <label>Discount On Promo Code(%)</label>
              <span>${{ isset($arr_influencer_settings['discount_on_promo_code'])?num_format($arr_influencer_settings['discount_on_promo_code']):'' }}</span>
             </div>

              <div class="influencer_reward">
              <label>Reward Amount</label>
              <span>${{ isset($arr_influencer_settings['reward_amount'])?num_format($arr_influencer_settings['reward_amount']):'' }} </span>
            </div>
            </div>

            <div class="pull-right top_small_icon">
                
                 <a  href="{{$module_url_path}}/create" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add"><i class="ti-plus"></i></a> 

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>
                
                <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            
          </div>
          <div class="table-responsive">
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
                   
                   
                  <th>Promo Code</th>
                  <th>Status</th>
                  <th>Is Assigned</th>
                  <th>Assigned Influencer Name</th>
                  <th>Start Date</th>
                  <th>Expiry Date</th>
                  <th>Action</th>

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

<div class="modal" tabindex="-1" role="dialog" id="assignPromoCodeModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Promo Code</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" id="assign_promo_code_form">
        {{csrf_field()}}
        <input type="hidden" name="current_promo_code_id" id="current_promo_code_id" value=""> 
        <input type="hidden" name="current_promo_code_name" id="current_promo_code_name" value=""> 
        <div class="modal-body">
            <p><i class="red">Note: Please be careful while assigning promo code, once you assigned you can not edit, delete or reassign the promo code</i></p>
            <div class="form-group row">
              <label class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-form-label" for="influencer_id">Influencer<i class="red">*</i></label>
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-9">
                <select class="form-control" name="influencer_id" id="influencer_id" required="true" data-parsley-required-message="Please select influencer" onchange="checkStripeAccountConnectedOrNot(this.value)">
                  <option value="">Select Influencer</option>
                  @if(isset($arr_influencer) && count($arr_influencer)>0)
                    @foreach($arr_influencer as $influencer)
                      <option value="{{ $influencer['id'] }}">{{ $influencer['first_name'] or '' }} {{ $influencer['last_name'] or '' }}</option>
                    @endforeach
                  @endif
                </select>
             </div>
            </div>     

            <div class="form-group row" id="connection_request_div" style="display: none;">
              <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                <p><b>Stripe Connection Request</b></p>
                <p>Currently this user is not associated with us on stripe, do you want to send email for stripe account association.</p>

                <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>

              </div>
            </div>
        </div>
      </form>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn_assign_promo_code">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
     $('[data-toggle="tooltip"]').tooltip();

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
                    url:module_url_path+'/get_influencer_promo_code_listing',

                   'data': function(d)
                        {
                          
                          d['column_filter[q_promo_code]']   = $("input[name='q_promo_code']").val()
                          d['column_filter[q_user_name]']    = $("input[name='q_user_name']").val()
                          d['column_filter[q_status]']       = $("select[name='q_status']").val()
                          d['column_filter[q_is_assigned]']  = $("select[name='q_is_assigned']").val()
                        
                        }
            
                  },
            
            columns: [

                  {
                     render : function(data, type, row, meta) 
                     {
                        if(row.is_assigned != 1)
                        {
                          return '<div class="checkbox checkbox-success"><input type="checkbox" '+
                             ' name="checked_record[]" '+  
                             ' value="'+row.id+'" id="checkbox'+row.id+'" class="case checkboxInput"/><label for="checkbox'+row.id+'">  </label></div>';
                        }
                        else
                        {
                          return '';
                        }
                        
                     },
                     "orderable": false,
                     "searchable":false
                  },
      
                  
                  {data: 'promo_code_name', "orderable": false, "searchable":false},
               

                 
                  {data: 'is_active',
                      orderable: false, 
                      searchable: false,
                      // responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.is_assigned != 1)
                        {
                          if(row.is_active == 1)
                          {
                              return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this,'deactivate' )"/>`
                          }
                          else
                          {
                              return `<input type="checkbox" data-size="small" data-enc_id="`+row.id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this,'activate')"/>`
                          }
                        }
                        else
                        {
                            if(row.is_active == 1)
                            {
                              return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.id+`" id="status_'.$data->id.'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" disabled/>`
                            }
                            else
                            {
                              return `<input type="checkbox" data-size="small" data-enc_id="`+row.id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" disabled/>`
                            }
                        }
                        
                    }
                  },

                  {data: 'is_assigned',
                      orderable: false, 
                      searchable: false,
                      // responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.is_assigned == 1)
                        {
                            return `<label class="label label-success">Yes</label>`
                        }
                        else
                        {
                            return `<label class="label label-danger">No</label>`
                        }
                    }
                  },

                  {data: 'user_name', "orderable": false, "searchable":false},
                  
                  {data: 'assigned_date', "orderable": false, "searchable":false},
                  
                  {data: 'expiry_date', "orderable": false, "searchable":false},

                  {                  
                    render(data, type, row, meta)
                    {
                      if(row.is_assigned != 1)
                      { 
                          let assign_promo_code_btn = '';
                          if(row.is_active == 1)
                          {
                            assign_promo_code_btn = `<button type="button" class="btn btn-circle btn-success btn-outline show-tooltip editstyle" onclick="openAssignPromoCodeModal(this)" data-enc_id="`+row.id+`" data-promo_code_name="`+row.promo_code_name+`">Assign Promo Code</button>`;
                          }
                          else
                          {
                            assign_promo_code_btn = `<button type="button" title="Please activate the promo code" data-toggle="tooltip" class="btn btn-circle btn-success btn-outline show-tooltip editstyle" disabled>Assign Promo Code</button>`;
                          }

                         let edit_delete_btn = `

                          <a href="`+module_url_path+`/edit/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Edit" class="btn btn-circle btn-success btn-outline show-tooltip editstyle">Edit</a>

                        
                           <a href="`+module_url_path+`/delete/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip deletestyle" onclick="confirm_delete(this,event);">Delete</a>

                             `;
                             
                          return assign_promo_code_btn+''+edit_delete_btn;
                      }
                      else
                      {
                          return '<label>Assigned</label>';
                      }
                        
                    },

                    "orderable": false, "searchable":false
                  }],
            
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
             

                 <td><input type="text" name="q_promo_code" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

              
               
                <td>
                  <select name="q_status" class="search-block-new-table column_filter form-control td-select-dropdown" id="q_status" onchange="filterData();">
                   <option value="">All</option>
                  
                   <option value="1">Active</option>
                    <option value="0">Deactivate</option>
                  </select>
                </td>

                <td>
                  <select name="q_is_assigned" class="search-block-new-table column_filter form-control" id="q_is_assigned" onchange="filterData();">
                   <option value="">All</option>
                   <option value="0">No</option>
                   <option value="1">Yes</option>
                  </select>
                </td>

                <td><input type="text" name="q_user_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                <td></td>
                <td></td>
                <td></td>
      
            </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

  });

  function openAssignPromoCodeModal(reference){
    $('#assignPromoCodeModal').modal('show');
    let current_promo_code_id = reference.getAttribute('data-enc_id');
    let current_promo_code_name = reference.getAttribute('data-promo_code_name');
    $('#current_promo_code_id').val(current_promo_code_id);
    $('#current_promo_code_name').val(current_promo_code_name);
  }
    
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
       var ref    = reference; 

       var msg    = '';
        if(type ==  'activate')
        {
        msg        = "Are you sure? Do you want to activate this Influencer Promo Code status.";
        }
        else if(type == 'deactivate')
        {
        msg       = "Are you sure? Do you want to deactivate this Influencer Promo Code status. "; 
        }

      swal({
              title: "Need Confirmation",
              /*text: 'Are you sure? Do you really want to change the status?',*/
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
            } else
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
             data:{promo_code_id:enc_id,status:status},
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

  $('#btn_assign_promo_code').click(function(){

      if($('#assign_promo_code_form').parsley().validate()==false) return;

      var formdata = $("#assign_promo_code_form").serialize();

       $.ajax({
            url: module_url_path+'/assign_promo_code',
            type:"POST",
            data: formdata,
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
                   $('#assign_promo_code_form')[0].reset();
                   $('#assignPromoCodeModal').modal('toggle');

                   swal({
                           title:"Success",
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
                    $('#assign_promo_code_form')[0].reset();
                    $('#assignPromoCodeModal').modal('toggle');

                    var status = data.status;
                        status = status.charAt(0).toUpperCase() + status.slice(1);
                      
                    swal(status,data.description,data.status);
                }  
            }
            
          }); 
  });

  function checkStripeAccountConnectedOrNot(influencer_id)
  {
     $.ajax({
        url:module_url_path+'/check_stripe_account_connected_or_not',
        type:'GET',
        dataType:'JSON',
        data:{influencer_id:influencer_id},
        beforeSend:function()
        {
          showProcessingOverlay();
        },
        success:function(response)
        {
          hideProcessingOverlay();

          if(response.status == 'success')
          {
            /*
               is_connected = 0 (Influencer is not connected to admin stripe account)
            */
            if(response.data.is_connected == '0')
            {
              $('#connection_request_div').show();
            }
            else if(response.data.is_connected == '1')
            {
              $('#connection_request_div').hide();
            }
          }
          else
          {
            swal("Error", response.description, response.status);
          }
        }
     });
  }

  function sendStripeAccountLink()
  {
    let user_id = $('#influencer_id').val();

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
