@extends('maker.layout.master')                
@section('main_content')

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
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
            {{-- <li><a href="{{$module_url_path or ''}}/dashboard">Dashboard</a></li> --}}
          
            <li><a href="{{url('/')}}/{{$maker_panel_slug or ''}}/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
   <!-- BEGIN Main Content -->
   <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
         @include('maker.layout._operation_status')
          {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!} 

            {{ csrf_field() }}
   
          <div class="col-sm-12">
            
            <div class="pull-left">
              <label>Discount On Promo Code(%)</label>
              <p>{{ $arr_influencer_settings['discount_on_promo_code'] or '' }}</p>

              <label>Reward Amount</label>
              <p> {{ $arr_influencer_settings['reward_amount'] or '' }} </p>
            </div>

            <div class="pull-right top_small_icon">
                
                 <a  href="{{$module_url_path}}/create" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add"><i class="ti-plus"></i></a> 

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>
                
                <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            
          </div>
          <br/>
          <br>
          <div class="col-sm-12 table-responsive">
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
        <div class="modal-body">
            <p><i class="red">Note: Please be careful while assigning promo code, once you assigned you can not edit, delete or reassign the promo code</i></p>
            <div class="form-group row">
              <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="influencer_id">Influencer<i class="red">*</i></label>
              <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                <select class="form-control" name="influencer_id" required="true" data-parsley-required-message="Please select influencer">
                  <option value="">Select Influencer</option>
                  @if(isset($arr_influencer) && count($arr_influencer)>0)
                    @foreach($arr_influencer as $influencer)
                      <option value="{{ $influencer['id'] }}">{{ $influencer['first_name'] or '' }} {{ $influencer['last_name'] or '' }}</option>
                    @endforeach
                  @endif
                </select>
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
                              return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
                          }
                          else
                          {
                              return `<input type="checkbox" data-size="small" data-enc_id="`+row.id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
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

                  {                  
                    render(data, type, row, meta)
                    {
                      if(row.is_assigned != 1)
                      { 
                          let assign_promo_code_btn = '';
                          if(row.is_active == 1)
                          {
                            assign_promo_code_btn = `<button type="button" class="btn btn-primary btn-sm" onclick="openAssignPromoCodeModal(this)" data-enc_id="`+row.id+`">Assign Promo Code</button>`;
                          }
                          else
                          {
                            assign_promo_code_btn = `<button type="button" title="Please activate the promo code" data-toggle="tooltip" class="btn btn-primary btn-sm" disabled>Assign Promo Code</button>`;
                          }

                         let edit_delete_btn = `

                          <a href="`+module_url_path+`/edit/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Edit" class="btn btn-cirle btn-success btn-outline show-tooltip">Edit</a>

                        
                           <a href="`+module_url_path+`/delete/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>

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
                  <select name="q_status" class="search-block-new-table column_filter form-control" id="q_status" onchange="filterData();">
                   <option value="">All</option>
                   <option value="0">Block</option>
                   <option value="1">Active</option>
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
      
            </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

  });

  function openAssignPromoCodeModal(reference){
    $('#assignPromoCodeModal').modal('show');
    let current_promo_code_id = reference.getAttribute('data-enc_id');
    $('#current_promo_code_id').val(current_promo_code_id);

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

  
  function change_status(reference)
  {
      swal({
              title: "Need Confirmation",
              text: 'Are you sure? Do you really want to change the status?',
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

</script>
@stop                    


