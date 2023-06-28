@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
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
<!-- .row -->
<div class="row">
   <div class="col-sm-12">
      <div class="white-box">
         @include('admin.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
                 <h3>
                    <span 
                       class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" >
                    </span>
                 </h3>
            </div>
          </div>
                    
          <div class="col-sm-8">

            
        <table class="table table-striped table-bordered">
                         <tr>
                          <th> Name</th>
                          <td>
                            {{ isset($arr_data['name']) && $arr_data['name'] !=""  ?$arr_data['name']:'NA' }}
                          </td>
                        </tr> 
                        
                        <tr>
                          <th>Mobile No</th>
                          <td>{{ isset($arr_data['mobile_no']) && $arr_data['mobile_no'] !=""  ?$arr_data['mobile_no']:'NA' }}</td>
                        </tr>
                        
                        <tr>
                          <th>Type</th>
                          <td>{{ isset($arr_data['type']) && $arr_data['type'] !=""  ?$arr_data['type']:'NA' }}</td>
                        </tr>
                        
                      </table>
                     </div>
                     <div class="form-group row">
                        <div class="col-10">
                           <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path}}">Back</a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
<!-- END Main Content -->


@stop