@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
<style>
  .multiselect-container {z-index:1;}
  .btn-primary, .btn-primary.disabled{
        width: 180px;
            padding: 8px 15px 6px;
    display: inline-block; border-radius: 3px;
  }
th {
    white-space: nowrap;
}
.multiselect-container>li .checkbox input[type=checkbox]{
  opacity: 1;
}
.input-group-btn{display: none;}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
    color: #564126;
    text-decoration: none;
    outline: 0;
       background-color: #eff3f8;
    border-bottom: 1px solid #d9dee4;
}
ul.multiselect-container.dropdown-menu {
    max-height: 290px;
    overflow: auto;
}
.frms-slt {
       display: block;
    position: relative;
    margin-bottom:0px;
}
.frms-slt .parsley-errors-list{
    position: relative;
    bottom: -63px;
    z-index: 9;
    width: 100%;
    display: block;
}
</style>
<!-- For multiselect with search -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<div class="row">
   <div class="col-sm-12">
      <div class="white-box">
             @include('admin.layout._operation_status')
         <div class="row">
            <div class="col-sm-12 col-xs-12">
              
               <form class="form-horizontal" id="validation-form" 
               > 
               {{ csrf_field() }}

               <div class="row m-b-20">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Area Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="area_name" placeholder="Enter Area Name" data-parsley-required="true" data-parsley-required-message="Please enter area name"/>
                     <span class="red">{{ $errors->first('area_name') }}</span>
                  </div>
               </div>

                  
               <div class="row m-b-20">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">States Covered<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     
                     <select id="boot-multiselect-demo" name="rep_state[]" id="rep_state" multiple="multiple" data-parsley-required="true" data-parsley-required-message="Please select states covered" data-parsley-errors-container="#error_container">
                     
                          @if(isset($state_names) && count($state_names)>0)
                             @foreach($state_names as $states)
                                @if(isset($states['name']) && count($states['name'] > 0))
                                       <option value="{{$states['id']}}">{{$states['name']}}</option>
                                @endif
                              @endforeach
                         @endif
                        </select>
                     <span id="error_container" class="red">{{ $errors->first('state') }}</span>
                  </div>
               </div>


               <div class="row m-b-20">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Division Categories</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     
                      
                     <select id="boot-multiselect-demo_category" name="category_names[]" id="category_names" multiple="multiple">
                     
                          @if(isset($category_names) && count($category_names)>0)
                             @foreach($category_names as $category)
                                @if(isset($category['cat_division_name']))

                                  <option value="{{$category['id']}}">{{$category['cat_division_name']}}</option>

                                @endif
                              @endforeach
                         @endif
                        </select>
                     <span class="red">{{ $errors->first('category') }}</span>
                  </div>
               </div>



               <div class="row m-b-20">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                         <input type="checkbox" checked="checked" name="area_status" id="area_status" value="1" data-size="small" class="js-switch active" data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div>
                    <div class="cancel_back_flex_btn ">
                      <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                      <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>

              </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">


    $(document).ready(function() {
      $('#boot-multiselect-demo').multiselect({
      includeSelectAllOption: false,
      enableFiltering: true,
      nonSelectedText: 'Select States'
  });
    });


       $(document).ready(function() {
      $('#boot-multiselect-demo_category').multiselect({
      includeSelectAllOption: false,
      enableFiltering: true,
      nonSelectedText: 'Select Division Category'
  });
    });




  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        // console.log(formdata);
        
        $.ajax({
          url: module_url_path+'/save',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend : function()
          {
              showProcessingOverlay();
          },
          success:function(data)
          {
            // console.log(data);
            hideProcessingOverlay();
             if('success' == data.status)
             {
                
                 $('#validation-form')[0].reset();

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
                          window.location = module_url_path;
                       }
                     });
              }
              else
              {
                
                 swal("Error",data.description,data.status);
              }  
          }
          
        });   

      });
   });

    function toggle_status()
    {
        var area_status = $('#area_status').val();
        if(area_status=='1')
        {
          $('#area_status').val('1');
        }
        else
        {
          $('#area_status').val('0');
        }
    }
  
</script>
@stop




