@extends('influencer.layout.master')                
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
          
            <li><a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
    @include('influencer.layout._operation_status')
   <!-- BEGIN Main Content -->
   <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
          {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!} 

            {{ csrf_field() }}
         
          <div class="col-sm-12 table-responsive">
            <input type="hidden" name="multi_action" value="" />
            
            <table class="table table-striped"  id="table_module" >
              <thead>
                <tr>
                  <th>Promo Code</th>
                 {{--  <th>Vendor Name</th> --}}
                 <th>Start Date</th>
                 <th>Expiry Date</th>
                 <th>Used Count</th>
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

      table_module = $('#table_module').DataTable({
        processing: true,
        serverSide: true,
        responsive:true,
        bFilter: false ,
        stateSave: true,
            // order : [[ 1, "desc" ]],
            
            ajax: {
                    url:module_url_path+'/get_promo_code_listing',

                   'data': function(d)
                        {
                          
                          d['column_filter[q_promo_code]']  = $("input[name='q_promo_code']").val()
                         /* d['column_filter[q_vendor_name]'] = $("input[name='q_vendor_name']").val()*/
                        }
            
                  },
            
            columns: [
                
                  {data: 'promo_code_name', "orderable": false, "searchable":false},
                  /*{
                     render : function(data, type, row, meta) 
                     {
                         return row.company_name + ' <span>('+ row.vendor_name +')</span>';
                     },
                     "orderable": false,
                     "searchable":false
                  }*/
                  {data: 'assigned_date', "orderable": false, "searchable":false},
                  {data: 'expiry_date', "orderable": false, "searchable":false},
                  {data: 'promo_code_used_cnt', "orderable": false, "searchable":false},
                  ],
            
       });

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

        /*search box*/

        /*<td><input type="text" name="q_vendor_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>*/

        $("#table_module").find("thead").append(`<tr>   
              
                <td><input type="text" name="q_promo_code" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                
                <td></td>
                <td></td>
                <td></td>
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

</script>
@stop                    


