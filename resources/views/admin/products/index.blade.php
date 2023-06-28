@extends('admin.layout.master')                

@section('main_content')

<style type="text/css">
  
   th {
    white-space: nowrap;
}

.goto-page .form-group {display:flex; align-items:center; white-space:nowrap; margin-bottom:0px;    justify-content: flex-end;}
.inner-goto {display:flex; align-items:center; margin-right:10px;}
.inner-goto input {width:80px; height:24px;}
.inner-goto .btn-go {padding: 7px 10px !important;}
.goto-page .form-group span {margin-right:10px;}

.dataTables_wrapper .col-sm-12{
    overflow-y: hidden;
    display: block;
    
    overflow-x: auto;
  }
</style>

<!-- BEGIN Page Title -->
<!-- <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css"> -->
    
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
           @include('admin.layout._operation_status')
        <div class="white-box">
        	<div class="pull-right top_small_icon">
            <!-- <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a>  -->
             <button type="button" onclick="adminProductExport()" title="Export Admin Product as .csv" class="btn btn-outline btn-info btn-circle show-tooltip" value="export" id="adminProductExport">Export</button>     

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate','true');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Approve"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate','true');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Reject"><i class="ti-lock"></i> </a> 

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>  

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
        
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <!-- <div class="table-responsive"> -->
                <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped white-space-no-wrap-table">
                      <thead>
                          <tr>
                          	<th>
                          	 	<div class="checkbox checkbox-success"><input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                          	 	</div>
				                    </th>
                              <th>Image</th>
                              <th>Product</th>    
                              <th>Vendor</th>
                              <th>Brand</th>
                              <th>Created On</th> 
                              <th>Vendor Status</th>                         
                              <th>Admin Status</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              </div>
            </form>
            <div class="goto-page">
                  <div class="form-group">
                      <div class="inner-goto">
                        <span>Go to</span>
                      <input type="text" name="search_page_no" id="search_page_no" class="form-control">
                      </div>
                      <div class="inner-goto">
                        <span>Page</span> 
                      <button class="btn btn-go" onclick="redirect_to_page()">Go</button>
                      </div>
                  </div>
                </div> 
        </div>
      </div>         
    </div>
  </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";

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
      stateSave: true,
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_all_products',
      'data': function(d)
        {
        
          d['column_filter[q_product_name]']   = $("input[name='q_product_name']").val()
          d['column_filter[q_company_name]']   = $("input[name='q_company_name']").val()
          d['column_filter[q_brand_name]']     = $("input[name='q_brand_name']").val()
          d['column_filter[q_user_name]']      = $("input[name='q_user_name']").val()
          d['column_filter[q_product_status]'] = $("select[name='q_product_status']").val()  
          d['column_filter[q_status]']         = $("select[name='q_status']").val()         
          d['column_filter[q_create_at]']      = $("input[name='q_create_at']").val()

        }
      },



      columns: [
      {
        render: function(data, type, row, meta)
      {
        return '<div class="checkbox checkbox-success"><input type="checkbox" id="checked_record" name="checked_record[]" value="'+row.enc_id+'" class="checkItem case"><label style="text-decoration: none"></label></div>';
      },
       "orderable": false,
       "searchable":false
      },
  
     {
        render(data, type, row, meta)
        {   
            return `<img class="zoom-img" src="`+row.product_image+`" height="100px" width="100px">`;
        },
        "orderable": false, "searchable":false
      },  
      // {data: 'product_name', "orderable": false, "searchable":false},
      {                  
        render(data, type, row, meta)
        {
             return `<a href="`+module_url_path+`/view/`+row.enc_id+`" class="link-underline">`+row.product_name+`</a>`;             
            
        },
        "orderable": false, "searchable":false
      },   
      {data: 'company_name', "orderable": false, "searchable":false},
      {data: 'brand_name', "orderable": false, "searchable":false},
      // {data: 'user_name', "orderable": true, "searchable":false},
      
      {data: 'created_at', "orderable": false, "searchable":false},
      {data: 'product_complete_status',orderable: false, searchable: false,responsivePriority:4,
         render(data, type, row, meta)
         {
              if(row.product_complete_status == 4)
              {
                return `<span class="label label-success">Completed</span>`;             
              }
              else
              {
                return `<span class="label label-success">Incomplete</span>`;                         
              }
          }
      },                      
     /* {data: 'is_active',
                      orderable: false, 
                      searchable: false,
                      responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.is_active == 1)
                        {
                          return `<input type="checkbox" checked data-size="small" class="js-switch" onchange='statusChange(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" />`;

                            // return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.enc_id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262"/>`
                        }
                        else
                        {

                          return `<input type="checkbox" data-size="small" class="js-switch" onchange='statusChange(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" />`

                            // return `<input type="checkbox" data-size="small" data-enc_id="`+row.enc_id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262"/>`
                        }
                    }
                  },      */

      {
        render(data, type, row, meta)
        {
             return row.is_active;
        },
        "orderable": false, "searchable":false
      },
                  
      {                  
        render(data, type, row, meta)
        {
            /* return `<a href="`+module_url_path+`/view/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" title="View Product Details" class="btn btn-circle btn-success btn-outline show-tooltip">View</i></a>`;*/

             return `<a href="`+module_url_path+`/delete/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip del_btn" onclick="confirm_delete(this,event);">Delete</a>

             <a href="`+module_url_path+`/view/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" title="View Product Details" class="btn btn-circle btn-success btn-outline show-tooltip view_btn">View</i></a>`;
            
        },
        "orderable": false, "searchable":false
      }     
      ]
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

      $("#table_module").on('page.dt', function (){

        var info = table_module.page.info();
       
        $("input.checkItemAll").prop('checked',false);
    
      });



    table_module.page(1).draw(false);
    $('#table_module').on('draw.dt',function(event)
    {
      var oTable = $('#table_module').dataTable();
      var recordLength = oTable.fnGetData().length;
      $('#record_count').html(recordLength);

      var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
           new Switchery($(this)[0], $(this).data());
        });   

        toggleSelect();  
    });

    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td></td>
                    <td></td>
                    <td class="link-underline"><input type="text" name="q_product_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>  
                     
                    <td><input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>  


                    <td><input type="text" name="q_brand_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                    <td><input type="text" name="q_create_at" placeholder="Search" class="search-block-new-table column_filter form-control-small datepicker" onchange="filterData();" readonly/></td> 
                    
                    <td> <select class="search-block-new-table column_filter form-control-small" name="q_product_status" id="q_product_status" onchange="filterData();">

                        <option value="">All</option>
                        <option value="4">Completed</option>
                        <option value="1">Incomplete</option>
                      
                        </select></td>   


                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Rejected</option>
                        <option value="2">Pending</option>
                        </select>
                    </td>    
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

 function redirect_to_page(){
     var page_no = Math.abs($("#search_page_no").val());       
      if(page_no != '0' && $.isNumeric( page_no )){
            page_no = page_no - 1;
            var page_no_new = parseInt(page_no);  
            table_module.page(page_no_new).draw(false);
      }  
  }

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this product.');
  }
  
  // function statusChange(data)
  // {
    
  //         var ref = data; 
  //         var type = data.attr('data-type');
  //         var enc_id = data.attr('data-enc_id');
  //         var id = data.attr('data-id');

  //         $.ajax({
  //             url:module_url_path+'/'+type,
  //             type:'GET',
  //             data:{id:enc_id},
  //             dataType:'json',
  //             success: function(response)
  //             {
  //               if(response.status=='SUCCESS')
  //               {
  //                 if(response.data=='ACTIVE')
  //                 {
  //                   $(ref)[0].checked = true;  
  //                   $(ref).attr('data-type','deactivate');

  //                 }else
  //                 {
  //                   $(ref)[0].checked = false;  
  //                   $(ref).attr('data-type','activate');
  //                 }

  //                 swal('Success','Status change successfully','success');
  //               }
  //               else
  //               {
  //                 sweetAlert('Error','Something went wrong!','error');
  //               }  
  //             }
  //         });   
  // } 
  
  function statusChange(product_id,ref)
  {
    var productStatus = '';
     
    if($(ref).is(":checked"))
    {
      productStatus = '1';
    }
    else
    {
      productStatus = '0';
    }
           
      $.ajax({
         method   : 'GET',
         dataType : 'JSON',
         data     : {product_id:product_id,productStatus:productStatus},
         url      : module_url_path+'/changeStatus/',
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
  }
  
/* check all */
/*$('#checked_record_all').change(function () 
{ 
  var is_checked = $(this).is(":checked"); 
  $('.checkItem').each(function(index,elem)
  {
     $(elem).prop('checked', is_checked);     
  });     
});*/


    
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

var saveData = (() => {
    var a = document.createElement('a');
    a.style = 'display: none';
    document.body.appendChild(a);

    return (data, fileName, type = 'application/csv') => {
      var blob = new Blob([data], { type });

      if (navigator.msSaveBlob) {
        return navigator.msSaveBlob(blob, fileName);
      }

      var url = URL.createObjectURL(blob);
      a.href = url;
      a.download = fileName;
      a.click();
      URL.revokeObjectURL(url);
      return true;
    };
  })();

function adminProductExport(){

    var q_product_name            = $("input[name='q_product_name']").val();
    var q_company_name            = $("input[name='q_company_name']").val()
    var q_brand_name              = $("input[name='q_brand_name']").val();
    var q_user_name               = $("input[name='q_user_name']").val()
    var q_product_status          = $("select[name='q_product_status']").val()
    var q_status                  = $("select[name='q_status']").val()
    var q_create_at               = $("input[name='q_create_at']").val()

    $.ajax({
          url: module_url_path+'/get_export_admin_product',
          data: {q_product_name:q_product_name,q_company_name:q_company_name,q_brand_name:q_brand_name,q_user_name:q_user_name,q_product_status:q_product_status,q_status:q_status,q_create_at:q_create_at},
        
          type:"get",
          beforeSend: function() 
          {
             showProcessingOverlay();                
          },
          success:function(data)
          {
            hideProcessingOverlay();
            if(data.status != null && data.status == 'error')
            {
              swal('Error',data.message,'error');
            }
            else
            {
              saveData(data, 'admin_products.csv');
            }
          }
        });
 }

/*setTimeout(function(){ 
  toggleSelect();
},1000);*/



</script>

@stop 